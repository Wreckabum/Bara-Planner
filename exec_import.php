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
	$import_data = json_decode($_POST['import_data']);
	$errors = [];
	$headers = [];
	$success_count = 0;
	
	str_clean($_POST['type']);
	str_clean($_POST['year']);
	str_clean($_POST['quarter']);
	
	//Check type
	if($_POST['type'] != "student" && $_POST['type'] != "faculty"){
		header("location: import.php?t={$_POST['type']}&y={$_POST['year']}&q={$_POST['quarter']}&err=3");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Get headers
	foreach($import_data->{'1'} as $key => $value){
		//For re-submissions
		if($key == "type" || $key == "year" || $key == "quarter"){
			continue;
		}
		
		$headers[] = $key;
	}
	
	//Process each row
	foreach($import_data as $row){
		//Prepare the strings for SQL insertion
		foreach($row as $key => &$value){
			str_clean($value);
			
			if($key == "Programme"){
				$row->major = $value;
			}elseif($key == "UOW ID"){
				$row->uow_id = $value;
			}elseif($key == "SIM ID"){
				$row->sim_id = $value;
			}elseif($key == "Name"){
				$row->name = $value;
			}elseif($key == "Mobile No."){
				$row->phone = $value;
			}elseif($key == "SIM Email"){
				$row->sim_email = $value;
			}elseif($key == "Personal Email"){
				$row->personal_email = $value;
			}
		}
		
		//Do not proceed with SQL insertion if any of the following fields are missing
		if(
			!isset($row->{'Programme'}) || 
			!isset($row->{'UOW ID'}) || 
			!isset($row->{'SIM ID'}) || 
			!isset($row->{'Name'}) || 
			!isset($row->{'SIM Email'})
		){
			$row->error = "Missing required field.";
			$errors[] = $row;
			continue;
		}
		
		//////Check if major(s) exists
		////
		$major_array = explode(",", $row->major);
		
		//For students
		if($_POST['type'] == "student"){
			if(count($major_array) == 1){
				//If only 1 major attached to row
				foreach($majors as $major){
					if($major->id == $row->major){
						$row->type = $major->get_student_type();
						break;
					}
				}
			}else{
				//Multiple majors
				$row->error = "Student cannot have multiple majors.";
				$errors[] = $row;
				continue;
			}
			
			$sql_year = "'{$_POST['year']}'";
			$sql_quarter = "'{$_POST['quarter']}'";
			$sql_major = "'[\"{$row->major}\"]'";
		}else{
			//For faculty
			$row->type = 0;
			$row->accepted_majors = [];
			
			foreach($majors as $major){
				//If only 1 major attached to row
				if(count($major_array) == 1){
					if($major->id == $row->major){
						$row->accepted_majors[] = $row->major;
						break;
					}
				}else{
					//Multiple majors
					foreach($major_array as $check_major){
						$check_major = trim($check_major);
						
						if($major->id == $check_major){
							$row->accepted_majors[] = $major->id;
						}
					}
				}
			}
			
			
			//There is a major in the list that is not found
			if(count($major_array) != count($row->accepted_majors)){
				$row->error = "Invalid major found.";
				$errors[] = $row;
				continue;
			}
			
			$sql_year = "NULL";
			$sql_quarter = "NULL";
			$sql_major = "'". addslashes(json_encode($row->accepted_majors)) ."'";
		}
		
		
		//Ensure type exist (checking student)
		if(!isset($row->type)){
			$row->error = "Invalid major.";
			$errors[] = $row;
			continue;
		}
		////
		//////
		
		$password = generate_password();
		$phone = ((isset($row->phone))? "'{$row->phone}'" : NULL);
		$personal_email = ((isset($row->personal_email))? "'{$row->personal_email}'" : NULL);
		
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
					('{$row->sim_id}', 
					'{$row->uow_id}', 
					'{$row->name}', 
					'{$row->sim_email}', 
					{$personal_email}, 
					'{$row->type}', 
					{$sql_major}, 
					{$sql_year}, 
					{$sql_quarter}, 
					{$phone},
					'{$password}')"
		) !== true){
			//Error when adding
			$row->error = "Duplicate account.";
			$errors[] = $row;
			continue;
		}else{
			//Sucessfully added
			$success_count++;
		}
	}
	
	$error_info = "";
	
	if(count($errors) > 0){
		//Clean the data sent back
		foreach($errors as $row){
			unset($row->sim_id);
			unset($row->uow_id);
			unset($row->name);
			unset($row->sim_email);
			unset($row->personal_email);
			unset($row->major);
			unset($row->type);
			unset($row->phone);
			unset($row->year);
			unset($row->quarter);
			unset($row->accepted_majors);
		}
		
		$error_info = "&err=". json_encode($errors) ."&h=". json_encode($headers) ."&t={$_POST['type']}&y={$_POST['year']}&q={$_POST['quarter']}";
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