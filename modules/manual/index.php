<?php
/**
 * LOGGiX Index
 *
 * @package Loggix
 * @author  Loggix Project
 * @since   5.5.16
 * @version 8.3.23
 */

$pathToIndex = '../..';
require_once $pathToIndex . '/lib/Loggix/Application.php';
require_once $pathToIndex . '/lib/Loggix/Expander.php';

$app = new Loggix_Application;
$exp = new Loggix_Expander;
$sessionState = $app->getSessionState();

header('Location: ./' . $app->setXmlLanguage() . '/');

