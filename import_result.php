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
	
	//If not admin
	if(!$account->is_admin()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$errors = ((isset($_GET['err'])) ? json_decode($_GET['err']) : []);
	$headers = ((isset($_GET['h'])) ? json_decode($_GET['h']) : []);
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Review imported data</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='stylesheet' href='include/css/dataTables.min.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>		
		<script src='include/js/dataTables.min.js'></script>
	</head>
	<body>
		<?php include('include/templates/header.php'); ?>
		<h4>
			Successfully added: <?= $_GET['c'] ?> Students
		</h4>
		<br />
		<h4>
			Errors: <?= count($errors) ?>
		</h4>
		<?php
			if(count($errors) > 0){
		?>
				<table id='filter_table' class='display'>
					<thead>
						<tr>
							<?php
								foreach($headers as $header){
							?>
									
									<th>
										<?= $header ?>
									</th>
							<?php
								}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach($errors as $error){
						?>
								<tr>
									<?php
										foreach($headers as $header){
									?>
											<td>
												<?= $error->$header ?>
											</td>
									<?php
										}
									?>
								</tr>
						<?php
							}
						?>
					</tbody>
				</table>
		<?php
			}
		?>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
	<script>
		$("#filter_table").DataTable({
			/* Disable initial sort */
			"aaSorting": [],
			"paging": false
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>