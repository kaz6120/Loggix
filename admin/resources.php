<?php
/**
 * Resources directory index
 * 
 * @since   5.6.29
 * @version 8.2.25
 */


$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();
$app->insertSafe();

if ($sessionState == 'on') {
    if (isset($_POST['del'])) {
        $del = $_POST['del'];
        unlink($del);
    }
    
    $dir = '../data/resources/';
    
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if (($file != '.') && 
                ($file != '..') && 
                ($file != 'index.php') && 
                ($file != '.DS_Store')) {
                $filePath  = $dir . $file;
                $item['file']      = $filePath;
                $item['file_name'] = (substr($file, 0, 30)).((strlen($file) >= 30) ? '...' : '');
                $item['file_date'] = date('Y-m-d G:i:s', filectime($filePath));
                $item['file_type'] = filetype($filePath);
                $item['file_size'] = $app->toMegaByte(filesize($filePath));
                $size = getimagesize($filePath);
                $item['width_height'] = ($size != null) 
                                        ? $size[0] . '&#215;' . $size[1] 
                                        : '-';
                $items[] = $item;
            }
        }
        $contents = new Loggix_View($pathToIndex . '/theme/admin/resources.html');
        $contents->assign('items', $items);
        $contents->assign('lang', $lang);
        $item['contents'] = $contents->render();
        //closedir($dir);
    } else {
        $item['contents'] = '';
    }

    $item['pager']  = '';
    $item['result'] = '';
    $item['title']  = $app->setTitle('Resources');
    $app->display($item, $sessionState);

} else {
    header("HTTP/1.0 404 Not Found");
    header("HTTP/1.1 301 Moved Permanently");
    header('Location: ../../index.php');
    exit;
}
