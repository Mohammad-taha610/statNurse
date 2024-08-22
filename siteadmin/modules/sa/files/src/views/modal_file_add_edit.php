<form class="form-horizontal" action="<?=\sacore\utilities\url::make('files_save', $id)?>">
    <div class="modal-header">

        <h4 class="modal-title pull-left">File Management</h4>

        <div class="pull-right">

        </div>
        <div class="clearfix"></div>
    </div>
    <div class="modal-body">

        <?php
        $notify = new \sacore\utilities\notification();
        $notify->showNotifications();

        ?>

        <div class="form-group">
            <label for="form-field-issue">File</label>
            <input class="form-control" type="text"  value="<?=$filename?>" readonly />
        </div>

        <div class="form-group">
            <label for="form-field-issue">Label</label>
            <input class="form-control" type="text" name="label" id="form-field-issue" value="<?=$label?>" placeholder="Label" />
        </div>

        <div class="form-group">
            <label for="form-field-description">Description</label>
            <textarea class="form-control" name="description" id="form-field-description" placeholder="Detailed description of the file"><?=$description?></textarea>
        </div>

        <div class="form-group">
            <label for="type">Type</label>
            <select id="type" name="type_id" class="form-control">
                <option value="">-- Select --</option>
                <?php foreach($file_types as $type) { ?>
                    <option <?=$type_id==$type['id'] ? 'selected="selected"' : '' ?> value="<?=$type['id']?>"><?=$type['name'];?></option>
                <?php } ?>
            </select>
        </div>


    </div>
    <div class="modal-footer">
        @view::modal_file_buttons
    </div>
</form>