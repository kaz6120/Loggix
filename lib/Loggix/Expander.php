<?php
/**
 * Loggix_Expander - Main class for Expander Module creation.
 *
 * PHP version 5
 *
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   8.7.29
 */


/**
 * Include Core Classes
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'View.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Core.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Plugin.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception.php';

/**
 * Loggix Expander Class
 *
 * Example:
 * <code>
 * /expander_dir/index.php?id=filename
 * </code>
 *
 */
class Loggix_Expander {
    
    const EXPANDER_DATA_DIR       = './data/';
    const EXPANDER_PLUGIN_DIR     = './plugins/';

    /**
     * @var object
     * @see __construct()
     */

    public $plugin;
    
    /**
     * @var float
     * @see __construct()
     * @see Loggix_Utility::getGenerationTime()
     */
    public static $startTime;

    /**
     * @var object
     * @see Loggix_Utility
     */    
    private $_delegate = null;

    /**
     * Constructor
     *
     * @uses microtime
     */
    public function __construct()
    {
        global $pathToIndex;
        
        self::$startTime = microtime();
        $this->plugin = new Loggix_Plugin;
        $this->_includePlugins();
    }


    /**
     * Relay the method call to Loggix_Utility
     *
     */
    public function __call($method, $args)
    {
        $this->_delegate = new Loggix_Core;
        return call_user_func_array(array($this->_delegate, $method), $args);
    }


    /**
     * Load Modules in /plugins/ directory.
     *
     * @uses   opendir
     * @uses   readdir
     * @uses   strpos
     */
    private function _includePlugins()
    {
        global $pathToIndex, $config, $module;
        if ($dh = @opendir(self::EXPANDER_PLUGIN_DIR)) {
            while (($file = readdir($dh)) !== false) {
                if (($file != '.') && ($file != '..') && 
                    (strpos($file, '.php'))) {
                    include_once self::EXPANDER_PLUGIN_DIR . $file;
                }
            }
            closedir($dh);
        }
    }

    /**
     * Get and Display Expander Content
     * 
     * Supported extensions are:
     * .inc.php | .php | .inc | .html | .txt | 
     * .text, .markdown, .md, .mdown, .mkd, .mkdn (Markdown format)
     *
     * @return string $contents
     */
    public function getContent() 
    {    
        global $pathToIndex, $config, $lang, $item, $module;
        
        $this->getModuleLanguage();
        
        $contents = '';
        
        // Load data file by ID
        // foo.inc.php | foo.php | foo.inc | foo.html | foo.txt | 
        // foo.text, foo.markdown, foo.md, foo.mdown, foo.mkd, foo.mkdn, |
        try {
            $id = (isset($_GET['id'])) 
                  ? str_replace(DIRECTORY_SEPARATOR, '', 
                                htmlspecialchars(basename($_GET['id'])))
                  : 'default';

            $contentType = array(
                'inc.php'  => self::EXPANDER_DATA_DIR . $id . '.inc.php',
                'php'      => self::EXPANDER_DATA_DIR . $id . '.php',
                'inc'      => self::EXPANDER_DATA_DIR . $id . '.inc',
                'html'     => self::EXPANDER_DATA_DIR . $id . '.html',
                'txt'      => self::EXPANDER_DATA_DIR . $id . '.txt',
                'text'     => self::EXPANDER_DATA_DIR . $id . '.text',
                'markdown' => self::EXPANDER_DATA_DIR . $id . '.markdown',
                'md'       => self::EXPANDER_DATA_DIR . $id . '.md',
                'mdown'    => self::EXPANDER_DATA_DIR . $id . '.mdown',
                'mkd'      => self::EXPANDER_DATA_DIR . $id . '.mkd',
                'mkdn'     => self::EXPANDER_DATA_DIR . $id . '.mkdn',
            );
            
            $aView = new Loggix_View();
            $aView->assign('config', $config);
            $aView->assign('lang', $lang);
            $aView->assign('module', $module);
            
            if (file_exists($contentType['inc.php'])) {
                $contents = $aView->render($contentType['inc.php']);
            } else if (file_exists($contentType['php'])) {
                $contents = $aView->render($contentType['php']);
            } else if (file_exists($contentType['inc'])) {
                $contents = $aView->render($contentType['inc']);
            } else if (file_exists($contentType['html'])) {
                $contents = $aView->render($contentType['html']);
            } else if (file_exists($contentType['txt'])) {
                $contents = "<pre>\n" 
                          . $aView->render($contentType['txt']) 
                          . "</pre>\n";
            } else if (file_exists($contentType['text'])) {
                $contents = $aView->render($contentType['text']);
            } else if (file_exists($contentType['markdown'])) {
                $contents = $aView->render($contentType['markdown']);
            } else if (file_exists($contentType['md'])) {
                $contents = $aView->render($contentType['md']);
            } else if (file_exists($contentType['mdown'])) {
                $contents = $aView->render($contentType['mdown']);
            } else if (file_exists($contentType['mkd'])) {
                $contents = $aView->render($contentType['mkd']);            
            } else if (file_exists($contentType['mkdn'])) {
                $contents = $aView->render($contentType['mkdn']);
            } else {
                $e = new Loggix_Exception();
                $item = $e->getFileNotFoundMessage();
                $contents = $item['contents'];
            }
        } catch (Loggix_Exception $e) {
            $item = $e->getFileNotFoundMessage();
            $contents = $item['contents'];
        }

        return $this->plugin->applyFilters('ex-content', $contents);
    }


