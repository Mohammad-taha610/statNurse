<div id="errorHolder"></div>
<div id="consoleContainer">
    <div id="consoleHolder"></div>
    <div id="consoleRunning"><i class="fa fa-circle-o-notch fa-spin"></i></div>
</div>

<script>
    var hasScrolled = false;
    var log_error_count = 0;
    
    $('#consoleHolder').scroll(function() {
        hasScrolled = true;
    });
    
    var progress = setInterval(function() {

        $('#pleaseWait').append('.');

        $.get('<?= \sacore\application\app::get()->getRouter()->generate('sa_module_composer_log')?>', '', function(response) {
            var output = response.output;
            var threadStatus = response.thread_status;
            var message = 'Store operations complete.';
            
            if ( output.indexOf('Aborted')!=-1 || output.indexOf('RuntimeException')!=-1 ) {
                message = 'An error has occurred.  Please review the output console.';
            }
            
            if((threadStatus.composer.has_run && !threadStatus.composer.has_errors) &&
                (threadStatus.doctrine && threadStatus.doctrine.has_run && !threadStatus.doctrine.has_errors) &&
                (threadStatus.postComposerTasks && threadStatus.postComposerTasks.has_run && !threadStatus.postComposerTasks.has_errors)) {

                clearInterval(progress);
                $('#consoleRunning').hide();
                $('#pleaseWait').html(message);

            }

            if((threadStatus.composer.has_run && threadStatus.composer.has_errors) ||
                (threadStatus.doctrine && threadStatus.doctrine.has_run && threadStatus.doctrine.has_errors) ||
                (threadStatus.postComposerTasks && threadStatus.postComposerTasks.has_run && threadStatus.postComposerTasks.has_errors)) {


                log_error_count++;

                if (log_error_count>15) {
                    message = 'An Error has occurred.';
                    console.log(threadStatus);

                    clearInterval(progress);
                    $('#consoleRunning').hide();
                    $('#pleaseWait').html(message);
                }
            }
            
            $('#consoleHolder').html(output);
            
            if(!hasScrolled) {
                $('#consoleHolder').scrollTop($('#consoleHolder').height());
            }

            var searchdata = output.replace(/&nbsp;/g, '');
        });

    }, 2000)
</script>