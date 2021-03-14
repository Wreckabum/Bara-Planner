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
			
			default:
				$err = "";
				break;
		}
	}
	
	//Prompt for semester
	if(!isset($_GET['semester'])){
?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='UTF-8'>
				<title>Choose the year/quarter</title>
				<link rel='stylesheet' href='include/css/main.css'>
				<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
			</head>
			<body>
				<?php include("include/templates/header.php"); ?>
				<form action='' method='GET'>
					<table id='choose_semester' class='basic_table' style='width:250px;'>
						<tr>
							<td>
								Choose semester
							</td>
						</tr>
						<tr>
							<td>
								<select name='semester' style='width:97%;' required>
									<?php
										$available_semesters = get_semesters();
										
										foreach($available_semesters as $year => $quarter_array){
											foreach($quarter_array as $quarter){
									?>
												<option value='<?= $year ?>_<?= $quarter ?>'>Year <?= $year ?>, Quarter <?= $quarter ?></option>
									<?php
											}
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<input type='submit' value='Select'>
							</td>
						</tr>
					</table>
				</form>
			</body>
		</html>
<?php
	}else{
		//Semester selected
		list($year, $quarter) = explode("_", $_GET['semester']);	
		
		$applicable_students = get_students($year, $quarter);
		$all_faculty = get_all_accounts([0]);
		$all_projects = get_all_projects();
?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='UTF-8'>
				<title>Choose preferred projects</title>
				<link rel='stylesheet' href='include/css/main.css'>
				<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
				<script src='include/js/jquery-light-v3.5.1.js'></script>
			</head>
			<body>
				<?php include("include/templates/header.php"); ?>
				<center>
					<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
				</center>
				<div id='students_container' style='display:inline-block; width:60%; vertical-align:top;'>
					<table id='students' class='basic_table' style='width:100%;'>
						<tr>
							<td>
								Applicable Students
							</td>
							<?php
								for($i = 1; $i <= count($all_projects); $i++){
									
							?>
									<td style='width:30px padding:0; text-align:center;'>
										<?= $i ?>
									</td>
							<?php
								}
							?>
						</tr>
						<?php
							foreach($applicable_students as $student){
								$selected_choices = $student->get_choices();
								$index = 0;
								$choice = 1;
						?>
								<tr>
									<td style='padding-right:0;'>
										<?= $student->get_name() ?>
									</td>
									<?php
										for($i = 1; $i <= count($all_projects); $i++){
									?>
											<td style='width:30px padding:0; text-align:center;'>
												<?php
													foreach($selected_choices as $rank => $id){
														if($id == $i){
												?>
															<?= $rank + 1 ?>
												<?php
														}
													}
												?>
											</td>
									<?php
										}
									?>
								</tr>
						<?php
							}
						?>
					</table>
				</div>
				<div id='group_container' style='display:inline-block; width:39%; vertical-align:top;'>
					<div id='group_inner_container'>
						<form action='exec_group.php' method='POST'>
							<table id='add_group' class='basic_table' style='width:100%;'>
								<tr>
									<td colspan='2'>
										Group Details
									</td>
								</tr>
								<tr>
									<td style='width:5%; padding:5px; text-align:center;'>
										Name:
									</td>
									<td style='width:95%; padding:5px;'>
										<input type='text' name='name' placeholder='FYP-99-S01' maxlength='32' style='width:97%;' required />
									</td>
								</tr>
								<tr>
									<td style='width:5%; padding:5px; text-align:center;'>
										Supervisor:
									</td>
									<td style='width:95%; padding:5px;'>
										<select name='supervisor' style='width:97%;' required>
											<?php
												foreach($all_faculty as $supervisor){
											?>
													<option value='<?= $supervisor->sim_id ?>'><?= $supervisor->get_name() ?></option>
											<?php
												}
											?>
										</select>
									</td>
								</tr><tr>
									<td style='width:5%; padding:5px; text-align:center;'>
										Assessor:
									</td>
									<td style='width:95%; padding:5px;'>
										<select name='assessor' style='width:97%;' required>
											<?php
												foreach($all_faculty as $assessor){
											?>
													<option value='<?= $assessor->sim_id ?>'><?= $assessor->get_name() ?></option>
											<?php
												}
											?>
										</select>
									</td>
								</tr><tr>
									<td style='width:5%; padding:5px; text-align:center;'>
										Project:
									</td>
									<td style='width:95%; padding:5px;'>
										<select name='assessor' style='width:97%;' required>
											<?php
												foreach($all_projects as $id => $details){
											?>
													<option value='<?= $id ?>'>(<?= $id ?>) - <?= $details['name'] ?></option>
											<?php
												}
											?>
										</select>
									</td>
								</tr><tr>
									<td colspan='2' style='width:5%; padding:5px; text-align:center; background-color:#D6EFFB;'>
										Members:
									</td>
								</tr>
								<tr>
									<td colspan='2' style='width:95%; padding:5px;'>
										<table style='width:100%; height:150px;'>
										
										</table>
									</td>
								</tr>
								<tr>
									<td colspan='2' style='padding:5px;'>
										<input type='submit' name='add_group' value='Add Group'>
									</td>
								</tr>
							</table>
						</form>
					</div>
				</div>
				<br />
				<a href='home.php'>Back to main page</a>
			</body>
			<script>
				//Enable choices container scroll
				var original_height = $('#group_inner_container').offset().top;
				
				$(window).scroll(function(){
					if($(window).scrollTop() >= original_height){
						$('#group_inner_container').css('position', 'fixed').css('top', '0');
					}else if(original_height >= $(window).scrollTop()){
						$('#group_inner_container').css('position', '').css('top', '');
					}
				});
			</script>
		</html>
<?php
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>