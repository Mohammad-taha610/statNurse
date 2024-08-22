<?php

namespace sa\files;

use Doctrine\Common\Util\Debug;
use \sacore\application\app;
use sacore\application\ioc;
use sacore\application\jsonView;
use sacore\application\modRequest;
use sacore\application\responses\Json;
use \sacore\application\saController;
use \sacore\application\saRoute;
use \sacore\application\navItem;
use \sacore\application\modelResult;
use \sacore\application\responses\View;
use sacore\utilities\doctrineUtils;
use \sacore\utilities\url;
use \sacore\utilities\notification;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class saFileBrowserController extends saController {

    static function getDefaultResources()
    {
        return array(
            array('type'=>'css', 'path'=> '/vendor/blueimp/jquery-file-upload/css/jquery.fileupload.css' ),
            array('type'=>'js', 'path'=> '/vendor/blueimp/jquery-file-upload/js/vendor/jquery.ui.widget.js'),
            array('type'=>'js', 'path'=> '/vendor/blueimp/jquery-file-upload/js/jquery.iframe-transport.js'),
            array('type'=>'js', 'path'=> '/vendor/blueimp/jquery-file-upload/js/jquery.fileupload.js')
        );
    }

    /**
     * @param static saFile $saFile
     * @throws \sacore\application\Exception
     */
    public function showFiles($request) {
        $folders = app::$entityManager->getRepository(  ioc::staticResolve('saFile') )->getFolders();

        if ( !in_array($request->get('ofolder'), $folders) && !empty($request->get('ofolder')) ) {
            $folders[] = $request->get('ofolder');
        }

        if ( !in_array($request->get('folder'), $folders) && !empty($request->get('folder')) ) {
            $folders[] = $request->get('folder');
        }

        $view = new View('file_browser', $this->viewLocation(), false );
        $view->data['folder_list'] = $folders;
        $view->data['highlight'] = !empty($_SESSION['last_upload_id']) ? $_SESSION['last_upload_id'] : '';
        $view->data['return'] = $request->get('return');
        $view->data['folder'] = $request->get('folder');
        $view->data['uploadOnly'] = $request->get('uploadOnly');
        $view->data['prependpath'] = $request->get('prepend_site_path') ? rtrim(app::get()->getConfiguration()->get('site_url')->getValue(), '/') : '';


        unset($_SESSION['last_upload_id']);
        return $view;
    }

    public static function modRequestGetFilesForFolder($data) {
        $files = app::$entityManager->getRepository(  ioc::staticResolve('saFile') )->getFilesInFolder($data['folder'], $data['offset'], 30, $data['search'], true );

        $prependpath = $data['prepend_site_path'] ? rtrim(app::get()->getConfiguration()->get('site_url')->getValue(), '/') : '';

        $data['files'] = array();

        /** @var saFile $file */
        foreach($files as $file) {
            $fileArray = doctrineUtils::getEntityArray($file);
            $fileArray['file_variations'] = array();

            if($file instanceof saImage) {
                if($micro = $file->getMicro()) {
                    $microArray = doctrineUtils::getEntityArray($micro);
                    $microArray['icon'] = $file->getFileIcon();
                    $microArray['filepath'] = $prependpath.app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $micro->getFolder(), 'file' => $micro->getFilename()]);
                    $fileArray['file_variations'][] = $microArray;
                }

                if($xSmall = $file->getXsmall()) {
                    $xSmallArray = doctrineUtils::getEntityArray($xSmall);
                    $xSmallArray['icon'] = $file->getFileIcon();
                    $xSmallArray['filepath'] = $prependpath.app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $xSmall->getFolder(), 'file' => $xSmall->getFilename()]);
                    $fileArray['file_variations'][] = $xSmallArray;
                }

                if($small = $file->getSmall()) {
                    $smallArray = doctrineUtils::getEntityArray($small);
                    $smallArray['icon'] = $file->getFileIcon();
                    $smallArray['filepath'] = $prependpath.app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $small->getFolder(), 'file' => $small->getFilename()]);
                    $fileArray['file_variations'][] = $smallArray;
                }

                if($medium = $file->getMedium()) {
                    $mediumArray = doctrineUtils::getEntityArray($medium);
                    $mediumArray['icon'] = $file->getFileIcon();
                    $mediumArray['filepath'] = $prependpath.app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $medium->getFolder(), 'file' => $medium->getFilename()]);
                    $fileArray['file_variations'][] = $mediumArray;
                }

                if($large = $file->getLarge()) {
                    $largeArray = doctrineUtils::getEntityArray($large);
                    $largeArray['icon'] = $file->getFileIcon();
                    $largeArray['filepath'] = $prependpath.app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $large->getFolder(), 'file' => $large->getFilename()]);
                    $fileArray['file_variations'][] = $largeArray;
                }
            }

            $fileArray['icon'] = $file->getFileIcon();
            $fileArray['filepath'] = $prependpath.app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $file->getFolder(), 'file' => $file->getFilename()]);

            $data['files'][] = $fileArray;
        }

        return $data;
    }

    public function uploadFile($request)
    {
        $folder = !empty($request->get('folder')) ? $request->get('folder') : 'Page_Editor_Files';

        $type = 'image';

        //Todo:Comeback here when I have figured out how to spoof files
        
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $friendlyFilename = preg_replace('/[\'"]/', '', $file->getClientOriginalName());

        $file->getPath() . '/' . $file->getPathname();
        $file = ['name' => $friendlyFilename, 'tmp_name' => $file->getRealPath()];
        $images = array('jpeg', 'jpg', 'png', 'gif');

        $classRef = null;
        $type = null;

        if ( in_array( strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), $images ) ) {
            $classRef = ioc::staticGet('saImage');
            $type = 'image';
        } else {
            $classRef = ioc::staticGet('saFile');
            $type = 'file';
        }

        try {
            /** @var saFile $file */
            $file = $classRef::upload($file, $folder);

            $fileArray = doctrineUtils::getEntityArray($file);
            $fileArray['icon'] = $file->getFileIcon();
            $fileArray['filepath'] = app::get()->getRouter()->generate('files_browser_view_file',['folder' => $file->getFolder(), 'file' => $file->getFilename()]);
            $fileArray['file_variations'] = array();

            if ($type=='image') {
                /** @var saImage $file */

                /** @var saImage $large */
                if($large = $file->getLarge()) {
                    $largeArray = doctrineUtils::getEntityArray($large);
                    $largeArray['icon'] = $file->getFileIcon();
                    $largeArray['filepath'] = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $large->getFolder(), 'file' => $large->getFilename()]);
                    $fileArray['file_variations'][] = $largeArray;
                }

                /** @var saImage $medium */
                if($medium = $file->getMedium()) {
                    $mediumArray = doctrineUtils::getEntityArray($medium);
                    $mediumArray['icon'] = $file->getFileIcon();
                    $mediumArray['filepath'] = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $medium->getFolder(), 'file' => $medium->getFilename()]);
                    $fileArray['file_variations'][] = $mediumArray;
                }

                /** @var saImage $small */
                if($small = $file->getSmall()) {
                    $smallArray = doctrineUtils::getEntityArray($small);
                    $smallArray['icon'] = $file->getFileIcon();
                    $smallArray['filepath'] = app::get()->getRouter()->generate('files_browser_view_file',['folder' => $small->getFolder(), 'file' => $small->getFilename()]);
                    $fileArray['file_variations'][] = $smallArray;
                }

                /** @var saImage $xSmall */
                if($xSmall = $file->getXsmall()) {
                    $xSmallArray = doctrineUtils::getEntityArray($xSmall);
                    $xSmallArray['icon'] = $file->getFileIcon();
                    $xSmallArray['filepath'] = app::get()->getRouter()->generate('files_browser_view_file',['folder' => $xSmall->getFolder(), 'file' => $xSmall->getFilename()]);
                    $fileArray['file_variations'][] = $xSmallArray;
                }

                /** @var saImage $micro */
                if($micro = $file->getMicro()) {
                    $microArray = doctrineUtils::getEntityArray($micro);
                    $microArray['icon'] = $file->getFileIcon();
                    $microArray['filepath'] = app::get()->getRouter()->generate('files_browser_view_file',['folder' => $micro->getFolder(), 'file' => $micro->getFilename()]);
                    $fileArray['file_variations'][] = $microArray;
                }
            }

            app::$entityManager->flush();
            $_SESSION['last_upload_id'] = $file->getId();
            $file = array( 'id'=>$file->getId() );

            modRequest::request('assets.rebuild');

            $view = new Json();
            $view->data['files'] = array($fileArray);
        } catch(FileUploadException $e) {
            $view = new Json(500);
            $view->data['error'] = $e->getMessage();

        }

        return $view;
    }

    public function modRequestDeleteFiles($data) {
        $view = new Json();

        if($data['files']) {
            $fileCollection = ioc::getRepository('saFile')->findBy(array('id' => $data['files']));

            if($fileCollection) {
                /** @var saFile $file */
                foreach($fileCollection as $file) {
                    $image = ioc::staticGet('saImage');

                    /**
                     * NOTE:
                     *   MSSQL doesn't allow us to have circular references with self-referenced tables.
                     *   It refuses to examine the relationship to see if these references are infinite,
                     *   so a manual deletion process is require to unhook all relationships and remove
                     *   these objects from the DB on MSSQL. (kcarter - 3/20/2020)
                     */

                    if($file instanceof $image) {
                        /** @var saImage $micro */
                        $micro = $file->getMicro();

                        if($micro) {
                            $file->setMicro(null);

                            $micro->setMicro(null);
                            $micro->setXsmall(null);
                            $micro->setSmall(null);
                            $micro->setMedium(null);
                            $micro->setLarge(null);
                            $micro->setOriginal(null);
                        }

                        /** @var saImage $xSmall */
                        $xSmall = $file->getXsmall();

                        if($xSmall) {
                            $file->setXsmall(null);

                            $xSmall->setMicro(null);
                            $xSmall->setXsmall(null);
                            $xSmall->setSmall(null);
                            $xSmall->setMedium(null);
                            $xSmall->setLarge(null);
                            $xSmall->setOriginal(null);
                        }

                        /** @var saImage $small */
                        $small = $file->getSmall();

                        if($small) {
                            $file->setSmall(null);

                            $small->setMicro(null);
                            $small->setXsmall(null);
                            $small->setSmall(null);
                            $small->setMedium(null);
                            $small->setLarge(null);
                            $small->setOriginal(null);
                        }

                        /** @var saImage $medium */
                        $medium = $file->getMedium();

                        if($medium) {
                            $file->setMedium(null);

                            $medium->setMicro(null);
                            $medium->setXsmall(null);
                            $medium->setSmall(null);
                            $medium->setMedium(null);
                            $medium->setLarge(null);
                            $medium->setOriginal(null);
                        }

                        /** @var saImage $large */
                        $large = $file->getLarge();

                        if($large) {
                            $file->setLarge(null);

                            $large->setMicro(null);
                            $large->setXsmall(null);
                            $large->setSmall(null);
                            $large->setMedium(null);
                            $large->setLarge(null);
                            $large->setOriginal(null);
                        }
                    }

                    try {
                        app::$entityManager->remove($file);
                        app::$entityManager->flush($file);

                        if($file instanceof $image) {
                            if($micro) {
                                app::$entityManager->remove($micro);
                            }

                            if($xSmall) {
                                app::$entityManager->remove($xSmall);
                            }

                            if($small) {
                                app::$entityManager->remove($small);
                            }

                            if($medium) {
                                app::$entityManager->remove($medium);
                            }

                            if($large) {
                                app::$entityManager->remove($large);
                            }

                            app::$entityManager->flush();
                        }
                    } catch(\Exception $e) {
                        $view->data['containsFailedFiles'] = true;
                    }
                }
            }
        }

        return $view;
    }

}
