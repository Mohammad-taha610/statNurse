<?php
/**
 * @var Event  $event
 * @var string $addReservationUrl
 * @var string $cancelReservationUrl
 */
use sa\events\Event;

?>

@asset::/events/js/reservation_form.js

<div class="event-reservation-form">
    <h2>RSVP This Event</h2>
    <p>Enter your email then select an option below.</p>

    <div class="notifications"></div>

    <div class="form-group">
        <input type="email" name="event-reservation-email" title="Reservation Email" class="form-control">
    </div>

    <div class="row">
        <div class="col-md-6 text-center">
            <form action="<?= $cancelReservationUrl ?>" data-action="cancel-reservation">
                <button type="submit" class="btn btn-default">Cancel Reservation</button>
            </form>
        </div>
        <div class="col-md-6 text-center">
            <form action="<?= $addReservationUrl ?>" data-action="add-reservation">
                <button type="submit" class="btn btn-primary">Reserve</button>
            </form>
        </div>
    </div>
</div>