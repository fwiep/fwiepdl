<?php
/**
 * Database backup utility
 *
 * PHP version 8
 *
 * @category Backup
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
namespace FWieP;
use FWieP\RuntimeData as RD;
use \ZipArchive as ZA;

if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
/**
 * Database backup utility
 *
 * @category Backup
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
final class DatabaseBackup
{
    private const CRLF = "\r\n";
    
    private const UNQOUTED_TYPES = [
        'tinyint', 'boolean', 'smallint', 'mediumint', 'int', 'integer', 'bigint'
    ];

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct()
    {
    }

    /**
     * Performs a backup of the database
     * 
     * @return string the export in SQL-format
     */
    private static function _getBackup() : string
    {
        $o = '';
        $o .= 'SET foreign_key_checks = 0;'.self::CRLF;
        
        $o .= self::_getTables();
        $o .= self::_getViews();
        $o .= self::_getTriggers();
        $o .= self::_getFunctions();
        $o .= self::_getProcedures();
        
        $o .= 'SET foreign_key_checks = 1;'.self::CRLF;
        return $o;
    }
    
    /**
     * Gets all defined tables and their data
     * 
     * @return string
     */
    private static function _getTables() : string
    {
        $o = '';
        $pdo = RD::g()->pdo;

        foreach ($pdo->query('SHOW TABLE STATUS') as $table) {
            if ($table['Comment'] == 'VIEW') {
                continue;
            }
            $createTbl = '';
            $sql = 'SHOW CREATE TABLE `'.$table['Name'].'`';
            
            foreach ($pdo->query($sql) as $k => $v) {
                if (array_key_exists('Create Table', $v)) {
                    $createTbl = $v['Create Table'];
                    break;
                } else {
                    continue;
                }
            }
            $sql = 'SHOW COLUMNS FROM `'.$table['Name'].'`;';
            $dataTypes = $pdo->query($sql)->fetchAll(\PDO::FETCH_NAMED);
            $colTypes = [];
            
            array_walk(
                $dataTypes,
                function (&$x, $ix) use (&$colTypes) {
                    $colTypes[$x['Field']] = $x;
                }
            );
            $o .= self::CRLF."-- Create table ".$table['Name'].self::CRLF.self::CRLF;
            $o .= "DROP TABLE IF EXISTS `".$table['Name']."`;".self::CRLF;
            $o .= $createTbl.";".self::CRLF.self::CRLF;
            unset($createTbl);
            
            $sql = "SELECT * FROM `".$table['Name']."`";
            $rows = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            
            $o .= "-- Dump data".self::CRLF.self::CRLF;
            
            $rowChunks = array_chunk($rows, 1000);
            unset($rows);
            
            foreach ($rowChunks as $rowChunk) {
                $o .= "INSERT INTO `".$table['Name']."`".self::CRLF."\t(";
                $row1 = reset($rowChunk);
                $sql_insert = '';
                foreach ($row1 as $k => $v) {
                    $sql_insert .= "`${k}`,";
                }
                $sql_insert = substr($sql_insert, 0, -1);
                $o .= $sql_insert;
                $o .= ")".self::CRLF."VALUES".self::CRLF;
            
                foreach ($rowChunk as $row) {
                    $sql_insert = "\t(";
                    foreach ($row as $k => $v) {
                        $thisColType = strtolower($colTypes[$k]['Type']);
                        $thisColType = explode('(', $thisColType)[0];
                        $allowsNull = ($colTypes[$k]['Null'] == 'YES');

                        if ((is_null($v) || '' == $v) && $allowsNull) {
                            $sql_insert .= 'NULL';
                        } else if (in_array($thisColType, self::UNQOUTED_TYPES)) {
                            $sql_insert .= $v;
                        } else {
                            $sql_insert .= $pdo->quote($v);
                        }
                        $sql_insert .= ",";
                    }
                    $sql_insert  = substr($sql_insert, 0, -1);
                    $sql_insert .= "),".self::CRLF;
                    $o .= $sql_insert;
                }
                $o = substr($o, 0, -3);
                $o .= ";".self::CRLF;
                unset($sql_insert);
            }
            unset($rowChunks);
        }
        return $o;
    }
    
    /**
     * Gets all defined views
     * 
     * @return string
     */
    private static function _getViews() : string
    {
        $o = '';
        $pdo = RD::g()->pdo;
            
        foreach ($pdo->query('SHOW TABLE STATUS') as $table) {
            if ($table['Comment'] !== 'VIEW') {
                   continue;
            }
            $createView = '';
            $sql = 'SHOW CREATE VIEW `'.$table['Name'].'`';
                
            foreach ($pdo->query($sql) as $k => $v) {
                if (array_key_exists('Create View', $v)) {
                    $createView = $v['Create View'];
                    break;
                } else {
                    continue;
                }
            }
            $createView = preg_replace(
                '/CREATE.*?VIEW/', 'CREATE VIEW', $createView
            );
            $o .= self::CRLF."-- Create view ".$table['Name'].self::CRLF.self::CRLF;
            $o .= "DROP VIEW IF EXISTS `".$table['Name']."`;".self::CRLF;
            $o .= $createView.";".self::CRLF.self::CRLF;
            unset($createView);
        }
        return $o;
    }
    
    /**
     * Gets all defined triggers
     *
     * @return string
     */
    private static function _getTriggers() : string
    {
        $o = '';
        $pdo = RD::g()->pdo;
        $sql = 'SHOW TRIGGERS';
    
        foreach ($pdo->query($sql) as $proc) {
            $sql = 'SHOW CREATE TRIGGER '.$proc['Trigger'].'';
            foreach ($pdo->query($sql) as $createProc) {
                $create = $createProc['SQL Original Statement'];
                $create = preg_replace(
                    '/CREATE.*?TRIGGER/',
                    'CREATE TRIGGER',
                    $create
                );
                $create = trim($create, " \t\n\r\0\x0B;");
    
                $o .= self::CRLF."-- Create trigger ".$proc['Trigger'].
                    self::CRLF.self::CRLF;
                $o .= "DROP TRIGGER IF EXISTS `".$proc['Trigger']."`;".
                    self::CRLF;
                $o .= $create.";".self::CRLF;
            }
        }
        return $o;
    }
    
    /**
     * Gets all defined functions
     *
     * @return string
     */
    private static function _getFunctions() : string
    {
        $o = '';
        $pdo = RD::g()->pdo;
        $sql = 'SHOW FUNCTION STATUS WHERE Db = "'.DBNAME.'";';
    
        foreach ($pdo->query($sql) as $proc) {
            $sql = 'SHOW CREATE FUNCTION '.$proc['Name'].'';
            foreach ($pdo->query($sql) as $createProc) {
                $create = $createProc['Create Function'];
                $create = preg_replace(
                    '/CREATE.*?FUNCTION/',
                    'CREATE FUNCTION',
                    $create
                );
                $create = trim($create, " \t\n\r\0\x0B;");
    
                $o .= self::CRLF."-- Create function ".$proc['Name']
                    .self::CRLF.self::CRLF;
                $o .= "DROP FUNCTION IF EXISTS `".$proc['Name']."`;"
                    .self::CRLF;
                $o .= $create.";".self::CRLF;
            }
        }
        return $o;
    }
    
    /**
     * Gets all defined stored procedures
     *
     * @return string
     */
    private static function _getProcedures() : string
    {
        $o = '';
        $pdo = RD::g()->pdo;
        $sql = 'SHOW PROCEDURE STATUS WHERE Db = "'.DBNAME.'";';

        foreach ($pdo->query($sql) as $proc) {
            $sql = 'SHOW CREATE PROCEDURE '.$proc['Name'].'';
            foreach ($pdo->query($sql) as $createProc) {
                $create = $createProc['Create Procedure'];
                $create = preg_replace(
                    '/CREATE.*?PROCEDURE/',
                    'CREATE PROCEDURE',
                    $create
                );
                $create = trim($create, " \t\n\r\0\x0B;");
            
                $o .= self::CRLF."-- Create procedure ".$proc['Name']
                    .self::CRLF.self::CRLF;
                $o .= "DROP PROCEDURE IF EXISTS `".$proc['Name']."`;"
                    .self::CRLF;
                $o .= $create.";".self::CRLF;
            }
        }
        $o .= self::CRLF;
        return $o;
    }
    
    /**
     * Sends an e-mail containing a SQL-backup of the database as an attachment
     * 
     * @return bool true on success, false on failure
     */
    public static function sendBackup() : bool
    {
        $sql_backup = self::_getBackup();
        
        $mailer = App::prepareMailer();
        $mailer->addReplyTo('info@fwiep.nl', 'FWieP');
        $mailer->addAddress('info@fwiep.nl');
        $mailer->Subject = 'MySQL backup DL-FWieP ('.DBNAME.') '.date('Y-m-d');

        $body = "Database DL-FWieP ('".DBNAME."') is op ".date('d-m-Y').
        " succesvol geback-upt (zie bijlage).\r\n";

        $mailer->Body = nl2br($body);
        $mailer->AltBody = $body;

        $tmpName = tempnam(sys_get_temp_dir(), 'dbbackup-'.DBNAME.'-');
        $zip = new ZA();

        if ($zip->open($tmpName, (ZA::CREATE | ZA::OVERWRITE)) === true) {
            $zip->setPassword(DL_BACKUP_PASSWORD);
            $zip->addFromString(DBNAME."_".date('Ymd-His').".sql", $sql_backup);
            $zip->setEncryptionIndex(0, ZA::EM_AES_256);
            $zip->close();
        }
        $mailer->addAttachment(
            $tmpName,
            'dbbackup-'.DBNAME.'.zip',
            $mailer::ENCODING_BASE64,
            'application/zip'
        );
        return $mailer->send() && unlink($tmpName);
    }
}
