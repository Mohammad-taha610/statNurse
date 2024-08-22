@extends('frontend-master')
@section('site-container')
@asset::/events/js/full_calendar.min.js
@asset::/events/css/full_calendar.min.css
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.css">
<link href="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/css/smart_wizard_all.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/js/jquery.smartWizard.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.js" type="text/javascript"></script>


<style>
    .sw-height-fix {
        height: auto!important;
    }
</style>
<script>
//    Not needed but this is how you can configure the fullCalendarConfig
    let customButtons = {
    };

    //Also how you configure the fullCalendarConfig
    //Anything that isn't list here will be default
    //Using default by default but as an example
    // let buttonLocations = {
    //     start: '',
    //     center: 'title',
    //     end: 'prev,next'
    // };

    let fullCalendarConfig = {
        customButtons: customButtons,
        // buttonLocations: buttonLocations
    };
</script>

<?php

?>
<div class="layout_div_container page-wrapper">
    <div class="container-fluid">

        <div id="vue-context" class="container" style="margin-top: 70px; margin-bottom:100px;">
            <!-- Calendar -->
            <fullcalendar v-bind="fullCalendarConfig"></fullcalendar>
            <!-- List -->
            <div id="list"></div>
        </div>
    </div>
</div>

<div class="modal" id="reserveModal" tabindex="-1" role="dialog" aria-labelledby="reserveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reserveModalLabel">Reserve Community Hall</h5>
                <button type="button" class="close" aria-label="Close" onclick="$('#reserveModal').modal('hide');$('#reserveSmartWizard').smartWizard('reset');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reserveEventForm" method="post">
                    <div id="reserveSmartWizard">
                        <ul class="nav">
                            <li>
                                <a class="nav-link" href="#step-1">
                                    Event Information
                                </a>
                            </li>
                            <li>
                                <a class="nav-link" href="#step-2">
                                    Contact Information
                                </a>
                            </li>
                            <!-- <li>
                                <a class="nav-link" href="#step-3">
                                    Waiver
                                </a>
                            </li> -->
                            <!-- <li>
                                <a class="nav-link" href="#step-4">
                                    Payment
                                </a>
                            </li> -->
                            <li>
                                <a class="nav-link" href="#step-3">
                                    Confirmation
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content sw-height-fix">
                            <div id="step-1" class="tab-pane" role="tabpanel">
                                <div id="step-1-errors" class="alert alert-danger" role="alert" style="display: none;"></div>
                                <label for="name">Event Name</label>
                                <input type="text" id="name" name="name"/><br>
                                <label for="name">Community Hall</label>
                                <select id="category_id" name="category">
                                    <option value="">Select Community Hall</option>
                                    <?php

                                    foreach ($categories as $cat) {
                                        ?>
                                        <option value="<?php echo $cat->getId(); ?>"><?php echo $cat->getName(); ?></option>
                                        <?php
                                    }
?>
                                </select><br>
                                <label for="description">Tell us a little a bit about the event</label>
                                <input type="textarea" id="description" name="description"/><br>
                                <!-- <label for="location_name">Where is the event taking place?</label>
                                <input type="text" id="location_name" name="location_name"/><br> -->
                                <label for="start_date">Start Date & Time</label>
                                <input type="text" id="datetimepicker" name="start_date"  autocomplete="off"/> <br>
                                <label for="start_date">End Time</label>

                                <input type="text" id="datetimepicker2" name="end_date"  autocomplete="off"/> <br>

                            </div>

                            <div id="step-2" class="tab-pane" role="tabpanel">
                                <div id="step-2-errors" class="alert alert-danger" role="alert" style="display: none;"></div>

                                <label for="contact_name">What is your name?</label>
                                <input type="text" id="contact_name" name="contact_name"/><br>
                                <label for="contact_phone">What is your phone number?</label>
                                <input type="text" id="contact_phone" name="contact_phone"/><br>
                                <label for="contact_email">What is your email address?</label>
                                <input type="text" id="contact_email" name="contact_email"/><br>
                            </div>

                            <div id="step-3" class="tab-pane" role="tabpanel">
                                <div id="step-5-errors" class="alert alert-danger" role="alert" style="display: none;">Test</div>
                                <div id="step-5-success"class="alert alert-success" role="alert" style="display: none;">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- smart wizard -->
