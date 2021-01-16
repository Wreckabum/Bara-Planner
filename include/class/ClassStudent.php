<?php
	require_once("include/funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Base information for all accounts
	*/
	class Student extends Account{
		private $choices;
		
		/*
			Constructor
		*/
		public function __construct($id){
			parent::__construct($id);
			
			$this->majors = json_decode($this->raw['majors'])[0];
			$this->choices = json_decode($this->raw['choices']);
			
			unset($this->raw);
		}
		
		/*
			Get majors for the account
			
			@return string
		*/
		public function get_majors(){
			return $this->majors;
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>