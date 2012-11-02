<?
	$usesWebMap = true;
	require('inc.header.php');
?>

	<div id="navigationContainer">
		<div class="layoutContainer">

			<ul>
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

				<ul id="squawks">
<?

	$squawkers = array();
	$_squawkers = $SQL->Query('SELECT * FROM squawkers ORDER BY seen DESC;');
	for($s = 0; $s < count($_squawkers); $s++) {
		$_squawkers[$s]['online'] = false;
		if($_squawkers[$s]['seen'] >= $cutoff) { $_squawkers[$s]['online'] = true; }
		$squawkers[$_squawkers[$s]['avatar']] = $_squawkers[$s];
	}
	unset($_squawkers);

	$squawks = $SQL->Query("SELECT * FROM squawks WHERE message NOT LIKE '@%' ORDER BY posted DESC LIMIT 12;");

	$milestones = array(array('Fresh', 0), array('Today', 7200), array('Yesterday', 86400), array('This Week', 172800), array('Older', 604800));
	$activestone = -1; $laststone = -1;

	$s = 0;
	foreach($squawks as $squawk) {

		if(substr_count($squawk['message'], ' ') < 2) continue;

		$diff = TimePDT() - $squawk['posted'];

		for($m = 0; $m < count($milestones); $m++) {
			if($activestone < $m) {
				if($diff >= $milestones[$m][1]) {
					$activestone = $m;
				}
			}
		}

		if($activestone != $laststone) {
			$laststone = $activestone;
?>
					<li class="timestamp"><?=$milestones[$activestone][0]?></li>
<?
		}

		$prefix = '';
		$freshness = 'stale';

		$donator = false;
		if(isset($donators[$squawk['avatar']])) $donator = true;

		if($activestone == 0) {
			$freshness = 'fresh';
			$postedtime = Age($squawk['posted']);
		} elseif ($activestone == 1) {
			$postedtime = gmdate('g:ia', $squawk['posted']);
		} elseif ($activestone == 2) {
			$prefix = 'Yesterday at ';
			$postedtime = gmdate('D g:ia', $squawk['posted']);
		} else {
			$postedtime = gmdate('M j g:ia', $squawk['posted']);
		}

		$status = 'Offline';
		if($squawkers[$squawk['avatar']]['online']) $status = 'Online';

		$marker  = '<p class="marker">';
		$marker .= '<span><a href="./people.php?name=' . urlencode($squawk['avatar']) . '">' . $squawk['avatar'] . '</a> ' . $prefix . $postedtime  . '</span>';
		$marker .= $squawk['message'];
		$marker .= '<span><a href="secondlife://' . urlencode($squawk['region']) . '/' . $squawk['x'] . '/' . $squawk['y'] . '/' . $squawk['z'] . '">Teleport</a> &nbsp; ';
		$marker .= '<a href="./people.php?name=' . urlencode($squawk['avatar']) . '&squawk=' . $squawk['id'] . '">Permalink</a>';
		$marker .= '</span></p>';

?>

					<li><a title="<?=$squawk['avatar']?> (<?=$status?>)" href="./people?name=<?=urlencode($squawk['avatar'])?>" class="<?=$status?>"><img src="./ext.avatar.php?t=<?=$squawkers[$squawk['avatar']]['twitter']?>" /></a>
						<p><a href="#" onclick="mapInstance.clickMarker(markers[<?=$s?>][0]);"><?=stripslashes($squawk['message'])?>
						<span class="time" title="Time in PDT. <?=gmdate('B', $squawk['posted'])?> in Swatch Internet time."><?=$postedtime?></span></a></p>
					</li>
					<script type="text/javascript">
                        markers[<?=$s?>] = new Array(
                            new Marker(<?=$freshness?>, new SLPoint('<?=$squawk['region']?>',<?=$squawk['x']?>,<?=$squawk['y']?>)),
                            '<?=$marker?>'
                        );
                    </script>

<?

	$s++;
	}

?>
					<li class="stats"><a href="./rss"><img src="./images/feed-icon-10x10.jpg" /></a> <?=$stats['today']?> squawks today. <?=$stats['squawks']?> to date.</li>
				</ul>

				<script type="text/javascript">

					mapInstance = new SLMap(document.getElementById('slmap'));
					mapInstance.panOrRecenterToSLCoord(new SLPoint('<?=$squawks[0]['region']?>',<?=$squawks[0]['x']?>,<?=$squawks[0]['y']?>), true);
					mapInstance.setCurrentZoomLevel(1);

					mapMatchSize('slmap', 'squawks');
					mapRefreshMarkers();

				</script>

			</div>

            <div id="mapFooter">

            	<p><a href="./about">Learn more about Squawk</a>, and grab your copy from our <a href="http://slurl.com/secondlife/Lythria/138/70/45">storefront</a> or <a href="http://www.slexchange.com/modules.php?name=Marketplace&file=item&ItemID=214292" onclick="return openURL(this.href,\'slexchange\');">SLExchange</a> and start connecting your Second Life up with your first. It's easy and free.</p>

                <p><strong>Special thanks to our supporters:</strong> <?$d = ''; foreach($donators as $donator){$d .= '<a href="./people?name=' . urlencode($donator['avatar']) . '">' . $donator['avatar'] . '</a>, ';} $d = substr($d, 0, -2); echo $d; ?>. You can donate
                   at our tip jars located at any of our Squawk outlets. Donations help keep Squawk development alive, and help pay the web hosting bills.</p>

            </div>

		</div>
	</div>

<?require('inc.footer.php');?>