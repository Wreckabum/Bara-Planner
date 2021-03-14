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
	
	try{
		get_account(str_clean($_POST['supervisor']));
	}catch(Exception $e){
		header("location: add_group.php?semester={$_POST['semester']}&err=1");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	try{
		get_account(str_clean($_POST['assessor']));
	}catch(Exception $e){
		header("location: add_group.php?semester={$_POST['semester']}&err=2");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$project = get_project($_POST['project'], 'proj_id');
	
	if(is_null($project)){
		header("location: add_group.php?semester={$_POST['semester']}&err=3");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	foreach($_GET['student'] as $student){
		try{
			get_account(str_clean($student));
		}catch(Exception $e){
			header("location: add_group.php?semester={$_POST['semester']}&err=4");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
	}
	
	if(isset($_POST['add'])){
		if(db_query(
			"INSERT INTO
				`groups`
					(`id`, 
					`name`, 
					`supervisor`, 
					`assessor`, 
					`members`, 
					`project`)
				VALUES
					(NULL, 
					'{$_POST['name']}', 
					'{$_POST['supervisor']}', 
					'{$_POST['assessor']}', 
					'". addslashes(json_encode($_GET['student'])) ."', 
					'{$_POST['project']}');"
			) !== true){
			//Error when adding
			header("location: add_group.php?semester={$_POST['semester']}&err=0");
		}else{
			//Sucessfully added
			header("location: view_group.php?g=". mysqli_insert_id($GLOBALS['mysql_link']));
		}
	}elseif(isset($_POST['edit'])){
		if(db_query(
			"UPDATE `groups` 
				SET
					`name` = '{$_POST['name']}', 
					`supervisor` = '{$_POST['supervisor']}', 
					`assessor` = '{$_POST['assessor']}', 
					`members` = '{$_POST['members']}', 
					`project` = '{$_POST['project']}'
				WHERE
					`id` = '{$_POST['id']}';"
			) !== true){
			//Error when updating
			header("location: view_group.php?g={$_POST['id']}&err=1");
		}else{
			//Sucessfully edited
			header("location: view_group.php?g={$_POST['id']}");
		}
	}else{
		//Unknown error
		header("location: view_all.php?t=groups");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>