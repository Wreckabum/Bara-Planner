<?php
	require_once("include/class/ClassAccount.php");
	require_once("include/class/ClassFaculty.php");
	require_once("include/class/ClassStudent.php");
	
	/*
		Connects to the DB when required
	*/
	function sql_connect(){
		$GLOBALS['mysql_link'] = mysqli_connect("localhost", "root", "", "fyp_mgmt_tool", 3306);
	}
	
	/*
		Cleans a string for SQL insertion
	*/
	function str_clean(&$string){
		//$GLOBALS['mysql_link'] = mysqli_connect("localhost", "root", "", "fyp_mgmt_tool", 3306);
		return mysqli_real_escape_string($GLOBALS['mysql_link'], trim($string));
	}
	
	/*
		Queries the DB
		
		@param	Query string
		@return	SQL query result / error
	*/
	function db_query($query){
		//$GLOBALS['mysql_link'] = mysqli_connect("localhost", "root", "", "fyp_mgmt_tool", 3306);
		$result = mysqli_query($GLOBALS['mysql_link'], $query);
		$error = mysqli_error($GLOBALS['mysql_link']);
		
		if ($error != ""){
			echo $error;
			echo nl2br(var_export(debug_backtrace(), true));
			exit();
		}
		
		return $result;
	}
?>