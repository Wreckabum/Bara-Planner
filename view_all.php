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
		@mysqli_close($GLOBALS['mysql_link']);
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
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='stylesheet' href='include/css/dataTables.min.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>		
		<script src='include/js/dataTables.min.js'></script>
		<script>
			function closeNav() {
				document.getElementById("sidebar-wrapper").style.width = "0";
			}
		</script>
		<style>
			.container{
				margin-left: 250px !important;
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
				<hr />
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
		<br />
		<div class='container' style='max-width:inherit; width:auto;'>
			<?php
				if(isset($rows)){
					if($_GET['t'] == "students" || $_GET['t'] == "pt" || $_GET['t'] == "ft"){
			?>
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
										Type
									</th>
									<th style='text-align:center;'>
										Major
									</th>
									<th style='text-align:center;'>
										Year
									</th>
									<th style='text-align:center;'>
										Quarter
									</th>
									<th style='text-align:center;'>
										Choices
									</th>
									<?php
										if($account->is_admin()){
									?>
											<th style='text-align:center;'>
												Actions
											</th>
									<?php
										}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach($rows as $student){
								?>
										<tr>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->sim_id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->uow_id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->get_name() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->get_sim_email() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->get_personal_email() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->get_phone() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= ($student->is_part_time() ? "Part-Time" : ($student->is_full_time() ? "Full-Time" : "")) ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->get_majors(true) ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->get_year() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												<?= $student->get_quarter() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $student->sim_id ?>');">
												
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
							</tbody>
						</table>
			<?php
					}elseif($_GET['t'] == "faculty"){
			?>
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
										Majors
									</th>
									<?php
										if($account->is_admin()){
									?>
											<th style='text-align:center;'>
												Actions
											</th>
									<?php
										}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach($rows as $faculty){
								?>
										<tr>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
												<?= $faculty->sim_id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
												<?= $faculty->uow_id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
												<?= $faculty->get_name() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
												<?= $faculty->get_sim_email() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
												<?= $faculty->get_personal_email() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
												<?= $faculty->get_phone() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $faculty->sim_id ?>');">
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
							</tbody>
						</table>
			<?php
					}elseif($_GET['t'] == "admin"){
			?>
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
									<?php
										if($account->is_super()){
									?>
											<th style='text-align:center;'>
												Actions
											</th>
									<?php
										}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach($rows as $admin){
								?>
										<tr>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
												<?= $admin->sim_id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
												<?= $admin->uow_id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
												<?= $admin->get_name() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
												<?= $admin->get_sim_email() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
												<?= $admin->get_personal_email() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('account', '<?= $admin->sim_id ?>');">
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
							</tbody>
						</table>
			<?php
					}elseif($_GET['t'] == "majors"){
			?>
						<table id='filter_table' class='display'>
							<thead>
								<tr>
									<th style='text-align:center;'>
										ID
									</th>
									<th style='text-align:center;'>
										Name
									</th>
									<th style='text-align:center;'>
										Description
									</th>
									<?php
										if($account->is_admin()){
									?>
											<th style='text-align:center;'>
												Actions
											</th>
									<?php
										}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach($rows as $major){
								?>
										<tr>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('major', '<?= $major->id ?>');">
												<?= $major->id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('major', '<?= $major->id ?>');">
												<?= $major->get_name() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('major', '<?= $major->id ?>');">
												<?= nl2br($major->get_description()) ?>
											</td>
											<?php
												if($account->is_admin()){
											?>
													<td style='text-align:center;'>
														<a href='edit_major.php?m=<?= $major->id ?>'>
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
							</tbody>
						</table>
			<?php
					}elseif($_GET['t'] == "projects"){
			?>
						<table id='filter_table' class='display'>
							<thead>
								<tr>
									<th style='text-align:center;'>
										ID
									</th>
									<th style='text-align:center;'>
										Project ID
									</th>
									<th style='text-align:center;'>
										Name
									</th>
									<th style='text-align:center;'>
										Description
									</th>
									<th style='text-align:center;'>
										Year
									</th>
									<th style='text-align:center;'>
										Quarter
									</th>
									<?php
										if($account->is_admin()){
									?>
											<th style='text-align:center;'>
												Actions
											</th>
									<?php
										}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach($rows as $project){
								?>
										<tr>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('project', '<?= $project->id ?>');">
												<?= $project->id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('project', '<?= $project->id ?>');">
												<?= $project->proj_id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('project', '<?= $project->id ?>');">
												<?= $project->get_name() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('project', '<?= $project->id ?>');">
												<?= nl2br($project->get_description()) ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('project', '<?= $project->id ?>');">
												<?= $project->get_year() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('project', '<?= $project->id ?>');">
												<?= $project->get_quarter() ?>
											</td>
											<?php
												if($account->is_admin()){
											?>
													<td style='text-align:center;'>
														<a href='edit_project.php?p=<?= $project->id ?>'>
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
							</tbody>
						</table>
			<?php
					}elseif($_GET['t'] == "groups"){
			?>
						<table id='filter_table' class='display'>
							<thead>
								<tr>
									<th style='text-align:center;'>
										ID
									</th>
									<th style='text-align:center;'>
										Name
									</th>
									<th style='text-align:center;'>
										Supervisor
									</th>
									<th style='text-align:center;'>
										Assessor
									</th>
									<th style='text-align:center;'>
										Members
									</th>
									<th style='text-align:center;'>
										Project
									</th>
									<?php
										if($account->is_admin()){
									?>
											<th style='text-align:center;'>
												Actions
											</th>
									<?php
										}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach($rows as $group){
								?>
										<tr>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('group', '<?= $group->id ?>');">
												<?= $group->id ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('group', '<?= $group->id ?>');">
												<?= $group->get_name() ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('group', '<?= $group->id ?>');">
												<?= (is_null($group->get_supervisor()) ? "" : "{$group->get_supervisor()->get_name()} ({$group->get_supervisor()->sim_id})") ?>
											</td>
											<td style='text-align:center; cursor:pointer;' onClick="go_to('group', '<?= $group->id ?>');">
												<?= (is_null($group->get_assessor()) ? "" : "{$group->get_assessor()->get_name()} ({$group->get_assessor()->sim_id})") ?>
											</td>
											<td style='text-align:left;' onClick="go_to('group', '<?= $group->id ?>');">
												<?php
													foreach($group->get_members() as $member){
												?>
														<?= $member->get_name() ?> (<?= $member->sim_id ?>)
														<br />
												<?php
													}
												?>
											</td>
											<td style='text-align:left;' onClick="go_to('group', '<?= $group->id ?>');">
												<?= $group->get_project()->id ?> - <?= $group->get_project()->get_name() ?>
											</td>
											<?php
												if($account->is_admin()){
											?>
													<td style='text-align:center;'>
														<a href='edit_group.php?g=<?= $group->id ?>'>
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
							</tbody>
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
		
		$("#filter_table").DataTable({
			/* Disable initial sort */
			"aaSorting": []
		});
	</script>
	<script src='include/js/view_filter.js'></script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>