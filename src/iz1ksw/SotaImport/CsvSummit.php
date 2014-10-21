<?php
/**
 * Created by PhpStorm.
 * User: gabriele
 * Date: 14/07/14
 * Time: 16:21
 */

namespace iz1ksw\SotaImport;


use iz1ksw\SotaImport\date\SotaDateTime;

class CsvSummit {
    /* @var $summitCode string */
    private $summitCode;
    /* @var $summitCode string */
    private $sotaId;
    /* @var $associationName string */
    private $associationName;
    /* @var $associationCode string */
    private $associationCode;
    /* @var $regionName string */
    private $regionName;
    /* @var $regionCode string */
    private $regionCode;
    /* @var $summitName string */
    private $summitName;
    /* @var $altitudeMeters int */
    private $altitudeMeters;
    /* @var $altitudeFeet int */
    private $altitudeFeet;
    /* @var $longitude float */
    private $longitude;
    /* @var $latitude float */
    private $latitude;
    /* @var $points int */
    private $points;
    /* @var $bonusPoints int */
    private $bonusPoints;
    /* @var $validFrom \DateTime */
    private $validFrom;
    /* @var $validTo \DateTime */
    private $validTo;
    /* @var $activationCount int */
    private $activationCount;
    /* @var $lastActivationDate \DateTime */
    private $lastActivationDate;
    /* @var $lastActivationCall string */
    private $lastActivationCall;

    /**
     * @param int $activationCount
     */
    public function setActivationCount($activationCount)
    {
        $this->activationCount = $activationCount;
    }

    /**
     * @return int
     */
    public function getActivationCount()
    {
        return $this->activationCount;
    }

    /**
     * @param int $altitudeFeet
     */
    public function setAltitudeFeet($altitudeFeet)
    {
        $this->altitudeFeet = $altitudeFeet;
    }

    /**
     * @return int
     */
    public function getAltitudeFeet()
    {
        return $this->altitudeFeet;
    }

    /**
     * @param int $altitudeMeters
     */
    public function setAltitudeMeters($altitudeMeters)
    {
        $this->altitudeMeters = $altitudeMeters;
    }

    /**
     * @return int
     */
    public function getAltitudeMeters()
    {
        return $this->altitudeMeters;
    }

    /**
     * @param string $associationName
     */
    public function setAssociationName($associationName)
    {
        $this->associationName = $associationName;
    }

    /**
     * @return string
     */
    public function getAssociationName()
    {
        return $this->associationName;
    }

    /**
     * @param int $bonusPoints
     */
    public function setBonusPoints($bonusPoints)
    {
        $this->bonusPoints = $bonusPoints;
    }

    /**
     * @return int
     */
    public function getBonusPoints()
    {
        return $this->bonusPoints;
    }

    /**
     * @param string $lastActivationCall
     */
    public function setLastActivationCall($lastActivationCall)
    {
        $this->lastActivationCall = $lastActivationCall;
    }

    /**
     * @return string
     */
    public function getLastActivationCall()
    {
        return $this->lastActivationCall;
    }

    /**
     * @param \DateTime $lastActivationDate
     */
    public function setLastActivationDate($lastActivationDate)
    {
        $this->lastActivationDate = $lastActivationDate;
    }

    /**
     * @return \DateTime
     */
    public function getLastActivationDate()
    {
        return $this->lastActivationDate;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param string $regionName
     */
    public function setRegionName($regionName)
    {
        $this->regionName = $regionName;
    }

    /**
     * @return string
     */
    public function getRegionName()
    {
        return $this->regionName;
    }

    /**
     * @param string $summitCode
     */
    public function setSummitCode($summitCode)
    {
        $this->summitCode = $summitCode;
    }

    /**
     * @return string
     */
    public function getSummitCode()
    {
        return $this->summitCode;
    }

    /**
     * @param string $summitName
     */
    public function setSummitName($summitName)
    {
        $this->summitName = $summitName;
    }

    /**
     * @return string
     */
    public function getSummitName()
    {
        return $this->summitName;
    }

    /**
     * @param \DateTime $validFrom
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;
    }

    /**
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param \DateTime $validTo
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;
    }

    /**
     * @return \DateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @return string
     */
    public function getAssociationCode()
    {
        return $this->associationCode;
    }

    /**
     * @param string $associationCode
     */
    public function setAssociationCode($associationCode)
    {
        $this->associationCode = $associationCode;
    }

    /**
     * @return string
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }

    /**
     * @param string $regionCode
     */
    public function setRegionCode($regionCode)
    {
        $this->regionCode = $regionCode;
    }

    /**
     * @return string
     */
    public function getSotaId()
    {
        return $this->sotaId;
    }

    /**
     * @param string $sotaId
     */
    public function setSotaId($sotaId)
    {
        $this->sotaId = $sotaId;
    }





    function __toString()
    {
        return
        "Summit Code:           $this->summitCode\n" .
        "Sota Id:               $this->sotaId\n" .
        "Association Name:      $this->associationName\n" .
        "Association Code:      $this->associationCode\n" .
        "Region Name:           $this->regionName\n" .
        "Region Code:           $this->regionCode\n" .
        "Summit Name:           $this->summitName\n" .
        "Altitude (m):          $this->altitudeMeters\n" .
        "Altitude (ft):         $this->altitudeFeet\n" .
        "Longitude:             $this->longitude\n" .
        "Latitude:              $this->latitude\n" .
        "Points:                $this->points\n" .
        "Bonus Points:          $this->bonusPoints\n" .
        "Valid from:            $this->validFrom\n" .
        "Valid to:              $this->validTo\n" .
        "Activation count:      $this->activationCount\n" .
        "Last Activation date:  $this->lastActivationDate\n" .
        "Last Activation call:  $this->lastActivationCall\n";
    }


}