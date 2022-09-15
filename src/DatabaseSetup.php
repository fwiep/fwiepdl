<?php
/**
 * Database setup utility
 *
 * PHP version 8
 *
 * @category Setup
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
namespace FWieP;
use FWieP\RuntimeData as RD;

if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
/**
 * Database setup utility
 *
 * @category Setup
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
final class DatabaseSetup
{
    const SETUP_SQL_FILE = __DIR__.'/../setup.sql';
    const DATA_SQL_FILE = __DIR__.'/../data.sql';

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct()
    {
    }

    /**
     * Executes the SQL statements to set up the application
     * 
     * @return bool true on success, false on failure
     */
    public static function setup() : bool
    {
        if (!file_exists(self::SETUP_SQL_FILE)) {
            return false;
        }
        $pdo = RD::g()->pdo;
        $ok = ($pdo->exec(file_get_contents(self::SETUP_SQL_FILE)) !== false);

        if (file_exists(self::DATA_SQL_FILE)) {
            $ok = $ok &&
                ($pdo->exec(file_get_contents(self::DATA_SQL_FILE)) !== false);
        }
        return $ok;
    }
}
