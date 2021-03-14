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
	
	//If not student
	if(!$account->is_student()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key){
		str_clean($value);
		$value = htmlspecialchars($value);
	});
	
	$choices = addslashes(json_encode([$_POST['choice_1'], $_POST['choice_2'], $_POST['choice_3']]));
	
	if(db_query(
		"UPDATE `accounts` 
			SET
				`choices` = '{$choices}'
			WHERE
				`sim_id` = '{$account->sim_id}';"
		) !== true){
		//Error when updating
		header("location: make_choices.php?err=0");
	}else{
		//Sucessfully updated
		header("location: view_choices.php");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>