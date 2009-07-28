<?php
/**
 * LOGGiX Expander Module
 *
 * @package LOGGiX
 * @author  LOGGiX Project
 * @since   5.5.16
 * @version 8.1.6 
 */


// =============== (( SETTINGS BEGIN )) ===============


$pathToIndex     = '../../..';

$title            = 'Manual';

$cascadeRootTheme = 'No';

// =============== (( SETTINGS   END )) ===============


set_include_path($pathToIndex . '/lib/Loggix/');

require_once 'Application.php';
require_once 'Expander.php';

$app = new Loggix_Application;
$exp = new Loggix_Expander;

$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();
$exp->getModuleLanguage();

// Title & Contents
$item = array(
    'title'    => $app->setTitle($title),
    'contents' => $exp->getContent(),
    'pager'    => '',
    'result'   => ''
);

if ($cascadeRootTheme == 'Yes') {
    $app->display($item, $sessionState);
} else {
    $exp->display($item, $sessionState);
}
