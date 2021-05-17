<?php
	// Initialize the session
	session_start();
	
	//If there is no session
	if(!isset($_SESSION["loggedin"])){
		header("location: index.php");
		exit();
	}
	
	// Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key){
		str_clean($value);
		$value = htmlspecialchars($value);
	});
	
	try{
		$group = get_group($_POST['group_id']);
	}catch(Exception $e){
		header("location: view_all.php?t=groups");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If not assessor
	if(!$group->is_assessor($account->sim_id) && !$group->is_supervisor($account->sim_id)){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If trying to change after approved
	if(
		($group->get_marking_scheme()->approve->supervisor == true && $group->is_supervisor($account->sim_id)) ||
		($group->get_marking_scheme()->approve->assessor == true && $group->is_assessor($account->sim_id))
	){
		header("location: grade_group.php?g={$group->id}&err=1");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Check if the submitted form has been approved
	if(isset($_POST['approve'])){
		//If asupervisor
		if(isset($_POST['approve']['supervisor'])){
			if($_POST['approve']['supervisor'] == 1){
				$group->get_marking_scheme()->approve->supervisor = true;
			}
		}elseif(isset($_POST['approve']['assessor'])){
			//If assessor
			if($_POST['approve']['assessor'] == 1){
				//If supervisor has already approved
				if($group->get_marking_scheme()->approve->supervisor == true){
					$group->get_marking_scheme()->approve->assessor = true;
				}else{
					//Supervisor has not approved their gradings yet
					header("location: grade_group.php?g={$group->id}&err=2");
					@mysqli_close($GLOBALS['mysql_link']);
					exit();
				}
			}
		}
	}
	
	//Remove unused fields
	unset($_POST['grade']);
	unset($_POST['group_id']);
	
	//If contains more than 1 type + feedback + approve
	if(count($_POST) > 3){
		header("location: grade_group.php?g={$group->id}&err=0");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If students have not all submitted their contribution scores, block approval
	if($group->get_contribution_percentage() === false){
		$group->get_marking_scheme()->approve->supervisor = false;
		$group->get_marking_scheme()->approve->assessor = false;
	}
	
	//Parse the grades
	foreach($_POST as $type => $score_array){
		if($type == 'feedback'){
			foreach($score_array as $feedback_type => $feedback_array){
				if($feedback_type == 'supervisor' || $feedback_type == 'assessor'){
					foreach($feedback_array as $feedback_id => $feedback_string){
						//Update supervisor/assessor comments
						$group->get_marking_scheme()->feedback->{$feedback_id}->{$feedback_type} = $feedback_string;
						
						//Update agreed bool here to get all records
						if($feedback_type == 'assessor'){							
							$group->get_marking_scheme()->feedback->{$feedback_id}->agreed = (isset($score_array['agreed'][$feedback_id]) ? (bool)$score_array['agreed'][$feedback_id] : false);
						}
					}
				}
			}
		}else{
			//For each submitted score
			foreach($score_array as $item => $score){
			//If handling faculty
				if($type == 'supervisor' || $type == 'assessor'){
					//Handle penalty
					if($item == 'penalty'){
						(end($group->get_marking_scheme()->faculty))->{$type} = (int)$score;
					}elseif($item == 'student' && $group->is_supervisor($account->sim_id)){
						//Handle individual student scores if supervisor
						foreach($score as $student_id => $student_score){
							$group->get_marking_scheme()->student->individual->{$student_id} = (int)$student_score;
						}
					}else{
						////Handle main/sub items
						
						//If there are sub-items
						if(is_array($score)){
							foreach($score as $sub_item => $sub_score){
								$group->get_marking_scheme()->faculty->{$item}->parts->{$sub_item}->{$type} = (int)$sub_score;
							}
						}else{
							//If no sub-items
							$group->get_marking_scheme()->faculty->{$item}->{$type} = (int)$score;
						}
					}
				}
			}
		}
	}
	
	$member_scores = json_decode($group->get_raw_members());
	
	//If both supervisor and assessor have approved, and students have all submitted their contribution percentages, update the grades
	if($group->get_marking_scheme()->approve->supervisor == true && $group->get_marking_scheme()->approve->assessor == true && $group->get_contribution_percentage() !== false){
		$total_average = 0;
		
		foreach($group->get_marking_scheme()->faculty as $item){
			//If handling penalties
			if($item->desc == "Penalty"){
				$total_average -= $item->supervisor;
				$total_average -= $item->assessor;
			}else{
				//If there are sub-items
				if(isset($item->parts)){
					foreach($item->parts as $sub_item){
						$total_average += $sub_item->supervisor;
						$total_average += $sub_item->assessor;
					}
				}else{
					//There are no subitems
					$total_average += $item->supervisor;
					$total_average += $item->assessor;
				}
			}
		}
		
		$total_average /= 2;
		
		foreach($member_scores as $member){
			//(Total average * Own Contribution Rate / Max Contribution Rate) + Individual Score
			$individual_score = 
				round((
					(int)$total_average * 
					(int)$group->get_contribution_percentage()[$member->id]  / 
					(int)max($group->get_contribution_percentage()) + 
					(int)$group->get_marking_scheme()->student->individual->{$member->id}
				), 2);
			
			$member->score= $individual_score;
		}
	}else{
		//Reset the grades if not all 3 are done
		foreach($member_scores as $member){			
			$member->score= "null";
		}
	}
	
	//If there are changes made to the grading
	if(db_query(
		"UPDATE 
			`groups`
		SET
			`members` = '". addslashes(json_encode($member_scores)) ."', 
			`grading` = '". addslashes(json_encode($group->get_marking_scheme())) ."'
		WHERE
			`id` = '{$group->id}';"
	) !== true){
		//Error when adding
		header("location: grade_group.php?g={$group->id}");
	}else{
		//Sucessfully added
		
		//If students have not all submitted their contribution scores, include error message
		if($group->get_contribution_percentage() === false){
			header("location: grade_group.php?g={$group->id}&err=3");
		}else{
			header("location: grade_group.php?g={$group->id}");
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	/* if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	} */
</script>