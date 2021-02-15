<?php
	require_once("include/funcs/sql_funcs.php");
	
	sql_connect();
	
	/* $q = db_query("SELECT * FROM `majors`;");
	
	$part_time = [];
	$full_time = [];
	$all = [];
	
	while($row = mysqli_fetch_assoc($q)){
		if($row['part_time'] == 1){
			$part_time[] = $row['id'];
		}
		
		if($row['full_time'] == 1){
			$full_time[] = $row['id'];
		}
		
		$all[] = $row['id'];
	} */
	
	/* $q = db_query("SELECT * FROM `accounts`;");
	
	while($row = mysqli_fetch_assoc($q)){
		if($row['type'] == 2){
			$rand_maj = $part_time[array_rand($part_time)];
			var_dump("UPDATE `accounts` SET `majors` = '[\"{$rand_maj}\"]' WHERE `id` = '{$row['id']}'");
			db_query("UPDATE `accounts` SET `majors` = '[\"{$rand_maj}\"]' WHERE `id` = '{$row['id']}'");
		}elseif($row['type'] == 1){
			$rand_maj = $full_time[array_rand($full_time)];
			db_query("UPDATE `accounts` SET `majors` = '[\"{$rand_maj}\"]' WHERE `id` = '{$row['id']}'");
		}
	} */
	
	/* $q = db_query("SELECT * FROM `groups`;");
	
	while($row = mysqli_fetch_assoc($q)){
		$mem = json_decode($row['members']);
		
		$type = [];
		
		foreach($mem as $m){
			$type[] = mysqli_fetch_assoc(db_query("SELECT `type` FROM `accounts` WHERE `id` = '{$m}';"))['type'];
		}
		
		//db_query("UPDATE `groups` SET `project` = 'CSIT-21-S1-{$k}' WHERE `id` = '{$row['id']}'");
	} */
	
	@mysqli_close($GLOBALS['mysql_link']);
?>