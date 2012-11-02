<?php

	require('../inc.sql.php');
	require('../inc.xml.php');
	$XML = new cXML;

	$month = array('Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7,
				   'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12);

	$offset = (integer)substr(date('O'), 2, 1);

	define('DEVELOPER_MODE', false);

	function cleanSpaces($message) {

		while(strpos($message, '  ')) {
			$message = str_replace('  ', ' ', $message);
		}

		return $message;

	}

	function decode_unicode_url($str)
	{
	  $res = '';

	  $i = 0;
	  $max = strlen($str) - 6;
	  while ($i <= $max)
	  {
	    $character = $str[$i];
	    if ($character == '%' && $str[$i + 1] == 'u')
	    {
	      $value = hexdec(substr($str, $i + 2, 4));
	      $i += 6;

	      if ($value < 0x0080) // 1 byte: 0xxxxxxx
	        $character = chr($value);
	      else if ($value < 0x0800) // 2 bytes: 110xxxxx 10xxxxxx
	        $character =
	            chr((($value & 0x07c0) >> 6) | 0xc0)
	          . chr(($value & 0x3f) | 0x80);
	      else // 3 bytes: 1110xxxx 10xxxxxx 10xxxxxx
	        $character =
	            chr((($value & 0xf000) >> 12) | 0xe0)
	          . chr((($value & 0x0fc0) >> 6) | 0x80)
	          . chr(($value & 0x3f) | 0x80);
	    }
	    else
	      $i++;

	    $res .= $character;
	  }

	  return $res . substr($str, $i);
	}

	function parseGeocode($tmessage) {

		$tmessage = str_replace('http://slurl.com/secondlife/', 'secondlife://', $tmessage);

		if(strpos($tmessage, 'SL:') && substr($tmessage, strpos($tmessage, 'SL:') + 4, 1) !== ' ') {
			$geotag_start = strpos($tmessage, 'SL:') + 3;
			if(strpos($tmessage, '(', $geotag_start)) {
				$coord_start = strpos($tmessage, '(', $geotag_start) + 1;
				if(strpos($tmessage, ')', $coord_start)) {
					$coord_end = strpos($tmessage, ')', $coord_start);

					$region = urlencode(substr($tmessage, $geotag_start, ($coord_start - 1 - $geotag_start)));

					$next = strpos($tmessage, ',', $coord_start);
					$x = substr($tmessage, $coord_start, ($next - $coord_start));

					$next = strpos($tmessage, ',', $next);
					$y = substr($tmessage, $next + 1);
					$y = substr($y, 0, strpos($y, ','));

					$next = strpos($tmessage, ',', $next + 1);
					$z = substr($tmessage, $next + 1, ($coord_end - $next) - 1);

					$tmessage = trim(substr($tmessage, 0, $geotag_start - 3)) . ' - secondlife://' . $region . '/' . $x . '/' . $y . '/' . $z . ' ' . trim(substr($tmessage, $coord_end + 1));
				}
			}
		}

		return $tmessage;

	}

	function prepareSQLData($string) {
		$string = stripslashes($string);
		$string = mysql_escape_string($string);
		$string = "'$string'";
		return $string;
	}

	function ago($timestamp){
		if(!$timestamp) return;
		$difference = gmmktime() - $timestamp;
		if(!$difference) return;
		$periods = array('second', 'minute', 'hour', 'day', 'week', 'month', 'years', 'decade');
		$lengths = array('60','60','24','7','4.35','12','10');
		for($j = 0; $difference >= $lengths[$j]; $j++)
			$difference /= $lengths[$j];
		$difference = round($difference);
		if($difference != 1) $periods[$j].= 's';
		$text = "$difference $periods[$j] ago";
		return $text;
	}

	function httpNotModified($message) {
		if(!DEVELOPER_MODE) {
			if(!headers_sent()) header('HTTP/1.0 304 Not Modified');
		}
		echo($message);
		exit;
	}

	function fetchFeed($domain, $path, $data = '', $user = null, $pass = null) {

		$feed = '';
		$status = 0;

		$head  = "GET {$path} HTTP/1.1\r\n";
		$head .= "Host: {$domain}\r\n";
		$head .= "Accept: application/xml, */*\r\n";
		$head .= "Accept-Encoding: gzip\r\n";
		$head .= "User-Agent: squawknest.com\r\n";
		if($user && $pass) { $head .= "Authorization: Basic " . base64_encode("{$user}:{$pass}") . "\r\n"; }
		$head .= "Connection: Close\r\n\r\n";
		$head .= $data;

		$api = fsockopen($domain, 80, $errno, $errstr, 10);
		if(!$api) {
			header('HTTP/1.0 500 Internal Server Error');
			echo('Could not contact ' + $domain + '. Try again later.');
			exit;
		} else {
			fwrite($api, $head);
			while(!feof($api)) { $feed .= fgets($api, 128); }
			@fclose($api);

			$feed = str_replace("\r\n", "\n", $feed);
			$feed = str_replace("\r", '', $feed);
			$status = substr($feed, strpos($feed, 'HTTP/') + 9, 3);
			$encoding = substr($feed, strpos($feed, 'Content-Encoding') + strlen('Content-Encoding'));
			if($encoding) {
				$encoding = trim(substr($feed, 0, strpos($encoding, "\n")));
				if(strpos($encoding, 'zip')) { $encoding = true; } else { $encoding = false; }
				}

			if($status == 302) {
				if(DEVELOPER_MODE) {
					echo $head . "\n\n";
					echo $feed;
				}
			} else if($status == 200 && $feed) {
				$feed = trim(substr($feed, strpos($feed, "\n\n") + 2));
				if($encoding) $feed = gzdecode($feed);

				global $XML;
				$feed = $XML->Parse($feed);

				$feed = $feed[1]['children'];
				return array($status, $feed);
			} else {
				header('HTTP/1.0 500 Internal Server Error');
				if(DEVELOPER_MODE) {
					echo("Improper response from $domain:\n\n" . $feed . "\n");
				}
				return null;
			}
		}
		@fclose($api);
	}

	header('Content-Type: text/plain');

	import_request_variables('gp', 'query_');
	if(!isset($query_name)) { header('HTTP/1.0 400 Bad Request'); echo('Invalid request.'); exit; }

	if(!isset($query_hidereplies)) $query_hidereplies = '';
	if(!isset($query_t)) $query_t = '';
	if(!isset($query_tp)) $query_tp = '';
	if(!isset($query_j)) $query_j = '';
	if(!isset($query_jp)) $query_jp = '';
	//if(!isset($query_d)) $query_d = '';
	//if(!isset($query_dp)) $query_dp = '';

	if(!isset($query_maxcount)) { $query_maxcount = 10; } else {
		$query_maxcount = (integer)$query_maxcount;
		if($query_maxcount > 10) $query_maxcount = 10;
		if($query_maxcount < 1) $query_maxcount = 1;
	}

	$filter_replies = urldecode($query_hidereplies);

	$twitter_username = urldecode($query_t);
	$twitter_password = urldecode($query_tp);

	$jaiku_username = urldecode($query_j);
	$jaiku_password = urldecode($query_jp);

	//$delicious_username = urldecode($query_d);
	//$delicious_password = urldecode($query_dp);

	$twitter = false;
	$jaiku = false;
	//$delicious = false;

	if($twitter_username && $twitter_password) $twitter = true;
	if($jaiku_username && $jaiku_password) $jaiku = true;
	//if($delicious_username && $delicious_password) $delicious = true;

	if(!$twitter && !$jaiku) { header('HTTP/1.0 500 Internal Server Error'); echo('No service configuration provided.'); exit; }

	$waiting = $SQL->Query("SELECT * FROM notification_queue WHERE name='" . $query_name . "';");

	if($waiting) {

		$SQL->Query("DELETE FROM notification_queue WHERE id='" . $waiting[0]['id'] . "' LIMIT 1;");

		if(!DEVELOPER_MODE && count($waiting) > 1) {
			header('HTTP/1.0 206 Partial Content');
		}

		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
			$data = gzencode($waiting[0]['message'],9);
			header('Content-Encoding: gzip');
			header('Content-Length: ' . strlen($data));
			echo $data;
		} else {
			echo $waiting[0]['message'];
		}

		exit;

	} else {

		$push = array();

		if($twitter) {
			$service = 1;
			$twitter = fetchFeed('twitter.com', '/statuses/friends_timeline.xml?id=' . $query_t, '', $twitter_username, $twitter_password);

			if($twitter && $twitter[0] == 200) {
				foreach($twitter[1] as $update) {
					if($update['name'] == 'status' && $update['children'][0]['name'] == 'created_at' && $update['children'][2]['name'] == 'text' && $update['children'][3]['name'] == 'user' && $update['children'][3]['children'][2]['name'] == 'screen_name' && $update['children'][3]['children'][2]['value'] != $twitter_username) {

						$time = substr($update['children'][0]['value'], 4);
						$time = substr($time, 0, 7) . substr($time, -4) . substr($time, 6, 9);
						$time = explode(' ', str_replace(':', ' ', $time));
						$time = gmmktime(($time[3] - $offset), $time[4], $time[5], $month[$time[0]], $time[1], $time[2]);

						if(!$time || !is_numeric($time)) continue;

						$name = decode_unicode_url(urldecode($update['children'][3]['children'][1]['value']));
						$message = trim(cleanSpaces(decode_unicode_url(urldecode($update['children'][2]['value']))));

						$name = trim(cleanSpaces(str_replace(array('*&#9786; ', '***&#8984; ', '&#8984;'), '', $name)));

						$uid = md5($service . $time . $message);

						$check = $SQL->Query("SELECT * FROM notifications WHERE uid='$uid' LIMIT 1;");

						if(!$check) {

							$url = 'http://twitter.com/' . $update['children'][3]['children'][2]['value'] . '/statuses/' . $update['children'][1]['value'];

							if(strlen($name) < 3) {
								$tmessage = $update['children'][3]['children'][2]['value'] . ' (' . ago($time) . " on Twitter) $message";
							} else {
								$tmessage = "$name (" . ago($time) . " on Twitter) $message";
							}

							$tmessage = parseGeocode($tmessage);

							if(!DEVELOPER_MODE) {
								$q  = "INSERT INTO notifications (uid,posted,tmessage,received) VALUES(";
								$q .= prepareSQLData($uid) . ',';
								$q .= prepareSQLData($time) . ',';
								$q .= prepareSQLData($tmessage) . ',';
								$q .= prepareSQLData($query_name . ',');
								$q .= ');';
								$SQL->Query($q);
							}

							if($filter_replies && substr($message, 0, 1) == '@' && substr($message, 1, 1) != ' ') {
								if(substr(strtolower($message), 1, strlen(strtolower($twitter_username))) == strtolower($twitter_username)) {
									$push[] = array($time, $tmessage);
								}
							} else {
									$push[] = array($time, $tmessage);
							}
						} else {
							$received = explode(',', $check[0]['received']);
							if(!in_array($query_name, $received)) {
								if(!DEVELOPER_MODE) { $SQL->Query("UPDATE notifications SET received='" . $check[0]['received'] . substr(prepareSQLData($query_name . ','), 1) . " WHERE id='{$check[0]['id']}' LIMIT 1;"); }
								if($filter_replies && substr($check[0]['message'], 0, 1) == '@' && substr($check[0]['message'], 1, 1) != ' ') {
									if(substr(strtolower($check[0]['message']), 1, strlen(strtolower($twitter_username))) == strtolower($twitter_username)) {
										$push[] = array($check[0]['posted'], $check[0]['tmessage']);
									}
								} else {
										$push[] = array($check[0]['posted'], $check[0]['tmessage']);
								}
							}
						}

					}
				}
			}
		}

		if($jaiku) {
			$service = 2;
			$jaiku = fetchFeed($jaiku_username . '.jaiku.com', '/contacts/feed/rss?user=' . $jaiku_username . '&personal_key=' . $jaiku_password);

			if($jaiku && $jaiku[0] == 200 && count($jaiku[1])) {
				if(isset($jaiku[1][0]['children'])) {
				foreach($jaiku[1][0]['children'] as $update) {
					if($update['name'] == 'item' && $update['children'][0]['name'] == 'title' && $update['children'][2]['name'] == 'link' &&
					   strpos($update['children'][2]['value'], 'jaiku.com/') && $update['children'][4]['name'] == 'pubdate' &&
					   $update['children'][6]['name'] == 'jaiku:use' && substr($update['children'][6]['attributes']['nick'], 0, strlen($jaiku_username)) != $jaiku_username &&
					   !strpos($update['children'][2]['value'], '#c-') && !strpos($update['children'][1]['value'], 'http://jaiku.com/images/icons/fav-twitter.gif')) {

					   $name = decode_unicode_url(urldecode($update['children'][6]['attributes']['first_name']) . ' ' . trim($update['children'][6]['attributes']['last_name']));
					   $message = trim(cleanSpaces(decode_unicode_url(urldecode($update['children'][0]['value']))));
					   $time = trim($update['children'][4]['value']) - ($offset * 3600);

					   $name = trim(cleanSpaces(str_replace(array('*&#9786; ', '***&#8984; ', '&#8984;'), '', $name)));

					   if(!$time || !is_numeric($time)) continue;

					   $uid = md5($service . $time . $message);

					   $check = $SQL->Query("SELECT * FROM notifications WHERE uid='$uid' LIMIT 1;");

					   if(!$check) {

							$url = $update['children'][2]['value'];

							if(strlen($name) < 3) {
								$tmessage = $update['children'][6]['attributes']['nick'] . ' (' . ago($time) . " on Jaiku) $message";
							} else {
								$tmessage = "$name (" . ago($time) . " on Jaiku) $message";
							}

							$tmessage = parseGeocode($tmessage);

							if(!DEVELOPER_MODE) {
								$q  = "INSERT INTO notifications (uid,posted,tmessage,received) VALUES(";
								$q .= prepareSQLData($uid) . ',';
								$q .= prepareSQLData($time) . ',';
								$q .= prepareSQLData($tmessage) . ',';
								$q .= prepareSQLData($query_name . ',');
								$q .= ');';
								$SQL->Query($q);
							}

							$push[] = array($time, $tmessage);

					   } else {

							$received = explode(',', $check[0]['received']);
							if(!in_array($query_name, $received)) {
								if(!DEVELOPER_MODE) { $SQL->Query("UPDATE notifications SET received='" . $check[0]['received'] . substr(prepareSQLData($query_name . ','), 1) . " WHERE id='{$check[0]['id']}' LIMIT 1;"); }
								if($filter_replies && substr($check[0]['message'], 0, 1) == '@' && substr($check[0]['message'], 1, 1) != ' ') {
									if(substr(strtolower($check[0]['message']), 1, strlen(strtolower($jaiku_username))) == strtolower($jaiku_username)) {
										$push[] = array($check[0]['posted'], $check[0]['tmessage']);
									}
								} else {
										$push[] = array($check[0]['posted'], $check[0]['tmessage']);
								}
							}

					   }

					}
				}
			}
			}
		}

		if($push) {

			function compare($x, $y)
			{
				if ($x[0] == $y[0])
					return 0;
				else if ($x[0] < $y[0])
					return -1;
				else
					return 1;
			}

			usort($push, 'compare');

			if(count($push) > $query_maxcount) {
				// Limit to a maximum of 10 in the queue.
				$_push = array();
				for($i = (count($push) - 1); $i > (count($push) - ($query_maxcount + 1)); $i--) {
					$_push[] = $push[$i];
				}
				$push = $_push;

				$_push = array();
				for($i = (count($push) - 1); $i >= 0; $i--) {
					$_push[] = $push[$i];
				}
				$push = $_push;
			}

			for($i = 0; $i < count($push); $i++) {
				if($i == 0) {
					if(!DEVELOPER_MODE) header('HTTP/1.0 206 Partial Content');
					echo $push[$i][1];
				} else {
					if(!DEVELOPER_MODE) $SQL->Query('INSERT INTO notification_queue (name,message) VALUES(' . prepareSQLData($query_name) . ',' . prepareSQLData($push[$i][1]) . ');');
					if(DEVELOPER_MODE) echo("\nPushed <em>{$push[$i][1]}</em> to queue.");
				}
			}

		} else {

			httpNotModified('No news is good news.');

		}

	}

	exit;
