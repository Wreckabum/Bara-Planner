<?php
	if(!isset($_SERVER['SERVER_NAME'])){
		//Cron
		define("IS_LOCAL", true);
	}elseif($_SERVER['SERVER_NAME'] == 'majorproj'){
		//Local
		define("IS_LOCAL", true);
		
		ini_set("xdebug.var_display_max_children", '-1');
		ini_set("xdebug.var_display_max_data", '-1');
		ini_set("xdebug.var_display_max_depth", '-1');
	}else{
		//Live
		define("IS_LOCAL", false);
	}
	
	//Update to match environment
	$config['db_host'] = "localhost";
	$config['db_user'] = "root";
	$config['db_pass'] = "";
	$config['db'] = "majproj_active";
	
	$config['logs'] = "C:\Wamp64\www\Bara-Planner\logs";
	
	$config['email'] = "noreply@baraplanner.atwebpages.com";
	$config['email_pass'] = "Q@3dlyn57G8TiAnU";
	$config['email_host'] = "mboxhosting.com";
?>