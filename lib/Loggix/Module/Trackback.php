<?php
/**
 * Loggix_Module_Trackback - Trackback Module for Loggix
 * 
 * PHP version 5
 *
 * @package   Loggix_Module
 * @copyright Copyright (C) Loggix Project
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.6.8
 * @version   10.2.20 
*/


/**
 * Include Module class
 */
require_once $pathToIndex . '/lib/Loggix/Module.php';


/**
 * @package   Loggix_Module
 */
class Loggix_Module_Trackback extends Loggix_Module
{
    const MODULE_DIR           = 'modules/trackback';
    const TRACKBACK_THEME_PATH = '/modules/trackback/theme/';
    
    /**
     * Retrieve Trackbacks from database
     *
     * @param  $item
     * @return boolean
     */
    public function getTrackbacks($item)
    {
        $sql = 'SELECT '
             .     'id, blog_id, title, excerpt, url, name, date '
             . 'FROM ' 
             .     TRACKBACK_TABLE . ' '
             . 'WHERE '
             .     '(blog_id = :refer_id) '
             . 'ORDER BY '
             .     'date ASC';
        
        $stmt = $this->db->prepare($sql);        
        return $stmt->execute(
                   array(
                       ':refer_id' => $item['id']
                   )
               );
    }

    /**
     * Number of Trackbacks
     *
     * @param  $item
     * @return string
     */
    public function getNumberOfTrackbacks($item)
    {
    
        $sql = 'SELECT '
             .     'COUNT(t.id) '
             . 'FROM ' 
             .     TRACKBACK_TABLE . ' AS t '
             . 'WHERE '
             .     '(t.blog_id = :refer_id) AND (t.trash = :trash) '
             . 'ORDER BY '
             .     't.date ASC';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(
                   array(
                       ':refer_id' => $item['id'],
                       ':trash'   => 0
                   )
               );
        return $stmt->fetchColumn();
    }


    /**
     * Get Status of Trackbacks
     *
     * @param  $item
     * @uses   getTrackbacks
     * @return array $item['trackbacks']
     */
    public function getTrackbackStatus($item)
    {
        global $pathToIndex, $lang;
        $item['trackbacks'] = array();
        $rows = $this->getNumberOfTrackbacks($item);
        if ($rows == '0') {
            $item['trackbacks']['status'] = '';
            $item['trackbacks']['num'] = '0';
        } else {
            $item['trackbacks']['status'] = '" class="status-on';
            $item['trackbacks']['num'] = $rows;
        }
        return $item['trackbacks'];
    }


    /**
     * Generate Trackback URI
     *
     * @param  $item
     * @return string
     */
    public function getTrackbackUri($item)
    {
        return $item['trackback']['uri'] = $this->getRootUri()
                                         . self::MODULE_DIR 
                                         . '/tb.php?id=' . $item['id'];
    }


