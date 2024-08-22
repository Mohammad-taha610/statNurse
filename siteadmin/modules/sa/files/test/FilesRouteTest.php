<?php

namespace sa\files\Test;

use PHPUnit\PhpParser\Node\Param;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Request;
use sacore\application\responses\File;
use sacore\application\responses\Json;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sa\files\filesConfig;
use sa\files\filesController;
use sa\files\saFile;
use sa\files\saFileBrowserController;
use sa\files\saFilesController;
use sa\system\systemConfig;
use sa\Test\RouteTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RouteCollection;

//Out of the 4 (System, Menus, Files, Messages) this one is probably the weakest in terms of test thoroughness, do with that info what you will
class FilesRouteTest extends RouteTest{

    const NUMBER_OF_ROUTES = 20;

    public function testRouteInit()
    {
        $rCollection = new RouteCollection();
        filesConfig::getRouteCollection($rCollection, 'files');
        $this->assertEquals(self::NUMBER_OF_ROUTES, $rCollection->count());
    }

    public function testFileUploadExists(){
        $this->singleRouteDefinition('files_modal_upload');
    }

    public function testFileUploadFunctionality(){
        $controller = new filesController();
        $view = $controller->showUpload();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testFileAcceptUploadExists(){
        $this->singleRouteDefinitionPost('files_accept_upload');
    }

    public function testFileAcceptUploadFunctionality(){
        $tempPath = sys_get_temp_dir();
        $tempFile = tempnam($tempPath, "tempTest.jpg");
        file_put_contents($tempFile, "testTestTestTest");
        $size = filesize($tempFile);
        $request = new Request([],[],[],[],['file' => ['name' => 'tempTest.jpg',
            'size' => $size, 'tmp_name' => $tempFile]]);
        $controller = new filesController();
        $json = $controller->acceptUpload($request);
        $this->assertInstanceOf(Json::class, $json);
    }

    public function testFilesExists(){
        $this->singleRouteDefinition('af_files');
    }

    public function testFilesFunctionality(){
        $controller = new filesController();
        $view = $controller->showFiles();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testFilesAjaxExists(){
        $this->singleRouteDefinition('af_files_ajax');
    }

    public function testFilesAjaxFunctionality(){
        $controller = new filesController();
        $json = $controller->ajaxGetFiles();
        $this->assertInstanceOf(Json::class, $json);
    }

    public function testFilesAddExists(){
        $this->singleRouteDefinition('files_add');
    }

    public function testFilesAddFunctionality(){
        $controller = new filesController();
        $json = $controller->ajaxGetFiles();
        $this->assertInstanceOf(Json::class, $json);
    }

    public function testFilesEditExists(){
        $this->singleRouteDefinition('files_edit');
    }

    public function testFilesEditNewFunctionality(){
        $request = new Request([],['filename'=>'test']);
        $request->setRouteParams(new ParameterBag(['id'=>0]));
        $controller = new filesController();
        $view = $controller->showAddEditFile($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testFilesEditOldFunctionality(){
        //Setup
        $id = $this->createFileRecord();

        $request = new Request([],['filename'=>'test']);
        $request->setRouteParams(new ParameterBag(['id'=>$id]));
        $controller = new filesController();

        //Test
        $view = $controller->showAddEditFile($request);
        $this->assertInstanceOf(View::class, $view);

        //Cleanup
        $this->removeFileRecordById($id);
    }

    public function testFilesSaveExists(){
        $this->singleRouteDefinitionPost('files_save');
    }

    public function testFilesSaveNewFunctionality(){
        $request = new Request([],['filename'=>'testSaveNew']);
        $request->setRouteParams(new ParameterBag(['id'=>0]));
        $controller = new filesController();
        $view = $controller->saveFile($request);
        $this->assertInstanceOf(View::class, $view);

        //Test
        $fileRepo = ioc::getRepository('saFile');
        $file = $fileRepo->search(['filename' => 'testSaveNew'])[0];
        $this->assertInstanceOf(saFile::class, $file);

        app::$entityManager->remove($file);
        app::$entityManager->flush();
    }


    public function testFilesSaveOldFunctionality(){
        //Setup
        $id = $this->createFileRecord();
        $request = new Request([],['filename'=>'testSave']);
        $request->setRouteParams(new ParameterBag(['id'=>$id]));
        $controller = new filesController();

        //Test
        $view = $controller->saveFile($request);
        $this->assertInstanceOf(View::class, $view);
        $fileRepo = ioc::getRepository('saFile');
        $file = $fileRepo->search(['filename' => 'testSave'])[0];
        $this->assertInstanceOf(saFile::class, $file);

        //Cleanup
        $this->removeFileRecordById($id);
    }

    public function testFilesDeleteExists(){
        $this->singleRouteDefinition('files_delete');
    }

    public function testFilesDeleteFunctionality(){
        $id = $this->createFileRecord();
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => $id]));
        $controller = new filesController();
        $view = $controller->deleteFile($request);
        $this->assertInstanceOf(View::class, $view);

        $repo = ioc::getRepository('saFile');
        $file = $repo->find($id);
        $this->assertNull($file);
    }

    public function testFilesBrowseExists(){
        $this->singleRouteDefinition('files_browse');
    }

    public function testFilesBrowseFunctionality(){
        $tempPath = sys_get_temp_dir();
        $tempFile = tempnam($tempPath, "tempTest.jpg");
        file_put_contents($tempFile, "testTestTestTest");
        $size = filesize($tempFile);
        $request = new Request([],
            ['folder' => 'testFolder','return' => true, 'uploadOnly' => false,'prependpath'=> true], [],[],
            ['file' => ['name' => 'tempTest.jpg', 'size' => $size, 'tmp_name' => $tempFile]]);
        $controller = new saFileBrowserController();
        $view = $controller->showFiles($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testFilesBrowseUploadExists(){
        $this->singleRouteDefinition('files_browse_upload');
    }

    public function testFileBrowseUploadFunctionality(){
        $tempPath = sys_get_temp_dir();
        $tempFile = tempnam($tempPath, "tempTest.jpg");
        file_put_contents($tempFile, "testTestTestTest");
        $size = filesize($tempFile);
        $request = new Request([],
            ['folder' => 'testFolder','return' => true, 'uploadOnly' => false,'prependpath'=> true], [],[],
            ['file' => ['name' => 'tempTest.jpg', 'size' => $size, 'tmp_name' => $tempFile]]);
        $controller = new saFileBrowserController();
        $json = $controller->uploadFile($request);
        $this->assertNotEquals(500, $json->getResponseCode());
    }

    public function testDownloadFileByIdExists(){
        $this->singleRouteDefinition('files_browse_download_file_by_id');
    }

    public function testDownloadFileByIdFunctionality(){
        $id = $this->createFileRecord();
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => $id]));
        $controller = new filesController();
        $file = $controller->downloadFileById($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testFilesBrowserViewExists(){
        $this->singleRouteDefinition('files_browser_view_file');
    }

    public function testFilesBrowserViewFunctionality(){
        $id = $this->createFileRecord();
        $tempPath = sys_get_temp_dir();
        $filename = 'test.zip';
        $tempFile = tempnam($tempPath, $filename);
        file_put_contents($tempFile, "testTestTestTest");
        $size = filesize($tempFile);
        $request = new Request([],
            ['folder' => 'testFolder','return' => true, 'uploadOnly' => false,'prependpath'=> true], [],[],
            ['file' => ['name' => 'tempTest.jpg', 'size' => $size, 'tmp_name' => $tempFile]]);
        $request->setRouteParams(new ParameterBag(['folder' => 'testFolder', 'file' => 'tempTest.jpg']));
        $controller = new filesController();
        $file = $controller->getFile($request);
        $this->assertInstanceOf(File::class, $file);
        $this->removeFileRecordById($id);
    }

    public function testFilesBrowserViewByIdExists(){
        $this->singleRouteDefinition('files_browser_view_file_by_id');
    }

    public function testFileBrowserViewByIdFunctionality(){
        $id = $this->createFileRecord();

        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => $id]));
        $controller = new filesController();
        $file = $controller->getFileById($request);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testFilesImagesExists(){
        $this->singleRouteDefinition('files_images');
    }

