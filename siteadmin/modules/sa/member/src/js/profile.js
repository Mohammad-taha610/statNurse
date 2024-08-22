$(document).ready(function() {

    $('#country').change(function() {
        var value = $(this).val();


        loadStates(value);
    });



    loadStates( $('#country').val() );

});

function loadStates(country) {

    modRequest.request('system.location.states', null, { country: country }, function(data) {
        var html = '';
        if ( data === null || data.length == 0 ) {
            //do nothing
        } else {
            for ( var i = 0; i < data.length; i++ ) {
                $('#state').append($('<option>', {
                    value: data[i].id,
                    text: data[i].abbreviation+' - '+data[i].name
                }));
            }
        }

        var previous_state_selection = $('#state').data('previous_selection');
        if (previous_state_selection) {
            $('#state').val(previous_state_selection);
        }


    });

}