    /**
     * Get Trackback List
     *
     * @param  $item
     * @uses   getTrackbacks
     * @uses   getTrackbackUri
     * @uses   setBBCode
     * @uses   Loggix_View
     * @return string
     */
    public function getTrackbackList($item)
    {
        global $pathToIndex, $lang, $sessionState;
        
        if (!empty($_GET['id'])) {
            $rows = $this->getNumberOfTrackbacks($item);
            $items = array();
            if ($rows > 0) {
                $item['trackback']['list'] = '';
                $sql = 'SELECT '
                     .     'id, blog_id, title, excerpt, url, name, date '
                     . 'FROM ' 
                     .     TRACKBACK_TABLE . ' '
                     . 'WHERE '
                     .     '(blog_id = :refer_id) '
                     . 'ORDER BY '
                     .     'date ASC';
                $stmt = $this->db->prepare($sql);        
                $stmt->execute(
                           array(
                               ':refer_id' => $item['id']
                           )
                       );
                       
                while ($row = $stmt->fetch()) {
                    $item['trackback']['id']      = intval($row['id']);
                    $item['trackback']['title']   = htmlspecialchars(stripslashes($row['title']));
                    $item['trackback']['name']    = htmlspecialchars(stripslashes($row['name']));
                    $item['trackback']['excerpt'] = $this->setBBCode($row['excerpt']);
                    $item['trackback']['date']    = date(self::$config['post_date_format'], strtotime($row['date']));
                    $item['trackback']['url']     = $row['url'];
                    if (!empty($item['trackback']['url'])) {
                        $item['trackback']['name'] = '<a href="'.$item['trackback']['url'].'">'
                                                   . $item['trackback']['name']
                                                   . '</a>'; 
                    }
                    if ($sessionState == 'on') {
                        $item['admin']['delete'] = '<span class="edit">'
                                                 . '<a href="'
                                                 . $pathToIndex . '/' 
                                                 . self::MODULE_DIR 
                                                 . '/delete.php?ping_id=' . $row['id']
                                                 . '&amp;article_id=' . $item['id']
                                                 . '">'
                                                 . $lang['delete']
                                                 . '</a></span>';
                    } else {
                        $item['admin']['delete'] = '';
                    }
                    $items[] = $item;
                }
                $trackbackListTheme = $pathToIndex 
                                    . self::TRACKBACK_THEME_PATH 
                                    .'trackbacks.html';
                $trackbackList = new Loggix_View($trackbackListTheme);
                $trackbackList->assign('items', $items);
                $trackbackList->assign('lang', $lang);
                $item['trackback']['list'] = $trackbackList->render();
            } else {
                $noTrackbackTheme = $pathToIndex 
                                  . self::TRACKBACK_THEME_PATH 
                                  . 'no-trackbacks.html';
                $noTrackbacks = new Loggix_View($noTrackbackTheme);
                $noTrackbacks->assign('lang', $lang);
                $item['trackback']['list'] = $noTrackbacks->render();
            }
            // Get Loggix_View
            $sql = 'SELECT '
                 .     'allow_pings '
                 . 'FROM ' 
                 .     LOG_TABLE . ' '
                 . 'WHERE '
                 .     'id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(
                       array(
                           ':id' => $item['id']
                       )
                   );
            $res = $stmt->fetchColumn();
            
            if ($res == '1') {
                $item['trackback']['uri'] = $this->getTrackbackURI($item);
                $trackbackListTheme = $pathToIndex 
                                    . self::TRACKBACK_THEME_PATH 
                                    . 'trackback-list.html';
                $trackbacks = new Loggix_View($trackbackListTheme);
                $trackbacks->assign('item', $item);
                $trackbacks->assign('lang', $lang);
            } else {
                $trackbackClosedTheme = $pathToIndex 
                                      . self::TRACKBACK_THEME_PATH 
                                      . 'closed.html';
                $trackbacks = new Loggix_View($trackbackClosedTheme);
                $trackbacks->assign('item', $item);
                $trackbacks->assign('lang', $lang);
            }

            return $trackbacks->render();
        } else {
            return '';
        }
    }
    
    
    /**
     * Sending Trackback Ping
     *
     * @param  int $id
     * @return array $pingStatus
     */
    public function sendTrackback($id)
    {
        global $lang, $item, $pingStatus;
    
        if ((!empty($_POST['send_ping_uri'])) && 
            (!empty($_POST['encode'])) &&
            ($_POST['send_ping_uri'] != 'http://')) {
            $pingUri = $_POST['send_ping_uri'];
            $encode  = $_POST['encode'];
        
            $sql  = 'SELECT '
                  .     'title, comment, excerpt, text_mode '
                  . 'FROM ' 
                  .     LOG_TABLE . ' '
                  . 'WHERE '
                  .     'id = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute(
                       array(
                           ':id' => $id
                       )
                   );
            $row = $stmt->fetch();
        
            switch($encode) {
            case 'EUC-JP':
                $row['title']   = mb_convert_encoding($row['title'],   'EUC-JP', 'auto');
                $row['comment'] = mb_convert_encoding($row['comment'], 'EUC-JP', 'auto');
                break;
            case 'SJIS':
                $row['title']   = mb_convert_encoding($row['title'],   'SJIS',   'auto');
                $row['comment'] = mb_convert_encoding($row['comment'], 'SJIS',   'auto');
                break;
            default :
                $row['title']   = mb_convert_encoding($row['title'],   'UTF-8',  'auto');
                $row['comment'] = mb_convert_encoding($row['comment'], 'UTF-8',  'auto');
                break;
            }
    
            $articleUri   = 'http://' . $_SERVER['HTTP_HOST'] . self::$config['root_dir'] . 'index.php?id=' . $id;
            $articleTitle =  stripslashes($row['title']);
        
            // Trim the strings to send
            if (!empty($_POST['excerpt'])) {
                $articleExcerpt = strip_tags($_POST['excerpt']);
            } elseif (!empty($row['excerpt'])) {
                $articleExcerpt = strip_tags($row['excerpt']);
            } else {
                $row['comment'] = $this->plugin->applyFilters('trackback-content', $row['comment']); 
                $articleExcerpt = mb_substr(strip_tags($row['comment']), 0, 100, $encode) . '...';
            }
            
            // send Ping to the target URI
            $targetUri = parse_url($pingUri);
            
            // set default port if port value is empty
            if (!isset($targetUri['port'])) {
                $targetUri['port'] = 80;
            }


            $targetUri['query'] = (isset($targetUri['query'])) 
                                  ? '?' . $targetUri['query']
                                  : '';

            $auth = (isset($targetUri['user'], $targetUri['pass']))
                    ? 'Authorization: Basic ' 
                      . base64_encode($targetUri['user'] 
                      . ':' . $targetUri['pass']) . "\r\n"
                    : '';

            $param = array('url'       => $articleUri,
                           'title'     => $articleTitle,
                           'excerpt'   => $articleExcerpt,
                           'blog_name' => self::$config['loggix_title']);
                           
            while (list($key, $val) = each($param)) {
                $params[] = $key . '=' . urlencode($val);
            }
            $data = join("&", $params);
        
            // prepare the post value
            $post  = 'POST ' . $targetUri['path'] . $targetUri['query']. " HTTP/1.1\r\n"
                   . 'Host: ' . $targetUri['host'] . "\r\n"
                   . 'User-Agent: Loggix' . "\r\n"
                   . $auth
                   . 'Content-Type: application/x-www-form-urlencoded' . "\r\n"
                   . 'Content-Length: ' . strlen($data) . "\r\n\r\n"
                   . $data . "\r\n";


            $fs = fsockopen($targetUri['host'], $targetUri['port']);
            
//            echo var_dump($fs);
            
            if (!$fs) {
                return 'Socket error!';
                $trackbackPingStatus = null;
            } else {
                fputs($fs, $post);       // send data...
                $res = fread($fs, 1024); // ...and get response
                
//                echo var_dump($res);
                
                // Read XML responses to check error
                if (preg_match('/<error>1<\/error>/', $res)) {
                    $trackbackPingResponse = 1;
                } elseif (preg_match('/<error>0<\/error>/', $res)) {
                    $trackbackPingResponse = 0;
                } else {
                    $trackbackPingResponse = null; // null?
                }
            
                // if sending Ping is success, return the results in an array.
                $pingStatus[] = array($pingUri, $trackbackPingResponse);
                
//                echo var_dump($pingStatus);
                // Plugin Action
                $this->plugin->doAction('after-trackback-sent', $pingStatus);
            }
        } else {
            $pingStatus = array();
        }
        
        return $pingStatus;
    }


    /**
     * Sending Weblog Update Ping
     *
     * @param  int $id
     * @return array $updatePingStatus
     */
    public function sendUpdatePing($id)
    {
        global $lang, $item;
        
        if (!empty($_POST['send_update_ping']) && ($_POST['send_update_ping'] == 'yes')) {
        
            $updatePingStatus = array();
            
            $pingServerList = explode(",\r\n", stripslashes(trim(self::$config['ping_server_list'])));
            foreach($pingServerList as $pingTarget) {
                $targetUri = parse_url($pingTarget);
                $fp = fsockopen($targetUri['host'], 80, $errno, $errstr, 30);
                if (!$fp) {
                    return 'Socket error!';
                } else {
                    // prepare XML-RPC request
                    $reqXml = '<?xml version="1.0" encoding="UTF-8"?>'
                            . '<methodCall>'
                            . '<methodName>weblogUpdates.ping</methodName>'
                            . '<params>'
                            . '<param>'
                            . '<value>' . htmlspecialchars(self::$config['loggix_title']) . '</value>'
                            . '</param>'
                            . '<param>'
                            . '<value>' 
                            . 'http://' . $_SERVER['HTTP_HOST'] . self::$config['root_path'] . 'index.php'
                            . '</value>'
                            . '</param>'
                            . '</params>'
                            . '</methodCall>';
                    // prepare the post value
                    $postPing = 'POST ' . $pingTarget . " HTTP/1.1\r\n"
                              . 'Host: ' . $_SERVER['HTTP_HOST'] . "\r\n"
                              . 'User-Agent: Loggix XML-RPC' . "\r\n"
                              . 'Content-Type: text/xml' . "\r\n"
                              . 'Content-Length: ' . strlen($reqXml) . "\r\n\r\n"
                              . $reqXml . "\r\n";     
                    fputs($fp, $postPing);       // send data...
                    $pingRes = fread($fp, 4096); // ...and get response
            
                    // Read XML responses to check error
                    if (preg_match('/<boolean>1<\/boolean>/', $pingRes)) {
                        $pingMessage = '<span class="important">' . $lang['tb_ping_error'] . '</span>';
                    } elseif (preg_match('/<boolean>0<\/boolean>/', $pingRes)) {
                        if (preg_match('/Thanks for your ping/', $pingRes)) {
                            $pingMessage = 'Thanks for your ping.';
                        } elseif (preg_match('/Thanks for the ping/', $pingRes)) {
                            $pingMessage = 'Thanks for the ping.';
                        } else {
                            $pingMessage = $lang['tb_ping_ok'];
                        }
                    } else {
                        $pingMessage = '-';
                    }
                    // if receiving Ping response is success...
                    $updatePingStatus[] = array($pingTarget, $pingMessage);
                }
            }
        } else {
            $updatePingStatus = array();
        }
        return $updatePingStatus;
    }


    /**
     * Set Entry Items
     *
     * @param  array $item
     * @return array $item
     */    
    public function setEntryItem($item)
    {
        global $pathToIndex, $lang, $module;

        $item['refer_id'] = stripslashes($item['blog_id']);
        $item['date']     = stripslashes($item['date']);
        $item['trash']    = stripslashes($item['trash']);
        
        $repSql = 'SELECT '
                .     'COUNT(id) '
                . 'FROM ' 
                .     TRACKBACK_TABLE . ' '
                . 'WHERE '
                .      'blog_id = :refer_id'
                .      ' AND '
                .      'trash = :trash';
        $stmt = $this->db->prepare($repSql);
        $stmt->execute(
                   array(
                       ':refer_id' => $item['blog_id'],
                       ':trash'    => 0
                   )                   
               );
        $item['replies'] = $stmt->fetchColumn();

        // Refered Entry Log Data
        $refSql = 'SELECT '
                .     'l.title, l.date '
                . 'FROM ' 
                .     LOG_TABLE . ' AS l '
                . 'WHERE '
                .     'l.id = :id';
        $stmt2 = $this->db->prepare($refSql);
        $stmt2->execute(
                    array(
                        ':id' => $item['refer_id']
                    )
                );
        $refRow = $stmt2->fetch();
        $item['refer_title'] = stripslashes($refRow[0]);
        $item['refer_date']  = stripslashes($refRow[1]);
        
        // Get Replies
        $repsSql = 'SELECT '
                 .     '* '
                 . 'FROM ' 
                 .     TRACKBACK_TABLE . ' '
                 . 'WHERE '                 
                 .     'blog_id = :refer_id'
                 .     ' AND '
                 .     'trash = :trash';                 
        $stmt3 = $this->db->prepare($repsSql);
        $stmt3->execute(
                    array(
                        ':refer_id' => $item['refer_id'],
                        ':trash'    => 0
                    )
                );
        $rep = $stmt3->fetch();
        do {
            $item['reps'][] = $rep;
        } while ($rep = $stmt3->fetch());
  
        return $item;
    }

    /**
     * Get Archived item list
     *
     * @uses   getModuleLanguage
     * @uses   setEntryItems
     * @uses   Loggix_View
     * @return string
     */
    public function getArchives($res)
    {
         global $sessionState, $module, $pathToIndex, $lang;
        
        $this->getModuleLanguage('trackback');

        $items = array();
        try {
            while ($item = $res->fetch()) {
                $setItem = $this->setEntryItem($item);                
                if (!in_array($item['blog_id'], $this->getDraftLogIdArray())) {
                    $items[] = $setItem;
                }
            }
            if (($_SERVER['QUERY_STRING']) && (empty($items))) {
                $xhtml = '/theme/errors/file-not-found.html';
            } elseif (!empty($items)) {
                $xhtml = self::TRACKBACK_THEME_PATH . 'archives.html';
            } else {
                $xhtml = self::TRACKBACK_THEME_PATH . 'default.html';
            }
        } catch (Exception $e) {
            $xhtml = '/theme/errors/file-not-found.html';  
        }

        $contents = new Loggix_View($pathToIndex . $xhtml);
        $contents->assign('session_state', $sessionState);
        $contents->assign('items', $items);
        $contents->assign('lang', $lang);
        $contents->assign('module', $module);                           
        
        return $contents->render();
    }
    
    /**
     * Get draft log id array
     *
     * @return array
     */
    public function getDraftLogIdArray()
    {
        $draftLogIdArray = array(0);
        $checkDraftSql = 'SELECT id FROM ' . LOG_TABLE . ' WHERE draft = 1';
        $checkDraftRes = $this->db->query($checkDraftSql);
        while ($draftRow = $checkDraftRes->fetch()) {
             $draftLogIdArray[] = $draftRow['id'];
        }
        return $draftLogIdArray;
    }

    /**
     * Generate Recent Trackbacks List
     *
     * @return string
     */
    public function getRecentTrackbacks()
    {
        global $pathToIndex, $lang;
        
        $this->getModuleLanguage('trackback');
        
        $trackbackList = '';
        $sql = 'SELECT '
             .    'id, blog_id, title, name, date '
             . 'FROM ' 
             .     TRACKBACK_TABLE . ' '
             . 'ORDER BY '
             .     'date DESC '
             . 'LIMIT '
             .     self::$config['recent_trackback_max'];
        $stmt = $this->db->prepare($sql);
        $res  = $stmt->execute();
        if ($res) {
            while ($row = $stmt->fetch()) {
                if (!in_array($row['blog_id'], $this->getDraftLogIdArray())) {
                    $trackbackTitle = htmlspecialchars(stripslashes($row['title']));
                    $trackbackList .= '<li>'
                                    . '<a href="' 
                                    . $pathToIndex . '/index.php?id=' . $row['blog_id'] . '#tb' . $row['id']
                                    . '" title="&quot;' . $trackbackTitle . '&quot;">'
                                    . 'From ' . htmlspecialchars(stripslashes($row['name'])) 
                                    . '<br />' . date('y/m/d H:i', strtotime($row['date'])) . '</a>'
                                    . "</li>\n";
                }
            }
        }
        
        if ($trackbackList == '') { 
            $trackbackList = '<li>' . $lang['trackback']['default_message'] . '</li>'; 
        }
        
        return $trackbackList;
    }
}


// Create a recent trackbacks list object
$recentTrackbacks = new Loggix_Module_Trackback;
$module['LM']['trackback']['recent'] = $recentTrackbacks->getRecentTrackbacks();