    /**
     * Get Expander Language File
     *
     * @param  string $module_name
     * @return array  $lang
     */
    public function getModuleLanguage()
    {
        global $config, $lang;
        $languageFile = './lang/' . $config['language'] . '.lang.php';
        if (file_exists($languageFile)) {
            include $languageFile;
        }
    }


    /**
     * Send HTTP Headers to User Agent
     * 
     * @return void
     */
    public function sendHttpHeaders()
    {
        global $pathToIndex, $config;
        
        // Get Last-Modified
        $date = $this->getDataLastModified('Y-m-d H:i:s');
        $lastModifiedGmt = (!empty($date)) 
                           ? gmdate('D, d M Y H:i:s', strtotime($date)) . ' GMT' 
                           : 'Tue, 24 Jan 1984 06:00:00 GMT';

        // Select XML Version
        switch ($config['xml_version']) {
            case '1.1':
                // Based on W3C Note
                $mediaType = 'application/xhtml+xml';
                break;
            case '1.1cn':
                // Content Negotiation
                if ((stristr($_SERVER['HTTP_ACCEPT'], 'application/xhtml+xml')) || 
                    (stristr($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator')) ||
                    (stristr($_SERVER['HTTP_USER_AGENT'], 'Another_HTML-lint'))) {
                    $mediaType = 'application/xhtml+xml';
                } else {
                    $mediaType = 'text/html';
                }
                break;
            default :
                $mediaType = 'text/html';
                break;
        };
        header('Last-Modified: ' . $lastModifiedGmt);
        header('Content-Type: ' . $mediaType . '; charset=utf-8');
        header('Vary: Accept');
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
    }

    /**
     * Display XHTML
     * 
     * @param  array $item
     * @param  string $sessionState
     * @uses   setMenuItems
     * @uses   sendHttpHeaders
     * @uses   Loggix_View
     */

    public function display($item, $sessionState)
    {
        global $config, $pathToIndex, $module, $lang;
        
        ob_start();
        
        $this->getModuleLanguage();
        
        $item['site_title'] = $this->plugin->applyFilters('h1', $config['loggix_title']);

        // Navigation View

        $navView = new Loggix_View();
        $navView->assign('item', $item);
        $navView->assign('lang', $lang);
        $navView->assign('module', $module);
        $item['navigation'] = $this->plugin->applyFilters('navigation', $navView->render('./theme/navigation.html'));

        // Send HTTP Headers
        $this->sendHttpHeaders();

        $item['gt'] = $this->getGenerationTime(self::$startTime);

        // View
        $xhtml = new Loggix_View();
        $xhtml->assign('item', $item);
        $xhtml->assign('lang', $lang);
        $xhtml->assign('module', $module);

        echo $this->plugin->applyFilters('xhtml', $xhtml->render('./theme/base.html'));
        
        ob_end_flush();
        
    }
}
