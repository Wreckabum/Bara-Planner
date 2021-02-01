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
	
	try{
		$view_account = get_account($_GET['a']);
	}catch(Exception $e){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Account - <?= $view_account->get_full_name() ?></title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_account' class='basic_table' style='width:30%;'>
			<tr>
				<td colspan='2'>
					<?= $view_account->get_full_name() ?>'s Profile
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					First Name:
				</td>
				<td>
					<?= ucfirst($view_account->get_first_name()) ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Last Name:
				</td>
				<td>
					<?= ucfirst($view_account->get_last_name()) ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					E-mail:
				</td>
				<td>
					<?= $view_account->get_email() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Type:
				</td>
				<td>
					<?= $view_account->get_account_type() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Major:
				</td>
				<td>
					<?= $view_account->get_majors() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Year:
				</td>
				<td>
					<?= $view_account->get_year() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Quarter:
				</td>
				<td>
					<?= $view_account->get_quarter() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Phone:
				</td>
				<td>
					<?= $view_account->get_phone() ?>
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