<?php
/**
 * GD Thumb-Nail Generator
 * @author   : P_BLOG Project
 * @created  : 2005-05-09 23:28:22
 * @modified : 2005-05-10 01:25:01
 */

//===================================================
// User Config

// For stand-alone use
// $img_dir = './img/';
// $cfg['thumb_nail_w'] = 90;
// $cfg['thumb_nail_h'] = 90;


// For P_BLOG use
$img_dir = './';

$cfg['thumb_nail_w'] = $_GET['w'];
$cfg['thumb_nail_h'] = $_GET['h'];

//===================================================

class GdThumbNail {

    function output($img_dir)
    {
        global $cfg;

        if (!empty($_GET['src'])) {
            $src      = $_GET['src'];
            $img_path = $img_dir . $src;

            $img_info = @getimagesize($img_path);
    
            if ($img_info[0] > $cfg['thumb_nail_w']) {
                $div_ratio = $img_info[0] / $cfg['thumb_nail_w'];
            } elseif ($img_info[1] > $cfg['thumb_nail_h']) {
                $div_ratio = $img_info[1] / $cfg['thumb_nail_w'];
            } else {
                $div_ratio = 1;
            }
 
            $thumb_img_w = round($img_info[0] / $div_ratio);
            $thumb_img_h = round($img_info[1] / $div_ratio);

            switch ($img_info[2]) {
                case '1': // GIF
                    // No thumb-nail generation when GIF. Output the PNG message instead.
                    $thumb_img = imagecreate(90, 90);
                    // Background
                    for ($i = 40; $i < 255; $i++) {
                        $r = $i; // Red
                        $g = $i; // Green
                        $b = $i; // Blue
                        $color = imagecolorallocate($thumb_img, $r, $g, $b);
                        imageline($thumb_img, 0, $r, 90, $r, $color);
                    }
                    // Background Highlighting
                    /*
                    for ($i = 2; $i < 40; $i++) {
                        $color = imagecolorallocate($thumb_img, 70, 70, 70);
                        imageline($thumb_img, 2, $i, 87, $i, $color);
                    }
                    */
                    // Border
                    $border_top = imagecolorallocate($thumb_img, 5, 5, 5);
                    imageline($thumb_img, 0, 0, 90, 0, $border_top);
                    $border_right = imagecolorallocate($thumb_img, 5, 5, 5);
                    imageline($thumb_img, 89, 0, 89, 90, $border_right);
                    $border_bottom = imagecolorallocate($thumb_img, 5, 5, 5);
                    imageline($thumb_img, 89, 89, 0, 89, $border_bottom);
                    $border_left = imagecolorallocate($thumb_img, 5, 5, 5);
                    imageline($thumb_img, 0, 89, 0, 0, $border_left);
                    // Text
                    $text_color = imagecolorallocate($thumb_img, 205, 205, 205);
                    // int imagestring ( int image, int font, int x, int y, string s, int col)
                    imagestring($thumb_img, 2, 15, 20, 'GIF IMG', $text_color);
                    imagestring($thumb_img, 2, 15, 40, "{$img_info[0]}x{$img_info[1]}px", $text_color);
                    header('Content-Type: image/png');
                    imagepng($thumb_img);       
                    break;
                case '2': // JPEG
                    $thumb_img = imagecreatetruecolor($thumb_img_w, $thumb_img_h);
                    $img_default = imagecreatefromjpeg($img_path);
                    imagecopyresized($thumb_img, $img_default, 0, 0, 0, 0, $thumb_img_w, $thumb_img_h, $img_info[0], $img_info[1]);
                    header('Content-Type: image/jpeg');
                    imagejpeg($thumb_img);
                    break;
                case '3': // PNG
                    $thumb_img = imagecreatetruecolor($thumb_img_w, $thumb_img_h);
                    $img_default = imagecreatefrompng($img_path);
                    imagecopyresized($thumb_img, $img_default, 0, 0, 0, 0, $thumb_img_w, $thumb_img_h, $img_info[0], $img_info[1]);
                    header('Content-Type: image/png');
                    imagepng($thumb_img);
                    break;
                default:
                    $thumb_img = imagecreate(90, 90);
                    // Background
                    for ($i = 40; $i < 255; $i++) {
                        $r = $i;
                        $g = $i;
                        $b = $i;
                        $color = imagecolorallocate($thumb_img, $r, $g, $b);
                        imageline($thumb_img, 0, $r, 90, $r, $color);
                    }
                    // Border
                    $border_top = imagecolorallocate($thumb_img, 5, 5, 5);
                    imageline($thumb_img, 0, 0, 90, 0, $border_top);
                    $border_right = imagecolorallocate($thumb_img, 5, 5, 5);
                    imageline($thumb_img, 89, 0, 89, 90, $border_right);
                    $border_bottom = imagecolorallocate($thumb_img, 5, 5, 5);
                    imageline($thumb_img, 89, 89, 0, 89, $border_bottom);
                    $border_left = imagecolorallocate($thumb_img, 5, 5, 5);
                    imageline($thumb_img, 0, 89, 0, 0, $border_left);
                    // Text
                    $text_color = imagecolorallocate($thumb_img, 205, 205, 205);
                    imagestring($thumb_img, 2, 23, 20, 'Unknown', $text_color);
                    imagestring($thumb_img, 2, 23, 40, 'Format', $text_color);
                    header('Content-Type: image/png');
                    imagepng($thumb_img);
                    break;
            }
            if (!empty($img_default)) { imagedestroy($img_default); }
            imagedestroy($thumb_img);
        }
    }
}

$gd_thumb = new GdThumbNail;
$gd_thumb->output($img_dir);
?>
