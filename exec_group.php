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
		//Working with multiple groups
		foreach($_POST as $key => $group){
			if($key == "add_multiple"){
				continue;
			}
			
			$header_link = "add_group_multiple.php?semester={$group['year']}_{$group['quarter']}&type={$group['type']}";
			
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
	}else{
		//Working with a single group
		$header_link = "";
		
		if(isset($_POST['add'])){
			$header_link = "add_group.php?semester={$_POST['semester']}&type={$_POST['type']}";
		}elseif(isset($_POST['edit'])){
			$header_link = "edit_group.php?g={$_POST['id']}";
		}else{
			$header_link = "home.php";
		}
		
		try{
			get_account($_POST['supervisor']);
		}catch(Exception $e){
			header("location: {$header_link}&err=1");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
		
		try{
			get_account($_POST['assessor']);
		}catch(Exception $e){
			header("location: {$header_link}&err=2");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
		
		try{
			get_project($_POST['project'], 'proj_id');
		}catch(Exception $e){
			header("location: {$header_link}&err=3");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
		
		$check_year = "";
		$check_quarter = "";
		$check_type = "";
		$members = [];
		
		foreach($_GET['student'] as $student){
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
		
		if(isset($_POST['add'])){
			//Deprecated
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
						'{$_POST['name']}', 
						'{$_POST['supervisor']}', 
						'{$_POST['assessor']}', 
						'". addslashes(json_encode($members)) ."', 
						'{$_POST['project']}');"
			) !== true){
				//Error when adding
				header("location: {$header_link}&err=0");
			}else{
				//Sucessfully added
				header("location: view_group.php?g=". mysqli_insert_id($GLOBALS['mysql_link']));
			}
		}elseif(isset($_POST['edit'])){
			if(db_query(
				"UPDATE `groups` 
					SET
						`name` = '{$_POST['name']}', 
						`supervisor` = '{$_POST['supervisor']}', 
						`assessor` = '{$_POST['assessor']}', 
						`members` = '". addslashes(json_encode($members)) ."', 
						`project` = '{$_POST['project']}'
					WHERE
						`id` = '{$_POST['id']}';"
			) !== true){
				//Error when updating
				header("location: {$header_link}&err=1");
			}else{
				//Sucessfully edited
				header("location: view_group.php?g={$_POST['id']}");
			}
		}else{
			//Unknown error
			header("location: view_all.php?t=groups");
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>