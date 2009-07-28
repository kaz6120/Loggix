<?php
/**
 * Loggix_Plugin - backslashToYen 
 *
 * This plugin converts UTF-8 or ascii "\(backslash)" and "&#92;"
 * to Japanese half-width "Yen" mark and "&#165;".
 *
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     9.4.1
 * @version   9.5.18
 */

$this->plugin->addFilter('entry-content', 'backslashToYen', 3);
//$this->plugin->addFilter('index-view', 'backslashToYen');
$this->plugin->addFilter('downloads-index-view', 'backslashToYen');
$this->plugin->addFilter('permalink-view', 'backslashToYen');
$this->plugin->addFilter('navigation', 'backslashToYen');
$this->plugin->addFilter('comment-text', 'backslashToYen');
$this->plugin->addFilter('edit-entry', 'backslashToYen');

function backslashToYen($text) 
{
    // Convert all backslashes to Yen mark
    return str_replace('&#92;', '&#165;', str_replace('\\', '&#92;', $text));
    //return str_replace('&#92;', '&#165;', str_replace('\', '&#92;', $text));
     
    // Convert all backslashee to Yen mark except "&#92;"
    //return str_replace('\\', '&#165;', $text);
}

