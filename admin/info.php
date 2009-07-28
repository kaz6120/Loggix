<?php
/**
 * @since   5.5.26
 * @version 9.2.23
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

$item = $app->setMenuItems($sessionState);

// Session
if ($sessionState == 'on') {

    // Session info
    $sql = 'SELECT * FROM ' . SESSION_TABLE;
    $res = $app->db->query($sql);
    $i = 0;
    $sessionRows = array();
    while ($sessionRow = $res->fetch()) {
        $i++;
        $sessionRow[2] = strftime('%G/%m/%d %Z %H:%M %p', $sessionRow[2]);
        $sessionRow[1] = preg_replace('/;./', ';<br />', $sessionRow[1]);
        $sessionRow['no'] = $i;
        $sessionRows[] = $sessionRow;
    }
    
    // Database table info
    $sql2 = ($app->db->getAttribute(PDO::ATTR_DRIVER_NAME) == 'mysql')
          ? 'SHOW TABLES'
          : 'SELECT '
          .     'name '
          . 'FROM '
          .     'sqlite_master '
          . 'WHERE '
          .     "type='table' "
          . 'ORDER BY '
          .     'name;';

    $res2 = $app->db->query($sql2);
    while ($row = $res2->fetch()) {
   
        if ($row[0] == 'loggix_user') {
            $keyId = 'user_id';
        } else if ($row[0] == 'loggix_config') {
            $keyId = 'config_key';
        } else {
            $keyId = 'id';
        }
        
        $countSql = 'SELECT COUNT(' . $keyId . ')  FROM ' . $row[0];
        $countRes = $app->db->query($countSql);
        $countRow = $countRes->fetch();
        $row['count'] = $countRow[0];
        
        $rows[] = $row;
        
    }
    
    // Check SQLite    
    $item['php_version']       = phpversion();
    $item['database_type']     = $app->db->getAttribute(PDO::ATTR_DRIVER_NAME);
    $item['database_version']  = $app->db->getAttribute(PDO::ATTR_SERVER_VERSION);
    $item['database_encoding'] = 'utf-8, iso-8859';//sqlite_libencoding();

    // Load system info view file
    $contents = new Loggix_View($pathToIndex . '/theme/admin/system-info.html');
    $contents->assign('item', $item);
    $contents->assign('lang', $lang);
    $contents->assign('rows', $rows);
    $contents->assign('sess_rows', $sessionRows);
    $item['contents'] = $contents->render();
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
$item['title'] = $app->setTitle($lang['system_info']);

$app->display($item, $sessionState);

