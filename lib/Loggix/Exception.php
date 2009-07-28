<?php 
/**
 * Loggix_Exception - Sub class for Exceptions.
 *
 * PHP version 5
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project.
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   8.3.23
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Core.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'View.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Plugin.php';

/**
 * Loggix Exception Class
 *
 * @package Loggix
 */
class Loggix_Exception extends Exception 
{

    /**
     * @var object
     * @see __construct()
     */
    public $plugin;
    
    public function __construct()
    {
        global $pathToIndex;
        
        $this->plugin = new Loggix_Plugin;
    }
    
    /**
     * @uses   Loggix_Application
     * @uses   Loggix_View
     * @return array $item
     */
    public function getArticleNotFoundMessage()
    {
        global $pathToIndex;
        
        $errorView = new Loggix_View($pathToIndex . Loggix_Core::LOGGIX_THEME_DIR . 'errors/article-not-found.html');
        
        return array('contents' => $errorView->render(),
                     'pager'    => '',
                     'result'   => '',
                     'title'    => Loggix_Application::setTitle('404 Not Found')
               );
    }


    /**
     * @uses   Loggix_Application
     * @uses   Loggix_View
     * @return array $item
     */
    public function getFileNotFoundMessage()
    {
        global $pathToIndex;

        $errorView = new Loggix_View($pathToIndex . Loggix_Core::LOGGIX_THEME_DIR . 'errors/file-not-found.html');
        
        return array('contents' => $errorView->render(),
                     'pager'    => '',
                     'result'   => '',
                     'title'    => Loggix_Application::setTitle('404 Not Found')
               );
    }
}
