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
	$group = null;
	$group_array = null;
	$title_extra = "";
	
	if($account->is_student()){
		//For students, only allow viewing of own group
		try{
			$group = get_group_by_member($account->sim_id);
			$title_extra = " (". $group->id .")";
		}catch(Exception $e){
			header("location: view_all.php?t=groups");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
	}elseif(isset($_GET['g'])){
		//Non-students viewing specific group
		try{
			$group = get_group($_GET['g']);
			$title_extra = " (". $group->id .")";
		}catch(Exception $e){
			header("location: view_all.php?t=groups");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
	}elseif($account->is_faculty()){
		//Faculty viewing their assigned groups
		try{
			$group_array = get_group_by_faculty($account->sim_id);
			$title_extra = "s";
		}catch(Exception $e){
			header("location: view_all.php?t=groups");
			@mysqli_close($GLOBALS['mysql_link']);
			exit();
		}
	}
	
	//If no such group
	if(is_null($group) && empty($group_array)){
		header("location: view_all.php?t=groups");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Group<?= $title_extra ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php
			include("include/templates/header.php"); 
			
			//As faculty, show all assigned groups
			if($account->is_faculty() && !isset($_GET['g'])){
		?>
				<table id='group_filter' class='basic_table' style='width:40%; text-align:center;'>
					<tr>
						<td colspan='2'>
							Filters
						</td>
					</tr>
					<tr>
						<td id='filter_supervisor' style='width:50%; text-align:center; font-weight:bold; background-color:#BFC8EC;'>
							Supervisor
						</td>
						<td id='filter_assessor' style='width:50%; text-align:center; font-weight:bold; background-color:#BFC8EC;'>
							Assessor
						</td>
					</tr>
				</table>
				<br />
		<?php
				foreach($group_array as $group){
					$supervisor_background = "";
					$assessor_background = "";
					$supervisor_field = "{$group->get_supervisor()->get_name()} ({$group->get_supervisor()->sim_id})";
					$assessor_field = "{$group->get_assessor()->get_name()} ({$group->get_assessor()->sim_id})";
					$filter_type = "unknown";
					
					if($group->is_supervisor($account->sim_id)){
						$supervisor_background = "background-color:#BCE2BE";
						$filter_type = "supervisor";
					}else{
						$supervisor_field = "<a href='view_account.php?a={$group->get_supervisor()->sim_id}'>{$supervisor_field}</a>";
					}
					
					if($group->is_assessor($account->sim_id)){
						$assessor_background = "background-color:#BCE2BE";
						$filter_type = "assessor";
					}else{
						$assessor_field = "<a href='view_account.php?a={$group->get_assessor()->sim_id}'>{$assessor_field}</a>";
					}
					
		?>
					<div class='<?= $filter_type ?>' style='margin-bottom:20px;'>
						<table id='view_group' class='basic_table' style='width:40%;'>
							<tr>
								<td colspan='3'>
									Group #<?= $group->id ?>
								</td>
							</tr>
							<tr>
								<td style='width:25%;'>
									Name:
								</td>
								<td colspan='2'>
									<?= $group->get_name() ?>
								</td>
							</tr>
							<tr>
								<td style='width:25%;'>
									Type:
								</td>
								<td colspan='2'>
									<?= (($group->get_type() == 1) ? "Full-Time" : "Part-Time") ?>
								</td>
							</tr>
							<tr>
								<td style='<?= $supervisor_background ?>'>
									Supervisor:
								</td>
								<td colspan='2' style='<?= $supervisor_background ?>'>
										<?= $supervisor_field ?>
								</td>
							</tr>
							<tr>
								<td style='<?= $assessor_background ?>'>
									Assessor:
								</td>
								<td colspan='2'style='<?= $assessor_background ?>'>
									<?= $assessor_field ?>
								</td>
							</tr>
							<tr>
								<td style='width:25%;'>
									Project:
								</td>
								<td colspan='2'>
									<?php
										if(!is_null($group->get_project())){
									?>
											<a href='view_project.php?p=<?= $group->get_project()->id ?>'>
												<?= $group->get_project()->id ?> - <?= $group->get_project()->get_name() ?>
											</a>
									<?php
										}
									?>
								</td>
							</tr>
							<tr>
								<td rowspan='<?= count($group->get_members()) ?>'style='width:25%;'>
									Members:
								</td>
								<?php
									$first = true;
									
									foreach($group->get_members() as $member){
										$score = "";
										
										if(is_null($member->score)){
											$score = "N/A";
										}else{
											$score = (int)$member->score ." (". Grades::get_grade($member->score) .")";
										}
										
										if($first){
								?>
											<td>
												<a href='view_account.php?a=<?= $member->details->sim_id ?>'>
													<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
												</a>
											</td>
											<td style='text-align:center;' >
												<?= $score ?>
											</td>
										</tr>
								<?php
											$first = false;
										}else{
								?>
											<tr>
												<td>
													<a href='view_account.php?a=<?= $member->details->sim_id ?>'>
														<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
													</a>
												</td>
												<td style='text-align:center;' >
													<?= $score ?>
												</td>
											</tr>
								<?php
										}
									}
								?>
								<tr>
									<td colspan='3'>
										<a href="grade_group.php?g=<?= $group->id ?>">
											[ Grade ]
										</a>
									</td>
								</tr>
						</table>
					</div>
		<?php
				}
		?>
				<a href='home.php'>Back to main page</a>
		<?php
			}else{
				//Viewing single group
		?>
				<table id='view_group' class='basic_table' style='width:40%;'>
					<tr>
						<td colspan='3'>
							Group #<?= $group->id ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Name:
						</td>
						<td colspan='2'>
							<?= $group->get_name() ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Type:
						</td>
						<td colspan='2'>
							<?= (($group->get_type() == 1) ? "Full-Time" : "Part-Time") ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Supervisor:
						</td>
						<td colspan='2'>
							<?= (is_null($group->get_supervisor()) ? "" : "<a href='view_account.php?a={$group->get_supervisor()->sim_id}'>{$group->get_supervisor()->get_name()} ({$group->get_supervisor()->sim_id})</a>") ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Assessor:
						</td>
						<td colspan='2'>
							<?= (is_null($group->get_assessor()) ? "" : "<a href='view_account.php?a={$group->get_assessor()->sim_id}'>{$group->get_assessor()->get_name()} ({$group->get_assessor()->sim_id})</a>") ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Project:
						</td>
						<td colspan='2'>
							<?php
								if(!is_null($group->get_project())){
							?>
									<a href='view_project.php?p=<?= $group->get_project()->id ?>'>
										<?= $group->get_project()->id ?> - <?= $group->get_project()->get_name() ?>
									</a>
							<?php
								}
							?>
						</td>
					</tr>
					<tr>
						<td rowspan='<?= count($group->get_members()) ?>'style='width:25%;'>
							Members:
						</td>
						<?php
							$first = true;
							
							foreach($group->get_members() as $member){
								$score = "";
								
								if($account->is_student()){
									if($account->sim_id == $member->details->sim_id){
										if(is_null($member->score)){
											$score = "N/A";
										}else{
											$score = (int)$member->score ." (". Grades::get_grade($member->score) .")";
										}
									}else{
										$score = "-";
									}
								}else{
									if(is_null($member->score)){
										$score = "N/A";
									}else{
										$score = (int)$member->score ." (". Grades::get_grade($member->score) .")";
									}
								}
									
								if($first){
						?>
									<td>
										<a href='view_account.php?a=<?= $member->details->sim_id ?>'>
											<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
										</a>
									</td>
									<td style='text-align:center;' >
										<?= $score ?>
									</td>
								</tr>
						<?php
									$first = false;
								}else{
						?>
									<tr>
										<td>
											<a href='view_account.php?a=<?= $member->details->sim_id ?>'>
												<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
											</a>
										</td>
										<td style='text-align:center;' >
											<?= $score ?>
										</td>
									</tr>
						<?php
								}
							}
						?>
					</tr>
					<?php
						//Ensure acocunt is admin
						if($account->is_admin()){
					?>
							<tr>
								<td>
									<a href="edit_group.php?g=<?= $group->id ?>">
										Edit Group
									</a>
									/
									<a href="edit_group_multiple.php?semester=<?= $group->get_year() ?>_<?= $group->get_quarter() ?>&type=<?= $group->get_type() ?>#<?= $group->id ?>">
										[ Edit All ]
									</a>
								</td>
								<td colspan='2'>
									<a id='delete_link' href='#' onClick="show_delete();">
										Delete Group
									</a>
									<form id='delete_form' action='delete_group.php' method='POST' style='display:none;'>
										<input type='checkbox' id='confirm_checkbox' name='delete_id' value='<?= $group->id ?>' required/>
										<input type='submit' name='delete_account' id='delete_submit' value='Delete' disabled/>
									</form>
								</td>
							</tr>
					<?php
						}
						if($group->is_assessor($account->sim_id) || $group->is_supervisor($account->sim_id)){
					?>
							<tr>
								<td colspan='3'>
									<a href="grade_group.php?g=<?= $group->id ?>">
										[ Grade ]
									</a>
								</td>
							</tr>
					<?php
						}
					?>
					<tr>
						<td colspan='3'>
							<a href='home.php'>
								Back to main page
							</a>
						</td>
					</tr>
				</table>
		<?php
			}
		?>
	</body>
	<script>
		function show_delete(){
			$("#delete_link").hide();
			$("#delete_form").show();
		}
		
		$("#confirm_checkbox").change(function(){
			if($(this).is(":checked")){
				$("#delete_submit").attr("disabled", false);
			}else{
				$("#delete_submit").attr("disabled", true);
			}
		});
		
		$("#filter_supervisor").click(function(){
			if($("#filter_supervisor").css("background-color") == "rgb(191, 200, 236)"){
				$("#filter_supervisor").css("background-color", "rgb(255, 255, 255)");
			}else{
				$("#filter_supervisor").css("background-color", "rgb(191, 200, 236)");
			}
			
			$(".supervisor").each(function(){
				$(this).toggle("fast");
			});
		});
		
		$("#filter_assessor").click(function(){
			if($("#filter_assessor").css("background-color") == "rgb(191, 200, 236)"){
				$("#filter_assessor").css("background-color", "rgb(255, 255, 255)");
			}else{
				$("#filter_assessor").css("background-color", "rgb(191, 200, 236)");
			}
			
			$(".assessor").each(function(){
				$(this).toggle("fast");
			});
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>