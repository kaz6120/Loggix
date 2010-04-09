<?php
/**
 * @since   8.3.2
 * @version 10.4.9
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();

$item = $app->setMenuItems($sessionState);


// Session
if ($sessionState == 'on') {

    $app->insertTagSafe();

    // Save Changes
    if (isset($_POST['root_dir'],
              $_POST['loggix_title'],
              $_POST['page_max'],
              $_POST['language'],
              $_POST['xml_version'],
              $_POST['tz'],
              $_POST['show_date_title'],
              $_POST['title_date_format'],
              $_POST['post_date_format'],
              $_POST['upload_file_max'],
              $_POST['menu_list'],
              $_POST['css_cookie_name'], 
              $_POST['css_cookie_time'], 
              $_POST['css_list'], 
              $_POST['recent_comment_max'], 
              $_POST['recent_trackback_max'],
              $_POST['block_tags'],
              $_POST['block_keywords'],
              $_POST['block_ascii_only_text'], 
              $_POST['save_settings'])) {
        if ($_POST['save_settings'] == 'save') {
            // Set new value
            $root_dir              = $_POST['root_dir'];
            $loggix_title          = $_POST['loggix_title'];
            $page_max              = $_POST['page_max'];
            $language              = $_POST['language'];
            $xml_version           = $_POST['xml_version'];
            $tz                    = $_POST['tz'];
            $maxlifetime           = '60';//$_POST['maxlifetime'];
            $show_date_title       = $_POST['show_date_title'];
            $title_date_format     = $_POST['title_date_format'];
            $post_date_format      = $_POST['post_date_format'];
            $upload_file_max       = $_POST['upload_file_max'];
            $menu_list             = str_replace('\r', PHP_EOL, str_replace("\n", PHP_EOL, $_POST['menu_list']));//str_replace('\r', '\r\n', str_replace("\n", '\r', $_POST['menu_list']));
            $css_cookie_name       = $_POST['css_cookie_name']; 
            $css_cookie_time       = $_POST['css_cookie_time'] * 86400; 
            $css_list              = $_POST['css_list'];//str_replace('\r', '\r\n', str_replace("\n", '\r', $_POST['css_list'])); 
            $recent_comment_max    = $_POST['recent_comment_max']; 
            $recent_trackback_max  = $_POST['recent_trackback_max'];
            $block_tags            = $_POST['block_tags'];
            $block_keywords        = $_POST['block_keywords'];
            $block_ascii_only_text = $_POST['block_ascii_only_text']; 
        } else {
            // Set default value
            $root_dir              = '/';
            $loggix_title          = 'My Great Log';
            $page_max              = '7';
            $language              = 'english';
            $xml_version           = '1.0';
            $tz                    = '0';
            $maxlifetime           = '60';
            $show_date_title       = 'yes';
            $title_date_format     = 'M d, Y';
            $post_date_format      = 'M d, Y G:i a';
            $upload_file_max       = '3';
            $menu_list             = 'Latest Entries,index.php' . PHP_EOL . 'Downloads,modules/downloads/index.php';
            $css_cookie_name       = 'loggix_style'; 
            $css_cookie_time       = '15724800'; 
            $css_list              = 'Default,default' . PHP_EOL . 'Elastic Template,elastic-template'; 
            $recent_comment_max    = '7'; 
            $recent_trackback_max  = '7';
            $block_tags            = 'h1|h2|h3|h4|h5|h6|a|p|pre|blockquote|div|hr';
            $block_keywords        = 'buy|viagra|online|cheap|discount|penis|hydrocodone|sex|casino';
            $block_ascii_only_text = 'no'; 
        }
        $sqlValue = array(
            'root_dir'              => $root_dir,
            'loggix_title'          => $loggix_title,
            'page_max'              => $page_max,
            'language'              => $language,
            'xml_version'           => $xml_version,
            'tz'                    => $tz,
            'maxlifetime'           => $maxlifetime,
            'show_date_title'       => $show_date_title,
            'title_date_format'     => $title_date_format,
            'post_date_format'      => $post_date_format,
            'upload_file_max'       => $upload_file_max,
            'menu_list'             => $menu_list,
            'css_cookie_name'       => $css_cookie_name, 
            'css_cookie_time'       => $css_cookie_time, 
            'css_list'              => $css_list, 
            'recent_comment_max'    => $recent_comment_max, 
            'recent_trackback_max'  => $recent_trackback_max,
            'block_tags'            => $block_tags,
            'block_keywords'        => $block_keywords,
            'block_ascii_only_text' => $block_ascii_only_text
        );
        foreach ($sqlValue as $key => $value) {
            $sql = 'UPDATE '
                 .     'loggix_config '
                 . 'SET '
                 .     "config_value = '" . $value . "' "
                 . 'WHERE '
                 .     "config_key = '" . $key . "'";
            $res = $app->db->query($sql);
        }
        if ($res) {
            header('Location : ' . $_SERVER['PHP_SELF']);
        }
     }
              
    // Load config
    $config = $app->getConfigArray();

    // Convert line breaks
    $config['css_list']   = str_replace('\r',   "\n", // OS X & Linux
                            str_replace('\r\n', '\r', // Windows
                            $config['css_list']));
    $config['menu_list']  = str_replace('\r',   "\n", 
                            str_replace('\r\n', '\r', 
                            $config['menu_list']));


    $item['xhtml_versions'] = array(
        '1.0'   => '1.0 Strict (' . $lang['recommended'] . ')',
        '1.0-transitional'   => '1.0 Transitional',
        '1.1-content-negotiation' => '1.1 (' . $lang['content_negotiation'] . ')',
        '1.1'   => '1.1 (' . $lang['application_xml'] . ')'
    );

    $item['time_zone'] = array(
        '12'  => '+12:00 ( NZST: New Zealand Standard )',
        '11'  => '+11:00',
        '10'  => '+10:00 ( GST: Guam Standard )',
        '9'   => '+09:00 ( JST : 日本標準時 )',
        '8'   => '+08:00 ( CCT: China Coast )',
        '7'   => '+07:00',
        '6'   => '+06:00',
        '5'   => '+05:00',
        '4'   => '+04:00',
        '3'   => '+03:00 ( BT: Baghdad )',
        '2'   => '+02:00 ( EET: Eastern European )',
        '1'   => '+01:00 ( CET: Central European )',
        '0'   => '*00:00 ( GMT : Greenwich Mean Time, London)',
        '-1'  => '-01:00 ( WAT: West Africa, Cape Verde Island )',
        '-2'  => '-02:00',
        '-3'  => '-03:00 ( Brazil, Buenos Aires, Argentina)',
        '-4'  => '-04:00 ( AST: Atlantic Standard )',
        '-5'  => '-05:00 ( EST: Bogota, Lima, Peru, New York )',
        '-6'  => '-06:00 ( CST: Central Standard )',
        '-7'  => '-07:00 ( MST: Mountain Standard )',
        '-8'  => '-08:00 ( PST: Pacific Standard )',
        '-9'  => '-09:00 ( YST: Yukon Standard )',
        '-10' => '-10:00 ( HST: Hawaii Standard )',
        '-11' => '-11:00',
        '-12' => '-12:00 ( IDLW: International Date Line West )',
    );
    if ($config['language'] == 'japanese') {
        $item['lang_en'] = '';
        $item['lang_ja'] = ' selected="selected"';
    } else {
        $item['lang_en'] = ' selected="selected"';
        $item['lang_ja'] = '';
    }

    if ($config['show_date_title'] == 'yes') {
        $item['show_date_title_yes'] = ' checked="checked"';
        $item['show_date_title_no']  = '';
    } else {
        $item['show_date_title_yes'] = '';
        $item['show_date_title_no']  = ' checked="checked"';
    }
    
    if ($config['block_ascii_only_text'] == 'yes') {
        $item['block_status_1'] = ' checked="checked"';
        $item['block_status_2'] = '';
    } else {
        $item['block_status_1'] = '';
        $item['block_status_2'] = ' checked="checked"';
    }
    
    // Load system info view file
    $contents = new Loggix_View($pathToIndex . '/theme/admin/preferences.html');
    $contents->assign('config', $config);
    $contents->assign('item', $item);
    $contents->assign('lang', $lang);
    $item['contents'] = $contents->render();
    // Pager
    //--------------------------
    $item['pager'] = '';
    $item['result'] = '';

    // Title
    //--------------------------
    $item['title'] = $app->setTitle($lang['system_info']);

    $app->display($item, $sessionState);
} else {
    // When session is off...
    header('Location: ../index.php');
}
