@extends('master')
@section('site-container')
<h1>Notification History</h1>
<?=$self->subViewWithModel('member_notification_table', 'notification_list', array('days' => 60), 0)?>
@show()
