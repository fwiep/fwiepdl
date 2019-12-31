<?php
/**
 * Static stuff class
 *
 * PHP version 8
 *
 * @category WebApplication
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
namespace FWieP;
use PHPMailer\PHPMailer\PHPMailer as PM;
/**
 * Static stuff class
 *
 * @category WebApplication
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
class App
{
    protected static $chars = "23456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";
    
    /**
     * Private constructor - this class should not be instantiated
     */
    private function __construct()
    {
    }

    /**
     * Returns a value from $_SESSION
     *
     * @param string $key the key to fetch
     *
     * @return mixed|NULL
     */
    public static function getSession(string $key)
    {
        if (is_string($key) && array_key_exists($key, $_SESSION)) {
            return $_SESSION [$key];
        }
        return null;
    }

    /**
     * Sets a value in the global $_SESSION
     *
     * @param string $key   the key
     * @param mixed  $value the value to set
     *
     * @return void
     */
    public static function setSession(string $key, $value) : void
    {
        if (is_string($key)) {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Gets the HTML page's href-base, derived from the current HTTP request
     * 
     * @return string
     */
    public static function getHrefBase() : string
    {
        $isSecure = ($_SERVER['SERVER_PORT'] == 443);
        $isSecure = $isSecure ||
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
        
        return sprintf(
            '%1$s://%2$s%3$s%4$s',
            $isSecure ? 'https' : 'http',
            $_SERVER['SERVER_NAME'],
            !in_array($_SERVER['SERVER_PORT'], array(80, 443))
                ? ':'.$_SERVER['SERVER_PORT'] : '',
            array_key_exists('REWRITEBASE', $_SERVER)
                ? $_SERVER['REWRITEBASE'] : '/'
        );
    }
    
    /**
     * Generates a string of given length containing 'safe' characters
     * 
     * @param int $length the length
     * 
     * @return string
     */
    public static function getRandomChars(int $length) : string
    {
        $o = '';
        $maxCharsIndex = strlen(self::$chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $o .= substr(self::$chars, mt_rand(0, $maxCharsIndex), 1);
        }
        return $o;
    }
    
    /**
     * Creates an RFC 4122 compliant Universally Unique IDentiefier (version 4)
     *
     * @return string
     */
    public static function createUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Formats a given amount of bytes to a fixed length, human readable form
     * 
     * @param int $bytes     the amount to format
     * @param int $precision the precision
     * 
     * @return string
     */
    public static function formatBytes(int $bytes, int $precision = 2) : string
    {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision).' '.$units[$pow];
    } 
    
    /**
     * Validates given string to be valid MIME-type
     * 
     * @param string $s the string to validate
     * 
     * @return bool
     */
    public static function isValidMimeType(string $s) : bool
    {
        return preg_match('!^[a-z][a-z-]*[a-z]/[a-z][a-z.-]*[a-z]$!', $s) == 1;
    }

    /**
     * Prepares an instance of PHPMailer with all common settings
     * 
     * @param string $fromName the default name of set in the FROM mail header
     *
     * @return PM
     */
    public static function prepareMailer(string $fromName = SMTP_FROMNAME) : PM
    {
        $mailer = new PM();
        $mailer->isSMTP();
        $mailer->isHTML(false);
        $mailer->CharSet = PM::CHARSET_UTF8;

        if (DEBUGMODE) {
            $mailer->Host = 'localhost';
            $mailer->SMTPAuth = false;
            $mailer->Port = 25252;
            $mailer->Sender = 'local@fwiep.nl';
            $mailer->setFrom($mailer->Sender);
        } else {
            $mailer->Host = SMTP_HOSTNAME;
            $mailer->Port = SMTP_HOSTPORT;
            $mailer->SMTPAuth = true;
            $mailer->Username = SMTP_USERNAME;
            $mailer->Password = SMTP_PASSWORD;
            $mailer->Sender = SMTP_USERNAME;
            $mailer->setFrom(SMTP_USERNAME, $fromName);
            
            switch (SMTP_HOSTPORT) {
            case 465:
                $mailer->SMTPSecure = PM::ENCRYPTION_SMTPS;
                break;
            case 587:
                $mailer->SMTPSecure = PM::ENCRYPTION_STARTTLS;
                break;
            default:
                $mailer->SMTPSecure = '';
                break;
            }
        }
        return $mailer;
    }
}
