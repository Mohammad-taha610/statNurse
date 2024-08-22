@extends('master')
@section('site-container')
<?php

echo 'Event Listeners: <pre>'.$events.'</pre>';
echo 'Mod Request Listeners: <pre>'.$listeners.'</pre>';
?>
@show
