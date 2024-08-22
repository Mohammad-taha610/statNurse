$(document).ready(function () {
    var $dropzone   = $('#profile-image-dropzone');
    var $dropzonePreviewContainer = $dropzone.find('.image-preview-container');
    var $dropzonePreview          = $dropzonePreviewContainer.find('.profile-photo');
    var $dropzoneLoadingIcon      = $dropzonePreviewContainer.find('.loading-icon');
    var photoInputSelector = 'input[name="avatar"]';
    var $photoInput = $(photoInputSelector);
    var $fileToggle = $dropzone.find('[data-action="change-profile-photo"]');
    var avatarUrl = $dropzone.data('avatar-url');
    
    /**
     * File toggles
     *
     * When clicked, the file browser will be opened.
     */
    $fileToggle.click(function (e) {
        e.preventDefault();
        $photoInput.click();
    });

    /**
     * File upload handler.
     */
    $photoInput.fileupload({
        url: $dropzone.attr('data-upload-route'),
        maxChunkSize: 1950000,
        dataType: "json",
        autoUpload: true,
        start: function() {
            toggleLoadingIcon(true);
            $dropzone.find('.warning').remove();
        },
        success: function(response){
            if(response.hasOwnProperty('success') && response.success == false) {
                $dropzonePreviewContainer.parent().find(".text-danger").remove();
                $dropzonePreviewContainer.after('<div class="text-danger text-center warning">' + response.message + '</div>');
            } else {
                createImageTag($dropzonePreview);

                var time = (new Date().getTime() / 1000);
                var url = '';
                
                if(avatarUrl == null || typeof avatarUrl === 'undefined') {
                    url = '/member/profile/avatar.jpg?t=' + time;
                } else {
                    url = avatarUrl + '?t=' + time;
                }

                $dropzonePreview.attr('src', url);
                
                toggleLoadingIcon(false);
            }
        },
        done: function(response) {
            //Plugin de-references this variable once the upload is complete.
            $dropzone   = $('#profile-image-dropzone');
            $photoInput = $(photoInputSelector);
        },
        error: function() {
            $dropzonePreviewContainer.parent().find(".text-danger").remove();
            $dropzonePreviewContainer.after('<div class="text-danger text-center warning">Failed to upload file. Please refresh and try again.</div>');
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');


    function toggleLoadingIcon(doShow) {
        if(doShow) {
            $dropzonePreview.fadeOut(300, function(){
                $dropzoneLoadingIcon.fadeIn(300);
            });
        } else {
            $dropzoneLoadingIcon.fadeOut(300, function(){
                $dropzonePreview.fadeIn(300);
            });
        }
    }

    function createImageTag() {
        $dropzonePreview = $dropzonePreviewContainer.find('.profile-photo');
        
        if($dropzonePreview.is('i') || $dropzonePreview.is('svg')) {
            $dropzonePreview.replaceWith('<img class="image-preview img-responsive profile-photo"/>');
        }

        $dropzonePreview = $dropzonePreviewContainer.find('.profile-photo');
    }

});