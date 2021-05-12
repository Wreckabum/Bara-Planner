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
		$group = get_group($_GET['g']);
		$title_extra = " (". $group->id .")";
	}catch(Exception $e){
		header("location: view_all.php?t=groups");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If not assessor/supervisor
	if(!$group->is_supervisor($account->sim_id) && !$group->is_assessor($account->sim_id)){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Grade Group<?= $title_extra ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?><div class='container'>
		<?= $group->print_marking_scheme($account->sim_id) ?>
		<a href='view_group.php?g=<?= $group->id ?>'>Back to group details</a>
		<br />
		<a href='view_group.php'>View all assigned groups</a>
		<br />
		<a href='home.php'>Back to main page</a>
		</div>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>