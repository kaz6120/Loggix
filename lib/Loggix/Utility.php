<?php
/**
 * Loggix_Utility
 *
 * PHP version 5
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   11.5.28
 */

/**
 * Loggix Utility Class
 *
 * Example 1:
 * <code>
 * Loggix_Utility::getDataLastModified();
 * </code>
 *
 * Example 2: 
 * <code>
 * $app = new Loggix_Application;
 * $app->getDataLastModified();
 * </code>
 *
 */
class Loggix_Utility
{

    /**
     * Set delemited identifier in SQL
     *
     * @return string $sql
     */
    public function setDelimitedIdentifier($sql)
    {
        return $sql = (Loggix_Core::LOGGIX_DATABASE_TYPE == 'SQLite')
                    ? str_replace('`', '', $sql)
                    : $sql;
    }
    
    /**
     * Insert Strings into Database.
     *
     * This function escapes strings and strip XHTML tags
     * before inserting strings into SQL query.
     *
     * @see     insertTagSafe
     * @since   5.5.16
     * @version 9.5.20
     */
    public function insertSafe()
    {
        if (get_magic_quotes_gpc()) { 
            $_REQUEST = array_map('stripslashes', $_REQUEST);
            $_GET     = array_map('stripslashes', $_GET); 
            $_POST    = array_map('stripslashes', $_POST); 
            $_COOKIE  = array_map('stripslashes', $_COOKIE);
        }

        $_GET[]  = array_map('strip_tags', 
                   array_map('trim', $_GET));
        $_POST[] = array_map('strip_tags', 
                   array_map('trim', $_POST));

    }


    /**
     * Insert Strings into Database.
     *
     * This function escapes strings without striping XHTML tags
     * when inserting strings into SQL query.
     *
     * @see     insertSafe
     * @since   5.5.16
     * @version 9.5.20
     */
    public function insertTagSafe()
    {

        if (get_magic_quotes_gpc()) { 
            $_REQUEST = array_map('stripslashes', $_REQUEST);
            $_GET     = array_map('stripslashes', $_GET); 
            $_POST    = array_map('stripslashes', $_POST); 
            $_COOKIE  = array_map('stripslashes', $_COOKIE);
        }

        $_GET[]  = array_walk_recursive($_GET, 'trim');
        $_POST[] = array_walk_recursive($_POST, 'trim');

    }


    /**
     * Get DateTime Array
     *
     * @uses   gmdate
     * @uses   time
     * @return array
     * @usee
     */    
    public function getDateArray()
    {
        global $config;
        return array(
            'Y' => gmdate('Y', time() + ($config['tz'] * 3600)),
            'm' => gmdate('m', time() + ($config['tz'] * 3600)),
            'd' => gmdate('d', time() + ($config['tz'] * 3600)),
            'H' => gmdate('H', time() + ($config['tz'] * 3600)),
            'i' => gmdate('i', time() + ($config['tz'] * 3600)),
            's' => gmdate('s', time() + ($config['tz'] * 3600))
        );
    }

    /**
     * Set DateTime Array
     *
     * @param  string $str
     * @uses   gmdate
     * @uses   time
     * @return array
     */    
    public function setDateArray($str)
    {
        global $config;
        return array(
            'Y' => gmdate('Y', strtotime($str) + ($config['tz'] * 3600)),
            'm' => gmdate('m', strtotime($str) + ($config['tz'] * 3600)),
            'd' => gmdate('d', strtotime($str) + ($config['tz'] * 3600)),
            'H' => gmdate('H', strtotime($str) + ($config['tz'] * 3600)),
            'i' => gmdate('i', strtotime($str) + ($config['tz'] * 3600)),
            's' => gmdate('s', strtotime($str) + ($config['tz'] * 3600))
        );
    }



