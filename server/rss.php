<?
	header('Content-Type: application/xml');
	import_request_variables('gp', 'query_');

	require_once('./inc.sql.php');

	if(isset($query_name)) {
		$query_name = mysql_escape_string(trim(urldecode($query_name)));
		$user = $SQL->Query("SELECT * FROM squawkers WHERE avatar='{$query_name}' LIMIT 1;");
		if(!$user) { header('HTTP/1.1 404 File Not Found'); echo 'User not found.'; exit; } else { $user = $user[0]; }
		$squawks = $SQL->Query("SELECT * FROM squawks WHERE avatar='{$query_name}' ORDER BY posted DESC LIMIT 50;");

		$title = 'Recent Squawks by ' . $user['avatar'];
		$desc = 'The latest squawks from ' . $user['avatar'] . '.';
	} else {
		$title = 'Recent Squawks';
		$desc = 'The latest squawks from within Second Life.';

		$squawks = $SQL->Query('SELECT * FROM squawks ORDER BY posted DESC LIMIT 50;');
	}
?>
<?='<'?>?xml version="1.0"?<?='>'?>
<rss version="2.0">
<channel>
<title><?=$title?></title>
<link>http://www.squawknest.com/</link>
<description><?=$desc?></description>
<language>en-us</language>
<ttl>10</ttl>

<?
	foreach($squawks as $squawk) {
		if(isset($query_name)) {
?>
<item>
<title><![CDATA[<?=ucfirst(stripslashes($squawk['message']))?>]]></title>
<description><![CDATA[<p><?=ucfirst(stripslashes($squawk['message']))?> <?=$squawk['avatar']?> (<a href="secondlife://<?=urlencode($squawk['region'])?>/<?=urlencode($squawk['x'])?>/<?=urlencode($squawk['y'])?>/<?=urlencode($squawk['z'])?>"><?=$squawk['region']?> @ <?=$squawk['x']?>,<?=$squawk['y']?>,<?=$squawk['z']?></a>)</p>]]></description>
<pubDate><?=(substr(gmdate('r', $squawk['posted']), 0, -5) . 'PDT')?></pubDate>
<guid isPermaLink="true">http://www.squawknest.com/people?name=<?=urlencode($squawk['avatar'] . '&squawk=' . $squawk['id'])?></guid>
</item>

<?
		} else {
?>
<item>
<title><![CDATA[<?=$squawk['avatar']?>: <?=ucfirst(stripslashes($squawk['message']))?>]]></title>
<description><![CDATA[<p><?=ucfirst(stripslashes($squawk['message']))?></p>
<p><?=$squawk['avatar']?> (<a href="secondlife://<?=urlencode($squawk['region'])?>/<?=urlencode($squawk['x'])?>/<?=urlencode($squawk['y'])?>/<?=urlencode($squawk['z'])?>"><?=$squawk['region']?> @ <?=$squawk['x']?>,<?=$squawk['y']?>,<?=$squawk['z']?></a>)</p>]]></description>
<pubDate><?=(substr(gmdate('r', $squawk['posted']), 0, -5) . 'PDT')?></pubDate>
<guid isPermaLink="true">http://www.squawknest.com/people?name=<?=urlencode($squawk['avatar'] . '&squawk=' . $squawk['id'])?></guid>
</item>

<?		}
	}
?>

</channel>

</rss>