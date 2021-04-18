<?php
	require_once (dirname(__FILE__)."/../funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Class for projects
	*/
	class ArchivedProject{
		public	$id;
		public	$proj_id;
		
		private	$name;
		private	$description;
		private	$year;
		private	$quarter;
		
		/*
			Constructor
		*/
		public function __construct($proj_id, $year, $quarter){
			str_clean($proj_id);
			str_clean($year);
			str_clean($quarter);
			
			$query = db_query("SELECT * FROM `archive_projects` WHERE `proj_id` = '{$proj_id}' AND `year` = '{$year}' AND `quarter` = '{$quarter}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the poject exists
			if(mysqli_num_rows($query) == 1){
				$this->id = $result['id'];
				$this->proj_id = $result['proj_id'];
				$this->name = htmlspecialchars_decode($result['name']);
				$this->description = htmlspecialchars_decode($result['description']);
				$this->year = $result['year'];
				$this->quarter = $result['quarter'];
			}else{
				throw new Exception("Archived project not found.");
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
			Get year
		*/
		public function get_year(){
			return (int)$this->year;
		}
		
		/*
			Get quarter
		*/
		public function get_quarter(){
			return (int)$this->quarter;
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>