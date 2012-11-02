<?
	$usesWebMap = true;
	require('inc.header.php');

	$people = $SQL->Query('SELECT user.seen,user.twitter,user.avatar,twitter.avatar,COUNT(*) FROM squawks as twitter, squawkers as user WHERE user.avatar = twitter.avatar GROUP BY twitter.avatar ORDER BY COUNT(*) DESC LIMIT 50;');
?>

	<div id="navigationContainer">
		<div class="layoutContainer">

			<ul>
				<li><a href="./">What's Up?</a></li>
				<li><a href="./places">Places</a></li>
				<li class="active"><a href="./people">People</a></li>
				<li><a href="./statistics">Statistics</a></li>
				<li><a href="http://squawkdev.wordpress.com/" onclick="return openURL(this.href,'devblog');">Blog</a></li>
				<li><a href="./about">About</a></li>
			</ul>

		</div>
	</div>

	<div id="contentContainer">
		<div class="layoutContainer about">

			<h2>Top Squawkers</h2>

			<div id="mapContainer">

				<div id="slmap"></div>

				<ul id="squawkers">
<?
	$s = 0; $first = 0;
	foreach($people as $person) {
		$person['status'] = 'offline';
		if($person['seen'] >= $cutoff) $person['status'] = 'online';

		$person['latest'] = $SQL->Query("SELECT * FROM squawks WHERE avatar='{$person['avatar']}' ORDER BY posted DESC LIMIT 1;");
		$person['latest'] = $person['latest'][0];
		if($s==0) $first = $person['latest'];

		$postedtime = gmdate('M j g:ia', $person['latest']['posted']);

		$marker  = '<p class="marker">';
		$marker .= '<a href="./people.php?name=' . urlencode($person['avatar']) . '">' . $person['avatar'] . '</a> ' . $postedtime;
		$marker .= '<span><em>Latest:</em> ' . $person['latest']['message'] . '</span>';
		$marker .= 'Ranked #' . ($s+1) . ' &nbsp; &nbsp; ';
		$marker .= '<a href="secondlife://' . urlencode($person['latest']['region']) . '/' . $person['latest']['x'] . '/' . $person['latest']['y'] . '/' . $person['latest']['z'] . '">Teleport</a> &nbsp; ';
		$marker .= '<a href="./people.php?name=' . urlencode($person['avatar']) . '&squawk=' . $person['latest']['id'] . '">Permalink</a>';
		$marker .= '</p>';
?>
					<li value="<?=$person['COUNT(*)']?>">
						<a title="<?=$person['avatar']?> (<?=$person['status']?>)" href="./people?name=<?=urlencode($person['avatar'])?>" onmouseover="mapInstance.panOrRecenterToSLCoord(new SLPoint('<?=$person['latest']['region']?>',<?=$person['latest']['x']?>,<?=$person['latest']['y']?>), true);" class="<?=$person['status']?>"><img src="./ext.avatar.php?t=<?=$person['twitter']?>" /></a>
					</li>
					<script type="text/javascript">
						markers[<?=$s?>] = new Array(
							new Marker(fresh, new SLPoint('<?=$person['latest']['region']?>',<?=$person['latest']['x']?>,<?=$person['latest']['y']?>)),
							'<?=$marker?>'
						);
					</script>

<?
	$s++;
	if($s == 51) break;
	}
?>
				</ul>

				<script type="text/javascript">

					mapInstance = new SLMap(document.getElementById('slmap'));
					mapInstance.panOrRecenterToSLCoord(new SLPoint('<?=$first['region']?>',<?=$first['x']?>,<?=$first['y']?>), true);
					mapInstance.setCurrentZoomLevel(1);

					mapMatchSize('slmap', 'squawkers');
					mapRefreshMarkers();

				</script>

			</div>

		</div>
	</div>

<?require('inc.footer.php');?>