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
	array_walk($_POST, function(&$value, $key) use (&$scores){
		str_clean($value);
		$value = htmlspecialchars($value);
		
		if(substr($key, 0, 6) == "score_"){
			$scores[substr($key, 6)] = (int)$value;
		}
	});
	
	try{
		$group = get_group($_POST['id']);
	}catch(Exception $e){
		header("location: view_all.php?t=groups");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If not assessor
	if(!$group->is_assessor($account->sim_id)){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Get current scores
	$members = json_decode(mysqli_fetch_assoc(db_query("SELECT `members` FROM `groups` WHERE `id` = '{$_POST['id']}';"))['members']);
	
	//Update the scores
	array_walk($members, function(&$value, $key) use (&$scores){
		if(array_key_exists($value->id, $scores)){
			$value->score = $scores[$value->id];
		}
	});
	
	if(db_query(
		"UPDATE 
			`groups`
		SET
			`members` = '". addslashes(json_encode($members)) ."'
		WHERE
			`id` = '{$_POST['id']}';"
	) !== true){
		//Error when adding
		header("location: grade_group.php?g={$_POST['id']}");
	}else{
		//Sucessfully added
		header("location: view_group.php?g={$_POST['id']}");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>