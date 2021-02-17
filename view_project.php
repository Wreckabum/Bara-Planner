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
	$project = get_project($_GET['p']);
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Major - <?= $project['id'] ?></title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_major' class='basic_table' style='width:30%;'>
			<tr>
				<td colspan='2'>
					<?= $project['proj_id'] ?> (<?= $project['id'] ?>)
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Name:
				</td>
				<td>
					<?= $project['name'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Description:
				</td>
				<td>
					<?= nl2br($project['description']) ?>
				</td>
			</tr>
			
			<tr>
				<td style='width:25%;'>
					Available for:
				</td>
				<td>
					<?php						
						$available_for = get_major(json_decode($project['available_for']));
						
						foreach($available_for as $id => $major){
					?>
							<?= $id ?> - <?= $major['name'] ?>
							<br />
					<?php
						}
					?>
				</td>
			</tr>
			
			<tr>
				<td style='width:25%;'>
					Active:
				</td>
				<td>
					<?= ($project['active'] ? "Yes" : "No") ?>
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