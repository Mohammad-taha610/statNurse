@extends('master')
@section('site-container')
<h2>Safe mode was triggered because of a fatal error that occured within Site Admin.</h2>
<a href="@url('system_safemode_disable')" class="btn btn-primary">DISABLE SAFE MODE</a>
<hr />

<?=$log?>
@show
