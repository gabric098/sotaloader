<?php
/**
 * Created by PhpStorm.
 * User: gabriele
 * Date: 17/09/2014
 * Time: 11:05
 */

namespace iz1ksw\SotaImport\db;

use iz1ksw\SotaImport\utils\DateUtils;
use iz1ksw\SotaImport\log\SotaLogger;
use iz1ksw\SotaImport\CsvSummit;

class DbAdapter {
    private $dbhost = '';
    private $dbname = '';
    private $dbuser = '';
    private $dbpwd = '';
    /**
     * @var $pdo \PDO
     */
    private $pdo;
    /**
     * @var $log \Logger
     */
    private $log;

    public function __construct()
    {
        $this->log = SotaLogger::getLogger();
    }

    /**
     * @param $summitList CsvSummit[]
     */
    public function addSummits($summitList)
    {
        $this->openConnection();
        $fieldList = ['assoc_code', 'assoc_name', 'reg_code', 'reg_name', 'code', 'name',
        'sota_id', 'altitude_m', 'altitude_ft', 'longitude', 'latitude', 'points', 'bonus_points', 'valid_from', 'valid_to'];
        $psQuery = "CALL add_summit(";
        $i = 0;
        foreach ($fieldList as $field) {
            if ($i > 0) {
                $psQuery .= ', ';
            }
            $psQuery .= ':' . $field;
            $i++;
        }
        $psQuery .= ')';
        $st = $this->pdo->prepare($psQuery);

        $assoc_code = '';
        $assoc_name = '';
        $reg_code = '';
        $reg_name = '';
        $code = '';
        $name = '';
        $sota_id = '';
        $altitude_m = '';
        $altitude_ft = '';
        $latitude = '';
        $longitude = '';
        $points = '';
        $bonus_points = '';
        $valid_from = '';
        $valid_to = '';

        foreach ($fieldList as $field) {
            $st->bindParam(':' . $field, ${$field});
        }

        /**
         * @var $summit CsvSummit
         */
        foreach($summitList as $summit) {
            $assoc_code = (string)$summit->getAssociationCode();
            $assoc_name = (string)$summit->getAssociationName();
            $reg_code = (string)$summit->getRegionCode();
            $reg_name = (string)$summit->getRegionName();
            $code = (string)$summit->getSummitCode();
            $name = (string)$summit->getSummitName();
            $sota_id = (string)$summit->getSotaId();
            $altitude_m = (int)$summit->getAltitudeMeters();
            $altitude_ft = (int)$summit->getAltitudeFeet();
            $latitude = (float)$summit->getLatitude();
            $longitude = (float)$summit->getLongitude();
            $points = (int)$summit->getPoints();
            $bonus_points = (int)$summit->getBonusPoints();
            $valid_from = (string)DateUtils::toDatabaseDate($summit->getValidFrom());
            $valid_to = (string)DateUtils::toDatabaseDate($summit->getValidTo());
            if ($st->execute() === false) {
                echo(implode($st->errorInfo()));
            }
            break;
        }
        $this->closeConnection();

    }

    private function openConnection()
    {
        try {
            $this->pdo = new \PDO("mysql:host=$this->dbhost;dbname=$this->dbname", $this->dbuser, $this->dbpwd);
        } catch (\PDOException $e) {
            $this->log->error("Connection Failed: " . $e->getMessage());
            die();
        }
    }

    private function closeConnection()
    {
        // destroy the object and close the connection
        $this->pdo = null;
    }
}