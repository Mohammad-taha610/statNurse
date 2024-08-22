<?php

namespace sa\files;

use Doctrine\ORM\Query;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modDataRequest;
use sacore\application\modelResult;
use sacore\application\modRequest;
use sacore\utilities\fieldValidation;

/**
 * @Entity(repositoryClass="saFileRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="sa_file", indexes={
 *      @Index(name="IDX_sa_file_folder", columns={"folder"}),
 *      @Index(name="IDX_sa_file_filename", columns={"filename"}),
 *      @Index(name="IDX_sa_file_filename_key", columns={"filename_key"}),
 * })
 */
class saFile
{

	/** @Id @Column(type="integer") @GeneratedValue */
	protected $id;
	/** @Column(type="string", nullable=true) */
	protected $label;
	/** @Column(type="string", nullable=true) */
	protected $description;
	/** @Column(type="string", nullable=true) */
	protected $filename;
	/** @Column(type="string", nullable=true) */
	protected $disk_file_name;
	/** @Column(type="string", nullable=true) */
	protected $folder;
	/** @Column(type="datetime", nullable=true) */
	protected $date_created;
	/** @Column(type="string", nullable=true, length=5000) */
	protected $meta_info;
	/** @Column(type="string", nullable=true, length=5000) */
	protected $chunked_file_code;
	/** @Column(type="boolean", nullable=true) */
	protected $complete_file;
	/** @Column(type="integer", nullable=true) */
	protected $file_size;
	/** @Column(type="integer", nullable=true) */
	protected $first_byte_index;
	/** @Column(type="integer", nullable=true) */
	protected $last_byte_index;
	/** @Column(type="integer", nullable=true) */
	protected $file_chunk_size;
	/** @Column(type="string", nullable=true, length=7)  */
	protected $filename_key;
	
	public static $_allowedFileExtensions = array('png', 'gif', 'jpg', 'jpeg', 'xlsx', 'xlsb', 'xls', 'docx', 'doc', 'ppt', 'pptx', 'pdf', 'csv', 'tiff', 'tif', 'avi', 'mpeg', 'mpg', 'mp3', 'mp4', 'mov','wmv', 'stl');

	protected static function generateDiskFilename($base_name, $directory = null, $prepend = '')
	{
	    if(is_null($directory)) $directory = app::get()->getConfiguration()->get('uploadsDir');
		$upload_filename = $prepend . time() . rand(0, 999) . $base_name;
		while (file_exists($directory . '/' . $upload_filename)) {
			$upload_filename = $prepend . time() . rand(0, 999) . $base_name;
		}

		return $upload_filename;
	}

	/**
	 * Upload a file to system
	 *
	 * @param bool   $upload_file
	 * @param string $folder
	 * @param string $entity_name
	 * @return saFile
	 * @throws FileUploadException
	 * @throws \sacore\application\IocException
	 * @internal param \sa\uploads\uploaded $array file
	 */
	protected static function uploadCompleteFile($upload_file, $folder = 'generic', $entity_name = 'saFile')
	{
        /** @var saFile $file */
        $file = null;
	    
	    if(app::get()->getConfiguration()->get('allow_filename_overrides')->getValue()) {
            $file = ioc::getRepository($entity_name)->findOneBy(array('filename' => $upload_file['name'], 'folder' => $folder, 'complete_file' => true));

            if($file) {
                $upload_filename = $file->getDiskFileName();
            }
        }
        
        if(empty($upload_filename)) {
            $upload_filename = self::generateDiskFilename($upload_file['name']);
        }

		$moved = move_uploaded_file($upload_file['tmp_name'], app::get()->getConfiguration()->get('uploadsDir') . '/' . $upload_filename);

		if ($_SERVER['SERVER_NAME'] != 'unit_testing' && $moved) {
			$member = modRequest::request('auth.member', 0);
            
            if(!$file) {
                /** @var saFile $file */
                $file = ioc::resolve($entity_name);
            }
            
			$file->setFilename($upload_file['name']);
			$file->setDiskFileName($upload_filename);
			$file->setDateCreated(new \sacore\application\DateTime());
			$file->setFolder($folder);
			$file->setCompleteFile(true);
			$file->setFileSize(filesize(app::get()->getConfiguration()->get('uploadsDir') . '/' . $upload_filename));

			app::$entityManager->persist($file);
			app::$entityManager->flush();

			return $file;
		}
		//FOR TESTING ONLY | move_uploaded_file has a check that is impossible to get around on the server side, forking here to still have a fairly accurate test
		else if($_SERVER['SERVER_NAME'] == 'unit_testing' && rename($upload_file['tmp_name'], app::get()->getConfiguration()->get('uploadsDir') . '/' . $upload_filename)){
            $member = modRequest::request('auth.member', 0);

            if(!$file) {
                /** @var saFile $file */
                $file = ioc::resolve($entity_name);
            }

            $file->setFilename($upload_file['name']);
            $file->setDiskFileName($upload_filename);
            $file->setDateCreated(new \sacore\application\DateTime());
            $file->setFolder($folder);
            $file->setCompleteFile(true);
            $file->setFileSize(filesize(app::get()->getConfiguration()->get('uploadsDir') . '/' . $upload_filename));

            app::$entityManager->persist($file);
            app::$entityManager->flush();

            return $file;
        } else {
		    throw new FileUploadException('The file was not uploaded.');
		}
	}

