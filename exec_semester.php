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
		if(isset($_POST['details'])){
			header("location: view_semester.php?y={$_POST['year']}q={$_POST['quarter']}");
		}else{
			echo 0;
		}
		
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	if(isset($_POST['add'])){
		//Prepare the strings for SQL insertion
		array_walk_recursive($_POST, function(&$value, $key){
			str_clean($value);
			$value = htmlspecialchars($value);
		});
		
		if(db_query(
			"INSERT INTO
				`semester_details`
					(`year`,
					`quarter`, 
					`deadline`, 
					`details`)
				VALUES
					('{$_POST['year']}', 
					'{$_POST['quarter']}', 
					'{$_POST['deadline']}', 
					'". htmlspecialchars($_POST['details'], ENT_QUOTES) ."');"
		) !== true){
			//Error when adding
			header("location: view_semester.php?y={$_POST['year']}&q={$_POST['quarter']}&err=4");
		}else{
			//Sucessfully adding
			header("location: view_semester.php?y={$_POST['year']}&q={$_POST['quarter']}");
		}
	}elseif(isset($_POST['update'])){ //If updating a single semester
		//Prepare the strings for SQL insertion
		array_walk_recursive($_POST, function(&$value, $key){
			str_clean($value);
			$value = htmlspecialchars($value);
		});
		
		$deadline = ((empty($_POST['deadline'])) ? "NULL" : "'{$_POST['deadline']}'");
		
		if(db_query(
			"UPDATE `semester_details` 
				SET
					`deadline` = {$deadline}, 
					`details` = '". htmlspecialchars($_POST['details'], ENT_QUOTES) ."'
				WHERE
					`year` = '{$_POST['year']}' AND
					`quarter` = '{$_POST['quarter']}';"
		) !== true){
			//Error when updating
			header("location: view_semester.php?y={$_POST['year']}&q={$_POST['quarter']}&err=0");
		}else{
			//Sucessfully updated
			header("location: view_semester.php?y={$_POST['year']}&q={$_POST['quarter']}");
		}
	}elseif(isset($_POST['update_all'])){
		//Updating all deadlines
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
				"UPDATE `semester_details` 
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
			echo 10;
		}else{
			db_query("ROLLBACK;");
			echo 0;
		}
	}else{
		//Unknown source
		header("location: semester_details.php?err=0");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>