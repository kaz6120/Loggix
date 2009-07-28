<?php
/**
 * Logix_Module_Comment - Comment Module for Loggix
 *
 * PHP version 5
 *
 * @package   Loggix_Module
 * @copyright Copyright (C) Loggix Project
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.31
 * @version   9.5.19
 */

/**
 * Include Module class
 */
require_once $pathToIndex . '/lib/Loggix/Module.php';

/**
 * @package   Loggix_Module
 */
class Loggix_Module_Comment extends Loggix_Module
{
    const COMMENT_THEME_PATH = '/modules/comment/theme/';

    /**
     * Retrieve Comments from database
     *
     * @param  $item
     * @return boolean
     */
    public function getComments($item)
    {
        $sql = 'SELECT '
             .     'id, title, comment, user_name, user_pass, '
             .     'user_uri, date, user_ip, refer_id, trash '
             . 'FROM ' 
             .     COMMENT_TABLE . ' '
             . 'WHERE '
             .     '(refer_id = :refer_id) AND (trash = :trash) '
             . 'ORDER BY '
             .     'date ASC';
             
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(
                   array(
                       ':refer_id' => $item['id'],
                       ':trash'   => 0
                   )
               );
    }

    /**
     * Number of comments
     *
     * @param  $item
     * @return string
     */
    public function getNumberOfComments($item)
    {
    
        $sql = 'SELECT '
             .     'COUNT(c.id) '
             . 'FROM ' 
             .     COMMENT_TABLE . ' AS c '
             . 'WHERE '
             .     '(c.refer_id = :refer_id) AND (c.trash = :trash) '
             . 'ORDER BY '
             .     'c.date ASC';
        
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
     * Update Comment
     *
     * @param $item
     * @param $authorized
     */
    public function updateComment($item, $authorized) 
    {
        global $pathToIndex;
        
        $currentTime = gmdate('YmdHis', time() + (self::$config['tz'] * 3600));
                   
        if ($authorized == 'yes') {
            if ($item['trash'] == '1') {
                $this->db->beginTransaction();
                $sql = 'DELETE FROM ' 
                     .     COMMENT_TABLE . ' '
                     . 'WHERE '
                     .     'id = :id;';
                $stmt = $this->db->prepare($sql);
                $stmt->execute(array(':id' => $item['id']));
                $this->db->commit();
            } else {                            
                $updateSql = 'UPDATE ' 
                           .     COMMENT_TABLE . ' '
                           . 'SET '
                           .     '`title` = :title, '
                           .     '`comment` = :comment, '
                           .     '`user_name` = :user_name, '
                           .     '`user_uri` = :user_uri, '
                           .     '`mod` = :mod, '
                           .     '`trash` = :trash '
                           . 'WHERE '
                           .     'id = :id;';
                $updateSql = $this->setDelimitedIdentifier($updateSql);
                $this->db->beginTransaction();
                $stmt = $this->db->prepare($updateSql);
                $stmt->execute(
                           array(
                               ':title'     => $item['title'],
                               ':comment'   => $item['comment'],
                               ':user_name' => $item['user_name'],
                               ':user_uri'  => $item['user_uri'],
                               ':mod'       => $currentTime,
                               ':trash'     => $item['trash'],
                               ':id'        => $item['id']
                           )
                       );
                $affectedRows = $this->db->lastInsertId();
                $this->db->commit();
            }
            
            header('Location: ' . $pathToIndex . '/index.php?id=' 
                   . urlencode($item['refer_id']) . '#c' . urlencode($item['id']));
            exit;
        } else {
            header('Location: ./edit.php?id=' . urlencode($item['id']));
            exit;
        }
    }

    /**
     * Get Status of Comments
     *
     * @param  $item
     * @uses   getNumberOfComments
     * @return array $item['comments']
     */
    public function getCommentStatus($item)
    {
        global $pathToIndex, $lang;
        $item['comments'] = array();
        $rows  = $this->getNumberOfComments($item);
        if ($rows == '0') {
            $item['comments']['status'] = '';
            $item['comments']['num'] = '0';
        } else {
            $item['comments']['status'] = '" class="status-on';
            $item['comments']['num'] = $rows;
        }
        return $item['comments'];
    }

    /**
     * Get Admin's Nickname List Array
     *
     * @return array $adminNicknameListArray
     */
    public function getAdminNicknameListArray() 
    {
        $adminNicknameCheckSql  = 'SELECT user_nickname FROM ' . USER_TABLE;
        foreach ($this->db->query($adminNicknameCheckSql) as $row) {
            $adminNicknameListArray[] = htmlspecialchars($row['user_nickname']);
        }
        return $adminNicknameListArray;
    }

    /**
     * Get Comment List
     */
    public function getCommentList($item)
    {
        global $pathToIndex, $lang;

        $this->getModuleLanguage('comment');
        
        if (!empty($_GET['id'])) {
            $rows = $this->getNumberOfComments($item);
            $items = array();
            if ($rows > 0){
                $item['comments']['list'] = '';
                $sql = 'SELECT '
                     .     'id, title, comment, user_name, user_pass, '
                     .     'user_uri, date, user_ip, refer_id, trash '
                     . 'FROM ' 
                     .     COMMENT_TABLE . ' '
                     . 'WHERE '
                     .     '(refer_id = :refer_id) AND (trash = :trash) '
                     . 'ORDER BY '
                     .     'date ASC';
                $stmt = $this->db->prepare($sql);
                $stmt->execute(
                           array(
                               ':refer_id' => $item['id'],
                               ':trash' => 0
                           )
                       );

                while ($row = $stmt->fetch()) {
                    $item['comments']['id'] = intval($row['id']);
                    $item['comments']['title'] = htmlspecialchars($row['title']);
                    $item['comments']['comment'] = $this->plugin->applyFilters('comment-text', 
                                                   $this->setSmiley(
                                                   $this->setBBCode($row['comment']
                                                   )));
                    $item['comments']['date'] = date(self::$config['post_date_format'], strtotime($row['date']));
                    $item['comments']['user_name'] =  (!empty($row['user_uri']))
                                                   ? '<a href="'
                                                     . $row['user_uri']
                                                     . '" title="Go to his or her website">'
                                                     . htmlspecialchars($row['user_name'])
                                                     . '</a>'
                                                   : htmlspecialchars($row['user_name']);
                    $item['comments']['class'] = (in_array($row['user_name'], $this->getAdminNicknameListArray()))
                                               ? 'admin '
                                               : '';
                    $items[] = $item;
                }

                $commentList = new Loggix_View($pathToIndex . self::COMMENT_THEME_PATH . 'comments.html');
                $commentList->assign('items', $items);
                $commentList->assign('lang', $lang);
                $item['comments']['list'] = $commentList->render();
                $item['comments']['parent_key'] = '0';
            } else {
                $noComments = new Loggix_View($pathToIndex . self::COMMENT_THEME_PATH . 'no-comments.html');
                $noComments->assign('lang', $lang);
                $item['comments']['list'] = $noComments->render();
                $item['comments']['parent_key'] = '1';
            }
            $smileyButton = new Loggix_View($pathToIndex . '/theme/smiley-button.html');
            $item['smiley_button'] = $smileyButton->render();
            $item['title'] = $item['title'];
            
            // Set Cookies
            $item['user_cookie']['user_name'] = (isset($_COOKIE['loggix_comment_user']))
                                              ? $_COOKIE['loggix_comment_user'] 
                                              : '';
            $item['user_cookie']['user_email'] = (isset($_COOKIE['loggix_comment_email'])) 
                                              ? $_COOKIE['loggix_comment_email'] 
                                              : '';
            $item['user_cookie']['user_uri'] = (isset($_COOKIE['loggix_comment_uri'])) 
                                              ? $_COOKIE['loggix_comment_uri'] 
                                              : '';
            $item['user_cookie']['status'] = (isset($_COOKIE['loggix_comment_user'])) 
                                              ? ' checked="checked"' 
                                              : '';
            // Get View
            $sql = 'SELECT '
                 .     'allow_comments '
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
                $item['cd'] = $pathToIndex;
                $item['language'] = self::$config['language'];
                $comments = new Loggix_View($pathToIndex . self::COMMENT_THEME_PATH . 'post-form.html');
                $comments->assign('item', $item);
                $comments->assign('lang', $lang);
            } else {
                $comments = new Loggix_View($pathToIndex . self::COMMENT_THEME_PATH . 'closed.html');
                $comments->assign('item', $item);
                $comments->assign('lang', $lang);
            }
            return $this->plugin->applyFilters('comment-post-form', $comments->render());
        } else {
            return '';
        }
    }


