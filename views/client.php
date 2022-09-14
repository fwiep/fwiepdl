<?php
/**
 * Client view
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
if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
$xml = RD::g()->xml;
$html = $xml->createElement('div');

$langSwitcher = $xml->createElement('div');
$langSwitcher->setAttribute('class', 'btn-group position-absolute');
$langSwitcher->setAttribute('role', 'group');
$langSwitcher->setAttribute('aria-label', _('Language switcher'));

foreach (array_keys($supportedLangs) as $l) {
    $btn = $xml->createElement('a', $supportedLangs[$l]);
    $btn->setAttribute('role', 'button');
    $btn->setAttribute('lang', $l);
    $btn->setAttribute('href', $l.'/'.$code);
    if ($l == $lang) {
        $btn->setAttribute('class', 'btn btn-outline-dark active');
    } else {
        $btn->setAttribute('class', 'btn btn-outline-dark');
    }
    $langSwitcher->appendChild($btn);
}
if (in_array($out['httpStatus'], [200, 201])) {
    
    // All is well in the world...
    $p = $xml->createElement('p');
    
    $p->appendChild(
        $xml->createTextNode(_('Your download should start automatically in '))
    );
    $spnTimeOut = $xml->createElement('span', DOWNLOAD_TIMEOUT);
    $spnTimeOut->setAttribute('aria-live', 'polite');
    $spnTimeOut->setAttribute('id', 'spnTimeOut');
    $p->appendChild($spnTimeOut);

    $p->appendChild($xml->createTextNode(_(' seconds.')));
    $p->appendChild($xml->createElement('br'));
    $p->appendChild($xml->createTextNode(_('If it doesn\'t, please ')));

    $aDirect = $xml->createElement('a', _('start the download manually'));
    $aDirect->setAttribute('id', 'aDirectDownload');
    $aDirect->setAttribute('href', $code.'/1');
    $p->appendChild($aDirect);

    $p->appendChild($xml->createTextNode(_('.')));

    $html->appendChild($p);

} else {
    // An error occurred!
    $pError = $xml->createElement('p');
    $pError->setAttribute('class', 'alert alert-danger');
    $pError->setAttribute('role', 'alert');

    $pError->appendChild($xml->createTextNode(_('An error occurred!')));
    $pError->appendChild($xml->createElement('br'));
    $pError->appendChild($xml->createTextNode($out['errorMessage']));

    $html->appendChild($pError);
}
require_once __DIR__.'/client-header.php';
?>
<div class="row h-100">
  <main class="col my-auto text-center">

    <h1><?php print _('Download') ?></h1>
    <?php print $xml->saveHTML($html) ?>

  </main>
</div>
<a target="_blank" id="gitbanner" href="https://github.com/fwiep/fwiepdl"
  ><?php print _('Fork me on GitHub') ?></a>
<?php
require_once __DIR__.'/client-footer.php';
