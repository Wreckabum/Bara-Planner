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
	require_once("dompdf/autoload.inc.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	
	//Clean parameter
	str_clean($_GET['g']);
	
	try{
		$group = get_group($_GET['g']);
	}catch(Exception $e){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If not group supervisor/assessor/admin
	if(!$group->is_supervisor($account->sim_id) && !$group->is_assessor($account->sim_id) && !$account->is_admin()){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Get the output DOM
	ob_start();
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<link rel='stylesheet' href='include/css/main.css' />
		<link href='include/css/bootstrap.min.css' rel='stylesheet' id='bootstrap-css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<style>
			.basic_table {
				width:100%;
				border: 1px solid #000000;
				border-spacing: -1px;
			}

			.basic_table tr td {
				border: 1px solid #000000;
				padding: 3px 15px;
			}

			.basic_table tr:first-child td {
				background-color:#B0D8EA;
				font-weight: bold;
			}
			
			#grading_table {
				display: inline-table;
			}
			
			#grading_table td {
				text-align: center;
				white-space: nowrap;
				padding: 10px 15px;
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
			
			#grading_table .supervisor {
				background-color: #E6ffE6;
			}
			
			#grading_table .assessor {
				background-color: #CFCFFF;
			}
			
			#grading_table .student {
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
			
			#grading_table .table_header {
				background-color:#B0D8EA;
				font-weight: bold;
			}
			
			#grading_table .feedback {
				height: 115px;
			}
		</style>
	</head>
	<body>
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
							<?= (is_null($group->get_supervisor()) ? "" : "{$group->get_supervisor()->get_name()} ({$group->get_supervisor()->sim_id})") ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Assessor:
						</td>
						<td colspan='2'>
							<?= (is_null($group->get_assessor()) ? "" : "{$group->get_assessor()->get_name()} ({$group->get_assessor()->sim_id})") ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Project:
						</td>
						<td colspan='2'>
							<?= $group->get_project()->id ?> - <?= $group->get_project()->get_name() ?>
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
											<?= $member->details->get_name() ?>
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
											<?= $member->details->get_name() ?>
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
				<table id='grading_table' class='basic_table' style='display:table; width:auto; margin:0 auto;'>
					<tr>
						<td colspan='2'>
							Item
						</td>
						<td>
							Assignment Items &amp; Format
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
											$total_average -= (($section_details->supervisor + $section_details->assessor) / 2);
									?>
												<td class='due penalty'>
													-
												</td>
												<td class='weight penalty'>
													-%
												</td>
												<td class='supervisor penalty'>
													<?= $section_details->supervisor ?>
												</td>
												<td class='assessor penalty'>
													<?= $section_details->assessor ?>
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
																	<?= $part_details->supervisor ?>
																</td>
																<td class='assessor'>
																	<?= $part_details->assessor ?>
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
													$total_average += (($section_details->supervisor + $section_details->assessor) / 2);
											?>
														<td class='weight'>
															<?= $section_details->weight ?>%
														</td>
														<td class='supervisor'>
															<?= $section_details->supervisor ?>
														</td>
														<td class='assessor'>
															<?= $section_details->assessor ?>
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
									<?= $group->get_marking_scheme()->student->individual->{$member->details->sim_id} ?>
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
					?>
							<tr>
								<td colspan='2' class='feedback'>
									<?= $feedback_id ?>
								</td>
								<td class='feedback'>
									<?= $feedback_details->desc ?>
								</td>
								<td colspan='2' class='supervisor feedback'>
									<?= str_replace("\r\n", "<br>", $feedback_details->supervisor) ?>
								</td>
								<td class='assessor feedback'>
									<?= (($feedback_details->agreed) ? "Agreed" : "Disagreed") ?>
								</td>
								<td colspan='3' class='assessor feedback'>
									<?= str_replace("\r\n", "<br>", $feedback_details->assessor) ?>
								</td>
							</tr>
					<?php
						}
					?>
					<tr>
						<td colspan='9' style='background-color:#F5E6FF; padding:20px 5px; font-weight:bold;'>
							Approval
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
										<?= (($group->get_marking_scheme()->approve->supervisor) ? "<strong>Approved</strong>" : "<strong>NOT</strong> Approved") ?>
									</td>
									<td style='width:50%; background-color:#FFD5C2;'>
										<?= (($group->get_marking_scheme()->approve->assessor) ? "<strong>Approved</strong>" : "<strong>NOT</strong> Approved") ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<br />
			</div>
		</div>
	</body>
</html>
<?php
	$page = ob_get_contents();
	ob_get_clean();

	$doc = new DOMDocument();
	$doc->loadHTML($page);
	
	//var_dump($doc->saveHTML());
	
	use Dompdf\Dompdf;
	
	$dompdf = new Dompdf();
	$dompdf->loadHtml($doc->saveHTML());
	$dompdf->setPaper('A3', 'landscape');
	
	/* $options = $dompdf->getOptions();
	$options->isPhpEnabled(true);
	$dompdf->setOptions($options); */

	$dompdf->render();
	$dompdf->stream($group->get_name());
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>