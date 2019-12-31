<?php
/**
 * Logout confirmation page
 *
 * PHP version 8
 *
 * @category LoginForm
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
session_unset();
session_destroy();
setcookie("PHPSESSID", "", 1); // Force the cookie to expire.

require_once __DIR__.'/../views/admin-header.php';
?>
<main>

<h1>Logged out</h1>
<hr />

<p>You have been logged out successfully.</p>
<a class="btn btn-sm btn-primary" href="admin/login">Return to login form</a>

</main>
<?php
require_once __DIR__.'/../views/admin-footer.php';