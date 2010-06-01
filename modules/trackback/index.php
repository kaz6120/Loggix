<?php
/**
 * @package   Trackback
 * @since     6.1.29
 * @version   10.6.1 
 */

/**
 * Include Trackback Module class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/lib/Loggix/Module/Trackback.php';

$app = new Loggix_Module_Trackback;
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
            $q = array('fields'   => '*',
                       'table'    => TRACKBACK_TABLE . ' AS t',
                       'title'    => 't.title',
                       'comment'  => 't.excerpt',
                       'date'     => 't.date',
                       'draft'    => 't.trash',
                       'group_by' => 't.blog_id'
                 );
            $resultViewFile = 'search-result.html';
            $sql  = $app->getSearchSql($q);
            $sql2 = $app->getSearchHitsSql($q);
        }
        
        if ($res = $app->db->query($sql)) {
            // Get the number of hit results
            $res2 = $app->db->query($sql2);
            $totalItemsCount = count($res2->fetchAll());
            //echo $totalItemsCount;

            if ($totalItemsCount !== 0) {

                // Archive By Date
                if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $date)) {
                    $contents = $app->getArchives($res);
                    $pager    = $app->getPager($totalItemsCount, 
                                               $pageNumberToShow, 
                                               $date, 
                                               $expand);
                    $result   = '';
                } else {
                    $contents = $app->getArchives($res);
                    $pager    = $app->getPager($totalItemsCount, 
                                               $pageNumberToShow, 
                                               $date, 
                                               $expand);
                    $result = new Loggix_View($pathToIndex . '/theme/' . $resultViewFile);
                    $item = $app->setSearchItems($totalItemsCount, $previousItemNumber, $date);
                    $result->assign('item', $item);
                    $result->assign('lang', $lang);
                    $result = $result->render();
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
        }
        
    // (2) Index View (Show Recent Entries)
    } else {
        $sql = 'SELECT '
             .     '* '
             . 'FROM ' 
             .     TRACKBACK_TABLE . ' '
             . 'GROUP BY '
             .     'blog_id '
             . 'ORDER BY '
             .     'date DESC '
             . 'LIMIT '
             .     $config['page_max'];
        
        $countTotalItemsSql = 'SELECT '
                            .     'COUNT(id) '
                            . 'FROM ' 
                            .     TRACKBACK_TABLE . ' '
                            . 'GROUP BY '
                            .     'blog_id';
//        $totalItemsCount = $app->getTotalItemsCount($countTotalItemsSql);
        $countRes = $app->db->query($countTotalItemsSql);
        $totalItemsCount = count($countRes->fetchAll());
//echo $totalItemsCount;
        $item = array('title'    => $app->setTitle('Trackbacks'),
                      'contents' => $app->getArchives($app->db->query($sql)),
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

