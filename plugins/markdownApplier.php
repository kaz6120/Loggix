<?php
/**
 * Loggix_Plugin - markdownApplier
 *
 * This plugin applies Markdown.
 *
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     9.5.16
 * @version   10.2.20
 */

$this->plugin->addFilter('entry-content', 'markdownApplier', 1);
$this->plugin->addFilter('ex-content', 'markdownApplier', 1);
$this->plugin->addFilter('trackback-content', 'markdownApplier', 1);

function markdownApplier($text)
{
    global $pathToIndex;

    require_once $pathToIndex . '/plugins/markdown/markdown.php';
    
    return str_replace("><", ">\n<", 
               str_replace("\n\n", "\n", 
                   str_replace('<p><hr /></p>', '<hr />', 
                       Markdown(
                           str_replace('\\', '&#92;', $text)
                       )
                   )
               )
           );  
    //return str_replace('\n', '', Markdown(str_replace('\\', '&#92;', $text)));
}

