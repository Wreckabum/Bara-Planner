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
	
	//If not admin or not own account
	if(!$account->is_admin() && $account->sim_id != $_GET['a']){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
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
		$faculty = get_account($_GET['a']);
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
		<title><?= (($account->sim_id == $faculty->sim_id) ? "Edit your account details" : "Edit a faculty account") ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<form action='exec_faculty.php' method='POST'>
			<table id='edit_faculty' class='basic_table' style='width:auto;'>
				<tr>
					<td colspan='3'>
						<span style='float:left;'>
							<?= (($account->sim_id == $faculty->sim_id) ? "Edit your account details" : "Edit a faculty account") ?>
						</span>
						<span style='float:right;'>
							<?= (($account->sim_id == $faculty->sim_id) ? "<a href='forget_password.php'>Reset password</a>" : "") ?>
						</span>
					</td>
				</tr>
				<tr>
					<td>
						SIM ID
					</td>
					<td colspan='2'>
						<?php
							if($account->is_admin()){
						?>
								<input type='text' name='new_sim_id' maxlength='10' value='<?= $faculty->sim_id ?>' style='width:97%;' required />
						<?php
							}else{
						?>
								<?= $faculty->sim_id ?>
						<?php
							}
						?>
					</td>
				</tr>
				<tr>
					<td>
						UOW ID
					</td>
					<td colspan='2'>
						<?php
							if($account->is_admin()){
						?>
								<input type='text' name='new_uow_id' maxlength='10' value='<?= $faculty->uow_id ?>' style='width:97%;' required />
						<?php
							}else{
						?>
								<?= $faculty->uow_id ?>
						<?php
							}
						?>
					</td>
				</tr>
				<tr>
					<td>
						Name
					</td>
					<td colspan='2'>
						<input type='text' name='name' maxlength='64' value='<?= $faculty->get_name() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						SIM E-mail
					</td>
					<td colspan='2'>
						<input type='email' name='sim_email' value='<?= $faculty->get_sim_email() ?>' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Personal E-mail
					</td>
					<td>
						<input type='email' name='personal_email' value='<?= $faculty->get_personal_email() ?>' maxlength='64'  style='width:97%;' />
					</td>
					<?php
						if($account->sim_id == $faculty->sim_id){
							//Only allow self to update the field
					?>
							<td>
								<label><input type='checkbox' name='show_email' value='1' <?= (($faculty->show_email()) ? "checked" : "") ?>/> Show to others?</label>
							</td>
					<?php
						}else{
					?>
							<td style='background-color:<?= (($faculty->show_email()) ? "#C7E8C7" : "#E28D8D") ?>'>
								<?= (($faculty->show_email()) ? "Shown" : "Hidden") ?>
							</td>
					<?php
						}
					?>
				</tr>
					<td>
						Phone number
					</td>
					<td>
						<input type='text' name='phone' value='<?= $faculty->get_phone() ?>' style='width:97%;' />
					</td>
					<?php
						if($account->sim_id == $faculty->sim_id){
							//Only allow self to update the field
					?>
							<td>
								<label><input type='checkbox' name='show_phone' value='1' <?= (($faculty->show_phone()) ? "checked" : "") ?>/> Show to others?</label>
							</td>
					<?php
						}else{
					?>
							<td style='background-color:<?= (($faculty->show_phone()) ? "#C7E8C7" : "#E28D8D") ?>'>
								<?= (($faculty->show_phone()) ? "Shown" : "Hidden") ?>
							</td>
					<?php
						}
					?>
				</tr>
				<tr>
					<td>
						Majors
					</td>
					<td colspan='2'>
						<?php
							$all_majors = get_all_majors();
							
							foreach($all_majors as $major){
						?>
								<label><input type='checkbox' name='majors[]' value='<?= $major->id ?>' <?= (in_array($major->id, $faculty->get_majors()) ? "checked" : "") ?>/><?= $major->id ?> - <?= $major->get_name() ?></label>
								<br />
						<?php
							}
						?>
					</td>
				</tr>
				<tr>
				<tr>
					<td colspan='3'>
						<?php
							if($account->is_admin()){
						?>
								<input type='hidden' name='old_sim_id' value='<?= $faculty->sim_id ?>'/>
								<input type='hidden' name='old_uow_id' value='<?= $faculty->uow_id ?>'/>
						<?php
							}else{
						?>
								<input type='hidden' name='id' value='<?= $faculty->sim_id ?>'/>
						<?php
							}
						?>
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