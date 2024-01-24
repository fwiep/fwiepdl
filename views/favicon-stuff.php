<?php
/**
 * HTML fragment containing favicon related link- and meta tags
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
$prefix = App::getHrefBase();?>

<link rel="apple-touch-icon" sizes="180x180"
  href="<?php print $prefix ?>apple-touch-icon.png?v=3" />
<link rel="icon" type="image/png" sizes="32x32"
  href="<?php print $prefix ?>favicon-32x32.png?v=3" />
<link rel="icon" type="image/png" sizes="192x192"
  href="<?php print $prefix ?>android-chrome-192x192.png?v=3" />
<link rel="icon" type="image/png" sizes="16x16"
  href="<?php print $prefix ?>favicon-16x16.png?v=3" />
<link rel="manifest"
  href="<?php print $prefix ?>site.webmanifest?v=3" />
<link rel="mask-icon" color="#5bbad5"
  href="<?php print $prefix ?>safari-pinned-tab.svg?v=3" />
<link rel="shortcut icon"
  href="<?php print $prefix ?>favicon.ico?v=3" />

<meta name="apple-mobile-web-app-title" content="FWieP Download Service" />
<meta name="application-name" content="FWieP Download Service" />
<meta name="msapplication-TileColor" content="#da532c" />
<meta name="theme-color" content="#ffffff" />
