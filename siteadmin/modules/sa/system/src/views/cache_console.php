@extends('master')
@section('site-container')
<div id="errorHolder">Please Wait</div>
<div id="consoleContainer">
    <div id="consoleHolder"></div>
    <div id="consoleRunning"><i class="fa fa-circle-o-notch fa-spin"></i></div>
</div>

<script>
    var hasScrolled = false;
    
    $('#consoleHolder').click(function() {
        hasScrolled = true;
    });
    
    var progress = setInterval(function() {

        $('#pleaseWait').append('.');

        $.get("url('sa_asset_build_log')", '', function(response) {
            var output = response.output;
            var threadStatus = response.thread_status;
            var message = 'Build Complete';
            
            if ( output.indexOf('Aborted')!=-1 || output.indexOf('RuntimeException')!=-1 ) {
                message = 'An error has occurred.  Please review the output console.';
            }
            
            if(threadStatus.has_run && !threadStatus.has_errors) {

                clearInterval(progress);
                $('#consoleRunning').hide();
                $('#errorHolder').html(message);
            }

            if(threadStatus.has_run && threadStatus.has_errors) {

                message = 'An Error has occurred.';
                console.log(threadStatus);

                clearInterval(progress);
                $('#consoleRunning').hide();
                $('#errorHolder').html(message);
            }
            
            $('#consoleHolder').html(output);
            
            if(!hasScrolled) {
                $('#consoleHolder').scrollTop($('#consoleHolder')[0].scrollHeight);
            }

            var searchdata = output.replace(/&nbsp;/g, '');
        });

    }, 2000)
</script>
@show