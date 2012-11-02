<?

	require_once('inc.sql.php');

	// Grab avatar URL based on twitter username.

	// NOTE: Twitter no longer supports BASIC authorization.
	// This will no longer work without drastic modification.

	$suser = '';
	$spass = '';

	import_request_variables('gp', 'query_');

	if(isset($query_t)) {

		$query_t = trim(substr($query_t, 0, 64));

		$cache = $SQL->Query('SELECT * FROM squawkers WHERE twitter=\'' . mysql_escape_string($query_t) . '\' LIMIT 1;');
		if(!$cache) pushDefault(); // User isn't in database.
		$cache = $cache[0];

		if(strlen($cache['img'])) {
			if(strlen($cache['img_check'])) {
				if($cache['img_check'] >= (time() - 86400)) {
					header('Location: ' . $cache['img']);
					exit;
				}
			}
		}

		$data = null;

		$api = fsockopen('twitter.com', 80, $errno, $errstr, 10);
		if(!$api) {
			pushDefault();
		} else {
			$head  = "GET /users/show/{$query_t}.xml HTTP/1.1\r\n";
			$head .= "Accept: application/xml\r\n";
			$head .= "Accept-Encoding: none\r\n";
			$head .= "User-Agent: Squawk (Website)\r\n";
			$head .= "Host: twitter.com\r\n";
			$head .= "Authorization: Basic " . base64_encode("{$suser}:{$spass}") . "\r\n";
			$head .= "Connection: Close\r\n\r\n";

			fwrite($api, $head);
			while(!feof($api)) { $data .= fgets($api, 128); }
			fclose($api);
		}

		if($data) {
			require('inc.xml.php');

			$XML = new cXML;
			$data = $XML->Parse($data);

			print_r($data);

			exit;

			if(isset($data[1]['children'][5]['value'])) {
				$url = $data[1]['children'][5]['value'];
				$SQL->Query('UPDATE squawkers SET img=\'' . $url . '\', img_check=\'' . time() . '\' WHERE twitter=\'' . $query_t . '\' LIMIT 1;');
				header('Location: ' . $url);
				exit;
			} else {
				header('Location: http://assets0.twitter.com/images/default_image.gif?1177551172');
				exit;
			}

		}

		exit;

	}

	function pushDefault() {
		header('Location: http://assets0.twitter.com/images/default_image.gif?1177551172');
		exit;
	}