	/**
	 * Upload a complete file to system
	 *
	 * @param bool $upload_file
	 * @param string $folder
	 * @param string $entity_name
	 * @return saFile file object or false if failed to upload
	 * @throws FileUploadException
	 * @throws \sacore\application\IocException
	 * @internal param \sa\uploads\uploaded $array file
	 */
	protected static function uploadChunkedFile($upload_file, $folder='generic', $entity_name = 'saFile', $chunkFileCode = null)
	{
		/** @var saFile $file */
		$file = ioc::resolve($entity_name);

		//bytes 0-1499999/18092384
		preg_match('/bytes ([0-9]{1,})-([0-9]{1,})\/([0-9]{1,})/', $_SERVER['HTTP_CONTENT_RANGE'], $filerangeinfo);
		
		$upload_filename = self::generateDiskFilename($upload_file['name'], app::get()->getConfiguration()->get('uploadsDir'), 'tmp_');

		$file->setChunkedFileCode($chunkFileCode);

		$file->setFilename($upload_file['name']);
		$file->setDiskFileName($upload_filename);
		$file->setDateCreated(new DateTime());
		$file->setFolder($folder);
		$file->setFileSize($filerangeinfo[3]);
		$file->setFirstByteIndex($filerangeinfo[1]);
		$file->setLastByteIndex($filerangeinfo[2]);
		$file->setFileChunkSize($filerangeinfo[2] - $filerangeinfo[1] + 1); // Byte ranges are 0 indexed
		$file->setCompleteFile(false);

		if(move_uploaded_file( $upload_file['tmp_name'] , app::get()->getConfiguration()->get('uploadsDir') . DIRECTORY_SEPARATOR . $file->getDiskFileName())) {
			app::$entityManager->persist($file);
			app::$entityManager->flush();
		}

		$files = app::$entityManager->getRepository(ioc::staticResolve($entity_name))->findBy(array('chunked_file_code' => $file->getChunkedFileCode(), 'complete_file' => 0, 'filename' => $file->getFilename()), array('first_byte_index' => 'ASC'));
		$chunkTotalSize = 0;

		/** @var saFile $fileChunk */
		foreach($files as $fileChunk) {
			$chunkTotalSize += $fileChunk->getFileChunkSize();
		}

		if($chunkTotalSize > $file->getFileSize()) {
			/** @var saFile $failedChunk */
			foreach($files as $failedChunk) {
				app::$entityManager->remove($failedChunk);
			}

			app::$entityManager->flush();

			throw new FileUploadException('Total Chunk size does not match expected file size.');
		} else if($chunkTotalSize == $file->getFileSize()) {
            /** @var saFile $completeFile */
            $completeFile = null;
		    
		    if(app::get()->getConfiguration()->get('allow_filename_overrides')->getValue()) {
                $completeFile = ioc::getRepository($entity_name)->findOneBy(array('filename' => $upload_file['name'], 'folder' => $folder, 'complete_file' => true));
                
                if($completeFile) {
                    $upload_filename = $completeFile->getDiskFileName();
                }
            } 
            
            if(!$completeFile) {
                /** @var saFile $completeFile */
                $completeFile = ioc::resolve($entity_name);
                $upload_filename = self::generateDiskFilename($upload_file['name'], app::get()->getConfiguration()->get('uploadsDir'));
            }
            
			$completeFile->setFilename($file->getFilename());
			$completeFile->setDiskFileName($upload_filename);
			$completeFile->setDateCreated(new DateTime());
			$completeFile->setFolder($file->getFolder());
			$completeFile->setFileSize($file->getFileSize());
			$completeFile->setCompleteFile(true);

			$fh = fopen(app::get()->getConfiguration()->get('uploadsDir'). '/' .$completeFile->getDiskFileName(), 'w');

			if (!$fh) {
				throw new FileUploadException('Can\'t open the file for writing.');
			}

			/** @var saFile $fileChunk */
			foreach($files as $fileChunk) {
				fwrite($fh, $fileChunk->get());
				app::$entityManager->remove($fileChunk);
			}

			fclose($fh);

			app::$entityManager->persist($completeFile);
			app::$entityManager->flush();

			return $completeFile;
		} else {
			return $file;
		}
	}

