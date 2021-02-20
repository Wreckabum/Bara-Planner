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
			
			case "admin":
				$rows = get_all_accounts([8, 9]);
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
				<td style='width:20%; text-align:center;' onClick="show_student_options();">
					Students
				</td>
				<td style='width:20%; text-align:center;' onClick="get_accounts('faculty');">
					Faculty
				</td>
				<td style='width:20%; text-align:center;' onClick="get_accounts('admin');">
					Admin
				</td>
				<td style='width:20%; text-align:center;' onClick="get_accounts('majors');">
					Majors
				</td>
				<td style='width:20%; text-align:center;' onClick="get_accounts('projects');">
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
				<td style='width:33%; text-align:center;' onClick="get_accounts('ft');">
					Full-Time
				</td>
				<td style='width:33%; text-align:center;' onClick="get_accounts('pt');">
					Part-Time
				</td>
			</tr>
		</table>
		<br />
		<?php
			if(isset($rows)){
				if($_GET['t'] == "students" || $_GET['t'] == "pt" || $_GET['t'] == "ft"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_id' class='search_checkbox' value='0' checked/> ID</label>
					<label><input type='checkbox' id='search_name' class='search_checkbox' value='1' /> Name</label>
					<label><input type='checkbox' id='search_email' class='search_checkbox' value='2' /> E-Mail</label>
					<label><input type='checkbox' id='search_phone' class='search_checkbox' value='3' /> Phone</label>
					<label><input type='checkbox' id='search_type' class='search_checkbox' value='4'/> Type</label>
					<label><input type='checkbox' id='search_major' class='search_checkbox' value='5' /> Major</label>
					<label><input type='checkbox' id='search_year' class='search_checkbox' value='6' /> Year</label>
					<label><input type='checkbox' id='search_quarter' class='search_checkbox' value='7' /> Quarter</label>
					<label><input type='checkbox' id='search_choices' class='search_checkbox' value='8' /> Choices</label>
					<br />
					<br />
					<table id='filter_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								ID
							</td>
							<td style='text-align:center;'>
								Name
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
							<td style='text-align:center;'>
								Actions
							</td>
						</tr>
						<?php
							foreach($rows as $student){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										<?= $student->id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										<?= $student->get_name() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										<?= $student->get_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										<?= $student->get_phone() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										<?= ($student->is_part_time() ? "Part-Time" : ($student->is_full_time() ? "Full-Time" : "")) ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										<?= $student->get_majors(true) ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										<?= $student->get_year() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										<?= $student->get_quarter() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->id ?>');">
										
									</td>
									<td style='text-align:center;'>
										<a href='edit_student.php?a=<?= $student->id ?>'>
											[ Edit ]
										</a>
									</td>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "faculty"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_id' class='search_checkbox' value='0' checked/> ID</label>
					<label><input type='checkbox' id='search_name' class='search_checkbox' value='1' /> Name</label>
					<label><input type='checkbox' id='search_email' class='search_checkbox' value='2' /> E-Mail</label>
					<label><input type='checkbox' id='search_phone' class='search_checkbox' value='3' /> Phone</label>
					<label><input type='checkbox' id='search_majors' class='search_checkbox' value='4' /> Majors</label>
					<br />
					<br />
					<table id='filter_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								ID
							</td>
							<td style='text-align:center;'>
								Name
							</td>
							<td style='text-align:center;'>
								Email
							</td>
							<td style='text-align:center;'>
								Phone
							</td>
							<td style='text-align:center;'>
								Majors
							</td>
							<td style='text-align:center;'>
								Actions
							</td>
						</tr>
						<?php
							foreach($rows as $faculty){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->get_name() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->get_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->get_phone() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->get_majors(true) ?>
									</td>
									<td style='text-align:center;'>
										<a href='edit_faculty.php?a=<?= $faculty->id ?>'>
											[ Edit ]
										</a>
									</td>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "admin"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_id' class='search_checkbox' value='0' checked/> ID</label>
					<label><input type='checkbox' id='search_name' class='search_checkbox' value='1' /> Name</label>
					<label><input type='checkbox' id='search_email' class='search_checkbox' value='2' /> E-Mail</label>
					<label><input type='checkbox' id='search_phone' class='search_checkbox' value='3' /> Phone</label>
					<br />
					<br />
					<table id='filter_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								ID
							</td>
							<td style='text-align:center;'>
								Name
							</td>
							<td style='text-align:center;'>
								Email
							</td>
							<td style='text-align:center;'>
								Phone
							</td>
						</tr>
						<?php
							foreach($rows as $faculty){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->get_name() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->get_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->id ?>');">
										<?= $faculty->get_phone() ?>
									</td>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "majors"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_id' class='search_checkbox' value='0' checked/> ID</label>
					<label><input type='checkbox' id='search_name' class='search_checkbox' value='1' /> Name</label>
					<label><input type='checkbox' id='search_description' class='search_checkbox' value='2' /> Description</label>
					<label><input type='checkbox' id='search_full_time' class='search_checkbox' value='3' /> Full-Time</label>
					<label><input type='checkbox' id='search_part_time' class='search_checkbox' value='4' /> Part-Time</label>
					<br />
					<br />
					<table id='filter_table' class='basic_table'>
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
								Full-Time
							</td>
							<td style='text-align:center;'>
								Part-Time
							</td>
							<td style='text-align:center;'>
								Actions
							</td>
						</tr>
						<?php
							foreach($rows as $id => $major){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('major', '<?= $id ?>');">
										<?= $id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('major', '<?= $id ?>');">
										<?= $major['name'] ?>
									</td>
									<td style='text-align:center;' onClick="go_to('major', '<?= $id ?>');">
										<?= nl2br($major['description']) ?>
									</td>
									<td style='text-align:center;' onClick="go_to('major', '<?= $id ?>');">
										<?= ($major['full_time'] ? "Yes" : "No") ?>
									</td>
									<td style='text-align:center;' onClick="go_to('major', '<?= $id ?>');">
										<?= ($major['part_time'] ? "Yes" : "No") ?>
									</td>
									<td style='text-align:center;'>
										<a href='edit_major.php?m=<?= $id ?>'>
											[ Edit ]
										</a>
									</td>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "projects"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_id' class='search_checkbox' value='0' checked/> ID</label>
					<label><input type='checkbox' id='search_proj_id' class='search_checkbox' value='1' /> Project ID</label>
					<label><input type='checkbox' id='search_first_name' class='search_checkbox' value='2' /> Name</label>
					<label><input type='checkbox' id='search_last_name' class='search_description' value='3' /> Description</label>
					<label><input type='checkbox' id='search_available_for' class='search_checkbox' value='4' /> Available for</label>
					<label><input type='checkbox' id='search_year' class='search_checkbox' value='5' /> Year</label>
					<label><input type='checkbox' id='search_quarter' class='search_checkbox' value='6' /> Quarter</label>
					<table id='filter_table' class='basic_table'>
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
								Year
							</td>
							<td style='text-align:center;'>
								Quarter
							</td>
							<td style='text-align:center;'>
								Actions
							</td>
						</tr>
						<?php
							foreach($rows as $id => $project){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('project', '<?= $id ?>');">
										<?= $id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('project', '<?= $id ?>');">
										<?= $project['proj_id'] ?>
									</td>
									<td style='text-align:center;' onClick="go_to('project', '<?= $id ?>');">
										<?= $project['name'] ?>
									</td>
									<td style='text-align:center;' onClick="go_to('project', '<?= $id ?>');">
										<?= nl2br($project['description']) ?>
									</td>
									<td style='text-align:center;' onClick="go_to('project', '<?= $id ?>');">
										<?= implode(", ", json_decode($project['available_for'])) ?>
									</td>
									<td style='text-align:center;' onClick="go_to('project', '<?= $id ?>');">
										<?= $project['year'] ?>
									</td>
									<td style='text-align:center;' onClick="go_to('project', '<?= $id ?>');">
										<?= $project['quarter'] ?>
									</td>
									<td style='text-align:center;'>
										<a href='edit_project.php?p=<?= $id ?>'>
											[ Edit ]
										</a>
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
		<table id='no_records' class='basic_table' style='width:60%; display:none;'>
			<tr>
				<td style="text-align:center; background-color:#C9E0EF;">
					:: No Matching tickets ::
				</td>
			</tr>
		</table>
	</body>
	<script>
		function show_student_options(){
			$("#view_student").show();
		}
		
		function get_accounts(type){
			window.location.href = window.location.origin + window.location.pathname + "?t=" + type;
		}
		
		function go_to(type, id){
			if(type == "account"){
				window.location.href = "view_account?a=" + id;
			}else if(type == "major"){
				window.location.href = "view_major?m=" + id;
			}else if(type == "project"){
				window.location.href = "view_project?p=" + id;
			}
		}
	</script>
	<script src='include/js/view_filter.js'></script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>