<?php
	// Initialize the session
	session_start();
	
	//If there is no session
	if(!isset($_SESSION["loggedin"])){
		echo 0;
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	// Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	
	//If not admin or own account
	if(!$account->is_admin()){
		echo 0;
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$error_flag = false;
	
	db_query("START TRANSACTION;");
	
	foreach($_POST['all_rows'] as $details){
		parse_str($details, $result);
		
		//Prepare the strings for SQL insertion
		array_walk_recursive($result, function(&$value, $key){
			str_clean($value);
			$value = htmlspecialchars($value);
		});
		
		$deadline = ((empty($result['deadline'])) ? "NULL" : "'{$result['deadline']}'");
		
		$query = db_query(
			"UPDATE `choice_deadlines` 
				SET
					`deadline` = {$deadline}
				WHERE
					`year` = '{$result['year']}' AND
					`quarter` = '{$result['quarter']}';"
		);
		
		if($query !== true){
			//Error when updating
			$error_flag = true;
			break;
		}
	}
	
	if(!$error_flag){
		db_query("COMMIT;");
		echo 9;
	}else{
		db_query("ROLLBACK;");
		echo 0;
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>