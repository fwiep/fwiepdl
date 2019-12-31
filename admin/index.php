<?php
/**
 * Placeholder for administrative access to the application 
 *
 * PHP version 8
 *
 * @category WebApplication
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
use FWieP\DatabaseBackup as DB;
use FWieP\DatabaseSetup as DS;
use FWieP\RuntimeData as RD;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('date.timezone', 'Europe/Amsterdam');
date_default_timezone_set('Europe/Amsterdam');
mb_internal_encoding('UTF-8');

ini_set('session.gc_maxlifetime', '10800');
ini_set('max_input_time', '10800');
ini_set('max_execution_time', '10800');

define('_FWIEPEXEC', true);
define('_FWIEPADMIN', true);
define('_CONFIGPATH', __DIR__.'/../config.php');

if (file_exists(_CONFIGPATH)) {
    include_once _CONFIGPATH;
} else {
    http_response_code(503);
    header('Content-Type: text/plain;charset=utf-8');
    die(
        "Configuration file does not exist! Exiting.\n\n" .
        "Please create it by copying *config.template.php*\n" .
        "to *config.php* and then editing it to your needs."
    );
}
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../constants.php';
require_once __DIR__.'/../localesetup.php';

// Start the global session
session_set_cookie_params(['samesite' => 'Strict']);
@session_start();

if ('backup' == RD::g()->qsPage) {
    header('Content-Type:application/json');
    print json_encode(DB::sendBackup());
    exit;

} else if ('setup' == RD::g()->qsPage) {
    header('Content-Type:application/json');
    print json_encode(DS::setup());
    exit;
    
} else if (!RD::g()->isUserLoggedIn()) {
    include_once __DIR__.'/login.php';

} else if (file_exists(__DIR__.'/'.RD::g()->qsPage.'.php')) {
    include_once __DIR__.'/'.RD::g()->qsPage.'.php';
}
exit;
