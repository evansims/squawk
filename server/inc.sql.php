<?php

	$host = 'localhost';
	$user = 'root';
	$password = '';
	$database = 'squawk';

	class cSQL {

		var $link, $selected;

		function Connect() {
			global $host, $user, $password, $database;

			$this->link = @mysql_connect($host, $user, $password);
			if(!$this->link) die('Database connection failed. Try again later.');

			$this->selected = @mysql_select_db($database, $this->link);
			if(!$this->selected) die('Database selection failed.');
		}

		function Query($cmd) {

			$ret = @mysql_query($cmd);
			if(!$ret) return false;

			$out = array();
			while ($row = @mysql_fetch_assoc($ret)) {
			    $out[] = $row;
			}

			@mysql_free_result($ret);
			return $out;

		}

	}

	$SQL = new cSQL;
	$SQL->Connect();

	function TimePDT() {
		$time = gmmktime();
		$time = $time - (3600 * 3);
		return $time;
	}

	function Age($timestamp){
		$difference = TimePDT() - $timestamp;
		$periods = array('second', 'minute', 'hour', 'day', 'week', 'month', 'years', 'decade');
		$lengths = array('60','60','24','7','4.35','12','10');
		for($j = 0; $difference >= $lengths[$j]; $j++)
			$difference /= $lengths[$j];
		$difference = round($difference);
		if($difference != 1) $periods[$j].= 's';
		$text = "$difference $periods[$j] ago";
		return $text;
	}
