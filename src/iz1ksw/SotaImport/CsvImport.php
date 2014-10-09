<?php
/**
 * Created by PhpStorm.
 * User: gabriele
 * Date: 11/07/14
 * Time: 17:15
 */
namespace iz1ksw\SotaImport;
use iz1ksw\SotaImport\log\SotaLogger;
use iz1ksw\SotaImport\db\DbAdapter;
use Logger;


class CsvImport {
    const CSV_REMOTE_PATH = 'http://www.sotadata.org.uk/summitslist.csv';
    const CSV_LOCAL_PATH = 'csv_file';
    const CSV_LOCAL_TEMP_NAME = 'summitslist.csv.tmp';
    const LOG_CONFIG = '';

    /* @var $log Logger */
    private $log;

    function __construct()
    {
        // initialize the logger
        $this->log = SotaLogger::getLogger();

        // start csv file import
        $this->execute();
    }

    public function execute() {
        // copy remote file to a temp file name
        if (!$this->copyCsvToLocal()) {
            exit(0);
        }

        // parse the Csv file
        $parser = new SotaCsvParser(CsvImport::CSV_LOCAL_PATH . '/' . CsvImport::CSV_LOCAL_TEMP_NAME);
        $dba = new DbAdapter();
        while ($parser->parseMoreElement() === true) {
            $dba->addSummits($parser->getCsvArray());
        }
    }

    private function copyCsvToLocal()
    {
        if (copy(CsvImport::CSV_REMOTE_PATH, CsvImport::CSV_LOCAL_PATH . '/' . CsvImport::CSV_LOCAL_TEMP_NAME)) {
            $this->log->info("Csv file sucessfully copyied to " . CsvImport::CSV_LOCAL_PATH . '/' . CsvImport::CSV_LOCAL_TEMP_NAME);
            return true;
        } else {
            $this->log->error("Error copying csv file from remote location.");
            return false;
        }
    }
} 