<script>

    $(document).ready(function() {

        $("#reserveSmartWizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {

            if(stepDirection <= stepNumber){
                return true;
            }
            var errors = 0;
            var output = 0;
            if(stepNumber == 0) {

                $('#step-1-errors').empty();
                errors = step1(1);
                if(errors == 1) {
                    return false;
                }
                return true;

            }
            //Start Step 2
            else if(stepNumber == 1) {

                $('#step-2-errors').empty();
                errors = step2(2);
                if(errors == 1) {
                    return false;
                }

                //Final Step
                $('#step-3-errors').empty();
                $('#step-5-success').empty();
                $('#step-4-errors').empty();
                errors = stepfinal(4);
                if(errors == 1) {
                    $('#step-4-errors').show();
                    return false;
                } else {
                    var form_data = $("#reserveEventForm").serialize();
                    load_widget_html('step-5-success', '', form_data, function(result) {
                        if(!result){

                            $('#step-4-errors').empty();
                            $('#step-5-errors').empty();
                            $('#step-5-success').empty();
                            $('#step-5-success').hide();
                            $('#step-5-errors').append("<strong>Something is wrong</strong><br>");
                            $('#step-5-errors').show();
                            errors = 1;
                            return false;
                        }else{

                            if(!result.responseJSON){

                                $('#step-5-errors').hide();
                                $('#step-4-errors').empty();
                                $('#step-5-errors').empty();
                                $('#step-5-success').empty();
                                $('#step-5-success').append("<strong>Thank you for your reservation. We will be in touch with you shortly.</strong><br>");
                                $('#step-5-success').show();
                                return true;
                            }else{
                                $('#step-4-errors').empty();
                                $('#step-5-errors').empty();
                                $('#step-5-success').empty();
                                $('#step-5-success').hide();
                                $('#step-5-errors').append(result.responseJSON["error"].message);
                                $('#step-5-errors').show();
                                return true;
                            }

                        }
                    });
                }
                //End Final Step
            }
            // else if(stepNumber == 2) {



            // }

            return true;


        });

        function load_widget_html(container, request, data, callback) {
            modRequest.request("events.saveEvent", null, {"request": request, "passData": data}, function(resp) {

                if ( !resp.error) {
                    //$('#step-5-success').append("<strong>Event Added Successfully!</strong><br>");
                    callback(resp);
                } else {
                    //$(container).html("Failed to load");
                    callback(resp);
                }

            }, function(resp){
                callback(resp);
            });

        }
        function isUrlValid(url) {
            return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
        }
        function step1(num){
            var errors = 0;

            $('#step-'+num+'-errors').empty();
            if(!$('#name').val()) {
                $('#step-'+num+'-errors').append("<strong>Please enter an event name.</strong><br>");
                errors = 1;
            }

            if(!$('#description').val()) {
                $('#step-'+num+'-errors').append("<strong>Please enter information about the event.</strong><br>");
                errors = 1;
            }

            if(!$( "#category_id option:selected").val()) {
                $('#step-'+num+'-errors').append("<strong>Please select an event hall.</strong><br>");
                errors = 1;
            }

            // if(!$('#location_name').val()) {
            //     $('#step-'+num+'-errors').append("<strong>Please select a location for the event</strong><br>");
            //      errors = 1;
            // }

            if(!$('#datetimepicker').val()) {
                $('#step-'+num+'-errors').append("<strong>Please select a start date and time for the event.</strong><br>");
                errors = 1;
            }

            if(!$('#datetimepicker2').val()) {
                $('#step-'+num+'-errors').append("<strong>Please select an end time for the event.</strong><br>");
                errors = 1;
            }

            if(errors == 1) {
                $('#step-'+num+'-errors').show();
                return true;
            } else {
                $('#step-'+num+'-errors').empty();
                $('#step-'+num+'-errors').hide();
                return false
            }
        }

        function IsEmail(email) {
            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if(!regex.test(email)) {
                return false;
            }else{
                return true;
            }
        }

        function step2(num){
            var errors = 0;

            $('#step-'+num+'-errors').empty();
            if(!$('#contact_name').val()) {
                $('#step-'+num+'-errors').append("<strong>Please enter a contact name</strong><br>");
                errors = 1;
            }
            if(!$('#contact_phone').val()) {
                $('#step-'+num+'-errors').append("<strong>Please enter a contact phone</strong><br>");
                errors = 1;
            }
            var phone = $('input[name="contact_phone"]').val(),
                intRegex = /[0-9 -()+]+$/;
            if((phone.length < 9) || (!intRegex.test(phone)))
            {
                $('#step-'+num+'-errors').append("<strong>Please enter a valid phone number</strong><br>");
                errors = 1;
            }
            if(!$('#contact_email').val()) {
                $('#step-'+num+'-errors').append("<strong>Please enter a contact email</strong><br>");
                errors = 1;
            }
            var myemail = $('#contact_email').val();
            if(IsEmail(myemail)==false){
                $('#step-'+num+'-errors').append("<strong>Please enter a valid contact email</strong><br>");
                errors = 1;
            }
            // if(!$('#contact_comments').val()) {
            //     $('#step-'+num+'-errors').append("<strong>Please enter a contact comments</strong><br>");
            //     errors = 1;
            // }
            if(errors == 1) {
                $('#step-'+num+'-errors').show();
                return true;
            } else {
                $('#step-'+num+'-errors').empty();
                $('#step-'+num+'-errors').hide();
                return false
            }

        }

        function stepfinal(num){
            var errors =0;
            errors = step1(num);
            if(errors == 1) {
                $('#step-'+num+'-errors').show();
                return true;
            }
            errors = step2(num);
            if(errors == 1) {
                $('#step-'+num+'-errors').show();
                return true;
            }
            return false;
        }




    });
</script>

@asset::/events/js/index_view_vue.js

@show