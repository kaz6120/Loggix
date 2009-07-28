<?php
/**
 * @package   Downloads
 *
 * @since   5.5.26
 * @version 9.5.20
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

    // Title & Content
    $item['title']         = $app->setTitle($lang['new_dl']);
    $item['time']          = $app->getDateArray();
    $item['attachments']   = $app->setAttachments();
    $item['tag_cloud']     = $app->getTagCloudArray('Downloads');
    
    $smileyButton = new Loggix_View($pathToIndex . '/theme/smiley-button.html');
    $item['smiley_button'] = $smileyButton->render();
    
    $templateFile = $pathToIndex . LM_Downloads::THEME_PATH . 'admin/write.html';
    $contentsView = new Loggix_View($templateFile);
    $templateVars = array('item' => $item,
                          'lang' => $lang
                    );
    $contentsView->assign($templateVars);
    
    $item['contents'] = $app->plugin->applyFilters('permalink-view', $contentsView->render());
    $item['pager']    = '';
    $item['result']   = '';
    
    $app->display($item, $sessionState);

} else {
    header('Location: ../index.php');
}
