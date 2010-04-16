<?php
/**
 *
 * Loggix Downloads Controller
 *
 * @since     5.7.19
 * @version   10.4.17
 */

/**
 * Include Download Module class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/modules/downloads/lib/LM_Downloads.php';

$app = new LM_Downloads;
$config       = $app->getConfigArray();
$sessionState = $app->getSessionState();
$app->getModuleLanguage('downloads');

try {
    $_SERVER['QUERY_STRING'] = htmlentities($_SERVER['QUERY_STRING']);
    
    // Cleanup the request array.
    $app->insertSafe();
    
    // (1) Category, Keyword Search, or Archive By Date
    if ((!empty($_GET['t'])) || (!empty($_GET['k'])) || (!empty($_GET['d']))) {
        
        $previousItemNumber = (empty($_GET['p']))  ? '0' : $_GET['p'];
        $date               = (empty($_GET['d']))  ? ''  : $_GET['d'];
        $expand             = (empty($_GET['ex'])) ? '0' : $_GET['ex'];
        $pageNumberToShow   = (empty($_GET['pn'])) ? '1' : $_GET['pn'];
        
        // Tag Search
        if (!empty($_GET['t'])) {
            $key = $_GET['t'];
            $params = array(
                'fields'         => '*',
                'main_table'     => 'loggix_downloads_meta',
                'draft'          => 'draft',
                'date'           => 'file_date',
                'map_table'      => 'loggix_downloads_tag_map',
                'log_id'         => 'log_id',
                'tag_id'    => 'tag_id',
                'tag_name'  => 'tag_name',
                'tag_table' => 'loggix_downloads_tag'
            );
            $resultTemplate = 'tag-result.html';
            $sql      = $app->getTagSql($params);
            $countSql = $app->getTagHitsSql($params);
        // Keyword Search
        } else {
            $key = $_GET['k'];
            $params = array(
                'fields'   => 'id, file_title, file_type, file_name, file_size, '.
                              'file_date, file_mod, file_date, file_comment, '.
                              'text_mode, file_count, draft',
                'table'    => 'loggix_downloads_meta AS l',
                'title'    => 'l.file_title',
                'comment'  => 'l.file_comment',
                'date'     => 'l.file_date',
                'draft'    => 'l.draft',
                'group_by' => ''
            );
            $resultTemplate = 'search-result.html';
            $sql      = $app->getSearchSql($params);
            $countSql = $app->getSearchHitsSql($params);
        }
        
        // Count  the number of hit results
        $totalItemsCount = $app->getTotalItemsCount($countSql);
        
        if ($totalItemsCount !== '0') {
            
            // Archive By Date
            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $date)) {
                $result   = '';
            } else {
                $item = $app->setSearchItems($totalItemsCount, $previousItemNumber, $date);
                $resultView = new Loggix_View();
                $templateVars = array(
                                    'item' => $item,
                                    'lang' => $lang
                                );
                $resultView->assign($templateVars);
                $result = $resultView->render($pathToIndex . '/theme/' . $resultTemplate);
            } 
            
            $title = (!empty($_GET['c'])) ? 
                ($app->setTitle(array($item['keyword'], $lang['archive']))) : 
                ($app->setTitle(array('Downloads', $lang['archive'])));

            // Title , Contents, Pager, and Results
            $item = array(
                        'title'    => $title,
                        'contents' => $app->getArchives($sql),
                        'pager'    => $app->getPager($totalItemsCount, 
                                                     $pageNumberToShow, 
                                                     $date, 
                                                     $expand),
                        'result'   => $result,
                    );
        } else {
            $e = new Loggix_Exception();
            $item = $e->getArticleNotFoundMessage();
        }
        
    // (2) Permalink
    } elseif (!empty($_GET['id'])) {
        $id  = intval($_GET['id']);

        $getPermalinkSql = 'SELECT '
                         .     '* '
                         . 'FROM '
                         .     DOWNLOADS_META_TABLE . ' '
                         . 'WHERE '
                         .     '(id = :id) AND (draft = :draft)';

        $getPermalinkSql = $app->setDelimitedIdentifier($getPermalinkSql); 
        $stmt = $app->db->prepare($getPermalinkSql);
        $stmt->execute(
                   array(
                       ':id' => $id,
                       ':draft' => 0,
                   )
               );
        $itemArray = $stmt->fetch();

        if ($itemArray == true) {
            $item = $stmt->fetch();
            $item['title_date'] = date($config['title_date_format'], strtotime($item['file_date']));
            $item = $app->setEntryItems($itemArray);
            $permalinkTemplateFile = $pathToIndex . LM_Downloads::THEME_PATH
                                   . 'parmalink.html';
            $contentsView = new Loggix_View($permalinkTemplateFile);
            $contentsVars = array('session_state' => $sessionState,
                                  'module' => $module,
                                  'item' => $item,
                                  'lang' => $lang
                            );
            $contentsView->assign($contentsVars);
            $permalinkTitle = $app->setTitle(array('Downloads', $item['file_title']));
            $item = array(
                        'title'    => $permalinkTitle,
                        'contents' => $app->plugin->applyFilters('permalink-view', $contentsView->render()),
                        'pager'    => '',
                        'result'   => ''
                    );
        } else {
            $e = new Loggix_Exception();
            $item = $e->getFileNotFoundMessage();
        }
    // (3) Index View (Show Recent Entries)
    } else {
        $getLatestItemsSql  = 'SELECT '
                            .     '* '
                            . 'FROM '
                            .     DOWNLOADS_META_TABLE . ' AS d '
                            . 'WHERE '
                            .     "d.draft = '0' "
                            . 'ORDER BY '
                            .     'd.file_date DESC '
                            . 'LIMIT '
                            .     $config['page_max'];
        $countTotalItemsSql = 'SELECT '
                            .     'COUNT(d.id) '
                            . 'FROM ' 
                            .     DOWNLOADS_META_TABLE . ' AS d '
                            . 'WHERE '
                            .     "d.draft = '0'";
        $totalItemsCount    = $app->getTotalItemsCount($countTotalItemsSql);

        $item = array(
                    'title'     => $app->setTitle('Downloads'),
                    'contents'  => $app->getArchives($getLatestItemsSql),
                    'pager'     => $app->getPager($totalItemsCount, 
                                                  $pageNumberToShow = '1', 
                                                  $date = '', 
                                                  $expand = '0'),
                     'result'    => ''
                );
    }
} catch (Loggix_Exception $e) {
    $item = $e->getArticleNotFoundMessage();
}

$app->display($item, $sessionState);

