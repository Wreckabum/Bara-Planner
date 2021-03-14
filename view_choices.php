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
	
	//If not student
	if(!$account->is_student()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$projects = get_project($account->get_choices());
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Choices</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php
			include("include/templates/header.php");
			
			if(is_null($projects)){
		?>
				<a href='make_choices.php'>Make Choices</a>
				<br />
				<a href='home.php'>Back to main page</a>
		<?php
			}else{
				$choice = 1;
				
				foreach($projects as $id => $project){
		?>
					<table id='choice_<?= $choice ?>' class='basic_table' style='width:40%;'>
						<tr>
							<td colspan='2'>
								Choice #<?= $choice++ ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Project ID:
							</td>
							<td>
								<?= $project['proj_id'] ?>
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
					</table>
					<br />
		<?php
				}
		?>
				<a href='make_choices.php'>Update Choices</a>
				<br />
				<a href='home.php'>Back to main page</a>
		<?php
			}
		?>
		</div>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>