    public function testFilesImagesFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'throbber.gif']));
        $controller = new filesController();
        $response = $controller->img($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testFilesCSSExists(){
        $this->singleRouteDefinition('files_css');
    }

    public function testFilesCSSFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'browser.css']));
        $controller = new filesController();
        $response = $controller->css($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testFilesJSExists(){
        $this->singleRouteDefinition('files_js');
    }

    public function testFilesJSFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'files.js']));
        $controller = new FilesController();
        $response = $controller->js($request);
        $this->assertInstanceOf(File::class, $response);
    }

    public function testSAFilesExists(){
        $this->singleRouteDefinition('sa_files');
    }

    public function testSAFilesFunctionality(){
        $request = new Request();
        $controller = new saFilesController();
        $view = $controller->manageFiles($request);
        $this->assertInstanceOf(View::class, $view);
    }

    public function testSAFilesCreateExists(){
        $this->singleRouteDefinition('sa_files_create');
    }

    public function testSAFilesCreateFunctionality(){
        $controller = new saFilesController();
        $controller->editUploads();
        $this->assertTrue(method_exists($controller, 'editUploads'));
    }

    public function testSAFilesDeleteExists(){
        $this->singleRouteDefinition('sa_files_delete');
    }

    public function testSAFilesDeleteFunctionality(){
        $id = $this->createFileRecord();
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['id' => $id]));
        $controller = new saFilesController();
        $redirect = $controller->deleteFiles($request);
        $this->assertInstanceOf(Redirect::class, $redirect);
        $repo = ioc::getRepository('saFile');
        $file = $repo->find($id);
        $this->assertNull($file);
    }

    public function testSAFilesImageExists(){
        $this->singleRouteDefinition('sa_files_img');
    }

    public function testSAFilesImageFunctionality(){
        $request = new Request();
        $request->setRouteParams(new ParameterBag(['file' => 'throbber.gif']));
        $controller = new filesController();
        $response = $controller->img($request);
        $this->assertInstanceOf(File::class, $response);
    }

    /** HELPER FUNCTIONS */
    private function createFileRecord(){
        $filename = 'test.zip';
        /**
         * @var saFile $file
         */
        $file = ioc::resolve('saFile');
        $file->setFilename($filename);
        $file->setDiskFilename($filename);
        $file->setFolder('testFolder');
        $tempPath = sys_get_temp_dir();
        $tempFile = tempnam($tempPath, $filename);
        file_put_contents($tempFile, "testTestTestTest");
        $size = filesize($tempFile);
        $file->setFileSize($size);

        $uploadDir = app::get()->getConfiguration()->get('uploadsDir') . '/' . $filename;
        rename($tempFile, $uploadDir);
        app::$entityManager->persist($file);
        app::$entityManager->flush();

        $id = $file->getId();
        return $id;
    }

    private function removeFileRecordById($id){
        $repo = ioc::getRepository('saFile');
        $file = $repo->find($id);
        app::$entityManager->remove($file);
        app::$entityManager->flush();
    }

    protected function moduleName()
    {
        return "files";
    }
    protected function configClass()
    {
        return filesConfig::class;
    }
}