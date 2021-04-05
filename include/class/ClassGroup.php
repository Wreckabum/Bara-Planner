<?php
	require_once (dirname(__FILE__)."/../funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Class for groups
	*/
	class Group{
		public	$id;
		
		private	$name;
		
		private	$project;
		private	$supervisor;
		private	$assessor;
		
		private	$members = [];
		
		/*
			Constructor
		*/
		public function __construct($id){
			$id = str_clean($id);
			
			$query = db_query("SELECT * FROM `groups` WHERE `id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the poject exists
			if(mysqli_num_rows($query) == 1){
				$this->id = $result['id'];
				
				$this->name = $result['name'];
				
				$this->project = get_project($result['project'], "proj_id");
				$this->supervisor = get_account($result['supervisor']);
				$this->assessor = get_account($result['assessor']);
				foreach(json_decode($result['members']) as $member){
					$member->details = get_account($member->id); //Create student object
					unset($member->id); //Unset the ID variable
					$this->members[] = $member;
				}
			}else{
				throw new Exception("Group not found.");
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
			return $this->project;
		}
		
		/*
			Get supervisor object
		*/
		public function get_supervisor(){
			return $this->supervisor;
		}
		
		/*
			Get assessor object
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
			Check if supervisor
		*/
		public function is_supervisor($supervisor_id){
			return ($this->supervisor->sim_id == $supervisor_id);
		}
		
		/*
			Check if assessor
		*/
		public function is_assessor($supervisor_id){
			return ($this->assessor->sim_id == $supervisor_id);
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