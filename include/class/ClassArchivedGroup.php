<?php
	require_once (dirname(__FILE__)."/../funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Class for groups
	*/
	class ArchivedGroup{
		private	$name;
		private	$supervisor;
		private	$assessor;
		private	$proj_id;
		private $year;
		private $quarter;
		
		private	$members = [];
		
		/*
			Constructor
		*/
		public function __construct($name, $year, $quarter){
			$name = str_clean($name);
			
			$query = db_query("SELECT * FROM `archive_groups` WHERE `name` = '{$name}', `year` = '{$year}', `quarter` = '{$quarter}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the poject exists
			if(mysqli_num_rows($query) == 1){
				$this->name = $result['name'];
				$this->supervisor = $result['supervisor'];
				$this->assessor = $result['assessor'];
				$this->proj_id = get_project($result['proj_id']);
				$this->year = $result['year'];
				$this->quarter = $result['quarter'];
				
				$all_members = json_decode($result['members']);
				
				if(count($all_members) > 0 ){
					foreach($all_members as $member){
						$member->details = new ArchivedStudent($member->id, $this->year, $this->quarter);
						
						unset($member->id); //Unset the ID variable
						
						$this->members[] = $member;
					}
				}
			}else{
				throw new Exception("Archived group not found.");
			}	
		}
		
		/*
			Get name
		*/
		public function get_name(){
			return $this->name;
		}
		
		/*
			Get project ID
		*/
		public function get_project(){
			return new ArchivedProject($this->project_id, $this->year, $this->quarter);
		}
		
		/*
			Get supervisor name
		*/
		public function get_supervisor(){
			return $this->supervisor;
		}
		
		/*
			Get assessor name
		*/
		public function get_assessor(){
			return $this->assessor;
		}
		
		/*
			Get members [score: "", details: {}]
		*/
		public function get_members(){
			return $this->members;
		}		
		
		/*
			Get group type
		*/
		public function get_type(){
			return $this->get_members()[0]->details->get_type_int();
		}
		
		/*
			Get group year
		*/
		public function get_year(){
			return $this->year;
		}
		
		/*
			Get group quarter
		*/
		public function get_quarter(){
			return $this->quarter;
		}
		
		/*
			Check if member
		*/
		public function is_member($member_id){
			foreach($this->members as $member){
				if($member->details->sim_id == $member_id){
					return true;
				}
			}
			
			return false;
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>