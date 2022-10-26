<?php
/**
 * Main UrlShortener class
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
use FWieP\RuntimeData as RD;
/**
 * Main UrlShortener class
 *
 * @category WebApplication
 * @package  FWiePdl
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
class UrlShortener
{
    protected static $table = "url";
    protected static $shortLength = 6;
    
    /**
     * Gets properties to be exposed publically
     * 
     * @return array
     */
    public function getProps() : array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'short' => $this->shortUrl,
            'long' => $this->longUrl,
            'local' => $this->isLocal,
            'downloads' => $this->downloadCount,
            'validFrom' => ($this->validFrom
                ? $this->validFrom->format('Y-m-d') : null),
            'validUntil' => ($this->validUntil
                ? $this->validUntil->format('Y-m-d') : null),
            'fullFilename' => $this->fileName.$this->fileExtension
        ];
    }
    
    protected $id = 0;

    /**
     * Gets the ID
     * 
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
    
    protected $uuid = null;
    /**
     * Gets the UUID
     *
     * @return string
     */
    public function getUuid() : string
    {
        return $this->uuid;
    }
    
    protected $shortUrl = null;
    
    /**
     * Gets the short URL (without prefix)
     * 
     * @return string
     */
    public function getShortUrl() : string
    {
        return $this->shortUrl;
    }
    
    protected $longUrl = null;
    
    /**
     * Gets the long (full) URL
     *
     * @return string|NULL
     */
    public function getLongUrl() : ?string
    {
        return $this->longUrl;
    }
    
    /**
     * Sets the long (full) URL
     * 
     * @param string $url the url to set
     * 
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setLongUrl(string $url) : void
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $this->longUrl = $url;
            $this->isLocal = false;
        } else {
            throw new \InvalidArgumentException("Long URL was not valid.");
        }
    }
    
    protected $validFrom = null;
    
    /**
     * Gets the datetime this URL is valid FROM
     * 
     * @return \DateTime|NULL
     */
    public function getValidFrom() : ?\DateTime
    {
        return $this->validFrom;
    }
    
    /**
     * Sets the datetime this URL is valid FROM
     * 
     * @param \DateTime $dt the datetime to set
     * 
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setValidFrom(?\DateTime $dt) : void
    {
        if (!is_null($dt)
            && !is_null($this->validUntil)
            && $dt >= $this->validUntil
        ) {
            throw new \InvalidArgumentException(
                "ValidFROM cannot be greater than or equal to ValidUNTIL."
            );
        }
        $this->validFrom = $dt;
    }
    
    protected $validUntil = null;
    
    /**
     * Gets the datetime this URL is valid UNTIL
     *
     * @return \DateTime|NULL
     */
    public function getValidUntil() : ?\DateTime
    {
        return $this->validUntil;
    }
    
    /**
     * Sets the datetime this URL is valid UNTIL
     *
     * @param \DateTime $dt the datetime to set
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setValidUntil(?\DateTime $dt) : void
    {
        if (!is_null($dt)
            && !is_null($this->validFrom)
            && $dt <= $this->validFrom
        ) {
            throw new \InvalidArgumentException(
                "ValidUNTIL cannot be less than or equal to ValidFROM."
            );
        }
        $this->validUntil = $dt;
    }
    
    protected $mimeType = null;
    
    /**
     * Gets the URL's file MIME type
     * 
     * @return string
     */
    public function getMimeType() : string
    {
        return $this->mimeType;
    }
    
    /**
     * Sets the MIME type
     * 
     * @param string $s the MIME type to set
     * 
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setMimeType(string $s) : void
    {
        if (App::isValidMimeType($s)) {
            $this->mimeType = $s;
        } else {
            throw new \InvalidArgumentException("MimeType was not valid.");
        }
    }
    
    protected $mimeEncoding = null;
    
    /**
     * Gets the URL's file MIME encoding
     *
     * @return string|NULL
     */
    public function getMimeEncoding() : ?string
    {
        return $this->mimeEncoding;
    }
    
    /**
     * Sets the MIME encoding
     *
     * @param string $s the MIME encoding to set
     *
     * @return void
     */
    public function setMimeEncoding(string $s) : void
    {
        $this->mimeEncoding = $s;
    }
    
    protected $fileName = null;
    
    /**
     * Gets the file name (excluding extension)
     *
     * @return string|NULL
     */
    public function getFileName() : ?string
    {
        return $this->fileName;
    }
    
    protected $fileExtension = null;
    
    /**
     * Gets the file extension (including leading dot)
     * 
     * @return string|NULL
     */
    public function getFileExtension() : ?string
    {
        return $this->fileExtension;
    }
    
    protected $fileSize = 0;
    
    /**
     * Gets the file size in bytes
     *
     * @return int
     */
    public function getFileSize() : int
    {
        return $this->fileSize;
    }
    
    protected $isLocal = false;
    
    /**
     * Gets whether the url points to a file that is locally stored 
     *
     * @return bool
     */
    public function getIsLocal() : bool
    {
        return $this->isLocal;
    }

    protected $downloadCount = 0;

    /**
     * Gets the URL's download count
     * 
     * @return int
     */
    public function getDownloadCount() : int
    {
        return $this->downloadCount;
    }
    
    protected $created = null;
    
    /**
     * Gets the datetime this url was created (in the database)
     * 
     * @return \DateTime|NULL
     */
    public function getCreated() : ?\DateTime
    {
        return $this->created;
    }
    
    /**
     * Creates a new URL instance
     */
    public function __construct()
    {
        $this->uuid = App::createUUID();
        $this->shortUrl = App::getRandomChars(self::$shortLength); 
    }

    /**
     * Increments the URL's download count in the database
     * 
     * @return bool
     */
    public function incrementDownloadCount() : bool
    {
        if ($this->id == 0) {
            return false;
        }
        $this->downloadCount++;
        
        $query = sprintf(
            "UPDATE `%1\$s` SET
                download_count = :dlcount
            WHERE
                url_id = :id AND url_uuid = :uuid;",
            self::$table
        );
        $stmt = RD::g()->pdo->prepare($query);
        
        $stmt->bindValue('id', $this->id);
        $stmt->bindValue('uuid', $this->uuid);
        $stmt->bindValue('dlcount', $this->downloadCount);
        
        return $stmt->execute();
    }
    
    /**
     * Saves the url to the database
     * 
     * @return bool
     */
    public function save() : bool
    {
        if ($this->id > 0) {
            $query = sprintf(
                "UPDATE `%1\$s` SET
                    long_url = :long,
                    valid_from = :validfrom, valid_until = :validuntil
                WHERE
                    url_id = :id AND url_uuid = :uuid;",
                self::$table
            );
            
            $stmt = RD::g()->pdo->prepare($query);
            
            $stmt->bindValue('id', $this->id);
            $stmt->bindValue('uuid', $this->uuid);
            $stmt->bindValue('long', $this->longUrl);
            
            if ($this->validFrom) {
                $stmt->bindValue(
                    'validfrom', $this->validFrom->format('Y-m-d H:i:s')
                );
            } else {
                $stmt->bindValue('validfrom', null, \PDO::PARAM_NULL);
            }
            if ($this->validUntil) {
                $stmt->bindValue(
                    'validuntil', $this->validUntil->format('Y-m-d H:i:s')
                );
            } else {
                $stmt->bindValue('validuntil', null, \PDO::PARAM_NULL);
            }
            return $stmt->execute();
        }
        
        $query = sprintf(
            "INSERT INTO `%1\$s` (
                url_uuid, short_url, long_url, valid_from, valid_until,
                mime_type, mime_encoding, file_name, file_extension,
                file_size, is_local
            ) VALUES (
                :uuid, :short, :long, :validfrom, :validuntil,
                :mimetype, :mimeencoding, :filename, :fileextension,
                :filesize, :islocal
            );",
            self::$table
        );
        $stmt = RD::g()->pdo->prepare($query);
        
        $stmt->bindValue('uuid', $this->uuid);
        $stmt->bindValue('short', $this->shortUrl);
        $stmt->bindValue('long', $this->longUrl);
        
        if ($this->validFrom) {
            $stmt->bindValue('validfrom', $this->validFrom->format('Y-m-d H:i:s'));
        } else {
            $stmt->bindValue('validfrom', null, \PDO::PARAM_NULL);
        }
        if ($this->validUntil) {
            $stmt->bindValue('validuntil', $this->validUntil->format('Y-m-d H:i:s'));
        } else {
            $stmt->bindValue('validuntil', null, \PDO::PARAM_NULL);
        }
        
        $stmt->bindValue('mimetype', $this->mimeType);
        $stmt->bindValue('mimeencoding', $this->mimeEncoding);
        $stmt->bindValue('filename', $this->fileName);
        $stmt->bindValue('fileextension', $this->fileExtension);
        $stmt->bindValue('filesize', $this->fileSize);
        $stmt->bindValue('islocal', $this->isLocal, \PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }
    
    /**
     * Removes this item from the database
     * 
     * @return bool
     */
    public function delete() : bool
    {
        $query = sprintf(
            "DELETE FROM `%1\$s` WHERE `url_id` = :id AND `url_uuid` = :uuid;",
            self::$table
        );
        $stmt = RD::g()->pdo->prepare($query);
        $stmt->bindValue('id', $this->id);
        $stmt->bindValue('uuid', $this->uuid);
        return $stmt->execute();
    }
    
    /**
     * Examines a local file and fills the URLs properties
     * 
     * @param string $filename name of the file to process
     * 
     * @return void
     * @throws \InvalidArgumentException
     */
    public function examineLocalFile(string $filename) : void
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(
                "Given file does not exist, or is not readable"
            );
        }
        $fi = finfo_open(FILEINFO_MIME_TYPE | FILEINFO_MIME_ENCODING);
        $this->mimeType = finfo_file($fi, $filename, FILEINFO_MIME_TYPE);
        $this->mimeEncoding = finfo_file($fi, $filename, FILEINFO_MIME_ENCODING);
        $this->fileName = pathinfo($filename, PATHINFO_FILENAME);
        $this->fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
        $this->fileSize = filesize($filename);
        $this->isLocal = true;
    }
    
    /**
     * Processes a file's name  and fills the URLs properties
     *
     * @param string $filename name of the file to process
     *
     * @return void
     */
    public function setFileNameAndExtension(string $filename) : void
    {
        $this->fileName = pathinfo($filename, PATHINFO_FILENAME);
        $this->fileExtension = '.'.pathinfo($filename, PATHINFO_EXTENSION);

        // Override detected mimetype for (among others) zipped Android ROMs
        if ('.zip' == $this->fileExtension
            && 'application/java-archive' == $this->mimeType
        ) {
            $this->mimeType = 'application/zip';
        }
    }
    
    /**
     * Serves a locally stored file as an HTTP attachtment (download)
     * 
     * @return bool FALSE on failure
     */
    public function serveLocalFile() : bool
    {
        if (!$this->isLocal) {
            return false;
        }
        $ctcs = sprintf(
            '%s; charset=%s', $this->mimeType, $this->mimeEncoding
        );
        $fn = sprintf('%s%s', $this->fileName, $this->fileExtension);
        $fnOnDisk = LOCAL_STORAGE_FOLDER.'/'.$this->uuid;
        
        if (!file_exists($fnOnDisk)) {
            return false;
        }
        header('Content-Type: '.$ctcs, true);
        header('Content-Length: '.$this->fileSize, true);
        header('Content-Disposition: attachment; filename="'.$fn.'"', true);
        header('X-Robots-Tag: noindex', false);
        readfile($fnOnDisk);
        exit;
    }
    
    /**
     * Gets all url objects from storage 
     * 
     * @param string $uuid     the UUID to filter
     * @param string $shortUrl the short URL to filter
     * 
     * @return \FWieP\UrlShortener[]
     */
    public static function getUrls(string $uuid = null, string $shortUrl = null)
    {
        $query = sprintf(
            "SELECT * FROM `%1\$s` WHERE
                (:uuid IS NULL OR `url_uuid` = :uuid )
                AND
                (:short IS NULL OR `short_url` = :short )
            ORDER BY
                `file_name` ASC;",
            self::$table
        );
        $stmt = RD::g()->pdo->prepare($query);
        $stmt->bindValue('uuid', $uuid);
        $stmt->bindValue('short', $shortUrl);
        $stmt->execute();
        $o = [];
        
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $url = new self();
            $url->id = intval($r['url_id']);
            $url->uuid = $r['url_uuid'];
            $url->shortUrl = $r['short_url'];
            $url->longUrl = $r['long_url'];
            $url->validFrom = (empty($r['valid_from'])
                ? null : new \DateTime($r['valid_from']));
            $url->validUntil = (empty($r['valid_until'])
                ? null : new \DateTime($r['valid_until']));
            $url->mimeType = $r['mime_type'];
            $url->mimeEncoding = $r['mime_encoding'];
            $url->fileName = $r['file_name'];
            $url->fileExtension = $r['file_extension'];
            $url->fileSize = intval($r['file_size']);
            $url->isLocal = (bool)$r['is_local'];
            $url->downloadCount = intval($r['download_count']);
            $url->created = new \DateTime($r['created']);
            
            $o[] = $url;
        }
        return $o;
    }
}
