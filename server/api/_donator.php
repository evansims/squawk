<?php

	import_request_variables('gp', 'query_');

	if(!isset($query_name)) {
		header('HTTP/1.0 400 Bad Request');
		header('Content-Type: text/plain');
		echo 'Malformed request';
		exit;
	}

	$query_name =  mysql_escape_string(urldecode($query_name));

	require('../inc.sql.php');

	$user = $SQL->Query("SELECT * FROM donators WHERE avatar='$query_name' LIMIT 1;");

	if(!$user) { header('HTTP/1.0 404 File Not Found'); echo '0'; } else { header('HTTP/1.0 200 OK'); echo $user[0]['amount']; }

	exit;
