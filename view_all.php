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
	
	//Ensure not student
	if($account->is_student()){
		header("location: home.php");
		exit();
	}
	
	if(isset($_GET['t'])){
		switch($_GET['t']){
			case "ft":
				$rows = get_all_accounts([1]);
				break;
			
			case "students":
				$rows = get_all_accounts([1, 2]);
				break;
			
			case "pt":
				$rows = get_all_accounts([2]);
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
			
			case "groups":
				$rows = get_all_groups();
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
		
		<script>
		function closeNav() {
		  document.getElementById("sidebar-wrapper").style.width = "0";
		}
		</script>
		
		<style>
			.container{
				margin-left: 20% !important;
			}

			#sidebar-wrapper {
				margin-top: -105px;
				z-index: 0;
				position: fixed;
				left: 250px;
				width: 0;
				height: 105%;
				margin-left: -250px;
				overflow-y: auto;
				background: #000;
				-webkit-transition: all 0.5s ease;
				-moz-transition: all 0.5s ease;
				-o-transition: all 0.5s ease;
				transition: all 0.5s ease;
			}

			#page-content-wrapper {
				width: 100%;
				position: absolute;
				padding: 15px;
			}
			/* Sidebar Styles */

			.sidebar-nav {
				position: absolute;
				top: 0;
				width: 250px;
				margin: 0;
				padding: 0;
				list-style: none;
			}
			.sidebar-nav li {
				text-indent: 20px;
				line-height: 40px;
			}
			.sidebar-nav li a {
				display: block;
				text-decoration: none;
				color: #999999;
			}
			.sidebar-nav li a:hover {
				text-decoration: none;
				color: #fff;
				background: rgba(255, 255, 255, 0.2);
			}
			.active-page{
				text-decoration: none;
				color: #fff;
				background: rgba(255, 255, 255, 0.2);
			}
			.sidebar-nav li a:active,
			.sidebar-nav li a:focus {
			   text-decoration: none;
			}
			.sidebar-nav > .sidebar-brand {
				height: 65px;
				font-size: 18px;
				line-height: 60px;
			}
			.sidebar-nav > .sidebar-brand a {
			    color: #999999;
			}
			.sidebar-nav > .sidebar-brand a:hover {
				color: #fff;
				background: none;
			}
			@media(min-width:768px) {
			#wrapper {
				padding-left: 250px;
			}
			#wrapper.toggled {
				padding-left: 0;
			}
			#sidebar-wrapper {
				width: 250px;
			}
			#wrapper.toggled #sidebar-wrapper {
				width: 0;
			}
			#page-content-wrapper {
				padding: 20px;
				position: relative;
			}
			#wrapper.toggled #page-content-wrapper {
				position: relative;
				margin-right: 0;
			}
			}
			
			.sidebar-nav .closebtn {
			  position: relative;
			  top: 0;
			  left: 0px;
			  font-size: 36px;
			  margin-left: 50px;
			}

		</style>
	</head>
	<body>
		
		<?php include("include/templates/header.php"); ?>

		<!-- Sidebar -->
		<div id="sidebar-wrapper">
			<ul class="sidebar-nav">
				<li class="sidebar-brand">
				<a href="#"></a>
				</li>
				<li class="sidebar-brand">
				<a href="#"></a>
				</li>
				
				<li>
				<a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
				</li>
				
				<li>
				<a href="#" class='menu' onClick="show_student_options();get_active();">Students</a>
				</li>
				<li>
				<a href="#" class='menu' onClick="get_accounts('faculty');get_active();">Faculty</a>
				</li>
				<li>
				<a href="#" class='menu' onClick="get_accounts('admin');get_active();">Admin</a>
				</li>
				<li>
				<a href="#" class='menu' onClick="get_accounts('majors');get_active();">Majors</a>
				</li>
				<li>
				<a href="#" class='menu' onClick="get_accounts('projects');get_active();">Projects</a>
				</li>
				<li>
				<a href="#" class='menu' onClick="get_accounts('groups');get_active();">Groups</a>
				</li>
				<hr>
				<div id='view_student' style='display:none;'>
					<li>
					<a href="#" onClick="get_accounts('students');">All</a>
					</li>
					<li>
					<a href="#" onClick="get_accounts('ft');">Full-Time</a>
					</li>
					<li>
					<a href="#" onClick="get_accounts('pt');">Part-Time</a>
					</li>
				</div>
			</ul>
		</div>
		<!-- <table id='view_all' class='basic_table container'>
			<tr>
				<td style='width:16%; text-align:center;' onClick="show_student_options();">
					Students
				</td>
				<td style='width:16%; text-align:center;' onClick="get_accounts('faculty');">
					Faculty
				</td>
				<td style='width:16%; text-align:center;' onClick="get_accounts('admin');">
					Admin
				</td>
				<td style='width:16%; text-align:center;' onClick="get_accounts('majors');">
					Majors
				</td>
				<td style='width:16%; text-align:center;' onClick="get_accounts('projects');">
					Projects
				</td>
				<td style='width:16%; text-align:center;' onClick="get_accounts('groups');">
					Groups
				</td>
			</tr>
		</table>
		<br />
		<table id='view_student' class='basic_table container' style='display:none;'>
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
		</table> -->
		<br />
		<div class='container'>
		<?php
			if(isset($rows)){
				if($_GET['t'] == "students" || $_GET['t'] == "pt" || $_GET['t'] == "ft"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_sim_id' class='search_checkbox' value='0' checked/> SIM ID</label>
					<label><input type='checkbox' id='search_uow_id' class='search_checkbox' value='1' checked/> UOW ID</label>
					<label><input type='checkbox' id='search_name' class='search_checkbox' value='2' /> Name</label>
					<label><input type='checkbox' id='search_sim_email' class='search_checkbox' value='3' /> SIM E-Mail</label>
					<label><input type='checkbox' id='search_personal_email' class='search_checkbox' value='4' /> Personal E-Mail</label>
					<label><input type='checkbox' id='search_phone' class='search_checkbox' value='5' /> Phone</label>
					<label><input type='checkbox' id='search_type' class='search_checkbox' value='6'/> Type</label>
					<label><input type='checkbox' id='search_major' class='search_checkbox' value='7' /> Major</label>
					<label><input type='checkbox' id='search_year' class='search_checkbox' value='8' /> Year</label>
					<label><input type='checkbox' id='search_quarter' class='search_checkbox' value='9' /> Quarter</label>
					<label><input type='checkbox' id='search_choices' class='search_checkbox' value='10' /> Choices</label>
					<br />
					<br />
					<table id='filter_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								SIM ID
							</td>
							<td style='text-align:center;'>
								UOW ID
							</td>
							<td style='text-align:center;'>
								Name
							</td>
							<td style='text-align:center;'>
								SIM Email
							</td>
							<td style='text-align:center;'>
								Personal Email
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
							<?php
								if($account->is_admin()){
							?>
									<td style='text-align:center;'>
										Actions
									</td>
							<?php
								}
							?>
						</tr>
						<?php
							foreach($rows as $student){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->sim_id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->uow_id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->get_name() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->get_sim_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->get_personal_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->get_phone() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= ($student->is_part_time() ? "Part-Time" : ($student->is_full_time() ? "Full-Time" : "")) ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->get_majors(true) ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->get_year() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										<?= $student->get_quarter() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $student->sim_id ?>');">
										
									</td>
									<?php
										if($account->is_admin()){
									?>
											<td style='text-align:center;'>
												<a href='edit_student.php?a=<?= $student->sim_id ?>'>
													[ Edit ]
												</a>
											</td>
									<?php
										}
									?>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "faculty"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_sim_id' class='search_checkbox' value='0' checked/> SIM ID</label>
					<label><input type='checkbox' id='search_uow_id' class='search_checkbox' value='1' checked/> UOW ID</label>
					<label><input type='checkbox' id='search_name' class='search_checkbox' value='2' /> Name</label>
					<label><input type='checkbox' id='search_sim_email' class='search_checkbox' value='3' /> SIM E-Mail</label>
					<label><input type='checkbox' id='search_personal_email' class='search_checkbox' value='4' /> Personal E-Mail</label>
					<label><input type='checkbox' id='search_phone' class='search_checkbox' value='5' /> Phone</label>
					<label><input type='checkbox' id='search_majors' class='search_checkbox' value='6' /> Majors</label>
					<br />
					<br />
					<table id='filter_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								SIM ID
							</td>
							<td style='text-align:center;'>
								UOW ID
							</td>
							<td style='text-align:center;'>
								Name
							</td>
							<td style='text-align:center;'>
								SIM Email
							</td>
							<td style='text-align:center;'>
								Personal Email
							</td>
							<td style='text-align:center;'>
								Phone
							</td>
							<td style='text-align:center;'>
								Majors
							</td>
							<?php
								if($account->is_admin()){
							?>
									<td style='text-align:center;'>
										Actions
									</td>
							<?php
								}
							?>
						</tr>
						<?php
							foreach($rows as $faculty){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
										<?= $faculty->sim_id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
										<?= $faculty->uow_id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
										<?= $faculty->get_name() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
										<?= $faculty->get_sim_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
										<?= $faculty->get_personal_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
										<?= $faculty->get_phone() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
										<?= $faculty->get_majors(true) ?>
									</td>
									<?php
										if($account->is_admin()){
									?>
											<td style='text-align:center;'>
												<a href='edit_faculty.php?a=<?= $faculty->sim_id ?>'>
													[ Edit ]
												</a>
											</td>
									<?php
										}
									?>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "admin"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_sim_id' class='search_checkbox' value='0' checked/> SIM ID</label>
					<label><input type='checkbox' id='search_uow_id' class='search_checkbox' value='1' checked/> UOW ID</label>
					<label><input type='checkbox' id='search_name' class='search_checkbox' value='2' /> Name</label>
					<label><input type='checkbox' id='search_sim_email' class='search_checkbox' value='3' /> SIM E-Mail</label>
					<label><input type='checkbox' id='search_personal_email' class='search_checkbox' value='4' /> Personal E-Mail</label>
					<label><input type='checkbox' id='search_phone' class='search_checkbox' value='5' /> Phone</label>
					<br />
					<br />
					<table id='filter_table' class='basic_table'>
						<tr>
							<td style='text-align:center;'>
								SIM ID
							</td>
							<td style='text-align:center;'>
								UOW ID
							</td>
							<td style='text-align:center;'>
								Name
							</td>
							<td style='text-align:center;'>
								SIM Email
							</td>
							<td style='text-align:center;'>
								Personal Email
							</td>
							<td style='text-align:center;'>
								Phone
							</td>
							<?php
								if($account->is_super()){
							?>
									<td style='text-align:center;'>
										Actions
									</td>
							<?php
								}
							?>
						</tr>
						<?php
							foreach($rows as $admin){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
										<?= $admin->sim_id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
										<?= $admin->uow_id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
										<?= $admin->get_name() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
										<?= $admin->get_sim_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
										<?= $admin->get_personal_email() ?>
									</td>
									<td style='text-align:center;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
										<?= $admin->get_phone() ?>
									</td>
									<?php
										if($account->is_super()){
									?>
											<td style='text-align:center;'>
												<a href='edit_admin.php?a=<?= $admin->sim_id ?>'>
													[ Edit ]
												</a>
											</td>
									<?php
										}
									?>
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
							<?php
								if($account->is_admin()){
							?>
									<td style='text-align:center;'>
										Actions
									</td>
							<?php
								}
							?>
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
									<?php
										if($account->is_admin()){
									?>
											<td style='text-align:center;'>
												<a href='edit_major.php?m=<?= $id ?>'>
													[ Edit ]
												</a>
											</td>
									<?php
										}
									?>
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
					<label><input type='checkbox' id='search_year' class='search_checkbox' value='4' /> Year</label>
					<label><input type='checkbox' id='search_quarter' class='search_checkbox' value='5' /> Quarter</label>
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
								Year
							</td>
							<td style='text-align:center;'>
								Quarter
							</td>
							<?php
								if($account->is_admin()){
							?>
									<td style='text-align:center;'>
										Actions
									</td>
							<?php
								}
							?>
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
										<?= $project['year'] ?>
									</td>
									<td style='text-align:center;' onClick="go_to('project', '<?= $id ?>');">
										<?= $project['quarter'] ?>
									</td>
									<?php
										if($account->is_admin()){
									?>
											<td style='text-align:center;'>
												<a href='edit_project.php?p=<?= $id ?>'>
													[ Edit ]
												</a>
											</td>
									<?php
										}
									?>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}elseif($_GET['t'] == "groups"){
		?>
					Search: <input type='text' name='view_all_filter' id='view_all_filter' placeholder='Search for ticket' /> <span id='cancel_search'>X</span>
					<label><input type='checkbox' id='search_id' class='search_checkbox' value='0' checked/> ID</label>
					<label><input type='checkbox' id='search_name' class='search_checkbox' value='1' /> Name</label>
					<label><input type='checkbox' id='search_supervisor' class='search_checkbox' value='2' /> Supervisor</label>
					<label><input type='checkbox' id='search_assessor' class='search_checkbox' value='3' /> Assessor</label>
					<label><input type='checkbox' id='search_members' class='search_checkbox' value='4' /> Members</label>
					<label><input type='checkbox' id='search_project' class='search_checkbox' value='5' /> Project</label>
					<label><input type='checkbox' id='search_deadline' class='search_checkbox' value='6' /> Deadline</label>
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
								Supervisor
							</td>
							<td style='text-align:center;'>
								Assessor
							</td>
							<td style='text-align:center;'>
								Members
							</td>
							<td style='text-align:center;'>
								Project
							</td>
							<td style='text-align:center;'>
								Deadline
							</td>
							<?php
								if($account->is_admin()){
							?>
									<td style='text-align:center;'>
										Actions
									</td>
							<?php
								}
							?>
						</tr>
						<?php
							foreach($rows as $id => $group){
						?>
								<tr>
									<td style='text-align:center;' onClick="go_to('group', '<?= $id ?>');">
										<?= $id ?>
									</td>
									<td style='text-align:center;' onClick="go_to('group', '<?= $id ?>');">
										<?= $group['name'] ?>
									</td>
									<td style='text-align:center;' onClick="go_to('group', '<?= $id ?>');">
										<?= (is_null($group['supervisor']) ? "" : "{$group['supervisor']->get_name()} ({$group['supervisor']->sim_id})") ?>
									</td>
									<td style='text-align:center;' onClick="go_to('group', '<?= $id ?>');">
										<?= (is_null($group['assessor']) ? "" : "{$group['assessor']->get_name()} ({$group['assessor']->sim_id})") ?>
									</td>
									<td style='text-align:left;' onClick="go_to('group', '<?= $id ?>');">
										<?php
											foreach($group['members'] as $member){
										?>
												<?= $member->get_name() ?> (<?= $member->sim_id ?>)
												<br />
										<?php
											}
										?>
									</td>
									<td style='text-align:left;' onClick="go_to('group', '<?= $id ?>');">
										<?= $group['project']['id'] ?> - <?= $group['project']['name'] ?>
									</td>
									<td style='text-align:center;' onClick="go_to('group', '<?= $id ?>');">
										<?= $group['deadline'] ?>
									</td>
									<?php
										if($account->is_admin()){
									?>
											<td style='text-align:center;'>
												<a href='edit_group.php?g=<?= $id ?>'>
													[ Edit ]
												</a>
											</td>
									<?php
										}
									?>
								</tr>
						<?php
							}
						?>
					</table>
		<?php
				}
			}
		?>
		</div>
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
			}else if(type == "group"){
				window.location.href = "view_group?g=" + id;
			}
		}
		
		$(".menu").click(function() {  
			$('.menu').removeClass('active-page');
			$(this).addClass("active-page");      
		});
	</script>
	<script src='include/js/view_filter.js'></script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>