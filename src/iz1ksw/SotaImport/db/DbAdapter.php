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
    /**
     * @var $pdo \PDO
     */
    private $pdo;
    /**
     * @var $log \Logger
     */
    private $log;
    /**
     * @var $config
     */
    private $config;

    private $output;

    public function __construct($config)
    {
        $this->log = SotaLogger::getLogger();
        $this->config = $config;
        $this->output["new_assoc"] = array();
        $this->output["new_region"] = array();
        $this->output["new_summit"] = array();
        $this->output["upd_assoc"] = array();
        $this->output["upd_region"] = array();
        $this->output["upd_summit"] = array();
    }

    /**
     * @param $summitList CsvSummit[]
     */
    public function addSummits($summitList)
    {
        $this->openConnection();
        $inFieldList = ['assoc_code', 'assoc_name', 'reg_code', 'reg_name', 'code', 'name',
            'sota_id', 'altitude_m', 'altitude_ft', 'longitude', 'latitude', 'points', 'bonus_points', 'valid_from',
            'valid_to', 'activations_count', 'last_activation_date', 'last_activation_call'];
        $outFieldList = ['new_assoc', 'new_region', 'new_summit', 'upd_assoc', 'upd_region', 'upd_summit'];
        $psQuery = "CALL add_summit(";
        $i = 0;
        foreach ($inFieldList as $field) {
            if ($i > 0) {
                $psQuery .= ', ';
            }
            $psQuery .= ':' . $field;
            $i++;
        }
        foreach ($outFieldList as $field) {
            if ($i > 0) {
                $psQuery .= ', ';
            }
            $psQuery .= '@' . $field;
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
        $altitude_m = 0;
        $altitude_ft = 0;
        $latitude = 0;
        $longitude = 0;
        $points = 0;
        $bonus_points = 0;
        $valid_from = '';
        $valid_to = '';
        $last_activation_date = null;
        $last_activation_call = null;
        $activations_count = 0;

        foreach ($inFieldList as $field) {
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
            $lActDate = $summit->getLastActivationDate();
            $lActCall = $summit->getLastActivationCall();
            $actCount = $summit->getActivationCount();
            $last_activation_date = null;
            $last_activation_call = null;
            $activations_count = 0;
            if (isset($lActDate) && $lActDate != '')
                $last_activation_date = (string)DateUtils::toDatabaseDate($lActDate);
            if (isset($lActCall) && $lActCall != '')
                $last_activation_call = (string)$lActCall;
            if (isset($actCount) && $actCount != '')
                $activations_count = (int)$actCount;
            if ($st->execute() === false) {
                echo(implode($st->errorInfo()));
            }
            $outputArray = $this->pdo->query("SELECT @new_assoc, @new_region, @new_summit, @upd_assoc, @upd_region, @upd_summit")->fetchAll();

            foreach($outputArray as $row)
            {
                if ($row["@new_assoc"] != null) {
                    $this->output["new_assoc"][] = $row["@new_assoc"];
                }
                if ($row["@new_region"] != null) {
                    $this->output["new_region"][] = $row["@new_region"];
                }
                if ($row["@new_summit"] != null) {
                    $this->output["new_summit"][] = $row["@new_summit"];
                }
                if ($row["@upd_assoc"] != null) {
                    $this->output["upd_assoc"][] = $row["@upd_assoc"];
                }
                if ($row["@upd_region"] != null) {
                    $this->output["upd_region"][] = $row["@upd_region"];
                }
                if ($row["@upd_summit"] != null) {
                    $this->output["upd_summit"][] = $row["@upd_summit"];
                }
            }
        }
        $this->closeConnection();
    }

    private function openConnection()
    {
        try {
            $this->pdo = new \PDO("mysql:host=" . $this->config['db_host'] . ";dbname=" . $this->config['db_name'] .
                ";charset=utf8", $this->config['db_user'], $this->config['db_pass']);
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

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

}
