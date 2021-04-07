<?php
	require_once(dirname(__FILE__)."/config.php");
	require_once(dirname(__FILE__)."/../class/ClassAccount.php");
	require_once(dirname(__FILE__)."/../class/ClassAdmin.php");
	require_once(dirname(__FILE__)."/../class/ClassFaculty.php");
	require_once(dirname(__FILE__)."/../class/ClassStudent.php");
	require_once(dirname(__FILE__)."/../class/ClassGroup.php");
	require_once(dirname(__FILE__)."/../class/ClassProject.php");
	require_once(dirname(__FILE__)."/../class/ClassMajor.php");
	require_once(dirname(__FILE__)."/../class/ClassGrades.php");
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
		$query = db_query("SELECT `id` FROM `majors` ORDER BY `id` ASC;");
		
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
	function get_all_projects($year = "*", $quarter = "*"){
		$filter = [];
		
		if($year != "*"){
			$filter[] = "`year` = '". (int)$year ."'";
		}
		
		if($quarter != "*"){
			$filter[] = "`quarter` = '". (int)$quarter ."'";
		}
		
		$filter_text = "";
		
		if(count($filter) > 0){
			$filter_text = "WHERE ". implode(" AND ", $filter);
		}
		
		$query = db_query("SELECT `id` FROM `projects` {$filter_text} ORDER BY `id` ASC;");
		
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
		
		$query = db_query("SELECT `id` FROM `groups` WHERE JSON_CONTAINS(`members`, '{\"id\" : \"{$member_id}\"}')");
		
		if(mysqli_num_rows($query) == 1){
			$group = mysqli_fetch_assoc($query);
				
			return get_group($group['id']);
		}
		
		return null;
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
				(JSON_CONTAINS(`members`, '{\"id\" : \"{$id_1}\"}') AND
					(
						`supervisor` = '{$id_2}' OR
						`assessor` = '{$id_2}'
					)
				) 
				OR 
				(JSON_CONTAINS(`members`, '{\"id\" : \"{$id_2}\"}') AND
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
		
		$query = db_query("SELECT `id` FROM `groups` WHERE JSON_CONTAINS(`members`, '{\"id\" : \"{$id_1}\"}') AND JSON_CONTAINS(`members`, '{\"id\" : \"{$id_2}\"}');");
		
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
		
		$query = db_query("SELECT `deadline` FROM `semester_details` WHERE `year` = '{$year}' AND `quarter` = '{$quarter}';");
		
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
		$query = db_query("SELECT `year`, `quarter`, `deadline` FROM `semester_details` ORDER BY `year` DESC, `quarter` DESC;");
		
		$output = [];
		
		while($row = mysqli_fetch_object($query)){
			$output[] = $row;
		}
		
		return $output;
	}
	
	/*
		Checks for existing semesters with no deadline, and updates the table accordingly
		
		@return	bool
	*/
	function add_missing_deadlines(){
		$all_deadlines = get_all_deadlines();	
		$available_semesters = get_semesters();
		$new_semester_added = false;
		
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
							`semester_details`
								(`year`,
								`quarter`)
							VALUES
								('{$year}', 
								'{$quarter}');"
					);
					
					$new_semester_added = true;
				}
			}
		}
		
		if($new_semester_added){
			return true;
		}else{
			return false;
		}
	}
	
	/*
		Returns the details for a semester
		
		@param	int
		@param	int
		@return Array
	*/
	function get_semester_details($year, $quarter){
		str_clean($year);
		str_clean($quarter);
		
		$query = db_query("SELECT * FROM `semester_details` WHERE `year` = '{$year}' AND `quarter` = '{$quarter}';");
		
		if(mysqli_num_rows($query) <= 0){
			return NULL;
		}else{
			return mysqli_fetch_object($query);
		}
		
	}
	
	/*
		Returns the details for a semester
		
		@param	int ($year)
		@param	int ($quarter)
		@param	int ($type) [1/2]
		@param	int ($min)
		@param	int ($max)
	*/
	function test_auto_grouping(){
		/* 
			TODO:
				Handle extras when current target not meant using $max as limiter
				Group last batch if applicable
		 */
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$start = microtime(true);
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		//Declarefor testing
		$year = 2020;
		$quarter = 1;
		$type = 1;
		$min = 5;
		$max = 6;
		
		$applicable_students = get_students($year, $quarter, $type, true); //All students not in a group
		$all_projects = get_all_projects($year, $quarter); //All projects
		$project_ids = [];
		
		//Get project IDs
		array_walk($all_projects, function(&$value, $key) use (&$project_ids){
			$project_ids[] = $value->id;
		});
		
		$student_choices_weight = [];
		
		//For each student
		foreach($applicable_students as $student){
			$temp_choices = []; //Prepare choices array
			
			//Loop through each possible project choice
			foreach($project_ids as $project_id){
				$weight = 0; //Default weight
				
				//If student's choice, update weight
				foreach($student->get_choices() as $rank => $id){
					if($id == $project_id){
						switch($rank){
							case 0:
								$weight = 3;
								break;
							
							case 1:
								$weight = 2;
								break;
							
							case 2:
								$weight = 1;
								break;
						}
					}
				}
			
				$temp_choices[$project_id] = $weight; //Add to weight array for student
			}
			
			$student_choices_weight[$student->sim_id] = $temp_choices;
		}
		
		//Declare variables for grouping
		$groups = []; //Final group array
		$target_total_weight = $min * 3; //Target weight
		
		//Loop to create the groups whilst there are still positions available
		while(count($student_choices_weight) > $min){
			echo "Trying to target weight {$target_total_weight}<br>";
			
			foreach($project_ids as $project_id){
				$current_group = []; //Prepare temporary array for current group
				$current_total_weight = 0;
				
				uasort($student_choices_weight, function($a, $b) use (&$project_id){
					return $b[$project_id] - $a[$project_id];
				});
				
				//Loop through each $project_id and start grouping students
				foreach($student_choices_weight as $student_id => $choices){
					//Current group does not have $min students
					if(count($current_group) < $min){
						//Add current student to group
						$current_group[$student_id] = $choices;
						$current_total_weight += $choices[$project_id];
					}else{
						//Current group has $min students
						
						//If current group meets the current target weight
						if($current_total_weight >= $target_total_weight){
							//Add to groups
							$groups[$project_id][$current_total_weight][] = $current_group;
							
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Added group with total weight of {$current_total_weight} to Project #{$project_id} -> First ID: ". array_keys($current_group)[0] ."<br>";
							
							//Remove from students array
							foreach($current_group as $temp_id => $temp_choices){
								unset($student_choices_weight[$temp_id]);
							}
							
							$current_total_weight = 0; //Reset counter
							$current_group = []; //Reset temporary container
							
							//Add current student to group
							$current_group[$student_id] = $choices;
							$current_total_weight += $choices[$project_id];
						}else{
							//Can try to disperse where possible using $max as a limiter
							//TODO
							
							break; //Move on to the next $project_id
						}
					}
				}
			}
			
			$target_total_weight--; //Try next best
			
			echo "&nbsp;&nbsp;&nbsp;&nbsp;Left with ". count($student_choices_weight) ." students<br>";
		}
		
		//Finish final grouping of applicable
		if(count($student_choices_weight) >= $min){
			//TODO
		}
		
		echo "<br>";
		foreach($student_choices_weight as $student_id => $choices){
			echo $student_id ." -> ";print_r($choices);echo"<br>";
		}
		
		/* ini_set("xdebug.var_display_max_children", '-1');
		ini_set("xdebug.var_display_max_data", '-1');
		ini_set("xdebug.var_display_max_depth", '-1');
		var_dump($groups); */
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//Time taken
		var_dump(microtime(true) - $start);
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}
?>
