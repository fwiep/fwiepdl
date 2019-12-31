<?php
/**
 * Partial HTML view - footer
 *
 * PHP version 8
 *
 * @category WebApplication
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
?>
    </div><!-- /.container -->
    <script src="js/admin.min.js?v=<?php print MD5_ADMIN_JS ?>"
      integrity="<?php print SHA_ADMIN_JS ?>"></script>
  </body>
</html>