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
	
	//If not super admin
	if(!$account->is_admin()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$all_logs = get_all_logs_path();
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Logs</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<h5>
			Click to traverse/download logs.
		</h5>
		<ul>
			<li id='current' style='cursor:pointer;'>
				Active
				<ul style='display:none;'>
					<?php
						foreach($all_logs['current'] as $log){
					?>
						<li>
							<a target='_blank' href='<?= "{$all_logs['base_path']}/{$log}" ?>' download='<?= $log ?>'><?= $log ?></a>
						</li>
					<?php
						}
				?>
				</ul>
			</li>
			<li id='archive' style='cursor:pointer;'>
				Archive
				<ul style='display:none;'>
					<?php
						foreach($all_logs['archive'] as $archive_log){
					?>
						<li>
							<a target='_blank' href='<?= "{$all_logs['base_path']}/archive_logs/{$archive_log}" ?>' download='<?= $archive_log ?>'><?= $archive_log ?></a>
						</li>
					<?php
						}
					?>
				</ul>
			</li>
		</ul>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
	<script>
		$("#current, #archive").click(function(e){
			if($(e.target).is('#current, #archive')){
				$(this).children("ul").toggle();
			}
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>