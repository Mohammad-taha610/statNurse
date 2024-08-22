<?php
use sacore\application\app;

?>
@asset::/events/css/eventElement.css

<?php foreach ($events as $event) { ?>
    <div class="eventsContainer">
        <div class="col-sm-2 eventDate"><?=$event['recurrence']['start']->format('m/d')?></div>
        <div class="col-sm-10 eventName"><a href="<?= app::get()->getRouter()->get('event_single_recurrence', ['id' => $event['event']['id'], 'recurrenceId' => $event['recurrence']['id']]) ?>"><?=$event['event']['name']?></a></div>
    </div>
<?php } ?>