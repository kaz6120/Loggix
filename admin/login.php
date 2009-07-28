<?php
/**
 * @since   5.5.26
 * @version 8.2.25 
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();
$item         = $app->setMenuItems($sessionState);

// Logout
if (isset($_REQUEST['status']) == 'logout') {
    $sessionState = $app->getOutOfSession();
    if ($sessionState == 'off') {
        header('Location: ' . $pathToIndex . '/index.php');
    }
}

// Session
if ($sessionState == 'on') {
    header('Location: ' . $pathToIndex . '/index.php');
} else {
    // When session is off...
    $contents = new Loggix_View($pathToIndex . '/theme/admin/login.html');
    $contents->assign('lang', $lang);
    $item['contents'] = $contents->render();
}
// Pager
//--------------------------
$item['pager'] = '';
$item['result'] = '';

// Title
//--------------------------
$item['title'] = $app->setTitle(array());

$app->display($item, $sessionState);