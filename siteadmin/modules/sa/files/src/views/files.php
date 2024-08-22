@extends('master')
@section('site-container')
@asset::/themes/inspinia/assets/datatables/js/jquery.dataTables.min.js
@asset::/themes/inspinia/assets/datatables/js/jquery.dataTables.reload.js
@asset::/assets/files/js/files.js
@asset::/themes/inspinia/assets/datatables/css/jquery.dataTables.min.css

<div class="pull-right filter_action_buttons" data-destination="title-action">

    <span class="btn btn-primary fileinput-button">
        <i class="fa fa-plus"></i>
        <span>Select file</span>
        <!-- The file input field used as target for the file upload widget -->
    </span>
    <input id="uploadfilebutton" style="display: none" type="file" name="file">

</div>

<div class="clearfix"></div>

<table id="files_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th>Label</th>
        <th>File Name</th>
        <th class="text-right"></th>
    </tr>
    </thead>
</table>

@view::modal
@show