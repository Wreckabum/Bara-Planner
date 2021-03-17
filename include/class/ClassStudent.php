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
				$this->year = $this->raw['year'];			
				$this->quarter = $this->raw['quarter'];
				
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
			Get choices
		*/
		public function get_choices(){
			if(is_null($this->choices)){
				return [];
			}else{
				return $this->choices;
			}
		}
		
		/*
			Get deadline date
		*/
		public function get_choices_deadline(){
			return get_deadline($this->get_year(), $this->get_quarter());
		}
		
		/*
			Check if the student can still make a chocie based on the deadline
			
			@return bool
		*/
		public function can_make_choice(){
			return (strtotime(date("Y-m-d")) - strtotime($this->get_choices_deadline()) <= 0);
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