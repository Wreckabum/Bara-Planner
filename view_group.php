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
	$group = get_group($_GET['g']);
	
	//If no such project
	if(is_null($group)){
		header("location: view_all.php?t=groups");
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Group - <?= $group['id'] ?></title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_group' class='basic_table' style='width:40%;'>
			<tr>
				<td colspan='2'>
					Group #<?= $group['id'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Name:
				</td>
				<td>
					<?= $group['name'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Supervisor:
				</td>
				<td>
					<?= (is_null($group['supervisor']) ? "" : "{$group['supervisor']->id} - {$group['supervisor']->get_name()}") ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Assessor:
				</td>
				<td>
					<?= (is_null($group['assessor']) ? "" : "{$group['assessor']->id} - {$group['assessor']->get_name()}") ?>
				</td>
			</tr>
			
			<tr>
				<td style='width:25%;'>
					Members:
				</td>
				<td>
					<?php
						foreach($group['members'] as $member){
					?>
							<?= $member->id ?> - <?= $member->get_name() ?>
							<br />
					<?php
						}
					?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Project:
				</td>
				<td>
					<?= $group['project']['id'] ?> - <?= $group['project']['name'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Deadline:
				</td>
				<td>
					<?= $group['deadline'] ?>
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