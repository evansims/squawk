<?

	define('NL', "\n");
	define('TB', "\t");

	header('Content-Type: text/plain');
	require_once('inc.sql.php');

	$triggerOnline = TimePDT() - 301;
	$triggerFresh = TimePDT() - 3600;

	$out = '[' . NL;

	$squawks = $SQL->Query("SELECT * FROM squawks WHERE message NOT LIKE '@%' AND CHAR_LENGTH(message) > 10 ORDER BY posted DESC LIMIT 10;");
	$squawkers = array();

	foreach($squawks as $squawk) {

		if(!isset($squawkers[$squawk['avatar']])) {
			$ret = $SQL->Query("SELECT * FROM squawkers WHERE avatar='{$squawk['avatar']}' LIMIT 1");
			if($ret) {
				$ret = $ret[0];
				$squawkers[$squawk['avatar']] = $ret;
				$squawkers[$squawk['avatar']]['status'] = 'offline';
				if($squawkers[$squawk['avatar']]['seen'] >= $triggerOnline) { $squawkers[$squawk['avatar']]['status'] = 'online'; }
			}
		}

		$squawk['message'] = trim(strip_tags(stripslashes($squawk['message'])));
		$squawk['message'] = str_replace('"','\"', $squawk['message']);
		$squawk['message'] = ucfirst($squawk['message']);

		$squawk['aged'] = Age($squawk['posted']);

		$squawk['freshness'] = 'stale';
		if($squawk['posted'] >= $triggerFresh) $squawk['freshness'] = 'fresh';

		$out .= '{' . NL;
		$out .= TB . '"id": ' . $squawk['id'] . ',' . NL;
		$out .= TB . '"name": "' . $squawk['avatar'] . '",' . NL;
		$out .= TB . '"encoded_name": "' . urlencode($squawk['avatar']) . '",' . NL;
		$out .= TB . '"twitter": "' . urlencode($squawkers[$squawk['avatar']]['twitter']) . '",' . NL;
		$out .= TB . '"status": "' . $squawkers[$squawk['avatar']]['status'] . '",' . NL;
		$out .= TB . '"date": "' . $squawk['posted'] . '",' . NL;
		$out .= TB . '"age": "' . $squawk['aged'] . '",' . NL;
		$out .= TB . '"freshness": "' . $squawk['freshness'] . '",' . NL;
		$out .= TB . '"message": "' . $squawk['message'] . '",' . NL;
		$out .= TB . '"region": "' . $squawk['region'] . '",' . NL;
		$out .= TB . '"encoded_region": "' . urlencode($squawk['region']) . '",' . NL;
		$out .= TB . '"x": ' . $squawk['x'] . ',' . NL;
		$out .= TB . '"y": ' . $squawk['y'] . ',' . NL;
		$out .= TB . '"z": ' . $squawk['z'] . '' . NL;
		$out .= '},' . NL;

	}

	$out = substr($out, 0, -2);

	$out .= ']';

	echo $out;
