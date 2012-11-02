<?php

	define('DEVELOPER_MODE', false);

	function httpNotModified($message) {
		if(!DEVELOPER_MODE) {
			header('HTTP/1.0 304 Not Modified');
		}
		echo($message);
		exit;
	}

	header('Content-Type: text/plain');

	import_request_variables('gp', 'query_');
	if(!isset($query_twitter) || !isset($query_password)) { header('HTTP/1.0 400 Bad Request'); echo('Invalid request.'); exit; }

	$twitter = mysql_escape_string(urldecode($query_twitter));
	$password = mysql_escape_string(urldecode($query_password));
	if(!isset($query_protocol)) $query_protocol = 1;
	$version = mysql_escape_string(urldecode($query_protocol));

	$force = false;
	if(isset($query_force)) $force = true;

	require('../inc.sql.php');
	$user = $SQL->Query("SELECT * FROM friends WHERE twitter='$twitter' LIMIT 1;");

	if($user) {
		$user = $user[0];
		if((time() - $user['checked']) <= 180) {
			httpNotModified('Too soon. Try again shortly.');
		} else {
			$SQL->Query("UPDATE friends SET checked='" . time() . "' WHERE id='{$user['id']}' LIMIT 1;");
		}
	} else {
		$SQL->Query("INSERT INTO friends (twitter,checked) VALUES('$twitter','" . time() . "');");
		$user = $SQL->Query("SELECT * FROM friends WHERE twitter='$twitter' LIMIT 1;");
		$user = $user[0];
		$user['checked'] = (time() - 21600);
	}

	$friends = '';

	$api = fsockopen('twitter.com', 80, $errno, $errstr, 10);
	if(!$api) {
		header('HTTP/1.0 500 Internal Server Error');
		echo('Could not contact twitter. Try again later.');
		exit;
	} else {
		$since = '';
		if(!$force && isset($user['checked'])) $since = '&since=' . urlencode(date('r', $user['checked']));

		$head  = "GET /statuses/friends_timeline.xml?id={$twitter}{$since} HTTP/1.1\r\n";
		$head .= "Accept: application/xml\r\n";
		$head .= "Accept-Encoding: none\r\n";
		$head .= "User-Agent: Squawk (Proxy)\r\n";
		$head .= "Host: twitter.com\r\n";
		$head .= "Authorization: Basic " . base64_encode("{$twitter}:{$password}") . "\r\n";
		$head .= "Connection: Close\r\n\r\n";

		fwrite($api, $head);
		while(!feof($api)) { $friends .= fgets($api, 128); }
		fclose($api);
	}

	$process = false;

	$twitterHeaders = explode("\n", substr($friends, 0, strpos($friends, "\r\n\r\n")));
	foreach($twitterHeaders as $twitterHeader) {
		if(substr($twitterHeader, 0, 8) == 'HTTP/1.1') {
			$status = substr($twitterHeader, 9);
			$status = substr($status, 0, 3);
			if($status == '200') $process = true;
		} elseif(substr($twitterHeader, 0, 6) == 'ETag: ') {
			$etag = substr($twitterHeader, 6);
			if($process == true) {
				if(!DEVELOPER_MODE && ($force || !isset($user['cache']) || $user['cache'] != $etag)) { $SQL->Query("UPDATE friends SET cache='{$etag}' WHERE id='{$user['id']}' LIMIT 1;"); }
				if(!DEVELOPER_MODE && !$force && $user['cache'] == $etag) httpNotModified('No new twitters..');
			}
		}
	}
	unset($twitterHeader);
	unset($twitterHeaders);

	if(!$process) { header('HTTP/1.0 500 Internal Server Error'); echo 'Twitter appears to be having problems. Try again later.'; exit; }

	require('../inc.xml.php');

	$XML = new cXML;
	$friends = $XML->Parse($friends);

	$offset = (integer)substr(date('O'), 2, 1);

	$month = array('Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7,
				   'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12);

	foreach($friends as $node) {
		if($node['name'] == 'statuses') {
			foreach($node['children'] as $friend) {
				if($friend['name'] == 'status') {
					$time = '';
					$status = '';
					$username = '';
					$name = '';
					$image = '';

					foreach($friend['children'] as $subnode) {
						if($subnode['name'] == 'created_at') {
							$time = substr($subnode['value'], 4);
							$time = substr($time, 0, 7) . substr($time, -4) . substr($time, 6, 9);
							$time = explode(' ', str_replace(':', ' ', $time));
							$time = gmmktime(($time[3] - $offset), $time[4], $time[5], $month[$time[0]], $time[1], $time[2]);
							$time = ago($time);
						} elseif($subnode['name'] == 'text') {
							$status = trim(str_replace(array("\r"), '', str_replace(array('	',"\n"), ' ', $subnode['value'])));
						} elseif($subnode['name'] == 'user') {
							foreach($subnode['children'] as $user) {
								if($user['name'] == 'name') {
									$username = $user['value'];
								} elseif($user['name'] == 'screen_name') {
									$name = $user['value'];
								} elseif($user['name'] == 'profile_image_url') {
									$image = $user['value'];
								}
							}
						}
					}

					if($name == $twitter) continue;

					if($version == 1) {
						echo "$time###$username###$status\n";
					}
				}
			}
		}
	}

	exit;

	function ago($timestamp){
		$difference = gmmktime() - $timestamp;
		$periods = array('second', 'minute', 'hour', 'day', 'week', 'month', 'years', 'decade');
		$lengths = array('60','60','24','7','4.35','12','10');
		for($j = 0; $difference >= $lengths[$j]; $j++)
			$difference /= $lengths[$j];
		$difference = round($difference);
		if($difference != 1) $periods[$j].= 's';
		$text = "$difference $periods[$j] ago";
		return $text;
	}
