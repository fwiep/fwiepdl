<?php
/**
 * Administrative view
 *
 * PHP version 8
 *
 * @category WebApplication
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
use FWieP\RuntimeData as RD;
use FWieP\App;

if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
$msgInpUsername = 'You did not provide a (valid) username!';
$msgInpLoginPassword = 'You did not provide a (valid) password!';

$inpUsername = RD::g()->xml->createElement('input');
$inpUsername->setAttribute('id', 'inpUsername');
$inpUsername->setAttribute('name', 'inpUsername');
$inpUsername->setAttribute('type', 'text');
$inpUsername->setAttribute('maxlength', 100);
$inpUsername->setAttribute('required', 'required');
$inpUsername->setAttribute('autocomplete', 'on');
$inpUsername->setAttribute('class', 'form-control');

$inpLoginPassword = RD::g()->xml->createElement('input');
$inpLoginPassword->setAttribute('id', 'inpLoginPassword');
$inpLoginPassword->setAttribute('name', 'inpLoginPassword');
$inpLoginPassword->setAttribute('type', 'password');
$inpLoginPassword->setAttribute('maxlength', 200);
$inpLoginPassword->setAttribute('required', 'required');
$inpLoginPassword->setAttribute('autocomplete', 'off');
$inpLoginPassword->setAttribute('class', 'form-control');
$inpLoginPassword->setAttribute('data-add-toggle', 'true');

do {
    if ('GET' == $_SERVER['REQUEST_METHOD']) {
        $inpUsername->setAttribute('autofocus', 'autofocus');
        $_SESSION['token'] = md5(uniqid(mt_rand(), true));
        break;
    
    } else if ('POST' != $_SERVER['REQUEST_METHOD']) {
        header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
        exit;
    }
    $token = filter_input(INPUT_POST, '_token', FILTER_DEFAULT);
    if (!$token
        || !array_key_exists('token', $_SESSION)
        || $token !== $_SESSION['token']
    ) {
        header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
        exit;
    }
    if (!array_key_exists('inpUsername', $_POST)) {
        $inpUsername->setAttribute('class', 'form-control is-invalid');
        $msgInpUsername = 'You did not provide a username!';
        break;
    }
    $valUsername = $_POST['inpUsername'];
    $inpUsername->setAttribute('value', htmlspecialchars($valUsername));

    if (!array_key_exists('inpLoginPassword', $_POST)) {
        $inpLoginPassword->setAttribute('class', 'form-control is-invalid');
        $msgInpLoginPassword = 'You did not provide a password!';
        break;
    }
    $valPassword = $_POST['inpLoginPassword'];
    if (empty(trim($valPassword))) {
        $inpLoginPassword->setAttribute('class', 'form-control is-invalid');
        $msgInpLoginPassword = 'You provided an empty password!';
        break;
    }
    if (!array_key_exists($valUsername, DL_USERS)) {
        http_response_code(401);
        $inpUsername->setAttribute('class', 'form-control is-invalid');
        $msgInpUsername = 'The provided username is unknown!';
        break;
    }
    if (DL_USERS[$valUsername] !== $valPassword) {
        http_response_code(401);
        $inpLoginPassword->setAttribute('class', 'form-control is-invalid');
        $msgInpLoginPassword = 'The provided password is incorrect!';
        break;
    }
    RD::g()->logInUser($valUsername);
    header('Location:'.App::getHrefBase().'admin/home');
    exit;

} while (false);

require_once __DIR__.'/../views/admin-header.php';
?>

<main>
<h1>Login</h1>
<hr />

<form method="post" enctype="application/x-www-form-urlencoded"
  action="<?php print htmlspecialchars($_SERVER["REQUEST_URI"]);?>">

  <fieldset>
  <legend class="visually-hidden">Provide your login details</legend>

  <input type="hidden" id="inpSender" name="inpSender" />
  <input type="hidden" name="_token" value="<?php print $_SESSION['token'] ?>" />

  <div class="mb-3">
    <label class="form-label" for="inpUsername">Username <em>*</em></label>
    <?php print RD::g()->xml->saveHTML($inpUsername) ?>
    <div class="invalid-feedback"><?php print $msgInpUsername ?></div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="inpLoginPassword">Password <em>*</em></label>
    <?php print RD::g()->xml->saveHTML($inpLoginPassword) ?>
    <div class="invalid-feedback"><?php print $msgInpLoginPassword ?></div>
  </div>

  <div class="mb-0">
    <input type="submit" class="btn btn-primary" id="btnSubmit"
        name="btnSubmit" value="Log in" />
  </div>

  </fieldset>
  
</form>

</main>

<?php
require_once __DIR__.'/../views/admin-footer.php';
