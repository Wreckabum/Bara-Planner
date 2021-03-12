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
	
	try{
		$to_delete = get_account($_POST['delete_id']);
	}catch(Exception $e){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Confirm permissions to delete
	if(
		(!$account->is_admin()) || //Not admin
		($account->is_super() && $to_delete->is_admin())  //Super admin deleting admin accounts
	){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	db_query(
		"DELETE FROM
			`accounts`
		WHERE
			`sim_id` = '{$to_delete->sim_id}';");
	
	header("location: view_all.php");
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>