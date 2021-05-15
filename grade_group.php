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
	
	try{
		$group = get_group($_GET['g']);
		$title_extra = " (". $group->id .")";
	}catch(Exception $e){
		header("location: view_all.php?t=groups");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If not assessor/supervisor
	if(!$group->is_supervisor($account->sim_id) && !$group->is_assessor($account->sim_id)){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Grade Group<?= $title_extra ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<style>
			#grading_table {
				display: inline-table;
			}
			
			#grading_table td {
				text-align: center;
				white-space: nowrap;
			}
			
			#grading_table td.empty {
				background-color: #E4E4E4;
			}
			
			#grading_table .item_desc {
				width: 450px;
			}
			
			#grading_table .supervisor, 
			#grading_table .assessor, 
			#grading_table .total, 
			#grading_table .average {
				width: 112px;
			}
			
			#grading_table .supervisor,
			#feedback_table .supervisor{
				background-color: #E6ffE6;
			}
			
			#grading_table .assessor,
			#feedback_table .assessor {
				background-color: #CFCFFF;
			}
			
			#grading_table .student,
			#feedback_table .student {
				background-color: #E0B5E0;
			}
			
			#grading_table .total {
				background-color: #C8E0F1;
			}
			
			#grading_table .average {
				background-color: #E8E15F;
			}
			
			#grading_table .final {
				background-color: #FB9929;
				font-weight:bold;
			}
			
			#grading_table .penalty {
				background-color: #FFCECE;
			}
			
			#grading_table .table_header, 
			#feedback_table .table_header {
				background-color:#B0D8EA;
				font-weight: bold;
			}
			
			#grading_table input {
				width: 97%;
				text-align: center;
			}
			
			#grading_table input::-webkit-outer-spin-button,
			#grading_table input::-webkit-inner-spin-button {
				-webkit-appearance: none;
				margin: 0;
			}
			
			#grading_table input[type=number] {
				-moz-appearance: textfield;
			}
		</style>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<div class='container'>
			<div id='grading_container'>
				<table id='grade_group_table' class='basic_table' style='display:table; width:auto; margin:0 auto;'>
					<tr>
						<td colspan='3'>
							Group Details
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Group ID:
						</td>
						<td colspan='2'>
							#<?= $group->id ?>
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
							<a href='view_project.php?p=<?= $group->get_project()->id ?>'>
								<?= $group->get_project()->id ?> - <?= $group->get_project()->get_name() ?>
							</a>
						</td>
					</tr>
					<tr>
						<td rowspan='<?= count($group->get_members()) ?>'style='width:25%;'>
							Members:
						</td>
						<?php
							$first = true;
							
							foreach($group->get_members() as $member){
								if($first){
						?>
										<td>
											<a href='view_account.php?a=<?= $member->details->sim_id ?>'>
												<?= $member->details->get_name() ?>
											</a>
										</td>
										<td>
											<?= $member->details->sim_id ?>
										</td>
									</tr>
						<?php
									$first = false;
								}else{
						?>
									<tr>
										<td>
											<a href='view_account.php?a=<?= $member->details->sim_id ?>'>
												<?= $member->details->get_name() ?>
											</a>
										</td>
										<td>
											<?= $member->details->sim_id ?>
										</td>
									</tr>
						<?php
								}
							}
						?>
				</table>
				<br />
				<form id='grade_group' action='exec_grade_group.php' method='POST'>
					<table id='grading_table' class='basic_table' style='display:table; width:auto; margin:0 auto;'>
						<tr>
							<td colspan='2'>
								Item
							</td>
							<td>
								Assignment Items & Format
							</td>
							<td>
								Week Due
							</td>
							<td>
								Max Marks (%)
							</td>
							<td>
								Supervisor
							</td>
							<td>
								Assessor
							</td>
							<td>
								Total
							</td>
							<td>
								Average
							</td>
						</tr>
						<?php
							$total_average = 0;

							foreach($group->get_marking_scheme()->faculty as $section => $section_details){
						?>
									<tr>
										<td colspan='2'>
											<?= $section ?>
										</td>
										<td>
											<?= $section_details->desc ?>
										</td>
										<?php
											if($section_details->desc == "Penalty"){
												$supervisor_penalty = (
													($group->is_supervisor($account->sim_id)) ? 
														"<input type='number' name='supervisor[penalty]' min='0' max='100' value='{$section_details->supervisor}' />" : 
														$section_details->supervisor);
												
												$assessor_penalty = (
													($group->is_assessor($account->sim_id)) ? 
														"<input type='number' name='assessor[penalty]' min='0' max='100' value='{$section_details->assessor}' />" : 
														$section_details->assessor);
												
												$total_average -= (($section_details->supervisor + $section_details->assessor) / 2);
										?>
													<td class='due penalty'>
														-
													</td>
													<td class='weight penalty'>
														-%
													</td>
													<td class='supervisor penalty'>
														<?= $supervisor_penalty ?>
													</td>
													<td class='assessor penalty'>
														<?= $assessor_penalty ?>
													</td>
													<td class='total penalty'>
														<?= ($section_details->supervisor + $section_details->assessor) ?>
													</td>
													<td class='average penalty'>
														<?= (($section_details->supervisor + $section_details->assessor) / 2) ?>
													</td>
												</tr>
										<?php
											}else{
										?>
												<td class='due' <?= ((isset($section_details->parts)) ? "rowspan='". (count((array)$section_details->parts) + 1) ."'" : "") ?>>
													<?= $section_details->week_due ?>
												</td>
												<?php
													if(isset($section_details->parts)){
												?>
															<td colspan='5' class='empty'>-</td>
														</tr>
														<?php
															foreach($section_details->parts as $part => $part_details){
																$supervisor_input = (
																	($group->is_supervisor($account->sim_id)) ? 
																		"<input type='number' name='supervisor[{$section}][{$part}]' min='0' max='{$part_details->weight}' value='{$part_details->supervisor}' />" : 
																		$part_details->supervisor);
																
																$assessor_input = (
																	($group->is_assessor($account->sim_id)) ? 
																		"<input type='number' name='assessor[{$section}][{$part}]' min='0' max='{$part_details->weight}' value='{$part_details->assessor}' />" : 
																		$part_details->assessor);
																
																$total_average += (($part_details->supervisor + $part_details->assessor) / 2);
																
														?>
																<tr>
																	<td class='empty'>
																		-
																	</td>
																	<td>
																		<?= $part ?>
																	</td>
																	<td>
																		<?= $part_details->desc ?>
																	</td>
																	<td class='weight'>
																		<?= $part_details->weight ?>%
																	</td>
																	<td class='supervisor'>
																		<?= $supervisor_input ?>
																	</td>
																	<td class='assessor'>
																		<?= $assessor_input ?>
																	</td>
																	<td class='total'>
																		<?= ($part_details->supervisor + $part_details->assessor) ?>
																	</td>
																	<td class='average'>
																		<?= (($part_details->supervisor + $part_details->assessor) / 2) ?>
																	</td>
																</tr>
												<?php
															}
													}else{
														$supervisor_input = (
															($group->is_supervisor($account->sim_id)) ? 
																"<input type='number' name='supervisor[{$section}]' min='0' max='{$section_details->weight}' value='{$section_details->supervisor}'  />" : 
																$section_details->supervisor);
														
														$assessor_input = (
															($group->is_assessor($account->sim_id)) ? 
																"<input type='number' name='assessor[{$section}]' min='0' max='{$section_details->weight}' value='{$section_details->assessor}' />" : 
																$section_details->assessor);
														
														$total_average += (($section_details->supervisor + $section_details->assessor) / 2);
												?>
															<td class='weight'>
																<?= $section_details->weight ?>%
															</td>
															<td class='supervisor'>
																<?= $supervisor_input ?>
															</td>
															<td class='assessor'>
																<?= $assessor_input ?>
															</td>
															<td class='total'>
																<?= ($section_details->supervisor + $section_details->assessor) ?>
															</td>
															<td class='average'>
																<?= (($section_details->supervisor + $section_details->assessor) / 2) ?>
															</td>
														</tr>
						<?php
													}
											}
							}

							$last_student_col = (
								($group->get_contribution_percentage() === false) ?
									"Constribution ratings status" :
									"Final Grade");
						?>
							<tr>
								<td colspan='8' class='final'>
									Total
								</td>
								<td class='final'>
									<?= $total_average ?> / <?= (100 - $group->get_marking_scheme()->student->weight) ?>
								</td>
							</tr>
							<tr>
								<td colspan='9' style='background-color:#F5E6FF; padding:20px 5px; font-weight:bold;'>
									Individual Students
								</td>
							</tr>
							<tr>
								<td colspan='2' class='table_header'>
									#
								</td>
								<td class='table_header'>
									Name / SIM ID
								</td>
								<td class='table_header'>
									Contribution
								</td>
								<td class='table_header'>
									Individual (%)
								</td>
								<td class='table_header'>
									Supervisor
								</td>
								<td colspan='4' class='table_header'>
									<?= $last_student_col ?>
								</td>
							</tr>
						<?php
							$count = 1;

							foreach($group->get_members() as $member){
								$supervisor_input = (
									($group->is_supervisor($account->sim_id)) ? 
										"<input type='number' name='supervisor[student][{$member->details->sim_id}]' min='0' max='{$group->get_marking_scheme()->student->weight}' value='{$group->get_marking_scheme()->student->individual->{$member->details->sim_id}}' />" : 
										$group->get_marking_scheme()->student->individual->{$member->details->sim_id});
								
								$contribution_perc = (
									($group->get_contribution_percentage() === false) ?
										"-" :
										$group->get_contribution_percentage()[$member->details->sim_id] ."%");
								
								if($group->get_contribution_percentage() === false){
									$member_rating_done = (
										((count($group->get_members()) * 100) == array_sum((array)$group->get_marking_scheme()->student->contribution->{$member->details->sim_id})) ?
											"Contribution ratings submitted" :
											"Contribution ratings <strong>NOT</strong> submitted");
									
									$member_rating_colspan = "3";
									$member_rating_class = "student";
									$member_rating_grade = "";
								}else{
									//(Total average * Own Contribution Rate / Max Contribution Rate) + Individual Score (also found in the $group->get_raw_members()
									$member_rating_done = round((((int)$total_average * (int)$group->get_contribution_percentage()[$member->details->sim_id]) / (int)max($group->get_contribution_percentage()) + (int)$group->get_marking_scheme()->student->individual->{$member->details->sim_id}), 2);
									$member_rating_colspan = "2";									
									$member_rating_class = "empty";
									$member_rating_grade = "<td class='{$member_rating_class}'>". Grades::get_grade($member_rating_done) ."</td>";
								}
								
						?>
								<tr>
									<td colspan='2'>
										<?= $count ?>
									</td>
									<td>
										<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
									</td>
									<td class='student'>
										<?= $contribution_perc ?>
									</td>
									<td>
										<?= $group->get_marking_scheme()->student->weight ?>%
									</td>
									<td class='supervisor'>
										<?= $supervisor_input ?>
									</td>
									<td colspan='<?= $member_rating_colspan ?>' class='<?= $member_rating_class ?>'>
										<?= $member_rating_done ?>
									</td>
									<?= $member_rating_grade ?>
								</tr>
						<?php
								++$count;
							}

						?>
							<tr>
								<td colspan='9' style='background-color:#F5E6FF; padding:20px 5px; font-weight:bold;'>
									Feedback
								</td>
							</tr>
							<tr>
								<td colspan='2' class='table_header'>
									#
								</td>
								<td class='table_header'>
									Feedback subject
								</td>
								<td colspan='2' class='table_header'>
									Supervisor feedback
								</td>
								<td class='table_header'>
									Agreed
								</td>
								<td colspan='3' class='table_header'>
									Assessor feedback
								</td>
							</tr>
						<?php
							foreach($group->get_marking_scheme()->feedback as $feedback_id => $feedback_details){
								$supervisor_input = (
									($group->is_supervisor($account->sim_id)) ? 
										"<textarea name='feedback[supervisor][{$feedback_id}]' rows='4' style='width:100%;'>{$feedback_details->supervisor}</textarea>" : 
										str_replace("\r\n", "<br>", $feedback_details->supervisor));
								
								$assessor_input = (
									($group->is_assessor($account->sim_id)) ? 
										"<textarea name='feedback[assessor][{$feedback_id}] rows='4' style='width:100%;'>{$feedback_details->assessor}</textarea>" : 
										str_replace("\r\n", "<br>", $feedback_details->assessor));
								
								$agreed = (
									($group->is_assessor($account->sim_id)) ? 
										"<label style='margin:0;'><input type='radio' name='feedback[agreed][{$feedback_id}]' value='1' style='width:auto;'". (($feedback_details->agreed) ? " checked" : "") ."> Yes</label><br /><label style='margin:0;'><input type='radio' name='feedback[agreed][{$feedback_id}]' value='0' style='width:auto;'". ((!$feedback_details->agreed) ? " checked" : "") ."> No</label>" : 
										(($feedback_details->agreed) ? "Agreed" : "Disagreed"));
								
						?>
								<tr>
									<td colspan='2'>
										<?= $feedback_id ?>
									</td>
									<td>
										<?= $feedback_details->desc ?>
									</td>
									<td colspan='2' class='supervisor'>
										<?= $supervisor_input ?>
									</td>
									<td class='assessor'>
										<?= $agreed ?>
									</td>
									<td colspan='3' class='assessor'>
										<?= $assessor_input ?>
									</td>
								</tr>
						<?php
							}
						?>
						<tr>
							<td colspan='9' class='empty'>
								-
							</td>
						</tr>
						<tr>
							<td colspan='9'>
								<table class='basic_table' style='width:100%;'>
									<tr>
										<td>
											Supervisor
										</td>
										<td>
											Assessor
										</td>
									</tr>
									<tr>
										<td style='width:50%; background-color:#FFD5C2;'>
											<?=
												(
													($group->is_supervisor($account->sim_id)) ? 
														"<label style=' margin:.5rem;'><input type='checkbox' name='approve[supervisor]' value='1' style='width:auto; vertical-align:middle;' /> Approve grades for group</label> (Currently". (($group->get_marking_scheme()->approve->supervisor) ? "" : " <strong>NOT</strong>") ." Approved)" : 
														(($group->get_marking_scheme()->approve->supervisor) ? "Approved" : "<strong>NOT</strong> Approved")
												)
											?>
										</td>
										<td style='width:50%; background-color:#FFD5C2;'>
											<?=
												(
													($group->is_assessor($account->sim_id)) ? 
														"<label style=' margin:.5rem;'><input type='checkbox' name='approve[assessor]' value='1' style='width:auto; vertical-align:middle;' /> Approve grades for group</label> (Currently". (($group->get_marking_scheme()->approve->assessor) ? "" : " <strong>NOT</strong>") ." Approved)" : 
														(($group->get_marking_scheme()->approve->assessor) ? "Approved" : "<strong>NOT</strong> Approved")
												);
											?>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan='9' style='padding:10px 5px;'>
								<input type='submit' name='grade' value='Submit Grading' />
							</td>
						</tr>
					</table>
					<input type='hidden' name='group_id' value='<?= $group->id ?>'>
				</form>
				<br />
			</div>
			<a href='view_group.php?g=<?= $group->id ?>'>Back to group details</a>
			<br />
			<a href='view_group.php'>View all assigned groups</a>
			<br />
			<a href='home.php'>Back to main page</a>
		</div>
		<script>
			$('#grade_group').submit(function(e){
				return confirm('Confirm grades?\\nUpdates can be made at a later time.');
			});
		</script>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>