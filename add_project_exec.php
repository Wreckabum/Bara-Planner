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
	});
	
	$_POST['active'] = (int)(bool)$_POST['active'];
	
	if(db_query(
		"INSERT INTO
			`projects`
				(`id`,
				`proj_id`, 
				`name`, 
				`description`, 
				`available_for`, 
				`active`)
			VALUES
				(NULL, 
				'{$_POST['proj_id']}', 
				'{$_POST['name']}', 
				'{$_POST['description']}', 
				'". addslashes(json_encode($_POST['majors'])) ."', 
				'{$_POST['active']}')"
	) !== true){
		header("location: add_project.php?err=1");
	}else{
		header("location: view_project.php?p=". mysqli_insert_id($GLOBALS['mysql_link']) ."");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>