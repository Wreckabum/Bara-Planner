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
	if(!isset($_GET['semester']) || !isset($_GET['type'])){
?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='UTF-8'>
				<title>Choose the year/quarter and type</title>
				<link rel='stylesheet' href='include/css/main.css' />
				<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
			</head>
			<body>
				<?php include("include/templates/header.php"); ?>
				<form action='' method='GET'>
					<table id='choose_semester' class='basic_table' style='width:400px;'>
						<tr>
							<td colspan='2'>
								Choose semester and type
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
							<td>
								<select name='type' style='width:97%;' required>
									<option value='0'>Both</option>
									<option value='1'>Full-time</option>
									<option value='2'>Part-time</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan='2'>
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
		str_clean($_GET['semester']);
		str_clean($_GET['type']);
		
		if($_GET['type'] == 0){
			$type = [1, 2];
		}elseif($_GET['type'] == 1){
			$type = 1;
		}elseif($_GET['type'] == 2){
			$type = 2;
		}
		
		list($year, $quarter) = explode("_", $_GET['semester']);
		
		//Update any potentially missing deadlines
		add_missing_deadlines();
		
		//Check if deadline exists
		$deadline = get_deadline($year, $quarter);
		
		//If no deadline exists, redirect
		if(is_null($deadline)){
			header("location: view_semester.php?err=1");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
		
		//If deadline has passed
		if((strtotime(date("Y-m-d")) - strtotime($deadline)) > 0){
			header("location: view_semester.php?err=2");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
		
		$students = get_students($year, $quarter, $type); //Get all students in semester that is not in a group
?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='UTF-8'>
				<title>Review students to E-Mail</title>
				<link rel='stylesheet' href='include/css/main.css' />
				<link rel='stylesheet' href='include/css/dataTables.min.css' />
				<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
				<script src='include/js/jquery-light-v3.5.1.js'></script>
				<script src='include/js/jquery-ui.min.js'></script>				
				<script src='include/js/dataTables.min.js'></script>
			</head>
			<body>
				<?php include("include/templates/header.php"); ?>
				<center>
					<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
				</center>
				<h4>
					Students for: Year <?= $year ?>, Quarter <?= $quarter ?>
				</h4>
				<table id='students' class='display connected_sortable' style='width:100%;'>
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
							foreach($students as $student){
						?>
								<tr id='student_<?= $student->sim_id ?>' style='cursor:move;'>
									<td style='padding-right:0;'>
										<?= $student->sim_id ?>
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
									</td>
								</tr>
						<?php
							}
						?>
					</tbody>
				</table>
				<br />
				<form action='exec_email.php' method='POST'>
					<label><input type='checkbox' id='confirm_details' value='0' required/> I have checked and confirmed the students to be E-Mailed with their new passwords.</label>
					<br />
					<label><input type='checkbox' id='confirm_password' value='0' required/> I understand that passwords for ALL of the shown accounts will be reset.</label>
					<br />
					<input type='hidden' name='students' value='<?= json_encode($students) ?>'/>
					<input type='hidden' name='year' value='<?= $year ?>'/>
					<input type='hidden' name='quarter' value='<?= $quarter ?>'/>
					<input type='submit' name='email' value='E-Mail Students'>
				</form>
				<br />
				<a href='home.php'>Back to main page</a>
			</body>
			<script>
				var dt = $("#students").DataTable({
					/* Disable initial sort */
					"aaSorting": []
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