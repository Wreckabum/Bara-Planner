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
	
	$majors = get_all_majors();
	$students = json_decode($_POST['students']);
	$errors = [];
	$headers = [];
	$success_count = 0;
	
	str_clean($_POST['year']);
	str_clean($_POST['quarter']);
	
	//Get headers
	foreach($students->{'1'} as $key => $value){
		$headers[] = $key;
	}
	
	//Process each row
	foreach($students as $student){
		//Prepare the strings for SQL insertion
		foreach($student as $key => &$value){
			str_clean($value);
			
			if($key == "Programme"){
				$student->major = $value;
			}elseif($key == "UOW ID"){
				$student->uow_id = $value;
			}elseif($key == "SIM ID"){
				$student->sim_id = $value;
			}elseif($key == "Name"){
				$student->name = $value;
			}elseif($key == "Mobile No."){
				$student->phone = $value;
			}elseif($key == "SIM Email"){
				$student->sim_email = $value;
			}elseif($key == "Personal Email"){
				$student->personal_email = $value;
			}
		}
		
		//Check if major exists
		foreach($majors as $major){
			if($major->id == $student->major){
				$student->type = $major->get_type();
				break;
			}
		}
		
		//Do not proceed with SQL insertion if prior error found
		if(!isset($student->type)){
			$errors[] = $student;
			continue;
		}
		
		$password = generate_password();
		
		if(db_query(
			"INSERT INTO
				`accounts`
					(`sim_id`, 
					`uow_id`, 
					`name`, 
					`sim_email`, 
					`personal_email`, 
					`type`, 
					`majors`, 
					`year`, 
					`quarter`,
					`phone`,
					`password`
					)
				VALUES
					('{$student->sim_id}', 
					'{$student->uow_id}', 
					'{$student->name}', 
					'{$student->sim_email}', 
					'{$student->personal_email}', 
					'{$student->type}', 
					'[\"{$student->major}\"]', 
					'{$_POST['year']}', 
					'{$_POST['quarter']}', 
					'{$student->phone}',
					'{$password}')"
		) !== true){
			//Error when adding
			$errors[] = $student;
			continue;
		}else{
			//Sucessfully added
			$success_count++;
		}
	}
	
	$error_info = "";
	
	if(count($errors) > 0){
		$error_info = "&err=". json_encode($errors) ."&h=". json_encode($headers);
	}
	
	header("location: import_result.php?c={$success_count}{$error_info}");
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>