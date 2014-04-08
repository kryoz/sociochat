CREATE TABLE IF NOT EXISTS `activations` (`id` INT(11) NOT NULL AUTO_INCREMENT, `email` VARCHAR(50) NOT NULL, `code` VARCHAR(64) NOT NULL, `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `used` TINYINT(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `idx_email_and_code` (`email`, `code`, `timestamp`))
	ENGINE = InnoDB
	DEFAULT CHARSET =utf8;
CREATE TABLE IF NOT EXISTS `sessions` (`id` INT(11) NOT NULL AUTO_INCREMENT, `session_id` VARCHAR(32) NOT NULL, `access` DATETIME NOT NULL, `user_id` INT(11) NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `session_id` (`session_id`), KEY `access` (`access`), KEY `user_id` (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `users` (`id` INT(11) NOT NULL AUTO_INCREMENT, `social_token` VARCHAR(128) DEFAULT NULL, `email` VARCHAR(50) DEFAULT NULL, `password` VARCHAR(60) DEFAULT NULL, `date_register` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `chat_id` VARCHAR(32) NOT NULL DEFAULT '1', PRIMARY KEY (`id`), KEY `email` (`email`), KEY `chat_id` (`chat_id`), KEY `social_token` (`social_token`))
	ENGINE = InnoDB
	DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `user_blacklist` (`id` INT(11) NOT NULL AUTO_INCREMENT, `user_id` INT(11) NOT NULL, `ignored_user_id` INT(11) NOT NULL, PRIMARY KEY (`id`), KEY `user_id` (`user_id`, `ignored_user_id`)
)
	ENGINE =InnoDB
	DEFAULT CHARSET =utf8;

CREATE TABLE IF NOT EXISTS `user_properties` (
	`id`            INT(11)     NOT NULL AUTO_INCREMENT,
	`user_id`       INT(11)     NOT NULL,
	`name`          VARCHAR(20) NOT NULL,
	`sex`           TINYINT(4)  NOT NULL,
	`tim`           TINYINT(4)  NOT NULL,
	`notifications` TEXT,
	PRIMARY KEY (`id`),
	UNIQUE KEY `user_id` (`user_id`),
	UNIQUE KEY `name` (`name`)
)
	ENGINE = InnoDB
	DEFAULT CHARSET =utf8;
