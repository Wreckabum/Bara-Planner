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
				$err = "Project ID already exists.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
	
	$project = get_project(str_clean($_GET['p']));
	
	//If no such project
	if(is_null($project)){
		header("location: view_all.php?t=projects");
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Edit a project</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<form action='exec_project.php' method='POST'>
			<table id='edit_project' class='basic_table' style='width:30%;'>
				<tr>
					<td colspan='2'>
						Edit a project
					</td>
				</tr>
				<tr>
					<td>
						Project ID
					</td>
					<td>
						<input type='text' name='proj_id' maxlength='11' value='<?= $project['proj_id'] ?>' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Name
					</td>
					<td>
						<input type='text' name='name' maxlength='24' value='<?= $project['name'] ?>'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Description
					</td>
					<td>
						<textarea name='description' rows='10' style='width:97%;' required><?= $project['description'] ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						Year
					</td>
					<td>
						<input type='number' name='year' value='<?= $project['year'] ?>' maxlength='4'  style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td>
						Quarter
					</td>
					<td>
						<input type='number' name='quarter' value='<?= $project['quarter'] ?>' min='1' max='4' style='width:97%;' required />
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<input type='hidden' name='id' value='<?= $project['id'] ?>'/>
						<input type='submit' name='edit' value='Edit project'>
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