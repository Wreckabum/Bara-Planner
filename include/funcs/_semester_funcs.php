<?php
	require_once(dirname(__FILE__)."/config.php");
	require_once(dirname(__FILE__)."/../class/ClassGrades.php");
	require_once(dirname(__FILE__)."/sub_funcs.php");
	require_once(dirname(__FILE__)."/_account_funcs.php");
	require_once(dirname(__FILE__)."/_major_funcs.php");
	require_once(dirname(__FILE__)."/_project_funcs.php");
	require_once(dirname(__FILE__)."/_group_funcs.php");
	
	/*
		Returns all semester columns as object
		
		@paran	int
		@paran	int
		@return	Object
	*/
	function get_semester($year, $quarter){
		str_clean($year);
		str_clean($quarter);
		
		return mysqli_fetch_object(db_query("SELECT * FROM `semester_details` WHERE `year` = '{$year}' AND `quarter`  = '{$quarter}';"));
	}
	
	/*
		Returns all semester objects
		
		@return Array ofDate Objects
	*/
	function get_all_semesters(){
		$query = db_query("SELECT * FROM `semester_details` ORDER BY `year` DESC, `quarter` DESC;");
		
		$output = [];
		
		while($row = mysqli_fetch_object($query)){
			$output[] = $row;
		}
		
		return $output;
	}
		
	/*
		Returns all available years/quarters
		
		@return	2D array of [Year => [Quarter]]
	*/
	function get_all_semesters_quarters(){
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
		$available_semesters = get_all_semesters_quarters();
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
		Returns the default marking scheme
		
		@param	string (opt)
		@return Multidimensional array / JSON
	*/
	function get_default_marking_scheme($type = "array"){
		if($type == "array"){
			return
				[
				  'faculty' => [
					1 => [
					  'desc' => 'Project requirements documentation',
					  'weight' => 10,
					  'week_due' => 5,
					],
					2 => [
					  'desc' => 'Project progress presentation (Prototype demonstration]',
					  'weight' => 10,
					  'week_due' => 11,
					],
					3 => [
					  'desc' => 'Project progress report',
					  'weight' => 15,
					  'week_due' => 11,
					],
					4 => [
					  'desc' => 'Final product and documentation',
					  'weight' => 45,
					  'week_due' => 19,
					  'parts' => [
						'a' => [
						  'desc' => 'Source code',
						  'weight' => 10,
						],
						'b' => [
						  'desc' => 'Technical report/manual',
						  'weight' => 10,
						],
						'c' => [
						  'desc' => 'User manual',
						  'weight' => 10,
						],
						'd' => [
						  'desc' => 'Testing documentation',
						  'weight' => 10,
						],
						'e' => [
						  'desc' => 'Project website',
						  'weight' => 5,
						],
					  ],
					],
					5 => [
					  'desc' => 'Final Presentation',
					  'weight' => 15,
					  'week_due' => 20,
					],
					6 => [
					  'desc' => 'Penalty',
					],
				  ],
				  'student' => [
					'weight' => 5,
				  ],
				];
		}else{
			return 
				'{
					"faculty": {
						"1": {
							"desc": "Project requirements documentation", 
							"weight": 10, 
							"week_due": 5
						},
						"2": {
							"desc": "Project progress presentation (Prototype demonstration)", 
							"weight": 10, 
							"week_due": 11
						},
						"3": {
							"desc": "Project progress report", 
							"weight": 15, 
							"week_due": 11
						},
						"4": {
							"desc": "Final product and documentation", 
							"week_due": 19,
							"parts": {
								"a": {
									"desc": "Source code", 
									"weight": 10
								},
								"b": {
									"desc": "Technical report/manual", 
									"weight": 10
								},
								"c": {
									"desc": "User manual", 
									"weight": 10
								},
								"d": {
									"desc": "Testing documentation", 
									"weight": 10
								},
								"e": {
									"desc": "Project website", 
									"weight": 5
								}
							}
							
						},
						"5": {
							"desc": "Final Presentation", 
							"weight": 15, 
							"week_due": 20
						},
						"6": {
							"desc": "Penalty"
						}
					},
					"student": {
						"weight": 5
					}
				}';
		}
	}
?>