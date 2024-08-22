var secondsInactive = 0;
var timeout = 600;
var clockReset = 0;
var hasBeenWarned = false;
var sessionclock = null;
var title = '';

$(document).ready( function() {

    title = $('title').text();

    // RESET CLOCK ON OTHER TABS
    clockReset = getCookie('sa_session_clock_reset');
    if (clockReset=='')
        clockReset = 0;
    clockReset++;
    setCookie('sa_session_clock_reset', clockReset);

    timeout = getCookie('sa_session_length') - 5;
    sessionContinue();

    sessionclock = setInterval( checkSession, 1000 );
    $('body').on('click', '#continue_session', function() {
        sessionContinue();
    });

});

function str_pad_left(string,pad,length) {
    return (new Array(length+1).join(pad)+string).slice(-length);
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length,c.length);
        }
    }
    return "";
}

function setCookie(cname, value, days) {
    //document.cookie = cname+"="+value;

    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }
    document.cookie = cname + "=" + value + expires + "; path=/";

}


function sessionContinue() {
    secondsInactive = 0;
    hasBeenWarned = false;
    $('#sessionmodal').modal('hide');
    $('#sessionmodal, .modal-backdrop').remove();
    modRequest.request('sa.session.extend', null, null, function() { });
    $('title').text(title);
    $('body').removeClass('modal-open');

    setCookie('sa_session_expiring_notice', 0);
}

function checkSession() {

    secondsInactive+=1;

    var otherTabsClockReset = getCookie('sa_session_clock_reset');
    if (clockReset != otherTabsClockReset) {
        secondsInactive = 0;
        clockReset = otherTabsClockReset;
    }

    var warning = timeout * .9;

    var show_warning = false;
    var otherTabsWarning = getCookie('sa_session_expiring_notice');
    if (otherTabsWarning=='')
        otherTabsWarning = 0;


    if (!hasBeenWarned && secondsInactive >= warning) {
        setCookie('sa_session_expiring_clock', secondsInactive);
        setCookie('sa_session_expiring_notice', 1);
        show_warning = true;
    }
    else if (!hasBeenWarned && otherTabsWarning==1) {
        show_warning = true;

        var otherTabsClock = getCookie('sa_session_expiring_clock');
        if (otherTabsClock=='')
            otherTabsClock = 0;

        secondsInactive = parseInt(otherTabsClock);
    }
    else if (hasBeenWarned  && otherTabsWarning==0) {
        show_warning = false;
        sessionContinue();
        return;
    }

    if (!hasBeenWarned && show_warning ) {

        hasBeenWarned = true;
        $('#sessionmodal, .modal-backdrop').remove();

        $('body').append(
            '<div class="modal fade" id="sessionmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\
                <div class="modal-dialog">\
                    <div class="modal-content">\
                        <div class="modal-header">\
                            <i style="font-size: 35px;" class="fa fa-clock-o"></i> Session Timeout\
                        </div>\
                        <div class="modal-body">\
                                Your Site Administrator session will expire in\
                                <div class="clock"></div>\
                                Please click "Continue" to keep working or <br> clock "Log Off" to end your session now.\
                        </div>\
                        <div class="modal-footer">\
                            <button type="button" class="btn btn-primary" id="continue_session" data-dismiss="sessionmodal">Continue</button>\
                            <a href="/siteadmin/logoff" class="btn btn-danger">Log Off</a>\
                        </div>\
                    </div>\
                    \
                </div>\
            </div>');

        $('#sessionmodal').on('hidden.bs.modal', function () {
            sessionContinue();
        })

        $('#sessionmodal').modal('show');
    }

    if (hasBeenWarned) {
        var time = timeout - secondsInactive;
        var minutes = Math.floor(time / 60);
        var seconds = time - minutes * 60;
        var clock = str_pad_left(minutes,'0',2)+':'+str_pad_left(seconds,'0',2);
        $('.clock').text( clock );

        if ( $('title').text()=='*' ) {
            $('title').text('EXPIRING IN ' + clock);
        }
        else {
            $('title').text('*');
        }
    }

    if (secondsInactive>=timeout) {

        clearInterval(sessionclock);

        $('#sessionmodal, .modal-backdrop').remove();
        $('body').append(
            '<div class="modal fade" id="sessionmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\
                <div class="modal-dialog">\
                    <div class="modal-content">\
                        <div class="modal-header">\
                            <i style="font-size: 35px;" class="fa fa-clock-o"></i> Session Timeout\
                        </div>\
                        <div class="modal-body">\
                                Your Site Administrator session has expired. Please log in to continue.\
                        </div>\
                        <div class="modal-footer">\
                            <a href="/siteadmin/login" class="btn btn-primary">Log In</a>\
                        </div>\
                    </div>\
                    \
                </div>\
            </div>');

        $('title').text('SESSION EXPIRED');
        $('#main-container').html('');
        $('#sessionmodal').modal('show');
        modRequest.request('sa.session.logoff', null, null, function() {});

        $('body').click( function() {
            window.location.href= '/siteadmin/login';
        });

    }

}