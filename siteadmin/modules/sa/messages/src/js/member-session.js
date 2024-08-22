var secondsInactive = 0;
var clockReset = 0;
var hasBeenWarned = false;
var sessionclock = null;
var title = '';

$(document).ready(function() {
    title = $('title').text();
    
    // RESET CLOCK ON OTHER TABS
    clockReset = getCookie('member_session_clock_reset');
    
    if (clockReset == '') {
        clockReset = 0;
    }
    
    clockReset++;
    setCookie('member_session_clock_reset', clockReset);

    sessionContinue();

    sessionclock = setInterval( checkSession, 1000 );
    
    $('body').on('click', '#continue_session', function() {
        sessionContinue();
    });
    
    $(document).on('mousemove keypress scroll', 'body', function() {
        if($('#member-session-modal').hasClass('in')) {
            return;
        }
        
        secondsInactive = 0;
        hasBeenWarned = false;

        setCookie('member_session_expiring_notice', 0);
    });
});

function sessionContinue() {
    secondsInactive = 0;
    hasBeenWarned = false;
    $('#member-session-modal').modal('hide');
    $('#member-session-modal, .modal-backdrop').remove();
    modRequest.request('member.session.extend', null, null, function() { });
    $('title').text(title);

    setCookie('member_session_expiring_notice', 0);
}

function checkSession() {
    secondsInactive += 1;
    
    var otherTabsClockReset = getCookie('member_session_clock_reset');
    
    if (clockReset != otherTabsClockReset) {
        secondsInactive = 0;
        clockReset = otherTabsClockReset;
    }

    var warning = parseInt(sessionTimeoutWarningTimer);
    
    var show_warning = false;
    var otherTabsWarning = getCookie('member_session_expiring_notice');
    
    if (otherTabsWarning == '') {
        otherTabsWarning = 0;
    }
    
    if (!hasBeenWarned && secondsInactive >= warning) {
        setCookie('member_session_expiring_clock', secondsInactive);
        setCookie('member_session_expiring_notice', 1);
        show_warning = true;
    } else if (!hasBeenWarned && otherTabsWarning == 1) {
        show_warning = true;

        var otherTabsClock = getCookie('member_session_expiring_clock');
        if (otherTabsClock=='') {
            otherTabsClock = 0;
        }
         
        secondsInactive = parseInt(otherTabsClock);
    } else if (hasBeenWarned  && otherTabsWarning == 0) {
        show_warning = false;
        sessionContinue();
        return;
    }
    
    if (!hasBeenWarned && show_warning ) {
        hasBeenWarned = true;
        $('#member-session-modal, .modal-backdrop').remove();

        $('body').append(
            '<div class="modal fade" id="member-session-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\
                <div class="modal-dialog">\
                    <div class="modal-content">\
                        <div class="modal-header">\
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
                            <h4 class="modal-title">Inactivity Warning</h4>\
                        </div>\
                        <div class="modal-body">\
                                You will be logged out in\
                                <span class="clock" style="font-weight: bold">' + countdownTime + '</span>\
                                <br/><br/>Click "Continue" to extend your session.\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" id="continue_session" class="btn btn-primary">Continue</button>\
                        </div>\
                    </div>\
                </div>\
            </div>');

        $('#member-session-modal').on('hidden.bs.modal', function () {
            sessionContinue();
        });

        $('#member-session-modal').modal('show');
    }

    if (hasBeenWarned) {
        var time = timeout - secondsInactive;
        var minutes = Math.floor(time / 60);
        var seconds = time - minutes * 60;
        var clock = str_pad_left(minutes,'0',2)+':'+str_pad_left(seconds,'0',2);
        
        $('.clock').text( clock );

        if ( $('title').text()=='*' ) {
            $('title').text('EXPIRING IN ' + clock);
        } else {
            $('title').text('*');
        }
    }
    
    if (secondsInactive >= timeout) {
        clearInterval(sessionclock);
        
        modRequest.request('member.session.logoff', null, null, function() {
            window.location.reload();
        }, function() {
            window.location.reload();
        });
    }

    function str_pad_left(string,pad,length) {
        return (new Array(length+1).join(pad)+string).slice(-length);
    }
}