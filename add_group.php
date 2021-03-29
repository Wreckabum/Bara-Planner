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
			
			case 1:
				$err = "Cannot find selected supervisor.";
				break;
			
			case 2:
				$err = "Cannot find selected assessor.";
				break;
			
			case 3:
				$err = "Cannot find selected project.";
				break;
			
			case 4:
				$err = "Cannot find at least one of the selected students.";
				break;
			
			case 5:
				$err = "Not all students are from the same semester.";
				break;
			
			case 6:
				$err = "Not all students are from the same type (FT/PT).";
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
		
		list($year, $quarter) = explode("_", $_GET['semester']);	
		
		$applicable_students = get_students($year, $quarter, $_GET['type'], true); //Get all students in semester that is not in a group
		$all_faculty = get_all_accounts([0]);
		$all_projects = get_all_projects($year, $quarter);
?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='UTF-8'>
				<title>Create a group</title>
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
				<div id='students_container' style='display:inline-block; width:49%; vertical-align:top;'>
					<table id='applicable_students' class='display connected_sortable' style='width:100%;'>
						<thead>
							<tr>
								<th style='background-image:none !important;'>
									Applicable Students
								</th>
								<?php
									foreach($all_projects as $project){
								?>
										<th style='width:30px; padding:0; text-align:center; background-image:none !important;'>
											<?= $project->id ?>
										</th>
								<?php
									}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
								foreach($applicable_students as $student){
									$selected_choices = $student->get_choices();
									$index = 0;
									$choice = 1;
							?>
									<tr id='student_<?= $student->sim_id ?>' style='cursor:move;'>
										<td style='padding-right:0;'>
											<?= $student->get_name() ?>
										</td>
										<?php
											foreach($all_projects as $project){
										?>
												<td style='width:30px; padding:0; text-align:center;'>
													<?php
														foreach($selected_choices as $rank => $id){
															if($id == $project->id){
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
						</tbody>
					</table>
				</div>
				<div id='group_container' style='display:inline-block; width:50%; vertical-align:top;'>
					<div id='group_inner_container'>
						<form id='add_group_form' action='exec_group.php' method='POST'>
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
										<select id='supervisor' name='supervisor' style='width:97%;' required>
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
										<select id='assessor' name='assessor' style='width:97%;' required>
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
										<select id='project' name='project' style='width:97%;' required>
											<?php
												foreach($all_projects as $project){
											?>
													<option value='<?= $project->proj_id ?>'>(<?= $project->id ?>) - <?= $project->get_name() ?></option>
											<?php
												}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<td colspan='2' style='width:5%; padding:5px; text-align:center; background-color:#D6EFFB;'>
										Members:
									</td>
								</tr>
								<tr>
									<td colspan='2' style='width:95%; padding:5px;'>
										<table id='group_members' class='connected_sortable' style='width:100%;'>
											<tr>
												<td>
													Members
												</td>
												<?php
													foreach($all_projects as $project){
														
												?>
														<td style='width:30px; padding:0; text-align:center;'>
															<?= $project->id ?>
														</td>
												<?php
													}
												?>
											</tr>
										</table>
										<br />
										<br />
									</td>
								</tr>
								<tr>
									<td colspan='2' style='padding:5px;'>
										<input type='hidden' name='semester' value='<?= $_GET['semester'] ?>'>
										<input type='hidden' name='type' value='<?= $_GET['type'] ?>'>
										<input type='submit' name='add' value='Add Group'>
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
				var dt = $("#applicable_students").DataTable({
					/* Disable initial sort */
					"aaSorting": []
				});
				
				//Enable group container scroll
				var original_height = $('#group_inner_container').offset().top;
				
				$(window).scroll(function(){
					if($(window).scrollTop() >= original_height){
						$('#group_inner_container').css('position', 'fixed').css('top', '0');
					}else if(original_height >= $(window).scrollTop()){
						$('#group_inner_container').css('position', '').css('top', '');
					}
				});
				
				//Enable drag/drop
				$(".connected_sortable")
					.sortable({
						disabled: false,
						items: "tr:not(:first, :contains('No data available in table'))",
						helper: "clone",
						connectWith: ".connected_sortable",
						receive : function(event, element){
							//If from group members
							if($($(element)[0]['sender'][0]).attr("id") == "group_members"){
								let row_data = [];
								
								$($(element)[0]['item'][0]).find("td")
									.each(function(idx, col){
										row_data.push($(col).text().trim())
									});
								
								dt.row.add(row_data).draw();
							}
						},
						update: function(event, element){
							//If from group members for end only
							if($(element)[0]['sender'] !== null){
								if($($(element)[0]['sender'][0]).attr("id") == "group_members"){
									$(element)[0]['item'][0].remove();
								}else{
									dt.row($($(element)[0]['item'][0])).remove().draw();
								}
							}
						}
					})
					.disableSelection();
				
				$("#add_group_form").submit(function(e){
					//Check faculty involved
					if($("#supervisor option:selected").text() == $("#assessor option:selected").text()){
						alert("Supervisor and Assessor should be different.");
						e.preventDefault();
					}
					
					$('#hidden_members').remove();
					$('#add_group_form').attr("action", "exec_group.php?" + $("#group_members").sortable().sortable("serialize"));
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