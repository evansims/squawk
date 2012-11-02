<?

	import_request_variables('gp', 'query_');

	if(isset($query_tag)) {

		$tag = $query_tag;

		$tag = urldecode($tag);
		$tag = stripslashes($tag);
		$tag = strip_tags($tag);
		$tag = trim($tag);
		$tag = strtolower($tag);

	}

	if(!isset($tag) || !strlen($tag)) exit;

	require('inc.header.php');
	$people = $SQL->Query('SELECT * FROM tags WHERE data LIKE \'%,' . mysql_escape_string($tag) . ',%\' ORDER BY avatar LIMIT 100;');

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

            <h2>Tag Search: <?=$tag?></h2>

<? if($people) { ?>
			<ul id="matches">
<?	foreach($people as $person) {
				$person = $SQL->Query('SELECT * FROM squawkers WHERE avatar=\'' . $person['avatar'] . '\' LIMIT 1');
				if($person) {
					$person = $person[0];
					$person['status'] = 'offline';
					if($person['seen'] >= $cutoff) $person['status'] = 'online';
				?>
				<li><a title="<?=$person['avatar']?> (<?=$person['status']?>)" href="./people?name=<?=urlencode($person['avatar'])?>" class="<?=$person['status']?>"><img src="./ext.avatar.php?t=<?=$person['twitter']?>" /></a></li>
<?	} } ?>
			</ul>
<? } else { ?>
			<p>Your search didn't yield any results. Nobody has the tag '<?=$tag?>' in their profile.</p>
<? } ?>

			<p><a href="./people">Back to People</a></p>

		</div>
	</div>

<?require('inc.footer.php');?>