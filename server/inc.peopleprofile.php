<?

	import_request_variables('gp', 'query_');

	$query_name = urldecode($query_name);
	$rss = $query_name;

	$usesWebMap = true;
	require('inc.header.php');

	$person = $SQL->Query('SELECT * FROM squawkers WHERE avatar=\'' . mysql_escape_string($query_name) . '\' LIMIT 1;');
	if(!$person) {
		@header('HTTP/1.1 307 Temporary Redirect');
		@header('Location: http://www.squawknest.com/people');
		echo 'Unknown user.';
		exit;
	}
	$person = $person[0];
	$tags['twitter'] = strtolower($person['twitter']);

	$online = false;
	if($person['seen'] >= $cutoff) $online = true;

	$squawks = $SQL->Query('SELECT * FROM squawks WHERE avatar=\'' . mysql_escape_string($query_name) . '\';');

	$donator = $SQL->Query('SELECT * FROM donators WHERE avatar=\'' . mysql_escape_string($query_name) . '\';');
	if($donator) { $donator = true; } else { $donator = false; }

	$tags = $SQL->Query('SELECT * FROM tags WHERE avatar=\'' . mysql_escape_string($query_name) . '\' LIMIT 1;');
	if($tags) { $tags = $tags[0]; } else { $tags['built'] = 0; $tags['data'] = array(); }

	if($tags['built'] < (TimePDT() - 14400)) {
		$_tags = array();
		$_tags['name'] = strtolower(substr($query_name, (strrpos($query_name, ' ')+1)));
		foreach($squawks as $squawk) {

			if(!isset($_tags[$squawk['region']])) $_tags[$squawk['region']] = strtolower($squawk['region']);
			$__tags = explode(' ', $squawk['message']);

			foreach($__tags as $__tag) {
				if(substr_count($__tag, '@') != 0) continue;
				if(strlen($__tag) <= 3) continue;
				if(strlen($__tag) >= 7 && substr($__tag, 0, 7) == 'http://') continue;
				$__tag = strtolower($__tag);
				$__tag = trim(str_replace(array("\n", "\r", '.', '(', ')', '-', ',', '\\', '\'', '/', '?', '!', ':', '*', '"'), '', $__tag));
				if(!strlen($__tag)) continue;
				if($__tag != 'from' && $__tag != 'second' && $__tag != 'life' && $__tag != 'just' && $__tag != 'met' && $__tag != 'now' && $__tag != 'all' &&
				   $__tag != 'that' && $__tag != 'with' && $__tag != 'have' && $__tag != 'says' && $__tag != 'list' && $__tag != 'because' && $__tag != 'client' &&
				   $__tag != 'might' && $__tag != 'dont' && $__tag != 'cash' && $__tag != 'this' && $__tag != 'lots' && $__tag != 'info' && $__tag != 'site' &&
				   $__tag != 'grab' && $__tag != 'shop' && $__tag != 'shops' && $__tag != 'deal' && $__tag != 'look' && $__tag != 'test' && $__tag != 'testing' &&
				   $__tag != 'club' && $__tag != 'will' && $__tag != 'build' && $__tag != 'right' && $__tag != 'left' && $__tag != 'here' && $__tag != 'find' &&
				   $__tag != 'mate' && $__tag != 'cant' && $__tag != 'even' && $__tag != 'were' && $__tag != 'done' && $__tag != 'well' && $__tag != 'they' &&
				   $__tag != 'while' && $__tag != 'going' && $__tag != 'shit' && $__tag != 'fuck' && $__tag != 'very' && $__tag != 'there' && $__tag != 'first' &&
				   $__tag != 'youre' && $__tag != 'should' && $__tag != 'thats' && $__tag != 'them' && $__tag != 'then' && $__tag != 'over' && $__tag != 'really' &&
				   $__tag != 'time' && $__tag != 'follow' && $__tag != 'like' && $__tag != 'hate' && $__tag != 'your' && $__tag != 'where' && $__tag != 'nice' &&
				   $__tag != 'after' && $__tag != 'much' && $__tag != 'read' && $__tag != 'some' && $__tag != 'these' && $__tag != 'send' && $__tag != 'about'
				  ) {
					if(strlen($__tag) > 3 && !isset($__tags[$__tag])) {
						$_tags[$__tag] = $__tag;
					}
				}
			}
		}

		shuffle($_tags);
		$_tags = ',' . implode(',', $_tags) . ',';

		if(isset($tags['avatar'])) {
			$SQL->Query('UPDATE tags SET built=\'' . TimePDT() . '\', data=\'' . $_tags . '\' WHERE avatar=\'' . mysql_escape_string($query_name) . '\' LIMIT 1;');
		} else {
			$SQL->Query('INSERT INTO tags (avatar,built,data) VALUES(\'' . mysql_escape_string($query_name) . '\',\'' . TimePDT() . '\',\'' . $_tags . '\');');
		}

		$tags['data'] = $_tags;
	}

	$tags = explode(',', substr($tags['data'], 1, -1));

