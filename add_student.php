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
		<title>Add a new student</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<form action='exec_student.php' method='POST'>
			<table id='add_student' class='basic_table' style='width:auto;'>
				<tr>
					<td colspan='2'>
						Add a new student
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
						<input type='text' name='name' placeholder='Name' maxlength='64'  style='width:97%;' required />
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
						Type
					</td>
					<td>
						<label><input type='radio' name='type' value='1' required /> Full-time Student</label>
						<br />
						<label><input type='radio' name='type' value='2' required /> Part-time Student</label>
					</td>
				</tr>
				<tr>
					<td>
						Major
					</td>
					<td>
						<select name='major' style='width:97%;' required>
							<?php
								$all_majors = get_all_majors();
								
								foreach($all_majors as $id => $details){
							?>
									<option value='<?= $id ?>'><?= $id ?> - <?= $details['name'] ?></option>
							<?php
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						Year
					</td>
					<td>
						<input type='number' name='year' value='<?= date('Y') ?>' maxlength='4'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Quarter
					</td>
					<td>
						<input type='number' name='quarter' value='<?= ceil(date('n') / 3) ?>' min='1' max='4' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' name='add' value='Add student'>
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