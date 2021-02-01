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
	array_walk($_POST, function(&$value, $key){
		str_clean($value);
	});
	
	$_POST['type'] = (int)$_POST['type'];
	$_POST['year'] = (int)$_POST['year'];
	$_POST['quarter'] = (int)$_POST['quarter'];
	
	if(db_query(
		"INSERT INTO
			`accounts`
				(`id`, 
				`first_name`, 
				`last_name`, 
				`email`, 
				`type`, 
				`majors`, 
				`year`, 
				`quarter`,
				`phone`)
			VALUES
				(NULL, 
				'{$_POST['first_name']}', 
				'{$_POST['last_name']}', 
				'{$_POST['email']}', 
				'{$_POST['type']}', 
				'[\"{$_POST['major']}\"]', 
				'{$_POST['year']}', 
				'{$_POST['quarter']}', 
				'{$_POST['phone']}')"
	) !== true){
		header("location: add_student.php?err=1");
	}else{
		header("location: view_account.php?a=". mysqli_insert_id($GLOBALS['mysql_link']) ."");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>