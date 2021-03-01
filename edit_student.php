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
		$student = get_account(str_clean($_GET['a']));
	}catch(Exception $e){
		header("location: view_all.php?t=students");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Edit a student</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<span style='color:#E22C2C'><?= $err ?></span>
		<form action='exec_student.php' method='POST'>
			<table id='edit_student' class='basic_table' style='width:auto;'>
				<tr>
					<td colspan='2'>
						Edit a student
					</td>
				</tr>
				<tr>
					<td>
						ID
					</td>
					<td>
						<input type='text' name='new_id' maxlength='10' value='<?= $student->id ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Name
					</td>
					<td>
						<input type='text' name='name' maxlength='64' value='<?= $student->get_name() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						E-mail
					</td>
					<td>
						<input type='email' name='email' maxlength='64' value='<?= $student->get_email() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Type
					</td>
					<td>
						<label><input type='radio' name='type' value='1' required <?= ($student->is_full_time() ? "checked" : "") ?>/> Full-time Student</label>
						<br />
						<label><input type='radio' name='type' value='2' required <?= ($student->is_part_time() ? "checked" : "") ?>/> Part-time Student</label>
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
									<option value='<?= $id ?>' <?= ($id == $student->get_majors() ? "selected" : "") ?>><?= $id ?> - <?= $details['name'] ?></option>
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
						<input type='number' name='year' value='<?= $student->get_year() ?>' maxlength='4'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Quarter
					</td>
					<td>
						<input type='number' name='quarter' value='<?= $student->get_quarter() ?>' min='1' max='4' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Phone number
					</td>
					<td>
						<input type='text' name='phone' value='<?= $student->get_phone() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='hidden' name='old_id' value='<?= $student->id ?>'/>
						<input type='submit' name='edit' value='Edit student'>
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