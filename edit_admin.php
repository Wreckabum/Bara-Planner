<?php
	// Initialize the session
	session_start();
	
	//If there is no session
	if(!isset($_SESSION["loggedin"])){
		header("location: index.php");
		exit();
	}
	
	//Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	
	//If not super admin
	if(!$account->is_super()){
		header("location: home.php");
		exit();
	}
	
	$err = "";
	
	if(isset($_GET['err'])){
		switch($_GET['err']){
			case 1:
				$err = "Account already exists.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
	
	try{
		$admin = get_account(str_clean($_GET['a']));
	}catch(Exception $e){
		header("location: view_all.php?t=admin");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Edit an administrator account</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<span style='color:#E22C2C'><?= $err ?></span>
		<form action='exec_admin.php' method='POST'>
			<table id='edit_admin' class='basic_table' style='width:30%;'>
				<tr>
					<td colspan='2'>
						Edit administrator
					</td>
				</tr>
				<tr>
					<td>
						SIM ID
					</td>
					<td>
						<input type='text' name='new_sim_id' maxlength='10' value='<?= $admin->sim_id ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						UOW ID
					</td>
					<td>
						<input type='text' name='new_uow_id' maxlength='10' value='<?= $admin->uow_id ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Name
					</td>
					<td>
						<input type='text' name='name' maxlength='64' value='<?= $admin->get_name() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						SIM E-mail
					</td>
					<td>
						<input type='email' name='sim_email' value='<?= $admin->get_sim_email() ?>' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Personal E-mail
					</td>
					<td>
						<input type='email' name='personal_email' value='<?= $admin->get_personal_email() ?>' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Phone number
					</td>
					<td>
						<input type='text' name='phone' value='<?= $admin->get_phone() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='hidden' name='old_sim_id' value='<?= $admin->sim_id ?>'/>
						<input type='hidden' name='old_uow_id' value='<?= $admin->uow_id ?>'/>
						<input type='submit' name='edit' value='Edit admnistrator'>
					</td>
				</tr>
			</table>
		</form>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>