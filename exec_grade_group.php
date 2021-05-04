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
	$scores = [];
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key) use (&$scores){
		$value = (int)$value;
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
	
	//If contains mroe than 1 type
	if(count($_POST) > 1){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Parse the grades
	foreach($_POST as $submitter_type => $score_array){
		//For each submitted score
		foreach($score_array as $item => $score){
		//If handling faculty
			if($submitter_type == 'supervisor' || $submitter_type == 'assessor'){
				//Handle penalty
				if($item == 'penalty'){
					(end($group->get_marking_scheme()->faculty))->{$submitter_type} = $score;
				}elseif($item == 'student' && $group->is_supervisor($account->sim_id)){
					//Handle individual student scores if supervisor
					foreach($score as $student_id => $student_score){
						$group->get_marking_scheme()->student->individual->{$student_id} = $student_score;
					}
				}else{
					////Handle main/sub items
					
					//If there are sub-items
					if(is_array($score)){
						foreach($score as $sub_item => $sub_score){
							$group->get_marking_scheme()->faculty->{$item}->parts->{$sub_item}->{$submitter_type} = $sub_score;
						}
					}else{
						//If no sub-items
						$group->get_marking_scheme()->faculty->{$item}->{$submitter_type} = $score;
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