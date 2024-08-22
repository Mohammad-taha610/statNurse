<?php

namespace sa\events;

use sacore\application\app;
use sacore\application\modRequest;
use sacore\application\navItem;
use sacore\application\route;
use sacore\application\saRoute;
use sacore\application\staticResourceRoute;

abstract class eventsConfig extends \sacore\application\moduleConfig
{
    public static function init()
    {
        modRequest::listen('events.get_reservation_form', 'EventsController@getReservationForm');
        modRequest::listen('site.elements', 'EventsElementController@eventsElement', 1, 'events_element');

        modRequest::listen('events.fillCalendar', 'EventsApiController@fillCalendar', 1, null, true, false);
    }

//    public static function getRoutes()
//    {
//        return array(
//            /**
//             * Category entity CRUD
//             */
//            //index
//            new saRoute(array(
//                'id' => 'sa_events_categories',
//                'name'       => 'Event Categories',
//                'route'      => '/siteadmin/events/categories',
//                'controller' => 'SaEventsCategoriesController@index'
//            )),
//
//            //create
//            new saRoute(array(
//                'id' => 'sa_events_category_create',
//                'name'       => 'Create Category',
//                'route'      => '/siteadmin/events/category/create',
//                'controller' => 'SaEventsCategoriesController@edit'
//            )),
//
//            //edit
//            new saRoute(array(
//                'id' => 'sa_events_category_edit',
//                'name'       => 'Edit Category',
//                'route'      => '^/siteadmin/events/category/edit/[0-9]{1,}$',
//                'method'     => 'GET',
//                'controller' => 'SaEventsCategoriesController@edit'
//            )),
//
//            //save
//            new saRoute(array(
//                'id'         => 'sa_events_category_save',
//                'name'       => 'Save Event',
//                'method'     => 'POST',
//                'route'      => '^/siteadmin/events/category/save$',
//                'controller' => 'SaEventsCategoriesController@save'
//            )),
//
//            //delete
//            new saRoute(array(
//                'id'         => 'sa_events_category_delete',
//                'name'       => 'Delete events',
//                'route'      => '^/siteadmin/events/category/delete/[0-9]{1,}$',
//                'controller' => 'SaEventsCategoriesController@delete'
//            )),
//
//            /**
//             * Event entity CRUD
//             */
//            //index
//            new saRoute(array(
//                'id'         => 'sa_events_index',
//                'name'       => 'Events',
//                'route'      => '/siteadmin/events',
//                'controller' => 'SaEventsController@index'
//            )),
//
//            //create
//            new saRoute(array(
//                'id'         => 'sa_events_create',
//                'name'       => 'Create Event',
//                'route'      => '/siteadmin/events/create',
//                'controller' => 'SaEventsController@edit'
//            )),
//
//            //edit
//            new saRoute(array(
//                'id'         => 'sa_events_edit',
//                'name'       => 'Edit Event',
//                'route'      => '^/siteadmin/events/edit/[0-9]{1,}$',
//                'method'     => 'GET',
//                'controller' => 'SaEventsController@edit'
//            )),
//
//            //save
//            new saRoute(array(
//                'id'         => 'sa_events_save',
//                'name'       => 'Save Event',
//                'method'     => 'POST',
//                'route'      => '^/siteadmin/events/save$',
//                'controller' => 'SaEventsController@save'
//            )),
//
//            //delete
//            new saRoute(array(
//                'id'         => 'sa_events_delete',
//                'name'       => 'Delete events',
//                'route'      => '^/siteadmin/events/delete/[0-9]{1,}$',
//                'controller' => 'SaEventsController@delete'
//            )),
//
//            /**
//             * Recurrence CRUD
//             */
//            //index
//            new saRoute(array(
//                'id'         => 'sa_event_recurrences',
//                'name'       => 'Event Recurrences List',
//                'route'      => '^/siteadmin/events/[0-9]{1,}/recurrences$',
//                'controller' => 'SaEventRecurrencesController@index'
//            )),
//            //edit
//            new saRoute(array(
//                'id'         => 'sa_event_recurrence_edit',
//                'name'       => 'Update Event Recurrence',
//                'route'      => '^/siteadmin/events/recurrence/[0-9]{1,}$',
//                'method'     => 'get',
//                'controller' => 'SaEventRecurrencesController@edit'
//            )),
//
//            //save
//            new saRoute(array(
//                'id'         => 'sa_event_recurrence_save',
//                'name'       => 'Update Event Recurrence',
//                'route'      => '^/siteadmin/events/recurrence/[0-9]{1,}$',
//                'method'     => 'POST',
//                'controller' => 'SaEventRecurrencesController@save'
//            )),
//
//            //delete
//            new saRoute(array(
//                'id'         => 'sa_event_recurrence_delete',
//                'name'       => 'Delete Event Recurrence',
//                'route'      => '^/siteadmin/events/recurrence/[0-9]{1,}/delete$',
//                'method'     => 'GET',
//                'controller' => 'SaEventRecurrencesController@delete'
//            )),
//
//            /**
//             * Events Front End
//             */
//            new route(array(
//                'id'         => 'events_index',
//                'name'       => 'Events',
//                'route'      => '^/events$',
//                'controller' => 'EventsController@index'
//            )),
//            new route(array(
//                'id'         => 'events_single',
//                'name'       => 'Event',
//                'route'      => '^/events/[0-9]{1,}$',
//                'controller' => 'EventsController@single'
//            )),
//            new route(array(
//                'id'         => 'event_single_recurrence',
//                'name'       => 'Event Recurrence',
//                'route'      => '^/events/[0-9]{1,}/recurrence/[0-9]{1,}$',
//                'controller' => 'EventsController@singleRecurrence'
//            )),
//
//            /**
//             * Events API
//             */
//            new route(array(
//                'id'         => 'events_api_by_day',
//                'name'       => 'Events on Day',
//                'route'      => '^/events/api/day/[0-9]{10,}$',
//                'controller' => 'EventsApiController@findByDay'
//            )),
//            new route(array(
//                'id'         => 'events_api_by_month',
//                'name'       => 'Events on Month',
//                'route'      => '^/events/api/month/[0-9]{10,}$',
//                'controller' => 'EventsApiController@findByMonth'
//            )),
//            new route(array(
//                'id'         => 'events_api_by_year',
//                'name'       => 'Events on Year',
//                'route'      => '^/events/api/year/[0-9]{10,}$',
//                'controller' => 'EventsApiController@findByYear'
//            )),
//            new route(array(
//                'id'         => 'events_api_between_dates',
//                'name'       => 'Events Between Dates',
//                'route'      => '^/events/api/between/[0-9]{10,}/[0-9]{10,}$',
//                'controller' => 'EventsApiController@findBetweenDates'
//            )),
//            new route(array(
//                'id'         => 'events_api_upcoming_events',
//                'name'       => 'Events Upcoming Dates',
//                'route'      => '^/events/api/next/[0-9]{1,}$',
//                'controller' => 'EventsApiController@findUpcoming'
//            )),
//            new route(array(
//                'id'         => 'events_api_upcoming_after_date',
//                'name'       => 'Events Upcoming After Date',
//                'route'      => '^/events/api/after/[0-9]{10,}/[0-9]{1,}$',
//                'controller' => 'EventsApiController@findUpcomingAfterDate'
//            )),
//
//            /**
//             * Reservations API
//             */
//            new route(array(
//                'id'         => 'events_api_create_reservation',
//                'name'       => 'Reserve Event',
//                'route'      => '^/events/api/reservation/create/[0-9]{1,}$',
//                'controller' => 'ReservationsApiController@reserve'
//            )),
//            new route(array(
//                'id'         => 'events_api_cancel_reservation',
//                'name'       => 'Cancel Reservation',
//                'route'      => '^/events/api/reservation/cancel',
//                'controller' => 'ReservationsApiController@cancel'
//            )),
//
//
//            /**
//             * Resources
//             */
//            /** CSS */
//            new staticResourceRoute(array(
//                'id'         => 'events_css',
//                'name'       => 'css',
//                'route'      => '^/events/css/[a-zA-Z0-9-_\.]{1,}$',
//                'controller' => 'EventsAssetsController@css'
//            )),
//
//            /** JS */
//            new staticResourceRoute(array(
//                'id'         => 'events_js',
//                'name'       => 'js',
//                'route'      => '^/events/js/[a-zA-Z0-9-_\.]{1,}$',
//                'controller' => 'EventsAssetsController@js'
//            )),
//
//
//            new route(array(
//                'id'         => 'events_category_view',
//                'name'       => 'Event Category View',
//                'route'      => '^/events/category/[0-9]{1,}/id$',
//                'controller' => 'EventsController@viewCategoryById'
//            )),
//        );
//    }

