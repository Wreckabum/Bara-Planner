<?php
	require_once("include/funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Base information for all accounts
	*/
	abstract class Account{
		protected $raw;
		
		public	$id;
		private	$first_name;
		private	$last_name;
		private	$dob;
		private	$email;
		private $account_type;
		
		protected $majors;
		
		/*
			Constructor
		*/
		public function __construct($id){
			$id = str_clean($id);
			
			$query = db_query("SELECT * FROM `accounts` WHERE `id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			$this->raw = $result;
			
			//If the account exists
			if(mysqli_num_rows($query) == 1){
				$this->id = $result['id'];
				$this->first_name = $result['first_name'];
				$this->last_name = $result['last_name'];
				$this->dob = $result['dob'];
				$this->email = $result['email'];
				$this->account_type = $result['type'];
			}else{
				throw new Exception("Account not found.");
			}
		}
		
		/*
			Get first name
		*/
		public function get_first_name(){
			return $this->first_name;
		}
		
		/*
			Get last name
		*/
		public function get_last_name(){
			return $this->last_name;
		}
		
		/*
			Get full name
		*/
		public function get_full_name(){
			return $this->first_name . " " . $this->last_name;
		}
		
		/*
			Get date of birth
		*/
		public function get_dob(){
			return $this->dob;
		}
		
		/*
			Get E-mail
		*/
		public function get_email(){
			return $this->email;
		}
		
		/*
			Get account type
		*/
		public function get_account_type(){
			switch($this->account_type){
				case 0:
					return "Faculty";
					break;
				case 1:
					return "Full-time Student";
					break;
				case 2:
					return "Part-time Student";
					break;
				default:
					return "Unknown";
					break;
			};
		}
		
		/*
			Checks if the account is a faculty member
			
			@return bool
		*/
		public function is_faculty(){
			return $this->account_type == 0;
		}
		
		/*
			Checks if the account is a student
			
			@return bool
		*/
		public function is_student(){
			return ($this->account_type == 1 || $this->account_type == 2);
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
		
		/*
			Get majors for the account
			
			Faculty:
				@return array of string
			
			Students:
				@return string
		*/
		abstract public function get_majors();
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>