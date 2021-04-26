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
				Marking scheme upgrade
			</li>
			<li>
				Options page (allow import/addition only if semester exists first, etc)
			</li>
			<li>
				For add/edit groups, scroll to error
			</li>
		</ul>
		Clean-up:
		<ul>
			<li>
				Use classes for setters for updating where possible
			</li>
		</ul>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>