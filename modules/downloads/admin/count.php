<?php
/**
 * Manage Downloads Tag
 *
 * @package  Downloads
 * @since    5.5.26
 * @version  8.3.3 
 */

/**
 * Include Download Module class
 */
$pathToIndex = '../../..';
require_once $pathToIndex . '/modules/downloads/lib/LM_Downloads.php';

$app = new LM_Downloads;
$app->getModuleLanguage('downloads');
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

if ($sessionState == 'on') {

    $items = $app->getNumberOfDownloads();
    
    $viewFile = $pathToIndex . LM_Downloads::THEME_PATH 
              . 'admin/count.html';
    $contentsView = new Loggix_View($viewFile);
    $viewVars     = array('items' => $items,
                          'lang'  => $lang
                    );
    $contentsView->assign($viewVars);
    $item['contents'] = $contentsView->render();

    // Pager
    $item['pager'] = '';
    $item['result'] = '';
    
    // Title
    $item['title'] = $app->setTitle($lang['dl_tag']);

    $app->display($item, $sessionState);

} else {
    header('Location: ../index.php');
}
