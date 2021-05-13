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
	$year = ((isset($_GET['y'])) ? str_clean($_GET['y']) : date('Y'));
	$quarter = ((isset($_GET['q'])) ? str_clean($_GET['q']) : ceil(date('n') / 3));
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Result of E-Mail</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='stylesheet' href='include/css/dataTables.min.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<script src='include/js/dataTables.min.js'></script>
	</head>
	<body>
		<?php include('include/templates/header.php'); ?>
		<h4>
			Successfully E-Mailed: <?= $_GET['c'] ?> Students
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
							<th style='text-align:center;'>
								SIM ID
							</th>
							<th style='text-align:center;'>
								UOW ID
							</th>
							<th style='text-align:center;'>
								Name
							</th>
							<th style='text-align:center;'>
								SIM Email
							</th>
							<th style='text-align:center;'>
								Personal Email
							</th>
							<th style='text-align:center;'>
								Phone
							</th>
							<th style='text-align:center;'>
								Major
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach($errors as $error){
								$student = get_account($error);
						?>
								<tr>
									<td style='padding-right:0;'>
										<?= $student->sim_id ?>
										<input type='hidden' name='sim_id' value='<?= $student->sim_id ?>'/>
									</td>
									<td style='padding-right:0;'>
										<?= $student->uow_id ?>
									</td>
									<td style='padding-right:0;'>
										<?= $student->get_name() ?>
									</td>
									<td style='padding-right:0;'>
										<?= $student->get_sim_email() ?>
									</td>
									<td style='padding-right:0;'>
										<?= $student->get_personal_email() ?>
									</td>
									<td style='padding-right:0;'>
										<?= $student->get_phone() ?>
									</td>
									<td style='padding-right:0;'>
										<?= $student->get_majors() ?>
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
				action: "exec_email.php"
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