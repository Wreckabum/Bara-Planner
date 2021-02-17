<?php
	require_once("config.php");
	require_once("include/class/ClassAccount.php");
	require_once("include/class/ClassAdmin.php");
	require_once("include/class/ClassFaculty.php");
	require_once("include/class/ClassStudent.php");
	
	/*
		Connects to the DB when required
	*/
	function sql_connect(){
		global $config;
		
		$GLOBALS['mysql_link'] =  mysqli_connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db'], 3306);
	}
	
	/*
		Cleans a string for SQL insertion
	*/
	function str_clean(&$string){
		return mysqli_real_escape_string($GLOBALS['mysql_link'], trim($string));
	}
	
	/*
		Queries the DB
		
		@param	Query string
		@return	SQL query result / error
	*/
	function db_query($query){
		$result = mysqli_query($GLOBALS['mysql_link'], $query);
		$error = mysqli_error($GLOBALS['mysql_link']);
		
		if(IS_LOCAL && $error != ""){
			echo $error;
			echo nl2br(var_export(debug_backtrace(), true));
			exit();
		}
		
		return $result;
	}
	
	/*
		Returns the account object based on ID
		
		@param	int
		@return	Faculty/Student object
	*/
	function get_account($id){
		str_clean($id);
		
		$type = mysqli_fetch_assoc(db_query("SELECT `type` FROM `accounts` WHERE `id` = '{$id}';"))['type'];
		
		if($type == 1 || $type == 2){
			return new Student($id);
		}elseif($type == 8 || $type == 9){
			return new Admin($id);
		}else{
			return new Faculty($id);
		}
	}
	
	/*
		Returns a 2D array of all majors
		
		@return	2D array
	*/
	function get_all_majors(){
		$query = db_query("SELECT `*` FROM `majors` ORDER BY `name` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[$row['id']]['name'] = $row['name'];
			$output[$row['id']]['description'] = $row['description'];
			$output[$row['id']]['part_time'] = (bool)$row['part_time'];
			$output[$row['id']]['full_time'] = (bool)$row['full_time'];
		}
		
		return $output;
	}
	
	/*
		Returns an array for a major
		
		@param	string/array
		@return array
	*/
	function get_major($id){
		if(is_array($id)){
			$query = db_query("SELECT `*` FROM `majors` WHERE `id` IN ('". implode("', '", $id) ."');");
		
			$output = [];
			
			while($row = mysqli_fetch_assoc($query)){
				$output[$row['id']]['name'] = $row['name'];
				$output[$row['id']]['description'] = $row['description'];
				$output[$row['id']]['part_time'] = (bool)$row['part_time'];
				$output[$row['id']]['full_time'] = (bool)$row['full_time'];
			}
			
			return $output;
		}else{
			return mysqli_fetch_assoc(db_query("SELECT `*` FROM `majors` WHERE `id` = '{$id}';"));
		}
	}
	
	/*
		Returns a 2D array of all projects
		
		@return	2D array
	*/
	function get_all_projects(){
		$query = db_query("SELECT `*` FROM `projects` ORDER BY `name` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[$row['id']]['proj_id'] = $row['name'];
			$output[$row['id']]['name'] = $row['name'];
			$output[$row['id']]['description'] = $row['description'];
			$output[$row['id']]['available_for'] = $row['available_for'];
			$output[$row['id']]['active'] = (bool)$row['active'];
		}
		
		return $output;
	}
	
	/*
		Returns an array for a project
		
		@param	int/array
		@param	string (optional)
		@return array
	*/
	function get_project($id, $from = "id"){
		if($from != "proj_id"){
			$from = "id";
		}
		
		if(is_array($id)){
			$query = db_query("SELECT `*` FROM `projects` WHERE `{$from}` IN ('". implode("', '", $id) ."');");
		
			$output = [];
			
			while($row = mysqli_fetch_assoc($query)){
				$output[$row['id']]['proj_id'] = $row['proj_id'];
				$output[$row['id']]['name'] = $row['name'];
				$output[$row['id']]['description'] = $row['description'];
				$output[$row['id']]['available_for'] = json_decode($row['available_for']);
				$output[$row['id']]['active'] = (bool)$row['active'];
			}
			
			return $output;
		}else{
			return mysqli_fetch_assoc(db_query("SELECT `*` FROM `projects` WHERE `{$from}` = '{$id}';"));
		}
	}
?>