@extends('master')
@section('site-container')
<div class="widget-box">
    <div class="widget-header">
        <h4>Test Route</h4>
    </div>

    <div class="widget-body">
        <div class="widget-main">
            <div class="row-fluid">
                <label for="form-field-8">
                    Route
                </label>
                <input type="text" class="form-control" value="" id="route-to-test" name="route-to-test" />
            </div>
            <div class="row-fluid">
                <label for="form-field-8">
                    Method
                </label>
                <select  class="form-control" id="method-to-test">
                    <option value="ANY">ANY</option>
                    <option value="POST">POST</option>
                    <option value="GET">GET</option>
                </select>
                <button class="btn btn-primary" id="btn-find">Find</button>
            </div>
        </div>
    </div>
</div>

<script>

    $('#btn-find').click( function() {

        var test_value = $('#route-to-test').val();
        var test_method = $('#method-to-test').val();

        if (test_value==='') {
            $('table tbody tr').show();
        }
        else {
            modRequest.request('developer.test_route', null, { 'route': test_value, 'method': test_method }, function(d) {

                $('table tbody tr').each( function() {

                    var idtext = $('td:first', this).text();

                    if (idtext!==d.route_info.id) {
                        $(this).hide();
                    }
                    else
                    {
                        $(this).show();
                    }

                })

            })
        }
    })
</script>
@view::table
@show