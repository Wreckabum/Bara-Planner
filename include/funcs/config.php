<?php
	if(!isset($_SERVER['SERVER_NAME'])){
		//Cron
		define("IS_LOCAL", true);
	}elseif($_SERVER['SERVER_NAME'] == 'majorproj'){
		//Local
		define("IS_LOCAL", true);
	}else{
		//Live
		define("IS_LOCAL", false);
	}
	
	//Update to match environment
	$config['db_host'] = "localhost";
	$config['db_user'] = "root";
	$config['db_pass'] = "";
	$config['db'] = "majproj_active";
	$config['db_archive'] = "majproj_archive";
?>