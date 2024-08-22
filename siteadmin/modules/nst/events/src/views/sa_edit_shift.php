@extends('master)

@section('site-container')

<?php
$id = $id == 0 ? false : $id;

$auth = \sa\system\saAuth::getInstance();
$saUser = is_object($auth) ? $auth->getAuthUser() : null;
$canCreateApprovedShifts = $saUser?->hasGroupPermission('events-create-approved-shifts') ?: false;
?>

<sa-edit-shift-view
        id="<?= $id ?>"
        source_id="<?= $source_id ?>"
        providerid="<?= $provider_id ?>"
        nurse_id="<?= $nurse_id ?>"
        recurrence_id="<?= $recurrence_id ?>"
        recurrence_unique_id="'<?= $recurrence_unique_id ?>'"
        recurrence_source_id="<?= $recurrence_source_id ?>"
        is_recurrence="<?= boolval($is_recurrence) ?>"
        is_copy="<?= boolval($is_copy) ?>"
        _start_date="<?= $start_date ?>"
        _end_date="<?= $end_date ?>"
        can_create_approved="<?= $canCreateApprovedShifts ?>"
></sa-edit-shift-view>

@asset::/shift/js/sa_edit_shift_vue.js
