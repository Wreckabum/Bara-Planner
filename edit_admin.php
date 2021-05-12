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
	
	//If not super admin or not own account
	if(!$account->is_super() && $account->sim_id != $_GET['a']){
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
		$admin = get_account($_GET['a']);
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
		<title><?= (($account->sim_id == $admin->sim_id) ? "Edit your account details" : "Edit administrator") ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<div class='container'>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<form action='exec_admin.php' method='POST'>
			<table id='edit_admin' class='basic_table' >
				<tr>
					<td colspan='3'>
						<span style='float:left;'>
							<?= (($account->sim_id == $admin->sim_id) ? "Edit your account details" : "Edit administrator") ?>
						</span>
					</td>
				</tr>
				<tr>
					<td>
						SIM ID
					</td>
					<td colspan='2'>
						<?php
							if($account->is_super()){
						?>
								<input type='text' name='new_sim_id' maxlength='10' value='<?= $admin->sim_id ?>' style='width:97%;' required />
						<?php
							}else{
						?>
								<?= $admin->sim_id ?>
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
							if($account->is_super()){
						?>
								<input type='text' name='new_uow_id' maxlength='10' value='<?= $admin->uow_id ?>' style='width:97%;' required />
						<?php
							}else{
						?>
								<?= $admin->uow_id ?>
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
						<input type='text' name='name' maxlength='64' value='<?= $admin->get_name() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						SIM E-mail
					</td>
					<td colspan='2'>
						<input type='email' name='sim_email' value='<?= $admin->get_sim_email() ?>' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Personal E-mail
					</td>
					<td>
						<input type='email' name='personal_email' value='<?= $admin->get_personal_email() ?>' maxlength='64'  style='width:97%;' />
					</td>
					<?php
						if($account->sim_id == $admin->sim_id){
							//Only allow self to update the field
					?>
							<td>
								<label><input type='checkbox' name='show_email' value='1' <?= (($admin->show_email()) ? "checked" : "") ?>/> Show to others?</label>
							</td>
					<?php
						}else{
					?>
							<td style='background-color:<?= (($admin->show_email()) ? "#C7E8C7" : "#E28D8D") ?>'>
								<?= (($admin->show_email()) ? "Shown" : "Hidden") ?>
							</td>
					<?php
						}
					?>
				</tr>
				<tr>
					<td>
						Phone number
					</td>
					<td>
						<input type='text' name='phone' value='<?= $admin->get_phone() ?>' style='width:97%;' />
					</td>
					<?php
						if($account->sim_id == $admin->sim_id){
							//Only allow self to update the field
					?>
							<td>
								<label><input type='checkbox' name='show_phone' value='1' <?= (($admin->show_phone()) ? "checked" : "") ?>/> Show to others?</label>
							</td>
					<?php
						}else{
					?>
							<td style='background-color:<?= (($admin->show_phone()) ? "#C7E8C7" : "#E28D8D") ?>'>
								<?= (($admin->show_phone()) ? "Shown" : "Hidden") ?>
							</td>
					<?php
						}
					?>
				</tr>
				<tr>
					<td colspan='3'>
						<?php
							if($account->is_super()){
						?>
								<input type='hidden' name='old_sim_id' value='<?= $admin->sim_id ?>'/>
								<input type='hidden' name='old_uow_id' value='<?= $admin->uow_id ?>'/>
						<?php
							}else{
						?>
								<input type='hidden' name='id' value='<?= $admin->sim_id ?>'/>
						<?php
							}
						?>
						<input type='submit' name='edit' value='Edit admnistrator'>
					</td>
				</tr>
			</table>
		</form>
		<br />
		<a href='home.php'>Back to main page</a>
		</div>
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