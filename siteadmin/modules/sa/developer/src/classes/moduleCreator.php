<?php

namespace sa\developer;

use sacore\application\app;
use sacore\utilities\stringUtils;

class moduleCreator
{
    private $name = false;

    private $cleanedName = false;

    private $type = false;

    private $namespace = false;

    private $path = false;

    private $models = [];

    private $baseModel = false;

    public function setName($name)
    {
        $this->name = $name;
        $this->cleanedName = strtolower(stringUtils::convertAlphaNumeric($name));
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        $path = app::getInstance()->getAppPath();

        $namespace = explode('\\', $this->namespace);

        $this->path = $path.'/modules/'.$namespace[0].'/'.$namespace[1];
    }

    public function setModels($models)
    {
        $this->models = $models;
    }

    public function setBaseModel($baseModel)
    {
        $this->baseModel = $baseModel;

        if (! empty($baseModel)) {
            $this->models[] = $baseModel;
        }
    }

    public function create()
    {
        $this->createDirectories();

        $this->createModels();

        $this->createControllers();

        $this->createConfig();

        unset($_SESSION['moduleCache']);
        //exit;
    }

    public function createDirectories()
    {
        mkdir($this->path.'/classes', 0777, true);
        mkdir($this->path.'/controller', 0777, true);
        mkdir($this->path.'/css', 0777, true);
        mkdir($this->path.'/images', 0777, true);
        mkdir($this->path.'/js', 0777, true);
        mkdir($this->path.'/views', 0777, true);
    }

    public function createModels()
    {
        if (! is_array($this->models)) {
            $this->models = [];
        }

        foreach ($this->models as $model) {
            $this->createModel($model);
        }
    }

