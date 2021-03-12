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
				`majors`
					(`id`, 
					`name`, 
					`description`)
				VALUES
					('{$_POST['id']}', 
					'{$_POST['name']}', 
					'{$_POST['description']}');"
			) !== true){
			//Error when adding
			header("location: add_major.php?err=1");
		}else{
			//Sucessfully added
			header("location: view_major.php?m={$_POST['id']}");
		}
	}elseif(isset($_POST['edit'])){
		if(db_query(
			"UPDATE `majors` 
				SET
					`id` = '{$_POST['new_id']}', 
					`name` = '{$_POST['name']}', 
					`description` = '{$_POST['description']}'
				WHERE
					`id` = '{$_POST['old_id']}';"
			) !== true){
			//Error when updating
			header("location: edit_major.php?m={$_POST['old_id']}&err=1");
		}else{
			//Sucessfully edited
			header("location: view_major.php?m={$_POST['new_id']}");
		}
	}else{
		//Unknown error
		header("location: view_all.php?t=majors");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>