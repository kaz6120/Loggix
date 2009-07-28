<?php
/**
 * Block Trackback Plugin
 *
 * This plugin blocks trackback from the article witout any references nor 
 * link to this entry in its content.
 *
 * @copyright Loggix Project
 * @link      http://loggix.gotdns.org/
 * @author    nakamuxu
 * @author    kaz<kaz6120@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     6.8.11
 * @version   8.3.2
 */


$this->plugin->addAction('before-receive-trackback', "denyTrackbackWithNoReference");

function denyTrackbackWithNoReference($articleId) 
{
    global $config, $url;

    $siteUri  = 'http://' . $_SERVER['HTTP_HOST'] 
              . $config['root_dir'] . 'index.php?id=' . urlencode($articleId);

    $siteUri2 = preg_replace('/\/\/www\./', "//", $siteUri);
    $siteUri3 = preg_replace('/~/', "%7E", $siteUri);

    if ((!empty($siteUri2)) && (preg_match('/~/', $siteUri2))) {
        $siteUri4 = preg_replace('/~/', "%7E", $siteUri2);
    }
    $pingUri = trim($url, "\\\"");
    $htmlContent = @file_get_contents($pingUri); // do not report errors !
    $pos = stristr($htmlContent, $siteUri);
    if (($pos === false) && (!empty($siteUri2))) {
        $pos = stristr($htmlContent, $siteUri2);
    }
    if (($pos === false) && (!empty($siteUri3))) {
        $pos = stristr($htmlContent, $siteUri3);
    }
    if (($pos === false) && (!empty($siteUri4))) {
        $pos = stristr($htmlContent, $siteUri4);
    }
    
    if ($pos === false) {
        echo '<?xml version="1.0" encoding="UTF-8"?>
<response>
<error>1</error>
<message>Ping denied.</message>
</response>';
        exit;
    }
}

