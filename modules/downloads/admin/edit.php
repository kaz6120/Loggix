<?php
/**
 * Display Edit Form for Downloads
 *
 * @package   LM_Downloads
 * @since     5.7.19
 * @version   10.5.20 
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

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = 'SELECT * FROM ' . DOWNLOADS_META_TABLE . " WHERE id = {$id}";
        $res = $app->db->query($sql);
        if ($item = $res->fetch()) {
        
            $item['id']        = $item['id'];
            $item['time']      = $app->setDateArray($item['file_date']);
            $item['title']     = stripslashes(htmlspecialchars($item['file_title']));
            $item['comment']   = stripslashes(htmlspecialchars($item['file_comment']));
            $item['file_type'] = stripslashes($item['file_type']);
            $item['file_name'] = stripslashes($item['file_name']);
            $item['file_size'] = $app->toMegaByte($item['file_size']);
                               
            switch ($item['draft']) {
                case '1':
                    $item['draft_status_1'] = ' checked="checked"';
                    $item['draft_status_0'] = '';
                    break;
                default:
                    $item['draft_status_1'] = '';
                    $item['draft_status_0'] = ' checked="checked"';
                    break;
            }

            // Contents
            $item['attachments'] = $app->setAttachments();
            $item['tag_cloud']   = $app->getTagCloudArray('Downloads');
            
            $smileyButton = new Loggix_View($pathToIndex . '/theme/smiley-button.html');
            $item['smiley_button'] = $smileyButton->render();
            
            $templateFile = $pathToIndex . LM_Downloads::THEME_PATH . 'admin/edit.html';
            $contentsView = new Loggix_View($templateFile);
            $contentsVars = array(
                'item' => $item,
                'lang' => $lang,
                'config'  => $config
            );
            $contentsView->assign($contentsVars);
            
            $item = array(
                'title'    => $app->setTitle(array('Downloads', $lang['edit'], $item['title'])),
                'contents' => $app->plugin->applyFilters('permalink-view', $contentsView->render()),
                'pager'    => '',
                'result'   => ''
            );
            
            $app->display($item, $sessionState);
        }

    } else {
        header('Location: ../index.php');
    }
} else {
    header('Location: ../index.php');
}
