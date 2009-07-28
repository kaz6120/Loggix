<?php
/**
 * Post Comment Controller
 *
 * @package  Loggix_Module_Comment
 * @since    5.5.15
 * @version  9.3.17
 */


/**
 * Include Module class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/lib/Loggix/Module/Comment.php';

$app    = new Loggix_Module_Comment;
$config = $app->getConfigArray();
$app->insertTagSafe();

if ((isset($_POST['title'], 
          $_POST['comment'], 
          $_POST['user_name'], 
          $_POST['user_pass'], 
          $_POST['refer_id'], 
          $_POST['parent_key'])) &&
    ($_POST['title'] != '') && 
    ($_POST['comment'] != '') && 
    ($_POST['user_name'] != '') && 
    ($_POST['user_pass'] != '') && 
    ($_POST['refer_id'] != '') && 
    ($_POST['parent_key'] != '')
   ) {
   
   // Check if posting comment to the entry is allowed.
    $checkSql = 'SELECT '
              .     'allow_comments '
              . 'FROM ' 
              .     LOG_TABLE . ' '
              . 'WHERE '
              .     "id = '" . $_POST['refer_id'] . "'";
    $checkRes = $app->db->query($checkSql);
    $checkRes = $checkRes->fetchColumn();
    $receiveComment = ($checkRes == '1') ? 'allowed' : 'not_allowed';

    /**
     * Comment User Cookie
     */
    if (isset($_POST['loggix_comment_cookie'])) {
        if (isset($_POST['user_name'])) {
            $item['user_cookie']['user_name'] = $_POST['user_name'];
            setcookie('loggix_comment_user', $item['user_cookie']['user_name'], time()+86400*365, '/');
        }
        if (isset($_POST['user_email'])) {
            $item['user_cookie']['user_email'] = $_POST['user_email'];
            setcookie('loggix_comment_email', $item['user_cookie']['user_email'], time()+86400*365, '/');
        }
        if (isset($_POST['user_uri'])) {
            $item['user_cookie']['user_uri'] = $_POST['user_uri'];
            setcookie('loggix_comment_uri', $item['user_cookie']['user_uri'], time()+86400*365, '/');
        }
    }

    $userName  = $_POST['user_name'];
    $userPass  = sha1($_POST['user_pass']);
    $referId   = intval($_POST['refer_id']);
    $parentKey = intval($_POST['parent_key']);
    $title     = $_POST['title'];
    $comment   = $_POST['comment'];

    // Deny comment with the same content
    $checkSql = 'SELECT '
              .     'COUNT(id) '
              . 'FROM ' 
              .     COMMENT_TABLE . ' '
              . 'WHERE '
              .     'comment = :comment';
    
    $stmt = $app->db->prepare($checkSql);
    $stmt->execute(array(':comment' => $comment));
    $checkRow = $stmt->fetchColumn();
    
    if ($checkRow > 1) {
        header('Location: ' . $pathToIndex . '/index.php?id=' . $referId . '#comments');
        exit;
    }
    // Kill check sql connection
    unset($checkRes);
    
    // Deny by Referer
    if ((!isset($_SERVER['HTTP_REFERER'])) &&
        (!stristr($_SERVER['HTTP_REFERER'], 'comment/post.php'))
       ) {
        header('Location: ' . $pathToIndex . '/index.php?id=' . $referId . '#comments');
        exit;
    }

    // Plugin Filter before receiving comment
    $app->plugin->doAction('before-receive-comment', $referId);
    
    // Spam Blocking
    if ((preg_match('/.*<\/?(?: ' . $config['block_tags'] . ')/i',  $_POST['comment'])) ||
        (preg_match('/.*(' . $config['block_keywords'] . ')/i',  $_POST['comment'])) ||
        (($config['block_ascii_only_text'] == 'yes') &&
         (!preg_match('/.*[\x80-\xff]/', $_POST['comment']))) ||
        (preg_match('/.*<\/?(?:' . $config['block_tags'] . ')/i', $_POST['title'])) ||
        ($receiveComment == 'not_allowed')
       ) {
        header('Location: ' . $pathToIndex . '/index.php?id=' . $referId . '#comments');
    } else {
    
        if ($title == '') { $title = 'Re:'; }
    
        // Get user's remote host info
        $remoteHost = (!isset($_SERVER['REMOTE_HOST'])) 
                    ? @gethostbyaddr($_SERVER['REMOTE_ADDR'])
                    : $_SERVER['REMOTE_HOST'];

        $userUri = (isset($_POST['user_uri'])) ? $_POST['user_uri'] : '';

        $app->db->beginTransaction();
        $fdate = gmdate('Y-m-d H:i:s', time() + ($config['tz'] * 3600));
        $cmod  = gmdate('Y-m-d H:i:s', time() + ($config['tz'] * 3600));
        
        $sql = 'INSERT INTO '
             .     COMMENT_TABLE . ' '
             .         '('
             .             '`parent_key`, '
             .             '`title`, '
             .             '`comment`, '
             .             '`user_name`, '
             .             '`user_pass`, '
             .             '`user_uri`, '
             .             '`date`, '
             .             '`mod`, '
             .             '`user_ip`, '
             .             '`refer_id`'
             .         ') '
             .     'VALUES'
             .         '('
             .             ':parent_key, '
             .             ':title, '
             .             ':comment, '
             .             ':user_name, '
             .             ':user_pass, '
             .             ':user_uri, '
             .             ':date, '
             .             ':mod, '
             .             ':user_ip, '
             .             ':refer_id' 
             .         ')';
        $sql = $app->setDelimitedIdentifier($sql);      
        $stmt = $app->db->prepare($sql);
        $res = $stmt->execute(
                   array(
                       ':parent_key' => $parentKey,
                       ':title'      => $title,
                       ':comment'    => $comment,
                       ':user_name'  => $userName,
                       ':user_pass'  => $userPass,
                       ':user_uri'   => $userUri,
                       ':date'       => $fdate,
                       ':mod'        => $cmod,
                       ':user_ip'    => $remoteHost,
                       ':refer_id'   => $referId
                   )
               );
                
        $app->db->commit();

        header('Location: ' . $pathToIndex . '/index.php?id=' . $referId . '#comments');
    }
} else {
    $sessionState = $app->getSessionState();
    $additionalTitle = 'Not Allowed';
    $content = "<h2>Request Not Allowed</h2>\n";
    $item = array(
        'title'    => $app->setTitle($additionalTitle),
        'contents' => $content,
        'result'   => '',
        'pager'    => ''
    );
    $app->display($item, $sessionState);
    exit;
}

