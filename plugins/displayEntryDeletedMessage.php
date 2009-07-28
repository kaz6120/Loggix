<?php
/**
 * Display Entry Deleted Message Plugin
 *
 * エントリーを削除した後にメッセージを表示するプラグイン
 *
 * @copyright Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   New BSD Lisence
 * @since     6.9.8
 * @version   8.3.2
 */


$this->plugin->addAction('after-entry-deleted', 'displayEntryDeletedMessage');

function displayEntryDeletedMessage($id) 
{
    global $config, $id, $pathToIndex, $sessionState, $app;
    
    switch ($config['language']) {
        case 'japanese':
            $textParts = array(
                'エントリーの削除',
                'エントリー',
                'が削除されました',
                'ドラフト一覧に戻る'
            );
            break;
        default:
            $textParts = array(
                'Delete Entry',
                'Entry',
                'has been deleted.',
                'Back to draft list'
            );
            break;
    }
    
    // Additional Title
    $additionalTitle = $textParts[0];
    
    // Contents
    $content = '<h2>' . $textParts[0] . '</h2>
<p>' . $textParts[1] . $id . $textParts[2] . '</p>
<p class="ref"><a href="./drafts.php">' . $textParts[3] . '</a></p>';

    // Set Variables
    $item = array(
        'title'    => $app->setTitle($additionalTitle),
        'contents' => $content,
        'result'   => '',
        'pager'    => ''
    );

    // Display Trackback Status
    $app->display($item, $sessionState);
    exit;
}
