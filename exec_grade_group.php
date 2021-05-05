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
	
	//Remove unused fields
	unset($_POST['grade']);
	unset($_POST['group_id']);
	
	//If contains more than 1 type + feedback
	if(count($_POST) > 2){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
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
	
	if(db_query(
		"UPDATE 
			`groups`
		SET
			`grading` = '". addslashes(json_encode($group->get_marking_scheme())) ."'
		WHERE
			`id` = '{$group->id}';"
	) !== true){
		//Error when adding
		header("location: grade_group.php?g={$group->id}");
	}else{
		//Sucessfully added
		header("location: grade_group.php?g={$group->id}");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>