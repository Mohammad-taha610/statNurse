<?php
/**
 * @var string  $time         - A string representation of the recurrence's start/end time.
 * @var array   $event        - An event serialized as an array.
 * @var array   $recurrence   - A recurrence serialized as an array.
 */
?>
<div class="col-md-6">
    <div class="single-date">
        <a href="#" data-date="<?= $recurrence['start']->getTimestamp() ?>" class="active">
            <div class="date">
                <span class="day"><?= $recurrence['start']->format('d') ?></span>
                <span class="month"><?= $recurrence['start']->format('M') ?></span>
            </div>

            <div class="time">
                <span><?= $time ?></span>
            </div>
        </a>
    </div>
</div>