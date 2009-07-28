<?php
/**
 * Delete Downloads Entry Item
 *
 * @package   LM_Downloads
 * @since     5.7.20
 * @version   8.2.25
 */


/**
 * Include Download Module class
 */
$pathToIndex = '../../..';
require_once $pathToIndex . '/modules/downloads/lib/LM_Downloads.php';

$app = new LM_Downloads;
$sessionState = $app->getSessionState();

if ($sessionState == 'on') {
    if (!empty($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
        $sql1 = 'DELETE FROM ' . DOWNLOADS_META_TABLE  . " WHERE (draft = '1') AND (id = '{$id}')";
        $sql2 = 'DELETE FROM ' . DOWNLOADS_DATA_TABLE  . " WHERE masterid = '{$id}'";
        $sql3 = 'DELETE FROM ' . DOWNLOADS_TAG_MAP_TABLE . " WHERE log_id = '{$id}'";
        $res1 = $app->db->query($sql1) or die('Error');
        $res2 = $app->db->query($sql2) or die('Error');
        $res3 = $app->db->query($sql3) or die('Error');
        if (($res1) && ($res2) && ($res3)) {
            header('Location: ./drafts.php');
        }
    } else {
        header('Location: '.$pathToIndex.'/index.php');
    }
} else {
    header('Location: ../index.php');
}
?>
