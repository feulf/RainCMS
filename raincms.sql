-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 10, 2012 at 05:37 PM
-- Server version: 5.5.25
-- PHP Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

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
  `file` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `load_area` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `content_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `position` smallint(6) NOT NULL,
  PRIMARY KEY (`block_id`),
  KEY `page_id` (`layout_id`),
  KEY `in_content_id` (`in_content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=26 ;

--
-- Dumping data for table `block`
--

INSERT INTO `block` (`block_id`, `block_type_id`, `global`, `layout_id`, `type_id`, `in_content_id`, `file`, `load_area`, `template`, `content_id`, `file_id`, `position`) VALUES
(1, 1, 1, 0, 0, 0, '', 'right', 'content', 3, 0, 0),
(4, 1, 0, 0, 0, 0, '', 'left', 'content', 0, 0, 0),
(5, 1, 0, 0, 0, 0, '', 'left', 'content', 0, 0, 0),
(6, 1, 0, 0, 0, 0, '', 'left', 'content', 0, 0, 0),
(7, 1, 0, 0, 0, 0, '', 'left', 'content', 0, 0, 0),
(8, 1, 0, 0, 0, 0, '', 'left', 'content', 0, 0, 0),
(9, 1, 1, 0, 0, 0, '', 'left', 'content', 0, 0, 0),
(12, 0, 1, 0, 0, 0, '', 'left', 'content', 9, 0, 0),
(15, 1, 1, 0, 0, 0, '', 'left', 'content', 14, 0, 0),
(25, 1, 1, 0, 0, 0, '', 'left', 'content', 40, 0, 1);

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

-- --------------------------------------------------------

--
-- Table structure for table `block_type`
--

