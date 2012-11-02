<?

	$usesWebMap = false;
	require('inc.header.php');

?>

	<div id="navigationContainer">
		<div class="layoutContainer">

			<ul>
				<li><a href="./">What's Up?</a></li>
				<li><a href="./places">Places</a></li>
				<li><a href="./people">People</a></li>
				<li class="active"><a href="./statistics">Statistics</a></li>
				<li><a href="http://squawkdev.wordpress.com/" onclick="return openURL(this.href,'devblog');">Blog</a></li>
				<li><a href="./about">About</a></li>
			</ul>

		</div>
	</div>

	<div id="contentContainer">
		<div class="layoutContainer">

			<p><em>In development. Check back soon.</em></p>

		</div>
	</div>

<?require('inc.footer.php');?>