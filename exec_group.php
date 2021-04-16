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
	
	if(isset($_POST['add_multiple'])){
		//Adding multiple groups
		foreach($_POST as $key => $group){
			if(
				$key == "add_multiple" || 
				$key == "year" || 
				$key == "quarter" || 
				$key == "type"
			){
				continue;
			}
			
			$header_link = "add_group_multiple.php?semester={$_POST['year']}_{$_POST['quarter']}&type={$_POST['type']}";
			
			try{
				get_account($group['supervisor']);
			}catch(Exception $e){
				header("location: {$header_link}&err=1");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
			
			try{
				get_account($group['assessor']);
			}catch(Exception $e){
				header("location: {$header_link}&err=2");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
			
			try{
				get_project($group['project'], 'proj_id');
			}catch(Exception $e){
				header("location: {$header_link}&err=3");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
			
			$check_year = "";
			$check_quarter = "";
			$check_type = "";
			$members = [];
			
			foreach($group['students'] as $student){
				try{
					$check_account = get_account($student);
				}catch(Exception $e){
					header("location: {$header_link}&err=4");
					@mysqli_close($GLOBALS['mysql_link']);
					exit();
				}
				
				//For first student, set initial year/quarter
				if($check_year == "" && $check_year == ""){
					$check_year = $check_account->get_year();
					$check_quarter = $check_account->get_quarter();
				}else{
					//Check each subsequent student
					if($check_year != $check_account->get_year() || $check_quarter != $check_account->get_quarter()){
						header("location: {$header_link}&err=5");
						@mysqli_close($GLOBALS['mysql_link']);
						exit();
					}
				}
				
				//For first student, set initial type
				if($check_type == ""){
					$check_type = $check_account->get_account_type();
				}else{
					//Check each subsequent student
					if($check_type != $check_account->get_account_type()){
						header("location: {$header_link}&err=6");
						@mysqli_close($GLOBALS['mysql_link']);
						exit();
					}
				}
				
				//Populate the members array
				$temp = [];
				$temp['id'] = $check_account->sim_id;
				$temp['score'] = null;
				$members[] = $temp;
			}
			
			if(db_query(
				"INSERT INTO
					`groups`
						(`id`, 
						`name`, 
						`supervisor`, 
						`assessor`, 
						`members`, 
						`project`)
					VALUES
						(NULL, 
						'{$group['name']}', 
						'{$group['supervisor']}', 
						'{$group['assessor']}', 
						'". addslashes(json_encode($members)) ."', 
						'{$group['project']}');"
			) !== true){
				//Error when adding
				header("location: {$header_link}&err=0");
			}else{
				//Sucessfully added
				header("location: view_all.php?t=groups");
			}
		}
	}elseif(isset($_POST['update_multiple'])){
		//Updating multiple groups
		$all_groups = get_all_groups($_POST['year'], $_POST['quarter']);
			
		foreach($_POST as $key => $group){
			if(
				$key == "update_multiple" || 
				$key == "year" || 
				$key == "quarter" || 
				$key == "type"
			){
				continue;
			}
			
			$header_link = "edit_group_multiple.php?semester={$_POST['year']}_{$_POST['quarter']}&type={$_POST['type']}";
			
			try{
				get_account($group['supervisor']);
			}catch(Exception $e){
				header("location: {$header_link}&err=1");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
			
			try{
				get_account($group['assessor']);
			}catch(Exception $e){
				header("location: {$header_link}&err=2");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
			
			try{
				get_project($group['project'], 'proj_id');
			}catch(Exception $e){
				header("location: {$header_link}&err=3");
				@mysqli_close($GLOBALS['mysql_link']);
				exit();
			}
			
			$check_year = "";
			$check_quarter = "";
			$check_type = "";
			$members = [];
			
			foreach($group['students'] as $student){
				try{
					$check_account = get_account($student);
				}catch(Exception $e){
					header("location: {$header_link}&err=4");
					@mysqli_close($GLOBALS['mysql_link']);
					exit();
				}
				
				//For first student, set initial year/quarter
				if($check_year == "" && $check_year == ""){
					$check_year = $check_account->get_year();
					$check_quarter = $check_account->get_quarter();
				}else{
					//Check each subsequent student
					if($check_year != $check_account->get_year() || $check_quarter != $check_account->get_quarter()){
						header("location: {$header_link}&err=5");
						@mysqli_close($GLOBALS['mysql_link']);
						exit();
					}
				}
				
				//For first student, set initial type
				if($check_type == ""){
					$check_type = $check_account->get_account_type();
				}else{
					//Check each subsequent student
					if($check_type != $check_account->get_account_type()){
						header("location: {$header_link}&err=6");
						@mysqli_close($GLOBALS['mysql_link']);
						exit();
					}
				}
				
				//Populate the members array
				$temp = [];
				$temp['id'] = $check_account->sim_id;
				$temp['score'] = null;
				$members[] = $temp;
			}
			
			//Check if row exists
			$query = db_query("SELECT `id` FROM `groups` WHERE `id` = '{$group['id']}';");
			
			//If update
			if(mysqli_num_rows($query) == 1){
				if(db_query(
					"UPDATE `groups` 
						SET
							`name` = '{$group['name']}', 
							`supervisor` = '{$group['supervisor']}', 
							`assessor` = '{$group['assessor']}', 
							`members` = '". addslashes(json_encode($members)) ."', 
							`project` = '{$group['project']}'
						WHERE
							`id` = '{$group['id']}';"
				) !== true){
					//Error when updating
					header("location: {$header_link}&err=0");
				}
			}else{
				//New group
				if(db_query(
					"INSERT INTO
						`groups`
							(`id`, 
							`name`, 
							`supervisor`, 
							`assessor`, 
							`members`, 
							`project`)
						VALUES
							(NULL, 
							'{$group['name']}', 
							'{$group['supervisor']}', 
							'{$group['assessor']}', 
							'". addslashes(json_encode($members)) ."', 
							'{$group['project']}');"
				) !== true){
					//Error when adding
					header("location: add_group_multiple.php?semester={$_POST['year']}_{$_POST['quarter']}&type={$_POST['type']}&err=0");
				}else{
					//Sucessfully added
					header("location: view_all.php?t=groups");
				}
			}
			
			//Clear from the existing list of groups
			foreach($all_groups as $key => $this_group){
				if($this_group->id == $group['id']){
					unset($all_groups[$key]);
					break;
				}
			}
		}
		
		//Handle deletion of groups
		foreach($all_groups as $group){
			db_query(
				"DELETE FROM
					`groups`
				WHERE
					`id` = '{$group->id}';");
		}
		
		header("location: view_all.php?t=groups");
	}else{
		//Unknown error
		header("location: home.php");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>