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
	
	//Redirect to management if not local
	if(!IS_LOCAL){
		header("location: management.php.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
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
		<div class='container'>
		<h2>To implement dashboard based on user type</h2>
		<br />
		<br />
		Items left to do (? - denotes optional):
		<ul>
			<li>
				?-Options page (allow import/addition only if semester exists first, etc)
			</li>
			<li>
				?-For add/edit groups, scroll to error
			</li>
		</ul>
		</div>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>