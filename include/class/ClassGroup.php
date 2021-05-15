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
		
		private	$raw_members;
		private	$members = [];
		
		private	$marking_scheme;
		
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
				
				try{
					$this->project = get_project($result['project']);
				}catch(Exception $e){
						$this->project = null;
				}
				
				try{
					$this->supervisor = get_account($result['supervisor']);
				}catch(Exception $e){
					$this->supervisor = null;
				}
				
				try{
					$this->assessor = get_account($result['assessor']);
				}catch(Exception $e){
					$this->assessor = null;
				}
				
				$all_members = json_decode($result['members']);
				
				if(count($all_members) > 0){
					foreach($all_members as $member){
						$member->details = get_account($member->id); //Create student object
						
						unset($member->id); //Unset the ID variable
						
						$this->members[] = $member;
					}
					
					$this->raw_members = $result['members'];
				}else{
					db_query(
						"DELETE FROM
							`groups`
						WHERE
							`id` = '{$result['id']}';");
					throw new Exception("Group empty; deleted.");
				}
				
				//If groups grading field is not set
				if(is_null($result['grading']) || $result['grading'] == "null"){
					$designated_marking_scheme = get_marking_scheme($this->get_year(), $this->get_quarter());
					
					if(is_null($designated_marking_scheme)){
						$this->marking_scheme = NULL;
					}else{
						//If base marking scheme exists, prepare the marking scheme for the current group
						$base_marking_scheme = json_decode($designated_marking_scheme);
						
						foreach($base_marking_scheme as $faculty_student_feedback => $details){
							if($faculty_student_feedback == "faculty"){
								//Prepare faculty fields
								foreach($details as $section => $section_details){
									if(isset($section_details->parts)){
										foreach($section_details->parts as $sub_section => $sub_section_details){
											$sub_section_details->supervisor = 0;
											$sub_section_details->assessor = 0;
										}
									}else{
										$section_details->supervisor = 0;
										$section_details->assessor = 0;
									}
								}
							}elseif($faculty_student_feedback == "student"){
								//Prepare faculty/student fields
								$details->contribution = [];
								$details->individual = [];
								
								foreach($this->get_members() as $member){
									$details->contribution[$member->details->sim_id] = [];
									
									foreach($this->get_members() as $member_2){
										$details->contribution[$member->details->sim_id][$member_2->details->sim_id] = 0;
									}
									
									$details->individual[$member->details->sim_id] = 0;
								}
							}elseif($faculty_student_feedback == "feedback"){
								//Prepare feedback fields
								foreach($details as $feedback_id => $feedback){
									$feedback->supervisor = "";
									$feedback->assessor = "";
									$feedback->agreed = false;
								}
							}
						}
						
						db_query(
							"UPDATE
								`groups`
							SET
								`grading` = '". json_encode($base_marking_scheme) ."'
							WHERE
								`id` = {$this->id}");
						
						$this->marking_scheme = json_decode(json_encode($base_marking_scheme));
					}
				}else{
					$this->marking_scheme = json_decode($result['grading']);
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
			Get the raw members field as per the DB
		*/
		public function get_raw_members(){
			return $this->raw_members;
		}
		
		/*
			Get marking scheme for group as object
		*/
		public function get_marking_scheme(){
			return $this->marking_scheme;
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
			return $this->get_members()[0]->details->get_year();
		}
		
		/*
			Get group quarter
		*/
		public function get_quarter(){
			return $this->get_members()[0]->details->get_quarter();
		}
		
		/*
			Get the average contribution rating
		*/
		public function get_contribution_percentage(){
			$number_of_members = count($this->get_members());
			
			$output = [];
			
			//For each member
			foreach($this->get_members() as $member){
				//If this student has NOT submitted their contribution ratings
				if(($number_of_members * 100) != array_sum((array)$this->marking_scheme->student->contribution->{$member->details->sim_id})){
					return false;
				}else{
					//If they have submitted, check each member's rating
					foreach($this->marking_scheme->student->contribution->{$member->details->sim_id} as $member_rated_id => $rating){
						//If the member exists in the output array, add to it
						if(isset($output[$member_rated_id])){
							$output[$member_rated_id] += (int)$rating;
						}else{
							//Else, create first instance
							$output[$member_rated_id] = $rating;
						}
					}
				}
			}
			
			foreach($output as $member_id => $total_rating){
				$output[$member_id] /= $number_of_members;
			}
			
			return $output;
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
			Check if project (checks both ID nad Proj_id)
		*/
		public function is_project($proj_id){
			return ($this->project->id == $proj_id || $this->project->proj_id == $proj_id);
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