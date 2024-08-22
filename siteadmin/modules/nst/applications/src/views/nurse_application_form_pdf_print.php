@extends('member_guest')

<script src="https://unpkg.com/vee-validate@3.0.0/dist/vee-validate.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">

<style>
@media print {
  body { 
    overflow: auto !important;
    height: auto !important; 
  }
  .scroll-y {
     height: auto !important;
     overflow: visible !important;
  }
  .print-break-page {
       page-break-after: always !important;
 }
 body,
  .scroll-y {
    overflow: visible !important;
    height: auto !important;
  }
} 
</style>

@section('site-container')
<v-app>
    <nurse-app-form></nurse-app-form>
</v-app>

@asset::/siteadmin/system/js/nst_overlay.js
@asset::/applications/js/file-uploader.js
@asset::/applications/js/nurse-app-document-upload.js
@asset::/applications/js/nurse-app-form-pdf-print.js
@asset::/themes/nst/js/vue-mask.min.js
@asset::/themes/nst/assets/vendor/toastr/js/toastr.min.js
@asset::/themes/nst/assets/vendor/toastr/css/toastr.min.css
@endsection
