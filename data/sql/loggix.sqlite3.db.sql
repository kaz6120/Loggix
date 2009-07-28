# SQLiteManager Dump
# Version: 1.2.0
# http://www.sqlitemanager.org/
#
# SQLite Version: 3.3.17
# PHP Version: 5.2.5
# Database: loggix.sqlite3.db
# --------------------------------------------------------

#
# Table structure for table: loggix_access
#
DROP TABLE loggix_access;
CREATE TABLE loggix_access (
  id INTEGER PRIMARY KEY,
  referer,
  user_agent,
  remote_host,
  date,
  hour
);

#
# Dumping data for table: loggix_access
#
# --------------------------------------------------------


#
# Table structure for table: loggix_comment
#
DROP TABLE loggix_comment;
CREATE TABLE loggix_comment (
  id INTEGER PRIMARY KEY,
  tid,
  parent_key default '1',
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
  trash default '0'
);

#
# Dumping data for table: loggix_comment
#
# --------------------------------------------------------


#
# Table structure for table: loggix_config
#
DROP TABLE loggix_config;
CREATE TABLE loggix_config (
  config_key PRIMARY KEY,
  config_value
);

#
# Dumping data for table: loggix_config
#
INSERT INTO loggix_config (config_key, config_value) VALUES ('loggix_title', 'My Great Log');
INSERT INTO loggix_config (config_key, config_value) VALUES ('root_dir', '/');
INSERT INTO loggix_config (config_key, config_value) VALUES ('language', 'english');
INSERT INTO loggix_config (config_key, config_value) VALUES ('xml_version', '1.0');
INSERT INTO loggix_config (config_key, config_value) VALUES ('page_max', '7');
INSERT INTO loggix_config (config_key, config_value) VALUES ('tz', '0');
INSERT INTO loggix_config (config_key, config_value) VALUES ('maxlifetime', '60');
INSERT INTO loggix_config (config_key, config_value) VALUES ('show_date_title', 'yes');
INSERT INTO loggix_config (config_key, config_value) VALUES ('title_date_format', 'M d, Y');
INSERT INTO loggix_config (config_key, config_value) VALUES ('post_date_format', 'M d, Y G:i a');
INSERT INTO loggix_config (config_key, config_value) VALUES ('upload_file_max', '5');
INSERT INTO loggix_config (config_key, config_value) VALUES ('menu_list', 'Latest Entries,index.php
Downloads,modules/downloads/index.php');
INSERT INTO loggix_config (config_key, config_value) VALUES ('css_cookie_name', 'loggix_style');
INSERT INTO loggix_config (config_key, config_value) VALUES ('css_cookie_time', '15724800');
INSERT INTO loggix_config (config_key, config_value) VALUES ('default_style', 'default');
INSERT INTO loggix_config (config_key, config_value) VALUES ('css_list', 'Default,default
Elastic Template,elastic-template');
INSERT INTO loggix_config (config_key, config_value) VALUES ('recent_comment_max', '7');
INSERT INTO loggix_config (config_key, config_value) VALUES ('recent_trackback_max', '7');
INSERT INTO loggix_config (config_key, config_value) VALUES ('block_tags', 'h1|h2|h3|h4|h5|h6|a|p|pre|blockquote|div|hr');
INSERT INTO loggix_config (config_key, config_value) VALUES ('block_keywords', 'buy|viagra|cheap|discount|penis|hydrocodone|sex|casino');
INSERT INTO loggix_config (config_key, config_value) VALUES ('block_ascii_only_text', 'no');
# --------------------------------------------------------


#
# Table structure for table: loggix_downloads_data
#
DROP TABLE loggix_downloads_data;
CREATE TABLE loggix_downloads_data (
  id INTEGER PRIMARY KEY,
  masterid INTEGER DEFAULT '0',
  file_data
);

#
# Dumping data for table: loggix_downloads_data
#
# --------------------------------------------------------


#
# Table structure for table: loggix_downloads_meta
#
DROP TABLE loggix_downloads_meta;
CREATE TABLE loggix_downloads_meta (
  id INTEGER PRIMARY KEY,
  file_title,
  file_type,
  file_name,
  file_size,
  file_date,
  file_mod,
  file_comment,
  file_hash,
  text_mode INTEGER DEFAULT 0,
  file_count INTEGER DEFAULT '0',
  draft INTEGER DEFAULT '0'
);

#
# Dumping data for table: loggix_downloads_meta
#
# --------------------------------------------------------


#
# Table structure for table: loggix_downloads_tag
#
DROP TABLE loggix_downloads_tag;
CREATE TABLE loggix_downloads_tag (
  id INTEGER PRIMARY KEY,
  tag_name
);

#
# Dumping data for table: loggix_downloads_tag
#
INSERT INTO loggix_downloads_tag (id, tag_name) VALUES ('1', 'Untagged');
# --------------------------------------------------------


#
# Table structure for table: loggix_downloads_tag_map
#
DROP TABLE loggix_downloads_tag_map;
CREATE TABLE loggix_downloads_tag_map (
  id INTEGER PRIMARY KEY,
  log_id REFERENCES loggix_downloads_meta(id),
  tag_id REFERENCES loggix_downloads_tag(id)
);

#
# Dumping data for table: loggix_downloads_tag_map
#
# --------------------------------------------------------


#
# Table structure for table: loggix_log
#
DROP TABLE loggix_log;
CREATE TABLE loggix_log (
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
  allow_pings INTEGER DEFAULT 1
);

#
# Dumping data for table: loggix_log
#
INSERT INTO loggix_log (id, title, href, comment, text_mode, excerpt, date, mod, draft, ping_uri, allow_comments, allow_pings) VALUES ('1', 'Welcome!', NULL, '<img src="./theme/images/loggix-logo.png" alt="Loggix" />

This is the first entry.', NULL, NULL, '2009-07-28 00:00:00', '2009-07-28 00:00:00', '0', NULL, '1', '1');
# --------------------------------------------------------


#
# Table structure for table: loggix_log_tag
#
DROP TABLE loggix_log_tag;
CREATE TABLE loggix_log_tag (
  id INTEGER PRIMARY KEY,
  tag_name
);

#
# Dumping data for table: loggix_log_tag
#
INSERT INTO loggix_log_tag (id, tag_name) VALUES ('1', 'Untagged');
# --------------------------------------------------------


#
# Table structure for table: loggix_log_tag_map
#
DROP TABLE loggix_log_tag_map;
CREATE TABLE loggix_log_tag_map (
  id INTEGER PRIMARY KEY,
  log_id REFERENCES loggix_log(id),
  tag_id REFERENCES loggix_log_tag(id)
);

#
# Dumping data for table: loggix_log_tag_map
#
INSERT INTO loggix_log_tag_map (id, log_id, tag_id) VALUES ('1', '1', '1');
# --------------------------------------------------------


#
# Table structure for table: loggix_session
#
DROP TABLE loggix_session;
CREATE TABLE loggix_session (
  id PRIMARY KEY,
  sess_var,
  sess_date
);



#
# Table structure for table: loggix_trackback
#
DROP TABLE loggix_trackback;
CREATE TABLE loggix_trackback (
  id INTEGER PRIMARY KEY,
  blog_id default '0',
  title,
  excerpt,
  url,
  name,
  date,
  trash default '0'
);

#
# Dumping data for table: loggix_trackback
#
# --------------------------------------------------------


#
# Table structure for table: loggix_user
#
DROP TABLE loggix_user;
CREATE TABLE loggix_user (
  user_id INTEGER PRIMARY KEY,
  user_name,
  user_pass,
  user_nickname,
  user_mail,
  user_date,
  user_status INTEGER DEFAULT 0
);

#
# Dumping data for table: loggix_user
#
INSERT INTO loggix_user (user_id, user_name, user_pass, user_nickname, user_mail, user_date, user_status) VALUES ('2', 'root', 'dc76e9f0c0006e8f919e0c515c66dbba3982f785', 'root', NULL, NULL, '0');
# --------------------------------------------------------


#
# User Defined Function properties: md5rev
#
/*
function md5_and_reverse($string) {
    return strrev(md5($string));
}
*/

#
# User Defined Function properties: IF
#
/*
function sqliteIf($compare, $good, $bad){
	if ($compare) {
		return $good;
	} else {
		return $bad;
	}
}
*/
