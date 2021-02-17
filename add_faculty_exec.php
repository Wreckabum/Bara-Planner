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
	
	$_POST['experience'] = (($_POST['experience'] == "" || empty($_POST['experience'])) ? 0 : (int)$_POST['experience']);
	
	if(db_query(
		"INSERT INTO
			`accounts`
				(`id`, 
				`first_name`, 
				`last_name`, 
				`email`, 
				`type`, 
				`majors`, 
				`position`, 
				`experience`, 
				`phone`)
			VALUES
				(NULL, 
				'{$_POST['first_name']}', 
				'{$_POST['last_name']}', 
				'{$_POST['email']}', 
				'0', 
				'". addslashes(json_encode($_POST['majors'])) ."', 
				'{$_POST['position']}', 
				'{$_POST['experience']}', 
				'{$_POST['phone']}')"
	) !== true){
		header("location: add_faculty.php?err=1");
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