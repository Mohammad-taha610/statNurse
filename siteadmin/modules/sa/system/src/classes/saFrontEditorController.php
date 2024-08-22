<?php
namespace sa\system;

use sacore\application\app;
use sacore\application\modRequest;
use \sacore\application\saController;
use \sacore\application\view;
use sacore\utilities\doctrineUtils;

/*
* saFrontEditorController.php
*
*/
class saFrontEditorController extends saController
{


    /**
     * siteFrontEditor
     *
     * Describe your function here
     */
    public function siteFrontEditor() {

        $_SESSION['front_editor_mode'] = true;

        $view = new view('master_nobar', 'toolbar', $this->viewLocation());
        $view->setAllowFrames(true, true);
        $view->display();

        //doctrineUtils::getEntityCollectionArray()
    }


    public function siteFrontEditorSitemap() {

        
        
    }

}