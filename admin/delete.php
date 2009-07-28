<?php
/**
 * Delete Item
 *
 * @since   5.5.12
 * @version 9.2.8
 */

$pathToIndex = '..';
require_once $pathToIndex . '/lib/Loggix/Application.php';

$app = new Loggix_Application;
$sessionState = $app->getSessionState();
$config       = $app->getConfigArray();

//=============================================
// INSTALL SQLs
//=============================================
$installQueries = array(
'CREATE TABLE ' . LOG_TABLE . ' (
  id INTEGER PRIMARY KEY,
  title,
  href,
  comment,
  text_mode INTEGER DEFAULT 0,
  excerpt,
  date,
  mod,
  draft INTEGER DEFAULT 0,
  ping_uri,
  allow_comments INTEGER DEFAULT 1,
  allow_pings INTEGER DEFAULT 1,
  author
)',
'CREATE TABLE ' . LOG_TAG_TABLE . ' (
  id INTEGER PRIMARY KEY,
  tag_name
)',
'CREATE TABLE ' . LOG_TAG_MAP_TABLE . ' (
  id INTEGER PRIMARY KEY,
  log_id REFERENCES loggix_log(id),
  tag_id REFERENCES loggix_log_tag(id)
)',
'CREATE TABLE ' . DOWNLOADS_META_TABLE . ' (
  id INTEGER PRIMARY KEY,
  file_title,
  file_type,
  file_name,
  file_size,
  file_date,
  file_mod,
  file_comment,
  text_mode INTEGER DEFAULT 0,
  file_count INTEGER DEFAULT 0,
  draft INTEGER DEFAULT 0,
  author
)',
'CREATE TABLE ' . DOWNLOADS_DATA_TABLE . ' (
  id INTEGER PRIMARY KEY,
  masterid INTEGER DEFAULT 0,
  file_data
)',
'CREATE TABLE ' . DOWNLOADS_TAG_TABLE . ' (
  id INTEGER PRIMARY KEY,
  tag_name
)',
'CREATE TABLE ' . DOWNLOADS_TAG_MAP_TABLE . ' (
  id INTEGER PRIMARY KEY,
  log_id REFERENCES loggix_downloads_meta(id),
  tag_id REFERENCES loggix_downloads_tag(id)
)',
'CREATE TABLE ' . SESSION_TABLE . ' (
  id PRIMARY KEY,
  sess_var,
  sess_date
)',
'CREATE TABLE ' . ACCESSLOG_TABLE . ' (
  id INTEGER PRIMARY KEY,
  referer,
  user_agent,
  remote_host,
  date,
  hour
)',
'CREATE TABLE ' . TRACKBACK_TABLE . ' (
  id INTEGER PRIMARY KEY,
  blog_id default 0,
  title,
  excerpt,
  url,
  name,
  date,
  trash default 0
)',
'CREATE TABLE ' . COMMENT_TABLE . ' (
  id INTEGER PRIMARY KEY,
  tid,
  parent_key default 1,
  title,
  comment,
  user_name,
  user_pass,
  user_mail,
  user_uri,
  type,
  date,
  mod,
  user_ip,
  refer_id,
  trash default 0
)'
);


//=============================================
// UNINSTALL SQLs
//=============================================
$uninstallQueries = array(
'DROP TABLE ' . LOG_TABLE,
'DROP TABLE ' . LOG_TAG_TABLE,
'DROP TABLE ' . LOG_TAG_MAP_TABLE,
'DROP TABLE ' . DOWNLOADS_META_TABLE,
'DROP TABLE ' . DOWNLOADS_DATA_TABLE,
'DROP TABLE ' . DOWNLOADS_TAG_TABLE,
'DROP TABLE ' . DOWNLOADS_TAG_MAP_TABLE,
'DROP TABLE ' . SESSION_TABLE,
'DROP TABLE ' . ACCESSLOG_TABLE,
'DROP TABLE ' . TRACKBACK_TABLE,
'DROP TABLE ' . COMMENT_TABLE
);

$app->insertSafe();

