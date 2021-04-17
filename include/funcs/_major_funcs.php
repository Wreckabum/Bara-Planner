<?php
	require_once(dirname(__FILE__)."/../class/ClassMajor.php");
	
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
				$output[$row['id']] = get_major($row['id']);
			}catch(Exception $e){
				continue;
			}
		}
		
		return $output;
	}
?>
