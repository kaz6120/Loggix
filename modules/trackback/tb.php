<?php
/**
 * Receiving Trackback Ping
 *
 * @package   Trackback
 * @uses      Loggix_Application
 * @since     4.5.9
 * @version   9.3.24
 */


/**
 * Include Core Module class
 */
$pathToIndex = '../..';
require_once $pathToIndex . '/lib/Loggix/Module/Trackback.php';

$trackback = new Loggix_Module_Trackback;
$config    = $trackback->getConfigArray();

$error   = 0;
$message = '';

$trackback->insertSafe();

if (!isset($_GET['id'])) {
	$error = 1;
	$message = 'You must set blog id!';
} else {

    $id = $_GET['id'];
    
    // Check if trackback is allowed
    $checkSql = 'SELECT '
              .     'allow_pings '
              . 'FROM ' 
              .     LOG_TABLE . ' '
              . 'WHERE '
              .     'id = :id';                  
    $stmt = $trackback->db->prepare($checkSql);
    $stmt->execute(
               array(
                   ':id' => $id
               )
           );
    $checkRes = $stmt->fetchColumn();
    
//    echo var_dump($checkRes);
    
    $receiveTrackback = ($checkRes == '1') 
                      ? 'allowed'
                      : 'not_allowed';
//    echo var_dump($receiveTrackback);
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $title   = $_POST['title'];
        $excerpt = $_POST['excerpt'];
        $url     = $_POST['url'];
        $name    = $_POST['blog_name'];
	// Receiving Ping from MT doesn't work without this 
    } elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
        if (isset($_GET['title'], $_GET['excerpt'], $_GET['url'], $_GET['blog_name'])) {
            $title   = $_GET['title'];
            $excerpt = $_GET['excerpt'];
            $url     = $_GET['url'];
            $name    = $_GET['blog_name'];
        }
	} else {
        $title   = '';
        $excerpt = '';
        $url     = '';
        $name    = '';
    }
    // Deny when required values are empty
    if (empty($url)     || empty($title) ||
        empty($excerpt) || empty($name)  || 
        ($url == 'http://') ||
        ($receiveTrackback == 'not_allowed')) {
        $error = 1;
        $message = 'Bad Request. Error ID:1';
	} else {
        $articleId = intval($_GET['id']);
        $title   = $title;
        $excerpt = $excerpt;
        $url     = $url;
        $name    = $name;
        
        // Spam Blocking
        if ((preg_match($config['block_spam']['keywords'], $title)) ||
            (preg_match($config['block_spam']['keywords'], $excerpt)) ||
            (preg_match($config['block_spam']['keywords'], $url)) ||
            (preg_match($config['block_spam']['keywords'], $name)) ||
            (($config['block_spam']['deny_1byteonly'] == 'yes') && 
             (!preg_match('/.*[\x80-\xff]/', $excerpt)))
           ) {
            //echo 'You Are A Spammer!';
            header('Location: ' . $pathToIndex . '/index.php?id=' . $articleId);
            exit;
        }
        
        $trackback->plugin->doAction('before-receive-trackback', $articleId);
        
        // Deny Ping from the same page
        $checkSql = 'SELECT '
                  .     'COUNT(id) '
                  . 'FROM ' 
                  .     TRACKBACK_TABLE . ' '
                  . 'WHERE ' 
                  .     '(blog_id = :article_id)'
                  .     ' AND '
                  .     '(url = :url)';
        $stmt = $trackback->db->prepare($checkSql);
        $stmt->execute(
                   array(
                       ':article_id' => $articleId,
                       ':url'        => $url
                   )
               );
        $checkRow = $stmt->fetchColumn();
      
        // Deny ping if the content is same with previously posted one
        $checkSql2 = 'SELECT '
                   .     'COUNT(id) '
                   . 'FROM ' 
                   .     TRACKBACK_TABLE . ' '
                   . 'WHERE '
                   .     '(title = :title)'
                   .     ' AND '
                   .     '(excerpt = :excerpt)';
        $stmt2 = $trackback->db->prepare($checkSql2);
        $stmt2->execute(
                   array(
                       ':title'   => $title,
                       ':excerpt' => $excerpt
                   )
               );
        $checkRow2 = $stmt2->fetchColumn();

        if (($checkRow  == 0) &&
            ($checkRow2 == 0)) {
//            $trackback->db->query('BEGIN;');
            $fdate = gmdate('Y-m-d H:i:s', time() + ($config['tz'] * 3600));
            $sql = 'INSERT INTO ' 
                 .     TRACKBACK_TABLE . ' '
                 .         '(`blog_id`, `title`, `excerpt`, `url`, `name`, `date`)'
                 .         ' VALUES '
                 .         '(:article_id, :title, :excerpt, :url, :name, :fdate)';
            $sql = $trackback->setDelimitedIdentifier($sql);
            $stmt3 = $trackback->db->prepare($sql);
            $res = $stmt3->execute(
                        array(
                            ':article_id'   => $articleId,
                            ':title'        => $title,
                            ':excerpt'      => $excerpt,
                            ':url'          => $url,
                            ':name'         => $name,
                            ':fdate'        => $fdate
                        )
                    );

            if ($res) {
                $error = 0;
                $message = 'Ping received.';
            } else {
            	$error = 1;
            	$message = 'Internal error!';
            }
//            $trackback->db->query('COMMIT;');
        } else {
            $error = 1;
            $message = 'Ping denied.';
        }
	}
}

echo <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<response>
<error>{$error}</error>
<message>{$message}</message>
</response>
EOD;