CREATE TABLE `block_type` (
  `block_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `template` varchar(50) NOT NULL,
  `module` varchar(30) NOT NULL,
  PRIMARY KEY (`block_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `block_type`
--

INSERT INTO `block_type` (`block_type_id`, `type`, `description`, `template`, `module`) VALUES
(1, 'Content', 'default text block', 'content/block.', 'content'),
(2, 'news', '', 'block', 'news');

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

INSERT INTO `block_type_option` (`block_type_id`, `name`, `note`, `field_type`, `validation`, `command`, `param`, `layout`, `multilanguage`, `position`, `published`) VALUES
(1, 'content_id', 'is the id of the related content', 'text', 'numeric', '', '', 'content', 0, 1, 1);

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
  FULLTEXT KEY `search` (`title`,`subtitle`,`tags`,`content`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `title_2` (`title`,`subtitle`,`tags`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`content_id`, `lang_id`, `type_id`, `layout_id`, `menu_id`, `path`, `title`, `subtitle`, `tags`, `content`, `summary`, `date`, `template`, `read_access`, `published`, `ncomment`, `last_edit_time`, `changefreq`, `priority`, `extra1`, `extra2`, `extra3`, `extra4`, `extra5`, `extra6`, `extra7`, `extra8`) VALUES
(2, 'en', 2, 3, 2, '', 'Home', '', '', '<p>123<img><img><img>4<img></p><div class="preview"><img></div>', '', 1353897123, '', 0, 1, 0, 1353897123, 2, 0.7, '', '', '', '', '', '', '', ''),
(3, 'en', 1, 0, 0, '', 'Content Right', '', '', 'Hey this is another content', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(38, 'en', 1, 0, 0, '', 'test', '', '', '123', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(39, 'en', 21, 1, 0, 'Blog/2012/12/02/Change-blog-title2/', 'Change blog title2', '', '', '<p>test test test</p>', '', 1354463091, '', 0, 1, 0, 1354837317, 2, 0.7, '', '', '', '', '', '', '', ''),
(37, 'en', 1, 0, 0, '', 'TEST', '', '', '123', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(34, 'en', 1, 0, 0, '', 'test', '', '', '123', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(35, 'en', 1, 0, 0, '', '123', '', '', 'abc', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(36, 'en', 1, 0, 0, '', 'fede', '', '', 'rico', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(13, 'en', 10, 1, 2, 'News/', 'News', '', '', '', '', 1342558594, 'list', 0, 1, 0, 1348286143, 2, 0.7, '', '', '', '', '', '', '', ''),
(5, 'en', 1, 0, 0, '', 'new block', '', '', 'test', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(6, 'en', 1, 0, 0, '', 'left content', '', '', 'this is the content', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(7, 'en', 1, 0, 0, '', 'ABC', '', '', 'xxx', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(8, 'en', 1, 0, 0, '', 'Another Content', '', '', 'Insert the content here', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(9, 'en', 1, 0, 0, '', 'New Side Block', '', '', 'Test', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(14, 'en', 1, 0, 0, '', 'Block test', '', '', 'This block is a test', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(15, 'en', 1, 0, 0, '', '111', '', '', '123', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(16, 'en', 1, 0, 0, '', 'abc', '', '', 'abc', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(17, 'en', 1, 0, 0, '', 'News', '', '', 'test', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(18, 'en', 1, 0, 0, '', 'Test News', '', '', 'OK', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(19, 'en', 1, 0, 0, '', '', '', '', '', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(20, 'en', 1, 0, 0, '', '', '', '', '', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(21, 'en', 1, 0, 0, '', '', '', '', '', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(22, 'en', 1, 0, 0, '', 'Test news', '', '', 'It should be a news', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(23, 'en', 1, 0, 0, '', 'asodiuasiudasda', '', '', 'asdsadasdas', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(24, 'en', 1, 0, 0, '', 'asdasd', '', '', 'asdasd', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(30, 'en', 20, 1, 2, 'Blog/', 'Blog', '', '', '', '', 1343848867, 'list', 0, 1, 0, 1354421815, 2, 0.7, '', '', '', '', '', '', '', ''),
(31, 'en', 1, 0, 0, '', 'News', '', '', 'here goes the news', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(32, 'en', 1, 0, 0, '', 'test', '', '', 'test', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(33, 'en', 1, 0, 0, '', 'test', '', '', 'test2', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(28, 'en', 11, 1, 0, 'News/2012/08/01/first-news/', 'first news', '', '', '<p>test</p>', '', 1354328395, '', 0, 1, 0, 1354328395, 2, 0.7, '', '', '', '', '', '', '', ''),
(29, 'en', 11, 1, 0, 'News/2012/08/01/news2/', 'news2', '', '', '', '', 1343846812, 'news', 0, 1, 0, 1343846812, 2, 0.7, '', '', '', '', '', '', '', ''),
(40, 'en', 1, 0, 0, '', 'asodiuaosd', '', '', 'asdasdas', '', 0, '', 0, 1, 0, 0, 0, 0.0, '', '', '', '', '', '', '', ''),
(41, 'en', 1, 1, 0, 'Content-name/', 'Content name', '', '', '', '', 1354341398, 'content.content', 0, 1, 0, 1354341398, 2, 0.7, '', '', '', '', '', '', '', '');

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

INSERT INTO `content_comment` (`comment_id`, `lang_id`, `content_id`, `date`, `comment`, `user_id`, `name`, `email`, `website`, `ip`, `code`, `published`) VALUES
(125, 'en', 143, 1325353205, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(126, 'en', 143, 1325353210, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(127, 'en', 143, 1325353211, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(128, 'en', 143, 1325353212, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(129, 'en', 143, 1325353222, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(130, 'en', 143, 1325353225, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(131, 'en', 143, 1325353237, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(132, 'en', 143, 1325353276, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(133, 'en', 143, 1325353278, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(134, 'en', 143, 1325353278, 0x617364617364736164, 1, '', '', '', '127.0.0.1', '', 1),
(135, 'en', 143, 1325358478, 0x6369616f, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(136, 'en', 143, 1325358607, 0x68656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(137, 'en', 143, 1325358612, 0x68656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(138, 'en', 143, 1325358630, 0x6369616f, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(139, 'en', 143, 1325358670, 0x68657920636f6d65207661, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(140, 'en', 143, 1325358684, 0x617364617364617364, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(141, 'en', 143, 1325358698, 0x617364617364, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(142, 'en', 143, 1325358725, 0x616263, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(143, 'en', 143, 1325358755, 0x61626320313233, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(144, 'en', 143, 1325358796, 0x61626320313233, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(145, 'en', 143, 1325358799, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(146, 'en', 143, 1325358815, 0x617364, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(147, 'en', 143, 1325358823, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(148, 'en', 143, 1325358833, 0x617364, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(149, 'en', 143, 1325358963, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(150, 'en', 143, 1325358967, 0x74657374, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(151, 'en', 143, 1325359894, 0x496e746572657373616e7465, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(152, 'en', 143, 1325359896, 0x496e746572657373616e7465, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(153, 'en', 143, 1325359934, 0x74657374, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(154, 'en', 143, 1325359947, 0x68656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(155, 'en', 143, 1325359970, 0x617364, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(156, 'en', 143, 1325359974, 0x617364, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(157, 'en', 143, 1325359979, 0x717765, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(158, 'en', 143, 1325359991, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(159, 'en', 143, 1325359998, 0x313233, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(160, 'en', 143, 1325360016, 0x616263, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(161, 'en', 143, 1325360056, 0x616263, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(162, 'en', 143, 1325360087, 0x486579, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(163, 'en', 143, 1325360100, 0x48656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(164, 'en', 143, 1325360546, 0x7777772e7261696e74706c2e636f6d20626c6120626c61207777772e676f6f676c652e636f6d, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(165, 'en', 143, 1325569318, 0x68656c6c6f, 1, 'Rain', '', '', '127.0.0.1', '', 1),
(166, 'en', 143, 1325569324, 0x6369616f, 1, 'Rain', '', '', '127.0.0.1', '', 1);

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

INSERT INTO `content_rel` (`content_id`, `rel_id`, `position`, `rel_type`) VALUES
(29, 13, 2, 'parent'),
(13, 0, 2, 'parent'),
(2, 0, 0, 'parent'),
(30, 0, 3, 'parent'),
(28, 13, 1, 'parent'),
(39, 30, 1, 'parent'),
(41, 0, 4, 'parent');

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
  `easy_create` tinyint(1) NOT NULL COMMENT 'If 1 this content can be added from the front end of the website. Sections should be created from the backend, so set 0 to section',
  `path_type` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '{title}:title, {content_id}: content_id {$y}:year, {$m}:month, {$d}: day',
  `path_short` tinyint(4) NOT NULL COMMENT '1 for short path that doesn''t include categories, ex (product/usb-1) 0: for complete path ex (products/memory/usb-1)',
  `changefreq` tinyint(1) NOT NULL COMMENT '0 => always     1 => hourly     2 => daily     3 => weekly     4 => monthly     5 => yearly     6 => never',
  `priority` decimal(2,1) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `content_type`
--

INSERT INTO `content_type` (`type_id`, `type`, `icon`, `lang_index`, `module`, `action`, `template_index`, `published`, `multilanguage`, `comment_enabled`, `link_other_content`, `linked_copy`, `image_enabled`, `audio_enabled`, `video_enabled`, `document_enabled`, `archive_enabled`, `file_enabled`, `tags_enabled`, `order_by`, `searchable`, `cache`, `unique`, `write_access`, `easy_create`, `path_type`, `path_short`, `changefreq`, `priority`) VALUES
(1, 'content', 'content.gif', 'content', 'content', '', 'content/', 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 'r.position', 1, -1, 0, 0, 0, '{title}', 0, 2, 0.7),
(10, 'news_section', 'news section.gif', 'news', 'news', '', 'news/section.', 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 'r.position', 1, -1, 1, 0, 0, '{title}', 0, 2, 0.7),
(11, 'news', 'news.gif', 'news', 'news', '', 'news/news.', 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 'r.position', 1, -1, 0, 0, 1, '{y}/{m}/{d}/{title}', 0, 2, 0.7),
(20, 'blog_section', 'blog section.gif', 'blog', 'blog', '', 'blog/section.', 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 'r.position', 1, -1, 1, 0, 0, '{title}', 0, 2, 0.7),
(21, 'blog', 'blog.gif', 'blog', 'blog', '', 'blog/blog.', 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 'r.position', 1, -1, 0, 0, 1, '{y}/{m}/{d}/{title}', 0, 2, 0.7),
(3, 'sitemap', 'sitemap.gif', 'news', 'news', '', 'sitemap/sitemap.html', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, -1, 1, 0, 0, '{title}', 0, 2, 0.7),
(2, 'home', 'home.gif', 'home', 'home', '', 'home', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 1, 0, 0, '{$title}', 0, 0, 0.0),
(5, 'search', 'search.gif', 'search', 'search', '', 'search/search.html', 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, -1, 1, 0, 0, '{title}', 0, 0, 0.7);

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

INSERT INTO `content_type_field` (`type_id`, `name`, `note`, `field_type`, `validation`, `command`, `param`, `layout`, `multilanguage`, `position`, `published`) VALUES
(1, 'content', '', 'word', 'required', '', '', 'row', 1, 3, 1),
(11, 'content', '', 'word', 'required', '', '', 'row', 1, 3, 1),
(21, 'content', '', 'word', 'required', '', '', 'row', 1, 3, 1),
(21, 'cover', '', 'cover', '', '', 'tw=120&th=120&w=800', '', 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `content_type_setting`
--

CREATE TABLE `content_type_setting` (
  `type_id` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `note` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `field_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `validation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `command` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `param` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `layout` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`type_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `content_type_setting`
--

INSERT INTO `content_type_setting` (`type_id`, `name`, `note`, `field_type`, `validation`, `command`, `param`, `layout`, `position`) VALUES
(10, 'news_per_page', 'number of news per page', 'text', 'numeric,minvalue=1', '', '', '', 1);

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

INSERT INTO `content_type_tree` (`type_id`, `parent_id`) VALUES
(1, 0),
(1, 1),
(2, 0),
(5, 0),
(10, 0),
(10, 1),
(11, 10),
(20, 0),
(21, 10),
(21, 20);

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE `file` (
  `file_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `filepath` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ext` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `type_id` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:IMAGE  2:AUDIO  3:VIDEO  4:DOCUMENT  5:ARCHIVE',
  `width` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `height` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `size` int(11) NOT NULL,
  `last_edit_time` int(11) NOT NULL,
  `read_access` tinyint(1) NOT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

--
-- Dumping data for table `file`
--

INSERT INTO `file` (`file_id`, `name`, `filepath`, `ext`, `thumb`, `subtitle`, `description`, `type_id`, `width`, `height`, `size`, `last_edit_time`, `read_access`) VALUES
(1, 'jhane-barnes.png', 'raincms.com/12/09/195773930d5e157267175bf1449f875a.png', 'png', 'raincms.com/12/09/t_195773930d5e157267175bf1449f875a.png', '', '', 1, '510', '347', 277904, 1348286111, 0),
(2, 'stock-photo-11655246-sewing-hands.png', 'raincms.com/12/09/1e52b6e0554094a1b66152bed9e15f31.png', 'png', 'raincms.com/12/09/t_1e52b6e0554094a1b66152bed9e15f31.png', '', '', 1, '510', '347', 263619, 1348286143, 0),
(3, 'newyork.png', 'raincms.com/12/09/5f6a4b96ef677bdaba2adbb1d5122c4b.png', 'png', 'raincms.com/12/09/t_5f6a4b96ef677bdaba2adbb1d5122c4b.png', '', '', 1, '1000', '666', 1014433, 1348323819, 0),
(4, 'newyork.png', 'raincms.com/12/09/f5125a0e8f523ed8129e43455f3596de.png', 'png', 'raincms.com/12/09/t_f5125a0e8f523ed8129e43455f3596de.png', '', '', 1, '1000', '666', 1014433, 1348323842, 0),
(15, '0013729c00250bb7b76907.jpeg', 'raincms.com/12/12/bf5c1f9062cdef5c5adf2bba963f1e09.jpeg', 'jpeg', 'raincms.com/12/12/t_bf5c1f9062cdef5c5adf2bba963f1e09.jpeg', '', '', 1, '599', '427', 80027, 1354837317, 0),
(6, 'IMG_1058.JPG', 'raincms.com/12/09/4dfba1debbfdf735ce2b56d881fb4960.jpg', 'jpg', 'raincms.com/12/09/t_4dfba1debbfdf735ce2b56d881fb4960.jpg', '', '', 1, '600', '800', 134383, 1348323943, 0),
(7, 'IMG_0825.JPG', 'raincms.com/12/09/9a5bf28783ce6d519287d4c47a6bc5ad.jpg', 'jpg', 'raincms.com/12/09/t_9a5bf28783ce6d519287d4c47a6bc5ad.jpg', '', '', 1, '597', '800', 70255, 1348329131, 0),
(9, 'IMG_0503.JPG', 'raincms.com/12/09/e0637c8715c4d4f14bb937a2f837f76d.jpg', 'jpg', 'raincms.com/12/09/t_e0637c8715c4d4f14bb937a2f837f76d.jpg', '', '', 1, '640', '480', 73165, 1348329940, 0),
(10, 'IMG_0590.JPG', 'raincms.com/12/09/d65dac1962ea933ea718fa453ad22ed1.jpg', 'jpg', 'raincms.com/12/09/t_d65dac1962ea933ea718fa453ad22ed1.jpg', '', '', 1, '640', '480', 68618, 1348329948, 0),
(11, 'IMG_1213.JPG', 'raincms.com/12/09/9a568d5d7dbeb1486ffe653c893aef10.jpg', 'jpg', 'raincms.com/12/09/t_9a568d5d7dbeb1486ffe653c893aef10.jpg', '', '', 1, '600', '800', 144283, 1348330025, 0),
(12, 'image.jpeg', 'raincms.com/12/12/89b6c422e5a90cb325bf9ee30cf89e15.jpeg', 'jpeg', 'raincms.com/12/12/t_89b6c422e5a90cb325bf9ee30cf89e15.jpeg', '', '', 1, '1920', '1200', 221525, 1354421815, 0),
(13, 'image.jpeg', 'raincms.com/12/12/5e6f56b3ad3f68cdea708d11d4cd9e75.jpeg', 'jpeg', 'raincms.com/12/12/t_5e6f56b3ad3f68cdea708d11d4cd9e75.jpeg', '', '', 1, '800', '500', 221525, 1354463089, 0);

-- --------------------------------------------------------

--
-- Table structure for table `file_rel`
--

CREATE TABLE `file_rel` (
  `file_id` int(11) NOT NULL,
  `rel_id` int(11) NOT NULL,
  `module` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `rel_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'FILE_LIST:0, FILE_EMBED:1, FILE_BLOCK:2, FILE_COVER:3',
  `position` int(11) NOT NULL,
  PRIMARY KEY (`file_id`,`rel_id`,`rel_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `file_rel`
--

INSERT INTO `file_rel` (`file_id`, `rel_id`, `module`, `rel_type`, `position`) VALUES
(1, 2, 'content', 0, 0),
(2, 13, 'content', 0, 0),
(3, 0, 'content', 0, 0),
(4, 0, 'content', 0, 0),
(5, 0, 'content', 0, 0),
(6, 0, 'content', 0, 0),
(7, 2, '', 0, 1),
(0, 2, '', 1, 0),
(12, 30, '', 0, 0),
(0, 39, '', 1, 0),
(15, 39, '', 0, 0),
(9, 39, '', 2, 0);

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

INSERT INTO `language` (`lang_id`, `language`, `published`, `admin_published`, `position`) VALUES
('en', 'English', 1, 1, 1);

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

INSERT INTO `layout` (`layout_id`, `name`, `template`, `position`, `published`, `lock`) VALUES
(1, 'generic', 'generic', 0, 1, 1),
(2, 'not found', 'not_found', 1, 0, 1),
(3, 'home', 'home', 2, 1, 0),
(4, 'forum', 'forum', 3, 1, 0),
(5, 'documentation', 'doc', 3, 1, 0),
(7, 'fullscreen', 'fullscreen', 5, 1, 0);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `parent_id`, `name`, `link`, `content_id`, `position`, `write_access`, `published`) VALUES
(4, 1, 'website', '{URL}', 0, 0, 0, 1),
(5, 1, 'configure', '{ADMIN_FILE_URL}configure', 0, 2, 3, 1),
(6, 1, 'content', '{ADMIN_FILE_URL}content', 0, 3, 0, 1),
(8, 1, 'user', '{ADMIN_FILE_URL}user', 0, 5, 0, 1),
(1, -1, 'admin', '', 0, 0, 3, 1),
(9, 1, 'dashboard', '{ADMIN_FILE_URL}dashboard', 0, 1, 3, 1),
(7, 1, 'file', '{ADMIN_FILE_URL}file', 0, 4, 3, 1);

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

INSERT INTO `module` (`module`, `published`) VALUES
('content', 1),
('user', 1),
('comment', 1),
('installer', 1),
('rain_edit', 1),
('news', 1),
('setup', 1),
('blog', 1),
('sitemap', 1),
('home', 1),
('search', 1);

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

INSERT INTO `setting` (`setting`, `value`, `description`, `const`) VALUES
('theme', 'default', 'Default theme', 0),
('lang_id', 'en', 'Default lang', 0),
('theme_change', '0', 'Theme can be changed (1/0)', 0),
('user_login', '1', 'User can login (1/0)', 0),
('page_layout', '1', 'Default page layout', 0),
('website_name', 'Rain', 'Site name', 1),
('description', '', 'Website description', 0),
('keywords', '', 'Website keywords', 0),
('copyright', '', 'Copyright', 0),
('time_format', '%d/%m/%Y %H:%M', 'Default time format', 0),
('timezone', 'America/New_York', 'Default timezone', 0),
('lang_id_admin', 'en', 'Default control panel language', 0),
('email_admin', 'hi@raintm.com', 'Admin email', 0),
('website_url', 'http://localhost/buongiornonewyork/pro/', 'Website URL', 1),
('published', '1', 'Website published', 0),
('not_published_msg', '', 'Website under construction phrase (if not published)', 0),
('user_register', '1', 'User can register', 0),
('email_noreply', 'noreply@raincms.com', 'Website noreply email', 0),
('registration_confirm', '1', '0 => no registration confirm necessary, 1=>admin confirm user registration, 2=>confirm by email', 0),
('website_tel', '0', 'Website phone', 0),
('website_address', 'x', 'Website address', 0),
('theme_user', '1', 'user can change theme', 0),
('space_used', '33384745', 'Space used by files', 0),
('google_login', 'raincms', 'Google account login', 0),
('google_password', 'ilovegold81', 'Google password account', 0),
('website_domain', 'raincms.com', 'Website domain (necessary for Google Analytics)', 1),
('space_tot', '52428800', 'Total available space', 0),
('google_analytics', '1', 'Google Analytics enabled (1/0)', 0),
('email_type', 'mail', 'Email type (mail/smtp)', 0),
('smtp_login', '', 'SMTP login', 0),
('smtp_password', '', 'password of smtp', 0),
('smtp_host', '', 'email address of smtp', 0),
('charset', 'utf-8', 'charset of the website', 0),
('email_n_send', '30', 'n email send in one session', 0),
('email_wait', '30', 'seconds wait for next send', 0),
('google_analytics_refresh_time', '30', 'time to refresh the stats', 0),
('last_edit_time', '1275747233', '', 0),
('google_analytics_code', 'UA-5639487-13', 'google analytics code', 0),
('lang_in_domain', '0', '', 0),
('image_ext', 'jpg,jpeg,gif,png', '', 0),
('audio_ext', 'mp3', '', 0),
('video_ext', '', '', 0),
('document_ext', 'doc,docx,pdf,xls,csv,xlsx,txt,ttf,rtf', '', 0),
('archive_ext', 'zip,rar,gzip', '', 0),
('image_sizes', '800x600', 'allowed sizes for images', 0),
('image_quality', '90', 'quality of the image', 0),
('max_file_size_upload', '307200', '', 0),
('thumbnail_size', '180x150', 'size to create thumbnail when upload', 0);

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

INSERT INTO `theme` (`theme_id`, `theme`, `description`, `tags`, `colors`, `directory`, `date`, `author`, `author_email`, `author_website`, `published`) VALUES
('default', 'Default', 'A simple twitter bootstrap theme easy to extend', 'Default Rain', '', 'default', 0, 'Federico Ulfo with Twitter Bootstrap', 'rainelemental@gmail.com', 'http://www.federicoulfo.it/', 0);

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

INSERT INTO `user` (`user_id`, `name`, `email`, `password`, `salt`, `activation_code`, `lang_id`, `sex`, `birth_date`, `firstname`, `lastname`, `company`, `address`, `zip`, `city`, `prov`, `country`, `state`, `tel`, `mobile`, `fax`, `web`, `web2`, `status`, `is_registered`, `data_reg`, `data_login`, `last_ip`, `mailing_list`, `note`) VALUES
(1, 'demo', 'demo@demo.com', '61a66f7f0fec61836722d726c6dcafb1', '79816', '', '', NULL, NULL, 'demo', 'demo', '', '', NULL, '', '', '', '', '', '', '', '', '', 3, 1, 0, 1355099107, '', 0, '');

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

INSERT INTO `usergroup_user` (`group_id`, `user_id`) VALUES
(1, 103),
(100, 1);

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=254 ;
