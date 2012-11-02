<?

	require('../inc.sql.php');

	$cutoff = gmmktime() - 86400;

	$SQL->Query("DELETE FROM notifications WHERE posted < $cutoff;");
	$SQL->Query("OPTIMIZE TABLE notifications");

	echo "Success.";
	exit;
