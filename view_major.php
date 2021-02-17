<?php
	// Initialize the session
	session_start();
	
	//If there is no session
	if(!isset($_SESSION["loggedin"])){
		header("location: index.php");
		exit();
	}
	
	// Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	$major = get_major($_GET['m']);
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Major - <?= $major['id'] ?></title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_major' class='basic_table' style='width:30%;'>
			<tr>
				<td colspan='2'>
					<?= $major['id'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Name:
				</td>
				<td>
					<?= $major['name'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Description:
				</td>
				<td>
					<?= nl2br($major['description']) ?>
				</td>
			</tr>
			
			<tr>
				<td style='width:25%;'>
					Part Time:
				</td>
				<td>
					<?= ($major['part_time'] ? "Yes" : "No") ?>
				</td>
			</tr>
			
			<tr>
				<td style='width:25%;'>
					Full Time:
				</td>
				<td>
					<?= ($major['full_time'] ? "Yes" : "No") ?>
				</td>
			</tr>
		</table>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>