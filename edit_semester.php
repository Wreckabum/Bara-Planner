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
	
	//Update any potentially missing deadlines
	add_missing_deadlines();
	
	$semester_details = get_semester($_GET['y'], $_GET['q']);
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Edit semester - Year <?= $_GET['y'] ?>, Quarter <?= $_GET['q'] ?></title>
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
			<table id='update_semester' class='basic_table' >
				<tr>
					<td colspan='2'>
						Semester details
					</td>
				</tr>
				<tr>
					<td>
						Year
					</td>
					<td>
						<?= $semester_details->year ?>
						<input type='hidden' name='year' value='<?= $semester_details->year ?>' />
					</td>
				</tr>
				<tr>
					<td>
						Quarter
					</td>
					<td>
						<?= $semester_details->quarter ?>
						<input type='hidden' name='quarter' value='<?= $semester_details->quarter ?>' />
					</td>
				</tr>
				<tr>
					<td>
						Deadline
					</td>
					<td>
						<input type='date' name='deadline' <?= ((is_null($semester_details->deadline)) ? "" : "value='{$semester_details->deadline}'") ?> style='width:100%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Details
					</td>
					<td style='width:600px;'>
						<textarea name='details' style='width:100%; height:300px; resize:both;'><?= $semester_details->details ?></textarea>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='submit' name='update' value='Update'>
					</td>
				</tr>
			</table>
		</form>
		<br />
		<a href='semester_details.php'>Back to all semester details</a>
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