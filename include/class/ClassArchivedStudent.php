<?php
	require_once (dirname(__FILE__)."/../funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Base information for all accounts
	*/
	class ArchivedStudent{
		public	$sim_id;
		public	$uow_id;
		
		private	$name;
		private	$phone;
		private	$sim_email;
		private	$personal_email;
		private $account_type;
		private $major_id;
		private $major_name;
		private $choices;
		private $year;
		private $quarter;
		
		/*
			Constructor
		*/
		public function __construct($id, $year, $quarter){
			str_clean($id);
			str_clean($year);
			str_clean($quarter);
			
			$query = db_query("SELECT * FROM `archive_accounts` WHERE `sim_id` = '{$id}' AND `year` = '{$year}' AND `quarter` = '{$quarter}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the account exists
			if(mysqli_num_rows($query) == 1){
				$this->sim_id = $result['sim_id'];
				$this->uow_id = $result['uow_id'];
				
				$this->name = htmlspecialchars_decode($result['name']);
				$this->phone = $result['phone'];
				$this->sim_email = $result['sim_email'];
				$this->personal_email = $result['personal_email'];
				$this->account_type = $result['type'];
				$this->major_id = $result['major_id'];
				$this->major_name = $result['major_name'];
				$this->choices = json_decode($result['choices']);
				$this->year = $result['year'];
				$this->quarter = $result['quarter'];
			}else{
				throw new Exception("Archived account not found.");
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
			Get phone number
		*/
		public function get_phone(){
			return $this->phone;
		}
		
		/*
			Get SIM E-mail
		*/
		public function get_sim_email(){
			return $this->sim_email;
		}
		
		/*
			Get personal E-mail
		*/
		public function get_personal_email(){
			return $this->personal_email;
		}
		
		/*
			Get account type int
		*/
		public function get_type_int(){
			return $this->account_type;
		}
		
		/*
			Get account type
		*/
		public function get_account_type(){
			switch($this->account_type){
				case 1:
					return "Full-time Student";
					break;
				
				case 2:
					return "Part-time Student";
					break;
			};
		}
		
		/*
			Get major ID
			
			@return string
		*/
		public function get_major_id(){
			return $this->major_id;
		}
		
		/*
			Get major name
			
			@return string
		*/
		public function get_major_name(){
			return $this->major_name;
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