    /**
     * Loggix_Utility BBCode
     *
     * This function converts text strings with BBcode into XHML.
     * This function converts HTML entities automatically.
     *
     * @param   string $var
     * @return  string $var
     * @since   5.5.16
     * @version 9.8.19
     */
    public function setBBCode($str)
    {
        $lines = explode("\n", htmlspecialchars($str));

        $uriPatternWithTitle = '/([^=^\"]|^)(http\:[\w\.\~\-\/\?\&\+\=\:\@\%\;\#\%]+)(\()(.+?)(\))/';
        $uriPattern = '/([^=^\"]|^)(http\:[\w\.\~\-\/\?\&\+\=\:\@\%\;\#\%]+)/';
        
        for ($i = 0, $j = count($lines); $i < $j; $i++) {
            $lines[$i] = $lines[$i];
            // Convert URI
            if (preg_match($uriPatternWithTitle, $lines[$i])) {
                $referenceUriTitle = trim(preg_replace($uriPatternWithTitle, '$4', $lines[$i]));
                $referenceUri      = trim(preg_replace($uriPatternWithTitle, '$2', $lines[$i]));
                $lines[$i] = '<a href="' . $referenceUri.'" class="ex-ref">' . $referenceUriTitle . '</a>';
            } else {
                $lines[$i] = preg_replace($uriPattern, '$1<a href="$2">$2</a>', $lines[$i]);
            }
        }
        
        $str = join("\n", $lines);

        // Format line breaks and paragraphs
        $str = str_replace("\n",   "<br />",
               str_replace("\n\n", "</p><p>",  
               str_replace("\r\n", "\n",
               $str)));
        
        // Convert <pre> tags
        $str = str_replace('[/pre]', '</pre><p>', 
               str_replace('[pre]',  '</p><pre>', 
               $str));

        // Quote box coloring
        $str = str_replace('[/q3]', '</p></blockquote><p>', 
               str_replace('[q3]',  '</p><blockquote class="quote3"><p>', 
               str_replace('[/q2]', '</p></blockquote><p>', 
               str_replace('[q2]',  '</p><blockquote class="quote2"><p>', 
               str_replace('[/q1]', '</p></blockquote><p>', 
               str_replace('[q1]',  '</p><blockquote class="quote1"><p>', 
               str_replace('[/q]', '</p></blockquote><p>', 
               str_replace('[q]',  '</p><blockquote class="quote1"><p>', 
               $str))))))));

        // Remove bad patterns
        $str = str_replace('</p></p>',     '</p>', 
               str_replace('<p><p>',       '<p>', 
               str_replace('</pre></p>',   '</pre>', 
               str_replace('<p><pre>',     '<pre>', 
               str_replace('<br /></pre>', '</pre>', 
               str_replace('<pre><br />',  '<pre>', 
               str_replace('<br /></p>',   '</p>', 
               str_replace('</p><br />',   '</p>', 
               str_replace('<p><br />',    '<p>', 
               $str)))))))));
        
        // Put all together into the paragraph
        $str = '<p>' . $str . "</p>\n";
        
        // Remove bad patterns
        $str = str_replace('<br /><br />', '', 
               str_replace('<pre></pre>',  '', 
               str_replace('<p></p>',      '', 
               $str)));

        // Clean up the XHTML code
        $str = str_replace("><", ">\n<", $str);

        return $str;
    }


    /**
     * Get Time Zone
     *
     * This function returns time zone based on user config setting.
     *
     * @return  string
     * @since   5.5.16 
     * @version 6.2.22
     */
    public function getTimeZone() 
    {
        global $config;

        switch($config['tz']) {
            case '-12':
                return '-1200';
                break;
            case '-11':
                return '-1100';
                break;
            case '-10':
                return '-1000';
                break;
            case '-9':
                return '-0900';
                break;
            case '-8':
                return '-0800';
                break;
            case '-7':
                return '-0700';
                break;
            case '-6':
                return '-0600';
                break;
            case '-5':
                return '-0500';
                break;
            case '-4':
                return '-0400';
                break;
            case '-3':
                return '-0300';
                break;
            case '-2':
                return '-0200';
                break;
            case '-1':
                return '+0100';
                break;    
            case '12':
                return '+1200';
                break;
            case '11':
                return '+1100';
                break;
            case '10':
                return '+1000';
                break;
            case '9':
                return '+0900';
                break;
            case '8':
                return '+0800';
                break;
            case '7':
                return '+0700';
                break;
            case '6':
                return '+0600';
                break;
            case '5':
                return '+0500';
                break;
            case '4':
                return '+0400';
                break;
            case '3':
                return '+0300';
                break;
            case '2':
                return '+0200';
                break;
            case '1':
                return '+0100';
                break;
            default:
                return '+0000';
                break;
        }
    }


