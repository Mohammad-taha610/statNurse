@extends('master')

@section('page-title')
Nurses
@endsection

@section('site-container')

<nurse-search-view
        :provider_id="$provider_id"
        search_term="$search_term"
></nurse-search-view>

@asset::/siteadmin/member/js/nurse_list_vue.js

@endsection