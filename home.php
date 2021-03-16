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
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Home Dashboard</title>
		<link rel='stylesheet' href='include/css/main.css' />
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<h2>To implement dashboard based on user type</h2>
		<br />
		<br />
		Items left to do:
		<ul>
			<li>
				Set deadline for students making choices
			</li>
			<li>
				Page for sending emails to students
			</li>
			<li>
				Archiving
			</li>
			<li>
				Highlight repeat student based on archives
			</li>
		</ul>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>