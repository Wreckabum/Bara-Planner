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
		<title>Add a new student</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<span style='color:#E22C2C'><?= $err ?></span>
		<form action='add_student_exec.php' method='POST'>
			<table id='add_student' class='basic_table' style='width:30%;'>
				<tr>
					<td colspan='2'>
						Add a new student
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						First Name
					</td>
					<td style='width:75%;'>
						<input type='text' name='first_name' placeholder='First Name' maxlength='64' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						Last Name
					</td>
					<td style='width:75%;'>
						<input type='text' name='last_name' placeholder='Last Name' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						E-mail
					</td>
					<td style='width:75%;'>
						<input type='email' name='email' placeholder='account@email.com' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						Type
					</td>
					<td style='width:75%;'>
						<input type='radio' name='type' value='1' required /> Full-time Student
						<br />
						<input type='radio' name='type' value='2' required /> Part-time Student
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						Major
					</td>
					<td style='width:75%;'>
						<select name='major' style='width:97%;' required>
							<?php
								$all_majors = get_all_majors();
								
								foreach($all_majors as $id => $details){
							?>
									<option value='<?= $id ?>'><?= $details['name'] ?></option>
							<?php
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						Year
					</td>
					<td style='width:75%;'>
						<input type='number' name='year' value='<?= date('Y') ?>' maxlength='4'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						Quarter
					</td>
					<td style='width:75%;'>
						<input type='number' name='quarter' value='<?= ceil(date('n') / 3) ?>' min='1' max='4' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						Phone number
					</td>
					<td style='width:75%;'>
						<input type='text' name='phone' placeholder='98789636' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' name='submit' value='Add student'>
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