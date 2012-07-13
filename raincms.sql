-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 12, 2012 at 07:45 PM
-- Server version: 5.5.9
-- PHP Version: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `rain`
--

-- --------------------------------------------------------

--
-- Table structure for table `block`
--

CREATE TABLE `block` (
  `block_id` int(11) NOT NULL AUTO_INCREMENT,
  `block_type_id` int(11) NOT NULL,
  `global` tinyint(1) NOT NULL COMMENT 'load this block in all content (true/false)',
  `layout_id` smallint(6) NOT NULL COMMENT 'Se page_id > 0 in_content_id = 0',
  `type_id` int(11) NOT NULL COMMENT 'type of block',
  `in_content_id` int(11) NOT NULL COMMENT 'Il contenuto dove si trova questo blocco. Se > 0 allora page_id = 0',
  `module` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `load_area` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `content_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `position` smallint(6) NOT NULL,
  PRIMARY KEY (`block_id`),
  KEY `page_id` (`layout_id`),
  KEY `in_content_id` (`in_content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dumping data for table `block`
--

INSERT INTO `block` VALUES(1, 1, 1, 0, 0, 0, 'content', '', 'right', 'content', 3, 0, 0);
INSERT INTO `block` VALUES(4, 0, 0, 0, 0, 0, 'content', '', 'left', 'content', 0, 0, 0);
INSERT INTO `block` VALUES(5, 0, 0, 0, 0, 0, 'content', '', 'left', 'content', 0, 0, 0);
INSERT INTO `block` VALUES(6, 0, 0, 0, 0, 0, 'content', '', 'left', 'content', 0, 0, 0);
INSERT INTO `block` VALUES(7, 0, 0, 0, 0, 0, 'content', '', 'left', 'content', 0, 0, 0);
INSERT INTO `block` VALUES(8, 0, 0, 0, 0, 0, 'content', '', 'left', 'content', 0, 0, 0);
INSERT INTO `block` VALUES(9, 1, 1, 0, 0, 0, 'content', '', 'left', 'content', 0, 0, 0);
INSERT INTO `block` VALUES(12, 0, 1, 0, 0, 0, 'content', '', 'left', 'content', 9, 0, 0);
INSERT INTO `block` VALUES(11, 0, 1, 0, 0, 0, 'content', '', 'left', 'content', 8, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `block_setting`
--

CREATE TABLE `block_setting` (
  `block_id` int(11) NOT NULL,
  `setting` varbinary(80) NOT NULL,
  `value` varbinary(255) NOT NULL,
  PRIMARY KEY (`block_id`,`setting`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `block_setting`
--


-- --------------------------------------------------------

--
-- Table structure for table `block_type`
--

CREATE TABLE `block_type` (
  `block_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `template` varchar(50) NOT NULL,
  PRIMARY KEY (`block_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `block_type`
--

INSERT INTO `block_type` VALUES(1, 'Content', 'default text block', 'content/block.');

-- --------------------------------------------------------

--
-- Table structure for table `block_type_option`
--

CREATE TABLE `block_type_option` (
  `block_type_id` smallint(6) NOT NULL DEFAULT '0',
  `name` varbinary(30) NOT NULL,
  `note` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `field_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `validation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `command` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `param` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `layout` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `multilanguage` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`block_type_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `block_type_option`
--

INSERT INTO `block_type_option` VALUES(1, 'content_id', 'is the id of the related content', 'text', 'numeric', '', '', 'content', 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `content_id` int(11) NOT NULL,
  `lang_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `type_id` int(11) NOT NULL,
  `layout_id` tinyint(4) NOT NULL DEFAULT '0',
  `menu_id` int(11) NOT NULL,
  `path` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tags` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `summary` text COLLATE utf8_unicode_ci NOT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  `template` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `read_access` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL,
  `ncomment` int(11) NOT NULL,
  `last_edit_time` int(11) NOT NULL,
  `changefreq` tinyint(1) NOT NULL,
  `priority` decimal(2,1) NOT NULL,
  `extra1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extra2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extra3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extra4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extra5` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extra6` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extra7` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extra8` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`content_id`,`lang_id`),
  FULLTEXT KEY `search` (`title`,`subtitle`,`tags`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` VALUES(2, 'en', 1, 3, 2, '', 'Home', '', '', '<p>123<img><img><img></p><div class="preview"><img></div><div class="preview"><img></div>', '', 1341769695, 'content.content', 0, 1, 0, 1341769695, 2, 0.7, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(3, 'en', 1, 0, 0, '', 'Content Right', '', '', 'Hey this is another content', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(4, 'en', 1, 1, 2, 'Test/', 'Test', '', '', '<p>asdasdasda</p><p>d</p><p>sa</p><p>d</p><p>asd</p><p>as</p><div class="preview" style="border:3px solid #ccc;background:#eee;width:100%;height:100px;"><img src="data:image/png;base64,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"><div class="progress"></div></div>', '', 1341749888, 'content.content', 0, 1, 0, 1341749888, 2, 0.7, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(5, 'en', 1, 0, 0, '', 'new block', '', '', 'test', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(6, 'en', 1, 0, 0, '', 'left content', '', '', 'this is the content', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(7, 'en', 1, 0, 0, '', 'ABC', '', '', 'xxx', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(8, 'en', 1, 0, 0, '', 'Another Content', '', '', 'Insert the content here', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(9, 'en', 1, 0, 0, '', 'New Side Block', '', '', 'Test', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(10, 'en', 1, 0, 0, '', 'Sheska', '', '', 'this is sheska\\''s block', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', '');
INSERT INTO `content` VALUES(11, 'en', 1, 0, 0, '', 'Matteo AWCheng', '', '', 'bla bla ', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `content_comment`
--

CREATE TABLE `content_comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` varchar(2) COLLATE utf8_bin NOT NULL,
  `content_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `comment` text COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(20) COLLATE utf8_bin NOT NULL,
  `email` varchar(30) COLLATE utf8_bin NOT NULL,
  `website` varchar(30) COLLATE utf8_bin NOT NULL,
  `ip` varchar(15) COLLATE utf8_bin NOT NULL,
  `code` varchar(32) COLLATE utf8_bin NOT NULL,
  `published` tinyint(4) NOT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=167 ;

--
-- Dumping data for table `content_comment`
--

INSERT INTO `content_comment` VALUES(125, 'en', 143, 1325353205, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(126, 'en', 143, 1325353210, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(127, 'en', 143, 1325353211, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(128, 'en', 143, 1325353212, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(129, 'en', 143, 1325353222, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(130, 'en', 143, 1325353225, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(131, 'en', 143, 1325353237, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(132, 'en', 143, 1325353276, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(133, 'en', 143, 1325353278, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(134, 'en', 143, 1325353278, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(135, 'en', 143, 1325358478, 0x6369616f, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(136, 'en', 143, 1325358607, 0x68656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(137, 'en', 143, 1325358612, 0x68656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(138, 'en', 143, 1325358630, 0x6369616f, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(139, 'en', 143, 1325358670, 0x68657920636f6d65207661, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(140, 'en', 143, 1325358684, 0x617364617364617364, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(141, 'en', 143, 1325358698, 0x617364617364, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(142, 'en', 143, 1325358725, 0x616263, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(143, 'en', 143, 1325358755, 0x61626320313233, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(144, 'en', 143, 1325358796, 0x61626320313233, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(145, 'en', 143, 1325358799, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(146, 'en', 143, 1325358815, 0x617364, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(147, 'en', 143, 1325358823, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(148, 'en', 143, 1325358833, 0x617364, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(149, 'en', 143, 1325358963, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(150, 'en', 143, 1325358967, 0x74657374, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(151, 'en', 143, 1325359894, 0x496e746572657373616e7465, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(152, 'en', 143, 1325359896, 0x496e746572657373616e7465, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(153, 'en', 143, 1325359934, 0x74657374, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(154, 'en', 143, 1325359947, 0x68656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(155, 'en', 143, 1325359970, 0x617364, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(156, 'en', 143, 1325359974, 0x617364, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(157, 'en', 143, 1325359979, 0x717765, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(158, 'en', 143, 1325359991, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(159, 'en', 143, 1325359998, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(160, 'en', 143, 1325360016, 0x616263, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(161, 'en', 143, 1325360056, 0x616263, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(162, 'en', 143, 1325360087, 0x486579, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(163, 'en', 143, 1325360100, 0x48656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(164, 'en', 143, 1325360546, 0x7777772e7261696e74706c2e636f6d20626c6120626c61207777772e676f6f676c652e636f6d, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(165, 'en', 143, 1325569318, 0x68656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1);
INSERT INTO `content_comment` VALUES(166, 'en', 143, 1325569324, 0x6369616f, 1, 'Rain', '', '', '127.0.0.1', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `content_permission`
--

CREATE TABLE `content_permission` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_access` tinyint(1) NOT NULL,
  `subcontent_access` tinyint(1) NOT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `content_permission`
--


-- --------------------------------------------------------

--
-- Table structure for table `content_rel`
--

CREATE TABLE `content_rel` (
  `content_id` int(11) NOT NULL,
  `rel_id` int(11) NOT NULL DEFAULT '0',
  `position` tinyint(4) NOT NULL DEFAULT '0',
  `rel_type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`content_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `content_rel`
--

INSERT INTO `content_rel` VALUES(4, 0, 1, 'parent');
INSERT INTO `content_rel` VALUES(2, 0, 0, 'parent');

-- --------------------------------------------------------

--
-- Table structure for table `content_type`
--

CREATE TABLE `content_type` (
  `type_id` smallint(6) NOT NULL DEFAULT '0',
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Nome del tipo',
  `icon` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `lang_index` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'nome del file delle lingue',
  `module` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `action` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `template_index` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1/0',
  `multilanguage` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'set 1 for content in multilanguage, 0 if content is the same for all language',
  `comment_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1/0 commenti abilitati/disabilitati',
  `link_other_content` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'you can link contents to this, example "related products"',
  `linked_copy` tinyint(1) NOT NULL COMMENT 'allow to create a link of this content into others content (ex. e-commerce, sd memory could be copied into "accessories" and "Storage"',
  `image_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `audio_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `video_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `document_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `archive_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `file_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `tags_enabled` tinyint(1) NOT NULL,
  `order_by` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'order of children',
  `searchable` tinyint(1) NOT NULL,
  `cache` tinyint(4) NOT NULL,
  `unique` tinyint(1) NOT NULL COMMENT 'set 1 if can be only one page with this type of contents',
  `write_access` tinyint(1) NOT NULL,
  `path_type` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '{title}:title, {content_id}: content_id {$y}:year, {$m}:month, {$d}: day',
  `path_short` tinyint(4) NOT NULL COMMENT '1 for short path that doesn''t include categories, ex (product/usb-1) 0: for complete path ex (products/memory/usb-1)',
  `changefreq` tinyint(1) NOT NULL COMMENT '0 => always     1 => hourly     2 => daily     3 => weekly     4 => monthly     5 => yearly     6 => never',
  `priority` decimal(2,1) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `content_type`
--

INSERT INTO `content_type` VALUES(1, 'content', 'content.gif', 'content', 'content', '', 'content/', 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 'r.position', 1, -1, 0, 0, '{title}', 0, 2, 0.7);
INSERT INTO `content_type` VALUES(10, 'news_section', 'news_section.gif', 'news', 'news', '', 'news/section.', 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 'r.position', 1, -1, 0, 0, '{title}', 0, 2, 0.7);
INSERT INTO `content_type` VALUES(11, 'news', 'news.gif', 'news', 'news', '', 'news/news.', 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 'r.position', 1, -1, 0, 0, '{y}/{m}/{d}/{title}', 0, 2, 0.7);

-- --------------------------------------------------------

--
-- Table structure for table `content_type_field`
--

CREATE TABLE `content_type_field` (
  `type_id` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `note` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `field_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `validation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `command` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `param` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `layout` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `multilanguage` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`type_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `content_type_field`
--

INSERT INTO `content_type_field` VALUES(1, 'content', '', 'word', 'required', '', '', 'row', 1, 3, 1);
INSERT INTO `content_type_field` VALUES(11, 'content', '', 'word', 'required', '', '', 'row', 1, 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `content_type_tree`
--

CREATE TABLE `content_type_tree` (
  `type_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`type_id`,`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Define the children of each content type';

--
-- Dumping data for table `content_type_tree`
--

INSERT INTO `content_type_tree` VALUES(1, 0);
INSERT INTO `content_type_tree` VALUES(1, 1);
INSERT INTO `content_type_tree` VALUES(10, 1);
INSERT INTO `content_type_tree` VALUES(11, 11);

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE `file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `rel_id` int(11) NOT NULL,
  `module` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `filepath` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type_id` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:IMAGE  2:AUDIO  3:VIDEO  4:DOCUMENT  5:ARCHIVE',
  `width` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `height` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `size` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'FILE_LIST:0, FILE_EMBED:1, FILE_BLOCK:2, FILE_COVER:3',
  `last_edit_time` int(11) NOT NULL,
  `read_access` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=69 ;

--
-- Dumping data for table `file`
--

INSERT INTO `file` VALUES(15, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/32afdd301c064a0d588900a6dd7c030b.jpg', '', 'www.rainframework.com/12/07/t_32afdd301c064a0d588900a6dd7c030b.jpg', 1, '', '', 31329, 1, 1341535635, 0, 0);
INSERT INTO `file` VALUES(14, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/e457ce1b774e2b076f60d7a147b8b78e.jpg', '', 'www.rainframework.com/12/07/t_e457ce1b774e2b076f60d7a147b8b78e.jpg', 1, '', '', 31329, 1, 1341535613, 0, 0);
INSERT INTO `file` VALUES(13, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/f02648054215a8859b5e10189201bea9.png', '', 'www.rainframework.com/12/07/t_f02648054215a8859b5e10189201bea9.png', 1, '', '', 9917, 1, 1341535469, 0, 0);
INSERT INTO `file` VALUES(12, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/d4411a3e76b6ad940efcc0c08a307662.png', '', 'www.rainframework.com/12/07/t_d4411a3e76b6ad940efcc0c08a307662.png', 1, '', '', 9917, 1, 1341535366, 0, 0);
INSERT INTO `file` VALUES(10, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/935a82cb416665b5d5d86d04e73b4ea6.jpg', '', 'www.rainframework.com/12/07/t_935a82cb416665b5d5d86d04e73b4ea6.jpg', 1, '', '', 31329, 1, 1341530624, 0, 0);
INSERT INTO `file` VALUES(16, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/5e3ba40e05438a07979d8e0bb7cdd3d3.jpg', '', 'www.rainframework.com/12/07/t_5e3ba40e05438a07979d8e0bb7cdd3d3.jpg', 1, '', '', 31329, 1, 1341535644, 0, 0);
INSERT INTO `file` VALUES(17, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/f6c2bb8caa7f4a8c6dcab1e2039a5187.png', '', 'www.rainframework.com/12/07/t_f6c2bb8caa7f4a8c6dcab1e2039a5187.png', 1, '', '', 9917, 1, 1341535674, 0, 0);
INSERT INTO `file` VALUES(18, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/11216a26b6cb4ea85fed8ce814ea4ceb.jpg', '', 'www.rainframework.com/12/07/t_11216a26b6cb4ea85fed8ce814ea4ceb.jpg', 1, '', '', 31329, 1, 1341535685, 0, 0);
INSERT INTO `file` VALUES(19, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/b78b1d401bc1a2ed3d967beaf23ea72c.png', '', 'www.rainframework.com/12/07/t_b78b1d401bc1a2ed3d967beaf23ea72c.png', 1, '', '', 9917, 1, 1341535697, 0, 0);
INSERT INTO `file` VALUES(20, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/dfb1873b013658efe72132502c5ddb47.png', '', 'www.rainframework.com/12/07/t_dfb1873b013658efe72132502c5ddb47.png', 1, '', '', 9917, 1, 1341535715, 0, 0);
INSERT INTO `file` VALUES(21, 2, 'content', 'Screen Shot 2012-07-03 at 11.48.17 PM.png', 'www.rainframework.com/12/07/45fbb169c4d02b8a12a69faf5df3a348.png', '', 'www.rainframework.com/12/07/t_45fbb169c4d02b8a12a69faf5df3a348.png', 1, '', '', 30897, 1, 1341535722, 0, 0);
INSERT INTO `file` VALUES(22, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/b148745003358dc6135604579f27f63f.png', '', 'www.rainframework.com/12/07/t_b148745003358dc6135604579f27f63f.png', 1, '', '', 9917, 1, 1341535726, 0, 0);
INSERT INTO `file` VALUES(23, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/d326e5b1eb01d614bc993858f9b40b44.png', '', 'www.rainframework.com/12/07/t_d326e5b1eb01d614bc993858f9b40b44.png', 1, '', '', 9917, 1, 1341535772, 0, 0);
INSERT INTO `file` VALUES(24, 2, 'content', 'photo.JPG', 'www.rainframework.com/12/07/a6962c8ec4a40091cee5bcae597a1dcb.jpg', '', 'www.rainframework.com/12/07/t_a6962c8ec4a40091cee5bcae597a1dcb.jpg', 1, '', '', 468533, 1, 1341535988, 0, 0);
INSERT INTO `file` VALUES(25, 2, 'content', 'photo (1).JPG', 'www.rainframework.com/12/07/7a7a9d18bee54308e741843c7d67e8ad.jpg', '', 'www.rainframework.com/12/07/t_7a7a9d18bee54308e741843c7d67e8ad.jpg', 1, '', '', 452376, 1, 1341536048, 0, 0);
INSERT INTO `file` VALUES(26, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/3f9e84c407c66cf54a581ec68196dff5.png', '', 'www.rainframework.com/12/07/t_3f9e84c407c66cf54a581ec68196dff5.png', 1, '', '', 9917, 1, 1341536077, 0, 0);
INSERT INTO `file` VALUES(27, 2, 'content', 'photo.JPG', 'www.rainframework.com/12/07/1fc7c344d40df660da7ce3a5a45bf685.jpg', '', 'www.rainframework.com/12/07/t_1fc7c344d40df660da7ce3a5a45bf685.jpg', 1, '', '', 468533, 1, 1341536098, 0, 0);
INSERT INTO `file` VALUES(28, 2, 'content', 'photo.JPG', 'www.rainframework.com/12/07/162c60c1d577303957990fc76ba7a605.jpg', '', 'www.rainframework.com/12/07/t_162c60c1d577303957990fc76ba7a605.jpg', 1, '', '', 468533, 1, 1341536102, 0, 0);
INSERT INTO `file` VALUES(29, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/7f40f08ba64e28ab97ddecb73eae1d27.jpg', '', 'www.rainframework.com/12/07/t_7f40f08ba64e28ab97ddecb73eae1d27.jpg', 1, '', '', 31329, 1, 1341536107, 0, 0);
INSERT INTO `file` VALUES(30, 2, 'content', 'photo.JPG', 'www.rainframework.com/12/07/88746dd4e0e5bc94c90320f143ea7f85.jpg', '', 'www.rainframework.com/12/07/t_88746dd4e0e5bc94c90320f143ea7f85.jpg', 1, '', '', 468533, 1, 1341536155, 0, 0);
INSERT INTO `file` VALUES(31, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/55de75f2d58804cff14fd47ae24baef4.jpg', '', 'www.rainframework.com/12/07/t_55de75f2d58804cff14fd47ae24baef4.jpg', 1, '', '', 31329, 1, 1341750801, 0, 0);
INSERT INTO `file` VALUES(32, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/07805031ca45703a2784d3a22fada59c.jpg', '', 'www.rainframework.com/12/07/t_07805031ca45703a2784d3a22fada59c.jpg', 1, '', '', 31329, 1, 1341750813, 0, 0);
INSERT INTO `file` VALUES(33, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/b6b3bb2566c690c39568034c677ea59e.jpg', '', 'www.rainframework.com/12/07/t_b6b3bb2566c690c39568034c677ea59e.jpg', 1, '', '', 31329, 1, 1341750844, 0, 0);
INSERT INTO `file` VALUES(34, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/27d31c8c92470a63443525031afbe99c.jpg', '', 'www.rainframework.com/12/07/t_27d31c8c92470a63443525031afbe99c.jpg', 1, '', '', 31329, 1, 1341751376, 0, 0);
INSERT INTO `file` VALUES(35, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/4739eafaaf3f3c92682977d13174433f.jpg', '', 'www.rainframework.com/12/07/t_4739eafaaf3f3c92682977d13174433f.jpg', 1, '', '', 31329, 1, 1341751463, 0, 0);
INSERT INTO `file` VALUES(36, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/a7e46e565426fe72b7b69c8424dd9460.jpg', '', 'www.rainframework.com/12/07/t_a7e46e565426fe72b7b69c8424dd9460.jpg', 1, '', '', 31329, 1, 1341751468, 0, 0);
INSERT INTO `file` VALUES(37, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/0ae1174ec9e2a77cb164290c6e1c4093.jpg', '', 'www.rainframework.com/12/07/t_0ae1174ec9e2a77cb164290c6e1c4093.jpg', 1, '', '', 31329, 1, 1341751586, 0, 0);
INSERT INTO `file` VALUES(38, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/7a068b4511854eb5216f2be63ed9868a.jpg', '', 'www.rainframework.com/12/07/t_7a068b4511854eb5216f2be63ed9868a.jpg', 1, '', '', 31329, 1, 1341751590, 0, 0);
INSERT INTO `file` VALUES(39, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/73e594cb33e9ac2b142058503be20410.jpg', '', 'www.rainframework.com/12/07/t_73e594cb33e9ac2b142058503be20410.jpg', 1, '', '', 31329, 1, 1341760760, 0, 0);
INSERT INTO `file` VALUES(40, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/18742d2ab2049e711c527832449c9c79.jpg', '', 'www.rainframework.com/12/07/t_18742d2ab2049e711c527832449c9c79.jpg', 1, '', '', 31329, 1, 1341760784, 0, 0);
INSERT INTO `file` VALUES(41, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/d56f9f10120c7611ddd16df75b49c892.jpg', '', 'www.rainframework.com/12/07/t_d56f9f10120c7611ddd16df75b49c892.jpg', 1, '', '', 31329, 1, 1341760900, 0, 0);
INSERT INTO `file` VALUES(42, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/bb4ef70e8219da1d721bf35fd367d736.jpg', '', 'www.rainframework.com/12/07/t_bb4ef70e8219da1d721bf35fd367d736.jpg', 1, '', '', 31329, 1, 1341760926, 0, 0);
INSERT INTO `file` VALUES(43, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/6b59cd487f6f2431949854101931fb0b.jpg', '', 'www.rainframework.com/12/07/t_6b59cd487f6f2431949854101931fb0b.jpg', 1, '', '', 31329, 1, 1341760990, 0, 0);
INSERT INTO `file` VALUES(44, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/3cf171049c5c4f180151f73e91eedf37.jpg', '', 'www.rainframework.com/12/07/t_3cf171049c5c4f180151f73e91eedf37.jpg', 1, '', '', 31329, 1, 1341761003, 0, 0);
INSERT INTO `file` VALUES(45, 2, 'content', 'photo.JPG', 'www.rainframework.com/12/07/295a0a27b2116156631641bc2d53de8f.jpg', '', 'www.rainframework.com/12/07/t_295a0a27b2116156631641bc2d53de8f.jpg', 1, '', '', 468533, 1, 1341761456, 0, 0);
INSERT INTO `file` VALUES(46, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/bcaf2435cc57f79355da67f7bdfce360.jpg', '', 'www.rainframework.com/12/07/t_bcaf2435cc57f79355da67f7bdfce360.jpg', 1, '', '', 31329, 1, 1341761533, 0, 0);
INSERT INTO `file` VALUES(47, 2, 'content', 'photo.JPG', 'www.rainframework.com/12/07/c1d47058e886862ebdcf4ce82a52b439.jpg', '', 'www.rainframework.com/12/07/t_c1d47058e886862ebdcf4ce82a52b439.jpg', 1, '', '', 468533, 1, 1341763076, 0, 0);
INSERT INTO `file` VALUES(48, 2, 'content', 'fede.jpg', 'www.rainframework.com/12/07/44a672879b8f8f42ce4a15da606021a0.jpg', '', 'www.rainframework.com/12/07/t_44a672879b8f8f42ce4a15da606021a0.jpg', 1, '', '', 31329, 1, 1341763099, 0, 0);
INSERT INTO `file` VALUES(49, 2, 'content', 'fede.jpeg', 'www.rainframework.com/12/07/63148589369245bcd784e26ebdac9ee8.jpeg', '', 'www.rainframework.com/12/07/t_63148589369245bcd784e26ebdac9ee8.jpeg', 1, '', '', 45113, 1, 1341800798, 0, 0);
INSERT INTO `file` VALUES(50, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/3d44fcd8124356912dda778c5411edd0.png', '', 'www.rainframework.com/12/07/t_3d44fcd8124356912dda778c5411edd0.png', 1, '', '', 9917, 1, 1341862182, 0, 0);
INSERT INTO `file` VALUES(51, 2, 'content', 'preview.png', 'www.rainframework.com/12/07/ba9cbb61bd1033723df5523bf77b709d.png', '', 'www.rainframework.com/12/07/t_ba9cbb61bd1033723df5523bf77b709d.png', 1, '', '', 9917, 1, 1341960573, 0, 0);
INSERT INTO `file` VALUES(52, 2, 'content', 'Screen Shot 2012-07-10 at 8.40.30 PM.png', 'www.rainframework.com/12/07/3f9d8192683ba0e60b397988dee3b104.png', '', 'www.rainframework.com/12/07/t_3f9d8192683ba0e60b397988dee3b104.png', 1, '', '', 9517, 1, 1342022077, 0, 0);
INSERT INTO `file` VALUES(53, 2, 'content', 'Screen Shot 2012-07-10 at 8.40.30 PM.png', 'www.rainframework.com/12/07/16f8dc2f564d64ac6de5e3764e3156a3.png', '', 'www.rainframework.com/12/07/t_16f8dc2f564d64ac6de5e3764e3156a3.png', 1, '', '', 9517, 1, 1342022089, 0, 0);
INSERT INTO `file` VALUES(54, 2, 'content', 'aw.png', 'www.rainframework.com/12/07/6337c36f462cc85125bb5ce963ab3017.png', '', 'www.rainframework.com/12/07/t_6337c36f462cc85125bb5ce963ab3017.png', 1, '', '', 9517, 1, 1342022112, 0, 0);
INSERT INTO `file` VALUES(55, 2, 'content', 'fede.jpeg', 'www.rainframework.com/12/07/eebe3005d2629fcc67bfb44569a3aefa.jpeg', '', 'www.rainframework.com/12/07/t_eebe3005d2629fcc67bfb44569a3aefa.jpeg', 1, '', '', 45113, 1, 1342022123, 0, 0);
INSERT INTO `file` VALUES(56, 2, 'content', 'fede.jpeg', 'www.rainframework.com/12/07/f0460662d38b0cd597de281b2eecc550.jpeg', '', 'www.rainframework.com/12/07/t_f0460662d38b0cd597de281b2eecc550.jpeg', 1, '', '', 45113, 1, 1342022129, 0, 0);
INSERT INTO `file` VALUES(57, 2, 'content', 'fede.jpeg', 'www.rainframework.com/12/07/7662319771adf50a46cabd4d10d298b3.jpeg', '', 'www.rainframework.com/12/07/t_7662319771adf50a46cabd4d10d298b3.jpeg', 1, '', '', 45113, 1, 1342022166, 0, 0);
INSERT INTO `file` VALUES(58, 2, 'content', 'aw.png', 'www.rainframework.com/12/07/eef071784b91af993737fd8878737e84.png', '', 'www.rainframework.com/12/07/t_eef071784b91af993737fd8878737e84.png', 1, '', '', 9517, 1, 1342022224, 0, 0);
INSERT INTO `file` VALUES(59, 2, 'content', 'Screen Shot 2012-07-11 at 12.35.01 PM.png', 'www.rainframework.com/12/07/edaed1cb5a1633a489f6ddcb90563b91.png', '', 'www.rainframework.com/12/07/t_edaed1cb5a1633a489f6ddcb90563b91.png', 1, '', '', 37952, 1, 1342025387, 0, 0);
INSERT INTO `file` VALUES(60, 2, 'content', 'fede.jpeg', 'www.rainframework.com/12/07/bc82e8436e9bacaa1e8530d5d081fd73.jpeg', '', 'www.rainframework.com/12/07/t_bc82e8436e9bacaa1e8530d5d081fd73.jpeg', 1, '', '', 45113, 1, 1342026039, 0, 0);
INSERT INTO `file` VALUES(61, 2, 'content', 'fede.jpeg', 'www.rainframework.com/12/07/5109face583a2fb946c013d3fffce287.jpeg', '', 'www.rainframework.com/12/07/t_5109face583a2fb946c013d3fffce287.jpeg', 1, '', '', 45113, 1, 1342026059, 0, 0);
INSERT INTO `file` VALUES(62, 2, 'content', 'Screen Shot 2012-07-11 at 12.35.01 PM.png', 'www.rainframework.com/12/07/eb4efdb2007fb3745d86cf6c19d4611f.png', '', 'www.rainframework.com/12/07/t_eb4efdb2007fb3745d86cf6c19d4611f.png', 1, '', '', 37952, 1, 1342028629, 0, 0);
INSERT INTO `file` VALUES(63, 2, 'content', 'Screen Shot 2012-07-11 at 12.35.01 PM.png', 'www.rainframework.com/12/07/67cafb1e53b1ba26e0799c6b0f4b0ef5.png', '', 'www.rainframework.com/12/07/t_67cafb1e53b1ba26e0799c6b0f4b0ef5.png', 1, '', '', 37952, 1, 1342028791, 0, 0);
INSERT INTO `file` VALUES(64, 2, 'content', 'Screen Shot 2012-07-11 at 12.35.01 PM.png', 'www.rainframework.com/12/07/80e5321e7924974e06ee9b9e9b6d8708.png', '', 'www.rainframework.com/12/07/t_80e5321e7924974e06ee9b9e9b6d8708.png', 1, '', '', 37952, 1, 1342030278, 0, 0);
INSERT INTO `file` VALUES(65, 2, 'content', 'Screen Shot 2012-07-11 at 12.35.01 PM.png', 'www.rainframework.com/12/07/abd83cc3bf64ad86559922f2afecf7be.png', '', 'www.rainframework.com/12/07/t_abd83cc3bf64ad86559922f2afecf7be.png', 1, '', '', 37952, 1, 1342030313, 0, 0);
INSERT INTO `file` VALUES(66, 4, 'content', 'aw.png', 'www.rainframework.com/12/07/457915f0aa235fbc18d0058b1ed3681d.png', '', 'www.rainframework.com/12/07/t_457915f0aa235fbc18d0058b1ed3681d.png', 1, '', '', 9517, 1, 1342030814, 0, 0);
INSERT INTO `file` VALUES(67, 2, 'content', 'aw.png', 'www.rainframework.com/12/07/3e2b04057b8e3d65d82e2e36b2c73055.png', '', 'www.rainframework.com/12/07/t_3e2b04057b8e3d65d82e2e36b2c73055.png', 1, '', '', 9517, 1, 1342031135, 0, 0);
INSERT INTO `file` VALUES(68, 2, 'content', 'Screen Shot 2012-07-11 at 12.35.01 PM.png', 'www.rainframework.com/12/07/3e2b04057b8e3d65d82e2e36b2c73055.png', '', 'www.rainframework.com/12/07/t_3e2b04057b8e3d65d82e2e36b2c73055.png', 1, '', '', 37952, 1, 1342031135, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE `language` (
  `lang_id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(4) NOT NULL,
  `admin_published` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`lang_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `language`
--

INSERT INTO `language` VALUES('en', 'English', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `layout`
--

CREATE TABLE `layout` (
  `layout_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `position` smallint(6) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `lock` tinyint(1) NOT NULL,
  PRIMARY KEY (`layout_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Dumping data for table `layout`
--

INSERT INTO `layout` VALUES(1, 'generic', 'generic', 0, 1, 1);
INSERT INTO `layout` VALUES(2, 'not found', 'not_found', 1, 0, 1);
INSERT INTO `layout` VALUES(3, 'home', 'home', 2, 1, 0);
INSERT INTO `layout` VALUES(4, 'forum', 'forum', 3, 1, 0);
INSERT INTO `layout` VALUES(5, 'documentation', 'doc', 3, 1, 0);
INSERT INTO `layout` VALUES(7, 'fullscreen', 'fullscreen', 5, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL COMMENT '0 => control pannel    1 => website',
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `content_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `write_access` tinyint(1) NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` VALUES(4, 1, 'website', '{URL}', 0, 0, 0, 1);
INSERT INTO `menu` VALUES(5, 1, 'configure', '{ADMIN_FILE_URL}configure', 0, 2, 3, 1);
INSERT INTO `menu` VALUES(6, 1, 'content', '{ADMIN_FILE_URL}content', 0, 3, 0, 1);
INSERT INTO `menu` VALUES(7, 1, 'user', '{ADMIN_FILE_URL}user', 0, 4, 0, 1);
INSERT INTO `menu` VALUES(1, -1, 'admin', '', 0, 0, 3, 1);
INSERT INTO `menu` VALUES(8, 1, 'dashboard', '{ADMIN_FILE_URL}dashboard', 0, 1, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

CREATE TABLE `module` (
  `module` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL COMMENT '0=> not installed 1=> installed',
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `module`
--

INSERT INTO `module` VALUES('content', 1);
INSERT INTO `module` VALUES('user', 1);
INSERT INTO `module` VALUES('comment', 1);
INSERT INTO `module` VALUES('installer', 1);
INSERT INTO `module` VALUES('rain_edit', 1);
INSERT INTO `module` VALUES('setup', 1);

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

CREATE TABLE `setting` (
  `setting` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `const` tinyint(4) NOT NULL COMMENT 'set 1 if can be only one page with this type of contents',
  PRIMARY KEY (`setting`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` VALUES('theme', 'raincms2', 'Default theme', 0);
INSERT INTO `setting` VALUES('lang_id', 'en', 'Default lang', 0);
INSERT INTO `setting` VALUES('theme_change', '0', 'Theme can be changed (1/0)', 0);
INSERT INTO `setting` VALUES('user_login', '1', 'User can login (1/0)', 0);
INSERT INTO `setting` VALUES('page_layout', '1', 'Default page layout', 0);
INSERT INTO `setting` VALUES('website_name', 'Rain', 'Site name', 1);
INSERT INTO `setting` VALUES('description', '', 'Website description', 0);
INSERT INTO `setting` VALUES('keywords', '', 'Website keywords', 0);
INSERT INTO `setting` VALUES('copyright', '', 'Copyright', 0);
INSERT INTO `setting` VALUES('time_format', '%d/%m/%Y %H:%M', 'Default time format', 0);
INSERT INTO `setting` VALUES('timezone', 'America/New_York', 'Default timezone', 0);
INSERT INTO `setting` VALUES('lang_id_admin', 'en', 'Default control panel language', 0);
INSERT INTO `setting` VALUES('email_admin', 'rainelemental@gmail.com', 'Admin email', 0);
INSERT INTO `setting` VALUES('website_url', 'http://localhost/buongiornonewyork/pro/', 'Website URL', 1);
INSERT INTO `setting` VALUES('published', '1', 'Website published', 0);
INSERT INTO `setting` VALUES('not_published_msg', '', 'Website under construction phrase (if not published)', 0);
INSERT INTO `setting` VALUES('user_register', '1', 'User can register', 0);
INSERT INTO `setting` VALUES('email_noreply', 'noreply@poisonfx.com', 'Website noreply email', 0);
INSERT INTO `setting` VALUES('registration_confirm', '1', '0 => no registration confirm necessary, 1=>admin confirm user registration, 2=>confirm by email', 0);
INSERT INTO `setting` VALUES('website_tel', '0', 'Website phone', 0);
INSERT INTO `setting` VALUES('website_address', 'x', 'Website address', 0);
INSERT INTO `setting` VALUES('theme_user', '1', 'user can change theme', 0);
INSERT INTO `setting` VALUES('space_used', '29503700', 'Space used by files', 0);
INSERT INTO `setting` VALUES('google_login', 'rainelemental', 'Google account login', 0);
INSERT INTO `setting` VALUES('google_password', 'rainelemental81x', 'Google password account', 0);
INSERT INTO `setting` VALUES('website_domain', 'www.rainframework.com', 'Website domain (necessary for Google Analytics)', 1);
INSERT INTO `setting` VALUES('space_tot', '52428800', 'Total available space', 0);
INSERT INTO `setting` VALUES('google_analytics', '1', 'Google Analytics enabled (1/0)', 0);
INSERT INTO `setting` VALUES('email_type', 'mail', 'Email type (mail/smtp)', 0);
INSERT INTO `setting` VALUES('smtp_login', '', 'SMTP login', 0);
INSERT INTO `setting` VALUES('smtp_password', '', 'password of smtp', 0);
INSERT INTO `setting` VALUES('smtp_host', '', 'email address of smtp', 0);
INSERT INTO `setting` VALUES('charset', 'utf-8', 'charset of the website', 0);
INSERT INTO `setting` VALUES('email_n_send', '30', 'n email send in one session', 0);
INSERT INTO `setting` VALUES('email_wait', '30', 'seconds wait for next send', 0);
INSERT INTO `setting` VALUES('google_analytics_refresh_time', '30', 'time to refresh the stats', 0);
INSERT INTO `setting` VALUES('last_edit_time', '1275747233', '', 0);
INSERT INTO `setting` VALUES('google_analytics_code', 'UA-5639487-13', 'google analytics code', 0);
INSERT INTO `setting` VALUES('lang_in_domain', '0', '', 0);
INSERT INTO `setting` VALUES('image_ext', 'jpg,jpeg,gif,png', '', 0);
INSERT INTO `setting` VALUES('audio_ext', 'mp3', '', 0);
INSERT INTO `setting` VALUES('video_ext', '', '', 0);
INSERT INTO `setting` VALUES('document_ext', 'doc,docx,pdf,xls,csv,xlsx,txt,ttf,rtf', '', 0);
INSERT INTO `setting` VALUES('archive_ext', 'zip,rar,gzip', '', 0);
INSERT INTO `setting` VALUES('thumbnail_default_width', '100', '', 0);
INSERT INTO `setting` VALUES('thumbnail_default_height', '100', '', 0);
INSERT INTO `setting` VALUES('thumbnail_default_is_square', '1', '', 0);
INSERT INTO `setting` VALUES('thumbnail_default_quality', '90', '', 0);
INSERT INTO `setting` VALUES('admin_max_file_size_upload', '10485760', 'max filesize upload for admin panel (5mb)', 0);
INSERT INTO `setting` VALUES('max_file_size_upload', '307200', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `theme`
--

CREATE TABLE `theme` (
  `theme_id` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `theme` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tags` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `colors` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `directory` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `date` int(11) NOT NULL,
  `author` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `author_email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `author_website` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`theme_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `theme`
--

INSERT INTO `theme` VALUES('default', 'Default', 'A simple twitter bootstrap theme easy to extend', 'Default Rain', '', 'default', 0, 'Federico Ulfo with Twitter Bootstrap', 'rainelemental@gmail.com', 'http://www.federicoulfo.it/', 0);
INSERT INTO `theme` VALUES('raincms2', 'Rain CMS 2', 'Rain CMS', 'Rain CMS', '', 'raincms2', 0, 'Federico Ulfo', 'rainelemental@gmail.com', 'http://www.federicoulfo.it/', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `activation_code` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `lang_id` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `sex` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birth_date` int(11) DEFAULT NULL,
  `firstname` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `company` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `zip` int(5) DEFAULT NULL,
  `city` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `prov` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `tel` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `mobile` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `web` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `web2` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `is_registered` tinyint(1) NOT NULL,
  `data_reg` int(11) NOT NULL DEFAULT '0',
  `data_login` int(11) NOT NULL DEFAULT '0',
  `last_ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `mailing_list` tinyint(1) NOT NULL,
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` VALUES(1, 'demo', 'demo@demo.com', '10bfb1e2d9110f9874786743d78a9c77', '65157', '', '', NULL, NULL, 'demo', 'demo', '', '', NULL, '', '', '', '', '', '', '', '', '', 3, 1, 0, 1342136674, '', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `usergroup`
--

CREATE TABLE `usergroup` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `nuser` int(11) NOT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `usergroup`
--

INSERT INTO `usergroup` VALUES(1, 0, 'gruppo', 0, 0);
INSERT INTO `usergroup` VALUES(2, 0, 'New Group', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `usergroup_user`
--

CREATE TABLE `usergroup_user` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `usergroup_user`
--

INSERT INTO `usergroup_user` VALUES(1, 103);
INSERT INTO `usergroup_user` VALUES(100, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_localization`
--

CREATE TABLE `user_localization` (
  `user_localization_id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `content_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `os` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `browser` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `time_first_click` int(11) NOT NULL,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `country_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `region_code` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `region_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `city_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `zip` int(11) NOT NULL,
  `latitude` int(11) NOT NULL,
  `longitude` int(11) NOT NULL,
  `timezone_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `gmt_offset` int(11) NOT NULL,
  PRIMARY KEY (`user_localization_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=239 ;

--
-- Dumping data for table `user_localization`
--

INSERT INTO `user_localization` VALUES(238, 'ec410631168ecda6fb4615d6715a5ea1', 1, 0, 'demo', '', 'index.php', '/RainCMS/', 2, 1342136676, 'Macintosh', 'e 3', 1342022518, '', '', '', '', '', 0, 0, 0, '', 0);
