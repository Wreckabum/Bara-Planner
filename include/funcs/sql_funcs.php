<?php
	require_once("config.php");
	require_once("include/class/ClassAccount.php");
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
		
		if ($error != ""){
			echo $error;
			echo nl2br(var_export(debug_backtrace(), true));
			exit();
		}
		
		return $result;
	}
	
	/*
		Queries the DB
		
		@param	Query string
		@return	SQL query result / error
	*/
	function get_account($id){
		str_clean($id);
		
		$type = mysqli_fetch_assoc(db_query("SELECT `type` FROM `accounts` WHERE `id` = '{$id}';"))['type'];
		
		if($type == 1 || $type == 2){
			return new Student($id);
		}else{
			return new Faculty($id);
		}
	}
?>