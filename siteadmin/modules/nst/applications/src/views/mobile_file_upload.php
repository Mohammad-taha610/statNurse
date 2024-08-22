@extends('member_guest')

<script src="https://unpkg.com/vee-validate@3.0.0/dist/vee-validate.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">

<!-- Tailwind + DaisyUI -->
<link href="https://cdn.jsdelivr.net/npm/daisyui@3.1.5/dist/full.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            colors: {
                primary: '#ff0000',
            }
        }
    }
</script>

<?php
    $application_id = $application->getId();
    $uploaded_files = $uploaded_files['uploaded_files'];
?>

@section('site-container')
<v-app>
    <upload-documents-mobile-view
        :application_id="<?php echo $application_id; ?>"
        style="margin-top: 75px;"
    ></upload-documents-mobile-view>
</v-app>

@asset::/siteadmin/system/js/nst_overlay.js
@asset::/themes/nst/js/vue-mask.min.js
@asset::/applications/js/file-uploader.js
@asset::/applications/js/upload-documents-mobile-view.js
@asset::/themes/nst/assets/vendor/toastr/js/toastr.min.js
@asset::/themes/nst/assets/vendor/toastr/css/toastr.min.css
@asset::/themes/nst/assets/css/nurse-app-form.css