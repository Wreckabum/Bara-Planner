<?php
	require_once(dirname(__FILE__)."/../class/ClassGroup.php");
	require_once(dirname(__FILE__)."/../class/ClassArchivedGroup.php");
	
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
		
		@param	int (optional)
		@param	int (optional)
		@return	Array of Group Objects
	*/
	function get_all_groups($year = "*", $quarter = "*"){		
		$query = db_query("SELECT `id` FROM `groups` ORDER BY `id` ASC;");
		
		$output = [];
		
		while($row = mysqli_fetch_assoc($query)){
			try{
				$this_group = get_group($row['id']); //(int)$year
				
				//If $year was given, and this group is not from said year, skip
				if($year != "*" && $this_group->get_year() != (int)$year){
					continue;
				}
				
				//If $quarter was given, and this group is not from said quarter, skip
				if($year != "*" && $this_group->get_quarter() != (int)$quarter){
					continue;
				}
				
				$output[] = $this_group;
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
		Returns the archived group object based on group name, year, quarter
		
		@param	string
		@param	int
		@param	int
		@return	ArchivedGroup object
	*/
	function get_archived_group($name, $year, $quarter){
		str_clean($name);
		str_clean($year);
		str_clean($quarter);
		
		try{
			return new ArchivedGroup($name, $year, $quarter);
		}catch(Exception $e){
			return null;
		}
	}
?>
