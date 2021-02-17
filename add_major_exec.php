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
	
	$pt = 0;
	$ft = 0;
	
	if(isset($_POST['available_for'])){
		foreach($_POST['available_for'] as $available_for){
			if($available_for == "full"){
				$ft = 1;
			}elseif($available_for == "part"){
				$pt = 1;
			}
		}
	}
	
	if(db_query(
		"INSERT INTO
			`majors`
				(`id`, 
				`name`, 
				`description`, 
				`part_time`, 
				`full_time`)
			VALUES
				('{$_POST['id']}', 
				'{$_POST['name']}', 
				'{$_POST['description']}', 
				'{$pt}', 
				'{$ft}')"
	) !== true){
		header("location: add_major.php?err=1");
	}else{
		header("location: view_major.php?m={$_POST['id']}");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>