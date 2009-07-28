<?php
/**
 * Delete Trackback Ping URI from SQLite Database.
 *
 * @package   Loggix_Module_Trackback
 * @since     5.6.9
 * @version   8.1.6
 */


/**
 * Include Session class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/lib/Loggix/Module/Trackback.php';

$trackback = new Loggix_Module_Trackback;
$sessionState = $trackback->getSessionState();

$trackback->insertSafe();

if ($sessionState == 'on') {
    if ((empty($_REQUEST['ping_id'])) || (empty($_REQUEST['article_id']))) {
         header('Location: ' . $pathToIndex . '/index.php');
         exit;
    } else {
        $pingId    = intval($_REQUEST['ping_id']);
        $articleId = intval($_REQUEST['article_id']);
        // Submit delete query
        $sql = 'DELETE FROM ' 
             .     TRACKBACK_TABLE . ' '
             . 'WHERE '
             .     "id = '" . $pingId . "'";
        $res = $trackback->db->query($sql);
        if ($res) {
            header('Location: ' . $pathToIndex . '/index.php?id=' . $articleId . '#trackbacks');
        }
    }
} else {
     header('Location: ' . $pathToIndex . '/index.php');
     exit;
}
