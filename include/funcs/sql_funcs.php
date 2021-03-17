<?php
	require_once(dirname(__FILE__)."/config.php");
	require_once(dirname(__FILE__)."/../class/ClassAccount.php");
	require_once(dirname(__FILE__)."/../class/ClassAdmin.php");
	require_once(dirname(__FILE__)."/../class/ClassFaculty.php");
	require_once(dirname(__FILE__)."/../class/ClassStudent.php");
	require_once(dirname(__FILE__)."/../class/ClassGroup.php");
	require_once(dirname(__FILE__)."/../class/ClassProject.php");
	require_once(dirname(__FILE__)."/../class/ClassMajor.php");
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
		Returns the account object based on SIM ID
		
		@param	int
		@param	int
		@return	Faculty/Student/Admin object
	*/
	function get_account($id, $type = ""){
		str_clean($id);
		str_clean($type);
		
		if($type == ""){
			$type = mysqli_fetch_assoc(db_query("SELECT `type` FROM `accounts` WHERE `sim_id` = '{$id}';"))['type'];
		}
		
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
		Returns the account object based on SIM E-Mail
		
		@param	int
		@param	int
		@return	Faculty/Student/Admin object
	*/
	function get_account_by_email($email, $type = ""){
		str_clean($email);
		str_clean($type);
		
		if($type == ""){
			$type = mysqli_fetch_assoc(db_query("SELECT `type` FROM `accounts` WHERE `sim_email` = '{$email}';"))['type'];
		}
		
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
		Returns an array of all accounts (filter optional)
		
		@param	Array of ints [account types] (optional)
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
		
		$query = db_query("SELECT `sim_id`, `type` FROM `accounts` {$where} ORDER BY `sim_id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[] = get_account($row['sim_id'], $row['type']);
		}
		
		return $output;
	}
	
	/*
		Returns all students based on a semester (currently inefficient for large numbers)
		
		@param	int (optional)
		@param	int (optional)
		@param	Array of int (optional)		
		@param	bool (optional)
		@return	Array of Student objects
	*/
	function get_students($year = "*", $quarter = "*", $type = [1, 2], $check_group = false){
		str_clean($year);
		str_clean($quarter);
		
		$filter = [];
		$output = [];
		$type_checks = [];
		
		if(is_string($type) || is_int($type)){
			$type = [(int)$type];
		}
		
		if(in_array(1, $type)){
			$type_checks[] = 1;
		}
		
		if(in_array(2, $type)){
			$type_checks[] = 2;
		}
		
		if(count($type) == 0){
			$type_checks = [1, 2];
		}
		
		$filter[] = "`type` IN (". implode(", ", $type_checks) .")";
		
		if($year != "*"){
			$filter[] = "`year` = ". (int)$year;
		}

		if($quarter != "*"){
			$filter[] = "`quarter` = ". (int)$quarter;
		}
		
		$query = db_query("SELECT `sim_id`, `type` FROM `accounts` WHERE ". implode(" AND ", $filter));
		
		while($row = mysqli_fetch_assoc($query)){
			//If checking if already in group
			if($check_group){
				$check_query = db_query("SELECT `id` FROM `groups` WHERE JSON_CONTAINS(`members`, '\"{$row['sim_id']}\"');");
				
				if(mysqli_num_rows($check_query) != 0){
					continue;
				}
			}
			
			$output[] = get_account($row['sim_id'], $row['type']);
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
		Returns a Major Object / an Array of Major Objects
		
		@param	string/array
		@return Major Object / Array of Major Objects
	*/
	function get_major($id){
		if(is_array($id)){
			array_walk_recursive($id, function(&$value, $key){
				str_clean($value);
				$value = htmlspecialchars($value);
			});
		
			$output = [];
			
			while($row = mysqli_fetch_assoc($query)){
				try{
					$output[] = get_major($row['id']);
				}catch(Exception $e){
					continue;
				}
			}
			
			return $output;
		}else{
			str_clean($id);
			
			return new Major($id);
		}
	}
	
	/*
		Returns an Array of Major Objects
		
		@return	Array of Major Objects
	*/
	function get_all_majors(){
		$query = db_query("SELECT * FROM `majors` ORDER BY `id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			try{
				$output[] = get_major($row['id']);
			}catch(Exception $e){
				continue;
			}
		}
		
		return $output;
	}
	
	/*
		Returns a Project Object / an Array of Project Objects
		
		@param	int/array
		@param	string (optional)
		@return Project Object / Array of Project Objects
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
			
			$output = [];
			
			foreach($id as $i){
				try{
					$output[] = get_project($i, $from);
				}catch(Exception $e){
					continue;
				}
			}
			
			return $output;
		}else{
			str_clean($id);
			
			return new Project($id, $from);
		}
	}
	
	/*
		Returns an array of Project Objects
		
		@return	Array of Project Objects
	*/
	function get_all_projects(){
		$query = db_query("SELECT `id` FROM `projects` ORDER BY `id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			try{
				$output[] = get_project($row['id']);
			}catch(Exception $e){
				continue;
			}
		}
		
		return $output;
	}
	
	/*
		Returns a Group Objects / an Array of Group Objects
		
		@param	int/array
		@return Group Object / Array of Group Objects
	*/
	function get_group($id){		
		if(is_array($id)){
			array_walk_recursive($id, function(&$value, $key){
				str_clean($value);
				$value = htmlspecialchars($value);
			});
		
			$output = [];
			
			foreach($id as $i){
				try{
					$output[] = get_group($i);
				}catch(Exception $e){
					continue;
				}
			}
			
			return $output;
		}else{
			str_clean($id);
			
			return new Group($id);
		}
	}
	
	/*
		Returns an Array of Group Objects
		
		@return	Array of Group Objects
	*/
	function get_all_groups(){
		$query = db_query("SELECT `id` FROM `groups` ORDER BY `id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			try{
				$output[] = get_group($row['id']);
			}catch(Exception $e){
				continue;
			}
		}
		
		return $output;
	}
	
	/*
		Returns a Group Object
		
		@param	string
		@return Group Object
	*/
	function get_group_by_member($member_id){
		str_clean($member_id);
		
		$group = mysqli_fetch_assoc(db_query("SELECT `id` FROM `groups` WHERE JSON_CONTAINS(`members`, '\"{$member_id}\"');"));
			
		return get_group($group['id']);
	}
	
	/*
		Returns an Array of Group Objects
		
		@param	string
		@return Array of Group Objects
	*/
	function get_group_by_faculty($id){
		str_clean($id);
		
		$query = db_query("SELECT `id` FROM `groups` WHERE `supervisor` = '{$id}' OR `assessor` = '{$id}';");
			
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[] = get_group($row['id']);
		}
		
		return $output;
	}
	
	/*
		Checks if the faculty member is supervising/assessing the group members
		
		@param	string
		@param	string
		@return bool
	*/
	function check_same_group_faculty($id_1, $id_2){
		str_clean($id_1);
		str_clean($id_2);
		
		$query = db_query(
			"SELECT `id` FROM `groups` WHERE 
				(JSON_CONTAINS(`members`, '\"{$id_1}\"') AND
					(
						`supervisor` = '{$id_2}' OR
						`assessor` = '{$id_2}'
					)
				) 
				OR 
				(JSON_CONTAINS(`members`, '\"{$id_2}\"') AND
					(
						`supervisor` = '{$id_1}' OR
						`assessor` = '{$id_1}'
					)
				)
				OR
				(
					`supervisor` = '{$id_1}' AND
					`assessor` = '{$id_2}'
				)
				OR(
					`supervisor` = '{$id_2}' AND
					`assessor` = '{$id_1}'
				);");
		
		return ((mysqli_num_rows($query) <= 0) ? false : true);
	}
	
	/*
		Checks if the 2 members are in the same group
		
		@param	string
		@param	string
		@return bool
	*/
	function check_same_group_member($id_1, $id_2){
		str_clean($id_1);
		str_clean($id_2);
		
		$query = db_query("SELECT `id` FROM `groups` WHERE JSON_CONTAINS(`members`, '\"{$id_1}\"') AND JSON_CONTAINS(`members`, '\"{$id_2}\"');");
		
		return ((mysqli_num_rows($query) <= 0) ? false : true);
	}
	
	/*
		Returns the deadline for a semester
		
		@param	int
		@param	int
		@return Null / Date Object
	*/
	function get_deadline($year, $quarter){
		str_clean($year);
		str_clean($quarter);
		
		$query = db_query("SELECT `deadline` FROM `choice_deadlines` WHERE `year` = '{$year}' AND `quarter` = '{$quarter}';");
		
		if(mysqli_num_rows($query) <= 0){
			return NULL;
		}else{
			return mysqli_fetch_assoc($query)['deadline'];
		}
		
	}
	
	/*
		Returns the deadline for all semesters
		
		@param	int
		@param	int
		@return Null / Date Object
	*/
	function get_all_deadlines(){		
		$query = db_query("SELECT * FROM `choice_deadlines` ORDER BY `year` DESC, `quarter` DESC;");
		
		$output = [];
		
		while($row = mysqli_fetch_object($query)){
			$output[] = $row;
		}
		
		return $output;
	}
	
	/*
		Checks for existing semesters with no deadline, and updates the table accordingly
	*/
	function add_missing_deadlines(){		
		$all_deadlines = get_all_deadlines();	
		$available_semesters = get_semesters();
		
		foreach($available_semesters as $year => $quarters){
			foreach($quarters as $quarter){
				$found = false;
			
				foreach($all_deadlines as $deadline){
					if($deadline->year == $year && $deadline->quarter == $quarter){
						$found = true;
						break;
					}
				}
				
				if(!$found){
					db_query(
						"INSERT INTO
							`choice_deadlines`
								(`year`,
								`quarter`)
							VALUES
								('{$year}', 
								'{$quarter}');"
					);
				}
			}
		}
	}
?>
