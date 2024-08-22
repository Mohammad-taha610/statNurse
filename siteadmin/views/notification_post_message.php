<div class="alert alert-block alert-<?=$type?> sa-dismisable">
    <div class="row">
        <div class="pull-left" style="font-size:25px; padding-left:10px;">
            <strong>
                <i class="fa fa-<?=$icon?>"></i>
                <?=$headmessage?>
            </strong>
        </div>
        <div class="pull-left"  style="padding:10px 0 0 25px">
            <?=$message?>
        </div>
    </div>
</div>
<div class="notification_post_content">
	<?=$post_content ?>
</div>

<script>
	var i = 1;
	$('.sa-dismisable').each(function() {
		if(i == 1) {
			$(this).css('position','relative');
			$(this).prepend('<i class="fa fa-close" style="float: right; cursor: pointer;" onclick="dismissThis(this)")></i>');
		}
		else {
			dismissThis(this);
		}
		i++;
	});

	function dismissThis(elem)
	{
		$(elem).parent().next('.notification_post_content').remove();
		$(elem).parent().remove();
	}
</script>