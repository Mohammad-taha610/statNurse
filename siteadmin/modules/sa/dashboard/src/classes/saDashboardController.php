<?php

namespace sa\dashboard;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\View;
use sacore\application\saController;

class saDashboardController extends saController
{
    public static $registered_widgets = [];

    public static function addDashboardWidget($data)
    {
        static::$registered_widgets[] = ['id' => $data['id'], 'name' => $data['name']];
        modRequest::listen($data['id'], $data['action'], 1, null, true, true);

        return $data;
    }

    public function dashboard()
    {
        $auth = ioc::staticGet('saAuth');
        /** @var \sa\system\saUser $saUser */
        $auth = $auth::getInstance();
        $saUser = $auth->getAuthUser();

        $settings = $saUser->getUserDisplaySettings('dashboard_widgets');

        $view = new View('saDashboard');
        $view->data['available_widgets'] = ! is_array(static::$registered_widgets) ? [] : static::$registered_widgets;
        $view->data['settings'] = ! is_array($settings) ? [] : $settings;
        $view->setXSSSanitation(false);

        return $view;
    }

    public function saveSettings($data)
    {
        $auth = ioc::staticGet('saAuth');
        /** @var \sa\system\saUser $saUser */
        $auth = $auth::getInstance();

        $saUser = $auth->getAuthUser();

        $saUser->setUserDisplaySettings('dashboard_widgets', $data);
        app::$entityManager->flush($saUser);
    }
}
