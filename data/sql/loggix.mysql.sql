-- phpMyAdmin SQL Dump
-- version 2.10.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 21, 2009 at 07:34 PM
-- Server version: 5.0.41
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `loggix`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `loggix_downloads_meta`
-- 

CREATE TABLE `loggix_downloads_meta` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `file_title` varchar(100) NOT NULL default '',
  `file_type` varchar(60) NOT NULL default 'application/octet-stream',
  `file_name` varchar(100) NOT NULL default '',
  `file_size` bigint(20) unsigned NOT NULL default '0',
  `file_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `file_mod` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `file_hash` varchar(50) NOT NULL default '',
  `file_comment` longtext NOT NULL,
  `file_count` int(11) NOT NULL default '0',
  `draft` tinyint(4) NOT NULL default '0',
  `author` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `loggix_downloads_data` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `masterid` int(11) unsigned NOT NULL default '0',
  `file_data` mediumblob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `master_id` (`masterid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- 
-- Table structure for table `loggix_downloads_tag`
-- 

CREATE TABLE `loggix_downloads_tag` (
  `id` int(11) NOT NULL auto_increment,
  `tag_name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
INSERT INTO `loggix_downloads_tag` VALUES(1,'Untagged');

CREATE TABLE `loggix_downloads_tag_map` (
  `id` int(11) NOT NULL auto_increment,
  `log_id` int(11) NOT NULL default '0',
  `tag_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (log_id) REFERENCES loggix_downloads_meta(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES loggix_downloads_tag(id) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
INSERT INTO `loggix_downloads_tag_map` VALUES(1,1,1);
-- --------------------------------------------------------

-- 
-- Table structure for table `loggix_log`
-- 

CREATE TABLE `loggix_log` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `href` varchar(255) NOT NULL default '',
  `comment` longtext NOT NULL,
  `excerpt` longtext NOT NULL,
  `text_mode` tinyint(4) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `mod` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `draft` tinyint(4) NOT NULL default '0',
  `ping_uri` text NOT NULL,
  `allow_comments` tinyint(4) NOT NULL default '1',
  `allow_pings` tinyint(4) NOT NULL default '1',
  `author` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

INSERT INTO `loggix_log` VALUES (1, 'Welcome!', '', '<img src="./theme/images/loggix-logo.png" alt="Loggix" />\r\nThis is the first entry.', '', '0', '2009-07-28 00:00:00', '2009-07-28 00:00:00', 0, '', 1, 1, '');

-- 
-- Table structure for table `loggix_log_tag`
-- 

CREATE TABLE `loggix_log_tag` (
  `id` int(11) NOT NULL auto_increment,
  `tag_name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
INSERT INTO `loggix_log_tag` VALUES(1,'Untagged');

CREATE TABLE `loggix_log_tag_map` (
  `id` int(11) NOT NULL auto_increment,
  `log_id` int(11) NOT NULL default '0',
  `tag_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  FOREIGN KEY (log_id) REFERENCES loggix_log(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES loggix_log_tag(id) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
INSERT INTO `loggix_log_tag_map` VALUES(1,1,1);

-- --------------------------------------------------------

-- 
-- Table structure for table `loggix_user`
-- 

CREATE TABLE `loggix_user` (
  `user_id` smallint(3) NOT NULL auto_increment,
  `user_name` varchar(50) NOT NULL default '',
  `user_pass` varchar(40) NOT NULL default '',
  `user_nickname` varchar(50) NOT NULL default '',
  `user_mail` varchar(50) NOT NULL default '',
  `user_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `loggix_user`
-- 

INSERT INTO `loggix_user` VALUES(1,'root','dc76e9f0c0006e8f919e0c515c66dbba3982f785','root','admin@example.com','2009-07-28 00:00:00',0);

-- --------------------------------------------------------

-- 
-- Table structure for table `loggix_config`
-- 

CREATE TABLE `loggix_config` (
  `config_key` varchar(64) NOT NULL default '',
  `config_value` mediumtext NOT NULL,
  PRIMARY KEY  (`config_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `loggix_config`
-- 

INSERT INTO `loggix_config` VALUES('loggix_title','My Great Log');
INSERT INTO `loggix_config` VALUES('root_dir','/');
INSERT INTO `loggix_config` VALUES('language','english');
INSERT INTO `loggix_config` VALUES('xml_version',1.0);
INSERT INTO `loggix_config` VALUES('page_max',7);
INSERT INTO `loggix_config` VALUES('tz',0);
INSERT INTO `loggix_config` VALUES('maxlifetime',60);
INSERT INTO `loggix_config` VALUES('show_date_title','yes');
INSERT INTO `loggix_config` VALUES('title_date_format','M d, Y');
INSERT INTO `loggix_config` VALUES('post_date_format','M d, Y G:i a');
INSERT INTO `loggix_config` VALUES('upload_file_max',3);
INSERT INTO `loggix_config` VALUES('menu_list','Latest Entries,index.php\r\n
Downloads,modules/downloads/index.php');
INSERT INTO `loggix_config` VALUES('css_cookie_name','loggix_style');
INSERT INTO `loggix_config` VALUES('css_cookie_time',15724800);
INSERT INTO `loggix_config` VALUES('default_style','default');
INSERT INTO `loggix_config` VALUES('css_list','Default,default
Elastic Template,elastic-template
Tang, tango');
INSERT INTO `loggix_config` VALUES('recent_comment_max',7);
INSERT INTO `loggix_config` VALUES('recent_trackback_max',7);
INSERT INTO `loggix_config` VALUES('block_tags','h1|h2|h3|h4|h5|h6|a|p|pre|blockquote|div|hr');
INSERT INTO `loggix_config` VALUES('block_keywords','buy|viagra|online|cheap|discount|penis|hydrocodone|sex|casino');
INSERT INTO `loggix_config` VALUES('block_ascii_only_text','no');

-- --------------------------------------------------------

-- 
-- Table structure for table `loggix_comment`
-- 

CREATE TABLE `loggix_comment` (
  `id` int(8) unsigned NOT NULL auto_increment,
  `tid` int(8) unsigned NOT NULL default '0',
  `parent_key` int(8) NOT NULL default '1',
  `title` varchar(100) NOT NULL default '',
  `comment` longtext NOT NULL,
  `user_name` varchar(50) NOT NULL default '',
  `user_pass` varchar(40) NOT NULL default '',
  `user_mail` varchar(50) NOT NULL default '',
  `user_uri` varchar(100) NOT NULL default '',
  `type` int(8) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `mod` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `user_ip` varchar(255) NOT NULL default '',
  `refer_id` int(11) NOT NULL default '0',
  `trash` int(8) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `master_id` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `loggix_session`
-- 

CREATE TABLE `loggix_session` (
  `id` varchar(32) NOT NULL,
  `sess_var` text NOT NULL,
  `sess_date` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

-- 
-- Table structure for table `p_trackback`
-- 

CREATE TABLE `loggix_trackback` (
  `id` int(11) NOT NULL auto_increment,
  `blog_id` int(11) NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `excerpt` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `trash` int(8) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


