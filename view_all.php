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
	
	if(isset($_GET['t'])){
		switch($_GET['t']){
			case "students":
				$rows = get_all_accounts([1, 2]);
				break;
			
			case "pt":
				$rows = get_all_accounts([2]);
				break;
			
			case "ft":
				$rows = get_all_accounts([1]);
				break;
			
			case "faculty":
				$rows = get_all_accounts([0]);
				break;
			
			case "majors":
				$rows = get_all_majors();
				break;
			
			case "projects":
				$rows = get_all_projects();
				break;
			
			default:
				$rows = null;
				break;
		}
	}
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View All</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_all' class='basic_table'>
			<tr>
				<td style='width:25%; text-align:center;' onClick="show_student_options();">
					Students
				</td>
				<td style='width:25%; text-align:center;' onClick="get_accounts('faculty');">
					Faculty
				</td>
				<td style='width:25%; text-align:center;' onClick="get_accounts('majors');">
					Majors
				</td>
				<td style='width:25%; text-align:center;' onClick="get_accounts('projects');">
					Projects
				</td>
			</tr>
		</table>
		<br />
		<table id='view_student' class='basic_table' style='display:none;'>
			<tr>
				<td style='width:33%; text-align:center;' onClick="get_accounts('students');">
					All
				</td>
				<td style='width:33%; text-align:center;' onClick="get_accounts('pt');">
					Part-Time
				</td>
				<td style='width:33%; text-align:center;' onClick="get_accounts('ft');">
					Full-Time
				</td>
			</tr>
		</table>
		<br />
		<?php
			if(isset($rows)){
				if($_GET['t'] == "students" || $_GET['t'] == "pt" || $_GET['t'] == "ft"){
		?>
			
					<table id='students_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								ID
							</td>
							<td style='text-align:center;'>
								First Name
							</td>
							<td style='text-align:center;'>
								Last Name
							</td>
							<td style='text-align:center;'>
								Email
							</td>
							<td style='text-align:center;'>
								Phone
							</td>
							<td style='text-align:center;'>
								Type
							</td>
							<td style='text-align:center;'>
								Major
							</td>
							<td style='text-align:center;'>
								Year
							</td>
							<td style='text-align:center;'>
								Quarter
							</td>
							<td style='text-align:center;'>
								Choices
							</td>
						</tr>
						<?php
							foreach($rows as $student){
						?>
								<tr>
									<td style='text-align:center;'>
										<?= $student->id ?>
									</td>
									<td style='text-align:center;'>
										<?= $student->get_first_name() ?>
									</td>
									<td style='text-align:center;'>
										<?= $student->get_last_name() ?>
									</td>
									<td style='text-align:center;'>
										<?= $student->get_email() ?>
									</td>
									<td style='text-align:center;'>
										<?= $student->get_phone() ?>
									</td>
									<td style='text-align:center;'>
										<?= ($student->is_part_time() ? "Part-Time" : ($student->is_full_time() ? "Full-Time" : "")) ?>
									</td>
									<td style='text-align:center;'>
										<?= $student->get_majors(true) ?>
									</td>
									<td style='text-align:center;'>
										<?= $student->get_year() ?>
									</td>
									<td style='text-align:center;'>
										<?= $student->get_quarter() ?>
									</td>
									<td style='text-align:center;'>
										
									</td>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "faculty"){
		?>
				<table id='students_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								ID
							</td>
							<td style='text-align:center;'>
								First Name
							</td>
							<td style='text-align:center;'>
								Last Name
							</td>
							<td style='text-align:center;'>
								Email
							</td>
							<td style='text-align:center;'>
								Phone
							</td>
							<td style='text-align:center;'>
								Position
							</td>
							<td style='text-align:center;'>
								Experience
							</td>
							<td style='text-align:center;'>
								Majors
							</td>
						</tr>
						<?php
							foreach($rows as $faculty){
						?>
								<tr>
									<td style='text-align:center;'>
										<?= $faculty->id ?>
									</td>
									<td style='text-align:center;'>
										<?= $faculty->get_first_name() ?>
									</td>
									<td style='text-align:center;'>
										<?= $faculty->get_last_name() ?>
									</td>
									<td style='text-align:center;'>
										<?= $faculty->get_email() ?>
									</td>
									<td style='text-align:center;'>
										<?= $faculty->get_phone() ?>
									</td>
									<td style='text-align:center;'>
										<?= $faculty->get_position() ?>
									</td>
									<td style='text-align:center;'>
										<?= $faculty->get_experience() ?>
									</td>
									<td style='text-align:center;'>
										<?= $faculty->get_majors(true) ?>
									</td>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "majors"){
		?>
				<table id='students_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								ID
							</td>
							<td style='text-align:center;'>
								Name
							</td>
							<td style='text-align:center;'>
								Description
							</td>
							<td style='text-align:center;'>
								Part-Time
							</td>
							<td style='text-align:center;'>
								Full-Time
							</td>
						</tr>
						<?php
							foreach($rows as $id => $major){
						?>
								<tr>
									<td style='text-align:center;'>
										<?= $id ?>
									</td>
									<td style='text-align:center;'>
										<?= $major['name'] ?>
									</td>
									<td style='text-align:center;'>
										<?= nl2br($major['description']) ?>
									</td>
									<td style='text-align:center;'>
										<?= ($major['part_time'] ? "Yes" : "No") ?>
									</td>
									<td style='text-align:center;'>
										<?= ($major['full_time'] ? "Yes" : "No") ?>
									</td>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "projects"){
		?>
				<table id='students_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								ID
							</td>
							<td style='text-align:center;'>
								Project ID
							</td>
							<td style='text-align:center;'>
								Name
							</td>
							<td style='text-align:center;'>
								Description
							</td>
							<td style='text-align:center;'>
								Available for
							</td>
							<td style='text-align:center;'>
								Active
							</td>
						</tr>
						<?php
							foreach($rows as $id => $project){
						?>
								<tr>
									<td style='text-align:center;'>
										<?= $id ?>
									</td>
									<td style='text-align:center;'>
										<?= $project['proj_id'] ?>
									</td>
									<td style='text-align:center;'>
										<?= $project['name'] ?>
									</td>
									<td style='text-align:center;'>
										<?= nl2br($project['description']) ?>
									</td>
									<td style='text-align:center;'>
										<?= implode(", ", json_decode($project['available_for'])) ?>
									</td>
									<td style='text-align:center;'>
										<?= ($project['active'] ? "Yes" : "No") ?>
									</td>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}
			}
		?>
	</body>
	<script>
		function show_student_options(){
			$("#view_student").show();
		}
		
		function get_accounts(type){
			window.location.href = window.location.origin + window.location.pathname + "?t=" + type;
		}
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>