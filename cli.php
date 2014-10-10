<?php
$root = __DIR__;
$loader = require $root.'/vendor/autoload.php';

chdir(dirname(__FILE__));
\iz1ksw\SotaImport\Cli::main();