if ($sessionState == 'on') {
    // DELETE ENTRY
    if (!empty($_GET['id'])) {
        $id = intval($_GET['id']);
        
        // Delete entry
        $app->plugin->doAction('pre-delete-entry', $id);
        $deleteEntrySql      = 'DELETE FROM ' 
                             .     LOG_TABLE . ' '
                             . 'WHERE '
                             .     "(draft = '1') AND (id = '" . $id . "')";
        $deleteEntryRes = $app->db->query($deleteEntrySql);
        
        // Delete the entry's tag map
        $deleteTagMapSql     = 'DELETE FROM ' 
                             .     LOG_TAG_MAP_TABLE . ' '
                             . 'WHERE '
                             .     "log_id = '" . $id . "'";
        $deleteTagMapRes = $app->db->query($deleteTagMapSql);
        $app->plugin->doAction('after-entry-deleted', $id);
        
        // Delete the entry's comments
        $deleteCommentsSql   = 'DELETE FROM ' 
                             .     COMMENT_TABLE . ' '
                             . 'WHERE '
                             .     "refer_id = '" . $id . "'";
        $deleteCommentsRes = $app->db->query($deleteCommentsSql);
        $app->plugin->doAction('after-entry-and-comments-deleted', $id);
        
        // Delete the entry's trackbacks
        $deleteTrackbacksSql = 'DELETE FROM ' 
                             .     TRACKBACK_TABLE . ' '
                             . 'WHERE '
                             .     "blog_id = '" . $id . "'";
        $deleteTrackbacksRes = $app->db->query($deleteTrackbacksSql);
        $app->plugin->doAction('after-entry-and-trackbacks-deleted', $id);
        
        if ($deleteEntryRes && 
            $deleteTagMapRes && 
            $deleteCommentsRes &&
            $deleteTrackbacksRes) {
            header('Location: ./drafts.php');
        }

    // FORCED SESSION DELETE
    } elseif ((!empty($_POST['delete_sess_id'])) &&
              (strlen($_POST['delete_sess_id']) == '32')) {
        $deleteSessionId    = $_POST['delete_sess_id'];
        $deleteSessionIdSql = 'DELETE FROM ' 
                            .     SESSION_TABLE . ' '
                            . 'WHERE '
                            .     "id = '" . $deleteSessionId . "'";
        $res = $app->db->query($deleteSessionIdSql);
        if ($res) {
            header('Location: ./info.php');
        }

    // FORCED SESSION GARBAGE COLLECTION
    } elseif ((!empty($_POST['force_sess_gc'])) && 
              ($_POST['force_sess_gc'] == '1')) {
        //$sess_gc = $_POST['force_sess_gc'];
        $maxLifeTime = get_cfg_var("session.gc_maxlifetime");
        //$maxlifetime = '30';
        $expirationTime = time() - $maxLifeTime;
        $sql = 'DELETE FROM ' 
             .     SESSION_TABLE . ' '
             . 'WHERE '
             .     "sess_date < '" . $expirationTime . "'";
        $res = $app->db->query($sql);
        if ($res) {
            header('Location: ./info.php');
        }
    // INITIALIZE
    } elseif ((!empty($_POST['initialize_all'])) && 
              ($_POST['initialize_all'] == '1')) {
        // Delete all tables
        for ($i = 0; $i < count($uninstallQueries); $i++ ) {
                $res1 = $app->db->query($uninstallQueries[$i]);
        }
        // Create tables
        for ($i = 0; $i < count($installQueries); $i++) {
                $res2 = $app->db->query($installQueries[$i]);
        }

        // Insert first entry...
        // First Entry Data
        $title    = 'Welcome!';
        $comment  = '<img src="./theme/images/loggix_icon.png" alt="Loggix" />'
                  . "\n\n"
                  . 'This is the first entry.';
        $excerpt  = '';
        $postDate = gmdate('Y-m-d H:i:s', time() + ($config['tz'] * 3600));
        $modDate  = gmdate('Y-m-d H:i:s', time() + ($config['tz'] * 3600));
        $draft    = '0';

        // First Entry SQL
        $firstEntrySql = 'INSERT INTO ' 
                       .     LOG_TABLE 
                       .         '(' 
                       .             'title, '
                       .             'comment, '
                       .             'excerpt, '
                       .             'date, '
                       .             'mod, '
                       .             'draft'
                       .         ') '
                       .     'VALUES'
                       .         '('
                       .             "'" . $title    . "', "
                       .             "'" . $comment  . "', "
                       .             "'" . $excerpt  . "', "
                       .             "'" . $postDate . "', "
                       .             "'" . $modDate  . "', "
                       .             "'" . $draft    . "'"
                       .         ')';
        $firstEntryRes = $app->db->query($firstEntrySql);
        
        // Get the new entry ID.
        $getNewEntryIdSql = 'SELECT MAX(id) FROM ' . LOG_TABLE;
        $getNewEntryIdRes = $app->db->query($getNewEntryIdSql);
        $id   = $getNewEntryIdRes->fetchColumn();
        
        // Add the new entry tag
        $addTagNameSql = 'INSERT INTO ' 
                       .     LOG_TAG_TABLE 
                       .         '(tag_name) '
                       .    'VALUES'
                       .         "('Untagged')";
        $addTagNameRes = $app->db->query($addTagNameSql);

        $app->addTag(LOG_TAG_MAP_TABLE, $id);
        
// ----------------------------------------------

        $addDownloadsTagNameSql = 'INSERT INTO ' 
                                .     DOWNLOADS_TAG_TABLE 
                                .         '(tag_name) '
                                .    'VALUES'
                                .         "('Untagged')";
        $addDownloadsTagNameRes = $app->db->query($addDownloadsTagNameSql);

        $app->addTag(DOWNLOADS_TAG_MAP_TABLE, '0');

        header('Location: ../index.php');

    } else {
        header('Location: ../index.php');
    }
} else {
    header('Location: ../index.php');
}
