<?php

	import_request_variables('gp', 'query_');

	if(isset($query_heartbeat)) {

		if(!isset($query_name) || !isset($query_twitter)) {
			header('HTTP/1.0 400 Bad Request');
			header('Content-Type: text/plain');
			echo 'Malformed request';
			exit;
		}

		$query_name =  mysql_escape_string(trim(urldecode($query_name)));

		if(isset($query_twitter)) { $query_twitter =  mysql_escape_string(trim(urldecode($query_twitter))); } else { $query_twitter = ''; }
		if(isset($query_jaiku)) { $query_jaiku = mysql_escape_string(trim(urldecode($query_jaiku))); } else { $query_jaiku = ''; }
		if(isset($query_key)) { $query_key = mysql_escape_string(trim(urldecode($query_key))); } else { $query_key = ''; }

		require('../inc.sql.php');

		$user = $SQL->Query("SELECT * FROM squawkers WHERE avatar='$query_name' LIMIT 1;");

		header('HTTP/1.0 200 OK');
		header('Content-Type: text/plain');

		if(!$user) {
			$SQL->Query("INSERT LOW_PRIORITY INTO squawkers (avatar,seen,twitter,jaiku,uid) VALUES('$query_name','" . TimePDT() . "','$query_twitter','$query_jaiku','$query_key');");
			echo 'Success; Welcome';
		} else {
			$SQL->Query("UPDATE LOW_PRIORITY squawkers SET seen='" . TimePDT() . "', twitter='$query_twitter', jaiku='$query_jaiku', uid='$query_key' WHERE id='" . $user[0]['id'] . "' LIMIT 1;");
			echo 'Success; Updated';
		}

		exit;

	} elseif(isset($query_message)) {

		if(!isset($query_name) || !isset($query_region) || !isset($query_x) || !isset($query_y) || !isset($query_z) || !isset($query_message)) {
			header('HTTP/1.0 400 Bad Request');
			header('Content-Type: text/plain');
			echo 'Malformed request';
			exit;
		}

		$query_name = mysql_escape_string(trim(urldecode($query_name)));
		$query_region =  mysql_escape_string(trim(urldecode($query_region)));
		$query_x =  mysql_escape_string((int)trim(urldecode($query_x)));
		$query_y =  mysql_escape_string((int)trim(urldecode($query_y)));
		$query_z =  mysql_escape_string((int)trim(urldecode($query_z)));
		$query_message =  mysql_escape_string(trim(addslashes(stripslashes(urldecode($query_message)))));

		require('../inc.sql.php');

		$q = "INSERT INTO squawks (avatar,region,x,y,z,posted,message) VALUES ('$query_name','$query_region','$query_x','$query_y','$query_z','" . TimePDT() . "','$query_message');";
		$ret = $SQL->Query($q);

		header('HTTP/1.0 200 OK');
		header('Content-Type: text/plain');
		echo 'Success';
		exit;

	}

	header('HTTP/1.0 405 Method Not Allowed');
	header('Content-Type: text/plain');
	echo 'Invalid request.';
	exit;
