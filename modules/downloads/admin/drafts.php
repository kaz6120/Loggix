<?php
/**
 * Show Drafts of Downloads
 *
 * @package   Downloads
 * @since     5.5.16
 * @version   9.2.6
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
    if (!empty($_REQUEST['id'])) { // Publish or draft
        $id = $_REQUEST['id'];
        $app->db->query('BEGIN;');
        if (empty($_REQUEST['publish'])) { // Save as draft
            $sql = 'UPDATE ' . DOWNLOADS_META_TABLE . ' SET '
                 . "draft = '1' WHERE id = " . $id . '';
            $res = $app->db->query($sql);
            if ($res) {
                $app->db->query('COMMIT;');
                header('Location: ../index.php');
            }
        } elseif ($_REQUEST['publish'] == '1') {
            $sql = 'UPDATE ' . DOWNLOADS_META_TABLE . ' SET '
                 . "draft = '0' WHERE id = " . $id . '';
            $res = $app->db->query($sql);
            if ($res) {
                $app->db->query('COMMIT;');
                header('Location: ./drafts.php');
            }
        }
    } else { // Without "id", show drafts.

        if (empty($_GET['k']))  { $_GET['k']  = '';  } // Keyword
        if (empty($_GET['p']))  { $_GET['p']  = '0'; } // Start page of the result
        if (empty($_GET['pn'])) { $_GET['pn'] = '1'; } // Page number
        if (empty($_GET['ex'])) { $_GET['ex'] = '0'; } // Expand the pager link

        if (isset($_GET['k']))  { $keyword = $_GET['k'];  }
        if (isset($_GET['p']))  { $page    = $_GET['p'];  }
        if (isset($_GET['d']))  { $date    = $_GET['d'];  }
        if (isset($_GET['ex'])) { $expand  = $_GET['ex']; }
        
        $sql = 'SELECT '
             .     '* '
             . 'FROM ' 
             .     DOWNLOADS_META_TABLE . ' '
             . 'WHERE '
             .     "draft = '1' "
             . 'ORDER BY '
             .     'file_date DESC '
             . 'LIMIT '
             .     $page . ', ' . $config['page_max'];
        $res = $app->db->query($sql);
        if ($res) {
            $items = array();
            while ($item = $res->fetch()) {
                $item = $app->setEntryItems($item);
                $items[] = $item;
            }
            $templateFile = $pathToIndex . LM_Downloads::THEME_PATH
                          . 'admin/drafts.html';
            $contentsView = new Loggix_View($templateFile);
            $contentsVars = array(
               'items' => $items,
               'lang'  => $lang
            );
            $contentsView->assign($contentsVars);
            $item['contents'] = $contentsView->render();
        } else { $item['contents'] = ''; }
    
        // Pager
        $countSql = 'SELECT COUNT(id) FROM ' . DOWNLOADS_META_TABLE . " WHERE draft = '1'";
        $countRes = $app->db->query($countSql);
        $hits = $countRes->fetchColumn();
        $item['pager']  = $app->getPager('', 'all', $hits, '', '1');
        /*
        $totalItemsCount = $countRes->fetchColumn();
        $item['pager'] = $app->getPager($totalItemsCount, 'all', '', '1');
        */
        $item['result'] = '';
        $item['title']  = $app->setTitle($lang['dl_draft']);

        $app->display($item, $sessionState);
    }
} else {
    header('Location: ../index.php');
}
