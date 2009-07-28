<?php
/**
 * Display Drafts
 *
 * @since   5.5.16
 * @version 9.2.14
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

if ($sessionState == 'on') {
    if (!empty($_REQUEST['id'])) { //  Publish or draft
        $id = intval($_REQUEST['id']);
        $app->db->beginTransaction();
        // Prepare update query
        $sql = 'UPDATE ' 
             .     LOG_TABLE . ' '
             . 'SET '
             .     'draft = :draft '
             . 'WHERE '
             .     'id = :id';
        $stmt = $app->db->prepare($sql);        
        if (empty($_REQUEST['publish'])) { // Save as draft
            $res = $stmt->execute(
                       array(
                           ':draft' => 1,
                           ':id'    => $id
                       )
                   );
            if ($res) {
                $app->db->commit();
                header('Location: ' . $pathToIndex . '/index.php');
            }
        } elseif ($_REQUEST['publish'] == '1') { // Publish
            $res = $stmt->execute(
                       array(
                           ':draft' => 0,
                           ':id'    => $id
                       )
                   );
            if ($res) {
                $app->db->commit();
                header('Location: ./drafts.php');
            }
        }
    } else { // Show drafts

        if (empty($_GET['k']))  { $_GET['k']  = '';  } // Keyword
        if (empty($_GET['p']))  { $_GET['p']  = '0'; } // Start page of the result
        if (empty($_GET['pn'])) { $_GET['pn'] = '1'; } // Page number
        if (empty($_GET['ex'])) { $_GET['ex'] = '0'; } // Expand the pager link

        if (isset($_GET['k']))  { $keyword = $_GET['k'];  }
        if (isset($_GET['p']))  { $page    = $_GET['p'];  }
        if (isset($_GET['d']))  { $date    = $_GET['d'];  }
        if (isset($_GET['ex'])) { $expand  = $_GET['ex']; }
        
        $sql  = 'SELECT '
               .    '* '
               . 'FROM ' 
               .     LOG_TABLE . ' '
               . 'WHERE '
               .     'draft = :draft '
               . 'ORDER BY '
               .     'date DESC '
               . 'LIMIT '
               .     $page . ', ' . $config['page_max'];
        $stmt = $app->db->prepare($sql);
        $res  = $stmt->execute(
                           array(
                               ':draft' => 1
                           )
                       );
        if ($res) {
            $items = array();
            while ($item = $stmt->fetch()) {
                $item = $app->setEntryItems($item);
                $items[] = $item;
            }
            $templateFile = $pathToIndex . '/theme/admin/drafts.html';
            $contentsView = new Loggix_View($templateFile);
            $contentsVars = array(
                'items' => $items,
                'lang'  => $lang
            );
            $contentsView->assign($contentsVars);
            //$item['contents'] = $contentsView->render();
            $item['contents'] = $app->plugin->applyFilters('permalink-view', $contentsView->render());
        } else { $item['contents'] = ''; }

        // Pager
        $countSql = 'SELECT COUNT(id) FROM ' . LOG_TABLE . " WHERE draft = '1'";
        $countRes = $app->db->query($countSql);
        $totalItemsCount = $countRes->fetchColumn();
        $item['pager'] = $app->getPager($totalItemsCount, 'all', '', '1');
        $item['result'] = '';

        // Title
        $item['title'] = $app->setTitle($lang['draft']);

        $app->display($item, $sessionState);

    }
} else {
    header('Location: ../index.php');
}
