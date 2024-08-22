@extends('master')
@section('site-container')
<table class="table table-striped table-responsive">
    <tr>
        <th>Name</th>
        <th>Store name</th>
        <th>Version</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    <?php foreach ($pkgs as $pkg) { ?>
    <tr>
        <td><?=$pkg['name']?></td>
        <td><?=! empty($pkg['extra']['store']) ? $pkg['extra']['store']['name'] : '--'?></td>
        <td><?=$pkg['version_normalized']?></td>
        <td><?=$pkg['type']?></td>
        <td><?=$pkg['description']?></td>
    </tr>
    <?php } ?>

</table>
@show