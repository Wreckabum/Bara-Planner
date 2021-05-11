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
	
	$errors = ((isset($_POST['err'])) ? json_decode($_POST['err']) : []);
	$repeats = ((isset($_POST['rep'])) ? json_decode($_POST['rep']) : []);
	$headers = ((isset($_POST['h'])) ? json_decode($_POST['h']) : []);
	$type = (($_POST['t'] == "student") ? "student" : (($_POST['t'] == "faculty") ? "faculty" : "project"));
	$year = ((isset($_POST['y'])) ? str_clean($_POST['y']) : date('Y'));
	$quarter = ((isset($_POST['q'])) ? str_clean($_POST['q']) : ceil(date('n') / 3));
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
				Successfully added: <?= $_POST['c'] ?> <?= (($_POST['t'] == "student") ? "Students" : (($_POST['t'] == "faculty") ? "Faculty members" : "Projects")) ?>
			</h4>
		</span>
		<span style='float:right;'>
			<input type='button' id='toggle_repeats' value='Hide/Show students retaking FYP' />
		</span>
		<br />
		<?php
			if($type != "project" && count($repeats) > 0){
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
					<?php
						if($type == "student"){
					?>
							--> Students for: Year <?= $year ?>, Quarter <?= $quarter ?>
					<?php
						}elseif($type == "faculty"){
					?>
							--> Faculty members
					<?php
						}elseif($type == "project"){
					?>
							--> Projects for: Year <?= $year ?>, Quarter <?= $quarter ?>
					<?php
						}
					?>
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
			
			$.ajax({
				url: "exec_import.php",
				type: "POST",
				data: {
					import_data: JSON.stringify(all_rows), 
					type: '<?= $type ?>', 
					year: <?= $year ?>, 
					quarter: <?= $quarter ?>
				},
				success: function(data){
					data = JSON.parse(data);
					
					if(data['error']){
						if(typeof data['redirect'] === 'undefined'){
							$("#error_text").show().text(data['text']);
						}else{
							//Show error
							window.location.href = data['redirect'];
						}
					}else{
						$("body").append($("<form/>", {
							id: "jquery_form",
							method: "POST",
							action: data['redirect']
						}));

						$("#jquery_form").append($("<input/>", {
							type: "hidden",
							name: "c",
							value: data['c']
						}));
						
						$("#jquery_form").append($("<input/>", {
							type: "hidden",
							name: "err",
							value: JSON.stringify(data['err'])
						}));
						
						$("#jquery_form").append($("<input/>", {
							type: "hidden",
							name: "h",
							value: JSON.stringify(data['h'])
						}));
						
						$("#jquery_form").append($("<input/>", {
							type: "hidden",
							name: "y",
							value: data['y']
						}));
						
						$("#jquery_form").append($("<input/>", {
							type: "hidden",
							name: "q",
							value: data['q']
						}));
						
						$("#jquery_form").append($("<input/>", {
							type: "hidden",
							name: "rep",
							value: JSON.stringify(data['rep'])
						}));
						
						$("#jquery_form").append($("<input/>", {
							type: "hidden",
							name: "t",
							value: data['t']
						}));
						
						$("#jquery_form").submit();
					}
				},
				error: function(jqXHR,textStatus,errorThrown){
					console.log("Error with AJAX request.");
					//console.log(jqXHR); console.log(textStatus); console.log(errorThrown); //For testing
				}
			});
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>