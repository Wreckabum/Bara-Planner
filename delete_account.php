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
	
	//If student
	if($to_delete->is_student()){
		//Check if in a group
		$query = db_query("SELECT `id`, `members` FROM `groups` WHERE JSON_CONTAINS(`members`, '{\"id\" : \"{$to_delete->sim_id}\"}')");
		
		if(mysqli_num_rows($query) == 1){
			$group = mysqli_fetch_assoc($query);
			$members = json_decode($group['members']);
			
			foreach($members as $index => $member){
				if($member->id == $to_delete->sim_id){
					unset($members[$index]);
					break;
				}
			}
			
			//Delete group if no more members
			if(count($members) > 0){
				db_query("UPDATE `groups` SET `members` = '". addslashes(json_encode($members)) ."' WHERE `id` = '{$group['id']}'");
			}else{
				db_query("DELETE FROM `groups` WHERE `id` = '{$group['id']}'");
			}
		}
	}
	
	header("location: view_all.php?t=students");
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>