<?php
/**
 * CAPTCHA image creator
 *
 * @see : http://www.ejeliot.com/pages/2
 */

// include captcha class
require('php-captcha.inc.php');

// define fonts
$aFonts = array('fonts/VeraSeBd.ttf', 'fonts/VeraSe.ttf', 'fonts/VeraBd.ttf');

// create new image
$aPhpCaptcha = new PhpCaptcha($aFonts, 200, 50);
$aPhpCaptcha->SetBackgroundImages('images/beach.jpg');
$aPhpCaptcha->UseColour(true);
$aPhpCaptcha->SetMinFontSize(12);
//$aPhpCaptcha->SetFileType('png');
//$aPhpCaptcha->SetNumChars(6);
//$aPhpCaptcha->SetOwnerText('CAPTCHA Code');
$aPhpCaptcha->Create();

?>