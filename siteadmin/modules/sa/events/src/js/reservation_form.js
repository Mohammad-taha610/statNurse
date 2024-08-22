function Reservation($c) {
    var $container;
    var $createReservationForm;
    var $cancelReservationForm;
    var $emailInput;
    var $notificationsContainer;

    _construct($c);

    function _construct() {
        _cacheDom($c);
        _bindEventHandlers();
    }

    function _cacheDom($c) {
        $container  = $c;
        $emailInput = $c.find('input[name="event-reservation-email"]');
        $createReservationForm = $c.find('form[data-action="add-reservation"]');
        $cancelReservationForm = $c.find('form[data-action="cancel-reservation"]');
        $notificationsContainer = $c.find('.notifications');
    }

    function _bindEventHandlers() {
        $createReservationForm.bind('click', reserve);
        $cancelReservationForm.bind('click', cancel);
    }

    function reserve() {
        _send(
            $createReservationForm,
            {"email": $emailInput.val()},
            {
                success: "Reservation complete!",
                error: "Failed to reserve this event."
            }
        );
        return false;
    }

    function cancel() {
        _send($cancelReservationForm,
            {"email": $emailInput.val()},
            {
                success: "Your reservation has been <b>canceled</b>.",
                error: "Failed to cancel reservation."
            });
        return false;
    }

    function _send(form, data, messages) {
        $container.find('.alert').remove();
        var $submitButton = form.find('button[type="submit"]');

        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: data,
            dataType: 'json',
            beforeSend: function() {
                $submitButton.prop('disable', true);
            },
            success: function(request) {
                if(request.success == 1) {
                    $notificationsContainer.prepend('<div class="alert alert-success">' + messages['success'] + '</div>');
                } else {
                    $notificationsContainer.prepend('<div class="alert alert-danger">' + request.errorMsg + '</div>');
                }
            },
            error: function() {
                $notificationsContainer.prepend('<div class="alert alert-success">' + messages['success'] + '</div>');
            },
            complete: function() {
                $submitButton.prop('disable', false);
            }
        });
    }
}

$(document).ready(function(){
    var r = new Reservation($('.event-reservation-form'));
    console.log(r);
});