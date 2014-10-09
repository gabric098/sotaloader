<?php
/**
 * Created by PhpStorm.
 * User: gabriele
 * Date: 17/09/2014
 * Time: 17:24
 */
namespace iz1ksw\SotaImport\utils;

class SummitCode {
    private $summitCode;

    function __construct($summitCode)
    {
        $this->summitCode = $summitCode;
    }

    function getAssociationCode() {
        return substr($this->summitCode, 0, strpos($this->summitCode, '/'));
    }

    function getRegionCode() {
        return substr($this->summitCode, strpos($this->summitCode, '/')+1, (strpos($this->summitCode, '-')-strpos($this->summitCode, '/'))-1);
    }

    function getSummitId() {
        return substr($this->summitCode, strpos($this->summitCode, '-')+1);
    }
}