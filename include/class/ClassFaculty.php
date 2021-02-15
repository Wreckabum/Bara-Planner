<?php
	require_once("include/funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Base information for all accounts
	*/
	class Faculty extends Account{
		private $position;
		private $experience;
		
		/*
			Constructor
		*/
		public function __construct($id){
			parent::__construct($id);
			
			if($this->account_type == 1 || $this->account_type == 2){
				throw new Exception("Account is not part of faculty.");
			}else{
				$this->position = $this->raw['position'];
				$this->experience = $this->raw['experience'];
				$this->majors = json_decode($this->raw['majors']);
				
				unset($this->raw);
			}
		}
		
		/*
			Get majors for the account
			
			@param	bool
			
			True:
				@return string
			
			False:
				@return array of string
		*/
		public function get_majors($as_string = false){
			if($as_string){
				if($this->majors != null){
					return (implode(", ", $this->majors));
				}else{
					//For admin staff
					return "N/A";
				}
			}else{
				return $this->majors;
			}
		}
		
		/*
			Get Position
		*/
		public function get_position(){
			return $this->position;
		}
		
		/*
			Get Experience
		*/
		public function get_experience(){
			return $this->experience;
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>