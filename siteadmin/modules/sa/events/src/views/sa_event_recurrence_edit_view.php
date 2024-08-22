@extends('master')
@section('site-container')
<?php
/**
 * @var Event $event - The event associated with the recurrence.
 * @var array $recurrence - The recurrence to edit.
 */

use sacore\application\app;
use sa\events\Event;

?>
@asset::/events/css/bootstrap-datetimepicker.min.css
@asset::/events/css/sa_style.css
@asset::/events/js/moment.js
@asset::/events/js/bootstrap-datetimepicker.min.js
@asset::/events/js/rrule.js
@asset::/events/js/sa_script.js

<div class="row">
    <div class="col-md-6">
        <h2><strong>Event:</strong> <?= $event->getName() ?></h2>
    </div>
    <div class="col-sm-4 col-xs-4">
        <a href="<?= app::get()->getRouter()->generate('sa_event_recurrences', ['id' => $event->getId()]) ?>" class="btn btn-primary">
            <i class="fa fa-chevron-left"></i> Back to Recurrences
        </a>
    </div>
</div>

<?php
$notification = new \sacore\utilities\notification();
$notification->showNotifications();
?>

<div class="row">
    <div class="col-xs-12">
        <div class="tabbable">
            <form method="post" action="<?= app::get()->getRouter()->generate('sa_event_recurrence_save', ['eventId' => $event->getId(), 'recurrenceId' => $recurrenceId, 'recurrenceUniqueId' => $recurrenceUniqueId]) ?>">
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
                                                       value="<?= (isset($recurrence['start']))
                                                           ? $recurrence['start']->format('m/d/Y') : $event->getStartDate()->format('m/d/Y') ?>">

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
                                                       value="<?= (isset($recurrence['end']))
                                                           ? $recurrence['end']->format('m/d/Y') : $event->getEndDate()->format('m/d/Y') ?>">
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
if (isset($recurrence['start'])) {
    $startTimeStr = $recurrence['start']->format('H:i A');
} elseif (! is_null($event->getStartTime())) {
    $timezone = null;
    if ($event->getTimezone()) {
        $timezone = new DateTimeZone($event->getTimezone());
    }

    $startTime = new DateTime($event->getStartTime(), new DateTimeZone('UTC'));
    if ($timezone) {
        $startTime->setTimezone($timezone);
    }
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
if (isset($recurrence['end'])) {
    $endTimeStr = $recurrence['end']->format('H:i A');
} elseif (! is_null($event->getEndTime())) {
    $timezone = null;
    if ($event->getTimezone()) {
        $timezone = new DateTimeZone($event->getTimezone());
    }

    $endTime = new DateTime($event->getEndTime(), new DateTimeZone('UTC'));
    if ($timezone) {
        $endTime->setTimezone($timezone);
    }
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
                                                    <?php foreach ($timezones as $timezone) { ?>
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

                                    <h5>This recurrence exists:

                                        <label class="block-input block-primary">

                                            <input type="radio" name="recurrence-exists" value="0"

                                                <?= (! $recurrence['recurrenceExists']) ? 'checked' : '' ?> />

                                            <span>No</span>

                                        </label>



                                        <label class="block-input block-primary">

                                            <input type="radio" name="recurrence-exists" value="1"

                                                <?= ($recurrence['recurrenceExists']) ? 'checked' : '' ?> />

                                            <span>Yes</span>
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
                            </div>

                        </div>

                        <!-- end date/time form -->

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
                                           value="<?= (! is_null($recurrence['location_name'])) ? $recurrence['location_name'] : $event->getLocationName() ?>" class="form-control">

                                </div>



                                <!-- street_one -->

                                <div class="form-group">

                                    <label for="street_one">Street</label>

                                    <input type="text" name="street_one" title="Street One"
                                           value="<?= (! is_null($recurrence['street_one'])) ? $recurrence['street_one'] : $event->getStreetOne() ?>" class="form-control">

                                </div>



                                <!-- street_two -->

                                <div class="form-group">

                                    <label>Street Two</label>

                                    <input type="text" name="street_two" title="Street Two"
                                           value="<?= (! is_null($recurrence['street_two'])) ? $recurrence['street_two'] : $event->getStreetTwo() ?>" class="form-control">

                                </div>



                                <!-- city -->

                                <div class="form-group">

                                    <label>City</label>

                                    <input type="text" name="city" title="City"
                                           value="<?= (! is_null($recurrence['city'])) ? $recurrence['city'] : $event->getCity() ?>" class="form-control">

                                </div>





                                <div class="row">

                                    <div class="col-md-6">

                                        <!-- postal_code -->

                                        <div class="form-group">

                                            <label for="postal_code">Postal Code</label>

                                            <input type="text" name="postal_code" title="Postal Code"
                                                   value="<?= (! is_null($recurrence['postal_code'])) ? $recurrence['postal_code'] : $event->getPostalCode() ?>" class="form-control">

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
    if (! empty($recurrence['state'])) {
        //Todo: this will need to be fixed
        foreach ($states as $state) {
            $isSelected = (! empty($recurrence['state'])

                && $recurrence['state'] == $state->getAbbreviation())

                ? 'selected'

                : '';
            ?>

                                                    <option value="<?= $state->getAbbreviation() ?>"

                                                        <?= ($isSelected) ? 'selected' : '' ?>>

                                                        <?= $state->getName() ?>

                                                    </option>

                                                <?php }
        } else {
            //Todo: this will need to be fixed
            foreach ($states as $state) {
                $isSelected = (! empty($event->getState())

                    && $event->getState() == $state->getAbbreviation())

                    ? 'selected'

                    : '';

                ?>

                                                        <option value="<?= $state->getAbbreviation() ?>"

                                                            <?= ($isSelected) ? 'selected' : '' ?>>

                                                            <?= $state->getName() ?>

                                                        </option>
                                                <?php }
            } ?>

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
                                           class="form-control" value="<?= (! is_null($recurrence['contact_name'])) ? $recurrence['contact_name'] : $event->getContactName() ?>">

                                </div>



                                <!-- contact name -->

                                <div class="form-group">

                                    <label>Phone</label>

                                    <input type="text" name="contact_phone" title="Contact Phone"
                                           class="form-control" value="<?= (! is_null($recurrence['contact_phone'])) ? $recurrence['contact_phone'] : $event->getContactPhone() ?>">

                                </div>



                                <!-- contact name -->

                                <div class="form-group">

                                    <label>Email</label>

                                    <input type="text" name="contact_email" title="Contact Email"
                                           class="form-control"  value="<?= (! is_null($recurrence['contact_email'])) ? $recurrence['contact_email'] : $event->getContactEmail() ?>">

                                </div>

                            </div>



                        </div>

                    </div>



                </div>

                <div class="row form-actions">
                    <div class="col-md-offset-3 col-md-9">
                        <button class="btn btn-info" type="submit">
                            <i class="fa fa-save bigger-110"></i>
                            Save
                        </button>
                        &nbsp; &nbsp;
                        <a href="<?= app::get()->getRouter()->generate('sa_event_recurrences', ['id' => $event->getId()]) ?>" class="btn">
                            <i class="fa fa-undo bigger-110"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
    </div>
</div>
@show