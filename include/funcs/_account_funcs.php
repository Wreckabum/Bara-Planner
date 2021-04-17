<?php
	require_once(dirname(__FILE__)."/../class/ClassAccount.php");
	require_once(dirname(__FILE__)."/../class/ClassAdmin.php");
	require_once(dirname(__FILE__)."/../class/ClassFaculty.php");
	require_once(dirname(__FILE__)."/../class/ClassStudent.php");
	require_once(dirname(__FILE__)."/../class/ClassArchivedStudent.php");
	
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
			$query = db_query("SELECT `type` FROM `accounts` WHERE `sim_id` = '{$id}';");
			
			if(mysqli_num_rows($query) > 0){
				$type = mysqli_fetch_assoc($query)['type'];
			}else{
				throw new Exception("Account not found.");
			}
		}
		
		if($type == 1 || $type == 2){
			return new Student($id);
		}elseif($type == 8 || $type == 9){
			return new Admin($id);
		}elseif($type == 0){
			return new Faculty($id);
		}
		
		throw new Exception("Account type not found.");
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
		
		$query = db_query("SELECT `sim_id`, `type` FROM `accounts` WHERE `sim_email` = '{$email}';");
		
		if(mysqli_num_rows($query) > 0){
			$result = mysqli_fetch_assoc($query);
			
			$id = $result['sim_id'];
			
			if($type == ""){
				$type = $result['type'];
			}
		}else{
			throw new Exception("Account not found.");
		}
		
		if($type == 1 || $type == 2){
			return new Student($id);
		}elseif($type == 8 || $type == 9){
			return new Admin($id);
		}elseif($type == 0){
			return new Faculty($id);
		}
		
		throw new Exception("Account type not found.");
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
		
		if(count($type_checks) == 0){
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
				$check_query = db_query("SELECT `id` FROM `groups` WHERE JSON_CONTAINS(`members`, '{\"id\" : \"{$row['sim_id']}\"}')");
				
				if(mysqli_num_rows($check_query) != 0){
					continue;
				}
			}
			
			$output[] = get_account($row['sim_id'], $row['type']);
		}
		
		return $output;
	}
	
	/*
		Returns the archived account object based on SIM ID, year, quarter
		
		@param	int
		@param	int
		@param	int
		@return	ArchivedStudent object
	*/
	function get_archived_account($sim_id, $year, $quarter){
		str_clean($sim_id);
		str_clean($year);
		str_clean($quarter);
		
		try{
			return new ArchivedStudent($sim_id, $year, $quarter);
		}catch(Exception $e){
			return null;
		}
	}
	
	/*
		Returns an array of all archived student accounts (filter optional)
		
		@param	Array of ints [account types] (optional)
		@return	Array of ArchivedStudent objects
	*/
	function get_all_archived_accounts($type = []){
		array_walk($type, function(&$value, $key){
			$value = (int)$value;
		});
		
		$where = "";
		
		if(!empty($type)){
			$where = "WHERE `type` in ('". implode("', '", $type) ."')";
		}
		
		$query = db_query("SELECT `sim_id`, `year`, `quarter` FROM `archive_accounts` {$where} ORDER BY `sim_id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			$output[] = get_archived_account($row['sim_id'], $row['year'], $row['quarter']);
		}
		
		return $output;
	}
?>
