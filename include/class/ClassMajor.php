<?php
	require_once (dirname(__FILE__)."/../funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Class for majors
	*/
	class Major{
		public	$id;
		private	$name;
		private	$description;
		private $type;
		
		/*
			Constructor
		*/
		public function __construct($id){
			$id = str_clean($id);
			
			$query = db_query("SELECT * FROM `majors` WHERE `id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the poject exists
			if(mysqli_num_rows($query) == 1){
				$this->id = $result['id'];
				$this->name = htmlspecialchars_decode($result['name']);
				$this->description = htmlspecialchars_decode($result['description']);
				$this->type = $result['type'];
			}else{
				throw new Exception("Major not found.");
			}			
		}
		
		/*
			Get name
			
			@param	bool
		*/
		public function get_name($safe = false){
			if($safe){
				return htmlspecialchars($this->name, ENT_QUOTES);
			}else{
				return $this->name;
			}
		}
		
		/*
			Get description
			
			@param	bool
		*/
		public function get_description($safe = false){
			if($safe){
				return htmlspecialchars($this->description, ENT_QUOTES);
			}else{
				return $this->description;
			}
		}
		
		/*
			Get type
		*/
		public function get_type(){
			return $this->type;
		}
		
		/*
			Get student type
		*/
		public function get_student_type(){
			return (($this->is_full_time()) ? 1 : 2);
		}
		
		/*
			Checks if the major is for full-time students
			
			@return bool
		*/
		public function is_full_time(){
			return $this->type == 1;
		}
		
		/*
			Checks if the major is for part-time students
			
			@return bool
		*/
		public function is_part_time(){
			return $this->type == 0;
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>