<?

	$usesWebMap = true;
	require('inc.header.php');

	$_regions = $SQL->Query('SELECT region,COUNT(region) FROM squawks GROUP BY region ORDER BY COUNT(region) DESC;');
	$regions = array();
	$first = 0;

	foreach($_regions as $region) {
		if($region['COUNT(region)'] <= 1) continue;
		$regions[$region['region']]['name'] = $region['region'];
		$regions[$region['region']]['count'] = $region['COUNT(region)'];
		$regions[$region['region']]['recent'] = $SQL->Query("SELECT * FROM squawks WHERE region='{$region['region']}' ORDER BY posted DESC LIMIT 1;");
		$regions[$region['region']]['recent'] = $regions[$region['region']]['recent'][0];
		if(!$first) $first = $regions[$region['region']];
	}

?>

	<div id="navigationContainer">
		<div class="layoutContainer">

			<ul>
				<li><a href="./">What's Up?</a></li>
				<li class="active"><a href="./places">Places</a></li>
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

				<ol id="places">
<?
	$r=0;
	foreach($regions as $place) {

		$marker  = '<p class="marker">';
		$marker .= '<strong><a href="secondlife://' . urlencode($place['name']) . '/' . $place['recent']['x'] . '/' . $place['recent']['y'] . '/' . $place['recent']['z'] . '">' . $place['name'] . '</a></strong>';
		$marker .= '<span>Ranked #' . ($r + 1) . ' with ' . $place['count'] . ' squawks.</span>';
		$marker .= 'Most recent was ' . Age($place['recent']['posted']) . ' by <a href="./people?name=' . urlencode($place['recent']['avatar']) . '">' . $place['recent']['avatar'] . '</a>';
		$marker .= '</p>';

?>
					<li><p><strong><a href="#" onClick="mapInstance.clickMarker(markers[<?=$r?>][0]);"><?=$place['name']?></a></strong> (<?=$place['count']?>)<br />Most recent by <a href="./people?name=<?=urlencode($place['recent']['avatar'])?>"><?=$place['recent']['avatar']?></a></p></li>
					<script type="text/javascript">
                        markers[<?=$r?>] = new Array(
                            new Marker(fresh, new SLPoint('<?=$place['name']?>', <?=$place['recent']['x']?>, <?=$place['recent']['y']?>)),
							'<?=$marker?>'
                        );
                    </script>
<?
		$r++;
		if($r == 11) break;
	}
?>
                </ol>

				<script type="text/javascript">

					mapInstance = new SLMap(document.getElementById('slmap'));
					mapInstance.panOrRecenterToSLCoord(new SLPoint('<?=$first['name']?>', <?=$first['recent']['x']?>, <?=$first['recent']['y']?>), true);
					mapInstance.setCurrentZoomLevel(1);

					mapMatchSize('slmap', 'places');
					mapRefreshMarkers();

				</script>

			</div>
		</div>
	</div>

<?require('inc.footer.php');?>