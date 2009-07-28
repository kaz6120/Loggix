<?php
/**
 * @since   5.5.16
 * @version 9.5.18
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';
require_once $pathToIndex . '/lib/Loggix/View.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

if ($sessionState == 'on') {

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = 'SELECT '
             .     '* '
             . 'FROM ' 
             .     LOG_TABLE . ' '
             . 'WHERE '
             .     'id = :id';
        $stmt = $app->db->prepare($sql);
        $stmt->execute(array(':id' => $id));       
        if ($item = $stmt->fetch()) {
            $item['id']             = $item['id'];
            $item['time']           = $app->setDateArray($item['date']);
            $item['title']          = htmlspecialchars($item['title']);
            $item['comment']        = htmlspecialchars($item['comment']);
            $item['excerpt']        = htmlspecialchars($item['excerpt']);
            $item['allow_comments'] = $item['allow_comments'];
            $item['allow_pings']    = $item['allow_pings'];
                        
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
            
            switch ($item['allow_comments']) {
                case '0':
                    $item['allow_comments_status'] = '';
                    break;
                default:
                    $item['allow_comments_status'] = ' checked="checked"';
                    break;
            }

            switch ($item['allow_pings']) {
                case '0':
                    $item['allow_pings_status'] = '';
                    break;
                default:
                    $item['allow_pings_status'] = ' checked="checked"';
                    break;
            }

            // Contents
            $item['attachments'] = $app->setAttachments();
            $item['tag_cloud']   = $app->getTagCloudArray();

            $smileyButton = new Loggix_View($pathToIndex . '/theme/smiley-button.html');
            $smileyButton->assign('relativePath', $pathToIndex);
            $item['smiley_button'] = $smileyButton->render();

            $contents = new Loggix_View($pathToIndex . '/theme/admin/edit.html');
            $contents->assign('item', $item);
            $contents->assign('lang', $lang);
            $contents->assign('config',  $config);
            $item['contents'] = $app->plugin->applyFilters('edit-entry', $contents->render());
            
            // Pager
            $item['pager']  = '';
            $item['result'] = '';
            
            $item['title'] = $app->setTitle(array($lang['edit'], $item['title']));
            
            $app->display($item, $sessionState);
        }
    } else {
        header('Location: ../index.php');
    }
} else {
    header('Location: ../index.php');
}
