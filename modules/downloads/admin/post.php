<?php
/**
 * Upload Downloadble File into SQLite
 *
 * @package   Downloads
 * @since     5.7.18
 * @version   9.5.20
 */

/**
 * Include Download Module class
 */
$pathToIndex = '../../..';
require_once $pathToIndex . '/modules/downloads/lib/LM_Downloads.php';

$app = new LM_Downloads;
$sessionState = $app->getSessionState();
$app->insertTagSafe();

if ($sessionState == 'on') {
    if (isset($_POST['title'], $_POST['comment'], $_POST['draft'])) {

        $draft = $_POST['draft'];

        $app->sendAttachments();
        $app->sendDownloadableFile();
        
        // Get this entry's id
        $sql = 'SELECT MAX(id) FROM ' . DOWNLOADS_META_TABLE;
        $res = $app->db->query($sql);
        $id  = $res->fetchColumn();
        $app->addTag(DOWNLOADS_TAG_MAP_TABLE, $id);
        
        if (isset($id)) {
            if ($draft == '1') {
                header('Location: ./drafts.php');
                exit;
            } else {
                header('Location: ../index.php?id=' . urlencode($id));
                exit;
            }
        }
    } else {
        header('Location: ../index.php');
        exit;
    }
} else {
    header('Location: ../../index.php');
    exit;
}
?>
