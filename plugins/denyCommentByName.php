<?php
/**
 * denyComment Plugin
 *
 * "sexy_boy"という名前でコメントすると弾かれるサンプルプラグイン
 *
 * @copyright Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   New BSD Lisence
 * @since     6.9.8
 * @version   6.9.8
 */


$this->plugin->addAction('before-receive-comment', 'denyComment');

function denyComment($referId) 
{
    global $userName, $sessionState, $app;
    
    if ($userName == 'sexy_boy') {
    
        // Additional Title
        $additionalTitle = 'Not Allowed';
    
        // Contents
        $content = "<h2>Request Not Allowed</h2>\n"
                 . "<p>Ooops! You are not allowed here.</p>\n"
                 . '<p>君はコメント出来ません。残念！</p>';

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
}