	/**
	 * Upload a complete file or a chunked file to system
	 *
	 * @param bool   $upload_file
	 * @param string $folder
	 * @return saUploads file object or false if failed to upload
	 * @throws FileUploadException
	 * @internal param \sa\uploads\uploaded $array file
	 */
	public static function upload($upload_file, $folder = 'generic')
	{
        $upload_file['name'] = static::stripFilenameCharacters($upload_file['name']);

	    if(!app::get()->getConfiguration()->get('allow_filename_overrides')->getValue()) {
            $upload_file['name'] = self::validateFilename($upload_file['name'],$folder);
        }
	    
		if (empty($upload_file)) {
			throw new FileUploadException('The uploaded file was missing.');
		}

		if (!self::isAllowedType($upload_file['name'])) {
			throw new FileUploadException('The type of file uploaded is not allowed. Please upload one of the following types of files ' . implode(',', self::$_allowedFileExtensions) . '.');
		}

		$file = null;

		if (empty($_SERVER['HTTP_CONTENT_RANGE'])) {
			$file  = self::uploadCompleteFile($upload_file, $folder);
		} else {
			$chunkFileCode = self::getFileUuid($upload_file, $folder);
			$file = self::uploadChunkedFile($upload_file, $folder, 'saFile', $chunkFileCode);
		}

		return $file;
	}

	protected static function getFileUuid($upload_file, $folder) 
	{
		if(empty($_SESSION['chunked_file_code']) && !isset($_REQUEST['file_uuid'])) {
			$chunkFileCode = md5(time() . $upload_file['tmp_name'] . $folder);
			$_SESSION['chunked_file_code'] =  $chunkFileCode;
		} else if(empty($_SESSION['chunked_file_code']) && !empty($_REQUEST['file_uuid'])) {
			$chunkFileCode = $_REQUEST['file_uuid'];
		} else {
			$chunkFileCode = $_SESSION['chunked_file_code'];
		}

		return $chunkFileCode;
	}

	public static function saveStringToFile($fileName, $content, $folder = 'generic', $entity_name = 'saFile')
	{
        /** @var saFile $file */
        $file = null;
	    
        if(app::get()->getConfiguration()->get('allow_filename_overrides')->getValue()) {
            $file = ioc::getRepository($entity_name)->findOneBy(array('filename' => $fileName, 'folder' => $folder));

            if($file) {
                $upload_filename = $file->getDiskFileName();
            }
        }

        if(empty($upload_filename)) {
            $upload_filename = self::generateDiskFilename($fileName);
        }
        
		$fileName = static::stripFilenameCharacters($fileName);

        if(!app::get()->getConfiguration()->get('allow_filename_overrides')->getValue()) {
            $fileName = self::validateFilename($fileName, $folder);
        }

        if(!$file) {
            /** @var saFile $file */
            $file = ioc::resolve($entity_name);
        }
		
		if (file_put_contents(app::get()->getConfiguration()->get('uploadsDir') . '/' . $upload_filename, $content)) {
			$memberId = modDataRequest::request('auth.member_id', 0);
            
			$file->setFilename($fileName);
			$file->setDiskFileName($upload_filename);
			$file->setDateCreated(new \sacore\application\DateTime());
			$file->setFolder($folder);
			$file->setFileSize(filesize(app::get()->getConfiguration()->get('uploadsDir') . '/' . $upload_filename));

			app::$entityManager->persist($file);
			app::$entityManager->flush();

			return $file;
		} else {
			return false;
		}
	}

