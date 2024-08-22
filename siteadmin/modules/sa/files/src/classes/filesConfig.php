<?php

namespace sa\files;

use sacore\application\modRequest;
use sacore\application\moduleConfig;
use sacore\application\route;
use sacore\application\saRoute;
use sacore\application\resourceRoute;
use sacore\application\navItem;
use sacore\application\staticResourceRoute;

abstract class filesConfig extends  moduleConfig {

    const safe_mode_compatible = true;

    static function init()
    {
        modRequest::listen('sa.files.list', 'saFileBrowserController@modRequestGetFilesForFolder', 1, null, true, true);
        modRequest::listen('assets.additional_cache_request', 'saFilesController@modRequestAdditionalCache', 1, null, false, true);
        modRequest::listen('sa.files.delete', 'saFileBrowserController@modRequestDeleteFiles', 1, null, true, true);
//        modRequest::listen('sa.header', 'saFilesController@CMSHeaderWidget', route::PRIORITY_BELOWNORMAL);
    }

    static function postInit()
    {
        modRequest::request('store.post.tasks', null, array(
            'FilesModuleTasksV1'
        ));

        modRequest::request('api.registerEntityAPI', null, array(
            'controller'=>'saFileAPIController', 'route'=>'safile'
        ));
    }

//    static function getRoutes()
//    {
//        return array(
//            // -------------- FRONTEND ROUTES -------------------
//            new route(array( 'id'=>'files_modal_upload', 'name'=>'File Upload', 'route'=>'/files/upload', 'controller'=>'filesController@showUpload' )),
//
//            //new route(array( 'id'=>'files_upload', 'name'=>'File Upload', 'route'=>'/files/upload', 'controller'=>'filesController@showUpload' )),
//            new route(array( 'id'=>'files_accept_upload', 'name'=>'File Upload', 'method'=>"POST", 'route'=>'/files/upload', 'controller'=>'filesController@acceptUpload' )),
//            new route( array('id'=>'af_files', 'name'=>'Files', 'route'=>'/files', 'controller'=>'filesController@showFiles', 'protected'=>false )),
//            new route( array('id'=>'af_files_ajax', 'name'=>'Files - Ajax', 'route'=>'/files/ajax/get_files', 'controller'=>'filesController@ajaxGetFiles', 'protected'=>false )),
//
//            new route( array('id'=>'files_add', 'name'=>'File - Add', 'route'=>'/files/add', 'controller'=>'filesController@showAddEditFile',	'protected'=>true )),
//            new route( array('id'=>'files_edit', 'name'=>'File - Edit',	'route'=>'^/files/[0-9]{1,}/edit$',	'controller'=>'filesController@showAddEditFile', 'protected'=>true )),
//            new route( array('id'=>'files_save', 'name'=>'File - Save',	'method'=>'POST', 'route'=>'^/files/[0-9]{1,}/save$', 'controller'=>'filesController@saveFile', 'protected'=>true )),
//            new route( array('id'=>'files_delete', 'name'=>'File - Delete', 'route'=>'^/files/[0-9]{1,}/delete', 'controller'=>'filesController@deleteFile', 'protected'=>true )),
//
//            new saRoute( array('id'=>'files_browse', 'name'=>'Files Browse', 'route'=>'/siteadmin/files/browse', 'controller'=>'saFileBrowserController@showFiles', 'protected'=>true )),
//            new saRoute( array('id'=>'files_browse_upload', 'name'=>'Files Browser Upload', 'route'=>'/siteadmin/files/browse/upload', 'controller'=>'saFileBrowserController@uploadFile', 'protected'=>true )),
//
//            new staticResourceRoute(array('id'=>'files_browser_download_file_by_id', 'name'=>'Download File by ID', 'route'=>'^/assets/files/download/id/[0-9]{1,}$', 'controller'=>'filesController@downloadFileById')),
//
//            new staticResourceRoute(array('id'=>'files_browser_view_file', 'name'=>'images', 'route'=>'^/assets/files/[a-zA-Z0-9-_\.\s]{1,}/[a-zA_Z0-9-_\.\s]{1,}(.jpeg|.jpg|.png|.gif|.tiff|.pdf|.xls|.xlsx|.xlsb|.txt|.doc|.docx|.ppt|.pptx|.m4p|.m4v|.mp4|.mpg|.mp2|.mpeg|.mpe|.mpv|.3gp|.flv|.vob|.ogg|.ogv|.svg|.avi|.txt|.mp3|.wav|.wmv|.mov)$', 'controller'=>'filesController@getFile')),
//            new staticResourceRoute(array('id'=>'files_browser_view_file_by_id', 'name'=>'images by id', 'route'=>'^/assets/files/id/[0-9]{1,}$', 'controller'=>'filesController@getFileById')),
//
//            new staticResourceRoute(array('id'=>'files_images', 'name'=>'images', 'route'=>'^/assets/files/images/[a-zA-Z0-9-_\.]{1,}$', 'controller'=>'filesController@images')),
//            new staticResourceRoute(array('id'=>'files_css', 'name'=>'css', 'route'=>'^/assets/files/css/[a-zA-Z0-9-_\.]{1,}$', 'controller'=>'filesController@css')),
//            new staticResourceRoute(array('id'=>'files_js', 'name'=>'js', 'route'=>'^/assets/files/js/[a-zA-Z0-9-_\.]{1,}$', 'controller'=>'filesController@js')),
//
//            // -------------- SITEADMIN ROUTES -------------------
//
//            new saRoute(array( 'id'=>'sa_files', 'name'=>'Manage Uploads', 'permissions' => 'files_view', 'route'=>'/siteadmin/files', 'controller'=>'saFilesController@manageFiles' )),
//            new saRoute(array( 'id'=>'sa_files_create', 'name'=>'Create Uploads', 'route'=>'/siteadmin/files/create', 'controller'=>'saFilesController@editUploads' )),
//            new saRoute(array( 'id'=>'sa_files_delete', 'name'=>'Delete Uploads', 'route'=>'^/siteadmin/files/[0-9]{1,}/delete$', 'controller'=>'saFilesController@deleteFiles' )),
//
//            new staticResourceRoute(array( 'id'=>'sa_files_img', 'name'=>'files_img', 'route'=>'^/files/img/[a-zA-Z0-9-_\.]{1,}$', 'controller'=>'filesController@img' )),
//
//        );
//    }


