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
			
			case 7:
				$err = "Group must contain at least 1 student.";
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
				<script src='include/js/jquery-light-v3.5.1.js'></script>
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
										$available_semesters = get_all_semesters_quarters();
										
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
								<label><input id='automate' type='checkbox' name='automate' /> Automate grouping</label>
								<br />
								<div id='min_max' style='display:none;'>
									Min: <input type='number' name='min' value='5' min='1' style='width:50px; text-align:center;' />
									Max: <input type='number' name='max' value='7' min='1' style='width:50px; text-align:center;' />
								</div>
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
			<script>
				$("#automate").change(function(){
					$("#min_max").toggle();
				});
			</script>
		</html>
<?php
	}else{
		//Semester selected
		str_clean($_GET['semester']);
		str_clean($_GET['type']);
		str_clean($_GET['min']);
		str_clean($_GET['max']);
		
		list($year, $quarter) = explode("_", $_GET['semester']);	
		
		$applicable_students = get_students($year, $quarter, $_GET['type'], true); //Get all students in semester that is not in a group
		$all_faculty = get_all_accounts([0]);
		$all_projects = get_all_projects($year, $quarter);
		$groups = [];
		
		if(isset($_GET['automate'])){
			$groups = auto_group($year, $quarter, $_GET['type'], $_GET['min'], $_GET['max']);
			
			/*
				$groups = [
					project_id => [
						weight -> [
							group_key => [
								student_id => [weighted choices]
							]
						]
					]
				]
			*/
		}
?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='UTF-8'>
				<title>Create a group</title>
				<link rel='stylesheet' href='include/css/main.css' />
				<link rel='stylesheet' href='include/css/scroll_columns.css' />
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
				<div id='students_container' class='column_container' style='display:inline-block; width:49%; height:100%; vertical-align:top; overflow-y: hidden;'>
					<table id='applicable_students' class='display connected_sortable inner_content' style='width:100%;'>
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
									//If the student is placed in a group, skip
									if(nested_key_exists($student->sim_id, $groups)){
										continue;
									}
									
									$selected_choices = $student->get_choices();
							?>
									<tr id='student_<?= $student->sim_id ?>' class='move'>
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
				<div id='groups_container' class='column_container' style='display:inline-block; width:50%; vertical-align:top;'>
					<div id='groups_inner_container' class='inner_content'>
						<form id='add_group_form' action='exec_group.php' method='POST'>
							<?php
								//If automation returns no groups
								if(count($groups) == 0){
							?>
									<table id='add_group1' class='basic_table group_table' style='width:100%; margin-bottom:15px;'>
										<tr>
											<td colspan='2'>
												<span style='float:left'>
													Group Details
												</span>
												<span class='delete_group pointer' style='float:right;'>
													X
												</span>
											</td>
										</tr>
										<tr>
											<td style='width:5%; padding:5px; text-align:center;'>
												Name:
											</td>
											<td style='width:95%; padding:5px;'>
												<input type='text' class='group_name' name='group1[name]' placeholder='FYP-99-S01' maxlength='32' style='width:97%;' required />
											</td>
										</tr>
										<tr>
											<td style='width:5%; padding:5px; text-align:center;'>
												Supervisor:
											</td>
											<td style='width:95%; padding:5px;'>
												<select class='group1_supervisor check_same' name='group1[supervisor]' style='width:97%;' required>
													<?php
														foreach($all_faculty as $supervisor){
													?>
															<option value='<?= $supervisor->sim_id ?>'><?= $supervisor->get_name() ?></option>
													<?php
														}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td style='width:5%; padding:5px; text-align:center;'>
												Assessor:
											</td>
											<td style='width:95%; padding:5px;'>
												<select class='group1_assessor' name='group1[assessor]' style='width:97%;' required>
													<?php
														foreach($all_faculty as $assessor){
													?>
															<option value='<?= $assessor->sim_id ?>'><?= $assessor->get_name() ?></option>
													<?php
														}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td style='width:5%; padding:5px; text-align:center;'>
												Project:
											</td>
											<td style='width:95%; padding:5px;'>
												<select name='group1[project]' style='width:97%;' required>
													<?php
														foreach($all_projects as $project){
													?>
															<option value='<?= $project->id ?>'>(<?= $project->id ?>) - <?= $project->get_name() ?></option>
													<?php
														}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td colspan='2' style='width:95%; padding:5px;'>
												<table class='group_members basic_table_color connected_sortable' group_id='group1' style='width:100%; --color:#EFC4F9;'>
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
											</td>
										</tr>
										<input type='hidden' name='group1[year]' value='<?= $year ?>'>
										<input type='hidden' name='group1[quarter]' value='<?= $quarter ?>'>
										<input type='hidden' name='group1[type]' value='<?= $_GET['type'] ?>'>
									</table>
							<?php
								}else{
									//Automation has resulted in groups being made
									$counter = 1;
									
									foreach($groups as $project_id => $weights){
										foreach($weights as $weight => $group_ids){
											foreach($group_ids as $group_id => $student_ids){
							?>
												<table id='add_group<?= $counter ?>' class='basic_table group_table' style='width:100%; margin-bottom:15px;'>
													<tr>
														<td colspan='2'>
															<span style='float:left'>
																Group Details
															</span>
															<span class='delete_group pointer' style='float:right;'>
																X
															</span>
														</td>
													</tr>
													<tr>
														<td style='width:5%; padding:5px; text-align:center;'>
															Name:
														</td>
														<td style='width:95%; padding:5px;'>
															<input type='text' class='group_name' name='group<?= $counter ?>[name]' placeholder='FYP-99-S01' maxlength='32' style='width:97%;' required />
														</td>
													</tr>
													<tr>
														<td style='width:5%; padding:5px; text-align:center;'>
															Supervisor:
														</td>
														<td style='width:95%; padding:5px;'>
															<select class='group<?= $counter ?>_supervisor check_same' name='group<?= $counter ?>[supervisor]' style='width:97%;' required>
																<?php
																	foreach($all_faculty as $supervisor){
																?>
																		<option value='<?= $supervisor->sim_id ?>'><?= $supervisor->get_name() ?></option>
																<?php
																	}
																?>
															</select>
														</td>
													</tr>
													<tr>
														<td style='width:5%; padding:5px; text-align:center;'>
															Assessor:
														</td>
														<td style='width:95%; padding:5px;'>
															<select class='group<?= $counter ?>_assessor' name='group<?= $counter ?>[assessor]' style='width:97%;' required>
																<?php
																	foreach($all_faculty as $assessor){
																?>
																		<option value='<?= $assessor->sim_id ?>'><?= $assessor->get_name() ?></option>
																<?php
																	}
																?>
															</select>
														</td>
													</tr>
													<tr>
														<td style='width:5%; padding:5px; text-align:center;'>
															Project:
														</td>
														<td style='width:95%; padding:5px;'>
															<select name='group<?= $counter ?>[project]' style='width:97%;' required>
																<?php
																	foreach($all_projects as $project){
																		$selected = (($project_id == $project->id) ? "selected" : "");
																?>
																		<option value='<?= $project->id ?>' <?= $selected ?>>(<?= $project->id ?>) - <?= $project->get_name() ?></option>
																<?php
																	}
																?>
															</select>
														</td>
													</tr>
													<tr>
														<td colspan='2' style='width:95%; padding:5px;'>
															<table class='group_members basic_table_color connected_sortable' group_id='group<?= $counter ?>' style='width:100%; --color:#EFC4F9;'>
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
																<?php
																	foreach($student_ids as $student_id => $choices){
																?>
																		<tr>
																			<td>
																				<?php
																					foreach($applicable_students as $student_object){
																						if($student_object->sim_id == $student_id){
																							echo $student_object->get_name();
																							break;
																						}
																					}
																				?>
																			</td>
																			<?php
																				foreach($choices as $weights){
																			?>
																					<td style='width:30px; padding:0; text-align:center;'>
																						<?php
																							if($weights == 0){
																								echo 0;
																							}elseif($weights == 1){
																								echo 3;
																							}elseif($weights == 2){
																								echo 2;
																							}elseif($weights == 3){
																								echo 1;
																							}else
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
															<br />
														</td>
													</tr>
												</table>
							<?php
												$counter++;
											}
										}
									}
								}
							?>
							<input type='button' id='new_group' value='Include another group'>
							<br />
							<input type='hidden' name='year' value='<?= $year ?>'>
							<input type='hidden' name='quarter' value='<?= $quarter ?>'>
							<input type='hidden' name='type' value='<?= $_GET['type'] ?>'>
							<input type='submit' name='add_multiple' value='Add All Groups'>
						</form>
					</div>
				</div>
			</body>
			<script>
				//Sets all existing tables of the class as sortable
				function set_sortable(){
					//Enable drag/drop
					$(".connected_sortable")
						.sortable({
							disabled: false,
							items: "tr:not(:first, :contains('No data available in table'))",
							helper: "clone",
							connectWith: ".connected_sortable",
							start: function(event, element){
								//console.log($($(element)[0]['item'][0]));
							},
							receive : function(event, element){
								//Update the moved row to the new table
								if(/group_members/.test($($(element)[0]['sender'][0]).attr("class"))){
									//Group to all students
									let row_data = [];
									
									$($(element)[0]['item'][0]).find("td")
										.each(function(idx, col){
											row_data.push($(col).text().trim())
										});
									
									dt.row.add(row_data).node().id = $(element)[0]['item'][0].id;
									dt.draw();
									
									//Delete the hidden input
									$($(element)[0]['sender'][0]).children("input:hidden[value='" + $(element)[0]['item'][0].id.replace(/student_/, "") + "']").remove();
									
									
								}else{
									//All students to group
									
									//Add the hidden input
									$($($(element)[0]['item'][0].closest("table"))).append($("<input/>", {
										type: "hidden",
										name: $($($(element)[0]['item'][0].closest("table"))).attr("group_id") + "[students][]",
										value: $($(element)[0]['item'][0]).attr("id").replace(/student_/, "")
									}));
								}
							},
							update: function(event, element){
								//Delete the row from source
								if($(element)[0]['sender'] !== null){
									if(/group_members/.test($($(element)[0]['sender'][0]).attr("class"))){
										//Group to all students
										$(element)[0]['item'][0].remove();
									}else{
										//All students to group
										dt.row($($(element)[0]['item'][0])).remove().draw();
									}
								}
							}
						})
						.disableSelection();
				}
				
				set_sortable(); //Sets initial sortable for first group
				
				$.fn.dataTable.ext.type.order['rank-pre'] = function(r){
					switch($.trim(r)){
							case '1': case 1: return 1;
							case '2': case 2: return 2;
							case '3': case 3: return 3;
							case '': return 9;
					}
					
					return 9;
				};
				
				var dt = $("#applicable_students").DataTable({
					/* Disable initial sort */
					"aaSorting": [], 
					"paging": false, 
					"createdRow": function(row, data, dataIndex){
						$(row).addClass("ui-sortable-handle move");
					}, 
					"columnDefs": 
						[
							{"targets": [0], "type": "string"},
							{"targets": "_all", "type": "rank"}
						]
				});
				
				$("#add_group_form").submit(function(e){
					//Ensure at least 1 group exists
					if($(".group_table").length == 0){
						alert("There has to be at least 1 group to add.");
						e.preventDefault();
						
						return false;
					}
					
					//Check faculty involved for each group
					$(".check_same").each(function(){
						if($(this)[0]['selectedOptions'][0]['value'] == $("." + /group\d+/.exec($(this).attr("class"))[0] + "_assessor")[0]['selectedOptions'][0]['value']){
							alert("Supervisor and Assessor should be different.");
							e.preventDefault();
							
							return false;
						}
					});
					
					//Ensure no 2 group names are the same
					let names_list = [];
					
					$(".group_name").each(function(){
						if(jQuery.inArray($(this).val(), names_list) >= 0){
							alert("Group names must be unique.");
							e.preventDefault();
							
							return false;
						}
						
						names_list.push($(this).val());
					});
					
					//Ensure there is at least 1 group member for each group
					$(".group_members").each(function(){
						if($(this).find("tr:last").index() == 0){
							alert("Each group must have at least 1 member.");
							e.preventDefault();
							
							return false;
						}
					});
				});
				
				//Handle group tables
				let newest_group = $("#add_group1").clone().get(0).outerHTML;
				let counter = 1;
				
				//Add new group
				$("#new_group").click(function(){
					$("#new_group").before(newest_group.replace(/group\d+/g, "group" + (++counter)));
					
					set_sortable(); //Refresh all sortable tables on page
				});
				
				//Remove existing group
				$(document).on("click", ".delete_group", function(){
					let this_group = $(this).closest("table");
					
					this_group.find(".group_members tr:not(:first)").each(function(){
						let row_data = [];
						
						$(this).find("td")
							.each(function(idx, col){
								row_data.push($(col).text().trim())
							});
						
						dt.row.add(row_data).node().id = $(this)[0].id;
						dt.draw();
					});
					
					this_group.remove();
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