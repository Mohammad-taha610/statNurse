<?php  
$assetfolder = '/themes/acetheme/assets';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?=!empty($page_name) ? $page_name : 'Site Administrator'?></title>
		<meta name="description" content="overview &amp; stats">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="robots" content="NOINDEX,NOFOLLOW,NOARCHIVE,NOSNIPPET">

        <link rel="apple-touch-icon" sizes="57x57" href="<?=$assetfolder?>/images/apple-touch-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="<?=$assetfolder?>/images/apple-touch-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?=$assetfolder?>/images/apple-touch-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?=$assetfolder?>/images/apple-touch-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="<?=$assetfolder?>/images/apple-touch-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?=$assetfolder?>/images/apple-touch-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="<?=$assetfolder?>/images/apple-touch-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?=$assetfolder?>/images/apple-touch-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="<?=$assetfolder?>/images/apple-touch-icon-180x180.png">
        <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-96x96.png" sizes="96x96">
        <link rel="icon" type="image/png" href="<?=$assetfolder?>/images/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="<?=$assetfolder?>/images/manifest.json">
        <meta name="msapplication-TileColor" content="#438eb9">
        <meta name="msapplication-TileImage" content="<?=$assetfolder?>/images/mstile-144x144.png">
        <meta name="theme-color" content="#ffffff">

		<!-- basic styles -->
		<link rel="stylesheet" href="<?=$assetfolder?>/css/bootstrap.css">

		<link rel="stylesheet" href="<?=$assetfolder?>/css/jquery.growl.css">

		<!-- fonts -->
		<link rel="stylesheet" href="<?=$assetfolder?>/css/ace-fonts.css">

		<!-- ace styles -->
		<link rel="stylesheet" href="<?=$assetfolder?>/css/ace.css">

		<link rel="stylesheet" href="<?=$assetfolder?>/css/stylesheet.css">
		<link rel="stylesheet" href="<?=$assetfolder?>/css/sa-print.css">
		
		<script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
        <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/v4-shims.js"></script>

		<script src="<?=$assetfolder?>/js/jquery.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script>


        <script src="<?=$assetfolder?>/js/bootstrap.min.js"></script>
		<script src="<?=$assetfolder?>/js/ace.min.js"></script>
		<script src="<?=$assetfolder?>/js/ace2.min.js"></script>
		<script src="<?=$assetfolder?>/js/bootbox.min.js"></script>
		<script src="<?=$assetfolder?>/js/jquery.growl.js"></script>
		<script src="<?=$assetfolder?>/js/jquery-ui.min.js"></script>
		<script src="<?=$assetfolder?>/js/custom.js"></script>
		<script src="/siteadmin/system/js/activity-monitor.js"></script>

        <script src="<?=$assetfolder?>/ckeditor/ckeditor.js"></script>
</head>
<body>
