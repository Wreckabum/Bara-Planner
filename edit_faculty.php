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
	
	//If not admin
	if(!$account->is_admin()){
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
		$faculty = get_account(str_clean($_GET['a']));
	}catch(Exception $e){
		header("location: view_all.php?t=faculty");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Edit a faculty account</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<span style='color:#E22C2C'><?= $err ?></span>
		<form action='exec_faculty.php' method='POST'>
			<table id='edit_faculty' class='basic_table' style='width:auto;'>
				<tr>
					<td colspan='2'>
						Edit faculty member
					</td>
				</tr>
				<tr>
					<td>
						SIM ID
					</td>
					<td>
						<input type='text' name='new_sim_id' maxlength='10' value='<?= $faculty->sim_id ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						UOW ID
					</td>
					<td>
						<input type='text' name='new_uow_id' maxlength='10' value='<?= $faculty->uow_id ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Name
					</td>
					<td>
						<input type='text' name='name' maxlength='64' value='<?= $faculty->get_name() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						SIM E-mail
					</td>
					<td>
						<input type='email' name='sim_email' value='<?= $faculty->get_sim_email() ?>' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Personal E-mail
					</td>
					<td>
						<input type='email' name='personal_email' value='<?= $faculty->get_personal_email() ?>' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Majors
					</td>
					<td>
						<?php
							$all_majors = get_all_majors();
							
							foreach($all_majors as $id => $details){
						?>
								<label><input type='checkbox' name='majors[]' value='<?= $id ?>' <?= (in_array($id, $faculty->get_majors()) ? "checked" : "") ?>/><?= $id ?> - <?= $details['name'] ?></label>
								<br />
						<?php
							}
						?>
					</td>
				</tr>
				<tr>
					<td>
						Phone number
					</td>
					<td>
						<input type='text' name='phone' value='<?= $faculty->get_phone() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='hidden' name='old_sim_id' value='<?= $faculty->sim_id ?>'/>
						<input type='hidden' name='old_uow_id' value='<?= $faculty->uow_id ?>'/>
						<input type='submit' name='edit' value='Edit faculty member'>
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