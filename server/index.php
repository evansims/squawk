<?
	$usesWebMap = true;
	require('inc.header.php');
?>

	<div id="navigationContainer">
		<div class="layoutContainer">

			<ul>
				<li><?=gmdate('g:ia', TimePDT())?> SLT</li>
				<li class="active"><a href="./">What's Up?</a></li>
				<li><a href="./places">Places</a></li>
				<li><a href="./people">People</a></li>
				<li><a href="./statistics">Statistics</a></li>
				<li><a href="http://squawkdev.wordpress.com/" onclick="return openURL(this.href,'devblog');">Blog</a></li>
				<li><a href="./about">About</a></li>
			</ul>

		</div>
	</div>

	<div id="contentContainer">
		<div class="layoutContainer">

			<div id="mapContainer">

				<div id="slmap"></div>
				<ul id="squawks"></ul>

				<script type="text/javascript" src="./ajax.index"></script>

			</div>

            <div id="mapFooter">

            	<p><a href="./about">Learn more about Squawk</a>, or go ahead and grab your own copy from our <a href="http://slurl.com/secondlife/Minoa/80/98/106">inworld store</a>, or <a href="http://www.slexchange.com/modules.php?name=Marketplace&file=item&ItemID=214292" onclick="return openURL(this.href,\'slexchange\');">SLExchange</a>. Start connecting your Second Life up with your first. It's easy and free.</p>

<?
	$donators = array();
	$_donators = $SQL->Query('SELECT *FROM donators ORDER BY avatar');
	for($d = 0; $d < count($_donators); $d++) {
		$donators[$_donators[$d]['avatar']] = $_donators[$d];
	}
	unset($_donators);
?>

                <p><strong>Special thanks to our supporters:</strong> <?$d = ''; foreach($donators as $donator){$d .= '<a href="./people?name=' . urlencode($donator['avatar']) . '">' . $donator['avatar'] . '</a>, ';} $d = substr($d, 0, -2); echo $d; ?>. You can donate
                   using the tip jars located at any of our Squawk outlets. Donations keep Squawk development alive, and help pay the web hosting bills.</p>

            </div>

		</div>
	</div>

<?require('inc.footer.php');?>