    static function initRoutes($routes)
    {
        $routes->addWithOptionsAndName('File Upload', 'files_modal_upload', '/files/upload')->controller('filesController@showUpload');
        $routes->addWithOptionsAndName('File Upload', 'files_accept_upload', '/files/upload')->controller('filesController@acceptUpload')->methods(['POST']);
        //Todo: Page looks very janky, something is very wrong with this view
        $routes->addWithOptionsAndName('Files', 'af_files', '/files')->controller('filesController@showFiles');// 'protected' => false
        $routes->addWithOptionsAndName('Files - Ajax', 'af_files_ajax', '/files/ajax/get_files')->controller('filesController@ajaxGetFiles');// 'protected' => false

        $routes->addWithOptionsAndName('Files - Add', 'files_add', '/files/add')->controller('filesController@ajaxGetFiles')->middleware('SaAuthMiddleware');// 'protected' => true
        $routes->addWithOptionsAndName('Files - Edit', 'files_edit', '/files/{id}/edit')->controller('filesController@showAddEditFile')->middleware('SaAuthMiddleware');// 'protected' => true
        $routes->addWithOptionsAndName('Files - Save', 'files_save', '/files/{id}/save')->controller('filesController@saveFile')->methods(["POST"])->middleware('SaAuthMiddleware');// 'protected' => true
        $routes->addWithOptionsAndName('Files - Delete', 'files_delete', '/files/{id}/delete')->controller('filesController@deleteFile')->middleware('SaAuthMiddleware');// 'protected' => true

        $routes->addWithOptionsAndName('Files Browse', 'files_browse','/siteadmin/files/browse')->controller('saFileBrowserController@showFiles')->middleware('SaAuthMiddleware'); //'protected'=>true
        $routes->addWithOptionsAndName('Files Browser Upload', 'files_browse_upload','/siteadmin/files/browse/upload')->controller('saFileBrowserController@uploadFile')->methods(['POST'])->middleware('SaAuthMiddleware'); //'protected'=>true

        $routes->addWithOptionsAndName('Download File by ID', 'files_browse_download_file_by_id','/assets/files/download/id/{id}')->controller('filesController@downloadFileById');

        $routes->addWithOptionsAndName('images', 'files_images','/assets/files/images/{file}')->controller('filesController@img');
        $routes->addWithOptionsAndName('css', 'files_css','/assets/files/css/{file}')->controller('filesController@css');
        $routes->addWithOptionsAndName('js', 'files_js','/assets/files/js/{file}')->controller('filesController@js');

        $routes->addWithOptionsAndName('images by id', 'files_browser_view_file_by_id','/assets/files/id/{id}')->controller('filesController@getFileById');
        $routes->addWithOptionsAndName('images', 'files_browser_view_file','/assets/files/{folder}/{file}')->controller('filesController@getFile');


        $routes->addWithOptionsAndName('Manage Uploads', 'sa_files','siteadmin/files')->defaults(['route_permissions' => ['files_view']])->controller('saFilesController@manageFiles')->middleware('SaPermissionMiddleware');; //'permissions' => 'files_view'
        $routes->addWithOptionsAndName('Create Uploads', 'sa_files_create','/siteadmin/files/create')->controller('saFilesController@editUploads');
        $routes->addWithOptionsAndName('Delete Uploads', 'sa_files_delete','/siteadmin/files/{id}/delete')->controller('saFilesController@delteFiles');

        $routes->addWithOptionsAndName('files_img', 'sa_files_img','/files/img/{files}')->controller('filesController@img');


    }

