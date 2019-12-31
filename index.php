<?php
/**
 * Main entry point
 *
 * PHP version 8
 *
 * @category WebApplication
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
use FWieP as F;
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
define('_CONFIGPATH', __DIR__.'/config.php');

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
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/constants.php';
require_once __DIR__.'/localesetup.php';

$code = filter_input(INPUT_GET, 'code', FILTER_DEFAULT);
$trigger = filter_input(INPUT_GET, 'trigger', FILTER_VALIDATE_INT);
$error = filter_input(INPUT_GET, 'error', FILTER_VALIDATE_INT);

$out = [
    'result' => 'OK',
    'httpStatus' => 200,
    'errorMessage' => null
];
/**
 * Outputs the given data as a final JSON response
 * 
 * @param array $out the data to output
 * 
 * @global $code
 * @return void
 */
function outHTML(&$out) : void
{
    global $lang, $supportedLangs, $code;
    http_response_code($out['httpStatus']);
    header('X-Robots-Tag: noindex', false);
    include_once 'views/client.php';
    exit;
}
if (is_null(RD::g()->pdo)) {
    $out['result'] = 'FAIL';
    $out['httpStatus'] = 500;
    $out['errorMessage'] = _("Failed to establish connection to database.");
    outHTML($out);
}
if (defined('_FWIEPADMIN')) {
    exit;
}
if (!is_null($error)) {
    $out['result'] = 'FAIL';
    $out['httpStatus'] = $error;
    $out['errorMessage'] = _("Specified page could not be found.");
    outHTML($out);
}
if ('GET' != strtoupper($_SERVER['REQUEST_METHOD'])) {
    $out['result'] = 'FAIL';
    $out['httpStatus'] = 405;
    $out['errorMessage'] = _("Specified HTTP-method is not allowed.");
    outHTML($out);
}
if (is_null($code) || empty($code)) {
    $out['result'] = 'OK';
    $out['httpStatus'] = 204;
    $out['errorMessage'] = _("Nothing to see here, please move along.");
    outHTML($out);
}
$xs = F\UrlShortener::getUrls(null, $code);
if (!$xs) {
    $out['result'] = 'FAIL';
    $out['httpStatus'] = 404;
    $out['errorMessage'] = _("Download not found.");
    outHTML($out);
}
/**
 * The URL object to process
 * 
 * @var F\UrlShortener $u 
 */
$u = array_shift($xs);

if ($u->getValidFrom() && new \DateTime() < $u->getValidFrom()) {
    $out['result'] = 'FAIL';
    $out['httpStatus'] = 403;
    $out['errorMessage'] = _("Download is not yet valid.");
    outHTML($out);
}
if ($u->getValidUntil() && new \DateTime() >= $u->getValidUntil()) {
    $out['result'] = 'FAIL';
    $out['httpStatus'] = 403;
    $out['errorMessage'] = _("Download is no longer valid.");
    outHTML($out);
}
if (1 == $trigger) {

    // Increment the download count for this URL
    $u->incrementDownloadCount();

    if ($u->getIsLocal()) {
        // Serve the file locally
        $u->serveLocalFile();
        exit;

    } else {
        // Redirect external URLs using HTTP 303 (See Other) header
        header('Location: '.$u->getLongUrl(), true, 303);
        exit;
    }   
}
outHTML($out);