    public static function isAllowedType($file)
	{
		$pathInfo = pathinfo($file);
        $additional = array();
        if(app::get()->getConfiguration()->get('allowed_additional_files_types')) {
            $additional = explode(',', app::get()->getConfiguration()->get('allowed_additional_files_types'));
        }

		if (in_array(strtolower($pathInfo['extension']), static::$_allowedFileExtensions) || in_array(strtolower($pathInfo['extension']), $additional)) {
			return true;
		}

		return false;
	}

	public function toArray()
	{

		$vars = get_object_vars($this);

		$meta = json_decode($this->meta_info, true);
		if (!is_array($meta)) {
			$meta = array();
		}

		$vars = array_merge($vars, $meta);

		return $vars;
	}

	public function __set($name, $value)
	{
		$meta = json_decode($this->meta_info, true);
		if (!is_array($meta)) {
			$meta = array();
		}
		$meta[$name] = $value;
		$this->meta_info = json_encode($meta);
	}

	public function __get($name)
	{
		$meta = json_decode($this->meta_info, true);
		if (!is_array($meta)) {
			$meta = array();
		}

		return isset($meta[$name]) ? $meta[$name] : '';
	}

	/**
	 * @preRemove
	 */
	public function deleteDiskFile()
	{
		@unlink($this->getPath());
	}


	public function getPath()
	{
		return app::get()->getConfiguration()->get('uploadsDir') . '/' . $this->disk_file_name;
	}


	/**
	 * Get the file contents.
	 *
	 * @return string
	 */
	public function get()
	{
		return file_get_contents($this->getPath());
	}

	/**
	 * Get the size of the file.
	 *
	 * @return string
	 */
	public function getSize()
	{
		return filesize($this->getPath());
	}


	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set label
	 *
	 * @param string $label
	 * @return saFile
	 */
	public function setLabel($label)
	{
		$this->label = $label;

		return $this;
	}

	/**
	 * Get label
	 *
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 * @return saFile
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set filename
	 *
	 * @param string $filename
	 * @return saFile
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
        $this->filename_key = substr($filename, 0, 7);
		
		return $this;
	}

	/**
	 * Get filename
	 *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Get file type
	 *
	 * @return string
	 */
	public function getFileType()
	{
		return strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
	}

	/**
	 * Set disk_file_name
	 *
	 * @param string $diskFileName
	 * @return saFile
	 */
	public function setDiskFileName($diskFileName)
	{
		$this->disk_file_name = $diskFileName;

		return $this;
	}

	/**
	 * Get disk_file_name
	 *
	 * @return string
	 */
	public function getDiskFileName()
	{
		return $this->disk_file_name;
	}

	/**
	 * Set folder
	 *
	 * @param string $folder
	 * @return saFile
	 */
	public function setFolder($folder)
	{
		$this->folder = $folder;

		return $this;
	}

	/**
	 * Get folder
	 *
	 * @return string
	 */
	public function getFolder()
	{
		return $this->folder;
	}

	/**
	 * Set date_created
	 *
	 * @param \sacore\application\DateTime $dateCreated
	 * @return saFile
	 */
	public function setDateCreated($dateCreated)
	{
		$this->date_created = $dateCreated;

		return $this;
	}

	/**
	 * Get date_created
	 *
	 * @return \sacore\application\DateTime
	 */
	public function getDateCreated()
	{
		return $this->date_created;
	}

	/**
	 * Set meta_info
	 *
	 * @param string $metaInfo
	 * @return saFile
	 */
	public function setMetaInfo($metaInfo)
	{
		$this->meta_info = $metaInfo;

		return $this;
	}

	/**
	 * Get meta_info
	 *
	 * @return string
	 */
	public function getMetaInfo()
	{
		return $this->meta_info;
	}

	/**
	 * @return mixed
	 */
	public function getCompleteFile()
	{
		return $this->complete_file;
	}

	/**
	 * @param mixed $complete_file
	 */
	public function setCompleteFile($complete_file)
	{
		$this->complete_file = $complete_file;
	}

