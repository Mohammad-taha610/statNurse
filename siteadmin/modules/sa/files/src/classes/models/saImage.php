<?php

namespace sa\files;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PreRemove;
use sacore\application\app;
use sacore\application\ioc;
use sa\sa3ApiClient\Sa3ApiClient;

/**
 * @Entity
 */
class saImage extends saFile {

	/** @OneToOne(targetEntity="saImage", cascade={"remove"})
	 * @JoinColumn(name="large_id", referencedColumnName="id")*/
	protected $large;
	/** @OneToOne(targetEntity="saImage", cascade={"remove"})
	 * @JoinColumn(name="medium_id", referencedColumnName="id")*/
	protected $medium;
	/** @OneToOne(targetEntity="saImage", cascade={"remove"})
	 * @JoinColumn(name="small_id", referencedColumnName="id")*/
	protected $small;
	/** @OneToOne(targetEntity="saImage", cascade={"remove"})
	 * @JoinColumn(name="xsmall_id", referencedColumnName="id")*/
	protected $xsmall;
	/** @OneToOne(targetEntity="saImage", cascade={"remove"})
	 * @JoinColumn(name="micro_id", referencedColumnName="id")*/
	protected $micro;
	/** @OneToOne(targetEntity="saImage", cascade={"remove"})
	 * @JoinColumn(name="original_id", referencedColumnName="id")*/
	protected $original;

	/** @Column(type="string", nullable=true) */
	protected $size_name;

	public static function upload($upload_file, $folder='generic', $discard_original = false)
	{
	    $upload_file['name'] = static::stripFilenameCharacters($upload_file['name']);

	    if(!app::get()->getConfiguration()->get('allow_filename_overrides')->getValue()) {
            $upload_file['name'] = self::validateFilename($upload_file['name'],$folder);
        }
	    
		if (empty($upload_file)) {
			throw new FileUploadException('The uploaded file was missing.');
		}

		if ( !self::isAllowedType( $upload_file['name'] )) {
			throw new FileUploadException('The type of file uploaded is not allowed. Please upload one of the following types of files '.implode(',', self::$_allowedFileExtensions).'.');
		}

		if (empty($_SERVER['HTTP_CONTENT_RANGE'])) {
		    /** @var saImage $file */
			$file = self::uploadCompleteFile($upload_file, $folder, 'saImage');

		} else {
			$chunkFileCode = self::getFileUuid($upload_file, $folder);
			/** @var saImage $file */
			$file = self::uploadChunkedFile($upload_file, $folder, 'saImage', $chunkFileCode);
		}

		if($file->getCompleteFile()) {
		    /** @var saImage $file */
			$file = $file->createMultipleSizes($discard_original);
		}

		return $file;
	}



