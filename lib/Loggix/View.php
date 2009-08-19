<?php
/**
 * Loggix_View - Main class for view creation.
 *
 * PHP version 5
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   9.8.19 
 */


/**
 * Include Configuration and Constants Files
 */
//require_once dirname(__FILE__) . '/Core.php';
require_once dirname(__FILE__) . '/View/Helper.php';


/**
 * Loggix View Class
 *
 */
class Loggix_View {

    /**
     * @var array
     * @see __construct()
     */
    private $_templateVars;
    
    /**
     * @var string
     * @see __construct()
     */
    private $_templateFile;
    
    /**
     * @var string
     */
    private $_templateDir = '/theme/';

    /**
     * @var object
     */
    //private static $_delegate = null;
    private $_delegate = null;
    
    /**
     * Constructor
     *
     * @param $templateFile
     */
    public function __construct($templateFile = null) {
        $this->_templateFile = $templateFile;
        $this->_templateVars = array();
    }


    /**
     * Relay the method call to Loggix_View_Helper
     *
     */
    public function __call($method, $args)
    {
        $this->_delegate = new Loggix_View_Helper;
        return call_user_func_array(array($this->_delegate, $method), $args);
    }
    
    /**
     * Assign a template variable.
     *
     * @param  $varName  Template variable name
     * @param  $varValue Template variable value
     */
    public function assign($param)
    {
        $varName  = @func_get_arg(0);
        $varValue = @func_get_arg(1);
        if (is_object($varValue)) {
            $this->_templateVars[$varName] = $varValue->render();
        } else if (is_string($param)) {
            $this->_templateVars[$varName] = $varValue;
        } else if (is_array($param)) {
            foreach ($param as $varName => $varValue) {
                $this->_templateVars[$varName] = $varValue;
            }
        }
    }


    /**
     * Render the template file
     *
     * @param  $templateFile
     * @return string $contents
     */
    public function render($templateFile = null) 
    {
        if (!$templateFile) { $templateFile = $this->_templateFile; }
        extract($this->_templateVars);
        ob_start();
        include $templateFile;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }


    /**
     * Output the result to an User Agent.
     * 
     * @param  $templateFile
     * @return void
     */
    public function display($templateFile = null)
    {
        //ob_start('ob_gzhandler');
        ob_start();
        echo $this->render($templateFile);
        ob_end_flush();
    }

}
