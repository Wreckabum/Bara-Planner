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
			case "students":
				$rows = get_all_accounts([1, 2]);
				break;
			
			case "ft":
				$rows = get_all_accounts([1]);
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
	
	if(isset($_GET['a'])){
		switch($_GET['a']){
			case "students":
				$rows = get_all_archived_accounts([1, 2]);
				break;
			
			case "ft":
				$rows = get_all_archived_accounts([1]);
				break;
			
			case "pt":
				$rows = get_all_archived_accounts([2]);
				break;
			
			case "projects":
				$rows = get_all_archived_projects();
				break;
			
			case "groups":
				$rows = get_all_archived_groups();
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
		<link rel='stylesheet' href='include/css/sidebar.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<script src='include/js/dataTables.min.js'></script>
		<script src='include/js/sidebar.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<div id='body_content'>
			<div id='sidebar_wrapper'>
				<div id='sidebar_content'>
					<ul id='sidebar_nav'>
						<br />
						<li>
							<a href='#' class='menu' onClick="toggle_active();">Active</a>
						</li>
						<div id='active_records' style='display:none; padding-left:20px;'>
							<li>
								<a href='#' class='menu' onClick="toggle_active_students();">Students</a>
							</li>
							<div id='active_students' style='display:none; padding-left:20px;'>
								<li>
									<a href='#' onClick="get_accounts('t', 'students');">All</a>
								</li>
								<li>
									<a href='#' onClick="get_accounts('t', 'ft');">Full-Time</a>
								</li>
								<li>
									<a href='#' onClick="get_accounts('t', 'pt');">Part-Time</a>
								</li>
							</div>
							<li>
								<a href='#' class='menu' onClick="get_accounts('t', 'faculty');">Faculty</a>
							</li>
							<li>
								<a href='#' class='menu' onClick="get_accounts('t', 'admin');">Admin</a>
							</li>
							<li>
								<a href='#' class='menu' onClick="get_accounts('t', 'majors');">Majors</a>
							</li>
							<li>
								<a href='#' class='menu' onClick="get_accounts('t', 'projects');">Projects</a>
							</li>
							<li>
								<a href='#' class='menu' onClick="get_accounts('t', 'groups');">Groups</a>
							</li>
						</div>
						<br />
						<li>
							<a href='#' class='menu' onClick="toggle_archive();">Archived</a>
						</li>
						<div id='archived_records' style='display:none; padding-left:20px;'>
							<li>
								<a href='#' class='menu' onClick="toggle_archived_students();">Students</a>
							</li>
							<div id='archived_students' style='display:none; padding-left:20px;'>
								<li>
									<a href='#' onClick="get_accounts('a', 'students');">All</a>
								</li>
								<li>
									<a href='#' onClick="get_accounts('a', 'ft');">Full-Time</a>
								</li>
								<li>
									<a href='#' onClick="get_accounts('a', 'pt');">Part-Time</a>
								</li>
							</div>
							<li>
								<a href='#' class='menu' onClick="get_accounts('a', 'projects');">Projects</a>
							</li>
							<li>
								<a href='#' class='menu' onClick="get_accounts('a', 'groups');">Groups</a>
							</li>
						</div>
						<li>
					</ul>
				</div>
				<div id='sidebar_toggle' onClick="toggle_sidebar();">
					<
				</div>
			</div>
			<div id='main_content'>
				<div id='inner_content'>
					<?php
						if(isset($rows)){
							if(isset($_GET['t'])){
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
													Q
												</th>
												<th style='text-align:center;'>
													Choices
												</th>
												<th style='text-align:center;'>
													Actions
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												$all_projects = get_all_projects();
												
												foreach($rows as $student){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $student->sim_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->uow_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_name() ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_sim_email() ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_personal_email() ?>
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
															<?php
																foreach($student->get_choices() as $key => $choice){
																	$proj_name = ((isset($all_projects[$choice])) ? $all_projects[$choice]->get_name() : "N/A");
																	
																	echo "#". ($key + 1) ." - {$proj_name}<br />";
																}
															?>
														</td>
														<td style='text-align:center;'>
															<a href='view_account.php?a=<?= $student->sim_id ?>'>
																[ View ]
															</a>
															<?php
																if($account->is_admin()){
															?>
																	<br />
																	<a href='edit_student.php?a=<?= $student->sim_id ?>'>
																		[ Edit ]
																	</a>
															<?php
																}
															?>
														</td>
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
												<th style='text-align:center;'>
													Actions
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach($rows as $faculty){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $faculty->sim_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $faculty->uow_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $faculty->get_name() ?>
														</td>
														<td style='text-align:center;'>
															<?= $faculty->get_sim_email() ?>
														</td>
														<td style='text-align:center;'>
															<?= $faculty->get_personal_email() ?>
														</td>
														<td style='text-align:center;'>
															<?= $faculty->get_phone() ?>
														</td>
														<td style='text-align:center;'>
															<?= $faculty->get_majors(true) ?>
														</td>
														<td style='text-align:center;'>
															<a href='view_account.php?a=<?= $faculty->sim_id ?>'>
																[ View ]
															</a>
															<?php
																if($account->is_admin()){
															?>
																	<br />
																	<a href='edit_faculty.php?a=<?= $faculty->sim_id ?>'>
																		[ Edit ]
																	</a>
															<?php
																}
															?>
														</td>
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
												<th style='text-align:center;'>
													Actions
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach($rows as $admin){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $admin->sim_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $admin->uow_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $admin->get_name() ?>
														</td>
														<td style='text-align:center;'>
															<?= $admin->get_sim_email() ?>
														</td>
														<td style='text-align:center;'>
															<?= $admin->get_personal_email() ?>
														</td>
														<td style='text-align:center;'>
															<?= $admin->get_phone() ?>
														</td>
														<td style='text-align:center;'>
																<a href='view_account.php?a=<?= $admin->sim_id ?>'>
																	[ View ]
																</a>
															<?php
																if($account->is_super()){
															?>
																	<br />
																	<a href='edit_admin.php?a=<?= $admin->sim_id ?>'>
																		[ Edit ]
																	</a>
															<?php
																}
															?>
														</td>
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
												<th style='text-align:center;'>
													Type
												</th>
												<th style='text-align:center;'>
													Actions
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach($rows as $major){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $major->id ?>
														</td>
														<td style='text-align:center;'>
															<?= $major->get_name() ?>
														</td>
														<td style='text-align:center;'>
															<?= nl2br($major->get_description()) ?>
														</td>
														<td style='text-align:center;'>
															<?= (($major->is_full_time()) ? "Full-time" : "Part-time") ?>
														</td>
														<td style='text-align:center;'>
																<a href='view_major.php?m=<?= $major->id ?>'>
																	[ View ]
																</a>
															<?php
																if($account->is_admin()){
															?>
																	<br />
																	<a href='edit_major.php?m=<?= $major->id ?>'>
																		[ Edit ]
																	</a>
															<?php
																}
															?>
														</td>
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
													Q
												</th>
												<th style='text-align:center;'>
													Actions
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach($rows as $project){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $project->id ?>
														</td>
														<td style='text-align:center;'>
															<?= $project->proj_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $project->get_name() ?>
														</td>
														<td style='text-align:center;'>
															<?= nl2br($project->get_description()) ?>
														</td>
														<td style='text-align:center;'>
															<?= $project->get_year() ?>
														</td>
														<td style='text-align:center;'>
															<?= $project->get_quarter() ?>
														</td>
														<td style='text-align:center;'>
																<a href='view_project.php?p=<?= $project->id ?>'>
																	[ View ]
																</a>
															<?php
																if($account->is_admin()){
															?>
																	<br />
																	<a href='edit_project.php?p=<?= $project->id ?>'>
																		[ Edit ]
																	</a>
															<?php
																}
															?>
														</td>
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
													Type
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
												<th style='text-align:center;'>
													Actions
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach($rows as $group){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $group->id ?>
														</td>
														<td style='text-align:center;'>
															<?= $group->get_name() ?>
														</td>
														<td>
															<?= (($group->get_type() == 1) ? "Full-Time" : "Part-Time") ?>
														</td>
														<td style='text-align:center;'>
															<?= (is_null($group->get_supervisor()) ? "" : "{$group->get_supervisor()->get_name()} ({$group->get_supervisor()->sim_id})") ?>
														</td>
														<td style='text-align:center;'>
															<?= (is_null($group->get_assessor()) ? "" : "{$group->get_assessor()->get_name()} ({$group->get_assessor()->sim_id})") ?>
														</td>
														<td style='text-align:left;'>
															<?php
																foreach($group->get_members() as $member){
															?>
																	<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
																	<br />
															<?php
																}
															?>
														</td>
														<td style='text-align:left;'>
															<?= (is_null($group->get_project()) ? "" : "({$group->get_project()->proj_id}) - {$group->get_project()->get_name()}") ?>
														</td>
														<td style='text-align:center;'>
																<a href='view_group.php?g=<?= $group->id ?>'>
																	[ View ]
																</a>
															<?php
																if($account->is_admin()){
															?>
																	<br />
																	<a href="edit_group.php?g=<?= $group->id ?>">
																		[ Edit ]
																	</a>
																	<br />
																	<a href="edit_group_multiple.php?semester=<?= $group->get_year() ?>_<?= $group->get_quarter() ?>&type=<?= $group->get_type() ?>#<?= $group->id ?>">
																		[ Edit All ]
																	</a>
															<?php
																}
															?>
														</td>
													</tr>
											<?php
												}
											?>
										</tbody>
									</table>
					<?php
								}
							}elseif(isset($_GET['a'])){
								if($_GET['a'] == "students" || $_GET['a'] == "pt" || $_GET['a'] == "ft"){
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
													Choices
												</th>
												<th style='text-align:center;'>
													Year
												</th>
												<th style='text-align:center;'>
													Q
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												$all_archived_projects = get_all_archived_projects();
												
												foreach($rows as $student){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $student->sim_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->uow_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_name() ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_sim_email() ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_personal_email() ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_phone() ?>
														</td>
														<td style='text-align:center;'>
															<?= ($student->is_part_time() ? "Part-Time" : ($student->is_full_time() ? "Full-Time" : "")) ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_major_name() ?>
														</td>
														<td style='text-align:center;'>
															<?php
																foreach($student->get_choices() as $key => $choice){
																	echo "#". ($key + 1) ." - ". ((isset($all_archived_projects[$choice])) ? $all_archived_projects[$choice]->get_name() : "N/A") ."<br />";
																}
															?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_year() ?>
														</td>
														<td style='text-align:center;'>
															<?= $student->get_quarter() ?>
														</td>
													</tr>
											<?php
												}
											?>
										</tbody>
									</table>
					<?php
								}elseif($_GET['a'] == "projects"){
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
													Q
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach($rows as $project){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $project->id ?>
														</td>
														<td style='text-align:center;'>
															<?= $project->proj_id ?>
														</td>
														<td style='text-align:center;'>
															<?= $project->get_name() ?>
														</td>
														<td style='text-align:center;'>
															<?= nl2br($project->get_description()) ?>
														</td>
														<td style='text-align:center;'>
															<?= $project->get_year() ?>
														</td>
														<td style='text-align:center;'>
															<?= $project->get_quarter() ?>
														</td>
													</tr>
											<?php
												}
											?>
										</tbody>
									</table>
					<?php
								}elseif($_GET['a'] == "groups"){
					?>
									<table id='filter_table' class='display'>
										<thead>
											<tr>
												<th style='text-align:center;'>
													Name
												</th>
												<th style='text-align:center;'>
													Type
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
												<th style='text-align:center;'>
													Year
												</th>
												<th style='text-align:center;'>
													Q
												</th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach($rows as $group){
											?>
													<tr>
														<td style='text-align:center;'>
															<?= $group->get_name() ?>
														</td>
														<td>
															<?= (($group->get_type() == 1) ? "Full-Time" : "Part-Time") ?>
														</td>
														<td style='text-align:center;'>
															<?= $group->get_supervisor() ?>
														</td>
														<td style='text-align:center;'>
															<?= $group->get_assessor() ?>
														</td>
														<td style='text-align:left;'>
															<?php
																foreach($group->get_members() as $member){
															?>
																	<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
																	<br />
															<?php
																}
															?>
														</td>
														<td style='text-align:left;'>
															(<?= $group->get_project()->proj_id ?>) - <?= $group->get_project()->get_name() ?>
														</td>
														<td style='text-align:center;'>
															<?= $group->get_year() ?>
														</td>
														<td style='text-align:center;'>
															<?= $group->get_quarter() ?>
														</td>
													</tr>
											<?php
												}
											?>
										</tbody>
									</table>
					<?php
								}
							}
						}
					?>
				</div>
			</div>
		</div>
	</body>
	<script>
		$("#filter_table").DataTable({
			/* Disable initial sort */
			"aaSorting": []
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>