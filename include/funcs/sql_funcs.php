<?php
	require_once(dirname(__FILE__)."/config.php");
	require_once(dirname(__FILE__)."/../class/ClassGrades.php");
	require_once(dirname(__FILE__)."/sub_funcs.php");
	require_once(dirname(__FILE__)."/_account_funcs.php");
	require_once(dirname(__FILE__)."/_major_funcs.php");
	require_once(dirname(__FILE__)."/_project_funcs.php");
	require_once(dirname(__FILE__)."/_group_funcs.php");
	
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
		
		@param	int ($year)
		@param	int ($quarter)
		@param	int ($type) [1/2]
		@param	int ($min)
		@param	int ($max)
		$return	Multidemnsional-Array
	*/
	function auto_group($year, $quarter, $type, $min, $max){
		str_clean($year);
		str_clean($quarter);
		str_clean($type);
		str_clean($min);
		str_clean($max);
	
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
			foreach($project_ids as $project_id){
				$current_group = []; //Prepare temporary array for current group
				$current_total_weight = 0;
				
				uksort($student_choices_weight, function($a, $b) use (&$student_choices_weight, &$project_id){
					if($student_choices_weight[$b][$project_id] == $student_choices_weight[$a][$project_id]){
						return $a - $b;
					}else{
						return $student_choices_weight[$b][$project_id] - $student_choices_weight[$a][$project_id];
					}
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
							//Prepare containers for student by weight in current group to distribute
							$first_choice = [];
							$second_choice = [];
							$third_choice = [];
							
							//Sort out the remaining students based on weight
							foreach($current_group as $this_member_id => $this_member_choices){
								if($this_member_choices[$project_id] == 3){
									$first_choice[$this_member_id] = $current_group[$this_member_id];
								}elseif($this_member_choices[$project_id] == 2){
									$second_choice[$this_member_id] = $current_group[$this_member_id];
								}elseif($this_member_choices[$project_id] == 1){
									$third_choice[$this_member_id] = $current_group[$this_member_id];
								}
								
								unset($current_group[$this_member_id]); //Clear current group
							}
							
							//Which weight to attemp to distribute (prevents distributing too early)
							if($target_total_weight / $min === 3){
								$current_group = $first_choice;
							}elseif($target_total_weight / $min === 2){
								$current_group = $second_choice;
							}elseif($target_total_weight / $min === 1){
								$current_group = $third_choice;
							}
							
							//Try to distribute
							foreach($current_group as $this_member_id => $this_member_choices){
								$student_distributed = false;
								
								//Ensure project already has a group
								if(isset($groups[$project_id])){
									foreach($groups[$project_id] as $total_weight_key => $weighted_groups){
										if($student_distributed){
											break;
										}
										//Count and check if there are slots available for even distribution
										$total_number_of_groups_in_weight = 0;
										$total_members_in_project_weight = 0;
										
										foreach($groups[$project_id][$total_weight_key] as $group_key => $group_members){
											$total_number_of_groups_in_weight++;
											$total_members_in_project_weight += count($group_members);
										}
										
										//If can distribute
										if(
											($total_members_in_project_weight + count($current_group)) % $max == 0 || //Perfect distribution
											($total_number_of_groups_in_weight * $max) - ($total_members_in_project_weight + count($current_group)) > 0 //If remainder can fit within existing groups
										){
											foreach($weighted_groups as $weighted_group_key => $weighted_group_members){
												if($student_distributed){
													break;
												}
												
												//If an existing group still has slots available
												if(count($weighted_group_members) < $max){
													//Add this student to the group
													$groups[$project_id][$total_weight_key][$weighted_group_key][$this_member_id] = $this_member_choices;
													
													//Remove from students array
													unset($student_choices_weight[$this_member_id]);
													
													$student_distributed = true;
												}
											}
										}
									}
								}
							}
							
							break; //Move on to the next $project_id
						}
					}
				}
			}
			
			$target_total_weight--; //Try next best
		}
		
		//Distribute any remaining students
		if(count($student_choices_weight) >= 0){
			//For each remaining student
			foreach($student_choices_weight as $student_id => $choices){
				$student_preferred_choices = [];
				$student_preferred_choices[] = array_search(3, $choices);
				$student_preferred_choices[] = array_search(2, $choices);
				$student_preferred_choices[] = array_search(1, $choices);
				
				$student_distributed = false;
				
				//Check the desired choices
				foreach($student_preferred_choices as $choice_key => $choice_weight){
					//If existing group exists
					if(isset($groups[$choice_weight])){
						//Loop through the groups for the project
						foreach($groups[$choice_weight] as $group_weight => $group_keys){
							if($student_distributed){
								break;
							}
							
							//Check all groups regardless of weight
							foreach($group_keys as $group_key => $student_ids){
								if($student_distributed){
									break;
								}
								
								//If there is still a slot available
								if(count($student_ids) < $max){
									//Add to group
									$groups[$choice_weight][$group_weight][$group_key][$student_id] = $choices;
									
									//Remove from students array
									unset($student_choices_weight[$student_id]);
									
									$student_distributed = true;
									break;
								}
							}
						}
					}
				}
				
			}
		}
		
		return $groups;
	}
?>
