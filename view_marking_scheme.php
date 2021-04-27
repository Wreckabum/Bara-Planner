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
			case 0:
				$err = "Unexpected error.";
				break;
			
			case 9:
				$err = "Successfully updated.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
	
	//Update any potentially missing deadlines
	add_missing_deadlines();
	
	$marking_scheme_table = print_marking_scheme($_GET['y'], $_GET['q']);
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Marking Scheme - Year <?= $_GET['y'] ?>, Quarter <?= $_GET['q'] ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<style>
			.basic_table td {
				text-align: center;
				white-space: nowrap;
			}
			
			.basic_table td.empty {
				background-color: #E4E4E4;
			}
			
			.supervisor, .assessor, .total, .average {
				width: 125px;
			}
			
			.supervisor {
				background-color: #E6ffE6;
			}
			
			.assessor {
				background-color: #CFCFFF;
			}
			
			.penalty {
				background-color: #FFCECE;
			}
		</style>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<?= $marking_scheme_table ?>
		<br />
		<a href='update_marking_scheme.php?y=<?= $_GET['y'] ?>&q=<?= $_GET['q'] ?>'>Update marking scheme</a>
		<br />
		<br />
		<a href='semester_details.php'>Back to all semester details</a>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
	<script>
		
	</script>
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