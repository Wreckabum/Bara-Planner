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
	$repeats = ((isset($_GET['rep'])) ? json_decode($_GET['rep']) : []);
	$headers = ((isset($_GET['h'])) ? json_decode($_GET['h']) : []);
	$type = (($_GET['t'] == "student") ? "student" : "faculty");
	$year = ((isset($_GET['y'])) ? str_clean($_GET['y']) : date('Y'));
	$quarter = ((isset($_GET['q'])) ? str_clean($_GET['q']) : ceil(date('n') / 3));
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Result of import</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='stylesheet' href='include/css/dataTables.min.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<script src='include/js/dataTables.min.js'></script>
	</head>
	<body>
		<?php include('include/templates/header.php'); ?>		
		<span style='float:left;'>
			<h4>
				Successfully added: <?= $_GET['c'] ?> <?= (($_GET['t'] == "student") ? "Students" : "Faculty members") ?>
			</h4>
		</span>
		<span style='float:right;'>
			<input type='button' id='toggle_repeats' value='Hide/Show students retaking FYP' />
		</span>
		<br />
		<?php
			if(count($repeats) > 0){
		?>
				<div id='repeats_container'>
					<hr />
					<hr />
					<h4>
						Students retaking FYP: <?= count($repeats) ?>
					</h4>
					<table id='repeats' class='display'>
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
								foreach($repeats as $repeat){
							?>
									<tr>
										<?php
											foreach($headers as $header){
										?>
												<td>
													<?= $repeat->$header ?>
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
				</div>
		<?php
			}
			
			if(count($errors) > 0){
		?>
				<hr />
				<hr />
				<h4>
					Errors: <?= count($errors) ?>
					<br />
					--> Students for: Year <?= $year ?>, Quarter <?= $quarter ?>
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
										<input type='hidden' name='type' value='<?= $type ?>' />
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
		var repeats = $("#repeats").DataTable({
			/* Disable initial sort */
			"aaSorting": [],
			"paging": false
		});
		
		var dt = $("#filter_table").DataTable({
			/* Disable initial sort */
			"aaSorting": [],
			"paging": false
		});
		
		$("#toggle_repeats").click(function(){
			$("#repeats_container").toggle();
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
				name: "import_data",
				value: JSON.stringify(all_rows)
			}));
			
			$("#jquery_form").append($("<input/>", {
				type: "hidden",
				name: "type",
				value: "<?= $type ?>"
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