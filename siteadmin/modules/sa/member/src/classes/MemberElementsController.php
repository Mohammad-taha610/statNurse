<?php
namespace sa\member;

use \sacore\application\app;
use sacore\application\controller;
use sacore\application\ioc;
use \sacore\application\modelResult;
use \sacore\application\navItem;
use \sacore\application\route;
use \sacore\application\saController;
use \sacore\application\view;
use sacore\utilities\doctrineUtils;
use \sacore\utilities\url;
use \sacore\utilities\notification;
use sacore\application\modRequest;

class MemberElementsController extends controller
{

    function elements($data)
    {

        if ($data['request'] == 'available_elements') {

            $page_array = array();
            $pages = ioc::getRepository('Page')->findBy(array('is_active' => true), array('name' => 'ASC'));
            
            foreach ($pages as $page) {
                $page_array[] = array(
                    'value' => $page->getName(),
                    'text' => $page->getName()
                );
            }

            $data['result'][] = array(
                'action' => 'member_login',
                'name' => 'Member Login',
                'options' => array(
                    array(
						'type'=>'select-entity',
						'entity'=>'Page',
						'name'=>'page_id',
						'label'=>'Login Page Redirect',
						'required'=>false,
						'default_value'=>'Dashboard',
						'values'=>$page_array
                    ),
                    array(
						'type' => 'text',
						'name' => 'custom_redirect_url',
						'label' => 'Custom Redirect URL',
						'required' => false,
						'default_value' => ''
					)
                )
            );
        }
        elseif ($data['request']=='html' && $data['settings']['element_selection']=='member_login' ) {

            $page = ioc::get('Page', array('name' => $data['settings']['page_id']['id']));

            // Set redirect if given
            if($page) {
                $page_route = $page->getRoute(true);
            }
            $custom_redirect_url = $data['settings']['custom_redirect_url'];
            $_SESSION['login_redirect'] = $custom_redirect_url ? $custom_redirect_url : $page_route;

            $view = new view(null, 'element_login', self::viewLocation() );
            $view->setXSSSanitation(false);

            // check if member is logged in already
            $view->data['is_logged_in'] = modRequest::request('auth.member') ? true : false;

            $data['html'] .= $view->getHTML();


        }
        return $data;
    }
}