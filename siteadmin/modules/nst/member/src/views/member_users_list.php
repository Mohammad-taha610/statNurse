@extends('master')

@section('page-title')
Manage Users
@endsection

@section('site-container')


<provider-users-list
    member_id="$member_id"
    current_user_id="$current_user_id"
    current_user_type="$current_user_type"
></provider-users-list>

@asset::/siteadmin/member/js/member_users_list_vue.js

@endsection