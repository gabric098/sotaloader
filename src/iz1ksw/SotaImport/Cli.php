<?php
/**
 * Created by PhpStorm.
 * User: gabriele
 * Date: 11/07/14
 * Time: 16:09
 */

namespace iz1ksw\SotaImport;
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;


class Cli {
    /**
     * The application version
     */
    const VERSION = '1.0.0-alpha1';

    public static function main() {
        // manage the command line options
        $getopt = new Getopt(array(
            (new Option(null, 'version', Getopt::NO_ARGUMENT))
                ->setDescription('Display the current version'),
            (new Option(null, 'nomail', Getopt::NO_ARGUMENT))
                ->setDescription('Do not send report email'),
            (new Option(null, 'csvfile', Getopt::REQUIRED_ARGUMENT))
                ->setDescription('Use the specified path as csv input file'),
        ));

        try {
            $getopt->parse();
            if ($getopt->getOption('version') > 0) {
                echo("SotaImport version: " . Cli::VERSION . "\r\n");
                exit(1);
            }
            $nomail = $getopt->getOption('nomail');
            $csvfile = $getopt->getOption('csvfile');
        } catch (\UnexpectedValueException $e) {
            echo "Error: ".$e->getMessage()."\n";
            echo $getopt->getHelpText();
            exit(1);
        }

        $csvImport = new CsvImport();
        if ($nomail > 0) {
            $csvImport->setSendMail(false);
        }
        if (isset($csvfile) && $csvfile != '') {
            $csvImport->setCsvFilePath($csvfile);
        }
        $csvImport->execute();
    }
}