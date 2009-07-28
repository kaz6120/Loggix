<?php
/**
 * Loggix_View_Helper - Helper class for view creation.
 *
 * PHP version 5
 *
 * @package   Loggix_View
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   9.2.17
 */


class Loggix_View_Helper {

    const LOGGIX_CSS_DIR = '/theme/css/';

    /**
     * Get & Set Cascading Style Sheet
     *
     * @return void
     */
    public function getStyle()
    {
        global $pathToIndex, $config;

        // Set CSS cookie
        if (isset($_COOKIE[$config['css_cookie_name']])) {
            $styleName = $_COOKIE[$config['css_cookie_name']];
        }
        if (isset($_POST['style_name'])) {
            $styleName = $_POST['style_name'];
        } 
        if (empty($styleName)) {
            $styleName = $config['default_style'];
        }
        setcookie($config['css_cookie_name'], $styleName, time()+$config['css_cookie_time'], '/');
        
        $stylePath = $pathToIndex . self::LOGGIX_CSS_DIR . $styleName . '/';
        
        // Generate CSS path
        // for Mozilla / Gecko Rendering Engine Browsers
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'rv:1')) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Macintosh')) {
                if (file_exists($stylePath . 'gecko.css')) {
                    $style = 'gecko';
                } elseif (file_exists($stylePath . 'mac_gecko.css')) {
                    $style = 'mac_gecko';
                } else {
                    $style = 'default';
                }
            } else {
                $style = 'default';
            }
        // MSIE
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac_PowerPC')) {
                $style = (file_exists($stylePath . 'mac_ie.css'))
                         ? 'mac_ie' 
                         : 'default';
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows')) {
                $style = (file_exists($stylePath . 'win_ie.css'))
                         ? 'win_ie'
                         : 'default';
            }
        // iPhone
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')) {
            $style = (file_exists($stylePath . 'iphone.css')) 
                     ? 'iphone' 
                     : 'default';
        // Safari / KHTML
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'KHTML')) {
            $style = (file_exists($stylePath . 'safari.css')) 
                     ? 'safari' 
                     : 'default';
        // Opera
        } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
            $style = (file_exists($stylePath . 'opera.css')) 
                     ? 'opera'
                     : 'default';
        } else {
            $style = 'default';
        }
        return $stylePath . $style . '.css';
    }

    /**
     * Get XML Language
     *
     * @access protected
     * @return string
     */
    public function getXmlLanguage()
    {
        global $config;

        switch($config['language']) {
            case 'japanese':
                return 'ja';
                break;
            case 'italian':
                return 'it';
                break;
            case 'german':
                return 'de';
                break;
            default:
                return 'en';
                break;
        }
    }
    
    /**
     * Get XHTML Version Number
     *
     * @return void
     */
    public function getXhtmlVersion()
    {
        global $config;
        
        switch ($config['xml_version']) {
            case '1.1':
                return 'XHTML 1.1';
                break;
            case '1.1-content-negotiation':
                return 'XHTML 1.1';
                break;
            default :
                return 'XHTML 1.0';
                break;
        };
    }


    /**
     * Get XML Version
     *
     * @return void
     */
    public function getXmlVersion()
    {
       /**
         Don't show XML definition to Microsoft Internet Explorer 6
         for its poor XML+CSS support.
       */
       if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) {
           return '';
       } else  {
           return '<?xml version="1.0" encoding="utf-8"?>' . "\n";
       }
    }


    /**
     * Get XHTML Document Type Definition
     *
     * @uses   getXmlLanguage
     * @return void
     */
    public function getDTD()
    {
        global $config;
        
        $xmlLang = $this->getXmlLanguage();
        
        // Switch XML DTD (Document Type Definition)
        if (($config['xml_version'] == '1.1') || 
            ($config['xml_version'] == '1.1-content-negotiation')) {
            return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
                      "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $xmlLang . '">
<head>
';
        } elseif (($config['xml_version'] == '1.0-transitional')) {
            return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $xmlLang . '">
<head>
';
        } else {
            return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $xmlLang . '" lang="' . $xmlLang . '">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="content-style-type" content="text/css" />
';
        }
    }


    /**
     * Get current directory
     * 
     * @return string
     */
    public function getDir()
    {
        global $pathToIndex;

        return $pathToIndex;
    }

}
