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
			case 0:
				$err = "Unexpected error.";
				break;
			
			case 1:
				$err = "Deadline is not yet set.";
				break;
			
			case 2:
				$err = "Date has passed.";
				break;
			
			case 3:
				$err = "Invalid date.";
				break;
			
			case 9:
				$err = "Successfully updated.";
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
		<title>Add a new semester</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<div class='container'>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<form action='exec_semester.php' method='POST'>
			<table id='add_semester' class='basic_table' >
				<tr>
					<td colspan='2'>
						Add a new semester
					</td>
				</tr>
				<tr>
					<td>
						Year
					</td>
					<td>
						<input type='number' name='year' value='<?= date('Y') ?>' maxlength='4' class='form-control' required />
					</td>
				</tr>
				<tr>
					<td>
						Quarter
					</td>
					<td>
						<input type='number' name='quarter' value='<?= ceil(date('n') / 3) ?>' min='1' max='4' class='form-control' required />
					</td>
				</tr>
				<tr>
					<td>
						Deadline
					</td>
					<td>
						<input type='date' name='deadline' style='width:100%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Details
					</td>
					<td style='width:600px;'>
						<textarea name='details' style='width:100%; height:300px; resize:both;'></textarea>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' name='add' value='Add semester'>
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