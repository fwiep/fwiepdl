<?php
/**
 * Partial HTML view - header
 *
 * PHP version 8
 *
 * @category WebApplication
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
use FWieP\App;

if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
?><!DOCTYPE html>
<html lang="<?php print $lang ?>" class="h-100">
  <head>
    <meta charset="utf-8" />
    <base href="<?php print App::getHrefBase() ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex" />
    <title><?php print _('FWieP download service') ?></title>
    <link rel="stylesheet"
      href="css/client.min.css?v=<?php print MD5_CLIENT_CSS ?>"
      integrity="<?php print SHA_CLIENT_CSS ?>" />
    <?php require_once 'views/favicon-stuff.php'; ?>
    <script>var fwiepdl = {
      'download_timeout': <?php print DOWNLOAD_TIMEOUT ?> }</script>
  </head>
  <body class="h-100">

  <?php print $xml->saveHTML($langSwitcher) ?>
  
  <div class="container h-100">
