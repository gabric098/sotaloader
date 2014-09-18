DROP PROCEDURE IF EXISTS add_summit;
DELIMITER //
CREATE PROCEDURE add_summit(in assoc_code CHAR(5),
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
  SET @assoc_id = (SELECT id FROM association WHERE code = @assoc_code AND name = @assoc_name);
  IF (@assoc_id IS NULL) THEN
    INSERT INTO association(code, name) VALUES (@assoc_code, @assoc_name);
    set @assoc_id = (select last_insert_id());
  END IF;

  -- REGION check if a region with the given code and name already exists
  SET @region_id = (SELECT id FROM region WHERE code = @reg_code AND name = @reg_name AND association_id = @assoc_id);
  IF (@region_id IS NULL) THEN
    INSERT INTO region(association_id, code, name) VALUES (@assoc_id, @reg_code, @reg_name);
    set @region_id = (select last_insert_id());
  END IF;

  -- SUMMIT check if a summit with given parameters already exists
  SET summit_id = (SELECT id FROM summit WHERE association_id = @assoc_id AND region_id = @region_id
                                               AND code = @code AND name = @name AND sota_id = @sota_id);
  IF (summit_id IS NULL) THEN
    INSERT INTO summit(code, name, sota_id, association_id,  region_id, altitude_m, altitude_ft, longitude,
    latitude, points, bonus_points, valid_from, valid_to)
      VALUES (@code, @name, @sota_id, @assoc_id, @region_id, @altitude_m, @altitude_ft, @longitude, @latitude,
      @points, @bonus_points, @valid_from, @valid_to);
  END IF;
END;
//
DELIMITER ;