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
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Add a new faculty member</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<span style='color:#E22C2C'><?= $err ?></span>
		<form action='add_faculty_exec.php' method='POST'>
			<table id='add_faculty' class='basic_table' style='width:auto;'>
				<tr>
					<td colspan='2'>
						Add a new faculty member
					</td>
				</tr>
				<tr>
					<td>
						First Name
					</td>
					<td>
						<input type='text' name='first_name' placeholder='First Name' maxlength='64' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Last Name
					</td>
					<td>
						<input type='text' name='last_name' placeholder='Last Name' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						E-mail
					</td>
					<td>
						<input type='email' name='email' placeholder='account@email.com' maxlength='64'  style='width:97%;' required />
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
								<label><input type='checkbox' name='majors[]' value='<?= $id ?>' /><?= $details['name'] ?></label>
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
						<input type='text' name='phone' placeholder='98789636' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' name='submit' value='Add faculty member'>
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