    public function createConfig()
    {
        $className = explode('\\', $this->namespace);
        $className = end($className);

        $saController = $this->namespace.'\sa'.string::camelCasing(preg_replace('/[^a-zA-Z]/', '', $this->name), true).'Controller';

        $functionName = preg_replace('/[^a-zA-Z]/', '', string::camelCasing($this->name, true));

        $phpCode = '<?php

namespace '.$this->namespace.';

use sacore\application\moduleConfig;
use sacore\application\route;
use sacore\application\saRoute;
use sacore\application\resourceRoute;
use sacore\application\navItem;

abstract class '.$className.'Config extends  moduleConfig {

	static function getRoutes()
	{
		return array(
						
						// -------------- FRONTEND ROUTES ------------------- 


						// -------------- SITEADMIN ROUTES -------------------

						new saRoute(array( \'id\'=>\'sa_'.$this->cleanedName.'\', \'name\'=>\'Manage '.$this->name.'\', \'route\'=>\'/siteadmin/'.$this->cleanedName.'\', \'controller\'=>\''.$saController.'@manage'.$functionName.'\' )),
						new saRoute(array( \'id\'=>\'sa_'.$this->cleanedName.'_create\', \'name\'=>\'Create '.$this->name.'\', \'route\'=>\'/siteadmin/'.$this->cleanedName.'/create\', \'controller\'=>\''.$saController.'@edit'.$functionName.'\' )),
						new saRoute(array( \'id\'=>\'sa_'.$this->cleanedName.'_edit\', \'name\'=>\'Edit '.$this->name.'\', \'route\'=>\'^/siteadmin/'.$this->cleanedName.'/[0-9]{1,}/edit$\', \'controller\'=>\''.$saController.'@edit'.$functionName.'\' )),
						new saRoute(array( \'id\'=>\'sa_'.$this->cleanedName.'_save\', \'name\'=>\'Save '.$this->name.'\', \'method\'=>\'POST\', \'route\'=>\'^/siteadmin/'.$this->cleanedName.'/[0-9]{1,}/edit$\', \'controller\'=>\''.$saController.'@save'.$functionName.'\' )),
						new saRoute(array( \'id\'=>\'sa_'.$this->cleanedName.'_delete\', \'name\'=>\'Delete '.$this->name.'\', \'route\'=>\'^/siteadmin/'.$this->cleanedName.'/[0-9]{1,}/delete$\', \'controller\'=>\''.$saController.'@delete'.$functionName.'\' )),
						
		);	
	}

	static function getNavigation()
	{
		return array(
					   // FRONT END

				   	   // SITEADMIN
					   new navItem(array( \'id\'=>\'sa_'.$this->cleanedName.'\', \'name\'=>\''.$this->name.'\', \'icon\'=>\'fa fa-cube\', \'parent\'=>\'siteadmin_root\'  )),
					   new navItem(array( \'name\'=>\'Manage '.$this->name.'\',  \'routeid\'=>\'sa_'.$this->cleanedName.'\', \'icon\'=>\'fa fa-double-angle-right\', \'parent\'=>\'sa_'.$this->cleanedName.'\'  )),
					   new navItem(array( \'name\'=>\'Create '.$this->name.'\', \'routeid\'=>\'sa_'.$this->cleanedName.'_create\', \'icon\'=>\'fa fa-double-angle-right\', \'parent\'=>\'sa_'.$this->cleanedName.'\'  )),


		);
	}

	static function getDatabase()
	{
		return array(
						\'wormConfig\'=>array(
										\'alternativeNamespaces\'=>array(
																		\''.$this->namespace.'\'
																		),	
									),
						\'tables\'=>array()
					);
	}

}';

        file_put_contents($this->path.'/controller/'.$className.'Config.class.php', $phpCode);
    }

    public function createControllers()
    {
        $this->createController(string::camelCasing($this->name, false).'Controller', false);
        $this->createController('sa'.string::camelCasing($this->name, true).'Controller', true);
    }

    public function createController($controller, $saController = false)
    {
        //$name = string::camelCasing( $controller.$this->name ).'Controller.class.php';

        $controller = preg_replace('/[^a-zA-Z]/', '', $controller);

        $functionName = preg_replace('/[^a-zA-Z]/', '', string::camelCasing($this->name, true));

        $phpCode = '<?php

namespace '.$this->namespace.';

use \sacore\application\app;
use \sacore\application\\'.($saController ? 'saController' : 'controller').';
use \sacore\application\\'.($saController ? 'saRoute' : 'route').';
use \sacore\application\navItem;
use \sacore\application\modelResult;
use \sacore\application\view;
use \sacore\utilities\url;
use \sacore\utilities\notification;

class '.$controller.' extends '.($saController ? 'saController' : 'controller').' {';

        if ($saController && ! empty($this->baseModel)) {
            $baseModel = explode('\\', $this->baseModel);
            $baseModel = end($baseModel);
            $baseModelWDB = $this->baseModel;

            $baseModelColumns = $baseModelWDB::getObjectColumns();
            $baseModelKey = $baseModelWDB::getPrimaryKey();

            $header = '';

            $count = 0;
            foreach ($baseModelColumns as $column => $info) {
                if ($info['autoIncrement']) {
                    continue;
                }

                $name = ucwords(str_replace('_', ' ', $column));
                $header .= "array('name'=>'".$name."', 'class'=>''),";
                $map .= "'".$column."',";

                $count++;
                if ($count >= 5) {
                    break;
                }
            }

            $phpCode .= '
	public function manage'.$functionName.'() {

		$view = new view(\'master\', \'table\', $this->viewLocation(), false );

		$perPage = 20;
		$fieldsToSearch=array();
		foreach($_GET as $field=>$value)
		{
			if (strpos($field, \'q_\')===0 && !empty($value))
			{
				$fieldsToSearch[ str_replace(\'q_\', \'\', $field) ] = $value;
			}
		}
		$currentPage = !empty($_REQUEST[\'page\']) ? $_REQUEST[\'page\'] : 1;
		$sort = !empty($_REQUEST[\'sort\']) ? $_REQUEST[\'sort\'] : false;
		$sortDir = !empty($_REQUEST[\'sortDir\']) ? $_REQUEST[\'sortDir\'] : false;
		$totalRecords = '.$baseModel.'::findByFields($fieldsToSearch, false, false, false, $sort, $sortDir)->length;
		$data = '.$baseModel.'::findByFields($fieldsToSearch, false, $perPage, (($currentPage-1)*$perPage), $sort, $sortDir)->toArray();
		$totalPages = ceil($totalRecords / $perPage);

		$view->data[\'table\'][] = array( 

			/* SET THE HEADER OF THE TABLE UP */
			\'header\'=>array( '.$header.' ),
			/* SET ACTIONS ON EVERY ROW */
			\'actions\'=>array( \'edit\'=>array( \'name\'=>\'Edit\', \'routeid\'=>\'sa_'.$this->cleanedName.'_edit\', \'params\'=>array(\''.$baseModelKey.'\') ), \'delete\'=>array( \'name\'=>\'Delete\', \'routeid\'=>\'sa_'.$this->cleanedName.'_delete\', \'params\'=>array(\''.$baseModelKey.'\') ), ),
			/* SET THE NO DATA MESSAGE */
			\'noDataMessage\'=>\'No '.$this->name.' Available\',
			/* SET THE DATA MAP */
			\'map\'=>array( '.$map.' ),
			/* SET THE ACTION FOR THE HEADER CREATE BUTTON */
			\'tableCreateRoute\'=>\'sa_'.$this->cleanedName.'_create\',
			/* IS THE TABLE SORTABLE? */
			\'sortable\'=> true,
			/* IS THE TABLE SEARCHABLE? */
			\'searchable\'=> true,
			/* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
			\'totalRecords\'=> $totalRecords,
			\'totalPages\'=> $totalPages,
			\'currentPage\'=> $currentPage,
			\'perPage\'=> $perPage,
			/* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
			\'data\'=> $data
		);

		$view->display();
	}

	public function edit'.$functionName.'($id=0, $passData=false) {

		$view = new view(\'master\', \'dbform\',, $this->viewLocation(), false );
		$view->data[\'id\'] = $id;

		if ($id)
		{
			$mData = '.$baseModel.'::find($id)->toArray();
			$view->data = array_merge($view->data, $mData);
		}

		if ($passData)
			$view->data = array_merge($view->data, $passData);

		$view->data[\'dbform\'][] = array( 

			/* SET THE HEADER OF THE TABLE UP */
			\'columns\'=> '.$baseModel.'::getObjectColumns(),
			\'form\'=> true,
			\'saveRouteId\'=> \'sa_'.$this->cleanedName.'_save\',
			\'exclude\'=> array()
		);

		$view->display();
	}

	public function save'.$functionName.'($id=0) {
		$result = '.$baseModel.'::saveValidate($id, $_POST);

		$notify = new notification();
		if ($result->status==modelResult::STATUS_SUCCESS)
		{
			$notify->addNotification(\'success\', \'Success\', \'Record saved successfully.\');
			if ($id)
				url::redirect( url::make(\'sa_'.$this->cleanedName.'\') );
			else
				url::redirect( url::make(\'sa_'.$this->cleanedName.'_edit\', $result->id) );
		}
		else
		{
			$notify->addNotification(\'danger\', \'Error\', \'An error occured while saving your changes. <br />\'. saController::formatMessages( $result->messages ) );
			$this->edit'.$functionName.'($id, $_POST);
		}
	}

	public function delete'.$functionName.'($id=0) {
		$result = '.$baseModel.'::delete'.$baseModel.'($id);

		$notify = new notification();
		if ($result->status==modelResult::STATUS_SUCCESS)
		{
			$notify->addNotification(\'success\', \'Success\', \'Deleted successfully.\');
			url::redirect( url::make(\'sa_'.$this->cleanedName.'\') );
		}
		else
		{
			$notify->addNotification(\'danger\', \'Error\', \'An error occured while trying to delete that record. <br />\'. saController::formatMessages( $result->messages ) );
			url::redirect( url::make(\'sa_'.$this->cleanedName.'\') );
		}
	}

	';
        } elseif ($saController && empty($this->baseModel)) {
            $phpCode .= '
	public function manage'.$functionName.'() {

		$view = new view(\'master\', \'\',, $this->viewLocation(), false );
		$view->display();
	}

	public function edit'.$functionName.'($id=0, $passData=false) {

		$view = new view(\'master\', \'\',, $this->viewLocation(), false );
		$view->display();
	}

	public function save'.$functionName.'($id=0) {
		
		// FIX ME IM WRONG, saMember is a placeholder !!!!
		//$result = saMember::saveValidate($id, $_POST);

		$notify = new notification();
		if ($result->status==modelResult::STATUS_SUCCESS)
		{
			$notify->addNotification(\'success\', \'Success\', \'Record saved successfully.\');
			if ($id)
				url::redirect( url::make(\'sa_'.$this->cleanedName.'\') );
			else
				url::redirect( url::make(\'sa_'.$this->cleanedName.'_edit\', $result->id) );
		}
		else
		{
			$notify->addNotification(\'danger\', \'Error\', \'An error occured while saving your changes. <br />\'. saController::formatMessages( $result->messages ) );
			$this->edit'.$functionName.'($id, $_POST);
		}
	}

	public function delete'.$functionName.'($id=0) {

		// FIX ME IM WRONG, saMember is a placeholder  !!!!
		//$result = saMember::delete'.$baseModel.'($id);

		$notify = new notification();
		if ($result->status==modelResult::STATUS_SUCCESS)
		{
			$notify->addNotification(\'success\', \'Success\', \'Deleted successfully.\');
			url::redirect( url::make(\'sa_'.$this->cleanedName.'\') );
		}
		else
		{
			$notify->addNotification(\'danger\', \'Error\', \'An error occured while trying to delete that record. <br />\'. saController::formatMessages( $result->messages ) );
			url::redirect( url::make(\'sa_'.$this->cleanedName.'\') );
		}
	}

	';
        }

        $phpCode .= '
}';

        file_put_contents($this->path.'/controller/'.$controller.'.class.php', $phpCode);
    }

    public function createModel($model)
    {
        $className = explode('\\', $model);
        $className = end($className);

        $phpCode = '<?php

namespace '.$this->namespace.';

use sacore\application\modelResult;
use sacore\utilities\fieldValidation;

class '.$className.' extends \\'.$model.' {

	public static function saveValidate($id, $data)
	{
		$obj = self::findorCreate($id);
	
		$fv = new fieldValidation();
		//$fv->isNotEmpty($data[\'email\'], \'Please enter an email.\'); ';

        $baseModelWDB = $this->baseModel;
        $baseModelColumns = $baseModelWDB::getObjectColumns();

        foreach ($baseModelColumns as $column => $info) {
            $name = ucwords(str_replace('_', ' ', $column));
            if (! $info['nullable']) {
                $phpCode .= '		$fv->isNotEmpty($data[\''.$column.'\'], \'Please enter an '.$name.'.\');'."\n";
            }
        }

        $phpCode .= '
		if (!$fv->hasErrors())
		{
			$obj->loadDataFromArray($data);
			$obj->save();
			return new modelResult( array( \'status\'=>modelResult::STATUS_SUCCESS, \'id\'=>$obj->id ) );
		}
		else
		{
			return new modelResult( array( \'status\'=>modelResult::STATUS_FAIL, \'messages\'=>$fv->getErrors() ) );
		}
	}

	public static function delete'.$className.'($id)
	{
		$obj = self::find($id);
		if ($obj)
		{
			$obj->delete();

			return new modelResult( array(\'status\'=>modelResult::STATUS_SUCCESS ) );
		}
		else
		{
			
			return new modelResult( array( \'status\'=>modelResult::STATUS_FAIL, \'messages\'=>\'Record doesn\\\'t exist\' ) );
		}
	}

}';

        file_put_contents($this->path.'/classes/'.$className.'.class.php', $phpCode);
    }
}
