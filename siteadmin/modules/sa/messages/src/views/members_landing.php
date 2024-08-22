@asset::/themes/inspinia/assets/datatables/js/jquery.dataTables.min.js
@asset::/themes/inspinia/assets/datatables/js/jquery.dataTables.reload.js
@asset::/crashes/js/crashes_landing.js
@asset::/components/jquery-tags/jquery.tagsinput.min.js
@asset::/components/jquery-tags/jquery.tagsinput.css
@asset::/themes/inspinia/assets/datatables/css/jquery.dataTables.min.css


<div class="filter_action_buttons" data-destination="title-action">
    <button class="btn btn-primary show_filters"  value="">Filter/Search</button>
    <a id="addCrash" href="@url('cc_crashes_add')" class="btn btn-primary"  data-samodal="true" data-target="#crashes_modal_add">Add New</a>
</div>

<div class="clearfix"></div>
<div class="filters saHidden">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-3">
            <label for="search_crash_name">Crash Name</label>
            <input id="search_crash_name" type="text" class="form-control" value="" />
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <label for="search_comments">Comments</label>
            <input id="search_comments" type="text" class="form-control" value="" />
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <label for="search_status">Status</label>
            <select id="search_status" name="search_status" class="form-control">
                <option value="">-- Select --</option>
                <option <?=$status=='New' ? 'selected="selected"' : '' ?> value="New">New</option>
            </select>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <label for="search_date">Date</label>
            <input id="search_date" type="text" class="form-control" value="" />
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-3">
            <input id="search_has_cars_count" type="checkbox"  value="1" />
            <label for="search_has_cars_count">Has # of Cars</label>
            <br />
            <input id="search_has_plaintiffs_count" type="checkbox"  value="1" />
            <label for="search_has_plaintiffs_count">Has # of Plaintiffs</label>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <input id="search_has_photos" type="checkbox"  value="1" />
            <label for="search_has_photos">Has Photos</label>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">

        </div>
        <div class="col-xs-12 col-sm-12 col-md-3">
            <button class="btn btn-info pull-right" id="searchNow" style="margin-top: 10px" value="">Search</button>
        </div>
    </div>
</div>

<table id="crashes_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th></th>
        <th>Name</th>
        <th>Location</th>
        <th>Date</th>
        <th>Status</th>
        <th># Cars</th>
        <th># Plaintiffs</th>
        <th># Photos</th>
        <th></th>
    </tr>
    </thead>
</table>

@view::modal