	/**
	 * Alias of getCompleteFile
	 *
	 * Should help with confusion
	 *
	 * @return mixed
	 */
	public function getIsCompletedFile()
	{
		return $this->complete_file;
	}

	/**
	 * Alias of setCompleteFile
	 *
	 * Should help with confusion
	 *
	 * @param mixed $complete_file
	 */
	public function setIsCompletedFile($complete_file)
	{
		$this->complete_file = $complete_file;
	}

	/**
	 * @return mixed
	 */
	public function getChunkedFileCode()
	{
		return $this->chunked_file_code;
	}

	/**
	 * @param mixed $chunked_file_code
	 */
	public function setChunkedFileCode($chunked_file_code)
	{
		$this->chunked_file_code = $chunked_file_code;
	}

	/**
	 * @return mixed
	 */
	public function getFileSize()
	{
		return $this->file_size;
	}

	/**
	 * @param mixed $file_size
	 */
	public function setFileSize($file_size)
	{
		$this->file_size = $file_size;
	}

	/**
	 * @return mixed
	 */
	public function getLastByteIndex()
	{
		return $this->last_byte_index;
	}

	/**
	 * @param mixed $last_byte_index
	 */
	public function setLastByteIndex($last_byte_index)
	{
		$this->last_byte_index = $last_byte_index;
	}

	public function getFileIcon()
	{

		$type = '';
		switch ($this->getFileType()) {

			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'tiff':
				$type = 'fa-picture-o';
				break;
			case 'pdf':
				$type = 'fa-file-pdf-o';
				break;
			case 'xls':
			case 'xlsx':
			case 'xlsxb':
				$type = 'fa-file-excel-o';
				break;
			case 'txt':
				$type = 'fa-file-text-o';
				break;
			case 'doc':
			case 'docx':
				$type = 'fa-file-word-o';
				break;
			case 'ppt':
			case 'pptx':
				$type = 'fa-file-powerpoint-o';
				break;
			case 'm4p':
			case 'm4v':
			case 'mp4':
			case 'mpg':
			case 'mp2':
			case 'mp3':
			case 'mpeg':
			case 'mpe':
			case 'mpv':
			case '3gp':
			case 'flv':
			case 'vob':
			case 'ogg':
			case 'ogv':
			case 'avi':
				$type = 'fa-file-video-o';
				break;
			default:
				$type = 'fa-file';

		}

		return $type;
	}

    /**
     * Prevents duplicate filenames from being uploaded to the system
     * 
     * @param $filename_in
     * @param null $folder
     * @return mixed|string
     */
    public static function validateFilename($filename_in, $folder = null)
	{
		$filename = preg_replace("/[^A-Za-z0-9-_.]/", '', $filename_in);
        $originalFileNameParts = pathinfo($filename_in);
        
        /** @var saFileRepository $filesRepo */
		$filesRepo = ioc::getRepository('saFile');
		
		$matchedFiles = $filesRepo->matchesFilesLike(
		    $originalFileNameParts['filename'],
            $originalFileNameParts['extension'],
            $folder,
            Query::HYDRATE_ARRAY
        );
		
		$names = array();
		
		/** @var saFile $file */
		foreach($matchedFiles as $file) {
		    $names[] = $file['filename'];
        }
        
        $i = 2;

		if(!in_array($filename, $names)) {
            return $filename;
        }
        
        while(in_array($filename, $names)) {
            $filename = $originalFileNameParts['filename'] . '_' . $i . '.' . $originalFileNameParts['extension'];
            $i++;
        }
        
		return $filename;
	}

	/**
	 * @param mixed $first_byte_index
	 */
	public function setFirstByteIndex($first_byte_index)
	{
		$this->first_byte_index = $first_byte_index;
	}

	/**
	 * @return mixed
	 */
	public function getFileChunkSize()
	{
		return $this->file_chunk_size;
	}

	/**
	 * @param mixed $file_chunk_size
	 */
	public function setFileChunkSize($file_chunk_size)
	{
		$this->file_chunk_size = $file_chunk_size;
	}

    /**
     * @return mixed
     */
    public function getFilenameKey()
    {
        return $this->filename_key;
    }

	/**
     * Removes any characters that may cause
     * issues loading uploaded file
     */
    protected static function stripFilenameCharacters($filename)
    {
        return preg_replace("/[^A-Za-z0-9-_.]/", '', $filename);
    }
}
