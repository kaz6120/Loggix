<?php
/**
 * Loggix_Module_Rss - RSS Module for LOGGiX
 *
 * PHP version 5
 *
 * @package   Loggix_Module
 * @copyright Copyright (C) Loggix Project
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.31
 * @version   9.2.7
 */


/**
 * Include Module class
 */
require_once $pathToIndex . '/lib/Loggix/Module.php';


/**
 * @package   Loggix_Module
 */
class Loggix_Module_Rss extends Loggix_Module
{
    const RSS_THEME_PATH     = '/modules/rss/theme/';
    
    /**
     * Generate RSS URI
     */
    public function getRssUri($rssVersion)
    {
        global $module;
        
        if (stristr($this->getRequestUri(), 'modules/downloads')) {
            if (isset($_GET['id'])) {
                $id = 'id=dl_' . $_GET['id'];
            } elseif ($_SERVER['QUERY_STRING']) {
                $id = 'dl_'.$_SERVER['QUERY_STRING'];
            } else {
                $id = 'id=dl_recent';
            }
            $queryString = '?' . $id;
        } elseif (stristr($this->getRequestUri(), 'modules/comment')) {
            if (isset($_GET['id'])) {
                $id = 'id=comments_'.$_GET['id'];
            } elseif ($_SERVER['QUERY_STRING']) {
                $id = 'comments_'.$_SERVER['QUERY_STRING'];
            } else {
                $id = 'id=comments_recent';
            }
            $queryString = '?' . $id;
        } elseif (stristr($this->getRequestUri(), 'modules/trackback')) {
            if (isset($_GET['id'])) {
                $id = 'id=comments_'.$_GET['id'];
            } elseif ($_SERVER['QUERY_STRING']) {
                $id = 'trackback_'.$_SERVER['QUERY_STRING'];
            } else {
                $id = 'id=trackback_recent';
            }
            $queryString = '?' . $id;
        } elseif (stristr($this->getRequestUri(), 'var')) {
            $scriptName = preg_replace('/(.*)(\/var)(.*)/', '$2$3', $_SERVER['SCRIPT_NAME']);
            $scriptName = str_replace('.php', '', $scriptName);
            $scriptName = str_replace('/', '_', $scriptName);
            if (isset($_SERVER['QUERY_STRING'])) {
                $additionalQueryString = '&' . $_SERVER['QUERY_STRING'];
            }
            $queryString = '?mode=' . $scriptName . $additionalQueryString;
        } elseif (stristr($this->getRequestUri(), 'modules')) {
            $scriptName = preg_replace('/(.*)(\/modules)(.*)/', '$2$3', $_SERVER['SCRIPT_NAME']);
            $scriptName = str_replace('.php', '', $scriptName);
            $scriptName = str_replace('/', '_', $scriptName);
            if (isset($_SERVER['QUERY_STRING'])) {
                $additionalQueryString = '&' . $_SERVER['QUERY_STRING'];
            }
            $queryString = '?mode=' . $scriptName . $additionalQueryString;
        } elseif (!$_SERVER['QUERY_STRING']) {
            $queryString = '?id=recent';
        } else {
            $queryString = '?' . $_SERVER['QUERY_STRING'];
        }
        $rssUri = $this->getRootUri() 
                . 'modules/rss/' . $rssVersion . '.php' . $queryString;
        return htmlspecialchars($rssUri);
    }


    /**
     * Get RSS Time Format
     *
     * @param  string $version
     * @return string
     */
    public function getRssTimeFormat($version)
    {
        switch ($version) {
        case '2.0':
            return 'D, d M Y H:i:s ';
            break;
        case '1.0':
            return 'Y-m-d\TH:i:s';
            break;
        default :
            return 'Y-m-d\TH:i:s';
            break;
        }
    
    }


    /**
     * Get RSS Link Rel
     *
     * @return string
     */
    public function getRssLinkRel()
    {
        global $module;
        
        $rssVersion2 = $this->getRssUri('2.0');
        return '<link rel="alternate" type="application/rss+xml" '
             . 'title="RSS 2.0" href="' . $rssVersion2 . '" />' . "\n";
    }


    /**
     * Generate Link to RSS
     *
     * @return string
     */
    public function getRss()
    {
        global $module, $lang;
        
        $this->getModuleLanguage('rss');
        $rssVersion2 = $this->getRssUri('2.0');
        return '<p><a href="' . $rssVersion2 . '" class="rss" title="' 
             . $lang['rss_2_of_this_page'] . '">RSS</a></p>' . "\n";
    }


