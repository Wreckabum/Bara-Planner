<?php
	require_once (dirname(__FILE__)."/../funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Base information for all accounts
	*/
	class Student extends Account{
		private $choices;
		private $year;
		private $quarter;
		
		/*
			Constructor
		*/
		public function __construct($id){
			parent::__construct($id);
			
			if($this->account_type != 1 && $this->account_type != 2){
				throw new Exception("Account is not a student.");
			}else{
				$this->majors = json_decode($this->raw['majors'])[0];
				$this->choices = json_decode($this->raw['choices']);
				$this->year = json_decode($this->raw['year']);			
				$this->quarter = json_decode($this->raw['quarter']);
				
				unset($this->raw);
			}
		}
		
		/*
			Get majors for the account
			
			@return string
		*/
		public function get_majors(){
			return $this->majors;
		}
		
		/*
			Get year of project
		*/
		public function get_year(){
			return $this->year;
		}
		
		/*
			Get quarter of project
		*/
		public function get_quarter(){
			return $this->quarter;
		}
		
		/*
			Checks if the account is a full-time student
			
			@return bool
		*/
		public function is_full_time(){
			return $this->account_type == 1;
		}
		
		/*
			Checks if the account is a part-time student
			
			@return bool
		*/
		public function is_part_time(){
			return $this->account_type == 2;
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>