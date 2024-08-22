$(document).ready(function($) {

    $('body').on( 'click', '.confirm', function(e) {
        e.preventDefault();

        var message = 'Are you sure?';
        if ($(this).data('message')!='' && typeof($(this).data('message'))!='undefined')
            message = $(this).data('message');

        var href = $(this).attr('href');
        if ($(this).data('href')!='' && typeof($(this).data('href'))!='undefined')
            href = $(this).data('href');

        var functionName = $(this).data('callback');

        var obj = this;

        confirm(message, function() {

            if (typeof(functionName)!='undefined')
                window[functionName](obj);
            else
                window.location = href;

        });
    })

});

function confirm(message, callback) {

    bootbox.dialog({
        message: "<span class='bigger-110'>"+message+"</span>",
        buttons:
        {
            "OK" :
            {
                "label" : "<i class='icon-ok'></i> OK",
                "className" : "btn-sm btn-info",
                "callback": callback
            },
            "Cancel" :
            {
                "label" : "<i class='icon-times'></i> Cancel",
                "className" : "btn-sm btn-danger",
                "callback": function() { }
            }

        }
    });

}


function alert(message) {

    bootbox.dialog({
        message: "<span class='bigger-110'>"+message+"</span>",
        buttons:
        {
            "OK" :
            {
                "label" : "<i class='icon-ok'></i> OK",
                "className" : "btn-sm btn-success",
                "callback": function() {
                    //Example.show("great success");
                }
            }

        }
    });

}