    /**
     * Get Enclosure Information
     *
     * @param  string $enclosureName
     * @return array  $item
     */
    public function getEnclosureInfo($enclosureName)
    {
        global $pathToIndex;
        
        $filename = 'data/resources/' . $enclosureName;
        $enclosurePath = $pathToIndex . '/' . $filename;
        
        if (file_exists($enclosurePath)) {
            $enclosureName = $enclosureName;
            $enclosureSize = filesize($enclosurePath);
            $enclosureUri  = $this->getRootUri() . $filename;
        } else {
            $enclosureName = '';
            $enclosureSize = '';
            $enclosureUri  = '';
        }
        $item = array(
            'enclosure_name' => $enclosureName,
            'enclosure_size' => $enclosureSize,
            'enclosure_uri'  => $enclosureUri
        );
        return $item;
    }


    /**
     * Get Enclosure Node
     *
     * @param  string $enclosureName
     * @return string
     */
    public function getEnclosureNode($enclosureName)
    {
        global $pathToIndex;
        $item = $this->getEnclosureInfo($enclosureName);
        if (($item['enclosure_name'] != '') &&
            ($item['enclosure_size'] != '') &&
            ($item['enclosure_uri'] != '')) {
            $enclosure = '<enclosure '
                       . 'url="' . $item['enclosure_uri'] . '" '
                       . 'length="' . $item['enclosure_size'] . '" '
                       . 'type="audio/mpeg" />'."\n";
        } else {
            $enclosure = '';
        }
        return $enclosure;
    }

    /**
     * Get Enclosure
     *
     * @param  string $comment
     * @return string $enclosure
     */
    public function getEnclosure($comment)
    {
        global $pathToIndex;
        preg_match_all('/<!-- ?PODCAST=.*\.(mp3|m4a|mov|m4v) ?-->/', $comment, $matches);
        $enclosure = '';
        for ($i = 0; $i < count($matches[0]); $i++) {
            $enclosureName = substr($matches[0][$i], 13, strpos($matches[0][$i], ' -->')-13);
            $enclosure .= $this->getEnclosureNode($enclosureName);
        }
        return $enclosure;
    }
    
    /**
     * Convert Text to Enclosure
     *
     * @param  string $comment
     * @return string $comment
     */
    public function toEnclosure($comment)
    {
        global $pathToIndex, $item;
        preg_match_all('/<!-- ?PODCAST=.*\.(mp3|m4a|mov|m4v) ?-->/', $comment, $matches);
        $enclosure = '';
        for ($i = 0; $i < count($matches[0]); $i++) {
            $enclosureName = substr($matches[0][$i], 13, strpos($matches[0][$i], ' -->')-13);
            $item = $this->getEnclosureInfo($enclosureName);
            if (($item['enclosure_name'] != '') &&
                ($item['enclosure_size'] != '') &&
                ($item['enclosure_uri'] != '')) {
                $item['enclosure_size'] = $this->toMegaByte($item['enclosure_size']);
                if (stristr($item['enclosure_name'], '.mov')) {
                    $item['enclosure_class'] = 'mov';
                    $item['enclosure_mime']  = 'video/quicktime';
                } elseif (stristr($item['enclosure_name'], '.m4')) {
                    $item['enclosure_class'] = 'm4';
                    $item['enclosure_mime']  = 'audio/mpeg';
                } elseif (stristr($item['enclosure_name'], '.wav')) {
                    $item['enclosure_class'] = 'wav';
                    $item['enclosure_mime']  = 'audio/mpeg';
                } else {
                    $item['enclosure_class'] = 'mp3';
                    $item['enclosure_mime']  = 'audio/mpeg';
                }
                $templateFile = $pathToIndex . self::RSS_THEME_PATH . 'podcast.html';
                $enclosureView = new Loggix_View($templateFile);
                $enclosureView->assign('item', $item);
                $enclosureText = $enclosureView->render();
                $comment = preg_replace('/' . $matches[0][$i] . '/', $enclosureText, $comment);
            }
        }
        return $comment;
    }
}


$rss = new Loggix_Module_Rss;
$module['LM']['RSS']['linkrel'] = $rss->getRssLinkRel();
$module['LM']['RSS']['link']    = $rss->getRss();