    public static function initRoutes($routes)
    {
        $moduleName = app::get()->getConfiguration()->get('events_module_name')->getValue();
        $routeId = app::get()->getConfiguration()->get('events_module_route_id')->getValue();

        //Category Entity CRUD
        $routes->addWithOptionsAndName('Event Categories', 'sa_events_categories', '/siteadmin/'.$routeId.'/categories')->controller('SaEventsCategoriesController@index');
        $routes->addWithOptionsAndName('Create Category', 'sa_events_category_create', '/siteadmin/'.$routeId.'/category/create')->controller('SaEventsCategoriesController@edit');
        $routes->addWithOptionsAndName('Edit Category', 'sa_events_category_edit', '/siteadmin/'.$routeId.'/category/{id}/edit')->controller('SaEventsCategoriesController@edit');
        $routes->addWithOptionsAndName('Save Event', 'sa_events_category_save', '/siteadmin/'.$routeId.'/category/save')->controller('SaEventsCategoriesController@save')->methods(['POST']);
        $routes->addWithOptionsAndName('Delete events', 'sa_events_category_delete', '/siteadmin/'.$routeId.'/category/{id}/delete')->controller('SaEventsCategoriesController@delete')->methods(['POST']);

        //Event Entity CRUD
        $routes->addWithOptionsAndName('Events', 'sa_events_index', '/siteadmin/'.$routeId)->controller('SaEventsController@index');
        $routes->addWithOptionsAndName('Create Events', 'sa_events_create', '/siteadmin/'.$routeId.'/create')->controller('SaEventsController@edit');
        $routes->addWithOptionsAndName('Edit Events', 'sa_events_edit', '/siteadmin/'.$routeId.'/{id}/edit')->controller('SaEventsController@edit');
        $routes->addWithOptionsAndName('Save Events', 'sa_events_save', '/siteadmin/'.$routeId.'/save')->controller('SaEventsController@save')->methods(['POST']);
        $routes->addWithOptionsAndName('Delete Events', 'sa_events_delete', '/siteadmin/'.$routeId.'/{id}/delete')->controller('SaEventsController@delete');

        //Recurrence Entity CRUD
        $routes->addWithOptionsAndName('Event Recurrences List', 'sa_event_recurrences', '/siteadmin/'.$routeId.'/{id}/recurrences')->controller('SaEventRecurrencesController@index');
        $routes->addWithOptionsAndName('Update Event Recurrence', 'sa_event_recurrence_edit', '/siteadmin/'.$routeId.'/{eventId}/recurrence/{recurrenceId}/edit/{recurrenceUniqueId}')->controller('SaEventRecurrencesController@edit');
        $routes->addWithOptionsAndName('Update Event Recurrence', 'sa_event_recurrence_save', '/siteadmin/'.$routeId.'/{eventId}/recurrence/{recurrenceId}/save/{recurrenceUniqueId}')->controller('SaEventRecurrencesController@save')->methods(['POST']);
        $routes->addWithOptionsAndName('Delete Event Recurrence', 'sa_event_recurrence_delete', '/siteadmin/'.$routeId.'/{eventId}/recurrence/{recurrenceId}/delete')->controller('SaEventRecurrencesController@delete');

        //Events Front End
        $routes->addWithOptionsAndName('Events', 'events_index', '/'.$routeId)->controller('EventsController@index');
        $routes->addWithOptionsAndName('Events - POST', 'events_index_post', '/'.$routeId)->controller('EventsController@index')->methods(['POST']);
        $routes->addWithOptionsAndName('Events', 'events_single', '/'.$routeId.'/{id}')->controller('EventsController@single');
        $routes->addWithOptionsAndName('Event Recurrence', 'events_single_resources', '/'.$routeId.'/{id}/recurrence/{recurrenceId}/{recurrenceUniqueId}')->controller('EventsController@singleRecurrence');

        //Events API
        $routes->addWithOptionsAndName('Event on Day', 'events_api_by_day', '/'.$routeId.'/api/day/{day}')->controller('EventsApiController@findByDay')->methods(['POST']);
        $routes->addWithOptionsAndName('Event on Month', 'events_api_by_month', '/'.$routeId.'/api/month/{month}')->controller('EventsApiController@findByMonth')->methods(['POST']);
        $routes->addWithOptionsAndName('Event on Year', 'events_api_by_year', '/'.$routeId.'/api/year/{year}')->controller('EventsApiController@findByYear')->methods(['POST']);
        $routes->addWithOptionsAndName('Event Between Dates', 'events_api_between_date', '/'.$routeId.'/api/between/{dayOne}/{dayTwo}')->controller('EventsApiController@findBetweenDates')->methods(['POST']);
        $routes->addWithOptionsAndName('Events Upcoming Dates', 'events_api_upcoming_events', '/'.$routeId.'/api/next/{date}')->controller('EventsApiController@findUpcoming');
        $routes->addWithOptionsAndName('Events Upcoming After Dates', 'events_api_upcoming_after_date', '/'.$routeId.'/api/after/{date}')->controller('EventsApiController@findUpcomingAfterDate');

        //Reservations API
        $routes->addWithOptionsAndName('Reserve Event Recurrence', 'events_api_create_reservation_for_recurrence', '/'.$routeId.'/{id}/api/reservation/create/{recurrenceId}/{recurrenceUniqueId}')->controller('ReservationsApiController@reserveRecurrence')->methods(['POST']);
        $routes->addWithOptionsAndName('Cancel Reservation Recurrence', 'events_api_cancel_reservation_for_recurrence', '/'.$routeId.'/{id}/api/reservation/cancel/{recurrenceId}')->controller('ReservationsApiController@cancelRecurrence');

        $routes->addWithOptionsAndName('Reserve Event', 'events_api_create_reservation', '/'.$routeId.'/{id}/api/reservation/create')->controller('ReservationsApiController@reserve')->methods(['POST']);
        $routes->addWithOptionsAndName('Cancel Reservation', 'events_api_cancel_reservation', '/'.$routeId.'/{id}/api/reservation/cancel')->controller('ReservationsApiController@cancel');

        $routes->addWithOptionsAndName('css', 'events_css', '/'.$routeId.'/css/{file}')->controller('EventsAssetsController@css');
        $routes->addWithOptionsAndName('js', 'events_js', '/'.$routeId.'/js/{file}')->controller('EventsAssetsController@js');

        $routes->addWithOptionsAndName('Event Category View', 'events_category_view', '/'.$routeId.'/category/{id}/id')->controller('EventsController@viewCategoryById');
    }

