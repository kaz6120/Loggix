<?php
/**
 * @since   5.5.15
 * @version 9.3.24
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

if ($sessionState == 'on') {
   
    if (isset($_POST['title'], $_POST['comment'], $_POST['draft'])) {

        $app->insertTagSafe();
        
        $title       = $_POST['title'];
        $comment     = $_POST['comment'];
        $draftStatus = $_POST['draft'];
        $excerpt     = (isset($_POST['excerpt']))    ? $_POST['excerpt']    : '';
        $parent_key  = (isset($_POST['parent_key'])) ? $_POST['parent_key'] : '';
                
        if (isset($_POST['y'], $_POST['m'], $_POST['d'], 
                  $_POST['h'], $_POST['i'], $_POST['s'])) {
            $Y = $_POST['y'];
            $m = $_POST['m'];
            $d = $_POST['d'];
            $H = $_POST['h'];
            $i = $_POST['i'];
            $s = $_POST['s'];
            $postDate = $Y . '-' . $m . '-' . $d . ' ' . $H . ':' . $i . ':' . $s;
            $modDate  = $Y . '-' . $m . '-' . $d . ' ' . $H . ':' . $i . ':' . $s;
        } else {
            $postDate = gmdate('Y-m-d H:i:s', time() + ($config['tz'] * 3600));
            $modDate  = gmdate('Y-m-d H:i:s', time() + ($config['tz'] * 3600));
        }
        
        // Upload Attachiments
        $app->sendAttachments();
        
        // Insert an new entry
        $app->db->beginTransaction();
        $sql = 'INSERT INTO ' 
             .     LOG_TABLE . ' '
             .         '('
             .             '`title`, '
             .             '`comment`, '
             .             '`excerpt`, '
             .             '`date`, '
             .             '`mod`, '
             .             '`draft`'
             .          ') '
             .     'VALUES '
             .         '('
             .             ':title, '
             .             ':comment, '
             .             ':excerpt, '
             .             ':date, '
             .             ':mod, '
             .             ':draft'
             .         ')';
        $sql = $app->setDelimitedIdentifier($sql);
        $stmt = $app->db->prepare($sql);
        
        $res = $stmt->execute(
                   array(
                       ':title'   => $title,
                       ':comment' => $comment,
                       ':excerpt' => $excerpt,
                       ':date'    => $postDate,
                       ':mod'     => $modDate,
                       ':draft'   => $draftStatus
                   )
               );

        // Get the new entry ID.
//        $id = $app->db->lastInsertId();
        $selectMaxSql = 'SELECT MAX(id) FROM ' . LOG_TABLE;
        $id = $app->db->query($selectMaxSql)->fetchColumn();
        
        // Plugin action
        $app->plugin->doAction('after-new-entry-posted', $id);
        
        // Add tags
        $app->addTag(LOG_TAG_MAP_TABLE, $id);
        
       // echo var_dump($res);
        
        if (!empty($res)) {
            $app->db->commit();
            if ($draftStatus == '1') {
                // If the entry is draft, move to draft page.
                header('Location: ./drafts.php');
                exit;
            } else {
                // If the entry status is public, move to published page.
                if (class_exists('Loggix_Module_Trackback')) {
                    $tb  = new Loggix_Module_Trackback;
                    $tb->sendTrackback($id);
                }
                
                header('Location: ../index.php?id=' . urlencode($id));
                exit;
            }
        } else {
            echo 'Insert Error';
        }
    } else {
        header('Location: ./write.php');
    }
} else {
    header('Location: ../index.php');
}