    /**
     * Set Entry Items
     *
     * @param  array $item
     * @uses   smiley
     * @return array $item
     */    
    public function setEntryItem($item)
    {
        global $pathToIndex, $lang, $module;
        
        $repSql = 'SELECT '
                .     'COUNT(id) '
                . 'FROM ' 
                .     COMMENT_TABLE . ' '
                . 'WHERE '
                .      'refer_id = :refer_id'
                .      ' AND '
                .      'trash = :trash';
        $stmt = $this->db->prepare($repSql);
        $stmt->execute(
                   array(
                       ':refer_id' => $item['refer_id'],
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
        $item['refer_title'] = $refRow[0];
        $item['refer_date']  = $refRow[1];
        
        // Get Replies
        $repsSql = 'SELECT '
                 .     '* '
                 . 'FROM ' 
                 .     COMMENT_TABLE . ' '
                 . 'WHERE '
                 .     'refer_id = :refer_id'
                 .     ' AND '
                 .     'trash = :trash';
        $stmt3 = $this->db->prepare($repsSql);
        $stmt3->execute(
                    array(
                        ':refer_id' => $item['refer_id'],
                        ':trash'    => 0
                    )
                );
        while($rep = $stmt3->fetch()) {
            $item['reps'][] = $rep;
        }

        return $item;
    }

    /**
     * Get Archived item list
     *
     * @uses   getModuleLanguage
     * @uses   setEntryItems
     * @uses   View
     * @return string
     */
    public function getArchives($getItemsSql)
    {
         global $sessionState, $module, $pathToIndex, $lang;
        
        $this->getModuleLanguage('comment');
        $getItemsSql = $this->setDelimitedIdentifier($getItemsSql); 
        $stmt = $this->db->prepare($getItemsSql);
        $stmt->execute();
        $items = array();
        try {
            while ($item = $stmt->fetch()) {
                $setItem = $this->setEntryItem($item);                
                if (!in_array($item['refer_id'], $this->getDraftLogIdArray())) {
                    $items[] = $setItem;
                }
            }
            if (($_SERVER['QUERY_STRING']) && (empty($items))) {
                $xhtml = '/theme/errors/file-not-found.html';
            } elseif (!empty($items)) {
                $xhtml = self::COMMENT_THEME_PATH . 'archives.html';
            } else {
                $xhtml = self::COMMENT_THEME_PATH . 'default.html';
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
     * Generate Recent Comments List
     *
     * @return string
     */
    public function getRecentComments()
    {
        global $pathToIndex, $lang;
        
        $this->getModuleLanguage('comment');
        
        $commentList = '';
        $sql = 'SELECT '
             .     'id, tid, title, comment, '
             .     'user_name, user_pass, user_uri, date, refer_id, trash '           
             . 'FROM ' 
             .     COMMENT_TABLE . ' '
             . 'WHERE '
             .     'trash = :trash '
             . 'ORDER BY '
             .     'date DESC '
             . 'LIMIT ' 
             .     self::$config['recent_comment_max'];
        $stmt = $this->db->prepare($sql);
        $res  = $stmt->execute(
                           array(
                               ':trash' => 0
                           )
                       );
        if ($res) {
            while ($row = $stmt->fetch()) {
                if (!in_array($row['refer_id'], $this->getDraftLogIdArray())) {
                    $userClass = (in_array($row['user_name'], $this->getAdminNicknameListArray()))
                               ? 'admin' 
                               : 'guest';
                    $targetId = $pathToIndex . '/index.php?id=' . $row['refer_id'] . '#c'.$row['id'];
                    $commentTitle = htmlspecialchars($row['title']);
                    $commentList .= '<li>'
                                   . '<a href="' . $targetId 
                                   . '" class="' . $userClass
                                   . '" title="&quot;' . $commentTitle . '&quot;">'
                                   . 'From '. htmlspecialchars($row['user_name'])
                                   . '<br />' . date('y/m/d H:i', strtotime($row['date'])) . '</a>'
                                   . "</li>\n";
                } 
            }
        }
        
        if ($commentList == '') { 
            $commentList = '<li>' . $lang['comment']['default_message'] . '</li>'; 
        }
        
        return $commentList;
    }

}


// Create a recent comments list object
$aLoggixModuleComment = new Loggix_Module_Comment;
$module['LM']['comment']['recent'] = $aLoggixModuleComment->getRecentComments();

