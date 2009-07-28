<?php
/**
 * Loggix_Plugin - Sub class for plugin object creation.
 *
 * PHP version 5
 *
 * @package   Loggix
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   8.4.4
 */

/**
 * Loggix Plugin Class
 */
class Loggix_Plugin {

    /**
     * Filter Hook List Array
     *
     * @access public
     * @return array
     */
    public function getFilterHookList()
    {
        return array('title',
                     'h1',
                     'index-view',
                     'downloads-index-view',
                     'permalink-view',
                     'entry-content',
                     'edit-entry',
                     'navigation',
                     'comment-post-form',
                     'comment-text',
                     'ex-content'
               );
    }


    /**
     * Action Hook List Array
     *
     * @access public
     * @return array
     */
    public function getActionHookList()
    {
        return array('after-new-entry-posted',
                     'before-delete-entry',
                     'after-entry-deleted',
                     'before-recieve-comment',
                     'before-recieve-trackback',
                     'after-send-trackback'
               );
    }

    /**
     * Add Filter
     *
     * @access public
     * @return boolean
     */
    public function addFilter($tag, $functionToAdd, $priority = 10, $acceptedArgs = 1) 
    {
        global $filterTable;
        
        if (isset($filterTable[$tag][$priority])) {
            foreach($filterTable[$tag][$priority] as $filter) {
                if ($filter['function'] == $functionToAdd) {
                    return true;
                }
            }
        }

        $filterTable[$tag][$priority][] = array(
            'function'     => $functionToAdd, 
            'acceptedArgs' => $acceptedArgs
        );
        return true;
    }

    /**
     * Remove Filter
     *
     * @access public
     * @return boolean
     */
    public function removeFilter($tag, $functionToRemove, $priority = 10, $acceptedArgs = 1) 
    {
        global $filterTable;
        $toret = false;
        if (isset($filterTable[$tag][$priority])) {
            $newFunctionList = array();
            foreach($filterTable[$tag][$priority] as $filter) {
                if ($filter['function'] != $functionToRemove) {
                    $newFunctionList[] = $filter;
                } else {
                    $toret = true;
                }
            }
            $filterTable[$tag][$priority] = $newFunctionList;
        }
        return $toret;
    }

    /**
     * Do Action
     *
     * @access public
     * @return boolean
     */
    public function doAction($tag, $arg = array()) 
    {
        global $filterTable;

        $extraArgs = array_slice(func_get_args(), 2);
        $args = array_merge(array($arg), $extraArgs);
        if (!isset($filterTable[$tag])) {
            return;
        } else {
            ksort($filterTable[$tag]);
        }
        foreach ($filterTable[$tag] as $priority => $functions) {
            if (!is_null($functions)) {
                foreach($functions as $function) {
                    $functionName = $function['function'];
                    $acceptedArgs = $function['acceptedArgs'];
                    if ($acceptedArgs == 1) {
                        $theArgs = (is_array($arg)) ? $arg : array($arg);
                    } else if ( $acceptedArgs > 1 ) {
                        $theArgs = array_slice($args, 0, $acceptedArgs);
                    } else if ($acceptedArgs == 0) {
                        $theArgs = NULL;
                    } else {
                        $theArgs = $args;
                    }
                    $string = call_user_func_array($functionName, $theArgs);
                }
            }
        }
    }

    /**
     * Apply Filter
     *
     * @access public
     * @return boolean
     */
    public function applyFilters($tag, $string) 
    {
        global $filterTable;

        $args = array_slice(func_get_args(), 2);

        if (!isset($filterTable[$tag])) {
            return $string;
        } else {
            ksort($filterTable[$tag]);
        }

        foreach ($filterTable[$tag] as $priority => $functions) {
            if (!is_null($functions)) {
                foreach ($functions as $function) {
                    $allArgs      = array_merge(array($string), $args);
                    $functionName = $function['function'];
                    $acceptedArgs = $function['acceptedArgs'];
                    if ($acceptedArgs == 1) {
                        $theArgs = array($string);
                    } else if ($acceptedArgs > 1) {
                        $theArgs = array_slice($allArgs, 0, $acceptedArgs);
                    } else if ($acceptedArgs == 0) {
                        $theArgs = NULL;
                    } else {
                        $theArgs = $allArgs;
                    }
                    $string = call_user_func_array($functionName, $theArgs);
                }
            }
        }
        return $string;
    }

    // Other Plugin Functions
    public function addAction($tag, $functionToAdd, $priority = 10, $acceptedArgs = 1) {
        $this->addFilter($tag, $functionToAdd, $priority, $acceptedArgs);
    }

    public function removeAction($tag, $functionToRemove, $priority = 10, $acceptedArgs = 1) {
        $this->removeFilter($tag, $functionToRemove, $priority, $acceptedArgs);
    }
    

}
