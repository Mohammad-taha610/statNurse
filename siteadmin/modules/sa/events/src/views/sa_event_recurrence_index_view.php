@extends('master')
<?php
/**
 * @var view  $self
 * @var array $table
 * @var array $event
 */
use sacore\application\app;

?>
@section('site-container')
    <h3>Recurrences for <?=$event['name']; ?></h3>

    <div class="form-group">
        <a href="@url('sa_events_index')" class="btn btn-primary">
            <i class="fa fa-chevron-left"></i> Events
        </a>

        <a href="<?= app::get()->getRouter()->generate('sa_events_edit', ['id' => $event['id']]) ?>" class="btn btn-primary">
            <i class="fa fa-pencil" aria-hidden="true"></i> Edit Event
        </a>
    </div>

    <br>

    <p>** All day events will show times between 12am and 11:59pm.</p>

<!--This might have to be changed as well-->
<?= $self->subView('table', $data) ?>
$show
