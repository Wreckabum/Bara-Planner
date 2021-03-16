<?php
	require_once (dirname(__FILE__)."/../funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Class for projects
	*/
	class Project{
		public	$id;
		public	$proj_id;
		
		private	$name;
		private	$description;
		
		private	$year;
		private	$quarter;
		
		/*
			Constructor
		*/
		public function __construct($id, $from = "id"){
			if($from != "proj_id"){
				$from = "id";
			}
			
			$id = str_clean($id);
			
			$query = db_query("SELECT * FROM `projects` WHERE `{$from}` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the poject exists
			if(mysqli_num_rows($query) == 1){
				$this->id = $result['id'];
				$this->proj_id = $result['proj_id'];
				
				$this->name = $result['name'];
				$this->description = $result['description'];
				
				$this->year = $result['year'];
				$this->quarter = $result['quarter'];
			}else{
				throw new Exception("Project not found.");
			}			
		}
		
		/*
			Get name
		*/
		public function get_name(){
			return $this->name;
		}
		
		/*
			Get description
		*/
		public function get_description(){
			return $this->description;
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