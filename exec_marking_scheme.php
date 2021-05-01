<?php
	// Initialize the session
	session_start();
	
	//If there is no session
	if(!isset($_SESSION["loggedin"])){
		header("location: index.php");
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
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key){
		str_clean($value);
		$value = htmlspecialchars($value);
	});
	
	//If using default
	if(isset($_POST['submit'])){
		if($_POST['submit'] == "Use Default"){
			if(db_query(
				"UPDATE `semester_details` 
				SET
					`marking_scheme` = '". get_default_marking_scheme('json') ."'
				WHERE
					`year` = '{$_POST['year']}' AND
					`quarter` = '{$_POST['quarter']}';"
			) !== true){
				//Error when updating
				header("location: update_marking_scheme.php?y={$_POST['year']}&q={$_POST['quarter']}&err=0");
			}else{
				//Sucessfully edited
				
				//Reset all groups for the semester
				$semester_groups = get_all_groups($_POST['year'], $_POST['quarter']);
				
				$all_ids = [];
				
				foreach($semester_groups as $this_group){
					$all_ids[] = $this_group->id;
				}
				
				db_query(
						"UPDATE
							`groups`
						SET
							`grading` = 'null'
						WHERE
							`id` IN ('". implode("', '", $all_ids) ."');");
				
				header("location: update_marking_scheme.php?y={$_POST['year']}&q={$_POST['quarter']}");
			}
			
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
	}
	
	//Support multi-line descriptions
	array_walk_recursive($_POST['desc'], function(&$value, $key){
		$value = str_replace("\r\n", "<br>", $value);
	});
	
	$final = [
		"faculty" => [],
		"student" => [
			"weight" => (int)$_POST['student']
		]
	];
	
	$total_weight = (int)$_POST['student'];
	
	//Create the final array for JSON parsing
	foreach($_POST['desc'] as $item_num => $types){
		//Handle sub-parts
		if(isset($types['sub'])){
			//Handle sub data (desc)
			foreach($types['sub'] as $sub_key => $desc){
				$final['faculty'][$item_num]['parts'][$sub_key]['desc'] = $desc;
			}
			
			//Handle sub data (weight)
			foreach($_POST['weight'][$item_num]['sub'] as $sub_key => $weight){
				$final['faculty'][$item_num]['parts'][$sub_key]['weight'] = (int)$weight;
				$total_weight += (int)$weight;
			}
		}else{
			//No sub parts
			$final['faculty'][$item_num]['weight'] = (int)$_POST['weight'][$item_num]['main'];
			$total_weight += (int)$_POST['weight'][$item_num]['main'];
		}
		
		//Handle main desc
		$final['faculty'][$item_num]['desc'] = $types['main'];
		
		//Handle due dates
		$final['faculty'][$item_num]['week_due'] = (int)$_POST['due'][$item_num];
	}
	
	//Add Penalty row
	$final['faculty'][(count($final['faculty']) + 1)]['desc'] = "Penalty";
	
	//Check if total weight is 100%
	if($total_weight != 100){
		header("location: update_marking_scheme.php?y={$_POST['year']}&q={$_POST['quarter']}&err=1");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Set as JSON
	$final = json_encode($final);
	
	if(db_query(
		"UPDATE `semester_details` 
				SET
					`marking_scheme` = '{$final}'
				WHERE
					`year` = '{$_POST['year']}' AND
					`quarter` = '{$_POST['quarter']}';"
	) !== true){
		//Error when updating
		header("location: update_marking_scheme.php?y={$_POST['year']}&q={$_POST['quarter']}&err=0");
	}else{
		//Sucessfully edited
		
		//Reset all groups for the semester
		$semester_groups = get_all_groups($_POST['year'], $_POST['quarter']);
		
		$all_ids = [];
		
		foreach($semester_groups as $this_group){
			$all_ids[] = $this_group->id;
		}
		
		db_query(
				"UPDATE
					`groups`
				SET
					`grading` = 'null'
				WHERE
					`id` IN ('". implode("', '", $all_ids) ."');");
		
		header("location: update_marking_scheme.php?y={$_POST['year']}&q={$_POST['quarter']}");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>