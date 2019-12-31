<?php
/**
 * Helper class to manage runtime data
 *
 * PHP version 8
 *
 * @category RuntimeData
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
namespace FWieP;

if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
/**
 * Helper class to manage runtime data
 *
 * @category RuntimeData
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
final class RuntimeData
{
    private static $_instance;

    /**
     * The global database instance
     * 
     * @var \PDO
     */
    public $pdo;

    /**
     * The global XML document
     *
     * @var \DOMDocument
     */
    public $xml;

    /**
     * The requested page
     *
     * @var string
     */
    public $qsPage;

    /**
     * Whether the current request is performed by a logged-in user
     * 
     * @return bool
     */
    public function isUserLoggedIn() : bool
    {
        return !is_null(App::getSession('_DL_LOGIN'));
    }

    /**
     * Logs in a user
     * 
     * @param string $username the username to remember
     * 
     * @return void
     */
    public function logInUser(string $username) : void
    {
        App::setSession('_DL_LOGIN', $username);
    }

    /**
     * Gets the current runtime data
     *
     * @return RuntimeData
     */
    public static function g() : self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Class constructor, private to prevent instantiation
     */
    private function __construct()
    {
        try {
            $this->pdo = new \PDO(
                "mysql:host=".DBHOSTNAME.";dbname=". DBNAME,
                DBUSERNAME, DBPASSWORD
            );
        }
        catch (\Exception $ex) {
            $this->pdo = null;
        }
        $this->xml = new \DOMDocument('1.0', 'UTF-8');
        $this->qsPage = filter_input(INPUT_GET, 'p', FILTER_DEFAULT)
            ?? 'home';

        if (defined('_FWIEPADMIN') && _FWIEPADMIN) {

            if (!in_array($this->qsPage, ['backup', 'setup'])
                && !file_exists(__DIR__.'/../admin/'.$this->qsPage.'.php')
            ) {
                $this->qsPage = '301-404-410';
            }
        } else {

            if (!file_exists(__DIR__.'/../pages/'.$this->qsPage.'.php')
                && !file_exists(__DIR__.'/../pages/content/'.$this->qsPage.'.php')
            ) {
                $this->qsPage = '301-404-410';
            }
        }
    }
}
