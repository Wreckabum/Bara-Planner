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
		}elseif($type == "json"){
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
	
	/*
		Returns HTML code for the marking scheme
		
		@param	int
		@param	int
		@param	string
		@return HTML table
	*/
	function print_marking_scheme($year, $quarter, $type = "view", $use_default = false){
		if($use_default){
			$marking_scheme = json_decode(get_default_marking_scheme("json"));
		}else{
			$json_marking_scheme = mysqli_fetch_assoc(db_query("SELECT `marking_scheme` FROM `semester_details` WHERE `year` = '{$year}' AND `quarter` = '{$quarter}';"))['marking_scheme'];
			
			//If no marking scheme, return default
			if(is_null($json_marking_scheme) || $json_marking_scheme == "null"){
				return false;
			}
			
			$marking_scheme = json_decode($json_marking_scheme);
		}
		
		$output = 
			"<table class='basic_table' style='width:auto%;'>
				<tr>
					<td colspan='2'>
						Item
					</td>
					<td>
						Assignment Items & Format
					</td>
					<td>
						Week Due
					</td>
					<td>
						Max Marks (%)
					</td>
					<td>
						Supervisor
					</td>
					<td>
						Assessor
					</td>
					<td>
						Total
					</td>
					<td>
						Average
					</td>
				</tr>";
		
		foreach($marking_scheme->faculty as $section => $section_details){
			$output .=
				"<tr>
					<td colspan='2'>
						{$section}
					</td>
					<td>
						{$section_details->desc}
					</td>";
			
			if($section_details->desc == "Penalty"){
				$output .=
					"<td class='due penalty'>-</td>
					<td class='weight penalty'>-%</td>
					<td class='supervisor penalty'></td>
					<td class='assessor penalty'></td>
					<td class='total penalty'></td>
					<td class='average penalty'></td>
				</tr>";
			}else{
				$output .=
					"<td class='due' ". ((isset($section_details->parts)) ? "rowspan='". (count((array)$section_details->parts) + 1) ."'" : "") .">
						{$section_details->week_due}
					</td>";
					
				if(isset($section_details->parts)){
					$output .=
						"<td colspan='5' class='empty'>-</td>
					</tr>";
				
						foreach($section_details->parts as $part => $part_details){
							$output .=
								"<tr>
									<td class='empty'>-</td>
									<td>
										{$part}
									</td>
									<td>
										{$part_details->desc}
									</td>
									<td class='weight'>
										{$part_details->weight}%
									</td>
									<td class='supervisor'></td>
									<td class='assessor'></td>
									<td class='total'></td>
									<td class='average'></td>
								</tr>";
						}
				}else{
					$output .=
						"<td class='weight'>
							{$section_details->weight}%
						</td>
						<td class='supervisor'></td>
						<td class='assessor'></td>
						<td class='total'></td>
						<td class='average'></td>
					</tr>";
				}
			}
		}
		
		$output .=
				"<tr>
					<td colspan='2' class='empty'>-</td>
					<td>
						Individual Student
					</td>
					<td class='empty'>-</td>
					<td>
						{$marking_scheme->student->weight}%
					</td>
					<td colspan='4' class='empty'>-</td>
				</tr>
			</table>";
		
		return $output;
	}
?>