?>

	<?if(!$embedded){?>
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
	<?}?>

	<div id="contentContainer">
		<div class="layoutContainer">

			<?if(!$embedded){?>

			<p class="back"><a href="./people">Back to People</a></p>
            <h2><img <?if($online){?>class="online"<?}?> src="./ext.avatar.php?t=<?=$person['twitter']?>" /> <span><?=$person['avatar']?></span></h2>

			<?if($squawks){?>

			<div id="profileMap">
				<div id="slmap"></div>

				<script type="text/javascript">

<?					$s = 0; $permasquawk = null;
					foreach($squawks as $squawk) {

						$postedtime = gmdate('M j g:ia', $squawk['posted']);

						$freshness = 'stale';
						if(TimePDT() - $squawk['posted'] < 7200) $freshness = 'fresh';

						$marker  = '<p class="marker">';
						$marker .= '<span>' . $postedtime  . '</span>';
						$marker .= ucfirst(addslashes(stripslashes($squawk['message'])));
						$marker .= '<span><a href="secondlife://' . urlencode($squawk['region']) . '/' . $squawk['x'] . '/' . $squawk['y'] . '/' . $squawk['z'] . '">Teleport</a> &nbsp; ';
						$marker .= '<a href="./people?name=' . urlencode($squawk['avatar']) . '&squawk=' . $squawk['id'] . '">Permalink</a>';
						$marker .= '</span></p>';
?>
					markers[<?=$s?>] = new Array(
						new Marker(<?=$freshness?>, new SLPoint('<?=$squawk['region']?>',<?=$squawk['x']?>,<?=$squawk['y']?>)),
						'<?=$marker?>'
					);
<?
					if(isset($query_squawk) && !$permasquawk) {
						if($squawk['id'] == $query_squawk) {
							$permasquawk = $s;
						}
					}

					$s++; }
?>

					mapInstance = new SLMap(document.getElementById('slmap'));

<?	if($permasquawk) { ?>
					mapInstance.panOrRecenterToSLCoord(new SLPoint('<?=$squawks[$permasquawk]['region']?>', <?=$squawks[$permasquawk]['x']?>, <?=$squawks[$permasquawk]['y']?>), true);
<?	} else { ?>
					mapInstance.panOrRecenterToSLCoord(new SLPoint('<?=$squawks[0]['region']?>', <?=$squawks[0]['x']?>, <?=$squawks[0]['y']?>), true);
<?	} ?>
					mapInstance.setCurrentZoomLevel(2);

					mapRefreshMarkers();
<?	if($permasquawk) { ?>					mapInstance.clickMarker(markers[<?=$permasquawk?>][0]);<?}?>

				</script>
			</div>

            <?} }?>

            <div class="profileLeft" <?if($embedded){?>style="clear: both; padding: 0; margin: 0; width: auto;"<?}?>>

<?if($online){?>				<p><strong class="online">Online Now</strong></p><?}?>

				<p><strong>Squawks to Date:</strong> <?=count($squawks);?><br />
				   <strong>Most Recent:</strong> <a href="#" onclick="mapInstance.clickMarker(markers[<?=(count($squawks)-1)?>][0]);"><?=gmdate('l, F j Y g:ia', $squawks[(count($squawks)-1)]['posted']);?></a><br />
				   <strong>First:</strong> <a href="#" onclick="mapInstance.clickMarker(markers[0][0]);"><?=gmdate('l, F j Y g:ia', $squawks[0]['posted']);?></a><br />
				   <a href="./rss?name=<?=urlencode($query_name)?>"><img class="rss" src="./images/feed-icon-16x16.jpg" /> Personal RSS Feed</a></p>

				<p id="profileServices">
				   <?if(strlen($person['twitter'])){?><a href="http://twitter.com/<?=$person['twitter']?>" onclick="return openURL(this.href,\'twitter\');"><img src="./images/50px_twitter.jpg" /> <?=substr($person['avatar'], 0, strpos($person['avatar'], ' '))?>'s Twitter</a><br /><?}?>
				   <?if(strlen($person['jaiku'])){?><a href="http://<?=$person['jaiku']?>.jaiku.com/" onclick="return openURL(this.href,\'jaiku\');"><img src="./images/50px_jaiku.jpg" /> <?=substr($person['avatar'], 0, strpos($person['avatar'], ' '))?>'s Jaiku</a><br /><?}?>
				   <?if(strlen($person['tumblr'])){?><a href="http://<?=$person['tumblr']?>.tumblr.com/" onclick="return openURL(this.href,\'tumblr\');"><img src="./images/50px_tumblr.jpg" /> <?=substr($person['avatar'], 0, strpos($person['avatar'], ' '))?>'s Tumblr</a><br /><?}?>
				</p>

			</div>

			<div class="profileRight" <?if($embedded){?>style="clear: both; padding: 0; margin: 2em 0 0 0; width: auto;"<?}?>>

<? if($donator){?>
				<p><strong class="supporter">Squawk Supporter - Thanks!</strong></p>
<?}?>

				<?if(!$embedded){?><p><strong>Tags:</strong> <? $t = ''; foreach($tags as $tag) { $t .= '<a href="./search?tag=' . urlencode($tag) . '" rel="tag">' . $tag . '</a>, '; } echo substr($t, 0, -2); ?></p><?}?>

            </div>

		</div>
	</div>

<?require('inc.footer.php');?>