    static function getNavigation()
    {
        return array(
            // FRONT END

            //new navItem(array( 'id'=>'forms', 'name'=>'Forms', 'route'=>'#', 'icon'=>'fa fa-file', 'parent'=>'toUseMeChangeMeToRoot' )),
            //new navItem(array( 'id'=>'forms_upload', 'name'=>'Forms Upload', 'routeid'=>'files_upload', 'icon'=>'fa fa-file', 'parent'=>'forms' )),
            //new navItem(array('id'=>'af_files', 'name'=>'Files', 'routeid'=>'af_files', 'icon'=>'fa fa-floppy-o', 'parent'=>'root')),


            // SITEADMIN
            new navItem(array( 'id'=>'sa_files', 'name'=>'Files', 'icon'=>'fa fa-files-o', 'parent'=>'sa_settings'  )),
            new navItem(array( 'name'=>'Manage Files',  'routeid'=>'sa_files', 'icon'=>'fa fa-double-angle-right', 'parent'=>'sa_files'  )),


        );
    }

    static function getDatabase()
    {
        return array(
            'wormConfig' => array(
                'alternativeNamespaces' => array(
                    'sa\files'
                ),
            ),
            'tables' => array()
        );
    }

    public static function getPermissions()
    {
        $permissions = array();
        $permissions['files_view'] = 'View Files';
        $permissions['files_create'] = 'Add Files';
        $permissions['files_delete'] = 'Delete Files';
        return $permissions;
    }

    public static function getSettings()
    {
        $module_settings = array(
            'allowed_additional_files_types' => array('default' => ''),
            'allow_filename_overrides' => array('type' => 'boolean', 'default' => false),
            'image_resizing_api_url' => array('default' => 'http://images.siteadministrator.com'),
            'image_resizing_api_client_id' => array('default' => 'sa'),
            'image_resizing_api_client_key' => array('default' => 'nMxex5ZCgcSON13CvQz/5hgoFmXnB5ANRyhzHzw/Eik=')
        );

        return $module_settings;
    }

}
