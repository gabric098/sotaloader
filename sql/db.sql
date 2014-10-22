SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DELIMITER $$
DROP PROCEDURE IF EXISTS `add_summit`$$
CREATE DEFINER =`root`@`localhost` PROCEDURE `add_summit`(IN  assoc_code           CHAR(5),
                                                          IN  assoc_name           CHAR(50),
                                                          IN  reg_code             CHAR(2),
                                                          IN  reg_name             CHAR(100),
                                                          IN  p_code               CHAR(20),
                                                          IN  p_name               CHAR(100),
                                                          IN  p_sota_id            CHAR(5),
                                                          IN  altitude_m           SMALLINT(5),
                                                          IN  altitude_ft          SMALLINT(5),
                                                          IN  longitude            DECIMAL(10, 4),
                                                          IN  latitude             DECIMAL(10, 4),
                                                          IN  points               TINYINT(3),
                                                          IN  bonus_points         TINYINT(3),
                                                          IN  valid_from           DATE,
                                                          IN  valid_to             DATE,
                                                          IN  activations_count    MEDIUMINT(8),
                                                          IN  last_activation_date DATE,
                                                          IN  last_activation_call CHAR(30),
                                                          OUT new_assoc            CHAR(255),
                                                          OUT new_region           CHAR(255),
                                                          OUT new_summit           CHAR(255),
                                                          OUT upd_assoc            CHAR(255),
                                                          OUT upd_region           CHAR(255),
                                                          OUT upd_summit           CHAR(255)
)
  BEGIN
    DECLARE v_assoc_id SMALLINT(5);
    DECLARE v_region_id SMALLINT(5);
    DECLARE v_summit_id MEDIUMINT(8);
    DECLARE v_summit_stat_id MEDIUMINT(8);

-- ASSOCIATION just check if an association with given association code exists
    SELECT id
    INTO v_assoc_id
    FROM associations
    WHERE associations.code = assoc_code
    LIMIT 1;

    IF (v_assoc_id IS NULL)
    THEN
      INSERT INTO associations (code, name) VALUES (assoc_code, assoc_name);
      SET v_assoc_id = (SELECT last_insert_id());
      SET new_assoc = CONCAT(assoc_code, ' - ', assoc_name);
    ELSE -- check if association name has changed, and change the record accordingly
      IF (SELECT associations.name != assoc_name
          FROM associations
          WHERE associations.id = v_assoc_id
          LIMIT 1)
      THEN
        UPDATE associations
        SET associations.name = assoc_name
        WHERE associations.id = v_assoc_id;
        SET upd_assoc = CONCAT(assoc_code, ' - ', assoc_name);
      END IF;
    END IF;

-- REGION check if a region with the given code and association id already exists
    SELECT id
    INTO v_region_id
    FROM regions
    WHERE regions.code = reg_code AND regions.association_id = v_assoc_id;

    IF (v_region_id IS NULL)
    THEN
      INSERT INTO regions (association_id, code, name) VALUES (v_assoc_id, reg_code, reg_name);
      SET v_region_id = (SELECT last_insert_id());
      SET new_region = CONCAT(reg_code, ' - ', reg_name);
    ELSE -- check if region name has changed, and change the record accordingly
      IF (SELECT regions.name != reg_name
          FROM regions
          WHERE regions.id = v_region_id)
      THEN
        UPDATE regions
        SET regions.name = reg_name
        WHERE regions.id = v_region_id;
        SET upd_region = CONCAT(reg_code, ' - ', reg_name);
      END IF;
    END IF;

-- SUMMIT check if a summit with given parameters already exists
    SET v_summit_id = (SELECT id
                       FROM summits
                       WHERE summits.association_id = v_assoc_id AND summits.region_id = v_region_id
                             AND summits.code = p_code AND summits.sota_id = p_sota_id);
    IF (v_summit_id IS NULL)
    THEN
      INSERT INTO summits (code, name, sota_id, association_id, region_id, altitude_m, altitude_ft, longitude,
                           latitude, points, bonus_points, valid_from, valid_to, activations_count, last_activation_date,
                           last_activation_call)
      VALUES (p_code, p_name, p_sota_id, v_assoc_id, v_region_id, altitude_m, altitude_ft, longitude, latitude,
              points, bonus_points, valid_from, valid_to, activations_count, last_activation_date,
              last_activation_call);
      SET v_summit_id = (SELECT last_insert_id());
      SET new_summit = CONCAT(p_code, ' - ', p_name);
    ELSE -- check if some of the summit properties have changed and update the summit record
      IF (SELECT (summits.name != p_name OR summits.altitude_m != altitude_m OR summits.altitude_ft != altitude_ft OR
                  summits.latitude != latitude OR summits.longitude != longitude OR summits.points != points OR
                  summits.bonus_points != bonus_points OR summits.valid_from != valid_from OR
                  summits.valid_to != valid_to OR
                  summits.activations_count != activations_count OR summits.last_activation_date != last_activation_date
                  OR
                  summits.last_activation_call != last_activation_call)
          FROM
            summits
          WHERE
            summits.id = v_summit_id)
      THEN
        UPDATE
          summits
        SET
          summits.name                 = p_name, summits.altitude_m = altitude_m, summits.altitude_ft = altitude_ft,
          summits.longitude            = longitude, summits.latitude = latitude, summits.points = points,
          summits.bonus_points         = bonus_points, summits.valid_from = valid_from, summits.valid_to = valid_to,
          summits.activations_count    = activations_count, summits.last_activation_date = last_activation_date,
          summits.last_activation_call = last_activation_call
        WHERE
          summits.id = v_summit_id;
        SET upd_summit = CONCAT(p_code, ' - ', p_name);
      END IF;
    END IF;
  END$$

DELIMITER ;

DROP TABLE IF EXISTS `associations`;
CREATE TABLE IF NOT EXISTS `associations` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(5) NOT NULL,
  `name` char(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `regions`;
CREATE TABLE IF NOT EXISTS `regions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `association_id` smallint(5) unsigned NOT NULL,
  `code` char(2) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `summits`;
CREATE TABLE IF NOT EXISTS `summits` (
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
  `activations_count` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `last_activation_date` date NULL,
  `last_activation_call` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  PRIMARY KEY (`id`),
  KEY `region_id` (`region_id`),
  KEY `association_id` (`association_id`),
  KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;