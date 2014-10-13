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
  in p_code CHAR(20),
  in p_name CHAR(100),
  in p_sota_id CHAR(5),
  in altitude_m SMALLINT(5),
  in altitude_ft SMALLINT(5),
  in longitude DECIMAL(10,4),
  in latitude DECIMAL(10,4),
  in points TINYINT(3),
  in bonus_points TINYINT(3),
  in valid_from DATE,
  in valid_to DATE,
  out new_assoc CHAR(255),
  out new_region CHAR(255),
  out new_summit CHAR(255),
  out upd_assoc CHAR(255),
  out upd_region CHAR(255),
  out upd_summit CHAR(255)
)
BEGIN  
  declare v_assoc_id SMALLINT(5);
  declare v_region_id SMALLINT(5);
  declare v_summit_id MEDIUMINT(8);

  -- ASSOCIATION just check if an association with given association code exists
  SELECT id INTO v_assoc_id FROM association WHERE association.code = assoc_code LIMIT 1;

  IF (v_assoc_id IS NULL) THEN
    INSERT INTO association(code, name) VALUES (assoc_code, assoc_name);
    set v_assoc_id = (select last_insert_id());
    set new_assoc = CONCAT(assoc_code, ' - ', assoc_name);
  ELSE -- check if association name has changed, and change the record accordingly
    IF (SELECT association.name != assoc_name FROM association WHERE association.code = assoc_code LIMIT 1) THEN
      UPDATE association SET association.name = assoc_name WHERE association.code = assoc_code;
      set upd_assoc = CONCAT(assoc_code, ' - ', assoc_name);
    END IF;
  END IF;

  -- REGION check if a region with the given code and association id already exists
  SELECT id INTO v_region_id FROM region WHERE region.code = reg_code AND region.association_id = v_assoc_id;

  IF (v_region_id IS NULL) THEN
    INSERT INTO region(association_id, code, name) VALUES (v_assoc_id, reg_code, reg_name);
    set v_region_id = (select last_insert_id());
    set new_region = CONCAT(reg_code, ' - ', reg_name);
  ELSE -- check if region name has changed, and change the record accordingly
    IF (SELECT region.name != reg_name FROM region WHERE region.code = reg_code AND region.association_id = v_assoc_id) THEN
      UPDATE region SET region.name = reg_name WHERE region.code = reg_code AND region.association_id = v_assoc_id;
      set upd_region = CONCAT(reg_code, ' - ', reg_name);
    END IF;
  END IF;

  -- SUMMIT check if a summit with given parameters already exists
  SET v_summit_id = (SELECT id FROM summit WHERE summit.association_id = v_assoc_id AND summit.region_id = v_region_id
                               AND summit.code = p_code AND summit.sota_id = p_sota_id);
  IF (v_summit_id IS NULL) THEN
    INSERT INTO summit(code, name, sota_id, association_id,  region_id, altitude_m, altitude_ft, longitude,
    latitude, points, bonus_points, valid_from, valid_to)
      VALUES (p_code, p_name, p_sota_id, v_assoc_id, v_region_id, altitude_m, altitude_ft, longitude, latitude,
      points, bonus_points, valid_from, valid_to);
    set new_summit = CONCAT(p_code, ' - ', p_name);
  ELSE -- check if some of the summit properties have changed and update the summit record
    IF (SELECT (summit.name != p_name OR summit.altitude_m != altitude_m OR summit.altitude_ft != altitude_ft OR
               summit.latitude != latitude OR summit.longitude != longitude OR summit.points != points OR
               summit.bonus_points != bonus_points OR summit.valid_from != valid_from OR summit.valid_to != valid_to)
        FROM
          summit
        WHERE
          summit.association_id = v_assoc_id AND summit.region_id = v_region_id AND
          summit.code = p_code AND summit.sota_id = p_sota_id) THEN
      UPDATE
          summit
      SET
          summit.name = p_name, summit.altitude_m = altitude_m, summit.altitude_ft = altitude_ft,
          summit.longitude = longitude, summit.latitude = latitude, summit.points = points,
          summit.bonus_points = bonus_points, summit.valid_from = valid_from, summit.valid_to = valid_to
      WHERE
          summit.association_id = v_assoc_id AND summit.region_id = v_region_id AND
          summit.code = p_code AND summit.sota_id = p_sota_id;
      set upd_summit = CONCAT(p_code, ' - ', p_name);
      END IF;
  END IF;
END$$

DELIMITER ;

DROP TABLE IF EXISTS `activation`;
CREATE TABLE IF NOT EXISTS `activation` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `summit_id` mediumint(8) unsigned NOT NULL,
  `activations_count` mediumint(8) unsigned NOT NULL,
  `last_activation_date` date NOT NULL,
  `last_activation_call` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `association`;
CREATE TABLE IF NOT EXISTS `association` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(5) NOT NULL,
  `name` char(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `region`;
CREATE TABLE IF NOT EXISTS `region` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `association_id` smallint(5) unsigned NOT NULL,
  `code` char(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `summit`;
CREATE TABLE IF NOT EXISTS `summit` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;