    /**
     * Get the last modified time of the files in "/data/" directory.
     *
     * Example:
     * <code>
     * getDataLastModified('F d Y H:i:s')
     * getDataLastModified('RFC822')
     * getDataLastModified('UTC')
     * </code>
     *
     * @param   string $timeFormat;
     * @uses    getTimeZone
     * @return  string 
     * @since   5.10.18
     * @version 6.2.18
     */
    public function getDataLastModified($timeFormat = 'Y-m-d H:i:s')
    {
        global $config;
        $expanderDataDir = './data/';
        if (file_exists($expanderDataDir)) {
            if ($dh = @opendir($expanderDataDir)) {
                $modifiedTimes = array();
                while ($file = readdir($dh)) {
                    if ($file != '.' && $file != '..' && 
                        preg_match('/^.+\.(inc|php|html|txt|text)$/', $file)) {
                        $modifiedTimes[] .= filemtime($expanderDataDir . $file);
                    }
                }
                rsort($modifiedTimes);
            }
            if ($timeFormat == 'RFC822') {
                $timeFormat = 'D, d M Y H:i:s '.$this->getTimeZone();
                $local  = '';
            } else if ($timeFormat == 'UTC') {
                $timeFormat = 'D, d M Y H:i:s O';
                $local  = '';
            } else {
                $timeFormat = $timeFormat;
                $local  = $config['tz'] * 3600;
            }
        }
        return ((!empty($timeFormat)) && (isset($modifiedTimes[0]))) 
               ? gmdate($timeFormat, $modifiedTimes[0] + $local) 
               : '';
    }



    /**
     * Convert Smiley
     *
     * This function converts text smilies into graphical ones.
     *
     * @param   string $str
     * @return  string $str
     * @see     /theme/images/smiley/
     * @since   5.5.16 
     * @version 9.5.6
     */
    public function setSmiley($str)
    {
        global $pathToIndex;
        $smileyDir = Loggix_Core::getRootUri() . 'theme/images/smiley/';
        $smilies = array(
                ':-)'  => 'smile.png',
                ';-)'  => 'wink.png',
                ':-D'  => 'laugh.png',
                ':-!'  => 'foot_in_mouth.png',
                ':-('  => 'frown.png',
                '=-o'  => 'gasp.png',
                '8-)'  => 'cool.png',
                ':-P'  => 'tongue.png',
                '}:-[' => 'angry.png',
                ':-|'  => 'ambivalent.png',
                ':-/'  => 'undecided.png',
                ':-d'  => 'yum.png',
                ':.('  => 'cry.png',
                ':-X'  => 'sealed.png',
                ':.)'  => 'touched.png',
                '|-|'  => 'sleep.png',
                '}}:'  => 'focus.png',
                '}-)'  => 'strong.png',
                ':-S'  => 'confused.png',
                ':-}'  => 'embarassed.png',
        );
        $iconWidth  = 18;
        $iconHeight = 18;
        foreach ($smilies as $smileyText => $smileyImage) {
                $str = str_replace(
                        $smileyText, 
                        '<img' . 
                        ' src="'    . $smileyDir  . $smileyImage . '"' .
                        ' width="'  . $iconWidth  . '"' .
                        ' height="' . $iconHeight . '"' .
                        ' alt="'    . $smileyText . '"' .
                        ' />',
                        $str
                );
        }
        return $str;
    }


    /**
     * Convert byte value to KB or MB
     *
     * @param   int    $str
     * @return  string $str
     * @since   5.5.16
     * @version 5.11.6
     */
    public function toMegaByte($str)
    {
        $str = round(stripslashes($str) / 1024, 1); // Byte => KB 
        return  ($str > 1024) ? round($str / 1024, 1).' MB' : $str.' KB';

    }
    
    
    /**
     * Get Page Generation Time
     *
     * @since   6.2.21
     * @version 6.2.21
     */
    public function getGenerationTime($startTime) 
    {
        function getMicroseconds($microtime) 
        {
            $tmpArray = explode(' ', $microtime);
            return (((float)$tmpArray[0]) + ((float)$tmpArray[1]));
        }

        function getLapsedTime($startTime, $finishTime) 
        {
            return (getMicroseconds($finishTime) - getMicroseconds($startTime));
        }
        
        return number_format(getLapsedTime($startTime, microtime()), 4);
    }


    public function joinPath()
    {
        $numArgs = func_num_args();
        $args = func_get_args();
        $path = $args[0];
       
        if ( $numArgs > 1 ) {
           for ($i = 1; $i < $numArgs; $i++) {
               $path .= DIRECTORY_SEPARATOR . $args[$i];
           }
        }
       
        return $path;
    }
    
    
    /**
     * @param   str $separator
     * @param   str $newline
     * @param   str $data
     * @since   8.3.1
     * @version 8.3.1
     */
    public static function explodeAssoc($separator, $newline, $data)
    {
        $lines = explode($newline, $data);
        foreach($lines as  $value) {
            $pos = strpos($value, $separator);
            $key = substr($value, 0, $pos);
            $array[$key] = trim(substr($value, $pos+1, strlen($value)));
        }
        return $array;
    }

}

