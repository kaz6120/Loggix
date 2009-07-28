BEGIN TRANSACTION;
CREATE TABLE loggix_user (
  user_id INTEGER PRIMARY KEY,
  user_name,
  user_pass,
  user_nickname,
  user_mail,
  user_date,
  user_status INTEGER DEFAULT 0
);
INSERT INTO loggix_user VALUES(1,'root','dc76e9f0c0006e8f919e0c515c66dbba3982f785','root','admin@example.com','2009-07-27 00:00:00',0);
CREATE TABLE loggix_config (
  config_key PRIMARY KEY,
  config_value
);
INSERT INTO loggix_config VALUES('loggix_title','My Great Log');
INSERT INTO loggix_config VALUES('root_dir','/');
INSERT INTO loggix_config VALUES('language','english');
INSERT INTO loggix_config VALUES('xml_version',1.0);
INSERT INTO loggix_config VALUES('page_max',7);
INSERT INTO loggix_config VALUES('tz',0);
INSERT INTO loggix_config VALUES('maxlifetime',60);
INSERT INTO loggix_config VALUES('show_date_title','yes');
INSERT INTO loggix_config VALUES('title_date_format','M d, Y');
INSERT INTO loggix_config VALUES('post_date_format','M d, Y G:i a');
INSERT INTO loggix_config VALUES('upload_file_max',3);
INSERT INTO loggix_config VALUES('menu_list','Latest Entries,index.php
Downloads,modules/downloads/index.php');
INSERT INTO loggix_config VALUES('css_cookie_name','loggix_style');
INSERT INTO loggix_config VALUES('css_cookie_time',15724800);
INSERT INTO loggix_config VALUES('default_style','default');
INSERT INTO loggix_config VALUES('css_list','Default,default
Elastic Template,elastic-template');
INSERT INTO loggix_config VALUES('recent_comment_max',7);
INSERT INTO loggix_config VALUES('recent_trackback_max',7);
INSERT INTO loggix_config VALUES('block_tags','h1|h2|h3|h4|h5|h6|a|p|pre|blockquote|div|hr');
INSERT INTO loggix_config VALUES('block_keywords','buy|viagra|online|cheap|discount|penis|hydrocodone|sex|casino');
INSERT INTO loggix_config VALUES('block_ascii_only_text','no');
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
  allow_pings INTEGER DEFAULT 1,
  author
);
INSERT INTO loggix_log VALUES(1,'Welcome!',NULL,'<img src=\"./theme/images/loggix-logo.png\" alt=\"Loggix\" />

This is the first entry.','','','2009-07-27 00:00:00','2009-07-28 00:00:00',0,NULL,1,1,NULL);
CREATE TABLE loggix_log_tag (
  id INTEGER PRIMARY KEY,
  tag_name
);
INSERT INTO loggix_log_tag VALUES(1,'Untagged');
CREATE TABLE loggix_log_tag_map (
  id INTEGER PRIMARY KEY,
  log_id REFERENCES loggix_log(id),
  tag_id REFERENCES loggix_log_tag(id)
);
INSERT INTO loggix_log_tag_map VALUES(1,1,1);
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
  file_count INTEGER DEFAULT 0,
  draft INTEGER DEFAULT 0,
  author
);
CREATE TABLE loggix_downloads_data (
  id INTEGER PRIMARY KEY,
  masterid INTEGER DEFAULT 0,
  file_data
);
CREATE TABLE loggix_downloads_tag (
  id INTEGER PRIMARY KEY,
  tag_name
);
INSERT INTO loggix_downloads_tag VALUES(1,'Untagged');
CREATE TABLE loggix_downloads_tag_map (
  id INTEGER PRIMARY KEY,
  log_id REFERENCES loggix_downloads_meta(id),
  tag_id REFERENCES loggix_downloads_tag(id)
);
INSERT INTO loggix_downloads_tag_map VALUES(1,0,0);
CREATE TABLE loggix_session (
  id PRIMARY KEY,
  sess_var,
  sess_date
);

CREATE TABLE loggix_access (
  id INTEGER PRIMARY KEY,
  referer,
  user_agent,
  remote_host,
  date,
  hour
);
CREATE TABLE loggix_trackback (
  id INTEGER PRIMARY KEY,
  blog_id default 0,
  title,
  excerpt,
  url,
  name,
  date,
  trash default 0
);
CREATE TABLE loggix_comment (
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
);
COMMIT;
