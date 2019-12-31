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
use FWieP as F;

if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
/**
 * Parse a string to get the equivalent amount of bytes
 * 
 * @param string $s the size to parse
 * 
 * @return int
 */
function parseSize(string $s) : int
{
    $m = [];
    if (preg_match('!^([0-9]+)([kmgt]b?)?$!i', $s, $m) !== 1) {
        return 0;
    }
    $nr = intval($m[1]);
    $suff = '';

    if (array_key_exists(2, $m)) {
        $suff = $m[2];
    }
    switch (strtoupper($suff)) {
    
    default:
        $pow = 1;
        break;

    case 'K':
        $pow = 1024;
        break;

    case 'M':
        $pow = 1024 * 1024;
        break;

    case 'G':
        $pow = 1024 * 1024 * 1024;
        break;
    
    case 'T':
        $pow = 1024 * 1024 * 1024 * 1024;
        break;
    }
    return $nr * $pow;
}

/**
 * Transforms given string to valid DateTime or NULL on failure 
 * 
 * @param string $dt the datetime to transform
 * 
 * @return DateTime|NULL
 */
function safeDt(string $dt) : ?\DateTime
{
    if (empty($dt)) {
        return null;
    }
    try {
        $dt = new \DateTime($dt);
        return $dt;
    } catch (\Exception $e) {
        return null;
    }
}
/**
 * Checks whether given string is valid JSON format
 * 
 * @param string $str the string to validate
 * 
 * @return bool
 */
function isValidJSON(string $str) : bool
{
    @json_decode($str);
    return json_last_error() == JSON_ERROR_NONE;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST)) {
        $postdata = $_POST;
    } else {
        $postdata = file_get_contents("php://input");
        if (strlen($postdata) > 0 && isValidJSON($postdata)) {
            $postdata = json_decode($postdata, true);
        }
    }
    $token = (isset($postdata['_token']) ? $postdata['_token'] : null);
    if (!$token
        || !array_key_exists('token', $_SESSION)
        || $token !== $_SESSION['token']
    ) {
        header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
        exit;
    }
    $o = ['result' => false];
    try
    {
        // See https://stackoverflow.com/q/7852910 for details
        if (empty($postdata)) {
            throw new \Exception("File exceeds the post_max_size directive!");
        }
        $cmd = (isset($postdata['cmd']) ? $postdata['cmd'] : null);
        $inpId = (isset($postdata['inpId']) ? $postdata['inpId'] : null);
        $uuid = (isset($postdata['uuid']) ? $postdata['uuid'] : null);
        $inpUuid = (isset($postdata['inpUuid']) ? $postdata['inpUuid'] : null);
        $inpType = (isset($postdata['inpType']) ? $postdata['inpType'] : null);
        $inpValidFrom = (isset($postdata['inpValidFrom'])
            ? $postdata['inpValidFrom'] : null);
        $inpValidUntil = (isset($postdata['inpValidUntil'])
            ? $postdata['inpValidUntil'] : null);
        $inpLongUrl = (isset($postdata['inpLongUrl'])
            ? $postdata['inpLongUrl'] : null);

        if (empty($inpType)) {
            throw new \Exception("Upload type (external/local) not selected!");
        }
        if ($cmd == 'delete') {
            $u = F\UrlShortener::getUrls($uuid);
            $u = array_shift($u);
            $ok = ($u && ($ok = $u->delete()));
            if ($u->getIsLocal()) {
                $ok = ($ok && unlink(LOCAL_STORAGE_FOLDER.'/'.$uuid));
            }
            $o['result'] = $ok;
        }
        if ($cmd == 'edit') {
            $u = F\UrlShortener::getUrls($inpUuid);
            $u = array_shift($u);
            if (!$u || $u->getId() != $inpId) {
                throw new \Exception("Item to edit was not found!");
            }
            $u->setValidFrom(null);
            $u->setValidUntil(null);
            $u->setValidFrom(safeDt($inpValidFrom));
            $u->setValidUntil(safeDt($inpValidUntil));
            
            if ($u->getIsLocal()) {
                // Do nothing, local files cannot be changed
            } else {
                $u->setLongUrl($inpLongUrl);
            }
            $o['result'] = $u->save();
        }
        if ($cmd == 'new') {
            $u = new F\UrlShortener();
            $u->setValidFrom(safeDt($inpValidFrom));
            $u->setValidUntil(safeDt($inpValidUntil));
            
            if ($inpType == 'external') {
                $u->setLongUrl($inpLongUrl);
            
            } else if ($inpType == 'local') {
                if (!array_key_exists('localfile', $_FILES)) {
                    throw new \Exception("No local file was uploaded!");
                }
                if ($_FILES['localfile']['error'] != UPLOAD_ERR_OK) {
                    $msg = 'Upload failed, sorry! ('
                        .$_FILES['localfile']['error'].')';
                    
                    switch ($_FILES['localfile']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $msg = 'Uploaded filesize is too large!';
                        break;
                    }
                    throw new \Exception($msg);
                }
                $fn = $_FILES['localfile']['tmp_name']; 
                $u->examineLocalFile($fn);
                $u->setFileNameAndExtension($_FILES['localfile']['name']);
                move_uploaded_file($fn, LOCAL_STORAGE_FOLDER.'/'.$u->getUuid());
            }
            $o['result'] = $u->save();
        }
    } catch (\Exception $e) {
        $o = ['error' => $e->getMessage()];
    }
    header('Content-Type: application/json');
    print json_encode($o);
    exit;

} elseif ('GET' == $_SERVER['REQUEST_METHOD']) {
    $_SESSION['token'] = md5(uniqid(mt_rand(), true));
}
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
    exit;
}
$o = '';
$urls = F\UrlShortener::getUrls();

