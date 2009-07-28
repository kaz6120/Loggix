<?php
/**
 * Display New Log Entry Form
 *
 * @since   5.5.26
 * @version 9.1.31
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

if ($sessionState == 'on') {

    // Contents
    $item['time']        = $app->getDateArray();
    $item['attachments'] = $app->setAttachments();
    $item['tag_cloud']   = $app->getTagCloudArray();
    
    $smileyButton = new Loggix_View($pathToIndex . '/theme/smiley-button.html');
    $item['smiley_button'] = $smileyButton->render();
    
    $contents = new Loggix_View($pathToIndex . '/theme/admin/write.html');
    $contents->assign('item', $item);
    $contents->assign('lang', $lang);
    $contents->assign('config',  $config);
    $item['contents'] = $contents->render();
    
    // Pager
    $item['pager'] = '';
    $item['result'] = '';
    
    $item['title'] = $app->setTitle($lang['new_log']);
    
    $app->display($item, $sessionState);

} else {
    header('Location: ../index.php');
}
