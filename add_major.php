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
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Add a new student</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<form action='exec_major.php' method='POST'>
			<table id='add_major' class='basic_table' style='width:30%;'>
				<tr>
					<td colspan='2'>
						Add a new major
					</td>
				</tr>
				<tr>
					<td>
						ID
					</td>
					<td>
						<input type='text' name='id' placeholder='CSIT111' maxlength='8' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Name
					</td>
					<td>
						<input type='text' name='name' placeholder='Bachelor of Computer Science (Big Data)' maxlength='64'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Description
					</td>
					<td>
						<textarea name='description' placeholder='A summary of the major' rows='10' style='width:97%;' required></textarea>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' name='add' value='Add major'>
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