	public function createMultipleSizes($discard_original, $createLarge=true, $createMedium=true, $createSmall=true, $createExtraSmall=true, $createMicro=true)
	{
		$image_sizes = array(
			'lg' => 1200,
			'md' => 600,
			'sm' => 300,
			'xs' => 150,
			'micro' => 30,
		);

		$files = array();
		//Todo: This didn't seem to do anything didn't see this in the config, and it doesn't seem to make sense in the context of the new configuration
//		if(!empty(app::get()->getConfiguration()->get('image_sizes')->getValue()) && is_array(app::get()->getConfiguration()->get('image_sizes')->getValue())) {
//            $image_sizes = app::get()->getConfiguration()->get('image_sizes')->getValue();
//        }
			

		/** @var saImage $saFile */
		$saFile = ioc::staticResolve('saImage');


		$apisizes = array();
		if ($createLarge) {
            $apisizes[] = array('name_prefix'=>'lg_', 'max_width'=>$image_sizes['lg'], 'max_height'=>$image_sizes['lg']);
		}

        if ($createMedium) {
            $apisizes[] = array('name_prefix'=>'md_', 'max_width'=>$image_sizes['md'], 'max_height'=>$image_sizes['md']);
        }

        if ($createSmall) {
            $apisizes[] = array('name_prefix'=>'sm_', 'max_width'=>$image_sizes['sm'], 'max_height'=>$image_sizes['sm']);
        }

        if ($createExtraSmall) {
            $apisizes[] = array('name_prefix'=>'xs_', 'max_width'=>$image_sizes['xs'], 'max_height'=>$image_sizes['xs']);
        }

        if ($createMicro) {
            $apisizes[] = array('name_prefix'=>'micro_', 'max_width'=>$image_sizes['micro'], 'max_height'=>$image_sizes['micro']);
        }


        // RESIZE VIA THE API
        $results = self::resizeViaAPI($this->getPath(), $apisizes);
		if ($results) {
            $name = pathinfo($this->getPath(), PATHINFO_FILENAME);
            $ext = pathinfo($this->getPath(), PATHINFO_EXTENSION);
            $zip_path = app::get()->getConfiguration()->get('uploadsDir')->getValue() . '/' . $name . time() . rand(0, 9999) . '.zip';
            file_put_contents($zip_path, base64_decode($results['response']['zip']));
            $zip = new \ZipArchive();
            if ($zip->open($zip_path)) {

                if ($createLarge) {
                    $image = $zip->getFromName('lg_'.$name . '.' . $ext);
                    $files['lg'] = $saFile::saveStringToFile('lg_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
                }

                if ($createMedium) {
                    $image = $zip->getFromName('md_'.$name . '.' . $ext);
                    $files['md'] = $saFile::saveStringToFile('md_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
                }

                if ($createSmall) {
                    $image = $zip->getFromName('sm_'.$name . '.' . $ext);
                    $files['sm'] = $saFile::saveStringToFile('sm_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
                }

                if ($createExtraSmall) {
                    $image = $zip->getFromName('xs_'.$name . '.' . $ext);
                    $files['xs'] = $saFile::saveStringToFile('xs_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
                }

                if ($createMicro) {
                    $image = $zip->getFromName('micro_'.$name . '.' . $ext);
                    $files['micro'] = $saFile::saveStringToFile('micro_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
                }

                $zip->close();
                unlink($zip_path);


                if(!$discard_original) $files['original'] = $this;

                /** @var saImage $file */
                foreach($files as $k => $file)
                {
                    $file->setSizeName($k);
                    $file->setCompleteFile(true);

                    if($k != 'lg')
                        $file->setLarge($files['lg']);

                    if($k != 'md')
                        $file->setMedium($files['md']);

                    if($k != 'sm')
                        $file->setSmall($files['sm']);

                    if($k != 'xs')
                        $file->setXsmall($files['xs']);

                    if($k != 'micro')
                        $file->setMicro($files['micro']);

                    if($k != 'original')
                        $file->setOriginal($files['original']);

                }

            }

        }
        // RESIZE LOCALLY
        else {

            if ($createLarge) {
                $image = self::resize($this->getPath(), $image_sizes['lg'], $image_sizes['lg']);
                $files['lg'] = $saFile::saveStringToFile('lg_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
            }

            if ($createMedium) {
                $image = self::resize($this->getPath(), $image_sizes['md'], $image_sizes['md']);
                $files['md'] = $saFile::saveStringToFile('md_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
            }

            if ($createSmall) {
                $image = self::resize($this->getPath(), $image_sizes['sm'], $image_sizes['sm']);
                $files['sm'] = $saFile::saveStringToFile('sm_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
            }

            if ($createExtraSmall) {
                $image = self::resize($this->getPath(), $image_sizes['xs'], $image_sizes['xs']);
                $files['xs'] = $saFile::saveStringToFile('xs_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
            }

            if ($createMicro) {
                $image = self::resize($this->getPath(), $image_sizes['micro'], $image_sizes['micro']);
                $files['micro'] = $saFile::saveStringToFile('micro_' . $this->getFilename(), $image, $this->getFolder(), 'saImage');
            }

            if(!$discard_original) $files['original'] = $this;

            /** @var saImage $file */
            foreach($files as $k => $file)
            {
                $file->setSizeName($k);
                $file->setCompleteFile(true);

                if($k != 'lg')
                    $file->setLarge($files['lg']);

                if($k != 'md')
                    $file->setMedium($files['md']);

                if($k != 'sm')
                    $file->setSmall($files['sm']);

                if($k != 'xs')
                    $file->setXsmall($files['xs']);

                if($k != 'micro')
                    $file->setMicro($files['micro']);

                if($k != 'original')
                    $file->setOriginal($files['original']);

            }
        }



		if($discard_original) app::$entityManager->remove($this); // remove original image file
		app::$entityManager->flush();

		return $this;
	}


	/**
	 * @param $data
	 * @param $name
	 * @param string $folder
	 * @return bool|saImage
     */
	public static function uploadFromString($data, $name, $folder='generic')
    {
		$file = parent::saveStringToFile($name, $data, $folder, 'saImage');
		return $file;
    }

	public static function saveStringToFile($fileName, $content, $folder='generic', $entity_name = 'saFile')
	{
		$file = parent::saveStringToFile($fileName, $content, $folder, 'saImage');
		return $file;
	}

    public static function resizeViaAPI($source_image_path, $apisizes)
    {
        //$client = new Sa3ApiClient(app::get()->getConfiguration()->get('image_resizing_api_url')->getValue(), app::get()->getConfiguration()->get('image_resizing_api_client_id')->getValue(), app::get()->getConfiguration()->get('image_resizing_api_client_key')->getValue());
        
        // if (!$client->isConnected()) {
        //     return false;
        // }

        // $data = array(
        //     'image'=> base64_encode( file_get_contents($source_image_path) ),
        //     'sizes'=>$apisizes,
        //     'name'=> pathinfo($source_image_path, PATHINFO_BASENAME)
        // );
        // $result = $client->images->Resize->upload($data);

        // return $result;
    }

    public static function resize($source_image_path, $maxWidth, $maxHeight)
    {
        ini_set('memory_limit', '512M');
        
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);

        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($source_image_path);

                if(!$source_gd_image) {
                    throw new ImageResizeException('Failed to resize Image, likely corrupted.');
                }

                break;
            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($source_image_path);

                if(!$source_gd_image) {
                    throw new ImageResizeException('Failed to resize Image, likely corrupted.');
                }

                if(!function_exists('read_exif_data')) {
                    break;
                }

                $exifData = read_exif_data($source_image_path);
                $exifOrientation = $exifData['Orientation'];

                // No EXIF data related to orientation available,
                // so no rotation needed
                if(empty($exifOrientation)) {
                    break;
                }

                // Rotate the image appropriately

                switch($exifOrientation) {
                    case 8:
                        $source_gd_image = imagerotate($source_gd_image, 90, 0);
                        break;
                    case 3:
                        $source_gd_image = imagerotate($source_gd_image, 180, 0);
                        break;
                    case 6:
                        $source_gd_image = imagerotate($source_gd_image, -90, 0);
                        break;
                }

                break;
            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($source_image_path);

                if(!$source_gd_image) {
                    throw new ImageResizeException('Failed to resize Image, likely corrupted.');
                }

                imagealphablending( $source_gd_image, true );
                break;
            default:
                throw new ImageException('Invalid image.');
        }

        if ($source_gd_image === false) {
            return false;
        }

        $source_aspect_ratio = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $maxWidth / $maxHeight;
        if ($source_image_width <= $maxWidth && $source_image_height <= $maxHeight) {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($maxHeight * $source_aspect_ratio);
            $thumbnail_image_height = $maxHeight;
        } else {
            $thumbnail_image_width = $maxWidth;
            $thumbnail_image_height = (int) ($maxWidth / $source_aspect_ratio);
        }
        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagealphablending( $thumbnail_gd_image, false );
        imagesavealpha( $thumbnail_gd_image, true );
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);

        ob_start();
        //imagejpeg($thumbnail_gd_image);

        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                imagegif($thumbnail_gd_image);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail_gd_image, null, 60);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail_gd_image, null, 5);
                break;
            default:
                throw new ImageException('Invalid image.');
        }

        $file_contents = ob_get_contents();
        ob_end_clean();

        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);

        return $file_contents;
    }

	/**
	 * @return mixed
	 */
	public function getLarge()
	{
		if($this->size_name == 'lg') return $this;
		else return $this->large;
	}

	/**
	 * @param mixed $large
	 */
	public function setLarge($large)
	{
		$this->large = $large;
	}

	/**
	 * @return mixed
	 */
	public function getMedium()
	{
		if($this->size_name == 'md') return $this;
		else return $this->medium;
	}

	/**
	 * @param mixed $medium
	 */
	public function setMedium($medium)
	{
		$this->medium = $medium;
	}

	/**
	 * @return mixed
	 */
	public function getMicro()
	{
		if($this->size_name == 'micro') return $this;
		else return $this->micro;
	}

	/**
	 * @param mixed $micro
	 */
	public function setMicro($micro)
	{
		$this->micro = $micro;
	}

	/**
	 * @return mixed
	 */
	public function getSmall()
	{
		if($this->size_name == 'sm') return $this;
		else return $this->small;
	}

	/**
	 * @param mixed $small
	 */
	public function setSmall($small)
	{
		$this->small = $small;
	}

	/**
	 * @return mixed
	 */
	public function getXsmall()
	{
		if($this->size_name == 'xs') return $this;
		else return $this->xsmall;
	}

	/**
	 * @param mixed $xsmall
	 */
	public function setXsmall($xsmall)
	{
		$this->xsmall = $xsmall;
	}

	/**
	 * @return mixed
	 */
	public function getSizeName()
	{
		return $this->size_name;
	}

	/**
	 * @param mixed $size
	 */
	public function setSizeName($size)
	{
		$this->size_name = $size;
	}

	/**
	 * @return mixed
	 */
	public function getOriginal()
	{
		if($this->size_name == 'original') return $this;
		else return $this->original;
	}

	/**
	 * @param mixed $original
	 */
	public function setOriginal($original)
	{
		$this->original = $original;
	}

}
