<?php
/**
 * Serve Download File and Update Download Counter
 *
 * @package   Downloads
 * @since     4.6.1
 * @version   9.2.7
 */

/**
 * Include Download Module class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/modules/downloads/lib/LM_Downloads.php';

$app = new LM_Downloads;

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Pull out the meta data from binary info table
    $sql = 'SELECT '
         .     'COUNT(id) '
         . 'FROM ' 
         .     DOWNLOADS_META_TABLE . ' '
         . 'WHERE '
         .     "id = '" . $id . "'";
    
    $res = $app->db->query($sql);
    $row = $res->fetchColumn();

    if ((!$res) || ($row != 1)) {
        die('Error');
    }
    
    // Update downlaod counter
    $downloadsCountSql = 'UPDATE ' 
                       .     DOWNLOADS_META_TABLE . ' '
                       . 'SET '
                       .     "file_count = ifnull(file_count, 0) + 1 "
//                       .     "file_mod = '" . $fileMod . "' "
                       . 'WHERE '
                       .     "id = '" . $id . "'";

    if (!$downloadsCountRes = $app->db->query($downloadsCountSql)) {
        die('Error');
    }
    
    header('Location: ./bin.php?id=' . $id);
}
