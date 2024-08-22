<?php

namespace sa\files;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use sacore\application\app;
use sacore\application\Request;
use sacore\application\responses\Json;
use sacore\application\responses\ResponseUtils;
use sacore\application\responses\View;
use \sacore\application\controller;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\jsonView;
use sacore\application\modRequest;
use sacore\application\responses\File;
use sacore\application\ValidateException;
use sacore\application\ViewException;
use sacore\utilities\doctrineUtils;
use sacore\utilities\Header;
use \sacore\utilities\notification;
use GuzzleHttp\Psr7\MimeType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class filesController extends controller {

    static function getDefaultResources()
    {
        return array(

            array('type'=>'css', 'path'=> '/vendor/blueimp/jquery-file-upload/js/jquery.fileupload.css' ),
            array('type'=>'js', 'path'=> '/vendor/blueimp/jquery-file-upload/js/vendor/jquery.ui.widget.js'),
            array('type'=>'js', 'path'=> '/vendor/blueimp/jquery-file-upload/js/jquery.iframe-transport.js'),
            array('type'=>'js', 'path'=> '/vendor/blueimp/jquery-file-upload/js/jquery.fileupload.js'),

        );
    }

    public function getFile($request)
    {
        $folder = $request->getRouteParams()->get('folder');
        $file = $request->getRouteParams()->get('file');
        $dashes = str_replace('_', '-', $folder);
        $underscores = str_replace('-', '_', $folder);

        /** @var saFile $file */
        $file = app::$entityManager->getRepository( ioc::staticResolve('saFile') )->findOneBy( 
            array('folder'=>array($folder, $dashes, $underscores), 'filename'=>$file),
            array('id' => 'DESC') 
        );
        
        if (!$file) {
            return $this->error404(true);
        }

        return new File($file->getPath());
    }

    public function getFileById($request)
    {
        $id = $request->getRouteParams()->get('id');
        /** @var saFile $file */
        $file = app::$entityManager->getRepository( ioc::staticResolve('saFile') )->findOneBy( array('id'=>$id) );
        if (!$file) {
            return $this->error404(true);
        }

        return new File($file->getPath());
    }

    public function downloadFileById($request) {
        $id = $request->getRouteParams()->get('id');
        /** @var saFile $file */
        $file = ioc::getRepository('saFile')->findOneBy(array('id' => $id));
        if(!$file) {
            return $this->error404();
        }
        
        $uploadsDir = app::get()->getConfiguration()->get('uploadsDir')->getValue();

        $view = new File($uploadsDir . DIRECTORY_SEPARATOR . $file->getDiskFileName());
        $view->setDownloadable($file->getFilename());
        
        return $view;
    }

//    No longer needed, just causes errors, will keep here in case part of it is needed for something I don't understand
//    public function images($view)
//    {
//        $view = new assetView($view, $this->moduleLocation().'/images/');
//        $view->display();
//    }
//
//    public function css($view)
//    {
//        $view = new assetView( $view, $this->moduleLocation().'/css/');
//        $view->display();
//    }
//
//    public function js($view)
//    {
//        $view = new View( $view, $this->moduleLocation().'/js/');
//        $view->display();
//        return $view;
//    }
    public function js($request)
    {

        $file_path = ResponseUtils::filePath($request->getRouteParams()->get('file'), static::assetLocation('js'));
        if (!$file_path) {
            $response = $this->error404();
        }
        else{
            try {
                $response = new File($file_path);
            } catch(ViewException $e) {
                $response = $this->error404();
            }
        }
        return $response;

    }

    public function showFiles()
    {
        $view = new View('files', $this->viewLocation(), false);
        return $view;
    }

    public function ajaxGetFiles() {

        /** @var \sa\files\saFile $file */
        $member = modRequest::request('auth.member', false);

        $data = app::$entityManager->getRepository( ioc::staticResolve('saFile') )->dataTablesRequest( $member, $_REQUEST['search'], $_REQUEST['draw'], $_REQUEST['start'], $_REQUEST['length'], $_REQUEST['order'][0]['column'], $_REQUEST['order'][0]['dir'] );

        $view = new Json;
        $view->data = $data;
        return $view;

    }

    public function showUpload()
    {
        $view = new View( 'upload', $this->viewLocation(), false );
        return $view;
    }

    /**
     * @param Request $request
     */
    public function acceptUpload($request)
    {
        $notify = new notification();
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $uploaded_file = [
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'tmp_name' => $file->getRealPath(),
            'error' => $file->getError(),
            'size' => $file->getSize()
        ];


        /** @var saFile $file_info */
        $file_info = saFile::upload($uploaded_file, 'uploads');

        if ($file_info)
        {
            $files = array( 'files'=> array(
                "name"=> $file_info->getFilename(),
                "size"=> $file_info->getSize(),
                "id"=> $file_info->getId(),
                "date" => $file_info->getDateCreated()->format('m/d/Y g:i A'),
                "url" => app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $file_info->getFolder(), 'file' => $file_info->getFilename()]),
		"is_completed_file" => $file_info->getIsCompletedFile()
            ));

            $view = new Json();
            $view->data = $files;
            return $view;
        }
        else
        {
            header('HTTP/1.0 500 Error');
            echo 'File was not uploaded. Please try again.';
        }
    }

    public function deleteFile($request)
    {

        /** @var \sa\member\saMember $member */
        $member = modRequest::request('auth.member', false);
        $id = $request->getRouteParams()->get('id');

        $obj = app::$entityManager->getRepository(ioc::staticResolve('saFile'))->findOneBy(array('id' => $id));

        $notify = new notification();

        try {
            app::$entityManager->remove($obj);
            app::$entityManager->flush();

            $notify->addNotification('success', 'Success', 'File deleted successfully.');

            $view = new View('modal_message', $this->viewLocation(), true);
            return $view;
        } catch (ValidateException $e) {

        } catch (ForeignKeyConstraintViolationException $e) {
            $notify->addNotification('danger', 'Error', 'We are unable to delete this file.');
            $view = new View('modal_message', $this->viewLocation(), true);
            return $view;
        }
    }


    public function showAddEditFile($request) {
        /** @var \sa\member\saMember $member */
        $member = modRequest::request('auth.member', false);
        $id = $request->getRouteParams()->get('id');
        $data = $request->query->all();

        $view = new View('modal_file_add_edit', $this->viewLocation());
        $view->data['id'] = $id;

        if ($id>0) {
            /** @var saFile $file */
            $file = app::$entityManager->getRepository( ioc::staticResolve('saFile') )->findOneBy( array('id'=>$id) );

            $view->data = doctrineUtils::convertEntityToArray($file);
        }

        if ($data)
            $view->data = array_merge($view->data, $data);

        return $view;
    }

    public function saveFile($request) {
        /** @var \sa\member\saMember $member */
        $member = modRequest::request('auth.member', false);
        $user = modRequest::request('auth.user');

        $id = $request->getRouteParams()->get('id');
        $data = $request->request->all();
        $notify = new notification();

        /** @var saFile $file */
        if ($id>0) {
            $file = app::$entityManager->getRepository( ioc::staticResolve('saFile') )->findOneBy( array('id'=>$id) );
        }
        else {
            $file = ioc::resolve('saFile');
        }

        $file = doctrineUtils::setEntityData($data, $file);

        try {
            app::$entityManager->persist($file);
            app::$entityManager->flush();

            $notify->addNotification('success', 'Success', 'File saved successfully.');
            $view = new View('modal_message', $this->viewLocation(), true);
            return $view;

        }
        catch (ValidateException $e) {
            $notify->addNotification('danger', 'Failed', $e->getMessage() );
            $this->showAddEditFile($request);
        }
    }

}
