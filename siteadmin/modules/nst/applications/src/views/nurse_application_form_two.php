@extends('member_guest')

<script src="https://unpkg.com/vee-validate@3.0.0/dist/vee-validate.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

<script>
    var authenticatedMember = <?= json_encode($member) ?>;
</script>

@section('site-container')
    <v-app>
        <nurse-app-form></nurse-app-form>
    </v-app>

    @asset::/applications/js/nurse-app-form-two.js
    @asset::/themes/nst/js/vue-mask.min.js
@endsection
