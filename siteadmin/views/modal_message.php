<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"></h4>
</div>
<div class="modal-body">

    <?php
    $notify = new \sacore\utilities\notification();
    $notify->showNotifications();

    ?>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>