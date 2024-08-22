@extends('member_guest')

<script src="https://unpkg.com/vee-validate@3.0.0/dist/vee-validate.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

@section('site-container')
<v-app>
    <div class="container my-16 nurse-app-form" data-app>
        <div class="row">
            <v-card class="px-10 pt-10 pb-8" elevation="2" style="width: 100%;">
                <div class="col-lg-12">
                    <p>
                        <h3>Thank you for your interest in NurseStat.</h3>
                    </p>
                    <p>
                        <br /><b>Office/Text:</b> <a href="tel:+8597489600">859-748-9600</a>
                    </p>
                    <p>
                        <b>Fax:</b> 859-715-0555
                    </p>
                    <p>
                        <b>Email:</b> <a href="mailto:tanner@nursestatky.com">tanner@nursestatky.com</a>
                    </p>
                    <p>
                        <b>Address:</b>
                    </p>
                    <p>
                        226 Morris Drive
                    </p>
                    <p>
                        Harrodsburg, KY 40330
                    </p>
                    <p>
                        <b>We are excited to have you join the NurseStat team.</b>
                    </p>
                </div>
            </v-card>
        </div>
    </div>
</v-app>

@asset::/siteadmin/system/js/nst_overlay.js
@asset::/themes/nst/js/vue-mask.min.js
@asset::/themes/nst/assets/vendor/toastr/js/toastr.min.js
@asset::/themes/nst/assets/vendor/toastr/css/toastr.min.css
@endsection