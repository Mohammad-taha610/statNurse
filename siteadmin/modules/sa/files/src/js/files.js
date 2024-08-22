var table;

$(document).ready(function() {

    $('.fileinput-button').click( function() {
        $('#uploadfilebutton').click();
    });

    setupDataTables();

    $('#uploadfilebutton').fileupload({
        url: '/files/upload',
        dataType: 'json',
        done: function (e, data) {

            table._fnAjaxUpdate()

            $('#progress .progress-bar').css(
                'width',
                '0%'
            ).hide();

            //createModal('/files/'+data.jqXHR.responseJSON.files.id+'/edit');

        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            ).show();
        }
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

} );

function setupDataTables() {

    if (table)
        table.fnDestroy();

    table = $('#files_table').dataTable( {
        "ajax": {
            "url": '/files/ajax/get_files',
            "data": function ( d ) {

                $('.filters input, .filters select').each( function() {

                    if ( $(this).attr('type')=='checkbox' )
                    {
                        if ($(this).is(':checked'))
                            d.search[ $(this).prop('id') ] = $(this).val();
                    }
                    else
                    {
                        d.search[ $(this).prop('id') ] = $(this).val();
                    }

                });
            }
        },
        "processing": true,
        "serverSide": true,
        "bFilter": false,
        "order": [[ 0, "asc" ]],
        "columnDefs": [
            {
                "targets": [2],
                "orderable": false
            },
            {
                "render": function ( data, type, row ) {
                    return '<div class="text-right">' +
                    '<a class="btn btn-xs btn-danger " title="Delete File" href="/files/'+row[2]+'/delete"  data-ajaxconfirm="true" data-callback="file-change"><i class="fa fa-trash-o"></i></a></div>';
                },
                "targets": 2
            }
        ]
    });

    //$("#files_table_paginate").before( '<div id="table_length"></div>' )
    //$('#files_table_length').detach().prependTo("#table_length");

    addModalCallback('file-change', function() {
        table._fnAjaxUpdate()
    });
}