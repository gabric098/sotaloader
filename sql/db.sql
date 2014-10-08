SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DELIMITER $$
DROP PROCEDURE IF EXISTS `add_summit`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_summit`(in assoc_code CHAR(5),
  in assoc_name CHAR(50),
  in reg_code CHAR(2),
  in reg_name CHAR(100),
  in code CHAR(20),
  in name CHAR(100),
  in sota_id CHAR(5),
  in altitude_m SMALLINT(5),
  in altitude_ft SMALLINT(5),
  in longitude DECIMAL(10,4),
  in latitude DECIMAL(10,4),
  in points TINYINT(3),
  in bonus_points TINYINT(3),
  in valid_from DATE,
  in valid_to DATE)
BEGIN  
  declare assoc_id SMALLINT(5);
  declare region_id SMALLINT(5);
  declare summit_id MEDIUMINT(8);    

  -- ASSOCIATION check if an association with the given code and name already exists
  SELECT id INTO assoc_id FROM association LIMIT 1;
  call debug_msg(assoc_id);

  IF (assoc_id IS NULL) THEN
    INSERT INTO association(code, name) VALUES (assoc_code, assoc_name);
    set assoc_id = (select last_insert_id());
  END IF;

  -- REGION check if a region with the given code and name already exists
  SET region_id = (SELECT id FROM region WHERE code = reg_code AND name = reg_name AND association_id = assoc_id);
  IF (region_id IS NULL) THEN
    INSERT INTO region(association_id, code, name) VALUES (assoc_id, reg_code, reg_name);
    set region_id = (select last_insert_id());
  END IF;

  -- SUMMIT check if a summit with given parameters already exists
  SET summit_id = (SELECT id FROM summit WHERE association_id = assoc_id AND region_id = region_id
                                               AND code = code AND name = name AND sota_id = sota_id);
  IF (summit_id IS NULL) THEN
    INSERT INTO summit(code, name, sota_id, association_id,  region_id, altitude_m, altitude_ft, longitude,
    latitude, points, bonus_points, valid_from, valid_to)
      VALUES (code, name, sota_id, assoc_id, region_id, altitude_m, altitude_ft, longitude, latitude,
      points, bonus_points, valid_from, valid_to);
  END IF;
END$$

DROP PROCEDURE IF EXISTS `debug_msg`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debug_msg`(msg VARCHAR(255))
BEGIN
  IF true THEN BEGIN
    select concat("** ", msg) AS '** DEBUG:';
  END; END IF;
END$$

DELIMITER ;

DROP TABLE IF EXISTS `activation`;
CREATE TABLE IF NOT EXISTS `activation` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `summit_id` mediumint(8) unsigned NOT NULL,
  `activations_count` mediumint(8) unsigned NOT NULL,
  `last_activation_date` date NOT NULL,
  `last_activation_call` char(30) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `association`;
CREATE TABLE IF NOT EXISTS `association` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(5) NOT NULL,
  `name` char(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

DROP TABLE IF EXISTS `region`;
CREATE TABLE IF NOT EXISTS `region` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `association_id` smallint(5) unsigned NOT NULL,
  `code` char(2) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `name` char(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=61 ;

DROP TABLE IF EXISTS `summit`;
CREATE TABLE IF NOT EXISTS `summit` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `name` char(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `sota_id` char(5) NOT NULL,
  `association_id` smallint(5) unsigned NOT NULL,
  `region_id` smallint(5) unsigned NOT NULL,
  `altitude_m` smallint(5) unsigned NOT NULL,
  `altitude_ft` smallint(5) unsigned NOT NULL,
  `longitude` decimal(10,4) NOT NULL,
  `latitude` decimal(10,4) NOT NULL,
  `points` tinyint(3) unsigned NOT NULL,
  `bonus_points` tinyint(3) unsigned NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `region_id` (`region_id`),
  KEY `association_id` (`association_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=49 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;