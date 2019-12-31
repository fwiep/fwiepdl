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
use FWieP as F;

if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <base href="<?php print F\App::getHrefBase() ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FWieP download service - Administration</title>
    <link rel="stylesheet"
      href="css/admin.min.css?v=<?php print MD5_ADMIN_CSS ?>"
      integrity="<?php print SHA_ADMIN_CSS ?>" />
    <link rel="icon" type="image/png"
      href="favicon.png?v=<?php print MD5_ICO ?>">
  </head>
  <body>
  
  <div class="container">
