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
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key){
		str_clean($value);
		$value = htmlspecialchars($value);
	});
	
	//No confirmation
	if(!isset($_POST['archive_confirm'])){
		header("location: semester_details.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Confirmation error
	if($_POST['archive_confirm'] != 1){
		header("location: semester_details.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Ensure semester record exists
	$semester_details = get_semester_details($_POST['year'], $_POST['quarter']);
	
	//Semester does not exist
	if(is_null($semester_details)){
		header("location: semester_details.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//////Begin archiving process ------------------------------------
		db_query("START TRANSACTION;");
	//----------------------------------------------------------------
	
	
	//////Archive semester details -----------------------------------
	
	//Insert into archive
/* 		if(db_query(
			"INSERT INTO 
				`archive_semester_details` 
					(`year`, 
					`quarter`, 
					`deadline`, 
					`details`) 
				VALUES 
					('{$semester_details->year}', 
					'{$semester_details->quarter}', 
					'{$semester_details->deadline}', 
					'{$semester_details->details}');"
		) !== true){
			//If error
			db_query("ROLLBACK;");
			header("location: semester_details.php?err=4");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
		
		//Delete from main table
		if(db_query(
			"DELETE FROM 
				`semester_details` 
			WHERE 
				`year` = '{$semester_details->year}' AND 
				`quarter` = '{$semester_details->quarter}';"
		) !== true){
			//If error
			db_query("ROLLBACK;");
			header("location: semester_details.php?err=4");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
*/
	
	//////------------------------------------------------------------
	
	
	//////Archive projects -------------------------------------------
/*		
 		$all_projects = get_all_projects($_POST['year'], $_POST['quarter']);
		$proj_ids = [];
		
		//Insert into archive
		foreach($all_projects as $project){
			if(db_query(
				"INSERT INTO
					`archive_projects`
						(`proj_id`, 
						`name`, 
						`description`, 
						`year`, 
						`quarter`)
					VALUES
						('{$project->proj_id}', 
						'{$project->get_name()}', 
						'". htmlspecialchars($project->get_description(), ENT_QUOTES) ."', 
						'{$project->get_year()}', 
						'{$project->get_quarter()}');"
			) !== true){
				//If error
				db_query("ROLLBACK;");
				header("location: semester_details.php?err=4");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
			
			$proj_ids[] = $project->id;
		}
		
		//Delete from main table
		if(count($proj_ids) > 0){
			if(db_query(
				"DELETE FROM 
					`projects` 
				WHERE 
					`id` IN ('". implode("', '", $proj_ids) ."');"
			) !== true){
				//If error
				db_query("ROLLBACK;");
				header("location: semester_details.php?err=4");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			};
		}
*/
	
	//////------------------------------------------------------------
	
	
	//////Archive accounts -------------------------------------------
/* 		
 		$all_students = get_students($_POST['year'], $_POST['quarter']);
		$all_majors = get_all_majors();
		$student_ids = [];
		
		//Insert into archive
		foreach($all_students as $student){
			if(db_query(
				"INSERT INTO
					`accounts`
						(`sim_id`, 
						`uow_id`, 
						`name`, 
						`phone`, 
						`sim_email`, 
						`personal_email`, 
						`type`, 
						`major_id`, 
						`major_name`, 
						`year`, 
						`quarter`
						)
					VALUES
						('{$student->sim_id}', 
						'{$student->uow_id}', 
						'{$student->get_name()}', 
						'{$student->get_phone()}', 
						'{$student->get_sim_email()}', 
						'{$student->get_personal_email()}', 
						'{$student->get_type_int()}', 
						'{$student->get_majors()}', 
						'{$all_majors[$student->get_majors()]->get_name()}', 
						'{$student->get_year()}', 
						'{$student->get_quarter()}');"
			) !== true){
				//If error
				db_query("ROLLBACK;");
				header("location: semester_details.php?err=4");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
			
			$student_ids[] = $student->sim_id;
		}
		
		//Delete from main table
		if(count($student_ids) > 0){
			if(db_query(
				"DELETE FROM 
					`accounts` 
				WHERE 
					`id` IN ('". implode("', '", $student_ids) ."');"
			) !== true){
				//If error
				db_query("ROLLBACK;");
				header("location: semester_details.php?err=4");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
		}
 */
	//////------------------------------------------------------------
	
	
	//////Archive groups ---------------------------------------------
/* 		
		$all_groups = get_all_groups($_POST['year'], $_POST['quarter']);
		$group_ids = [];
		
		foreach($all_groups as $group){
			$members = [];
			
			foreach($group->get_members() as $student){
				
				//Populate the members array
				$temp = [];
				$temp['id'] = $student->details->sim_id;
				$temp['score'] = $student->score;
				$members[] = $temp;
			}
			
			//Insert into archive
			if(db_query(
				"INSERT INTO
					`groups`
						(`name`, 
						`supervisor`, 
						`assessor`, 
						`members`, 
						`project_id`, 
						`project_name`, 
						`year`, 
						`quarter`)
					VALUES
						('{$group->get_name()}', 
						'{$group->get_supervisor()->get_name()}',
						'{$group->get_assessor()->get_name()}', 
						'". addslashes(json_encode($members)) ."',  
						'{$group->get_project()->proj_id}', 
						'{$group->get_project()->get_name()}', 
						'{$group->get_year()}', 
						'{$group->get_quarter()}');"
			) !== true){
				//If error
				db_query("ROLLBACK;");
				header("location: semester_details.php?err=4");
			}
			
			$group_ids[] = $group->id;
		}
		
		//Delete from main table
		if(count($group_ids) > 0){
			if(db_query(
				"DELETE FROM 
					`groups` 
				WHERE 
					`id` IN ('". implode("', '", $group_ids) ."');"
			) !== true){
				//If error
				db_query("ROLLBACK;");
				header("location: semester_details.php?err=4");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
		}
*/
	//////------------------------------------------------------------
	
	//////Complete archiving process ---------------------------------
		db_query("COMMIT;");
	//----------------------------------------------------------------
	
	//TODO: Link to view archive
	header("location: semester_details.php");
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	/* if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	} */
</script>