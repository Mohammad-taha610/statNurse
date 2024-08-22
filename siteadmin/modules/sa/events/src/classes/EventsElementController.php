<?php

namespace sa\events;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\utilities\doctrineUtils;

class EventsElementController extends saController
{
    public function eventsElement($data)
    {
        if ($data['request'] == 'available_elements') {
            $eventCategories = app::$entityManager->getRepository(ioc::staticResolve('Category'))->findBy([], ['name' => 'ASC']);
            $categories = [];
            $categories[] = ['text' => 'ALL', 'value' => 'ALL'];
            foreach ($eventCategories as $eventCategory) {
                $categories[] = ['text' => $eventCategory->getName(), 'value' => $eventCategory->getName()];
            }

            $data['result'][] = [
                'action' => 'events',
                'name' => 'Latest Events',
                'options' => [
                    [
                        'type' => 'input',
                        'name' => 'num_of_events',
                        'label' => '# of Displayed Events',
                        'required' => true,
                        'default_value' => '4',
                    ],
                    [
                        'type' => 'select',
                        'name' => 'category',
                        'label' => 'Event Category',
                        'required' => true,
                        'default_value' => 'ALL',
                        'values' => $categories,
                    ],
                ],
            ];
            $data['result'][] = [
                'action' => 'eventsCalendar',
                'name' => 'Events Calendar',
                'options' => [
                    [
                        'type' => 'select',
                        'name' => 'defaultView',
                        'label' => 'Default View',
                        'required' => true,
                        'default_value' => '',
                        'values' => [
                            ['text' => 'List', 'value' => 'list'],
                            ['text' => 'Calendar', 'value' => 'calendar'],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'name' => 'category',
                        'label' => 'Event Category',
                        'required' => true,
                        'default_value' => 'ALL',
                        'values' => $categories,
                    ],
                ],
            ];
        } elseif ($data['request'] == 'html' && $data['settings']['element_selection'] == 'events') {
            $view = new View('events_element');
            $num_of_events = $data['settings']['num_of_events'];
            $view->data['events'] = [];

            if ($data['settings']['category'] == 'ALL') {
                $events = ioc::getRepository('EventRecurrence')->findAllUpcomingRecurrences('ALL', $num_of_events);
            } else {
                $category = app::$entityManager->getRepository(ioc::staticResolve('Category'))->findBy(['name' => $data['settings']['category']]);
                $events = ioc::getRepository('EventRecurrence')->findAllUpcomingRecurrences($category, $num_of_events);
            }
            $results = [];
            foreach ($events as $ev) {
                $tmp = [];
                $tmp['event'] = doctrineUtils::getEntityArray($ev->getEvent());
                $tmp['recurrence'] = doctrineUtils::getEntityArray($ev);
                $results[] = $tmp;
            }

            $view->data['events'] = $results;
            $view->setXSSSanitation(false);
            $data['html'] .= $view->getHTML();
        } elseif ($data['request'] == 'html' && $data['settings']['element_selection'] == 'eventsCalendar') {
            $view = new View('events_index_view');

            $view->data['category'] = $data['settings']['category'];

            $view->setXSSSanitation(false);
            $data['html'] .= $view->getHTML();
        }

        return $data;
    }
}
