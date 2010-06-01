<?php
/**
 * @package   Comments
 * @since     5.7.19
 * @version   10.6.1
 */

/**
 * Include Comment Module class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/lib/Loggix/Module/Comment.php';

$app = new Loggix_Module_Comment;
$config       = $app->getConfigArray();
$sessionState = $app->getSessionState();

try {
    $_SERVER['QUERY_STRING'] = htmlentities($_SERVER['QUERY_STRING']);
    
    $app->insertSafe();
    
    // (1) Keyword Search, or Archive By Date
    if ((!empty($_GET['c'])) || (!empty($_GET['k'])) || (!empty($_GET['d']))) {
    
        $previousItemNumber  = (empty($_GET['p']))  ? '0' : $_GET['p'];
        $date                = (empty($_GET['d']))  ? ''  : $_GET['d'];
        $expand              = (empty($_GET['ex'])) ? '0' : $_GET['ex'];
        $pageNumberToShow    = (empty($_GET['pn'])) ? '1' : $_GET['pn'];

        // Keyword Search
        if (isset($_GET['k'])) {
            $key = $_GET['k'];
            $params = array(
                          'fields'   => '*',
                          'table'    => COMMENT_TABLE . ' AS l',
                          'title'    => 'l.title',
                          'comment'  => 'l.comment',
                          'date'     => 'l.date',
                          'draft'    => 'l.trash',
                          'group_by' => 'l.refer_id'
                      );
            $resultTemplateFile = 'search-result.html';
            $sql      = $app->getSearchSql($params);
            $countSql = $app->getSearchHitsSql($params);
        }

        // Count the number of hit results
        $res = $app->db->query($countSql);
        $totalItemsCount = count($res->fetchAll());

        if ($totalItemsCount !== 0) {

            // Archive By Date
            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $date)) {
                $contents = $app->getArchives($sql);
                $pager    = $app->getPager($totalItemsCount, $pageNumberToShow, $date, $expand);
                $result   = '';
            } else {
                $contents = $app->getArchives($sql);
                $pager    = $app->getPager($totalItemsCount, $pageNumberToShow, $date, $expand);
                $templateFile = $pathToIndex . '/theme/' . $resultTemplateFile;
                $resultView = new Loggix_View($templateFile);
                $item = $app->setSearchItems($totalItemsCount, $previousItemNumber, $date);
                $resultView->assign('item', $item);
                $resultView->assign('lang', $lang);
                $result = $resultView->render();
            } 
            // Contents, Pager, and Results
            $item['contents'] = $contents;
            $item['pager']    = $pager;
            $item['result']   = $result;
            $item['title'] = (!empty($_GET['c'])) 
                ? ($app->setTitle(array($item['keyword'], $lang['archive']))) 
                : ($app->setTitle($lang['archive']));
        } else {
            $e = new Loggix_Exception();
            $item = $e->getArticleNotFoundMessage();
        }
        
        
    // (2) Index View (Show Recent Entries)
    } else {

        $sql = 'SELECT '
             .     '* '
             . 'FROM '
             .     COMMENT_TABLE . ' '
             . 'WHERE '
             .     "trash = '0' "
             . 'GROUP BY '
             .     'refer_id '
             . 'ORDER BY '
             .     'date DESC '
             . 'LIMIT '
             .     $config['page_max'];
        $countTotalItemsSql = 'SELECT '             
                            .     'COUNT(id) '
                            . 'FROM ' 
                            .     COMMENT_TABLE . ' '
                            . 'WHERE '
                            .     "trash = '0' "
                            . 'GROUP BY '
                            .     'refer_id';

        $countRes  = $app->db->query($countTotalItemsSql);
        $totalItemsCount = count($countRes->fetchAll());
        $item = array(
            'title'    => $app->setTitle('Comments'),
            'contents' => $app->getArchives($sql),
            'pager'    => $app->getPager($totalItemsCount, 
                                         $pageNumberToShow = '1', 
                                         $date = '', 
                                         $expand = '0'),
            'result'   => '',
        );
    }
} catch (Exception $e) {
    $templateFile = $pathToIndex . '/theme/errors/data-not-found.html';
    $contentsView = new Loggix_View($templateFile);
    $item = array(
        'title'    => $app->setTitle('404 Not Found'),
        'contents' => $contentsView->render(),
        'pager'    => '',
        'result'   => '',
    );
}

$app->display($item, $sessionState);

