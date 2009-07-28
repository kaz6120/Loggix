<?php
/**
 * Download File Generator
 *
 * @package   Downloads
 * @since     5.7.18
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
    
    // Pull out the meta data from binary meta info table
    $getMetaDataSql = 'SELECT '
                    .     '* '
                    . 'FROM ' 
                    .     DOWNLOADS_META_TABLE . ' '
                    . 'WHERE '
                    .     "id = '" . $id . "'";
    
    $metaRes = $app->db->query($getMetaDataSql);
    $metaRow = $metaRes->fetch();

//echo var_dump($metaRow);

    if ((!$metaRes) || $metaRow == null) {
        die('Error: Unable to retrieve meta data.');
    }

//    $fileObject = $res->fetchObject();
    
    // Pull out the data from binary table
    $sql2 = 'SELECT '
          .     'id '
          . 'FROM ' 
          .     DOWNLOADS_DATA_TABLE . ' '
          . 'WHERE '
          .     "masterid = '" . $id . "' "
          . 'ORDER BY '
          .     'id';
    if (!$res2 = $app->db->query($sql2)) {
        die('Error');
    }

    $list = array();
    
    while ($listObject = $res2->fetch()) {
        $list[] = $listObject['id'];
    }
    

    $deposition = (preg_match('/(image|text)/', $metaRow['file_type'])) 
                  ? 'inline' 
                  : 'attachment';
                  
    header('Content-Type: ' . $metaRow['file_type']);
    header('Content-Length: ' . $metaRow['file_size']);
    header('Content-Disposition: ' . $deposition . '; filename=' . $metaRow['file_name']);
    
    for ($i = 0; $i < count($list); $i++) {	
        $sql3 = 'SELECT '
              .     'file_data '
              . 'FROM ' 
              .     DOWNLOADS_DATA_TABLE . ' '
              . 'WHERE '
              .     'id=' . $list[$i];
        if (!$res3 = $app->db->query($sql3)) {
            die('Error');
        }
        $dataObject = $res3->fetchObject();
        echo $dataObject->file_data;
    }
}
