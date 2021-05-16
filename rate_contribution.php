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
	
	//If not student
	if(!$account->is_student()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$group = get_group_by_member($account->sim_id);
	
	$err = "";
	
	if(isset($_GET['err'])){
		switch($_GET['err']){
			case 0:
				$err = "Unexpected error.";
				break;
			
			case 1:
				$err = "Total contributions do not add up correctly.";
				break;
			
			case 2:
				$err = "Unknown member found.";
				break;
			
			case 3:
				$err = "Grades have already been approved and set by both the supervisor and assessor.";
				break;
			
			case 9:
				$err = "Successfully updated.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Rate Group Members Contributions</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<style>
			#constribution_table {
				display: inline-table;
			}
			
			#constribution_table td {
				text-align: center;
				white-space: nowrap;
			}
			
			#constribution_table .student {
				background-color: #E6ffE6;
			}
			
			#constribution_table input {
				width: 97%;
				text-align: center;
			}
			
			#constribution_table input::-webkit-outer-spin-button,
			#constribution_table input::-webkit-inner-spin-button {
				-webkit-appearance: none;
				margin: 0;
			}
			
			#constribution_table input[type=number] {
				-moz-appearance: textfield;
			}
		</style>
	</head>
	<body>
		<?php
			include("include/templates/header.php");
		?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:<?= (($_GET['err'] >= 9) ? "#0C7B0C" : "#E22C2C" ) ?>; padding:10px;'><?= $err ?></div>
		</center>
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
			<form id='rate_members' action='exec_group_contribution.php' method='POST'>
				<table id='constribution_table' class='basic_table' style='display:table; width:auto; margin:0 auto;'>
					<tr>
						<td>
							#
						</td>
						<td>
							Member
						</td>
						<td>
							Student ID
						</td>
						<td>
							Contribution (%)
						</td>
					</tr>
					<?php
						$count = 1;
						$max_percentage = (count((array)$group->get_marking_scheme()->student->contribution->{$account->sim_id}) * 100);
						$final_count = $max_percentage;
						
						foreach($group->get_marking_scheme()->student->contribution->{$account->sim_id} as $member_id => $contribution){
							$name = "";
							
							foreach($group->get_members() as $key => $member){
								if($member->details->sim_id == $member_id){
									$name = $member->details->get_name();
									break;
								}
							}
							
					?>
								<tr>
									<td>
										<?= $count++ ?>
									</td>
									<td>
										<?= $name ?>
									</td>
									<td>
										<?= $member_id ?>
									</td>
									<td class='student'>
										<input class='member_contribution' type='number' name='member[<?= $member_id ?>]' min='0' max='<?= $max_percentage ?>' value='<?= $contribution ?>' />
									</td>
								</tr>
					<?php
							$final_count -= $contribution;
						}
					?>
					<tr>
						<td colspan='3' style='padding:10px 5px;'>
							<input type='submit' name='rate' value='Rate Members' />
						</td>
						<td colspan='3' style='padding:10px 5px;'>
							Remaining: <span id='percentage_left'><?= $final_count ?></span>
						</td>
					</tr>
				</table>
				<input type='hidden' name='group_id' value='<?= $group->id ?>'>
			</form>
			<br />
			<br />
		</div>
		<br />
		<a href='home.php'>Back to main page</a>
		</div>
	</body>
	<script>$('.member_contribution').keyup(function(e){
			let total_perc = <?= $max_percentage ?>;
			
			$(this).val(Math.abs($(this).val()));
			
			$('.member_contribution').each(function(){
				total_perc -= parseInt($(this).val()) || 0;
			});
			
			$('#percentage_left').text(total_perc);
			
			if(total_perc < 0){
				console.log($('#percentage_left').parent());
				$('#percentage_left').parent().css('background-color', '#FFCECE');
			}else{
				$('#percentage_left').parent().css('background-color', '');
			}
		});
		
		$('#rate_members').submit(function(e){
			let total_perc = <?= $max_percentage ?>;
			
			$('#rate_members input[name^=member]').each(function(){
				total_perc -= parseInt($(this).val());
			});
			
			if(isNaN(total_perc) || total_perc != 0){
				alert('The total contribution percentages must total up to <?= $max_percentage ?>%.\\nYou have ' + total_perc + '% remaining to distribute.');
				e.preventDefault();
				
				return false;
			}
			
			return confirm('Confirm contribution percentage?\\nUpdates can be made at a later time.');
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>