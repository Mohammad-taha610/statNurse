@extends('master')
@section('site-container')
@view::table
Last Refreshed: <?=$last_refreshed ? $last_refreshed->format('m/d/Y h:i:s a') : '' ?>
@show