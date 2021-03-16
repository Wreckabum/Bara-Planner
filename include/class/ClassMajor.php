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
				$this->name = $result['name'];
				$this->description = $result['description'];
			}else{
				throw new Exception("Major not found.");
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
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>