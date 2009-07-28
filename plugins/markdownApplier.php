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
 * @version   9.5.17
 */

$this->plugin->addFilter('entry-content', 'markdownApplier', 1);

function markdownApplier($text)
{
    global $pathToIndex;

    require_once $pathToIndex . '/plugins/markdown/markdown.php';
    
    return Markdown(str_replace('\\', '&#92;', $text));
}

