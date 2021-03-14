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
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key){
		str_clean($value);
		$value = htmlspecialchars($value);
	});
	
	$_POST['year'] = (int)$_POST['year'];
	$_POST['quarter'] = (int)$_POST['quarter'];
	
	if(isset($_POST['add'])){
		if(db_query(
			"INSERT INTO
				`projects`
					(`id`,
					`proj_id`, 
					`name`, 
					`description`, 
					`year`, 
					`quarter`)
				VALUES
					(NULL, 
					'{$_POST['proj_id']}', 
					'{$_POST['name']}', 
					'{$_POST['description']}', 
					'{$_POST['year']}', 
					'{$_POST['quarter']}')"
			) !== true){
			//Error when adding
			header("location: add_project.php?err=1");
		}else{
			//Sucessfully added
			header("location: view_project.php?p=". mysqli_insert_id($GLOBALS['mysql_link']) ."");
		}
	}elseif(isset($_POST['edit'])){
		if(db_query(
			"UPDATE `projects` 
				SET
					`proj_id` = '{$_POST['proj_id']}', 
					`name` = '{$_POST['name']}', 
					`description` = '{$_POST['description']}', 
					`year` = '{$_POST['year']}', 
					`quarter` = '{$_POST['quarter']}'
				WHERE
					`id` = '{$_POST['id']}';"
			) !== true){
			//Error when updating
			header("location: edit_project.php?p={$_POST['id']}err=1");
		}else{
			//Sucessfully edited
			header("location: view_project.php?p={$_POST['id']}");
		}
	}else{
		//Unknown error
		header("location: view_all.php?t=projects");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>