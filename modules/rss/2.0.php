<?php
/**
 * RSS-2.0 generator for Loggix
 *
 * @package   RSS
 * @since     5.7.22
 * @version   9.1.4
 */

/**
 * Include RSS Module class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/lib/Loggix/Module/Rss.php';

$theRss = new Loggix_Module_Rss;
$config = $theRss->getConfigArray();

if (empty($_GET['k']))  { $_GET['k']  = '';  } // Keyword
if (empty($_GET['p']))  { $_GET['p']  = '0'; } // Start page of the result
if (empty($_GET['pn'])) { $_GET['pn'] = '1'; } // Page number
if (empty($_GET['ex'])) { $_GET['ex'] = '0'; } // Expand the pager link
if (empty($_GET['d']))  { $_GET['d']  = '';  } // Date

if (!isset($_GET['mode'])) {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // RECENT ENTRIES
        if ($id == 'recent') {
            $sql = 'SELECT '
                 .     '* '
                 . 'FROM ' 
                 .     LOG_TABLE . ' '
                 . 'WHERE '
                 .     "draft = '0' "
                 . 'ORDER BY '
                 .     'date DESC '
                 . 'LIMIT ' 
                 .     $config['page_max'];
            $checkLatestSql = 'SELECT '
                            .     'date '
                            . 'FROM ' 
                            .     LOG_TABLE . ' '
                            . 'WHERE '
                            .     "draft = '0' "
                            . 'ORDER BY '
                            .     'date desc LIMIT 1';
            $params = array('fields'  => '*',
                            'table'   => LOG_TABLE,
                            'title'   => 'title',
                            'comment' => 'comment',
                            'date'    => 'date'
                      );
            $dir = '';
            
        // PERMALINK
        } elseif (intval($id)) {
            $sql = 'SELECT '
                 .     '* '
                 . 'FROM ' 
                 .     LOG_TABLE . ' '
                 . 'WHERE '
                 .     "(id = '" . $id . "') AND (draft = '0')";
            $checkLatestSql = $sql;
            $params = array('fields'  => '*',
                           'table'   => LOG_TABLE,
                           'title'   => 'title',
                           'comment' => 'comment',
                           'date'    => 'date'
                      );
            $dir = '';
            
        // DOWNLOADS LATEST
        } elseif (stristr($id, 'dl_')) {
            if ($id == 'dl_recent') {
                $sql = 'SELECT '
                     .     '* '
                     . 'FROM ' 
                     .     DOWNLOADS_META_TABLE . ' '
                     . 'WHERE '
                     .     "draft = '0' "
                     . 'ORDER BY '
                     .     'file_date DESC '
                     . 'LIMIT ' 
                     .     $config['page_max'];
                $checkLatestSql = 'SELECT '
                                .     'file_date '
                                . 'FROM ' 
                                .     DOWNLOADS_META_TABLE . ' '
                                . 'WHERE '
                                .    "draft = '0' "
                                . 'ORDER BY '
                                .     'file_date DESC '
                                . 'LIMIT '
                                .     '1';
            } else { // DOWNLOADS PERMALINK
                $targetId = str_replace('dl_', '', $id);
                $sql = 'SELECT '
                     .     '* '
                     . 'FROM ' 
                     .     DOWNLOADS_META_TABLE . ' '
                     . 'WHERE '
                     .     "(id = '" . $targetId . "') AND (draft = '0')";
                $checkLatestSql = $sql;
            }
            $params = array('fields'  => '*',
                            'table'   => DOWNLOADS_META_TABLE,
                            'title'   => 'file_title',
                            'comment' => 'file_comment',
                            'date'    => 'file_date'
                      );
            $dir = 'modules/downloads/';
            
        // RECENT COMMENTS
        } elseif (stristr($id, 'comments_')) {
            if ($id == 'comments_recent') {
                $sql = 'SELECT '
                     .     '* '
                     . 'FROM ' 
                     .     COMMENT_TABLE . ' '
                     . 'WHERE '
                     .     "trash = '0' "
                     . 'ORDER BY '
                     .     'date DESC '
                     . 'LIMIT '
                     .     $config['page_max'];
                $checkLatestSql = 'SELECT '
                                .     'date '
                                . 'FROM ' 
                                .     COMMENT_TABLE . ' '
                                . 'WHERE '
                                .     "trash = '0' "
                                . 'ORDER BY '
                                .     'date DESC '
                                . 'LIMIT '
                                .     '1';
            } else {
                $targetId = str_replace('comments_', '', $id);
                $sql = 'SELECT '
                     .     '* '
                     . 'FROM ' 
                     .     COMMENT_TABLE . ' '
                     . 'WHERE '
                     .     "(id = '" . $targetId . "') AND (trash = '0')";
                $checkLatestSql = $sql;
            }
            $params = array('fields'  => '*',
                            'table'   => COMMENT_TABLE,
                            'title'   => 'title',
                            'comment' => 'comment',
                            'date'    => 'date'
                      );
            $dir = '';
            
        // TRACKBACKS
        } elseif (stristr($id, 'trackback_')) {
            if ($id == 'trakcback_recent') {
                $sql = 'SELECT '
                     .     '* '
                     . 'FROM ' 
                     .     TRACKBACK_TABLE . ' '
                     . 'WHERE '
                     .     "trash = '0' "
                     . 'ORDER BY '
                     .     'date DESC '
                     . 'LIMIT '
                     .     $config['page_max'];
                $checkLatestSql = 'SELECT '
                                .     'date '
                                . 'FROM ' 
                                .     TRACKBACK_TABLE . ' '
                                . 'WHERE '
                                .     "trash = '0' "
                                . 'ORDER BY '
                                .     'date DESC LIMIT 1';
            } else {
                $targetId = str_replace('comments_', '', $id);
                $sql = 'SELECT '
                     .     '* '
                     . 'FROM ' 
                     .     TRACKBACK_TABLE . ' '
                     . 'WHERE '
                     .     "(id = '" . $targetId . "') AND (trash = '0')";
                $checkLatestSql = $sql;
            }
            $params = array('fields'  => '*',
                            'table'   => TRACKBACK_TABLE,
                            'title'   => 'title',
                            'comment' => 'exerpt',
                            'date'    => 'date'
                      );
            $dir = '';
        }
    } else {
    
        $previousItemNumber = (empty($_GET['p']))  ? '0' : $_GET['p'];
        $date               = (empty($_GET['d']))  ? ''  : $_GET['d'];
        $expand             = (empty($_GET['ex'])) ? '0' : $_GET['ex'];
        $pageNumberToShow   = (empty($_GET['pn'])) ? '1' : $_GET['pn'];
        
        // Tag
        if (!empty($_GET['t'])) {
            $key    = $_GET['t'];
            $params = $theRss->getTagSqlParams();
            $sql    = $theRss->getTagSql($params);
            $dir    = '';
        // Keyword Search
        } elseif (!empty($_GET['k'])) {
            $key    = $_GET['k'];
            $params = $theRss->getSearchSqlParams();
            $sql    = $theRss->getSearchSql($params);
            $dir    = '';
        // Downloads Tag
        } elseif (!empty($_GET['dl_t'])) {
            $key    = $_GET['dl_'];
            $dRss   = new Loggix_Module_Downloads;
            $params = $dRss->getTagSqlParams();
            $sql    = $dRss->getTagSql($params);
            $dir    = '';
        // Downloads Keyword Search
        } elseif (!empty($_GET['dl_k'])) {
            $key    = $_GET['dl_k']; 
            $dRss   = new Loggix_Module_Downloads;
            $params = $dRss->getSearchSqlParams();
            $sql    = $dRss->getSearchSql($params);
            $dir    = '';
        }
        // Check Last-Modified SQL
        $checkLatestSql = $sql;
    }
    // Get Last-Midified
    $res = $theRss->db->query($checkLatestSql);
    $row = $res->fetch();
    $xml['last_modified'] = date(
                                $theRss->getRssTimeFormat('2.0'), 
                                strtotime($row[$params['date']])
                            ) . $theRss->getTimeZone();
    
    $stmt = $theRss->db->prepare($sql);

    if ($stmt->execute() == true) {
        $items     = array();
        $item      = array();
        while ($row = $stmt->fetch()) {
            // Link
            $targetId = ((isset($id)) && (stristr($id, 'comments_')))
                      ? $row['refer_id'].'#c'.$row['id']
                      : $row['id'];
            $link = $theRss->getRootUri() . $dir . 'index.php?id=' . $targetId;
            
            // Comment to Content Module
            $row[$params['comment']] = str_replace(
                                           './data/resources/', 
                                           $theRss->getRootUri() . 'data/resources/', 
                                           $row[$params['comment']]
                                       );
                
            // Apply Smiley 
            $row[$params['comment']] = $theRss->setSmiley($row[$params['comment']]);

            $row[$params['comment']] = str_replace(
                                           '/index.php?id=' . $targetId . '../../theme/', 
                                           '/theme/', 
                                           $row[$params['comment']]
                                       );
            // Apply plugin filter
            $row[$params['comment']] = $theRss->plugin->applyFilters('entry-content', $row[$params['comment']]); 
            
            // Excerpt
            if (isset($row['excerpt'])) {
                $description = ($row['excerpt'] != '') 
                             ? htmlspecialchars($row['excerpt']) 
                             : htmlspecialchars(
                                   mb_substr(
                                       strip_tags($row[$params['comment']]), 0, 120, 'UTF-8'
                                   )
                               ) . '...';
            } else {
                $description = htmlspecialchars(
                                   mb_substr(
                                       strip_tags($row[$params['comment']]), 0, 120, 'UTF-8'
                                   )
                               ) . '...';
            }
            // Enclosure
            $item['enclosure'] = (stristr(isset($id), 'dl_'))
                               ?    '<enclosure url="'
                                  . $theRss->getRootUri() . $dir . 'dl.php?id=' . $row['id']
                                  . '" length="' . $row['file_size'] 
                                  . '" type="' . $row['file_type'] 
                                  . '" />'."\n"
                               : $theRss->getEnclosure($row[$params['comment']]);
                                     
            $item['description']     = $theRss->setBBCode($description);
            $item['content_encoded'] = $row[$params['comment']];
            $item['link']            = $link;
            $item['title']           = htmlspecialchars($row[$params['title']]);
            $item['date']            = date(
                                           $theRss->getRssTimeFormat('2.0'), 
                                           strtotime($row[$params['date']])
                                       ) . $theRss->getTimeZone();
            $items[] = $item;
        }
        $xml['loggix_title'] = $config['loggix_title'];
        $xml['root_uri'] = $theRss->getRootUri() . 'index.php';
        $xml['rss_uri']  = $theRss->getRssUri('2.0');
        
        header("Content-type: application/xml");

        $rssVersion2 = new Loggix_View();
        $rssVersion2->assign('items', $items);
        $rssVersion2->assign('xml', $xml);
        $rssVersion2->display($pathToIndex . '/modules/rss/theme/rss.2.0.xml');
    }
} else { // Loggix Expander RSS

    // Get Loggix Expander Directory
    $expanderDir = str_replace('_', '/', 
                   str_replace('_index', '', 
                   str_replace('_modules', 'modules', 
                   $_GET['mode'])));
    
    // Resource Path
    $resourcePath = $expanderDir . '/data/' 
                  . ((isset($_GET['id'])) ? $_GET['id'] : 'default');
    
    // Resource URI
    $resourceUri  = str_replace('/data/', '/index.php?id=', $resourcePath);

    // Resource
    $resource = $pathToIndex . '/' . $resourcePath;
    
    // Get File Contents
    if (file_exists($resource . '.inc.php')) {
        $extension = '.inc.php';
        $item['pkg'] = $theRss->getPackageInfo();
        $item['vars']['last_modified'] = date(
            $theRss->getRssTimeFormat('2.0'), 
            filemtime($resource . $extension)
        ).$theRss->getTimeZone();
        include_once $resource . $extension;
        $retrievedContents = $contents;
    } elseif (file_exists($resource . '.text')) {
        $extension = '.text';
        $contents = new Loggix_View($resource . $extension);
        $retrievedContents = $theRss->fixMarkdown(
            Markdown($contents->render())
        );
    } elseif (file_exists($resource . '.txt')) {
        $extension = '.txt';
        $contents = new Loggix_View($resource . $extension);
        $retrievedContents = $contents->render();
    } else {
        $extension = '.html';
        $contents = new Loggix_View($resource . $extension);
        $retrievedContents = $contents->render();
    }

    // Comment to Content Module
    $retrievedContents = str_replace(
                             './data/resources/', 
                             $theRss->getRootUri() . $expanderDir . '/data/resources/',
                             $retrievedContents
                         );
    
    $item['title'] = $theRss->setTitle($config['title']);
    $item['link'] = $theRss->getRootUri() . $resourceUri;
    $item['content_encoded'] = $retrievedContents;
    $item['date'] = date(
        $theRss->getRssTimeFormat('2.0'), 
        filemtime($resource . $extension)
    ).$theRss->getTimeZone();
    $item['enclosure'] = '';
    $item['description'] = '';
    $items[] = $item;
    
    $xml['loggix_title'] = $theRss->setTitle($config['title']);
    $xml['root_uri'] = $theRss->getRootUri() .'index.php';
    $xml['rss_uri']  = $theRss->getRootUri() . $resourceUri;
    $xml['last_modified'] = $item['date'];
    
    header("Content-type: application/xml");
    $rssVersion2 = new Loggix_View();
    $rssVersion2->assign('items', $items);
    $rssVersion2->assign('xml', $xml);
    $rssVersion2->display($pathToIndex.'/modules/rss/theme/rss.2.0.xml');

}
