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
				
				if(count($all_members) > 0 ){
					foreach($all_members as $member){
						$member->details = get_account($member->id); //Create student object
						
						unset($member->id); //Unset the ID variable
						
						$this->members[] = $member;
					}
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
						
						foreach($base_marking_scheme as $faculty_student => $details){
							if($faculty_student == "faculty"){
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
							}elseif($faculty_student == "student"){
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
							}
						}
						
						$this->marking_scheme = json_decode(json_encode($base_marking_scheme));
						
						db_query(
							"UPDATE
								`groups`
							SET
								`grading` = '". json_encode($this->marking_scheme) ."'
							WHERE
								`id` = {$this->id}");
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
		
		/*
			Prints the marking scheme for the group based on user type
			
			@param	string (id)
		*/
		public function print_marking_scheme($id){
			str_clean($id);
			
			if($this->is_supervisor($id) || $this->is_assessor($id)){
				$style =
					"<style>
							#grading_table {
								display: inline-table;
							}
							
							#grading_table td {
								text-align: center;
								white-space: nowrap;
							}
							
							#grading_table td.empty {
								background-color: #E4E4E4;
							}
							
							#grading_table .item_desc {
								width: 450px;
							}
							
							#grading_table .supervisor, 
							#grading_table .assessor, 
							#grading_table .total, 
							#grading_table .average {
								width: 112px;
							}
							
							#grading_table .supervisor {
								background-color: #E6ffE6;
							}
							
							#grading_table .assessor {
								background-color: #CFCFFF;
							}
							
							#grading_table .total {
								background-color: #C8E0F1;
							}
							
							#grading_table .average {
								background-color: #E8E15F;
							}
							
							#grading_table .final {
								background-color: #FB9929;
								font-weight:bold;
							}
							
							#grading_table .penalty {
								background-color: #FFCECE;
							}
							
							#grading_table .table_header {
								background-color:#B0D8EA;
								font-weight: bold;
							}
							
							#grading_table input {
								width: 97%;
								text-align: center;
							}
							
							#grading_table input::-webkit-outer-spin-button,
							#grading_table input::-webkit-inner-spin-button {
								-webkit-appearance: none;
								margin: 0;
							}
							
							#grading_table input[type=number] {
								-moz-appearance: textfield;
							}
						</style>";
				
				$group_details =
					"<table id='grade_group_table' class='basic_table' style='display:table; width:40%; margin:0 auto;'>
						<tr>
							<td colspan='3'>
								Group Details
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Group ID:
							</td>
							<td colspan='2'>
								#{$this->id}
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Name:
							</td>
							<td colspan='2'>
								{$this->get_name()}
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Type:
							</td>
							<td colspan='2'>
								". (($this->get_type() == 1) ? "Full-Time" : "Part-Time") ."
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Supervisor:
							</td>
							<td colspan='2'>
								". (is_null($this->get_supervisor()) ? "" : "<a href='view_account.php?a={$this->get_supervisor()->sim_id}'>{$this->get_supervisor()->get_name()} ({$this->get_supervisor()->sim_id})</a>") ."
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Assessor:
							</td>
							<td colspan='2'>
								". (is_null($this->get_assessor()) ? "" : "<a href='view_account.php?a={$this->get_assessor()->sim_id}'>{$this->get_assessor()->get_name()} ({$this->get_assessor()->sim_id})</a>") ."
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Project:
							</td>
							<td colspan='2'>
								<a href='view_project.php?p={$this->get_project()->id}'>
									{$this->get_project()->id} - {$this->get_project()->get_name()}
								</a>
							</td>
						</tr>
						<tr>
							<td rowspan='". count($this->get_members()) ."'style='width:25%;'>
								Members:
							</td>";
				
				$first = true;
				
				foreach($this->get_members() as $member){
					if($first){
						$group_details .=
							"<td>
								<a href='view_account.php?a={$member->details->sim_id}'>
									{$member->details->get_name()}
								</a>
							</td>
							<td>
								{$member->details->sim_id}
							</td>
						</tr>";
						
						$first = false;
					}else{
						$group_details .=
						"<tr>
							<td>
								<a href='view_account.php?a={$member->details->sim_id}'>
									{$member->details->get_name()}
								</a>
							</td>
							<td>
								{$member->details->sim_id}
							</td>
						</tr>";
					}
				}
				
				$group_details .=
					"</table>";
				
				$marking_section = 
					"<table id='grading_table' class='basic_table' style='display:table; width:auto; margin:0 auto;'>
						<tr>
							<td colspan='2'>
								Item
							</td>
							<td>
								Assignment Items & Format
							</td>
							<td>
								Week Due
							</td>
							<td>
								Max Marks (%)
							</td>
							<td>
								Supervisor
							</td>
							<td>
								Assessor
							</td>
							<td>
								Total
							</td>
							<td>
								Average
							</td>
						</tr>";
				
				$total_average = 0;
				
				foreach($this->marking_scheme->faculty as $section => $section_details){
					$marking_section .=
						"<tr>
							<td colspan='2'>
								{$section}
							</td>
							<td>
								{$section_details->desc}
							</td>";
					
					if($section_details->desc == "Penalty"){
						$supervisor_penalty = (($this->is_supervisor($id)) ? "<input type='number' name='supervisor[penalty]' min='0' max='100' value='{$section_details->supervisor}' />" : $section_details->supervisor);
						$assessor_penalty = (($this->is_assessor($id)) ? "<input type='number' name='assessor[penalty]' min='0' max='100' value='{$section_details->assessor}' />" : $section_details->assessor);
						$total_average -= (($section_details->supervisor + $section_details->assessor) / 2);
						
						$marking_section .=
							"<td class='due penalty'>-</td>
							<td class='weight penalty'>-%</td>
							<td class='supervisor penalty'>{$supervisor_penalty}</td>
							<td class='assessor penalty'>{$assessor_penalty}</td>
							<td class='total penalty'>". ($section_details->supervisor + $section_details->assessor) ."</td>
							<td class='average penalty'>". (($section_details->supervisor + $section_details->assessor) / 2) ."</td>
						</tr>";
					}else{
						$marking_section .=
							"<td class='due' ". ((isset($section_details->parts)) ? "rowspan='". (count((array)$section_details->parts) + 1) ."'" : "") .">
								{$section_details->week_due}
							</td>";
							
						if(isset($section_details->parts)){
							$marking_section .=
								"<td colspan='5' class='empty'>-</td>
							</tr>";
						
								foreach($section_details->parts as $part => $part_details){
									$supervisor_input = (($this->is_supervisor($id)) ? "<input type='number' name='supervisor[{$section}][{$part}]' min='0' max='{$part_details->weight}' value='{$part_details->supervisor}' />" : $part_details->supervisor);
									$assessor_input = (($this->is_assessor($id)) ? "<input type='number' name='assessor[{$section}][{$part}]' min='0' max='{$part_details->weight}' value='{$part_details->assessor}' />" : $part_details->assessor);
									$total_average += (($part_details->supervisor + $part_details->assessor) / 2);
									
									$marking_section .=
										"<tr>
											<td class='empty'>-</td>
											<td>
												{$part}
											</td>
											<td>
												{$part_details->desc}
											</td>
											<td class='weight'>
												{$part_details->weight}%
											</td>
											<td class='supervisor'>{$supervisor_input}</td>
											<td class='assessor'>{$assessor_input}</td>
											<td class='total'>". ($part_details->supervisor + $part_details->assessor) ."</td>
											<td class='average'>". (($part_details->supervisor + $part_details->assessor) / 2) ."</td>
										</tr>";
								}
						}else{
							$supervisor_input = (($this->is_supervisor($id)) ? "<input type='number' name='supervisor[{$section}]' min='0' max='{$section_details->weight}' value='{$section_details->supervisor}'  />" : $section_details->supervisor);
							$assessor_input = (($this->is_assessor($id)) ? "<input type='number' name='assessor[{$section}]' min='0' max='{$section_details->weight}' value='{$section_details->assessor}' />" : $section_details->assessor);
							$total_average += (($section_details->supervisor + $section_details->assessor) / 2);
							
							$marking_section .=
								"<td class='weight'>
									{$section_details->weight}%
								</td>
								<td class='supervisor'>{$supervisor_input}</td>
								<td class='assessor'>{$assessor_input}</td>
								<td class='total'>". ($section_details->supervisor + $section_details->assessor) ."</td>
								<td class='average'>". (($section_details->supervisor + $section_details->assessor) / 2) ."</td>
							</tr>";
						}
					}
				}
				
				$marking_section .=
						"<tr>
							<td colspan='8' class='final'>
								Total
							</td>
							<td colspan='8' class='final'>
								{$total_average} / ". (100 - $this->marking_scheme->student->weight) ."
							</td>
						</tr>
						<tr>
							<td colspan='6' style='background-color:#F5E6FF; padding:10px 5px; font-weight:bold;'>
								Individual Students
							</td>
							<td colspan='3' class='empty'>-</td>
						</tr>
						<tr>
							<td colspan='2' class='table_header'>
								#
							</td>
							<td class='table_header'>
								Name / SIM ID
							</td>
							<td class='table_header'>
								Contribution
							</td>
							<td class='table_header'>
								Individual (%)
							</td>
							<td class='table_header'>
								Supervisor
							</td>
							<td colspan='4' class='empty'>-</td>
						</tr>";
				
				$count = 1;
				
				foreach($this->get_members() as $member){
					$supervisor_input = (($this->is_supervisor($id)) ? "<input type='number' name='supervisor[student][{$member->details->sim_id}]' min='0' max='{$this->marking_scheme->student->weight}' value='{$this->marking_scheme->student->individual->{$member->details->sim_id}}' />" : $this->marking_scheme->student->individual->{$member->details->sim_id});
					
					$marking_section .=
						"<tr>
							<td colspan='2'>
								{$count}
							</td>
							<td>
								{$member->details->get_name()} ({$member->details->sim_id})
							</td>
							<td class='empty'>-</td>
							<td>
								{$this->marking_scheme->student->weight}%
							</td>
							<td class='supervisor'>
								{$supervisor_input}
							</td>
							<td colspan='3' class='empty'>-</td>
						</tr>";
					
					++$count;
				}
				
				$marking_section .=
						"<tr>
							<td colspan='9' style='padding:10px 5px;'>
								<input type='submit' name='grade' value='Submit Grading'>
							</td>
						</tr>
					</table>";
				
				$script = 
					"<script>
						$('#grade_group').submit(function(e){
							 return confirm('Confirm grades?\\nUpdates can be made at a later time.');
						});
					</script>";
				
				$output =
					"<div id='grading_container'>
						{$style}
						{$group_details}
						<br />
						<form id='grade_group' action='exec_grade_group.php' method='POST'>
							{$marking_section}
							<input type='hidden' name='group_id' value='{$this->id}'>
						</form>
						<br />
						{$script}
						<br />
					</div>";
			}elseif($this->is_member($id)){
				//TODO
			}else{
				return false;
			}
			
			return $output;
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>