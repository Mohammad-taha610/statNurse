<?php

namespace sa\events;

use sacore\application\app;

class ViewHelper
{
    public static function makeEventRecurrencesList(array $event, array $recurrences, $isAllDay = false)
    {
        $html = '';
        $x = 0;

        /** @var EventRecurrence $recurrence */
        foreach ($recurrences as $recurrence) {
            if (! $isAllDay) {
                if ($recurrence['start']->format('g:i a') != $recurrence['end']->format('g:i a')) {
                    $time = $recurrence['start']->format('g:i a').' - '.$recurrence['end']->format('g:i a');
                } else {
                    $time = $recurrence['start']->format('g:i a');
                }
            } else {
                $time = 'All Day';
            }

            if ($x % 2 == 0) {
                if ($x != 0) {
                    $html .= '</div>';
                }
                $html .= '<div class="row">';
            }

            $html .= '
                <div class="col-md-6">
                    <div class="single-date">
                        <a href="'.app::get()->getRouter()->generate('event_single_recurrence', ['id' => $event['id'], 'recurrenceId' => $recurrence['id']]).'" 
                            data-date="'.$recurrence['start']->getTimestamp().'" 
                            class="active">
                            <div class="date">
                                <span class="day">'.$recurrence['start']->format('d').'</span>
                                <span class="month">'.$recurrence['start']->format('M').'</span>
                            </div>

                            <div class="time">
                                <span>'.$time.'</span>
                            </div>
                        </a>
                    </div>
                </div>
            ';

            if ($x == count($recurrences) - 1) {
                $html .= '</div>';
                break;
            }

            $x++;
        }

        return $html;
    }
}
