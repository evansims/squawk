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
				<li><a href="./statistics">Statistics</a></li>
				<li><a href="http://squawkdev.wordpress.com/" onclick="return openURL(this.href,'devblog');">Blog</a></li>
				<li class="active"><a href="./about">About</a></li>
			</ul>

		</div>
	</div>

	<div id="contentContainer">
		<div class="layoutContainer about">

        	<h2>What's Squawk?</h2>
            <p>Squawk is a tool for the metaverse <a href="http://www.secondlife.com">Second Life</a>, and allows you to connect your client up with services like the growingly popular <a href="http://www.twitter.com">Twitter</a> and <a href="http://www.jaiku.com">Jaiku</a> and share your experiences with friends, family and strangers alike. Squawk makes it easy to let your friends know whats up and how to find you inworld, and because it connects up with the social networking functions of <a href="http://www.squawknest.com">Squawk Nest</a> (our website) it makes it a breeze to meet interesting new folks and check out exciting new locations.</p>

            <p>Squawk is <strong>free</strong>, but donations are greatly appreciated. Special functionality is also limited to donators. Grab a copy from our <a href="http://slurl.com/secondlife/Minoa/80/98/106">storefront</a>, or at our <a href="http://www.slexchange.com/modules.php?name=Marketplace&file=item&ItemID=214292" onclick="return openURL(this.href,\'slexchange\');">SLExchange page</a>.</p>

			<div id="serviceLogos">
				<a href="http://www.twitter.com"><img src="./images/100px_twitter.jpg" /></a>
				<a href="http://www.jaiku.com"><img src="./images/100px_jaiku.jpg" /></a>
				<a href="http://www.tumblr.com"><img src="./images/100px_tumblr.jpg" /></a>
			</div>

			<h2>Features</h2>
			<ul id="featuresList">
				<li>Post presence updates to Twitter.</li>
				<li>Receive friend updates from Twitter.</li>
				<li>Post presence updates to Jaiku.</li>
				<li>Post blog entries to Tumblr.</li>
			</ul>

            <h2>In the News</h2>
            <div style="width: 50%; float: left;">
            <p><strong>Mashable!</strong><br />
               <a href="http://mashable.com/2007/05/08/jaiku-tools/">Jaiku Rocks! 19 Cool Jaiku Tools</a> 05/08/2007</p>
            </div>

            <p>Nick Wilson's <strong>Metaversed.com</strong><br />
               <a href="http://www.metaversed.com/05-may-2007/jaiku-second-life">Jaiku in Second Life</a> 05/05/2007<br />
               <a href="http://www.metaversed.com/03-may-2007/squawk-power-users-twitter-client-second-life">Squawk: A Power Users Twitter Client for Second Life</a> 05/03/2007</p>

            <h2 style="clear:both; margin: 1.5em 0 .5em 0;">FAQ</h2>
            <p><strong>Q: Does Squawk require a Twitter account?</strong><br />
               <strong>A:</strong> You can chose to disable Twitter functionality inside Squawk's configuration. Doing so will still submit your squawks to the <a href="http://www.squawknest.com">Nest</a>. However, your presence in the Nest will be fairly limited as it relies upon your Twitter profile to permit certain functionality (such as avatars). This is a limitation we hope to fix in time, but we have no alternatives at the moment.</p>

            <p><strong>Q: Squawk seems like an invasion of privacy.</strong><br />
               <strong>A:</strong> If you're already broadcasting your life on services like Twitter, you can't honestly expect much in the way of privacy can you? If you don't care to have your presence aggregated by the Squawk Nest, simply disable it's communication by setting the appropriate variable in your Squawk configuration file.</p>

            <p><strong>Q: Why isn't my Twitter avatar showing up?</strong><br />
               <strong>A:</strong> This can occur if Twitter is down for maintenance, or your Twitter account is set to a high level of privacy. If the Twitter site is down, just try again later. Squawk will continue to try to grab your avatar. If your account is set to limit access to your tweets, just add the <a href="http://twitter.com/squawk/">Squawk Users</a> account to your friends list to fix it.</p>

            <p><strong>Q: I changed my Twitter avatar, but it's not showing up on the website.</strong><br />
               <strong>A:</strong> For bandwidth reasons, Twitter avatars are cached for a period of 24 hours. The Squawk website will refresh your avatar within a day, so check back later.</p>

            <p><strong>Q: Why do you require donations for some features?</strong><br />
               <strong>A:</strong> This was a difficult decision to make, but it was necessary. The features put extra load on our web servers, which has the potential of pushing us over our provider's monthly limits and costing <em>us</em>. These donations offset these costs, and in the event that we don't run over our limits simply goes towards funding our storefront as well as further development of Squawk. We really appreciate your support!</p>

            <p><strong>Q: I'm not receiving updates from my friends on Jaiku; what gives?</strong><br />
               <strong>A:</strong> Notification support is in the works for Jaiku and should be available in the coming weeks.</p>

		</div>
	</div>

<?require('inc.footer.php');?>