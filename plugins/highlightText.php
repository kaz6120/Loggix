<?php
/**
 * Loggix_Plugin - Syntax Highlighting
 *
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     8.3.15
 * @version   9.8.20
 */

$this->plugin->addFilter('entry-content', 'highlightText', 2);
$this->plugin->addFilter('ex-content', 'highlightText');

function highlightText($text) 
{
    global $pathToIndex;
    
    set_include_path($pathToIndex . '/lib/php');
    require_once 'Text/Highlighter.php';
    require_once 'Text/Highlighter/Renderer/Html.php'; 

    $pattern = '/'
             . '(<pre class=")' // matches[1]
             . '('              // matches[2]
             .     'abap|cpp|css|diff|dtd|html|java|javascript|mysql|'
             .     'perl|php|python|ruby|sh|sql|vbscript|xml'
             . ')'
             . '(">)'           // matches[3]
             . '([^<]+[^\/]+)'  // matches[4]
             . '(<\/pre>)'      // matches[5]
             . '/m';

    // Search matched pattern in $text
    preg_match_all($pattern, $text, $matches);

    for ($i = 0; $i < count($matches[0]); $i++) {

        // Check syntax style
        $syntaxStyle = strtoupper($matches[2][$i]);

        // Take "matches[4]", which is "([^<]+[^\/]+)" as a source code.
        // Decode HTML tags
        $sourceCode = htmlspecialchars_decode($matches[4][$i], ENT_QUOTES);
        // Remove "\n" in the first line.
        $sourceCode = preg_replace('(^\\n)', '', $sourceCode);
        $sourceCode = str_replace('&#92;', '\\', $sourceCode); // To keep compatible with Markdown
        
        // Create renderer
        $renderer = new Text_Highlighter_Renderer_Html(
                            array("numbers" => HL_NUMBERS_TABLE, 
                                  "tabsize" => 4
                                 )
                            ); 
        $hlHtml = Text_Highlighter::factory($syntaxStyle); 
        $hlHtml->setRenderer($renderer);
        // Convert text to highligten code
        $replacement = $hlHtml->highlight($sourceCode);
            
        // Matched pattern
        $matchedText = $matches[1][$i]
                     . $matches[2][$i] 
                     . $matches[3][$i] 
                     . $matches[4][$i] 
                     . $matches[5][$i];

        $text = str_replace($matchedText, $replacement, $text);
    }
    return $text;
}
