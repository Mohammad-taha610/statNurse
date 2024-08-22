<!--Todo: This is completely broken and I don't even know where to start-->
@extends('master')
@section('site-container')
<script type="text/javascript">
	$('body').ready(function($) {

		$('.standalone').click(function(e) {
			e.preventDefault();
			window.open("@url('sa_developer_unit_testing')?standalone=1&uid=<?=$uid?>", "MsgWindow", "width=700, height=800");
		});

        $('.single-test').click(function(e) {
            e.preventDefault();
            executeTest();
        });

        executeTest();
	});

	function executeTest() {
        $('.test-summary-status').html("<div class='text-center'><i class='fa fa-circle-o-notch fa-spin fa-3x fa-fw'></i></div>");
        $('.test-summary-widget').removeClass('alert').removeClass('alert-danger');
        $('.test-summary-info').empty();

        modRequest.request('developer.execute.unittesting', null, null, function(d) {

            <?php if ($refresh) { ?>
                setTimeout( executeTest, 30000 );
            <?php } ?>

            $('#test-results-container').show();

            if (d.test_fail > 0) {
                $('.test-summary-status').html('<i class="fa fa-exclamation-triangle red"></i> <strong>'+d.test_fail+' Test Failed</strong>');
                $('.test-summary-widget').addClass('alert').addClass('alert-danger');
            }
            else {
                $('.test-summary-status').html('<i class="fa fa-check-square-o green"></i> <strong>All Test Passed</strong>');
                $('.test-summary-widget').removeClass('alert').removeClass('alert-danger');
            }

            $('.test-summary-info').html('<div class="col-xs-12 col-sm-3">\
                <i class="fa fa-clock-o black bigger-150"></i> <strong>Test Time:</strong> '+d.datetime+'\
                </div>\
                <div class="col-xs-12 col-sm-3">\
                    <i class="fa fa-check-circle-o blue bigger-150"></i> <strong>Test Run:</strong> '+d.test_num+'\
                </div>\
                <div class="col-xs-12 col-sm-3">\
                    <i class="fa fa-clock-o green bigger-150"></i> <strong>Assertions:</strong> '+d.test_assertions+'\
                </div>\
                <div class="col-xs-12 col-sm-3">\
                    <i class="fa fa-warning red bigger-150"></i> <strong>Test Failed:</strong> '+d.test_fail+'\
                </div>');


            $('#test-results-container').prepend(
                '<div class="row test-result">\
                    <div class="col-xs-12 col-sm-12 widget-container-span ui-sortable">\
                        <div class="widget-box">\
                            <div class="widget-header ">\
                                <h5>Detailed Results</h5>\
                                <div class="widget-toolbar">\
                                    <span style="padding-left: 15px"><i class="fa fa-clock-o black bigger-150"></i> <strong>Test Time:</strong> '+d.datetime+'</span>\
                                    <span style="padding-left: 15px"><i class="fa fa-check-circle-o blue bigger-150"></i> <strong>Test Run:</strong> '+d.test_num+'</span>\
                                    <span style="padding-left: 15px"><i class="fa fa-clock-o green bigger-150"></i> <strong>Assertions:</strong> '+d.test_assertions+'</span>\
                                    <span style="padding-left: 15px"><i class="fa fa-warning red bigger-150"></i> <strong>Test Failed:</strong> '+d.test_fail+'</span>\
                                </div>\
                            </div>\
                            <div class="widget-body">\
                                <div class="widget-body-inner" style="display: block;">\
                                    <div class="widget-main test-results">\
                                        <div class="row">\
                                            <div class="col-xs-12 col-sm-3">\
                                                <div style="padding-top: 15px">'+d.report+'</div>\
                                            </div>\
                                        </div>\
                                    </div>\
                                 </div>\
                            </div>\
                        </div>\
                    </div>\
                </div>'
            );

            $("#test-results-container").find(".test-result:gt(5)").remove();
        })
    }
</script>

<div class="row">
	<div class="col-xs-12 col-sm-12 widget-container-span ui-sortable">
		<div class="widget-box">
			<div class="widget-header">
				<h5>Last Test Summary</h5>

				<div class="widget-toolbar">
                    <?php if (! $refresh) { ?>
                    <button class="btn btn-primary btn-xs single-test">
                        <i class="fa fa-external-link"></i> Execute Test
                    </button>
                    <button class="btn btn-primary btn-xs standalone">
                        <i class="fa fa-external-link"></i> Launch Repeating Test
                    </button>
                    <?php } ?>
				</div>
			</div>

			<div class="widget-body"><div class="widget-body-inner" style="display: block;">
				<div class=" test-summary-widget widget-main">
					<div class="row">
						<div class="col-xs-12 col-sm-12  bigger-200 test-summary-status" >
                            Click "<a href="#" class="single-test">Execute Test</a>" to start a test.
						</div>
					</div>
					<div class="row test-summary-info" style="margin-top:20px">

					</div>
				</div>
			</div></div>
		</div>
	</div>
</div>

<div class="" id="test-results-container">

</div>

<?php if (! $refresh) { ?>

<div class="row">
	<div class="col-xs-12 col-sm-12 widget-container-span ui-sortable">
		<div class="widget-box">
			<div class="widget-header">
				<h5>Previous 5 Failed Test</h5>

				<div class="widget-toolbar">

				</div>
			</div>

			<div class="widget-body"><div class="widget-body-inner" style="display: block;">
				<div class="widget-main">
					<?= $previousreport ?>
				</div>
			</div></div>
		</div>
	</div>
</div>

<?php } ?>
@show