<?php
	require_once(dirname(__FILE__)."/../class/ClassProject.php");
	require_once(dirname(__FILE__)."/../class/ClassArchivedProject.php");
	
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
		
		@param	int (optional)
		@param	int (optional)
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
				$output[$row['id']] = get_project($row['id']);
			}catch(Exception $e){
				continue;
			}
		}
		
		return $output;
	}
	
	/*
		Returns the archived project object based on project ID, year, quarter
		
		@param	string
		@param	int
		@param	int
		@return	ArchivedProject object
	*/
	function get_archived_project($proj_id, $year, $quarter){
		str_clean($proj_id);
		str_clean($year);
		str_clean($quarter);
		
		try{
			return new ArchivedProject($proj_id, $year, $quarter);
		}catch(Exception $e){
			return null;
		}
	}
	
	/*
		Returns an array of all archived project objects
		
		@return	array of ArchivedProject objects
	*/
	function get_all_archived_projects(){
		$query = db_query("SELECT `id`, `proj_id`, `year`, `quarter` FROM `archive_projects`;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			try{
				$output[$row['id']] = get_archived_project($row['proj_id'], $row['year'], $row['quarter']);
			}catch(Exception $e){
				continue;
			}
		}
		
		return $output;
	}
?>
