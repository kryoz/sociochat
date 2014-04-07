CREATE TABLE IF NOT EXISTS `activations` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `email` varchar(50) NOT NULL,  `code` varchar(64) NOT NULL,  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,  `used` tinyint(1) NOT NULL DEFAULT '0',  PRIMARY KEY (`id`),  KEY `idx_email_and_code` (`email`,`code`,`timestamp`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;CREATE TABLE IF NOT EXISTS `sessions` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `session_id` varchar(32) NOT NULL,  `access` datetime NOT NULL,  `user_id` int(11) NOT NULL,  PRIMARY KEY (`id`),  UNIQUE KEY `session_id` (`session_id`),  KEY `access` (`access`),  KEY `user_id` (`user_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2797 ;CREATE TABLE IF NOT EXISTS `users` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `social_token` varchar(128) DEFAULT NULL,  `email` varchar(50) DEFAULT NULL,  `password` varchar(60) DEFAULT NULL,  `date_register` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,  `chat_id` varchar(32) NOT NULL DEFAULT '1',  PRIMARY KEY (`id`),  KEY `email` (`email`),  KEY `chat_id` (`chat_id`),  KEY `social_token` (`social_token`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2750 ;CREATE TABLE IF NOT EXISTS `user_blacklist` (  `id` int(11) NOT NULL AUTO_INCREMENT,  `user_id` int(11) NOT NULL,  `ignored_user_id` int(11) NOT NULL,  PRIMARY KEY (`id`),  KEY `user_id` (`user_id`,`ignored_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=238 ;

CREATE TABLE IF NOT EXISTS `user_properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `sex` tinyint(4) NOT NULL,
  `tim` tinyint(4) NOT NULL,
  `notifications` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2746 ;
