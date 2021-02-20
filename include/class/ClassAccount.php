<?php
	require_once("include/funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Base information for all accounts
	*/
	abstract class Account{
		protected $raw;
		
		public	$id;
		private	$name;
		private	$phone;
		private	$email;
		protected int $account_type;
		
		protected $majors;
		
		/*
			Constructor
		*/
		public function __construct($id){
			$id = str_clean($id);
			
			$query = db_query("SELECT * FROM `accounts` WHERE `id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the account exists
			if(mysqli_num_rows($query) == 1){
				$this->id = $result['id'];
				$this->name = $result['name'];
				$this->phone = $result['phone'];
				$this->email = $result['email'];
				$this->account_type = $result['type'];
				$this->raw = $result;
			}else{
				throw new Exception("Account not found.");
			}
		}
		
		/*
			Get name
		*/
		public function get_name(){
			return $this->name;
		}
		
		/*
			Get phone number
		*/
		public function get_phone(){
			return $this->phone;
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
				case 8:
					return "Administrator";
					break;
				case 9:
					return "Super Administrator";
					break;
				default:
					return "Unknown";
					break;
			};
		}
		
		/*
			Get position
		*/
		public function get_position(){
			return $this->position;
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
			Checks if the account is an admin
			
			@return bool
		*/
		public function is_admin(){
			return ($this->account_type == 8 || $this->account_type == 9);
		}
		
		/*
			Checks if the account is a super admin
			
			@return bool
		*/
		public function is_super(){
			return $this->account_type == 9;
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>