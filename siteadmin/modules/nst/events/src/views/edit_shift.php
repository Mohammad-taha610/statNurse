@extends('master')

@section('page-title')
Edit Shift
@endsection

@section('site-container')

<?php
$id = $id == 0 ? false : $id;
?>

<shift-view
        id="<?= $id ?>"
        source_id="<?= $source_id ?>"
        provider_id="<?= $provider_id ?>"
        nurse_id="<?= $nurse_id ?>"
        recurrence_id="<?= $recurrence_id ?>"
        recurrence_unique_id="'<?= $recurrence_unique_id ?>'"
        recurrence_source_id="<?= $recurrence_source_id ?>"
        is_recurrence="<?= boolval($is_recurrence) ?>"
        is_copy="<?= boolval($is_copy) ?>"
        _start_date="<?= $start_date ?>"
        _end_date="<?= $end_date ?>"
></shift-view>

@asset::/shift/js/edit_shift_vue.js

@endsection
