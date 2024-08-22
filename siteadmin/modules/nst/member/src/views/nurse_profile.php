@extends('master')

@section('page-title')
Nurse Profile
@endsection

@section('site-container')

<nurse-profile 
        :id="$id" 
        files-route="$providerNurseLoadFilesRoute"
></nurse-profile>


@asset::/applications/js/file-uploader.js
@asset::/siteadmin/member/js/nurse_files_vue.js
@asset::/siteadmin/member/js/nurse_profile_vue.js
@asset::/themes/nst/js/vue-mask.min.js
@endsection