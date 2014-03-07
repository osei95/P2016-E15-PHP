SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DELIMITER $$
CREATE DEFINER=`erwamartin`@`localhost` FUNCTION `get_distance_gps_points`(lat1 DOUBLE, lng1 DOUBLE, lat2 DOUBLE, lng2 DOUBLE) RETURNS double
BEGIN
    DECLARE rlo1 DOUBLE;
    DECLARE rla1 DOUBLE;
    DECLARE rlo2 DOUBLE;
    DECLARE rla2 DOUBLE;
    DECLARE dlo DOUBLE;
    DECLARE dla DOUBLE;
    DECLARE a DOUBLE;
    
    SET rlo1 = RADIANS(lng1);
    SET rla1 = RADIANS(lat1);
    SET rlo2 = RADIANS(lng2);
    SET rla2 = RADIANS(lat2);
    SET dlo = (rlo2 - rlo1) / 2;
    SET dla = (rla2 - rla1) / 2;
    SET a = SIN(dla) * SIN(dla) + COS(rla1) * COS(rla2) * SIN(dlo) * SIN(dlo);
    RETURN (6378137 * 2 * ATAN2(SQRT(a), SQRT(1 - a)));
END$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_shortname` varchar(45) NOT NULL,
  `activity_longname` varchar(45) NOT NULL,
  `activity_smallpicture` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

CREATE TABLE IF NOT EXISTS `appearance` (
  `appearance_id` int(11) NOT NULL,
  `appearance_name` varchar(45) NOT NULL,
  PRIMARY KEY (`appearance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `body` (
  `user_id` int(11) NOT NULL,
  `body_date` varchar(45) NOT NULL,
  `body_weight` int(11) DEFAULT NULL,
  `body_height` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`body_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `cities_list` (
  `city_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `city_departement` varchar(3) DEFAULT NULL,
  `city_country` varchar(255) DEFAULT NULL,
  `city_slug` varchar(255) DEFAULT NULL,
  `city_name` varchar(45) DEFAULT NULL,
  `city_full_name` varchar(45) DEFAULT NULL,
  `city_postcode` varchar(255) DEFAULT NULL,
  `city_lng` float DEFAULT NULL,
  `city_lat` float DEFAULT NULL,
  PRIMARY KEY (`city_id`),
  UNIQUE KEY `ville_slug` (`city_slug`),
  KEY `ville_departement` (`city_departement`),
  KEY `ville_nom` (`city_name`),
  KEY `ville_nom_reel` (`city_full_name`),
  KEY `ville_code_postal` (`city_postcode`),
  KEY `ville_longitude_latitude_deg` (`city_lng`,`city_lat`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=36569 ;

CREATE TABLE IF NOT EXISTS `following` (
  `following_from` int(11) NOT NULL,
  `following_to` int(11) NOT NULL,
  PRIMARY KEY (`following_from`,`following_to`),
  KEY `following_to_idx` (`following_to`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `goal` (
  `goal_id` int(11) NOT NULL AUTO_INCREMENT,
  `goal_from` int(11) NOT NULL,
  `goal_to` int(11) NOT NULL,
  `goal_unit` varchar(10) NOT NULL,
  `goal_value` int(11) NOT NULL,
  `goal_achievement` int(11) NOT NULL DEFAULT '0',
  `goal_date` int(11) NOT NULL,
  `goal_deadline` int(11) NOT NULL,
  `goal_accepted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`goal_id`),
  KEY `goal_from_idx` (`goal_from`),
  KEY `goal_to_idx` (`goal_to`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;
CREATE TABLE IF NOT EXISTS `goal_infos` (
`goal_id` int(11)
,`user_from_firstname` varchar(45)
,`user_from_lastname` varchar(45)
,`user_from_username` varchar(20)
,`user_from_gender` int(11)
,`user_from_id` int(11)
,`user_to_id` int(11)
,`goal_unit` varchar(10)
,`goal_value` int(11)
,`goal_achievement` int(11)
,`goal_date` int(11)
,`goal_deadline` int(11)
,`goal_accepted` int(1)
);
CREATE TABLE IF NOT EXISTS `input` (
  `input_id` int(11) NOT NULL AUTO_INCREMENT,
  `input_shortname` varchar(15) NOT NULL,
  `input_longname` varchar(45) NOT NULL,
  `input_smallpicture` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`input_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

CREATE TABLE IF NOT EXISTS `message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `message_from` int(11) NOT NULL,
  `message_to` int(11) NOT NULL,
  `message_content` text,
  `message_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `message_from_idx` (`message_from`),
  KEY `message_to_idx` (`message_to`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=132 ;

CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `news_from` int(11) NOT NULL,
  `news_to` varchar(45) NOT NULL DEFAULT 'all',
  `news_type` varchar(45) NOT NULL,
  `news_content` text,
  `news_date` int(11) NOT NULL,
  PRIMARY KEY (`news_id`),
  KEY `news_user_id_idx` (`news_from`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3888 ;
CREATE TABLE IF NOT EXISTS `news_infos` (
`news_id` int(11)
,`user_from_id` int(11)
,`user_from_firstname` varchar(45)
,`user_from_lastname` varchar(45)
,`user_from_username` varchar(20)
,`user_from_gender` int(11)
,`user_to_id` varchar(45)
,`news_type` varchar(45)
,`news_content` text
,`news_date` int(11)
,`news_supports` bigint(21)
);
CREATE TABLE IF NOT EXISTS `notification` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(45) NOT NULL,
  `notification_content` text NOT NULL,
  `notification_seen` int(11) NOT NULL DEFAULT '0',
  `notification_from` int(11) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `notification_user_id_idx` (`user_id`),
  KEY `notification_from_idx` (`notification_from`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=65 ;

CREATE TABLE IF NOT EXISTS `relationship` (
  `request_from` int(11) NOT NULL,
  `request_to` int(11) NOT NULL,
  `request_state` int(11) NOT NULL,
  `request_time` int(15) NOT NULL,
  PRIMARY KEY (`request_from`,`request_to`),
  KEY `request_to_idx` (`request_to`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `relationship_infos` (
`user_from_firstname` varchar(45)
,`user_from_lastname` varchar(45)
,`user_from_username` varchar(20)
,`user_from_gender` int(11)
,`request_from` int(11)
,`request_to` int(11)
,`request_state` int(11)
,`request_time` int(15)
);
CREATE TABLE IF NOT EXISTS `sport` (
  `sport_id` int(11) NOT NULL,
  `sport_name` varchar(45) DEFAULT NULL,
  `sport_smallpicture` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`sport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `support` (
  `user_id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`news_id`),
  KEY `support_news_id_idx` (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `temperament` (
  `temperament_id` int(11) NOT NULL AUTO_INCREMENT,
  `temperament_name` varchar(45) NOT NULL,
  PRIMARY KEY (`temperament_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_username` varchar(20) NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `user_password` binary(16) DEFAULT NULL,
  `user_gender` int(11) NOT NULL,
  `user_key` varchar(20) NOT NULL,
  `user_firstname` varchar(45) NOT NULL,
  `user_lastname` varchar(45) NOT NULL,
  `user_description` text NOT NULL,
  `user_birthday` date NOT NULL,
  `user_postcode` varchar(10) NOT NULL,
  `user_city` int(11) NOT NULL,
  `user_appearance` int(11) NOT NULL,
  `user_sport` int(11) NOT NULL,
  `user_level` int(3) NOT NULL,
  `user_temperament` int(11) NOT NULL,
  `user_fake` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_username_UNIQUE` (`user_username`),
  UNIQUE KEY `user_email_UNIQUE` (`user_email`),
  UNIQUE KEY `user_key_UNIQUE` (`user_key`),
  KEY `user_appareance_idx` (`user_appearance`),
  KEY `user_sport_idx` (`user_sport`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=94 ;

CREATE TABLE IF NOT EXISTS `user_has_activity` (
  `user_id` int(11) NOT NULL,
  `input_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `activity_input_id` varchar(45) NOT NULL,
  `duration` int(15) DEFAULT NULL,
  `distance` int(15) DEFAULT NULL,
  `calories` int(15) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`input_id`,`date`,`activity_id`),
  KEY `input_id_idx` (`input_id`),
  KEY `activity_id_idx` (`activity_id`),
  KEY `activity_news_id_idx` (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `user_has_input` (
  `user_id` int(11) NOT NULL,
  `input_id` int(11) NOT NULL,
  `user_has_input_id` varchar(60) NOT NULL,
  `user_has_input_oauth` varchar(100) DEFAULT NULL,
  `user_has_input_oauth_secret` varchar(100) DEFAULT NULL,
  `user_has_input_refresh_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`input_id`,`user_has_input_id`),
  KEY `input_id_idx` (`input_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `user_infos` (
`user_id` int(11)
,`user_email` varchar(50)
,`user_firstname` varchar(45)
,`user_lastname` varchar(45)
,`user_username` varchar(20)
,`user_password` binary(16)
,`user_key` varchar(20)
,`user_level` int(3)
,`user_gender` int(11)
,`user_description` text
,`user_city` int(11)
,`user_city_name` varchar(45)
,`user_postcode` varchar(10)
,`user_birthday` date
,`user_age` int(6)
,`input_id` int(11)
,`user_input_id` varchar(60)
,`input_shortname` varchar(15)
,`user_input_oauth` varchar(100)
,`user_input_oauth_secret` varchar(100)
,`user_input_oauth_refresh_token` varchar(100)
,`body_weight` int(11)
,`body_height` int(11)
,`body_date` varchar(45)
,`sport_name` varchar(45)
,`sport_smallpicture` varchar(45)
,`appearance_name` varchar(45)
,`temperament_name` varchar(45)
,`followings` bigint(21)
,`achieved_goals` bigint(21)
,`followings_common` decimal(21,0)
,`relations` bigint(21)
,`user_fake` int(1)
);DROP TABLE IF EXISTS `goal_infos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`erwamartin`@`localhost` SQL SECURITY DEFINER VIEW `goal_infos` AS select `goal`.`goal_id` AS `goal_id`,`user_from`.`user_firstname` AS `user_from_firstname`,`user_from`.`user_lastname` AS `user_from_lastname`,`user_from`.`user_username` AS `user_from_username`,`user_from`.`user_gender` AS `user_from_gender`,`goal`.`goal_from` AS `user_from_id`,`goal`.`goal_to` AS `user_to_id`,`goal`.`goal_unit` AS `goal_unit`,`goal`.`goal_value` AS `goal_value`,`goal`.`goal_achievement` AS `goal_achievement`,`goal`.`goal_date` AS `goal_date`,`goal`.`goal_deadline` AS `goal_deadline`,`goal`.`goal_accepted` AS `goal_accepted` from (`goal` join `user` `user_from` on((`goal`.`goal_from` = `user_from`.`user_id`)));
DROP TABLE IF EXISTS `news_infos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`erwamartin`@`localhost` SQL SECURITY DEFINER VIEW `news_infos` AS select `news`.`news_id` AS `news_id`,`news`.`news_from` AS `user_from_id`,`user_from`.`user_firstname` AS `user_from_firstname`,`user_from`.`user_lastname` AS `user_from_lastname`,`user_from`.`user_username` AS `user_from_username`,`user_from`.`user_gender` AS `user_from_gender`,`news`.`news_to` AS `user_to_id`,`news`.`news_type` AS `news_type`,`news`.`news_content` AS `news_content`,`news`.`news_date` AS `news_date`,count(`support`.`user_id`) AS `news_supports` from ((`news` left join `support` on((`news`.`news_id` = `support`.`news_id`))) left join `user` `user_from` on((`news`.`news_from` = `user_from`.`user_id`))) group by `news`.`news_id`;
DROP TABLE IF EXISTS `relationship_infos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`erwamartin`@`localhost` SQL SECURITY DEFINER VIEW `relationship_infos` AS select `user_from`.`user_firstname` AS `user_from_firstname`,`user_from`.`user_lastname` AS `user_from_lastname`,`user_from`.`user_username` AS `user_from_username`,`user_from`.`user_gender` AS `user_from_gender`,`relationship`.`request_from` AS `request_from`,`relationship`.`request_to` AS `request_to`,`relationship`.`request_state` AS `request_state`,`relationship`.`request_time` AS `request_time` from (`relationship` join `user` `user_from` on((`relationship`.`request_from` = `user_from`.`user_id`))) group by `user_from`.`user_id`;
DROP TABLE IF EXISTS `user_infos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`erwamartin`@`localhost` SQL SECURITY DEFINER VIEW `user_infos` AS select `user`.`user_id` AS `user_id`,`user`.`user_email` AS `user_email`,`user`.`user_firstname` AS `user_firstname`,`user`.`user_lastname` AS `user_lastname`,`user`.`user_username` AS `user_username`,`user`.`user_password` AS `user_password`,`user`.`user_key` AS `user_key`,`user`.`user_level` AS `user_level`,`user`.`user_gender` AS `user_gender`,`user`.`user_description` AS `user_description`,`user`.`user_city` AS `user_city`,`cities_list`.`city_name` AS `user_city_name`,`user`.`user_postcode` AS `user_postcode`,`user`.`user_birthday` AS `user_birthday`,((year(now()) - year(`user`.`user_birthday`)) - (date_format(now(),'00-%m-%d') < date_format(`user`.`user_birthday`,'00-%m-%d'))) AS `user_age`,`user_has_input`.`input_id` AS `input_id`,`user_has_input`.`user_has_input_id` AS `user_input_id`,`input`.`input_shortname` AS `input_shortname`,`user_has_input`.`user_has_input_oauth` AS `user_input_oauth`,`user_has_input`.`user_has_input_oauth_secret` AS `user_input_oauth_secret`,`user_has_input`.`user_has_input_refresh_token` AS `user_input_oauth_refresh_token`,`body`.`body_weight` AS `body_weight`,`body`.`body_height` AS `body_height`,`body`.`body_date` AS `body_date`,`sport`.`sport_name` AS `sport_name`,`sport`.`sport_smallpicture` AS `sport_smallpicture`,`appearance`.`appearance_name` AS `appearance_name`,`temperament`.`temperament_name` AS `temperament_name`,count(`following_to`.`following_from`) AS `followings`,(select count(`goal`.`goal_id`) from `goal` where ((`goal`.`goal_to` = `user`.`user_id`) and (`goal`.`goal_achievement` = 100))) AS `achieved_goals`,(select round((count(0) / 2),0) from `following` `following_from` where exists(select distinct `following_to`.`following_from`,`following_to`.`following_to` from `following` `following_to` where ((`following_from`.`following_from` = `following_to`.`following_to`) and (`following_to`.`following_from` = `following_from`.`following_to`) and (`following_from`.`following_from` = `user`.`user_id`)))) AS `followings_common`,(select distinct count(0) from `relationship` where (((`relationship`.`request_from` = `user`.`user_id`) or (`relationship`.`request_to` = `user`.`user_id`)) and (`relationship`.`request_state` = 1))) AS `relations`,`user`.`user_fake` AS `user_fake` from ((((((((`user_has_input` left join `user` on((`user_has_input`.`user_id` = `user`.`user_id`))) left join `input` on((`user_has_input`.`input_id` = `input`.`input_id`))) left join `sport` on((`user`.`user_sport` = `sport`.`sport_id`))) left join `appearance` on((`user`.`user_appearance` = `appearance`.`appearance_id`))) left join `temperament` on((`user`.`user_temperament` = `temperament`.`temperament_id`))) left join `body` on((`user_has_input`.`user_id` = `body`.`user_id`))) left join `cities_list` on((`cities_list`.`city_id` = `user`.`user_city`))) left join `following` `following_to` on((`following_to`.`following_to` = `user`.`user_id`))) group by `user`.`user_id` order by `body`.`body_date` desc;


ALTER TABLE `body`
  ADD CONSTRAINT `body_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `following`
  ADD CONSTRAINT `following_from` FOREIGN KEY (`following_from`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `following_to` FOREIGN KEY (`following_to`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `goal`
  ADD CONSTRAINT `goal_from` FOREIGN KEY (`goal_from`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `goal_to` FOREIGN KEY (`goal_to`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `message`
  ADD CONSTRAINT `message_from` FOREIGN KEY (`message_from`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `message_to` FOREIGN KEY (`message_to`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `news`
  ADD CONSTRAINT `news_user_id` FOREIGN KEY (`news_from`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `notification`
  ADD CONSTRAINT `notification_from` FOREIGN KEY (`notification_from`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `notification_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `relationship`
  ADD CONSTRAINT `request_from` FOREIGN KEY (`request_from`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `request_to` FOREIGN KEY (`request_to`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `support`
  ADD CONSTRAINT `support_news_id` FOREIGN KEY (`news_id`) REFERENCES `news` (`news_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `support_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `user`
  ADD CONSTRAINT `user_appareance` FOREIGN KEY (`user_appearance`) REFERENCES `appearance` (`appearance_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_sport` FOREIGN KEY (`user_sport`) REFERENCES `sport` (`sport_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `user_has_activity`
  ADD CONSTRAINT `activity_id` FOREIGN KEY (`activity_id`) REFERENCES `activity` (`activity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `activity_input_id` FOREIGN KEY (`input_id`) REFERENCES `input` (`input_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `activity_news_id` FOREIGN KEY (`news_id`) REFERENCES `news` (`news_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `activity_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `user_has_input`
  ADD CONSTRAINT `input_id` FOREIGN KEY (`input_id`) REFERENCES `input` (`input_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