    public static function getNavigation()
    {
        $moduleName = app::get()->getConfiguration()->get('events_module_name')->getValue();
        $moduleNameSingular = app::get()->getConfiguration()->get('events_module_name_singular')->getValue();
        $routeId = app::get()->getConfiguration()->get('events_module_route_id')->getValue();

        return [
            new navItem([
                'id' => 'saEvents',
                'name' => $moduleName,
                'routeid' => 'sa_Events',
                'icon' => 'fa fa-calendar',
                'parent' => 'siteadmin_root',
            ]),

            /**
             * Category entity CRUD links
             */
            new navItem([
                'id' => 'eventsCategoryCreate',
                'name' => 'Create Category',
                'routeid' => 'sa_events_category_create',
                'parent' => 'saEvents',
            ]),
            new navItem([
                'id' => 'eventsCategoriesIndex',
                'name' => 'Manage Categories',
                'routeid' => 'sa_events_categories',
                'parent' => 'saEvents',
            ]),

            /**
             * Event entity CRUD links
             */
            new navItem([
                'id' => 'eventCreate',
                'name' => 'Create '.$moduleNameSingular,
                'routeid' => 'sa_events_create',
                'parent' => 'saEvents',
            ]),
            new navItem([
                'id' => 'eventsIndex',
                'name' => 'Manage '.$moduleName,
                'routeid' => 'sa_events_index',
                'parent' => 'saEvents',
            ]),
        ];
    }

    public static function getSettings()
    {
        return [
            'events_module_name' => ['type' => 'text', 'module' => 'Events', 'default' => 'Events'],
            'events_module_name_singular' => ['type' => 'text', 'module' => 'Events', 'default' => 'Event'],
            'events_module_route_id' => ['type' => 'text', 'module' => 'Events', 'default' => 'events'],

        ];
    }
}
