<?php
/**
 * Loggix_Module - Sub class for modulue creation.
 *
 * PHP version 5
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   8.3.2
 */


/**
 * Include Application Class
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Application.php';

/**
 * Loggix Module Class
 *
 * All Loggix modules should extend this class
 *
 */
class Loggix_Module extends Loggix_Application {

    /**
     * Get Language File
     *
     * @access public
     * @param  string $module_name
     * @return array  $lang
     */
    public function getModuleLanguage($moduleName)
    {
        global $pathToIndex, $lang;
        include $pathToIndex . '/modules/' . $moduleName . '/lang/' . self::$config['language'] . '.lang.php';
    }

}
