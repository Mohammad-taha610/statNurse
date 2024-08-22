@extends('master')
@section('site-container')
<?php

echo 'Doctrine Data: <pre>'.$doctrine_data.'</pre>';

echo 'APC Data: <pre>'.$apc_data.'</pre>';
?>
@show