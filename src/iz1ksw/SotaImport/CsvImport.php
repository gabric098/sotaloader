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
    /**
     * @var $config
     */
    private $config;

    function __construct()
    {
        // read initialization file
        $this->config = parse_ini_file('config.ini', true);
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
        $dba = new DbAdapter($this->config);
        while ($parser->parseMoreElement() === true) {
            $dba->addSummits($parser->getCsvArray());
        }

        $this->sendMail($dba->getOutput());
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

    private function sendMail($results) {
        $message = "Import execution result:\r\n";
        $message .= "-------------------------------------------------------------------------------------------\r\n";
        $message .= "New associations: " . count($results["new_assoc"]);
        $message .= $this->printArrayVals($results["new_assoc"]);
        $message .= "New regions: " . count($results["new_region"]);
        $message .= $this->printArrayVals($results["new_region"]);
        $message .= "New summits: " . count($results["new_summit"]);
        $message .= $this->printArrayVals($results["new_summit"]);
        $message .= "Updated associations: " . count($results["upd_assoc"]);
        $message .= $this->printArrayVals($results["upd_assoc"]);
        $message .= "Updated regions: " . count($results["upd_region"]);
        $message .= $this->printArrayVals($results["upd_region"]);
        $message .= "Updated summits: " . count($results["upd_summit"]);
        $message .= $this->printArrayVals($results["upd_summit"]);
        $message .= "-------------------------------------------------------------------------------------------\r\n";
        mail($this->config['mail_to'], "[SotaImport] Result report", $message, "From: " . $this->config['mail_from']);
    }

    private function printArrayVals($array) {
        $msg = '';
        foreach($array as $val ) {
            $msg .= $val . "\r\n";
        }
        return $msg;
    }
} 