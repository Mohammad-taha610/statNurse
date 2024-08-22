@extends('master')
<?php

/**

 * @var array $categories

 * @var \nst\events\shift $event

 * @var array   $months

 * @var array   $week_days

 * @var array   $states

 * @var array   $timezones

 * @var string  $defaultTimezone

 */

use sa\events\Event;

use sacore\application\app;



$isAllDay = ($event->getId() && $event->isAllDay() || $_POST['is-all-day-event'] == 1);

$isRecurring = ($event->isRecurring() || $_POST['is-recurring'] == 1);

?>


@asset::/events/css/bootstrap-datetimepicker.min.css
@asset::/events/css/slider.css
@asset::/events/css/sa_style.css

@asset::/events/js/moment.js
@asset::/events/js/bootstrap-datetimepicker.min.js
@asset::/events/js/bootstrap-slider.js
@asset::/events/js/rrule.js
@asset::/events/js/nlp.js
@asset::/events/js/sa_script.js
@section('site-container')

<div class="row">

    <div class="col-xs-12">

        <div class="tabbable">

            <form id="event_editor_form" action="@url('sa_events_save')"

                  method="POST" role="form" enctype="multipart/form-data">

                <!-- Tabs -->
                <ul class="nav nav-tabs padding-16">

                    <!-- General -->
                    <li id="edit-gallery-li" class="active">
                        <a data-toggle="tab" href="#general-information">
                            <i class="green fa fa-edit bigger-125"></i> General
                        </a>
                    </li>

                    <!-- Date/Time -->
                    <li id="edit-gallery-li">
                        <a data-toggle="tab" href="#date-time-pane">
                            <i class="fa fa-clock-o" aria-hidden="true"></i> Date/Time
                        </a>
                    </li>

                    <!-- Pending Shifts -->
                    <li id="edit-gallery-li">
                        <a data-toggle="tab" href="#pending-shifts-pane">
                            <i class="fa fa-user-clock" aria-hidden="true"></i> Pending Shifts
                        </a>
                    </li>


                    <!-- Location -->
                    <li id="edit-gallery-li">
                        <a data-toggle="tab" href="#location-pane">
                            <i class="fa fa-map-marker" aria-hidden="true"></i> Location
                        </a>
                    </li>


                    <!-- Contact -->
                    <li id="edit-gallery-li">
                        <a data-toggle="tab" href="#contact-pane">
                            <i class="fa fa-phone" aria-hidden="true"></i> Contact
                        </a>
                    </li>
                </ul>



                <!-- Tabs content containers -->
                <div class="tab-content">

                    <!-- general -->
                    <div id="general-information" class="tab-pane active">
                        <div class="form-group">
                            <h4 class="header blue bolder smaller">General</h4>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-12 col-xs-12">

                                <!-- name -->
                                <div class="form-group">

                                    <label for="name">* Name</label>

                                    <input type="text" name="name" title="Name"
                                           value="<?= $event->getName() ?>" class="form-control">

                                </div>


                                <!-- category -->
                                <div class="form-group">
                                    <label for="category">* Category</label>
                                    <select name="category_id" id="category" class="form-control">
                                        <option value="">Select a category</option>
                                        <?php

                                        for ($x = 0; $x < count($categories); $x++) {

                                            $isCurrent = ($categories[$x] == $event->getCategory());

                                            ?>

                                            <option value="<?= $categories[$x]->getId() ?>"

                                                <?= ($isCurrent) ? 'selected' : '' ?>>

                                                <?= $categories[$x]->getName() ?>

                                            </option>

                                        <?php } ?>

                                    </select>

                                </div>

                                <!-- provider -->
                                <div class="form-group">
                                    <label>* Provider</label>
                                    <select name="provider_id" id="product" class="form-control">
                                        <option value="">Select a provider</option>
                                        <?php

                                        for ($x = 0; $x < count($providers); $x++) {

                                            $isCurrent = ($providers[$x] == $event->getProvider());

                                            ?>

                                            <option value="<?= $providers[$x]->getId() ?>"

                                                <?= ($isCurrent) ? 'selected' : '' ?>>

                                                <?= $providers[$x]->getMember()->getCompany() ?>

                                            </option>

                                        <?php } ?>

                                    </select>
                                </div>

                                <!-- url -->
                                <div class="form-group">
                                    <label>Link</label>
                                    <input type="text" name="link" value="<?= $event->getLink() ?>"
                                           title="Link" class="form-control">
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-8">
                                <!-- description -->
                                <div class="form-group">

                                    <label>Description</label>

                                    <textarea name="description" id="description" class="form-control"
                                              title="Description"><?= $event->getDescription() ?></textarea>

                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- date/time -->
                    <div id="date-time-pane" class="tab-pane">
                        <div class="form-group">
                            <h4 class="header blue bolder smaller">Date & Time</h4>
                        </div>
                        <br>



                        <!-- address information form -->
                        <div class="row">
                            <div class="col-md-8 col-sm-12 col-xs-12">
                                <h4>Which dates will your event occur between?</h4>



                                <!-- start date/time -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- start date -->
                                        <div class="date-picker" data-content="date-picker">
                                            <label>* Start Date</label>
                                            <div class="input-group">
                                                <input type="text" name="start_date"
                                                       class="form-control" title="Start Date"
                                                       value="<?= (!is_null($event->getStart()))
                                                           ? $event->getStart()->format('m/d/Y') : "" ?>">

                                                <span class="input-group-addon">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <!-- end date -->
                                        <div class="date-picker" data-content="date-picker">
                                            <label>* End Date</label>
                                            <div class="input-group">
                                                <input type="text" name="end_date" class="form-control" title="End Date"
                                                       value="<?= (!is_null($event->getEnd()))
                                                           ? $event->getEnd()->format('m/d/Y') : "" ?>">
                                                <span class="input-group-addon">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <br>



                                <h4>For each day of your event, what time will it start and end?</h4>



                                <ul class="input-menu">

                                    <li>

                                        <!-- all day checkbox -->
                                        <label class="icon-input">
                                            <input type="checkbox" name="is-all-day-event" value="1"
                                                <?= ($isAllDay) ? 'checked' : '' ?> />

                                            <span><i class="fa fa-check" aria-hidden="true"></i></span>
                                            All Day Event
                                        </label>

                                    </li>

                                </ul>


                                <div id="time-container" class="time-container <?= ($isAllDay) ? 'time-hidden' : '' ?>">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <!-- start time -->
                                            <?php
                                            $startTimeStr = '';
                                            if(!is_null($event->getStartTime())) {

                                                $timezone = null;

                                                $startTime = $event->getStart();
                                                $startTimeStr = $startTime->format('H:i A');
                                            }
                                            ?>

                                            <div class='time-picker' data-content="time-picker">

                                                <label>Start Time</label>

                                                <div class="input-group">

                                                    <input type='text' name="start_time"
                                                           class="form-control timepicker" title="Start Time"
                                                           value="<?= $startTimeStr ?>" />

                                                    <span class="input-group-addon">
                                                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                </span>

                                                </div>

                                            </div>



                                        </div>

                                        <div class="col-md-4">

                                            <!-- end time -->
                                            <?php
                                            $endTimeStr = '';
                                            if(!is_null($event->getEndTime())) {
                                                $endTime = $event->getEnd();
                                                $endTimeStr = $endTime->format('H:i A');
                                            }
                                            ?>

                                            <div class='time-picker' data-content="time-picker">
                                                <label>End Time</label>

                                                <div class="input-group">

                                                    <input type='text' name="end_time" class="form-control timepicker" title="End Time"
                                                           value="<?= $endTimeStr ?>" />

                                                    <span class="input-group-addon">
                                                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                </span>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="col-md-4">

                                            <!-- timezone -->
                                            <div class="form-group">

                                                <label>Timezone</label>

                                                <select name="timezone" id="timezone" title="Timezone" class="form-control">
                                                    <?php foreach($timezones as $timezone) { ?>
                                                        <option value="<?= $timezone ?>"
                                                            <?= ($timezone == $defaultTimezone) ? 'selected' : '' ?>>

                                                            <?= str_replace('_', ' ', $timezone) ?>

                                                        </option>

                                                    <?php } ?>

                                                </select>

                                            </div>

                                        </div>

                                    </div>

                                </div>



                                <br>



                                <!-- recurring events -->

                                <div class="form-group">

                                    <h5>This event repeats:

                                        <label class="block-input block-primary">

                                            <input type="radio" name="is-recurring" value="0"

                                                <?= (!$isRecurring) ? 'checked' : '' ?> />

                                            <span>No</span>

                                        </label>



                                        <label class="block-input block-primary">

                                            <input type="radio" name="is-recurring" value="1"

                                                <?= ($isRecurring) ? 'checked' : '' ?> />

                                            <span>Yes</span>

                                            <span class="label-anchor">Edit Repeat Rules</span>

                                        </label>

                                    </h5>



                                    <!--<h5>

                                        Description: <span class="text-success rrule-description">Every two days</span>

                                    </h5>



                                    <div class="recurrence-events-preview">

                                        <table class="table">

                                            <thead>

                                                <tr>

                                                    <th>Month</th>

                                                    <th>Day</th>

                                                    <th>Year</th>

                                                </tr>

                                            </thead>

                                            <tbody>



                                            </tbody>

                                        </table>

                                    </div>

                                    -->

                                </div>



                                <input type="hidden" name="frequency"

                                       value="<?= ($event->getFrequency()) ? $event->getFrequency() : 'DAILY' ?>">

                                <input type="hidden" name="interval"

                                       value="<?= ($event->getInterval()) ? $event->getInterval() : 1 ?>">

                                <input type="hidden" name="recurrence_rules" value="<?= $event->getRecurrenceRules() ?>"/>

                            </div>

                        </div>

                        <!-- end date/time form -->

                    </div>

                    <div id="pending-shifts-pane" class="tab-pane">
                        <div class="form-group">
                            <h4 class="header blue bolder smaller">Pending Shifts</h4>
                        </div>
                        @view::table
                    </div>



                    <!-- location information -->
                    <div id="location-pane" class="tab-pane">

                        <div class="form-group">

                            <h4 class="header blue bolder smaller">Location</h4>

                            <p>If you enter a location name or street, all fields are required.</p>

                        </div>



                        <div class="row">

                            <div class="col-md-6 col-sm-12 col-xs-12">

                                <!-- location name -->
                                <div class="form-group">

                                    <label>Name</label>

                                    <input type="text" name="location_name" title="Location Name"
                                           value="<?= $event->getLocationName() ?>" class="form-control">

                                </div>



                                <!-- street_one -->

                                <div class="form-group">

                                    <label for="street_one">Street</label>

                                    <input type="text" name="street_one" title="Street One"
                                           value="<?= $event->getStreetOne() ?>" class="form-control">

                                </div>



                                <!-- street_two -->

                                <div class="form-group">

                                    <label>Street Two</label>

                                    <input type="text" name="street_two" title="Street Two"
                                           value="<?= $event->getStreetTwo() ?>" class="form-control">

                                </div>



                                <!-- city -->

                                <div class="form-group">

                                    <label>City</label>

                                    <input type="text" name="city" title="City"
                                           value="<?= $event->getCity() ?>" class="form-control">

                                </div>





                                <div class="row">

                                    <div class="col-md-6">

                                        <!-- postal_code -->

                                        <div class="form-group">

                                            <label for="postal_code">Postal Code</label>

                                            <input type="text" name="postal_code" title="Postal Code"
                                                   value="<?= $event->getPostalCode() ?>" class="form-control">

                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <!-- state -->

                                        <div class="form-group">

                                            <label for="state">State</label>

                                            <select name="state" class="form-control" title="States">

                                                <option value="">Select a state</option>

                                                <?php

                                                /** @var \sa\system\saState $state */

                                                foreach($states as $state) {

                                                    $isSelected = (!empty($event->getState())

                                                        && $event->getState() == $state->getAbbreviation())

                                                        ? 'selected'

                                                        : '';

                                                    ?>

                                                    <option value="<?= $state->getAbbreviation() ?>"

                                                        <?= ($isSelected) ? 'selected' : '' ?>>

                                                        <?= $state->getName() ?>

                                                    </option>

                                                <?php } ?>

                                            </select>

                                        </div>

                                    </div>

                                </div>





                            </div>

                        </div>

                    </div>





                    <!-- contact information -->

                    <div id="contact-pane" class="tab-pane">

                        <div class="row">

                            <div class="col-md-6 col-sm-12 col-xs-12">

                                <div class="form-group">

                                    <h4 class="header blue bolder smaller">Contact</h4>

                                    <p>If you enter a name, you <strong>must</strong> provide an email or phone number.</p>

                                </div>



                                <!-- contact name -->

                                <div class="form-group">

                                    <label>Name</label>

                                    <input type="text" name="contact_name" title="Contact Name"
                                           class="form-control" value="<?= $event->getContactName() ?>">

                                </div>



                                <!-- contact name -->

                                <div class="form-group">

                                    <label>Phone</label>

                                    <input type="text" name="contact_phone" title="Contact Phone"
                                           class="form-control" value="<?= $event->getContactPhone() ?>">

                                </div>



                                <!-- contact name -->

                                <div class="form-group">

                                    <label>Email</label>

                                    <input type="text" name="contact_email" title="Contact Email"
                                           class="form-control"  value="<?= $event->getContactEmail() ?>">

                                </div>

                            </div>



                        </div>

                    </div>



                </div>

                <div class="clearfix form-actions">

                    <div class="col-md-offset-3 col-md-9">

                        <button class="btn btn-info" type="submit">
                            <i class="fa fa-save bigger-110"></i>
                            Save
                        </button>

                        &nbsp; &nbsp;

                        <a href="@url('sa_events_index')" class="btn">
                            <i class="fa fa-undo bigger-110"></i> Cancel
                        </a>



                        &nbsp; &nbsp;



                        <?php if(!empty($event->getId())) { ?>

                            <a href="<?= app::get()->getRouter()->generate('sa_event_recurrences', ['id' => $event->getId()]) ?>" class="btn btn-primary">

                                <i class="fa fa-pencil"></i> Edit Recurrences

                            </a>

                        <?php } ?>

                    </div>

                </div>



                <input type="hidden" value="<?= $event->getId() ?>" id="event_id" name="id"/>





                <!-- Modal -->
                <div class="modal fade recurrance-options-modal" id="recurring-options-modal" tabindex="-1" role="dialog">

                    <div class="modal-dialog" role="document">

                        <div class="modal-content">

                            <div class="modal-header">

                                <h4 class="modal-title" id="myModalLabel">Select your recurrence options</h4>

                            </div>

                            <div class="modal-body">

                                <ul class="nav nav-tabs" role="tablist">

                                    <li role="presentation" class="<?= empty($event->getFrequency()) || $event->getFrequency() == Event::FREQUENCY_DAILY ? 'active' : '' ?>">

                                        <a href="#recurrence-daily" data-frequency-toggle="daily" data-toggle="tab">Daily</a>

                                    </li>

                                    <li role="presentation" class="<?= $event->getFrequency() == Event::FREQUENCY_WEEKLY ? 'active' : '' ?>">

                                        <a href="#recurrence-weekly" data-frequency-toggle="weekly" data-toggle="tab">Weekly</a>

                                    </li>

                                    <li role="presentation" class="<?= $event->getFrequency() == Event::FREQUENCY_MONTHLY ? 'active' : '' ?>"

                                    ><a href="#recurrence-monthly" data-frequency-toggle="monthly" data-toggle="tab">Monthly</a>

                                    </li>

                                    <li role="presentation" class="<?= $event->getFrequency() == Event::FREQUENCY_YEARLY ? 'active' : '' ?>">

                                        <a href="#recurrence-yearly" data-frequency-toggle="yearly" data-toggle="tab">Yearly</a>

                                    </li>

                                    <!--<li role="presentation">

                                        <a href="#recurrence-custom" data-frequency-toggle="custom" data-toggle="tab">Custom</a>

                                    </li>-->

                                </ul>



                                <!-- Tab panes -->
                                <div class="tab-content">

                                    <!-- daily -->
                                    <div role="tabpanel" class="tab-pane <?= empty($event->getFrequency()) || $event->getFrequency() == Event::FREQUENCY_DAILY ? 'active' : '' ?>" id="recurrence-daily">

                                        <p>Every

                                            <input type="text" class="slider" value="1"
                                                   data-content="slider"
                                                   data-slider-min="1"
                                                   data-slider-max="30"
                                                   data-slider-step="1"
                                                   data-slider-value="<?= ($event->getInterval()

                                                       && $event->getFrequency() == Event::FREQUENCY_DAILY)

                                                       ? $event->getInterval()

                                                       : 1 ?>"

                                                   data-slider-orientation="horizontal"
                                                   data-slider-selection="before"
                                                   data-slider-tooltip="show"
                                                   title="">

                                            <span class="value"><?= ($event->getInterval()

                                                    && $event->getFrequency() == Event::FREQUENCY_DAILY)

                                                    ? $event->getInterval()

                                                    : 1 ?></span>

                                            day(s)

                                        </p>

                                    </div>



                                    <!-- weekly -->
                                    <div role="tabpanel" class="tab-pane <?= $event->getFrequency() == Event::FREQUENCY_WEEKLY ? 'active' : '' ?>" id="recurrence-weekly">

                                        <p>Every

                                            <input type="text" class="slider" value="1"

                                                   data-content="slider"
                                                   data-slider-min="1"
                                                   data-slider-max="52"
                                                   data-slider-step="1"
                                                   data-slider-value="<?= ($event->getInterval()

                                                       && $event->getFrequency() == Event::FREQUENCY_WEEKLY)

                                                       ? $event->getInterval()

                                                       : 1 ?>"

                                                   data-slider-orientation="horizontal"
                                                   data-slider-selection="before"
                                                   data-slider-tooltip="show"
                                                   title="">

                                            <span class="value"><?= ($event->getInterval()

                                                    && $event->getFrequency() == Event::FREQUENCY_WEEKLY)

                                                    ? $event->getInterval()

                                                    : 1 ?></span>

                                            week(s)

                                        </p>



                                        <br>



                                        <h4>On which days of the week?</h4>

                                        <div class="label-group">

                                            <?php foreach ($week_days as $key => $day) { ?>

                                                <label class="block-input block-primary">

                                                    <input type="checkbox" name="week_days[]" value="<?= $key ?>"

                                                        <?= (in_array($key, $event->getRecurrenceDays())

                                                            && $event->getFrequency() == Event::FREQUENCY_WEEKLY)

                                                            ? 'checked'

                                                            : '' ?> />

                                                    <span><?= $day ?></span>

                                                </label>

                                            <?php } ?>

                                        </div>

                                    </div>



                                    <!-- monthly -->
                                    <div role="tabpanel" class="tab-pane <?= $event->getFrequency() == Event::FREQUENCY_MONTHLY ? 'active' : '' ?>" id="recurrence-monthly">

                                        <p>Every

                                            <input type="text" class="slider" value="1"

                                                   data-content="slider"
                                                   data-slider-min="1"
                                                   data-slider-max="12"
                                                   data-slider-step="1"
                                                   data-slider-value="<?= ($event->getInterval()

                                                       && $event->getFrequency() == Event::FREQUENCY_MONTHLY)

                                                       ? $event->getInterval()

                                                       : 1 ?>"

                                                   data-slider-orientation="horizontal"
                                                   data-slider-selection="before"
                                                   data-slider-tooltip="show"
                                                   title="">

                                            <span class="value"><?= ($event->getInterval()

                                                    && $event->getFrequency() == Event::FREQUENCY_MONTHLY)

                                                    ? $event->getInterval()

                                                    : 1 ?></span>

                                            month(s)

                                        </p>

                                    </div>



                                    <!-- yearly -->

                                    <div role="tabpanel" class="tab-pane <?= $event->getFrequency() == Event::FREQUENCY_YEARLY ? 'active' : '' ?>" id="recurrence-yearly">

                                        <p>Every

                                            <input type="text" class="slider" value="1"

                                                   data-content="slider"
                                                   data-slider-min="1"
                                                   data-slider-max="10"
                                                   data-slider-step="1"
                                                   data-slider-value="<?= ($event->getInterval()

                                                       && $event->getFrequency() == Event::FREQUENCY_YEARLY)

                                                       ? $event->getInterval()

                                                       : 1 ?>"

                                                   data-slider-orientation="horizontal"
                                                   data-slider-selection="before"
                                                   data-slider-tooltip="show"
                                                   title="">

                                            <span class="value"><?= ($event->getInterval()

                                                    && $event->getFrequency() == Event::FREQUENCY_YEARLY)

                                                    ? $event->getInterval()

                                                    : 1 ?></span>

                                            year(s)

                                        </p>



                                        <br>



                                        <h4>In which months?</h4>

                                        <div class="label-group">

                                            <?php foreach ($months as $key => $month) { ?>

                                                <label class="block-input block-primary">

                                                    <input type="checkbox" name="months[]" value="<?= $key ?>"

                                                        <?= (in_array($key, $event->getRecurrenceMonths())

                                                            && $event->getFrequency() == Event::FREQUENCY_YEARLY)

                                                            ? 'checked'

                                                            : '' ?> />

                                                    <span><?= $month ?></span>

                                                </label>

                                            <?php } ?>

                                        </div>

                                    </div>

                                </div>



                                <br>



                                <h4>

                                    <span>Repeat until</span>

                                    <label class="icon-input">

                                        <input type="radio" name="repeat-until-date" value="0"

                                            <?= ($event->getUntilDate() == null) ? 'checked="checked"' : '' ?>>

                                        <span><i class="fa fa-check" aria-hidden="true"></i></span>

                                        Forever

                                    </label>



                                    <label class="icon-input">

                                        <input type="radio" name="repeat-until-date" value="1"

                                            <?= ($event->getUntilDate() != null) ? 'checked="checked"' : '' ?>>

                                        <span><i class="fa fa-check" aria-hidden="true"></i></span>

                                        Date

                                    </label>

                                </h4>



                                <div class="date-picker repeat-until-date-container" data-content="date-picker">

                                    <label>Date</label>

                                    <div class="input-group">

                                        <input type="text" name="until_date"

                                               class="form-control" title="Until Date"

                                               value="<?= (!is_null($event->getUntilDate()))

                                                   ? $event->getUntilDate()->format('m-d-Y')

                                                   : '' ?>">

                                        <span class="input-group-addon">

                                        <i class="fa fa-calendar" aria-hidden="true"></i>

                                    </span>

                                    </div>

                                </div>



                            </div>

                            <div class="modal-footer">

                                <button type="button" class="btn btn-primary" data-action="save-re-modal">

                                    Save changes

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>