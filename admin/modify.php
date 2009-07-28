<?php
/**
 * @since   5.5.27
 * @version 9.5.17
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

if ($sessionState == 'on') {
    
    $app->insertTagSafe();
    
    if (isset($_POST['title'], $_POST['comment'], $_POST['id'])) {
        $id            = $_POST['id'];
        $title         = $_POST['title'];
        $comment       = $_POST['comment'];
        $textMode      = (isset($_POST['text_mode'])) ? $_POST['text_mode'] : '';
        $excerpt       = (isset($_POST['excerpt']))   ? $_POST['excerpt']   : '';
        $allowComments = (!isset($_POST['allow_comments'])) ? '0' : '1';
        $allowPings    = (!isset($_POST['allow_pings']))    ? '0' : '1';
        $draft         = $_POST['draft'];
        
        // Upload Attachments
        $app->sendAttachments();
        
        
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
        
        $app->db->beginTransaction();
        
        $sql = 'UPDATE ' 
             .     LOG_TABLE . ' '
             . 'SET '
             .     '`title` = :title, '
             .     '`comment` = :comment, '
             .     '`text_mode` = :text_mode, '
             .     '`excerpt` = :excerpt, '
             .     '`date` = :date, '
             .     '`mod` = :mod, '
             .     '`draft` = :draft, '
             .     '`allow_comments` = :allow_comments, '
             .     '`allow_pings` = :allow_pings '
             . 'WHERE '
             .     'id = :id';
        $sql = $app->setDelimitedIdentifier($sql);
        $stmt = $app->db->prepare($sql);
        $res = $stmt->execute(
                   array(
                       ':title'          => $title,
                       ':comment'        => $comment,
                       ':text_mode'      => $textMode,
                       ':excerpt'        => $excerpt,
                       ':date'           => $postDate,
                       ':mod'            => $modDate,
                       ':draft'          => $draft,
                       ':allow_comments' => $allowComments,
                       ':allow_pings'    => $allowPings,
                       ':id'             => $id
                   )
               );
        
        // Add Tag
        $app->addTag(LOG_TAG_MAP_TABLE, $id);

        if ($res) {
            $app->db->commit();
            $sql  = 'SELECT '
                  .     'draft '
                  . 'FROM ' 
                  .     LOG_TABLE . ' '
                  . 'WHERE '
                  .     'id = :id';
            $stmt = $app->db->prepare($sql);
            $stmt->execute(
                       array(
                           'id' => $id
                       )  
                   );
            $item = $stmt->fetch();
            if ($item['draft'] == '1') {
                header('Location: ./drafts.php');
            } else {
                // Send Trackback
                if (($draft == '0') && (class_exists('Loggix_Module_Trackback'))) {
                    $aTrackback = new Loggix_Module_Trackback;
                    $pingStatus = $aTrackback->sendTrackback($id);
                }
                header('Location: ' . $pathToIndex . '/index.php?id=' . $id);
            }
        }
    } else {
        header('Location: ./write.php');
    }
} else {
    header('Location: ../index.php');
}