foreach ($urls as $u) {
    $lis = [];
    $lis[] = sprintf('Type: %s', $u->getIsLocal() ? 'LOCAL' : 'EXTERNAL');
    $lis[] = sprintf('Downloads: %d', $u->getDownloadCount());
    $lis[] = sprintf(
        'Short code: <a class="dl-link" target="_blank" href="%1$s%2$s"
          >%2$s</a>',
        F\App::getHrefBase(),
        htmlspecialchars($u->getShortUrl())
    );
    if ($u->getLongUrl()) {
        $lis[] = sprintf(
            'Long URL: <a href="%1$s" target="_blank">%1$s</a>',
            htmlspecialchars($u->getLongUrl())
        );
    }
    if ($u->getValidFrom()) {
        $lis[] = sprintf(
            'Valid from: %s',
            $u->getValidFrom()->format('Y-m-d H:i:s')
        );
    }
    if ($u->getValidUntil()) {
        $lis[] = sprintf(
            'Valid until: %s',
            $u->getValidUntil()->format('Y-m-d H:i:s')
        );
    }
    if ($u->getIsLocal()) {
        $lis[] = sprintf(
            'Original filename: %s%s',
            htmlspecialchars($u->getFileName()),
            htmlspecialchars($u->getFileExtension())
        );
        $lis[] = sprintf(
            'MIME-type: %s (%s)',
            htmlspecialchars($u->getMimeType()),
            htmlspecialchars($u->getMimeEncoding())
        );
        $lis[] = sprintf(
            'Filesize: %s',
            htmlspecialchars(F\App::formatBytes($u->getFileSize()))
        );
    }
    //$lis[] = sprintf('Created: %s', $u->getCreated()->format('Y-m-d H:i:s'));
    //$lis[] = sprintf('UUID: %s', $u->getUuid());
    
    $o .= sprintf(
        '<div class="list-group-item">
          <div class="row align-items-center">
            <ul class="col-sm-8 ps-4 mb-0">
              %1$s
            </ul>
            <div class="col-sm-4" data-obj=\'%2$s\'>
             <button class="btn btn-primary btn-copy"
               >Copy</button>
             <button class="btn btn-primary btn-download" data-cmd="download"
               >Download</button>
             <button class="btn btn-primary btn-edit" data-cmd="edit"
               >Edit</button>
             <button class="btn btn-danger btn-delete" data-cmd="delete"
               >Delete</button>
            </div>
          </div>
        </div>',
        array_reduce(
            $lis,
            function ($c, $i) {
                return $c.'<li>'.$i.'</li>';
            }
        ),
        str_replace("'", "&#39;", json_encode($u->getProps()))
    );
}
$oMinValidFrom = new \DateTime();
$oMinValidFrom = $oMinValidFrom->format('Y-m-d');
$oMinValidUntil = new \DateTime();
$oMinValidUntil = $oMinValidUntil->format('Y-m-d');

require_once __DIR__.'/../views/admin-header.php';
?>

