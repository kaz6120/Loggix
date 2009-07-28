<?php
/**
 * Loggix_Plugin - hilightKeywords
 *
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     6.9.1
 * @version   6.9.17
 */

$this->plugin->addFilter('entry-content', 'highlightKeywords');

function highlightKeywords($text)
{
    // Tag Settings
    $tagName  = 'em';
    $tagClass = ' class="highlight"';

    if (isset($_GET['k'])) {
        $keyword = $_GET['k'];
        $keys = (!strrchr($keyword, ' ')) 
              ? explode(',', $keyword) 
              : explode(' ', $keyword);
        for ($i = 0; $i < sizeof($keys); $i++) {
            if ($keys[$i] != '') {
                $key  = stripslashes($keys[$i]);
                $text = preg_replace(
                            '/(' . $key . ')/i', 
                            '<' . $tagName . $tagClass . '>$1</' . $tagName . '>', 
                            $text
                        );
                $pattern = '/'
                         . '(<[^<>]*)'
                         . '(<' . $tagName . $tagClass . '>)'
                         . '(' . $key . ')'
                         . '(<\/' . $tagName . '>)'
                         . '/i';
                while (preg_match($pattern, $text)) {
                    $text = preg_replace($pattern, '$1$3', $text);
                }
            }
        }
        return $text;
    }
}
