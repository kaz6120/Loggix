<?php
/**
 * Edit Comment Controller
 *
 * @package   Loggix_Module_Comment
 * @since     5.5.15
 * @version   9.5.20
 */


/**
 * Include Module class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/lib/Loggix/Module/Comment.php';

$app    = new Loggix_Module_Comment;
$config       = $app->getConfigArray();
$sessionState = $app->getSessionState();
$app->getModuleLanguage('comment');
$app->insertTagSafe();

// Display Editor 
if (isset($_GET['id'])) {
    $id  = intval($_GET['id']);
    // Main Contents
    $sql = 'SELECT '
         .     '* '
         . 'FROM ' 
         .     COMMENT_TABLE . ' '
         . 'WHERE '
         .     'id = :id';
    $stmt = $app->db->prepare($sql);
    $stmt->execute(
               array(
                   ':id' => $id
               )
           );
    
    if ($stmt) {
        while ($row = $stmt->fetch()) {
            $item['comments']['id']        = intval($row['id']);
            $item['comments']['tid']       = intval($row['tid']);
            $item['comments']['user_name'] = htmlspecialchars($row['user_name']);
            $item['comments']['user_mail'] = htmlspecialchars($row['user_mail']);
            $item['comments']['user_uri']  = htmlspecialchars($row['user_uri']);
            $item['comments']['title']     = htmlspecialchars($row['title']);
            $item['comments']['comment']   = htmlspecialchars($row['comment']);
            $item['comments']['refer_id']  = intval($row['refer_id']);
            
            // Items only admin can see...
            $item['admin']['user_ip']      = $row['user_ip'];
            $item['admin']['user_mail']    = $row['user_mail'];
            $item['admin']['user_pass']    = $row['user_pass'];
            
            // Load presentation template
            $sessionState = $app->getSessionState();
            if ($sessionState == 'on') {
                $item['admin']['password'] = $_SESSION['user_pass'];
                $editFormAdminTemplateFile = $pathToIndex . Loggix_Module_Comment::COMMENT_THEME_PATH
                                      . 'edit-form-admin.html';
                $adminEditForm = new Loggix_View($editFormAdminTemplateFile);
                $adminEditForm->assign('item', $item);
                $adminEditForm->assign('lang', $lang);
                $item['admin']['button'] = $adminEditForm->render();
            } else {
                $item['admin']['password'] = '';
                $item['admin']['button'] = '';
            }
            $item = $app->setMenuItems($sessionState);
            $smileyButton = new Loggix_View($pathToIndex . '/theme/smiley-button.html');
            $item['smiley_button'] = $smileyButton->render();
            
            $editFormViewFile = $pathToIndex 
                              . Loggix_Module_Comment::COMMENT_THEME_PATH
                              . 'edit-form.html';
            $contentsView = new Loggix_View($editFormViewFile);
            $templateVars = array(
                'item'   => $item,
                'lang'   => $lang,
                'config' => $config,
            );
            $contentsView->assign($templateVars);
            $item['contents'] = $contentsView->render();
            // Apply plugin filter
            $item['contents'] = $app->plugin->applyFilters('comment-text', $item['contents']);

        }
    } else {
        $item['contents'] = 'Error!';
    }
    $item['title'] = $app->setTitle(array($lang['edit'], 
                                          $lang['comments'] . ' No.' . $item['comments']['id']));
    // Pager
    $item['pager'] = '';
    $item['result'] = '';
    $app->display($item, $sessionState);
    
// Edit Action
} elseif (isset($_POST['user_name'], 
                $_POST['user_pass'],
                $_POST['title'], 
                $_POST['comment'], 
                $_POST['id'], 
                $_POST['refer_id'], 
                $_POST['mod_del'])
          ) {
    $userName       = $_POST['user_name'];
    $userPass       = $_POST['user_pass'];
    $title          = $_POST['title'];
    $comment        = $_POST['comment'];
    $id             = intval($_POST['id']);
    $referId        = intval($_POST['refer_id']);
    $modifyOrDelete = intval($_POST['mod_del']);
    $userUri        = (isset($_POST['user_uri'])) ? $_POST['user_uri'] : '';
    
    $item = array('user_name' => $userName,
                  'user_pass' => $userPass,
                  'title'     => $title,
                  'comment'   => $comment,
                  'id'        => $id,
                  'refer_id'  => $referId,
                  'trash'     => $modifyOrDelete,
                  'user_uri'  => $userUri                
            );
    

    $userCheckSql = 'SELECT '
                  .     'user_pass '
                  . 'FROM ' 
                  .     COMMENT_TABLE . ' '
                  . 'WHERE '
                  .     "id = '" . $item['id'] . "'";
    
    $checkRes = $app->db->query($userCheckSql);
    $checkRow = $checkRes->fetch();
    $checkRes = null; // to unlock database
    
    // Authorize 
    $authorized = (($sessionState == 'on') && (isset($_POST['admin']) == 'yes') ||
                   ($checkRow['user_pass'] == $userPass))
                ? 'yes'
                : 'no';
    $app->updateComment($item, $authorized);
    

} else {
    header('Location: ' . $pathToIndex . '/index.php');
    exit;
}
