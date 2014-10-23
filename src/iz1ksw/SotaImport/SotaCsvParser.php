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

class SotaCsvParser {
    const csvPointerFile = 'last_loc.txt';
    const csvBufferSize = 1000; // processes 1000 lines batches

    /**
     * @var bool
     */
    private $hasMoreData = true;
    /**
     * @var int
     */
    private $bufferPages = 0;
    /**
     * @var
     */
    private $csvFilePath;
    /**
     * @var CsvSummit[]
     */
    private $csvSummitsArray;
    /**
     * @var array
     */
    private $errors = array();
    /**
     * Count all the valid summits
     * @var int
     */
    private $validSummitsCount = 0;

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

    public function parseMoreElement()
    {
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
                    try {
                        $this->csvSummitsArray[] = $this->processCsvRow($data);
                        $this->validSummitsCount++;
                    } catch (\Exception $e) {
                        $this->errors[] = $this->getAbsoluteCsvLine($csvProcessedLinesCount);
                    } finally{
                        $csvProcessedLinesCount++;
                        unset($data);
                    }
                }
            }
            $this->bufferPages++;
            $this->log->info(count($this->csvSummitsArray) . " items batch completed and ready for processing");
            file_put_contents(SotaCsvParser::csvPointerFile, ftell($handle)); // take note of the last position in the file
            fclose($handle);
        }
        return true;
    }

    /**
     * Checks if a specific row is valid using sanitize function.
     * @param $csvRowData
     * @return CsvSummit
     */
    private function processCsvRow($csvRowData)
    {
        $csvSummit = new CsvSummit();

        // summit code
        $sumCode = $csvRowData[SotaCsvParser::$csvConfig["SUMMIT_CODE"]];
        $this->log->info("Processing " . $sumCode);
        $sumCode = $this->sanitize($sumCode, 'summitcode', '', true);
        $sumCodeUtils = new SummitCode($sumCode);
        $csvSummit->setSummitCode($sumCode);
        $csvSummit->setSotaId($sumCodeUtils->getSummitId());
        $csvSummit->setAssociationCode($sumCodeUtils->getAssociationCode());
        $csvSummit->setRegionCode($sumCodeUtils->getRegionCode());

        // association name
        $assocName = $csvRowData[SotaCsvParser::$csvConfig["ASSOCIATION_NAME"]];
        $assocName = $this->sanitize($assocName, 'string', '', true);
        $csvSummit->setAssociationName($assocName);

        // region name
        $regionName = $csvRowData[SotaCsvParser::$csvConfig["REGION_NAME"]];
        $regionName = $this->sanitize($regionName, 'string', '', true);
        $csvSummit->setRegionName($regionName);

        // summit name
        $sumName = $csvRowData[SotaCsvParser::$csvConfig["SUMMIT_NAME"]];
        $sumName = $this->sanitize($sumName, 'string', '', true);
        $csvSummit->setSummitName($sumName);

        // alt meters
        $altMeters = $csvRowData[SotaCsvParser::$csvConfig["ALTITUDE_METERS"]];
        $altMeters = $this->sanitize($altMeters, 'int', 0);
        $csvSummit->setAltitudeMeters(intval($altMeters));

        // alt feet
        $altFeet = $csvRowData[SotaCsvParser::$csvConfig["ALTITUDE_FEET"]];
        $altFeet = $this->sanitize($altFeet, 'int', 0);
        $csvSummit->setAltitudeFeet(intval($altFeet));

        // longitude
        $longitude = $csvRowData[SotaCsvParser::$csvConfig["LONGITUDE"]];
        $longitude = $this->sanitize($longitude, 'float', 0);
        $csvSummit->setLongitude(floatval($longitude));

        // latitude
        $latitude = $csvRowData[SotaCsvParser::$csvConfig["LATITUDE"]];
        $latitude = $this->sanitize($latitude, 'float', 0);
        $csvSummit->setLatitude(floatval($latitude));

        // points
        $points = $csvRowData[SotaCsvParser::$csvConfig["POINTS"]];
        $points = $this->sanitize($points, 'int', 0);
        $csvSummit->setPoints(intval($points));

        // bonus points
        $bonus = $csvRowData[SotaCsvParser::$csvConfig["BONUS_POINTS"]];
        $bonus = $this->sanitize($bonus, 'int', 0);
        $csvSummit->setBonusPoints(intval($bonus));

        // valid from
        $validFrom = $csvRowData[SotaCsvParser::$csvConfig["VALID_FROM"]];
        $validFrom = $this->sanitize($validFrom, 'datetime', null);
        if ($validFrom != null)
            $csvSummit->setValidFrom(\DateTime::createFromFormat('d/m/Y', $validFrom));
        else
            $csvSummit->setValidFrom($validFrom);

        // valid to
        $validTo = $csvRowData[SotaCsvParser::$csvConfig["VALID_TO"]];
        $validTo = $this->sanitize($validTo, 'datetime', null);
        if ($validTo != null)
            $csvSummit->setValidTo(\DateTime::createFromFormat('d/m/Y', $validTo));
        else
            $csvSummit->setValidTo($validTo);

        // activation count
        $actCount = $csvRowData[SotaCsvParser::$csvConfig["ACTIVATIONS_COUNT"]];
        $actCount = $this->sanitize($actCount, 'int', 0);
        $csvSummit->setActivationCount(intval($actCount));

        // last activation date
        $lActDate = $csvRowData[SotaCsvParser::$csvConfig["LAST_ACTIVATION_DATE"]];
        $lActDate = $this->sanitize($lActDate, 'datetime', null);
        if ($lActDate != null)
            $csvSummit->setLastActivationDate(\DateTime::createFromFormat('d/m/Y', $lActDate));
        else
            $csvSummit->setLastActivationDate($lActDate);

        // last activation call
        $lActCall = $csvRowData[SotaCsvParser::$csvConfig["LAST_ACTIVATION_CALL"]];
        $lActCall = $this->sanitize($lActCall, 'string', null);
        $csvSummit->setLastActivationCall($lActCall);

        return $csvSummit;
    }

    /**
     * It just checks if the value is coherent with the expected data type,
     * it doesn't perform any data type transformations.
     * @param $value
     * @param $type
     * @param $default
     * @param bool $isBlocking
     * @return int|string
     * @throws \Exception
     */
    private function sanitize($value, $type, $default, $isBlocking=false)
    {
        $returnValue = $default;
        $value = trim($value);
        $isValid = true;
        switch ($type) {
            case 'summitcode':
                $pattern = '/^[A-Z0-9][A-Z0-9]?[A-Z0-9]?\/[A-Z][A-Z]-[0-9][0-9][0-9]$/';
                if (preg_match($pattern, $value)) {
                    $returnValue = $value;
                } else {
                    $isValid = false;
                }
                break;
            case 'string':
                if ($value != '') {
                    $returnValue = $value;
                } else {
                    $isValid = false;
                }
                break;
            case 'float':
            case 'int':
                if ($value != '' && is_numeric($value)) {
                    $returnValue = $value;
                } else {
                    $isValid = false;
                }
                break;
            case 'datetime':
                $dt = \DateTime::createFromFormat("d/m/Y", $value);
                if ($dt !== false && !array_sum($dt->getLastErrors())) {
                    $returnValue = $value;
                } else {
                    $isValid = false;
                }
                break;
            default:
                $returnValue = $value;
                break;
        }
        if (!$isValid) {
            if ($isBlocking) {
                $this->log->error("Unable to validate value '" . $value . "' for type: " . $type);
                throw new \Exception("Unable to validate csv row.");
            } else {
                $this->log->warn("Unable to validate value '" . $value . "' for type: " . $type);
            }
        }
        return $returnValue;
    }

    /**
     * Given the current position in the buffer, it calculate the absolute csv line number
     * @param $currentPageLine
     * @return int
     */
    private function getAbsoluteCsvLine($currentPageLine)
    {
        return (SotaCsvParser::csvBufferSize * $this->bufferPages) + $currentPageLine + 3;
    }

    /**
     * Returns the list of validation errors occured.
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns the array of CsvSummit ready to be processed.
     * @return CsvSummit[]
     */
    public function getCsvArray() {
        return $this->csvSummitsArray;
    }

    /**
     * Returns the number of summits which passed validation.
     * @return int
     */
    public function getValidSummitsCount() {
        return $this->validSummitsCount;
    }
}