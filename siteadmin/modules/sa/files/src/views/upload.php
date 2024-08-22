<div class="row">

    <div class="col-xs-12 col-md-12">
        <!-- PAGE CONTENT BEGINS -->

        <div id="dropzone">
            <form action="<?=$url->make('files_accept_upload')?>" class="dropzone dz-clickable">
                <div class="dz-default dz-message">
                    <span>
                        <span class="bigger-150 bolder">
                            <i class="fa fa-caret-right red"></i> Drop files
                        </span> to upload
                        <span class="smaller-80 grey">(or click)</span> <br>
                        <i class="upload-icon fa fa-cloud-upload blue fa fa-3x"></i>
                    </span>
                </div>
                <div class="fallback">
                    <input name="file" type="file" multiple />
                </div>
            </form>
        </div>
        <!-- PAGE CONTENT ENDS -->
    </div>

</div>