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
	
	//If not admin or own account
	if(!$account->is_admin() && $account->sim_id != $_POST['id']){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key){
		str_clean($value);
		$value = htmlspecialchars($value);
	});
	
	if(isset($_POST['add'])){
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
					`phone`,
					`password`
					)
				VALUES
					('{$_POST['sim_id']}', 
					'{$_POST['uow_id']}', 
					'{$_POST['name']}', 
					'{$_POST['sim_email']}', 
					'{$_POST['personal_email']}', 
					'0', 
					'". addslashes(json_encode($_POST['majors'])) ."', 
					'{$_POST['phone']}',
					'{$password}')"
		) !== true){
			//Error when adding
			header("location: add_faculty.php?err=1");
		}else{
			//Sucessfully added
			header("location: view_account.php?a={$_POST['sim_id']}");
		}
	}elseif(isset($_POST['edit'])){
		$_POST['id'] = ((isset($_POST['id'])) ? $_POST['id'] : "");
		$show_phone = (isset($_POST['show_phone']) ? 1 : 0);
		$show_email = (isset($_POST['show_email']) ? 1 : 0);
		
		if($account->sim_id == $_POST['id']){
			$query = 
				"UPDATE `accounts` 
					SET
						`name` = '{$_POST['name']}', 
						`sim_email` = '{$_POST['sim_email']}', 
						`personal_email` = '{$_POST['personal_email']}', 
						`majors` = '". addslashes(json_encode($_POST['majors'])) ."', 
						`phone` = '{$_POST['phone']}', 
						`show_phone` = '{$show_phone}', 
						`show_email` = '{$show_email}'
					WHERE
						`sim_id` = '{$_POST['id']}';";
			
			$_POST['new_sim_id'] = $_POST['id'];
		}else{
			$query = 
				"UPDATE `accounts` 
					SET
						`sim_id` = '{$_POST['new_sim_id']}', 
						`uow_id` = '{$_POST['new_uow_id']}', 
						`name` = '{$_POST['name']}', 
						`sim_email` = '{$_POST['sim_email']}', 
						`personal_email` = '{$_POST['personal_email']}', 
						`majors` = '". addslashes(json_encode($_POST['majors'])) ."', 
						`phone` = '{$_POST['phone']}'
					WHERE
						`sim_id` = '{$_POST['old_sim_id']}';";
		}

		if(db_query($query) !== true){
			//Error when updating
			header("location: edit_faculty.php?a={$_POST['old_sim_id']}err=1");
		}else{
			//Sucessfully edited
			
			//If admin doing the update
			if($account->sim_id != $_POST['id']){
				//Update groups
				db_query(
					"UPDATE `groups` 
						SET
							`supervisor` = 
								CASE
									WHEN `supervisor` = '{$_POST['old_sim_id']}'
										THEN '{$_POST['new_sim_id']}'
									ELSE
										`supervisor`
								END,
							`assessor` = 
								CASE
									WHEN `assessor` = '{$_POST['old_sim_id']}'
										THEN '{$_POST['new_sim_id']}'
									ELSE
										`assessor`
								END
						WHERE
							`supervisor` = '{$_POST['old_sim_id']}' OR
							`assessor` = '{$_POST['old_sim_id']}';"
				);
			}
			
			header("location: view_account.php?a={$_POST['new_sim_id']}");
		}
	}else{
		//Unknown error
		header("location: view_all.php?t=faculty");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>