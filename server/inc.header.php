<?

	require_once('inc.gzip.start.php');
	require_once('inc.sql.php');

	$embedded = false;
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Second Life') !== false){ $embedded = true; }

	$cutoff = TimePDT() - 301;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<title>Squawk: Connects Second Life up with Twitter and Jaiku.</title>
	<meta name="verify-v1" content="N6PIz+hvnj5yz3GyOBhlpSg/eJlgCOnf7R8PHB52mk8=" />

	<link rel="shortcut icon" href="./favicon.ico" />

	<link rel="stylesheet" type="text/css" href="main.css" />

	<?if(isset($rss)){?><link rel="alternate" type="application/rss+xml" title="<?=$rss?>'s Recent Squawks (RSS 2.0)" href="./rss?name=<?=urlencode($rss)?>"><? echo "\n"; }?>
	<link rel="alternate" type="application/rss+xml" title="Recent Squawks (RSS 2.0)" href="./rss">

<?if(isset($usesWebMap)){?>
	<script src="http://secondlife.com/apps/mapapi/" type="text/javascript"></script>
<?}?>
	<script src="./ue.js" type="text/javascript"></script>

	<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script>
	<script type="text/javascript">
		_uacct = "UA-1112337-4";
		urchinTracker();
	</script>

</head>

<body>

<?if(!$embedded){?>
	<div id="headerContainer">
		<div class="layoutContainer">

			<h1><a href="./"><span class="hidden">Squawk: Don't Be a Stranger</span></a></h1>
			<p class="hidden">Squawk is a powerful little tool for connecting the Second Life metaverse up with popular web services like Twitter, Jaiku and Flickr. Share the adventures and experiences of your Second Life with the friends and family of your first, and see just how easy it is to make new ones.</p>

		</div>
	</div>
<?}?>
