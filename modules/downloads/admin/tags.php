<?php
/**
 * Manage Downloads Tag
 *
 * @package  Downloads
 * @since    5.5.26
 * @version  10.4.11
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

    $app->insertSafe();
    
    // Add New Tag
    if ((isset($_POST['action']) == 'add') && (isset($_POST['new_tag']))) {
        $aNewTag = $_POST['new_tag'];
        $checkSql = 'SELECT '
                  .     'COUNT(id) '
                  . 'FROM ' 
                  .     DOWNLOADS_TAG_TABLE . ' '
                  . 'WHERE '
                  .     'tag_name = :tag_name';
        $stmt = $app->db->prepare($checkSql);
        $stmt->execute(array(':tag_name' => $aNewTag));
        $countId = $stmt->fetchColumn();
        if ($countId == '0') {
            $sql = 'INSERT INTO ' 
                 .     DOWNLOADS_TAG_TABLE
                 .         '(tag_name) '
                 .     'VALUES'
                 .         '(:tag_name)';
            $stmt = $app->db->prepare($sql);
            $stmt->execute(
                       array(
                           ':tag_name' => $aNewTag
                       )
                   );
            header('Location : '.$_SERVER['PHP_SELF']);
        }
    // Save Changes
    } elseif ((isset($_POST['action']) == 'save') && 
              (isset($_POST['id'], $_POST['tag_name']))) {
        $id = intval($_POST['id']);
        $aTagName = ($_POST['tag_name'] == '') 
                         ? 'Untagged' 
                         : $_POST['tag_name'];
        $checkSql = 'SELECT '
                  .     'COUNT(id) '
                  . 'FROM ' 
                  .     DOWNLOADS_TAG_TABLE . ' '
                  . 'WHERE '
                  .     'tag_name = :tag_name';
        $stmt = $app->db->prepare($checkSql);
        $stmt->execute(
                   array(
                       ':tag_name' => $aTagName
                   )
               );
        $countId = $stmt->fetchColumn();
        
        if ($countId == '0') {
            $sql = 'UPDATE ' 
                 .     DOWNLOADS_TAG_TABLE . ' '
                 . 'SET '
                 .     'tag_name = :tag_name '
                 . 'WHERE '
                 .     'id = :id';
            $stmt2 = $app->db->prepare($sql);
            $stmt2->execute(
                       array(
                           ':tag_name' => $aTagName,
                           ':id' => $id
                       )
                   );
            header('Location : ' . $_SERVER['PHP_SELF']);
        }
    // Delete Tag
    } elseif ((isset($_POST['action']) == 'delete') && (isset($_POST['id']))) {
       $idToDelete = intval($_POST['id']);
       $sql = 'DELETE FROM ' 
            .     DOWNLOADS_TAG_TABLE . ' '
            . 'WHERE '
            .     'id = :id';
       $stmt = $app->db->prepare($sql);
       $stmt->execute(
                  array(
                      ':id' => $idToDelete
                  )
              );
       header('Location : ' . $_SERVER['PHP_SELF']);
    }

    // Show Tag List
    $sql = 'SELECT '
          .    '* '
          . 'FROM ' 
          .     DOWNLOADS_TAG_TABLE;
    $res = $app->db->query($sql);
    $tagList = '';
    if ($res) {
        $items = array();
        while ($item = $res->fetch()) {
            $sql2 = 'SELECT '
                  .     'COUNT(id) '
                  . 'FROM ' 
                  .     DOWNLOADS_TAG_MAP_TABLE . ' '
                  . 'WHERE '
                  .     "tag_id = '" . $item['id'] . "'";
            $res2 = $app->db->query($sql2);
            $item['number_of_tag'] = $res2->fetchColumn();
            $item['disabled_status'] = ($item['id'] == '1') 
                                       ? 'disabled="disabled" ' 
                                       : '';
            $item['tag_name'] = htmlspecialchars($item['tag_name']);
            $items[] = $item;
        }
        $templateFile = $pathToIndex . LM_Downloads::THEME_PATH 
                      . 'admin/tags.html';
        $contentsView = new Loggix_View($templateFile);
        $templateVars = array(
            'items' => $items,
            'lang'  => $lang
        );
        $contentsView->assign($templateVars);
        $item['contents'] = $app->plugin->applyFilters('permalink-view', $contentsView->render());
    }
    // Pager
    $item['pager'] = '';
    $item['result'] = '';
    
    // Title
    $item['title'] = $app->setTitle($lang['dl_tag']);

    $app->display($item, $sessionState);

} else {
    header('Location: ../index.php');
}
