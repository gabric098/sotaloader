<?php
/**
 * Created by PhpStorm.
 * User: gabriele
 * Date: 11/07/14
 * Time: 19:16
 */

namespace iz1ksw\SotaImport\log;
use Logger;


class SotaLogger {
    private static $log;

    public static function getLogger() {
        if (SotaLogger::$log == null) {
            // initialize the logger
            Logger::configure('logConfig.xml');
            SotaLogger::$log = Logger::getLogger('importLogger');
        }
        return SotaLogger::$log;
    }
} 