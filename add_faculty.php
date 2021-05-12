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
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Add a new faculty member</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<div class='container'>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<form action='exec_faculty.php' method='POST'>
			<table id='add_faculty' class='basic_table' >
				<tr>
					<td colspan='2'>
						Add a new faculty member
					</td>
				</tr>
				<tr>
					<td>
						SIM ID
					</td>
					<td>
						<input type='text' name='sim_id' placeholder='SIM ID' maxlength='10' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						UOW ID
					</td>
					<td>
						<input type='text' name='uow_id' placeholder='UOW ID' maxlength='10' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Name
					</td>
					<td>
						<input type='text' name='name' placeholder='Name' maxlength='64' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						SIM E-mail
					</td>
					<td>
						<input type='email' name='sim_email' placeholder='account@mymail.sim.edu.sg' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Personal E-mail
					</td>
					<td>
						<input type='email' name='personal_email' placeholder='account@email.com' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Phone number
					</td>
					<td>
						<input type='text' name='phone' placeholder='98789636' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Majors
					</td>
					<td>
						<?php
							$all_majors = get_all_majors();
							
							foreach($all_majors as $major){
						?>
								<label><input type='checkbox' name='majors[]' value='<?= $major->id ?>' /> <?= $major->id ?> - <?= $major->get_name() ?></label>
								<br />
						<?php
							}
						?>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' name='add' value='Add faculty member'>
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