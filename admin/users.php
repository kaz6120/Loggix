<?php
/**
 * @since   5.5.26
 * @version 9.2.6
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

if ($sessionState == 'on') {

    $app->insertSafe();
    
    // Add New User
    if ((isset($_POST['action']) == 'add') && 
        (!empty($_POST['new_user_name'])) &&
        (!empty($_POST['new_user_pass'])) &&
        (!empty($_POST['new_user_nickname']))) {
        $aNewUserName     = $_POST['new_user_name'];
        $aNewUserPass     = sha1($_POST['new_user_pass']);
        $aNewUserNickname = $_POST['new_user_nickname'];
        // Check if the posted user name exists
        $checkSql = 'SELECT '
                  .     'COUNT(user_id) '
                  . 'FROM ' 
                  .     USER_TABLE . ' '
                  . 'WHERE '
                  .     "user_name = '" . $aNewUserName . "'";
        $countRes = $app->db->query($checkSql);
        $countId  = $countRes->fetchColumn();
        // If there's no posted user name, then add new user.
        if ($countId == '0') {
            $sql = 'INSERT INTO ' 
                 .     USER_TABLE 
                 .         '('
                 .             'user_name, '
                 .             'user_pass, '
                 .             'user_nickname'
                 .         ') '
                 .     'VALUES'
                 .         "('"
                 .             $aNewUserName . "', '"
                 .             $aNewUserPass . "', '"
                 .             $aNewUserNickname
                 .         "')";
            $res = $app->db->query($sql);
            if ($res) {
                header('Location : ' . $_SERVER['PHP_SELF']);
            }
        }

    // Delete User
    } elseif ((isset($_POST['action']) == 'delete') && (isset($_POST['user_id']))) {
       $idToDelete = $_POST['user_id'];
       $sql = 'DELETE FROM ' . USER_TABLE . " WHERE user_id = '{$idToDelete}'";
       $res = $app->db->query($sql);
       if ($res) {
           header('Location : ' . $_SERVER['PHP_SELF']);
        }
    }

    // Show Tag List
    $sql = 'SELECT * FROM ' . USER_TABLE;
    $res = $app->db->query($sql);
    $tagList = '';
    if ($res) {
        $items = array();
        while ($item = $res->fetch()) {
            $sql2 = 'SELECT '
                  .     'COUNT(user_id) '
                  . 'FROM ' 
                  .     USER_TABLE . ' '
                  . 'WHERE '
                  .     "user_id = '" . $item['user_id'] . "'";
            $res2 = $app->db->query($sql2);
            $item['number_of_tag'] = $res2->fetchColumn();
            $item['disabled_status'] = ($item['user_name'] == $_SESSION['user_name']) 
                                       ? 'disabled="disabled" ' 
                                       : '';
            $items[] = $item;
        }
        $contentsView = new Loggix_View($pathToIndex . '/theme/admin/users.html');
        $contentsView->assign('items', $items);
        $contentsView->assign('lang', $lang);
        $item['contents'] = $contentsView->render();
    }
    
    // Pager
    $item['pager'] = '';
    $item['result'] = '';
    
    // Title
    $item['title'] = $app->setTitle($lang['log_tag']);
    
    $app->display($item, $sessionState);

} else {
    header('Location: ../index.php');
}
