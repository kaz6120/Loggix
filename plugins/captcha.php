<?php
/**
 * Plugin Name: CAPTCHA plugin for Loggix
 *
 * @copyright Copyright (C) Loggix Project
 * @link      http://loggix.gotdns.org/
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @since     5.5.16
 * @version   9.2.21
 */ 

$this->plugin->addFilter('comment-post-form', 'displayCaptcha');
$this->plugin->addAction('before-receive-comment', 'checkCommentWithCaptcha');


/**
 * Display CAPTCHA image
 */
function displayCaptcha($text)
{

    global $config, $pathToIndex;
    
    switch ($config['language']) {
        case 'japanese':
            $textParts = array(
                '画像内の文字を入力して下さい。',
            );
            break;
        default:
            $textParts = array(
                'Type the word above.',
            );
            break;
    }
    
    $stringsToConvert = array(
        '<p id="comment-submit">', 
    );
    
    $replacements = array(
'<p id="captcha">
<img src="' . $pathToIndex . '/plugins/captcha/captcha-image.php" width="200" height="50" alt="CAPTCHA™ Code" />
<input type="text" size="25" value="' . $textParts[0] . '" id="userInputCaptchaPhrase" name="captcha_phrase" onfocus="if (value == \''. $textParts[0] . '\') { value = \'\'; }" onblur="if (value == \'\') { value = \''. $textParts[0] . '\'; }" />
</p>
<p id="comment-submit">',
    );
    

    $captcha = (function_exists('imagecreate'))
             ? str_replace($stringsToConvert, $replacements, $text)
             : $text;

    return $captcha; //str_replace($stringsToConvert, $replacements, $text);
}

/**
 * Check posted comment with CAPTCHA
 */
function checkCommentWithCaptcha()
{
    global $config, $pathToIndex, $userName, $sessionState, $app;

    require $pathToIndex . '/plugins/captcha/php-captcha.inc.php';
    
    switch ($config['language']) {
        case 'japanese':
            $textParts = array(
                'コメント認証',
                'コメント内容が認証出来ません。',
            );
            break;
        default:
            $textParts = array(
                'Not Allowed',
                'Request Not Allowed.',
            );
            break;
    }
    
    if (PhpCaptcha::Validate($_POST['captcha_phrase'])) {
        return true;
    } else {
        $additionalTitle = $textParts[0];
        $content = '<h2>' . $textParts[0] . '</h2>' . "\n"
                 . '<div class="important warning">' . "\n"
                 . '<p>' . $textParts[1] . '</p>'. "\n"
                 . '</div>' . "\n";
                 
        $item = array(
            'title'    => $app->setTitle($additionalTitle),
            'contents' => $content,
            'result'   => '',
            'pager'    => ''
        );
        
        $app->display($item, $sessionState);
        exit;
    }
}

