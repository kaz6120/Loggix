<?php
/**
 * Modify Downloads Article 
 *
 * @package   LM_Downloads
 *
 * @since     5.5.27
 * @version   9.3.17
 */

/**
 * Include Download Module class
 */
$pathToIndex = '../../..';
require_once $pathToIndex . '/modules/downloads/lib/LM_Downloads.php';

$app = new LM_Downloads;
$sessionState = $app->getSessionState();

if ($sessionState == 'on') {
    
    $app->insertTagSafe();
    
    if ((!empty($_POST['title'])) && (!empty($_POST['comment'])) && (!empty($_POST['id']))) {

        $id       = $_POST['id'];
        $title    = $_POST['title'];
        $comment  = $_POST['comment'];
        $draft    = $_POST['draft'];

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
            $postDate = $Y.'-'.$m.'-'.$d.' '.$H.':'.$i.':'.$s;
            $modDate  = $Y.'-'.$m.'-'.$d.' '.$H.':'.$i.':'.$s;
        } else {
            $postDate = gmdate('Y-m-d H:i:s', time() + ($cfg['tz'] * 3600));
            $modDate  = gmdate('Y-m-d H:i:s', time() + ($cfg['tz'] * 3600));
        }

        $app->db->beginTransaction();
        
        $sql = 'UPDATE ' 
             .     DOWNLOADS_META_TABLE . ' '
             . 'SET '
             .     '`file_title` = :title, '
             .     '`file_comment` = :comment, '
             .     '`file_date` = :date, '
             .     '`file_mod` = :mod, '
             .     '`draft` = :draft '
             . 'WHERE '
             .     'id = :id';
        $sql = $app->setDelimitedIdentifier($sql);
        $stmt = $app->db->prepare($sql);
        $res = $stmt->execute(
                   array(
                       ':title'          => $title,
                       ':comment'        => $comment,
                       ':date'           => $postDate,
                       ':mod'            => $modDate,
                       ':draft'          => $draft,
                       ':id'             => $id
                   )
               );        
        $app->addTag(DOWNLOADS_TAG_MAP_TABLE, $id);

        if ($res) {
            $app->sendDownloadableFile();
            $app->db->commit();
            $sql  = 'SELECT '
                  .     'draft '
                  . 'FROM ' 
                  .     DOWNLOADS_META_TABLE . ' '
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
                header('Location: ../index.php?id='.$id);
            }
        }
    } else {
        header('Location: ./write.php');
    }
} else {
    header('Location: ' . $pathToIndex . '/index.php');
}
?>
