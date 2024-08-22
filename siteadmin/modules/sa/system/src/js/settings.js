var img_container = null;
var img_input = null;
var img_module = null;

$(document).ready( function() {
    $('.select-image').click(function (e) {
        img_container = $(this).data('container');
        img_input = $(this).data('input');
        img_module = $(this).data('module');
        e.preventDefault();
        var browserurl = $('#settings-form').data('browser');
        window.open(browserurl + "?return=object&folder=siteadmin_theme_images", "browser", "location=1,status=0,scrollbars=0,width=600,height=700");
    });

    $('.delete-image').click(function (e) {
        img_container = $(this).data('container');
        img_input = $(this).data('input');
        img_module = $(this).data('module');
        e.preventDefault();
        $('.image-holder.' + img_container).html('<input type="hidden" name="'+img_module+'[' + img_input + ']" value="" /> No Image Selected');

    });
});


function fileBrowserSelectCallBack(object) {
    $('.image-holder.' + img_container).html('<img src="' + object.filepath + '" height="35" /><br /><input type="hidden" name="'+img_module+'[' + img_input + ']" value="' + object.id + '" />');
}