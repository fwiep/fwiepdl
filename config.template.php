<?php
/**
 * Configuration and database-connection
 *
 * PHP version 8
 *
 * @category Configuration
 * @package  DL-FWieP
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}

// Boolean, whether to use a fake local, or a real external SMTP mailserver
define('DEBUGMODE', false);

// String, ZIP-password for encryption of the e-mailed database backup
define('DL_BACKUP_PASSWORD', 'my-super-secret-backup-password');

// String, path to data storage folder
define('LOCAL_STORAGE_FOLDER', __DIR__.'/data');

// Integer, default download delay in seconds
define('DOWNLOAD_TIMEOUT', 5);

// String, database connection hostname
define('DBHOSTNAME', 'localhost');

// String, database connection username
define('DBUSERNAME', 'root');

// String, database connection password
define('DBPASSWORD', 'my-super-secret-database-password');

// String, database connection database name
define('DBNAME', 'dlfwiep');

// String, SMTP email server hostname
define('SMTP_HOSTNAME', 'smtp.email.server');

// Integer, SMTP email server port number
define('SMTP_HOSTPORT', 587);

// String, SMTP email server username
define('SMTP_USERNAME', 'my-smtp-username@email.server');

// String, SMTP email server password
define('SMTP_PASSWORD', 'my-super-secret-email-password');

// String, SMTP email FROM sender name
define('SMTP_FROMNAME', 'FWieP mailer');

// Array, user accounts with plaintext passwords
define(
    'DL_USERS', ['admin' => 'my-super-secret-admin-password']
);
