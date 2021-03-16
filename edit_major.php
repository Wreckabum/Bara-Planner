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
				$err = "Major ID already exists.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
	
	try{
		$major = get_major($_GET['m']);
	}catch(Exception $e){
		header("location: view_all.php?t=majors");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Edit a major</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<form action='exec_major.php' method='POST'>
			<table id='edit_major' class='basic_table' style='width:40%;'>
				<tr>
					<td colspan='2'>
						Edit a major
					</td>
				</tr>
				<tr>
					<td style='width:25%;'>
						ID
					</td>
					<td>
						<input type='text' name='new_id' maxlength='8' value='<?= $major->id ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Name
					</td>
					<td>
						<input type='text' name='name' maxlength='64'  value='<?= $major->get_name() ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Description
					</td>
					<td>
						<textarea name='description' rows='10' style='width:97%;' required><?= $major->get_description() ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						Type
					</td>
					<td>
						<label><input type='radio' name='type' value='1' required <?= ($major->is_full_time() ? "checked" : "") ?>/> Full-time Student</label>
						<br />
						<label><input type='radio' name='type' value='2' required <?= ($major->is_part_time() ? "checked" : "") ?>/> Part-time Student</label>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='hidden' name='old_id' value='<?= $major->id ?>'/>
						<input type='submit' name='edit' value='Edit major'>
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