<?php
/**
 * Trackback Status Plugin
 *
 * This plugin shows status if trackback sending is succeeded or not
 * after sending trackback. This plugin works only when trackback is sent.
 *
 * @copyright Loggix Project
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License 
 * @since     6.8.13
 * @version   8.3.3
 */


$this->plugin->addAction('after-trackback-sent', 'showTrackbackStatus');

function showTrackbackStatus($pingStatus) 
{
    global $config, $id, $pathToIndex, $sessionState, $app;

    $pingUri               = $pingStatus[0];
    $trackbackPingResponse = $pingStatus[1];
    
    switch ($config['language']) {
        case 'japanese':
            $textParts = array(
                'トラックバック',
                'ステータス',
                '送信先',
                '送信したトラックバックが受信されました。',
                '送信したトラックバックが拒否されました。',
            );
            break;
        default:
            $textParts = array(
                'Trackback',
                'Status',
                'Sent to',
                'Trackback has been received.',
                'Trackback has been refused.'
            );
            break;
    }
    
     // Title
    $additionalTitle = 'Article ' . $id . ' : ' . $textParts[0] . $textParts[1];
    
    // Contents
    $message    = ($trackbackPingResponse == 0) ? $textParts[3] : $textParts[4];
    $valueClass = ($trackbackPingResponse == 0) ? '' : ' important';

    $content = '<h2>Article ' . $id . ' : ' . $textParts[0] . $textParts[1] . '</h2>
<table summary="Trackback Status" class="horizontal-graph">
<tr>
<th abbr="Trackback Status Key">' . $textParts[2] . '</th>
<th abbr="Trackback Status Value">' . $textParts[1] . '</th>
</tr>
<tr>
<td class="key"><em>' . $pingUri . '</em></td>
<td class="value' . $valueClass . '">' . $message . '</td>
</tr>
</table>
<p class="ref"><a href="../index.php?id=' . $id . '">OK</a></p>
';

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

