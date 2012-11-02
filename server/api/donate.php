<?php

	import_request_variables('gp', 'query_');

	if(!isset($query_name) || !isset($query_amount)) {
		header('HTTP/1.0 400 Bad Request');
		header('Content-Type: text/plain');
		echo 'Malformed request';
		exit;
	}

	$query_name =  mysql_escape_string(urldecode($query_name));
	$query_amount =  (int)urldecode($query_amount);

	require('../inc.sql.php');

	$user = $SQL->Query("SELECT * FROM donators WHERE avatar='$query_name' LIMIT 1;");

	if(!$user) {
		$SQL->Query("INSERT HIGH_PRIORITY INTO donators (avatar,donated,amount) VALUES('$query_name','" . TimePDT() . "','$query_amount');");
	} else {
		$amount = $user[0]['amount'] + $query_amount;
		$SQL->Query("UPDATE donators SET amount='$amount', donated='" . TimePDT() . "' WHERE id='" . $user[0]['id'] . "' LIMIT 1;");
	}

	$donations = $SQL->Query("SELECT * FROM stats WHERE name='donations' LIMIT 1;");
	$amount = (int)$donations[0]['content'];
	$amount = $amount + $query_amount;
	$id = (int)$donations[0]['id'];
	$SQL->Query("UPDATE stats SET content='$amount' WHERE id='$id' LIMIT 1;");

	header('HTTP/1.0 200 OK');
	header('Content-Type: text/plain');
	echo 'Success';
	exit;