<main>
<h1 class="float-start">Uploads and URLs</h1>
<a class="btn btn-primary float-end" href="admin/logout">Log out</a>
<div class="clearfix"></div>
<hr />

<div class="modal fade" tabindex="-1" aria-hidden="true"
  aria-labelledby="modaltitle">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="modaltitle">Uploading...</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body text-center collapse" id="errormsg">
      </div>
      <div class="modal-body text-center" id="uploadmsg">
        <img src="loading.gif" alt="Upload animation" />
        <div class="clearfix"></div>
        <div class="progress" style="height: 2em">
          <div class="progress-bar progress-bar-striped progress-bar-animated"
            id="pbar" role="progressbar" aria-valuemin="0" aria-valuemax="100"
            aria-valuenow="0">0%</div>
        </div>
      </div><!-- /.modal-body -->
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<form class="ajax" method="post" enctype="multipart/form-data"
  action="<?php print htmlspecialchars($_SERVER["REQUEST_URI"]);?>">

  <input type="hidden" name="_token" value="<?php print $_SESSION['token'] ?>" />

  <fieldset>
  <legend class="visually-hidden">Create a new URL or upload</legend>

  <input type="hidden" name="cmd" id="cmd" value="new" />
  <input type="hidden" name="inpId" id="inpId" />  
  <input type="hidden" name="inpUuid" id="inpUuid" />
  
  <div class="mb-3 row">
    <span class="col-sm-2 form-label">Type: <em>*</em></span>
    <div class="col-sm-10">
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inpType"
          id="inpTypeExternal" value="external" required>
        <label class="form-check-label" for="inpTypeExternal">EXTERNAL</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="inpType"
          id="inpTypeLocal" value="local" required>
        <label class="form-check-label" for="inpTypeLocal">LOCAL</label>
      </div>
    </div>
  </div>
  
  <div class="mb-3 row">
    <label class="col-sm-2 form-label" for="inpValidFrom">Valid FROM:</label>
    <div class="col-sm-10">
      <input class="form-control" type="date" name="inpValidFrom"
          id="inpValidFrom" min="<?php print $oMinValidFrom ?>" />
    </div>
  </div>
  
  <div class="mb-3 row">
    <label class="col-sm-2 form-label" for="inpValidUntil">Valid UNTIL:</label>
    <div class="col-sm-10">
      <input class="form-control" type="date" name="inpValidUntil"
          id="inpValidUntil" min="<?php print $oMinValidUntil ?>" />
    </div>
  </div>
  
  <div id="cardExternal" class="collapse">
    <div class="mb-3 row">
      <label class="col-sm-2 form-label" for="inpLongUrl"
        >Original URL: <em>*</em></label>
      <div class="col-sm-10">
        <input type="text" id="inpLongUrl" name="inpLongUrl"
          class="form-control" maxlength="400" />
      </div>
    </div>
  </div><!-- /#cardExternal -->
    
  <div id="cardLocal" class="collapse">
  <div class="mb-3 row">
    <label class="col-sm-2 form-label" for="inpLocalFile"
        >Local file: <em>*</em></label>
    <div class="col-sm-10">
      <label for="inpReadOnlyFilename" class="visually-hidden"
        >Local file being edited:</label>
      <input type="text" readonly class="form-control collapse"
        id="inpReadOnlyFilename" />
      <input type="hidden" name="MAX_FILE_SIZE"
        value="<?php print parseSize(ini_get('upload_max_filesize')) ?>" />
      <input type="file" class="form-control collapse show" id="inpLocalFile">
      <small class="form-text text-muted">The maximum allowed filesize is <?php
          print F\App::formatBytes(parseSize(ini_get('upload_max_filesize')))
        ?>.</small>
      
    </div>
  </div>
  </div><!-- /#cardLocal -->
  
  <div class="mb-3 row">
    <div class="d-none d-sm-block col-sm-2">&nbsp;</div>
    <div class="col-sm-10">
      <input class="btn btn-primary" type="submit" value="Submit" />
    </div>
  </div>

  </fieldset>
  
</form>

<div class="mb-4">
<label for="filterUrl" class="visually-hidden">Filter results</label>
<input class="form-control" id="filterUrl" name="filterUrl"
  type="text" placeholder="Search...">
</div>

<div class="list-group">
  <?php print $o ?>
</div>

</main>

<?php
require_once __DIR__.'/../views/admin-footer.php';
