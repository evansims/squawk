<?

	import_request_variables('gp', 'query_');
	
	if(isset($query_name)) {
	
		require('inc.peopleprofile.php');
	
	} else {
	
		require('inc.peopleindex.php');
	
	}
