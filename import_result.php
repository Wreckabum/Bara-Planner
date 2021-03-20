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
	$year = ((isset($_GET['y'])) ? str_clean($_GET['y']) : date('Y'));
	$quarter = ((isset($_GET['q'])) ? str_clean($_GET['q']) : ceil(date('n') / 3));
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
			<br />
			Errors: <?= count($errors) ?>
		</h4>
		<?php
			if(count($errors) > 0){
		?>
				<br />
				<h4>
					Students for: Year <?= $year ?>, Quarter <?= $quarter ?>
				</h4>
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
							<th>
								Error
							</th>
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
												<input type='text' name='<?= $header ?>' value='<?= $error->$header ?>' />
											</td>
									<?php
										}
									?>
									<td>
										<input type='hidden' name='year' value='<?= $year ?>' />
										<input type='hidden' name='quarter' value='<?= $quarter ?>' />
										<?= $error->error ?>
									</td>
								</tr>
						<?php
							}
						?>
					</tbody>
				</table>
				<br />
				<input type='button' id='resubmit' value='Re-submit rows' />
		<?php
			}
		?>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
	<script>
		var dt = $("#filter_table").DataTable({
			/* Disable initial sort */
			"aaSorting": [],
			"paging": false
		});
		
		$("#resubmit").click(function(){
			let all_rows = {};
			let start = 1;
			
			dt.rows().nodes().each(function(row){
				let row_inputs = ($(row).find(":input").serializeArray());
				let row_array = {};
				
				row_inputs.forEach(function(pair){
					row_array[pair.name] = pair.value;
				});
				
				all_rows[start++] = row_array;
			});
			
			$("body").append($("<form/>", {
				id: "jquery_form",
				method: "POST",
				action: "exec_import.php"
			}));

			$("#jquery_form").append($("<input/>", {
				type: "hidden",
				name: "students",
				value: JSON.stringify(all_rows)
			}));
			
			$("#jquery_form").append($("<input/>", {
				type: "hidden",
				name: "year",
				value: <?= $year ?>
			}));
			
			$("#jquery_form").append($("<input/>", {
				type: "hidden",
				name: "quarter",
				value: <?= $quarter ?>
			}));
			
			$("#jquery_form").submit();
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>