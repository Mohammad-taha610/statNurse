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


<script>
    var authenticatedMember = <?= json_encode($member) ?>;
</script>

@section('site-container')
<v-app>
    <nurse-app-form style="margin-top: 75px;"></nurse-app-form>
</v-app>

@asset::/siteadmin/system/js/nst_overlay.js
@asset::/themes/nst/js/vue-mask.min.js
@asset::/applications/js/nurse-app-form-step1.js
@asset::/applications/js/nurse-app-form-step2.js
@asset::/applications/js/nurse-app-form-step3.js
@asset::/applications/js/nurse-app-form-step4.js
@asset::/applications/js/nurse-app-form-step5.js
@asset::/applications/js/nurse-app-form-step6.js
@asset::/applications/js/nurse-app-form-step7.js
@asset::/applications/js/nurse-app-form.js
@asset::/applications/js/create-login.js
@asset::/applications/js/upload-documents-view.js
@asset::/applications/js/file-uploader.js
@asset::/applications/js/drug-screen.js
@asset::/applications/js/background-check.js
@asset::/themes/nst/assets/vendor/toastr/js/toastr.min.js
@asset::/themes/nst/assets/vendor/toastr/css/toastr.min.css
@asset::/themes/nst/assets/css/nurse-app-form.css
@endsection