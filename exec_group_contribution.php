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
		$value = (int)$value;
	});
	
	try{
		$group = get_group($_POST['group_id']);
	}catch(Exception $e){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If not a member
	if(!$group->is_member($account->sim_id)){
		header("location: rate_contribution.php?err=0");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If contains more than 1 type + feedback
	if((count($group->get_members()) * 100) != array_sum($_POST['member'])){
		header("location: rate_contribution.php?err=1");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Save original JSON for comparison (do not check the "approve")
	$original_json = unserialize(serialize($group->get_marking_scheme()->student->contribution->{$account->sim_id}));
	
	//Parse the contribution rates
	foreach($_POST['member'] as $member_id => $rate){
		if(property_exists($group->get_marking_scheme()->student->contribution->{$account->sim_id}, $member_id)){
			$group->get_marking_scheme()->student->contribution->{$account->sim_id}->{$member_id} = (int)$rate;
		}else{
			//Unknown member
			header("location: rate_contribution.php?err=2");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
	}
	
	//Comparison JSON (do not check the "approve")
	$compare_json = unserialize(serialize($group->get_marking_scheme()->student->contribution->{$account->sim_id}));
	
	//Get original member scores
	$member_scores = json_decode($group->get_raw_members());
	
	//If the contribution percentages have been changed
	if($original_json != $compare_json){
		//Reset the grades if not all 3 are done
		foreach($member_scores as $member){			
			$member->score= "null";
		}
		
		//Reset approved status
		$group->get_marking_scheme()->approve->supervisor = false;
		$group->get_marking_scheme()->approve->assessor = false;
	}
	
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
		header("location: rate_contribution.php?err=0");
	}else{
		//Sucessfully added
		header("location: rate_contribution.php?err=9");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>