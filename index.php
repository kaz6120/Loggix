<?php
/**
 * Loggix CMS index.php
 *
 * Loggix Controller
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @since     5.5.16
 * @version   9.8.19
 */
$pathToIndex = '.';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

try {

    $_SERVER['QUERY_STRING'] = htmlentities($_SERVER['QUERY_STRING']);

    // Cleanup the request array.
    $app->insertSafe();
    
    // (1) Search by Tag, by Keyword, and by Date
    if ((!empty($_GET['t'])) || (!empty($_GET['k'])) || (!empty($_GET['d']))) {

        $previousItemNumber = (empty($_GET['p']))  ? '0' : $_GET['p'];
        $date               = (empty($_GET['d']))  ? ''  : $_GET['d'];
        $expand             = (empty($_GET['ex'])) ? '0' : $_GET['ex'];
        $pageNumberToShow   = (empty($_GET['pn'])) ? '1' : $_GET['pn'];

        // Tag Search
        if (!empty($_GET['t'])) {
            $key      = $_GET['t'];
            $params   = $app->getTagSqlParams();
            $sql      = $app->getTagSql($params);
            $countSql = $app->getTagHitsSql($params);
            $resultTemplate = 'tag-result.html';
        // Keyword Search
        } else {
            $key      = $_GET['k'];
            $params   = $app->getSearchSqlParams();
            $sql      = $app->getSearchSql($params);
            $countSql = $app->getSearchHitsSql($params);
            $resultTemplate = 'search-result.html';
        }
        
        // Count the number of hit results
        $totalItemsCount = $app->getTotalItemsCount($countSql);

        if ($totalItemsCount !== '0') {

            // Archive By Date
            if ((preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $date)) ||
                (preg_match('/^[0-9]{4}-[0-9]{2}/', $date))) {
                $result = '';
            } else {
                $item = $app->setSearchItems($totalItemsCount, 
                                             $previousItemNumber, 
                                             $date);
                $resultView = new Loggix_View();
                $templateVars = array('item' => $item,
                                      'lang' => $lang
                                );
                $resultView->assign($templateVars);
                $result = $resultView->render($pathToIndex . '/theme/' . $resultTemplate);
            }
            $title = (!empty($_GET['t'])) 
                   ? ($app->setTitle(array('Tag', $item['keyword']))) 
                   : ($app->setTitle($lang['archive']));
            // Title , Contents, Pager, and Results
            $item = array('title'    => $title,
                          'contents' => $app->getArchives($sql),
                          'pager'    => $app->getPager($totalItemsCount, 
                                                       $pageNumberToShow, 
                                                       $date, 
                                                       $expand),
                          'result'   => $result,
                    );
            $getLastModifiedSql = $sql;
        } else {
            $e = new Loggix_Exception();
            $item = $e->getArticleNotFoundMessage();
        }

    // (2) Permalink
    } elseif (!empty($_GET['id'])) {
        $id  = intval($_GET['id']);
        $getPermalinkSql = 'SELECT '
                         .     '`id`, `title`, `comment`, `date`, `mod`, '
                         .     '`draft`, `allow_comments`, `allow_pings` '
                         . 'FROM '
                         .     LOG_TABLE . ' AS l '
                         . 'WHERE '
                         .     "(l.id = '" . $id . "') AND (l.draft = '0')";
        $getPermalinkSql = $app->setDelimitedIdentifier($getPermalinkSql);
        $stmt = $app->db->prepare($getPermalinkSql);
        if ($stmt->execute() == true) {
            $itemArray = $stmt->fetch();
            $item = $app->setEntryItems($itemArray);
            $contentsView = new Loggix_View();
            $templateVars = array('session_state' => $sessionState,
                                  'module'        => $module,
                                  'item'          => $item,
                                  'lang'          => $lang
                            );
            $contentsView->assign($templateVars);
            $template = $pathToIndex . '/theme/permalink.html';
            $titleDate = date($config['title_date_format'], strtotime($item['date']));
            $item = array('title_date' => $titleDate,
                          'title'      => $app->setTitle($item['title']),
                          'contents'   => $app->plugin->applyFilters(
                                              'permalink-view', 
                                              $contentsView->render($template)
                                          ),
                          'pager'      => '',
                          'result'     => ''
                    );
            $getLastModifiedSql = $getPermalinkSql;
            
        } else {
            $e = new Loggix_Exception();
            $item = $e->getFileNotFoundMessage();
        }
        
    // (3) Index View (Show the Latest Entries)
    } else {                                
        $getLatestItemsSql  = 'SELECT '
                            . '*'
                            . 'FROM '
                            .     LOG_TABLE . ' '
                            . 'WHERE '
                            .     "draft = '0' "
                            . 'ORDER BY '
                            .     'date DESC '
                            . 'LIMIT '
                            .     $config['page_max'];
        $countTotalItemsSql = 'SELECT '
                            .     'COUNT(id) '
                            . 'FROM '
                            .     LOG_TABLE . ' l '
                            . 'WHERE '
                            .     "draft = '0'";
        $totalItemsCount = $app->getTotalItemsCount($countTotalItemsSql);
        $item = array(
                    'title'     => $app->setTitle(array()),
                    'contents'  => $app->getArchives($getLatestItemsSql),
                    'pager'     => $app->getPager($totalItemsCount, 
                                                  $pageNumberToShow = '1', 
                                                  $date = '', 
                                                  $expand = '0'),
                    'result'    => ''
                );

        $getLastModifiedSql = $getLatestItemsSql;

    }
} catch (Loggix_Exception $e) {
    $item = $e->getFileNotFoundMessage();
}

$app->display($item, $sessionState);
