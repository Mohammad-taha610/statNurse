@extends('frontend-master')
@asset::/events/css/style.css
@asset::/events/css/calendario.css
@asset::/events/css/calendario-theme.css
@asset::/events/js/calendario.custom.js
@asset::/events/js/event_calendar.js
@asset::/events/js/script.js

@section('site-container')
<div class="container">
    <form id="calendar-container" action="/events/api/month/">
        <div class="calendar-header">
            <!-- Loading icon -->
            <span class="loading-icon">
                <i class="icon fa fa-spiner" aria-hidden="true"></i>
            </span>

            <!-- Calendar month/year indicators -->
            <h3 class="custom-month-year">
                <span id="custom-month" class="custom-month calendar-month"></span>
                <span id="custom-year" class="custom-year calendar-year"></span>
                <div class="holder">
                    <div class="toggle"></div>
                </div>
                <span class="calendar-navigation">

                    <!-- Go to previous month button -->
                    <span id="custom-prev" class="calendar-handle custom-prev" data-action="to-previous-month"></span>

                    <!-- Go to next month button -->
                    <span id="custom-next" class="calendar-handle custom-next" data-action="to-next-month"></span>

                    <!-- Return to current date button -->
                    <span id="custom-current" class="calendar-handle custom-current"
                          title="Go to current date" data-action="to-current-month"></span>
                </span>
            </h3>
        </div>

        <!-- Calendar -->
        <div id="calendar" class="calendar fc-calendar-container hidden" data-content="calendar"></div>
        <!-- List -->
        <div id="list"></div>
    </form>
</div>
<script>

    var category = '<?= $category ?: '' ?>';

    $('.holder').on('click', function() {
        if ($(this).hasClass('on') || $(this).hasClass('off')) {
            $(this).toggleClass('on');
            $(this).toggleClass('off');
        }
        else {
            $(this).addClass('on');
        }
    });
    $('body').on('click','.holder', function() {
        $('#calendar').toggle("fast","linear");
        $('#list').toggle("fast","linear");
    });
</script>
@show
