<?php
namespace sa\system;

use sa\api\api;
use \sacore\application\app;
use sacore\application\ioc;
use sacore\application\Request;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use \sacore\application\saController;
use sa\sa3ApiClient\ApiClientException;
use sa\sa3ApiClient\Sa3ApiClient;
use \sacore\utilities\url;
use \sacore\utilities\notification;


class saSettingsController extends saController {

	public function viewSettings()
	{
    
        if ( app::get()->getConfiguration()->get('disable_settings')->getValue() ) {

            $notify = new notification();
            $notify->addNotification('warning', 'Not Available', 'Settings have been disabled');

            $view = new View( 'blank');
            return $view;
        }

         $modules = app::get()->getModules();

		$settings = app::get()->getConfiguration()->getAllSettings();
        

		$module_settings = array();
		foreach($modules as $mod) {
			$fqn = $mod['namespace'].'\\'.$mod['module'].'Config';

			if (!class_exists($fqn)) {
			    continue;
            }

			$modsettings = $fqn::getSettings();

			foreach($modsettings as $k=>$v) {
				$modsettings[$k]['value'] = isset($settings[ $k ]) ? $settings[ $k ]->getValue() : '';

				if( array_key_exists($k, $settings )) {
					$settings[$k]->claimed = true;
				}

				$module_location = !empty($modsettings[$k]['tab']) ? $modsettings[$k]['tab'] : $mod['module'];

				$module_settings[ $module_location ][$k] = $modsettings[$k];
			}

		}

		// Place all unclaimed settings in System tab
		foreach($settings as $k => $v) {
			if ($v->claimed)
				continue;

			$temp_array = array( 'value' => $v->getValue() );
			$module_settings['system'][$k] = $temp_array;
		}


        $hidden_settings = array(
            'hasBeenSetup',
            'cacheApp',
            'sa_key',
            'uploadsDir',
            'tempDir',
            'public_directory',
            'php_path_executable',
            'db_name',
            'db_password',
            'db_username',
            'db_path',
            'db_driver',
            'db_server',
            'saTheme',
            'db_name_secondary',
            'db_password_secondary',
            'db_username_secondary',
            'db_path_secondary',
            'db_driver_secondary',
            'db_server_secondary',
            'wkhtmltopdf_exe',
        );
        

		$view = new View( 'settings');
		$view->data['settings'] = $module_settings;

		$view->data['hidden'] = $hidden_settings;

		return $view;
	}

    /**
     * @param Request $request
     * @return Redirect
     */
	public function saveSettings($request)
	{
//        echo '<pre>' . var_export($request->request->all(), true) . '</pre>';
//	    die();
	    $new_settings = $request->request;
		if($new_settings === null) {
            $new_settings = $_POST;
        }

        $configuration = app::get()->getConfiguration();

        $no_sync = $configuration->get('settings_no_sync')->getValue();
        $no_sync = explode(',', $no_sync);

		$changed = array();

		foreach($new_settings as $module => $module_vars)
		{
			foreach($module_vars as $key=>$value) {
                $setting = $configuration->get($key);

                if ($setting->getType()=='array') {
                    $value = explode(';', $value);
                }

                if($setting->getValue() != $value) {
                    if(!in_array($key, $no_sync)) {
                        $changed[ $key ] = $value;
                    }

                    $setting->setValue($value);
                }
			}
		}

        $configuration->persist();

		unset($_SESSION['appTheme']);
        
		$notify = new notification();
		$notify->addNotification('success', 'Success', 'Settings saved successfully');

		$route = app::get()->activeRoute;
		if ($route->id=='sa_settings_modal_post')
		    $redirect = new Redirect( app::get()->getRouter()->generate('sa_settings_modal') );
		else
            $redirect = new Redirect( app::get()->getRouter()->generate('sa_settings') );

        app::get()->getLogger()->addInfo('Setting have been updated.');



        /**
         * Replicate to nodes
         */
		if (count($changed)>0) {
            $nodes = ioc::getRepository('saClusterNode')->findAll();
            /** @var saClusterNode $node */
            foreach ($nodes as $node) {

				app::get()->getLogger()->addInfo('Replicating settings to Node: '.$node->getName());

                try {
                    $client = new Sa3ApiClient($node->getSaApiUrl(), $node->getClientId(), $node->getApiKey());
                    if (!$client->isConnected()) {
                        $notify->addNotification("warning", "Notice", "Unable to connect to the cluster node " . $node->getName() . ". Settings on that node may not be updated.");
                        app::get()->getLogger()->addWarning('Unable to connect to the cluster node: '.$node->getName());
                        continue;
                    }


                    $result = $client->custom->sanode->updateSettings(['environment'=>$node->getEnvironment(), 'changes'=>$changed]);
                    if ($result['response']['error']) {
                        $notify->resetNotifications();
                        app::get()->getLogger()->addWarning('Cluster node: '.$node->getName().' reported an error syncing settings.');
                        $notify->addNotification('danger', 'Replication Error', ' The node '.$node->getSaApiUrl().' reported an error syncing the settings.');
                    }
                }
                catch(ApiClientException $e) {
                    $notify->resetNotifications();
                    app::get()->getLogger()->addWarning('Cluster node: '.$node->getName().' is unavailable.');
                    $notify->addNotification('danger', 'Replication Error', ' The node '.$node->getSaApiUrl().' is not available.');
                }

            }
        }
        

		return $redirect;
	}

}