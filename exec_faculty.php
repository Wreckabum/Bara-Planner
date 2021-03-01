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
	
	//If not admin
	if(!$account->is_admin()){
		header("location: home.php");
		exit();
	}
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key){
		str_clean($value);
		$value = htmlspecialchars($value);
	});
	
	if(isset($_POST['add'])){
		if(db_query(
			"INSERT INTO
				`accounts`
					(`id`, 
					`name`, 
					`sim_email`, 
					`personal_email`, 
					`type`, 
					`majors`, 
					`phone`)
				VALUES
					('{$_POST['id']}', 
					'{$_POST['name']}', 
					'{$_POST['sim_email']}', 
					'{$_POST['personal_email']}', 
					'0', 
					'". addslashes(json_encode($_POST['majors'])) ."', 
					'{$_POST['phone']}')"
			) !== true){
			//Error when adding
			header("location: add_faculty.php?err=1");
		}else{
			//Sucessfully added
			header("location: view_account.php?a={$_POST['id']}");
		}
	}elseif(isset($_POST['edit'])){
		if(db_query(
			"UPDATE `accounts` 
				SET
					`id` = '{$_POST['new_id']}',
					`name` = '{$_POST['name']}', 
					`sim_email` = '{$_POST['sim_email']}', 
					`personal_email` = '{$_POST['personal_email']}', 
					`majors` = '". addslashes(json_encode($_POST['majors'])) ."', 
					`phone` = '{$_POST['phone']}'
				WHERE
					`id` = '{$_POST['old_id']}';"
			) !== true){
			//Error when updating
			header("location: edit_faculty.php?a={$_POST['old_id']}err=1");
		}else{
			//Sucessfully edited
			header("location: view_account.php?a={$_POST['new_id']}");
		}
	}else{
		//Unknown error
		header("location: view_all.php?t=faculty");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>