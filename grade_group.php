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
	
	//If not assessor
	if(!$group->is_assessor($account->sim_id)){
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
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<form id='grade_form' action='exec_grade_group.php' method='POST'>
			<table id='grade_group_table' class='basic_table' style='width:40%;'>
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
										<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
									</a>
								</td>
								<td>
									<input type='number' name='score_<?= $member->details->sim_id ?>' min='0' max='100' value='<?= (int)$member->score ?>' class='form-control' style='text-align:center;'  required />
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
									<td>
										<input type='number' name='score_<?= $member->details->sim_id ?>' min='0' max='100' value='<?= (int)$member->score ?>' class='form-control' style='text-align:center;' required />
									</td>
								</tr>
					<?php
							}
						}
					?>
				<tr>
					<td colspan='3'>					
						<input type='checkbox' id='confirm_checkbox' name='delete_id' value='<?= $group->id ?>' required/>
						<span id='confirm_text'>Confirm Scores</span>
						<input type='hidden' name='id' value='<?= $group->id ?>'>
						<input type='submit' name='grade_group' id='grade_group' value='Grade' style='display:none;' disabled/>
					</td>
				</tr>
				<tr>
					<td colspan='3'>
						<a href='home.php'>
							Back to main page
						</a>
					</td>
				</tr>
			</table>
		</form>
	</body>
	<script>
		$("#confirm_checkbox").change(function(){
			if($(this).is(":checked")){
				$("#confirm_text").hide();
				$("#grade_group").show().attr("disabled", false);
			}else{
				$("#confirm_text").show();
				$("#grade_group").hide().attr("disabled", true);
			}
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>