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
    const CSV_LOCAL_PATH = 'csv_file';
    const LOG_CONFIG = '';

    /* @var $log Logger */
    private $log;
    /**
     * @var $config
     */
    private $config;
    /**
     * @var array
     */
    private $csvFilePath;
    /**
     * @var string
     */
    private $csvFileName;
    /**
     * @var bool flag for email report sending
     */
    private $sendMail = true;

    function __construct()
    {
        // read initialization file
        $this->config = parse_ini_file('config.ini', true);
        // reads the cvs remote path from config file
        $this->csvFilePath = $this->config['cvs_remote_path'];
        // initialize the logger
        $this->log = SotaLogger::getLogger();
    }

    public function execute() {
        $this->csvFileName = basename($this->csvFilePath);

        // if filepath is an url, copy remote file to a temp file name
        if (!$this->copyCsvToLocal()) {
            exit(0);
        }

        // parse the Csv file
        $parser = new SotaCsvParser(CsvImport::CSV_LOCAL_PATH . '/' . $this->csvFileName . '.tmp');
        $dba = new DbAdapter($this->config);
        while ($parser->parseMoreElement() === true) {
            $dba->addSummits($parser->getCsvArray());
        }

        // send a confirmation email
        if ($this->sendMail && isset($this->config['mail_to']) && $this->config['mail_to'] != '') {
            $this->log->info("Sending confirmation email to " . $this->config['mail_to']);
            $this->sendMail($dba->getOutput(), $parser->getErrors());
        }

        // rename temporary file with date
        if (!rename(CsvImport::CSV_LOCAL_PATH . '/' . $this->csvFileName . '.tmp', CsvImport::CSV_LOCAL_PATH . '/' .
            date('Ymd') . '_'. $this->csvFileName)) {
            $this->log->error("Unable to rename temporary file");
        }
        $this->log->info("CSV file process finished.");
    }

    private function copyCsvToLocal()
    {
        if (copy($this->csvFilePath, CsvImport::CSV_LOCAL_PATH . '/' . $this->csvFileName . '.tmp')) {
            $this->log->info("Csv file sucessfully copyied to " . CsvImport::CSV_LOCAL_PATH . '/' . $this->csvFileName . '.tmp');
            return true;
        } else {
            $this->log->error("Error copying csv file from remote location.");
            return false;
        }
    }

    private function sendMail($results, $parserErrors) {
        $message = "Import execution result:\r\n";
        if (count($parserErrors) > 0) {
            $message .= "ERROR on CSV on:\r\n";
            foreach ($parserErrors as $parserError) {
                $message .= "line: " . $parserError. "\r\n";
            }
        }

        $message .= "-------------------------------------------------------------------------------------------\r\n";
        $message .= "New associations: " . count($results["new_assoc"]) . "\r\n";
        $message .= $this->printArrayVals($results["new_assoc"]) . "\r\n";
        $message .= "New regions: " . count($results["new_region"]) . "\r\n";
        $message .= $this->printArrayVals($results["new_region"]) . "\r\n";
        $message .= "New summits: " . count($results["new_summit"]) . "\r\n";
        $message .= $this->printArrayVals($results["new_summit"]) . "\r\n";
        $message .= "Updated associations: " . count($results["upd_assoc"]) . "\r\n";
        $message .= $this->printArrayVals($results["upd_assoc"]) . "\r\n";
        $message .= "Updated regions: " . count($results["upd_region"]) . "\r\n";
        $message .= $this->printArrayVals($results["upd_region"]) . "\r\n";
        $message .= "Updated summits: " . count($results["upd_summit"]) . "\r\n";
        $message .= $this->printArrayVals($results["upd_summit"]);
        $message .= "-------------------------------------------------------------------------------------------";
        mail($this->config['mail_to'], "[SotaImport] Result report", $message, "From: " . $this->config['mail_from']);
    }

    private function printArrayVals($array) {
        $msg = '';
        foreach($array as $val ) {
            $msg .= $val . "\r\n";
        }
        return $msg;
    }

    /**
     * Enable or disable sending email report
     * @param bool $sendMail
     */
    public function setSendMail($sendMail) {
        $this->sendMail = $sendMail;
    }

    /**
     * Set the csv file location (overrides the location read from ini file)
     * @param $csvFilePath
     */
    public function setCsvFilePath($csvFilePath) {
        $this->csvFilePath = $csvFilePath;
    }
} 