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
	
	//If not super admin or own account
	if(!$account->is_super() && $account->sim_id != $_POST['id']){
		header("location: home.php");
		exit();
	}
	
	//Prepare the strings for SQL insertion
	array_walk_recursive($_POST, function(&$value, $key){
		str_clean($value);
		$value = htmlspecialchars($value);
	});
	
	if(isset($_POST['add'])){
		if(db_query(
			"INSERT INTO
				`accounts`
					(`sim_id`, 
					`uow_id`, 
					`name`, 
					`sim_email`, 
					`personal_email`, 
					`type`, 
					`phone`)
				VALUES
					('{$_POST['sim_id']}', 
					'{$_POST['uow_id']}', 
					'{$_POST['name']}', 
					'{$_POST['sim_email']}', 
					'{$_POST['personal_email']}', 
					'8', 
					'{$_POST['phone']}')"
			) !== true){
			//Error when adding
			header("location: add_admin.php?err=1");
		}else{
			//Sucessfully added
			header("location: view_account.php?a={$_POST['sim_id']}");
		}
	}elseif(isset($_POST['edit'])){
		if($account->sim_id == $_POST['id']){
			$query = 
				"UPDATE `accounts` 
					SET
						`name` = '{$_POST['name']}', 
						`sim_email` = '{$_POST['sim_email']}',  
						`personal_email` = '{$_POST['personal_email']}',  
						`phone` = '{$_POST['phone']}'
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
						`phone` = '{$_POST['phone']}'
					WHERE
						`sim_id` = '{$_POST['old_sim_id']}';";
		}
		
		if(db_query($query) !== true){
			//Error when updating
			header("location: edit_admin.php?a={$_POST['old_sim_id']}err=1");
		}else{
			//Sucessfully edited
			header("location: view_account.php?a={$_POST['new_sim_id']}");
		}
	}else{
		//Unknown error
		header("location: view_all.php?t=admin");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>