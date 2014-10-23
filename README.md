sotaloader
==========

SOTA API data loader

## How to use it
1. Install composer using the command `curl -sS https://getcomposer.org/installer | php`
2. Use the command `php composer.phar install` to install all the required libraries
3. Create the database structure using the sql script provided in sql/db.sql 
4. Configure your database connection editing `config.ini`
5. If you want to receive a mail report, configure your email settings in `config.ini`
6. Run `php cli.php [options]`
Options:
  `--version`               Display the current version
  `--nomail`                Do not send report email
  `--csvfile <arg>`         Use the specified path as csv input file