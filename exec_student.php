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
	
	$_POST['type'] = (int)$_POST['type'];
	$_POST['year'] = (int)$_POST['year'];
	$_POST['quarter'] = (int)$_POST['quarter'];
	
	if(isset($_POST['add'])){
		if(db_query(
			"INSERT INTO
				`accounts`
					(`id`, 
					`name`, 
					`email`, 
					`type`, 
					`majors`, 
					`year`, 
					`quarter`,
					`phone`)
				VALUES
					('{$_POST['id']}', 
					'{$_POST['name']}', 
					'{$_POST['email']}', 
					'{$_POST['type']}', 
					'[\"{$_POST['major']}\"]', 
					'{$_POST['year']}', 
					'{$_POST['quarter']}', 
					'{$_POST['phone']}')"
			) !== true){
			//Error when adding
			header("location: add_student.php?err=1");
		}else{
			//Sucessfully added
			header("location: view_account.php?a=". mysqli_insert_id($GLOBALS['mysql_link']) ."");
		}
	}if(isset($_POST['edit'])){
		if(db_query(
			"UPDATE `accounts` 
				SET
					`name` = '{$_POST['name']}', 
					`email` = '{$_POST['email']}', 
					`type` = '{$_POST['type']}', 
					`majors` = '[\"{$_POST['major']}\"]', 
					`year` = '{$_POST['year']}', 
					`quarter` = '{$_POST['quarter']}',
					`phone` = '{$_POST['phone']}'
				WHERE
					`id` = '{$_POST['id']}';"
			) !== true){
			//Error when updating
			header("location: add_student.php?a={$_POST['id']}err=1");
		}else{
			//Sucessfully edited
			header("location: view_account.php?a={$_POST['id']}");
		}
	}else{
		//Unknown error
		header("location: view_all.php?t=students");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>