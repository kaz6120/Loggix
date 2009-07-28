<?php
/**
 * denyComment Plugin
 *
 * 特定のIPからコメントすると弾かれるプラグイン
 *
 * @copyright Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     6.9.8
 * @version   8.1.6
 */


$this->plugin->addAction('before-receive-comment', 'denyCommentByIp');

function denyCommentByIp($referId) 
{
    global $userName, $sessionState, $app;
    
    if (!isset($_SERVER['REMOTE_HOST'])) {
        $_SERVER['REMOTE_HOST'] = @gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $remoteHost = $_SERVER['REMOTE_HOST'];
    } else {
        $remoteHost = $_SERVER['REMOTE_HOST'];
    }

    if (preg_match('/(203.113.13.4)/', $remoteHost)) {
    
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

        // Display Status
        $app->display($item, $sessionState);
        exit;
    }
}

