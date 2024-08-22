@extends('master')

@section('page-title')
Review Shifts
@endsection

@section('site-container')

<review-shifts
    :provider_id="$provider_id">
</review-shifts>

@asset::/shift/js/review_shifts_vue.js

@endsection