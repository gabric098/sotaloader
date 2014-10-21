<?php
/**
 * Created by PhpStorm.
 * User: gabriele
 * Date: 11/07/14
 * Time: 19:15
 */

namespace iz1ksw\SotaImport;

use iz1ksw\SotaImport\log\SotaLogger;
use iz1ksw\SotaImport\utils\SummitCode;
use iz1ksw\SotaImport\utils\CastUtils;

class SotaCsvParser {
    const csvPointerFile = 'last_loc.txt';
    const csvBufferSize = 1000; // processes 100 lines batches

    private $hasMoreData = true;
    private $csvFilePath;
    private $csvSummitsArray;
    private static $csvConfig = array("SUMMIT_CODE" => 0,
                                        "ASSOCIATION_NAME" => 1,
                                        "REGION_NAME" => 2,
                                        "SUMMIT_NAME" => 3,
                                        "ALTITUDE_METERS" => 4,
                                        "ALTITUDE_FEET" => 5,
                                        "LONGITUDE" => 8,
                                        "LATITUDE" => 9,
                                        "POINTS" => 10,
                                        "BONUS_POINTS" => 11,
                                        "VALID_FROM" => 12,
                                        "VALID_TO" => 13,
                                        "ACTIVATIONS_COUNT" => 14,
                                        "LAST_ACTIVATION_DATE" => 15,
                                        "LAST_ACTIVATION_CALL" => 16
    );
    /* @var $log \Logger */
    private $log;

    function __construct($csvFilePath)
    {
        $this->csvFilePath = $csvFilePath;
        // initialize the logger
        $this->log = SotaLogger::getLogger();
        $this->initialize();
    }

    private function initialize()
    {
        // if for some reason the last_location file is present, detete it (previous corrupted execution)
        if (file_exists(SotaCsvParser::csvPointerFile)) {
            $this->log->warn("CSV pointer file already exist. I delete it and I start a fresh execution");
            @unlink(SotaCsvParser::csvPointerFile);
        }
    }

    public function parseMoreElement() {
        if (!$this->hasMoreData) {
            $this->log->info("Reached CSV EOF. I delete csv pointer file.");
            @unlink(SotaCsvParser::csvPointerFile);
            return false;
        }
        $this->csvSummitsArray = array();
        // get last csv file pointer (if file doesn't exist put the pointer to the beginning of the file)
        $lastPosition = @file_get_contents(SotaCsvParser::csvPointerFile);
        $lastPosition = ($lastPosition === false) ? 0 : intval($lastPosition);
        if(($handle = fopen($this->csvFilePath, 'r')) !== false) {
            fseek($handle, $lastPosition);
            if ($lastPosition == 0) {// parse headers only if you're a the beginning of the file
                // first line contains some information about the export date:
                // in this format: SOTA Summits List (Date=10/07/2014)
                $dateData = fgetcsv($handle);
                //TODO: add date processing

                // second line is the real CSV header
                $header = fgetcsv($handle);
            }

            // loop through the file line-by-line
            $csvProcessedLinesCount = 0;
            while($csvProcessedLinesCount < SotaCsvParser::csvBufferSize)
            {
                if (feof($handle)) {
                    $this->hasMoreData = false;  // reached EOF remove last location file
                    break;
                } else if (($data = fgetcsv($handle)) !== false) {
                    $this->csvSummitsArray[] = $this->processCsvRow($data);
                    $csvProcessedLinesCount++;
                    unset($data);
                }
            }
            $this->log->info(count($this->csvSummitsArray) . " items batch completed and ready for processing");
            file_put_contents(SotaCsvParser::csvPointerFile, ftell($handle)); // take note of the last position in the file
            fclose($handle);
        }
        return true;
    }

    private function processCsvRow($csvRowData) {
        $csvSummit = new CsvSummit();
        $sumCode = $csvRowData[SotaCsvParser::$csvConfig["SUMMIT_CODE"]];
        $sumCodeUtils = new SummitCode($sumCode);
        $csvSummit->setSummitCode($sumCode);
        $csvSummit->setSotaId($sumCodeUtils->getSummitId());
        $csvSummit->setAssociationName($csvRowData[SotaCsvParser::$csvConfig["ASSOCIATION_NAME"]]);
        $csvSummit->setAssociationCode($sumCodeUtils->getAssociationCode());
        $csvSummit->setRegionName($csvRowData[SotaCsvParser::$csvConfig["REGION_NAME"]]);
        $csvSummit->setRegionCode($sumCodeUtils->getRegionCode());
        $csvSummit->setSummitName($csvRowData[SotaCsvParser::$csvConfig["SUMMIT_NAME"]]);
        $csvSummit->setAltitudeMeters(intval($csvRowData[SotaCsvParser::$csvConfig["ALTITUDE_METERS"]]));
        $csvSummit->setAltitudeFeet(intval($csvRowData[SotaCsvParser::$csvConfig["ALTITUDE_FEET"]]));
        $csvSummit->setLongitude(floatval($csvRowData[SotaCsvParser::$csvConfig["LONGITUDE"]]));
        $csvSummit->setLatitude(floatval($csvRowData[SotaCsvParser::$csvConfig["LATITUDE"]]));
        $csvSummit->setPoints(intval($csvRowData[SotaCsvParser::$csvConfig["POINTS"]]));
        $csvSummit->setBonusPoints(intval($csvRowData[SotaCsvParser::$csvConfig["BONUS_POINTS"]]));
        $csvSummit->setValidFrom(\DateTime::createFromFormat('d/m/Y', $csvRowData[SotaCsvParser::$csvConfig["VALID_FROM"]]));
        $csvSummit->setValidTo(\DateTime::createFromFormat('d/m/Y', $csvRowData[SotaCsvParser::$csvConfig["VALID_TO"]]));
        if ($csvRowData[SotaCsvParser::$csvConfig["ACTIVATIONS_COUNT"]] != '') {
            $csvSummit->setActivationCount(intval($csvRowData[SotaCsvParser::$csvConfig["ACTIVATIONS_COUNT"]]));
        }
        if ($csvRowData[SotaCsvParser::$csvConfig["LAST_ACTIVATION_DATE"]] != '') {
            $csvSummit->setLastActivationDate(\DateTime::createFromFormat('d/m/Y', $csvRowData[SotaCsvParser::$csvConfig["LAST_ACTIVATION_DATE"]]));
        }
        if ($csvRowData[SotaCsvParser::$csvConfig["LAST_ACTIVATION_CALL"]] != '') {
            $csvSummit->setLastActivationCall($csvRowData[SotaCsvParser::$csvConfig["LAST_ACTIVATION_CALL"]]);
        }
        return $csvSummit;
    }

    public function getCsvArray() {
        return $this->csvSummitsArray;
    }
}