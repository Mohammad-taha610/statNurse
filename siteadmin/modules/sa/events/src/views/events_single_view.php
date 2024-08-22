@extends('frontend-master')
<?php
/**
 * @var view        $self                   - The instance of the current view.
 * @var array       $event                  - The current event serialized as an array.
 * @var bool     $is_all_day             - If true, the event lasts all day.
 * @var bool     $is_recurring           - If true, the event is recurring.
 * @var string      $location_address       - The address at which this event will take place.
 * @var array|null  $recurrence             - A recurrence object serialized as array.
 * @var DateTime    $start                  - The start date of the recurrence.
 * @var DateTime    $end                    - The end date of the recurrence.
 * @var string      $reservationForm        - The HTML for the reservation form.
 * @var array       $upcoming_recurrences   - A list of event recurrences serialized as an array collection.
 */

use sacore\application\view;
use sa\events\ViewHelper;

?>

@section('site-container')
@asset::/events/css/style.css

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <h1><?= $event['name'] ?></h1>

            <!-- start/end times & dates -->
            <div class="event-date-time">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="event-time">
                            <h5>
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                
                                <?php if (! $is_all_day) { ?>
                                    <?php if ($start->format('g:ia') != $end->format('g:ia')) { ?>
                                        From <span class="start-time"><?= $start->format('g:ia') ?></span>
                                        to <span class="end-time"><?= $end->format('g:ia') ?></span>
                                    <?php } else { ?>
                                        <span class="start-time"><?= $start->format('g:ia') ?></span>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="all-day">All Day</span>
                                <?php } ?>
                                
                            </h5>
                        </div>
                    </div>
                    <div class="col-sm-6 text-right">
                        <div class="event-date">
                            <h5>
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                <span class="start-date">
                                    <?= $start->format('m/d/Y') ?>
                                </span>

                                <?php
                                if ($start->format('Y-m-d') != $end->format('Y-m-d')) { ?>
                                   <span class="end-date">
                                       - <?= $end->format('m/d/Y') ?>
                                   </span>
                                <?php } ?>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end start/end times & dates -->

            <div class="event-descriptions">
                <!-- event description -->
                <?php if (! empty($event['description'])) { ?>
                    <h2>Description</h2>
                    <?= $event['description'] ?>
                <?php } ?>
                <!-- end description -->

                <!-- recurrence description -->
                <?php if (! empty($recurrence) && ! empty($recurrence['description'])) { ?>
                    <h3>Special Notes</h3>
                    <p><?= $recurrence['description'] ?></p>
                <?php } ?>
                <!-- end recurrence description -->
            </div>

            <!-- upcoming recurrences -->
            <?php if (count($upcoming_recurrences) > 1) { ?>
                <button class="recurring-times-toggle collapsed" type="button"
                        data-toggle="collapse" data-target="#recurring-times">
                    Additional Scheduled Date<?= (count($upcoming_recurrences) > 1) ? 's' : '' ?>
                </button>

                <div class="collapse recurring-times-container" id="recurring-times">
                    <?= ViewHelper::makeEventRecurrencesList($event, $upcoming_recurrences) ?>

                    <?php if (count($upcoming_recurrences) > 20) { ?>
                        <div class="text-center">
                            <button disabled class="btn">More Events Coming Soon</button>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
            <!-- end upcoming recurrences -->

            <!-- location -->
            <?php if (! empty($event['location_name'])) { ?>
                <div class="location">
                    <div class="row">
                        <div class="col-sm-2">
                            <i class="fa fa-map-marker"></i>
                        </div>
                        <div class="col-sm-10">
                            <h4><?= $event['location_name'] ?></h4>
                            <p><?= $location_address ?></p>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <!-- end location -->

            <!-- contact -->
            <?php if (! empty($event['contact_name'])) { ?>
                <div class="organizer">
                    <h3><?= $event['contact_name'] ?></h3>
                    <?php if (! is_null($event['contact_email'])) { ?>
                        <p class="contact-detail">
                            <i class="fa fa-envelope"></i>
                            <a href="mailto:<?= $event['contact_email'] ?>">
                                <?= $event['contact_email'] ?>
                            </a>
                        </p>
                    <?php } ?>

                    <?php if (! is_null($event['contact_phone'])) { ?>
                        <p class="contact-detail">
                            <i class="fa fa-phone"></i>
                            <a href="tel:<?= preg_replace('/[^0-9]/', '', $event['contact_phone']) ?>">
                                <?= $event['contact_phone'] ?>
                            </a>
                        </p>
                    <?php } ?>

                    <?php if (! is_null($event['link'])) { ?>
                        <p class="contact-detail">
                            <i class="fa fa-globe"></i>
                            <a href="<?= $event['link'] ?>" target="_blank">
                                <?= $event['link'] ?>
                            </a>
                        </p>
                    <?php } ?>
                </div>
            <?php } ?>
            <!-- end contact -->

            <!-- reservation form -->
            <?= $reservationForm ?>
            <!-- end reservation form -->

            <div class="text-center">
                <br>
                <a href="@url('events_index')" class="btn btn-default">
                    Back to Calendar
                </a>
            </div>

        </div>
    </div>
</div>
@show