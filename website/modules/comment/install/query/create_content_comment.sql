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
