<?php
/**
 * Created by PhpStorm.
 * User: gabriele
 * Date: 15/07/14
 * Time: 10:45
 */

namespace iz1ksw\SotaImport\utils;


use DateTimeZone;

class DateUtils {

    /**
     * @param $dateObject \DateTime
     * @return string
     */
    public static function toDatabaseDate($dateObject)
    {
        return $dateObject->format('Y-m-d');
    }
}