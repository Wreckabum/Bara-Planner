<?php
	require_once(dirname(__FILE__)."/config.php");
	require_once(dirname(__FILE__)."/../class/ClassAccount.php");
	require_once(dirname(__FILE__)."/../class/ClassAdmin.php");
	require_once(dirname(__FILE__)."/../class/ClassFaculty.php");
	require_once(dirname(__FILE__)."/../class/ClassStudent.php");
	require_once(dirname(__FILE__)."/sub_funcs.php");
	
	/*
		Connects to the DB when required
	*/
	function sql_connect(){
		global $config;
		
		//$GLOBALS['mysql_link'] =  mysqli_connect("localhost", "root", "", "majproj_active", 3306); //For PHPUnit
		
		$GLOBALS['mysql_link'] =  mysqli_connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db'], 3306);
	}
	
	/*
		Cleans a string for SQL insertion
	*/
	function str_clean(&$string){
		//$GLOBALS['mysql_link'] =  mysqli_connect("localhost", "root", "", "majproj_active", 3306); //For PHPUnit
		
		return mysqli_real_escape_string($GLOBALS['mysql_link'], trim($string));
	}
	
	/*
		Queries the DB
		
		@param	Query string
		@return	SQL query result / error
	*/
	function db_query($query){
		//$GLOBALS['mysql_link'] =  mysqli_connect("localhost", "root", "", "majproj_active", 3306); //For PHPUnit
		
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
		Returns an array of all accounts (filter optional)
		
		@param	array of ints -> account types (optinal)
		@return	Array of Faculty/Student/Admin objects
	*/
	function get_all_accounts($type = []){
		array_walk($type, function(&$value, $key){
			$value = (int)$value;
		});
		
		$where = "";
		
		if(!empty($type)){
			$where = "WHERE `type` in ('". implode("', '", $type) ."')";
		}
		
		$query = db_query("SELECT * FROM `accounts` {$where} ORDER BY `sim_id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			if($row['type'] == 1 || $row['type'] == 2){
				$output[] = new Student($row['sim_id']);
			}elseif($row['type'] == 8 || $row['type'] == 9){
				$output[] = new Admin($row['sim_id']);
			}elseif($row['type'] == 0){
				$output[] = new Faculty($row['sim_id']);
			}
		}
		
		return $output;
	}
	
	/*
		Returns the account object based on SIM ID
		
		@param	int
		@return	Faculty/Student/Admin object
	*/
	function get_account($id){
		str_clean($id);
		
		$type = mysqli_fetch_assoc(db_query("SELECT `type` FROM `accounts` WHERE `sim_id` = '{$id}';"))['type'];
		
		if($type == 1 || $type == 2){
			return new Student($id);
		}elseif($type == 8 || $type == 9){
			return new Admin($id);
		}elseif($type == 0){
			return new Faculty($id);
		}
		
		return false;
	}
	
	/*
		Returns all students based on a semester (currently inefficient for large numbers)
		
		@param	int (optional)
		@param	int (optional)
		@return	Array of Student objects
	*/
	function get_students($year = "*", $quarter = "*", $check_group = false){
		str_clean($year);
		str_clean($quarter);
		
		$filter = [];
		$output = []; 
		
		if($year != "*"){
			$filter[] = "`year` = ". (int)$year;
		}

		if($quarter != "*"){
			$filter[] = "`quarter` = ". (int)$quarter;
		}
		
		$filter_text = ((count($filter) == 0) ? "" : " AND ". implode(" AND ", $filter));
		
		$query = db_query("SELECT * FROM `accounts` WHERE `type` IN (1, 2) {$filter_text};");
		
		while($row = mysqli_fetch_assoc($query)){
			//If checking if already in group
			if($check_group){
				$check_query = db_query("SELECT * FROM `groups` WHERE JSON_CONTAINS(`members`, '\"{$row['sim_id']}\"');");
				
				if(mysqli_num_rows($check_query) != 0){
					continue;
				}
			}
			
			$output[] = new Student($row['sim_id']);
		}
		
		return $output;
	}
	
	/*
		Returns all available years
		
		@return	2D array of [Year => [Quarter]]
	*/
	function get_semesters(){
		$query = db_query(
			"SELECT 
				`year`, 
				`quarter` 
			FROM 
				`accounts` 
			WHERE 
				`year` IS NOT NULL AND 
				`quarter` IS NOT NULL 
			GROUP BY 
				`year`, 
				`quarter` 
			ORDER BY 
				`year` ASC, 
				`quarter` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[$row['year']][] = $row['quarter'];
		}
		
		return $output;
	}
	
	/*
		Returns a 2D array of all majors
		
		@return	2D array
	*/
	function get_all_majors(){
		$query = db_query("SELECT * FROM `majors` ORDER BY `id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[$row['id']]['name'] = $row['name'];
			$output[$row['id']]['description'] = $row['description'];
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
			array_walk_recursive($id, function(&$value, $key){
				str_clean($value);
				$value = htmlspecialchars($value);
			});
			
			$query = db_query("SELECT * FROM `majors` WHERE `id` IN ('". implode("', '", $id) ."') ORDER BY `id` ASC;");
		
			$output = [];
			
			while($row = mysqli_fetch_assoc($query)){
				$output[$row['id']]['name'] = $row['name'];
				$output[$row['id']]['description'] = $row['description'];
			}
			
			return $output;
		}else{
			str_clean($id);
			
			return mysqli_fetch_assoc(db_query("SELECT * FROM `majors` WHERE `id` = '{$id}';"));
		}
	}
	
	/*
		Returns a 2D array of all projects
		
		@return	2D array
	*/
	function get_all_projects(){
		$query = db_query("SELECT * FROM `projects` ORDER BY `id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[$row['id']]['proj_id'] = $row['proj_id'];
			$output[$row['id']]['name'] = $row['name'];
			$output[$row['id']]['description'] = $row['description'];
			$output[$row['id']]['year'] = $row['year'];
			$output[$row['id']]['quarter'] = $row['quarter'];
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
			array_walk_recursive($id, function(&$value, $key){
				str_clean($value);
				$value = htmlspecialchars($value);
			});
			
			$query = db_query("SELECT * FROM `projects` WHERE `{$from}` IN ('". implode("', '", $id) ."') ORDER BY `id` ASC;");
		
			$output = [];
			
			while($row = mysqli_fetch_assoc($query)){
				$output[$row['id']]['proj_id'] = $row['proj_id'];
				$output[$row['id']]['name'] = $row['name'];
				$output[$row['id']]['description'] = $row['description'];
				$output[$row['id']]['year'] = $row['year'];
				$output[$row['id']]['quarter'] = $row['quarter'];
			}
			
			return $output;
		}else{
			str_clean($id);
			
			return mysqli_fetch_assoc(db_query("SELECT * FROM `projects` WHERE `{$from}` = '{$id}';"));
		}
	}
	
	/*
		Returns a 2D array of all groups
		
		@return	2D array
	*/
	function get_all_groups(){
		$query = db_query("SELECT * FROM `groups` ORDER BY `id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[$row['id']]['name'] = $row['name'];
			
			try{
				$output[$row['id']]['supervisor'] = new Faculty($row['supervisor']);
			}catch(Exception $e){
				$output[$row['id']]['supervisor'] = null;
			}
			
			try{
				$output[$row['id']]['assessor'] = new Faculty($row['assessor']);
			}catch(Exception $e){
				$output[$row['id']]['assessor'] = null;
			}
			
			foreach(json_decode($row['members']) as $member_id){
				$output[$row['id']]['members'][] = new Student($member_id);
			}
			
			$output[$row['id']]['project'] = get_project($row['project'], "proj_id");
		}
		
		return $output;
	}
	
	/*
		Returns an array for a group
		
		@param	int/array
		@return array
	*/
	function get_group($id){		
		if(is_array($id)){
			array_walk_recursive($id, function(&$value, $key){
				str_clean($value);
				$value = htmlspecialchars($value);
			});
			
			$query = db_query("SELECT * FROM `groups` WHERE `id` IN ('". implode("', '", $id) ."') ORDER BY `id` ASC;");
		
			$output = [];
			
			while($row = mysqli_fetch_assoc($query)){
				$output[$row['id']]['name'] = $row['name'];
				
				try{
					$output[$row['id']]['supervisor'] = new Faculty($row['supervisor']);
				}catch(Exception $e){
					$output[$row['id']]['supervisor'] = null;
				}
				
				try{
					$output[$row['id']]['assessor'] = new Faculty($row['assessor']);
				}catch(Exception $e){
					$output[$row['id']]['assessor'] = null;
				}
				
				foreach(json_decode($row['members']) as $member_id){
					$output[$row['id']]['members'][] = new Student ($member_id);
				}
				
				$output[$row['id']]['project'] = get_project($row['project'], "proj_id");
			}
			
			return $output;
		}else{
			str_clean($id);
			
			$group = mysqli_fetch_assoc(db_query("SELECT * FROM `groups` WHERE `id` = '{$id}';"));
			
			$output['id'] = $group['id'];
			$output['name'] = $group['name'];
			
			try{
				$output['supervisor'] = new Faculty($group['supervisor']);
			}catch(Exception $e){
				$output['supervisor'] = null;
			}
			
			try{
				$output['assessor'] = new Faculty($group['assessor']);
			}catch(Exception $e){
				$output['assessor'] = null;
			}
			
			foreach(json_decode($group['members']) as $member_id){
				$output['members'][] = new Student ($member_id);
			}
			
			$output['project'] = get_project($group['project'], "proj_id");
			
			return $output;
		}
	}
	
	/*
		Returns an array for a group based on membership
		
		@param	string
		@return array
	*/
	function get_group_by_member($id){
		str_clean($id);
		
		$group = mysqli_fetch_assoc(db_query("SELECT * FROM `groups` WHERE JSON_CONTAINS(`members`, '\"{$id}\"');"));
			
		$output['id'] = $group['id'];
		$output['name'] = $group['name'];
		
		try{
			$output['supervisor'] = new Faculty($group['supervisor']);
		}catch(Exception $e){
			$output['supervisor'] = null;
		}
		
		try{
			$output['assessor'] = new Faculty($group['assessor']);
		}catch(Exception $e){
			$output['assessor'] = null;
		}
		
		foreach(json_decode($group['members']) as $member_id){
			$output['members'][] = new Student ($member_id);
		}
		
		$output['project'] = get_project($group['project'], "proj_id");
		
		return $output;
	}
	
	/*
		Returns an array for a group based on membership
		
		@param	string
		@return 2D array
	*/
	function get_group_by_faculty($id){
		str_clean($id);
		
		$query = db_query("SELECT * FROM `groups` WHERE `supervisor` = '{$id}' OR `assessor` = '{$id}';");
			
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[$row['id']]['name'] = $row['name'];
			
			try{
				$output[$row['id']]['supervisor'] = new Faculty($row['supervisor']);
			}catch(Exception $e){
				$output[$row['id']]['supervisor'] = null;
			}
			
			try{
				$output[$row['id']]['assessor'] = new Faculty($row['assessor']);
			}catch(Exception $e){
				$output[$row['id']]['assessor'] = null;
			}
			
			foreach(json_decode($row['members']) as $member_id){
				$output[$row['id']]['members'][] = new Student ($member_id);
			}
			
			$output[$row['id']]['project'] = get_project($row['project'], "proj_id");
		}
		
		return $output;
	}
?>
