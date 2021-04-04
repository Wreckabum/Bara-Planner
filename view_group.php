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
		$group = get_group_by_member($account->sim_id);
		$title_extra = " (". $group->id .")";
	}elseif(isset($_GET['g'])){
		//Non-students viewing specific group	
		$group = get_group($_GET['g']);
		$title_extra = " (". $group->id .")";
	}elseif($account->is_faculty()){
		//Faculty viewing their assigned groups
		$group_array = get_group_by_faculty($account->sim_id);
		$title_extra = "s";
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
			
			//$As faculty, show all assigned groups
			if($account->is_faculty() && !isset($_GET['g'])){
				foreach($group_array as $group){
					$supervisor_background = "";
					$assessor_background = "";
					$supervisor_field = "{$group->get_supervisor()->get_name()} ({$group->get_supervisor()->sim_id})";
					$assessor_field = "{$group->get_assessor()->get_name()} ({$group->get_assessor()->sim_id})";
					
					if($group->get_supervisor()->get_name() == $account->get_name()){
						$supervisor_background = "background-color:#BCE2BE";
					}else{
						$supervisor_field = "<a href='view_account.php?a={$group->get_supervisor()->sim_id}'>{$supervisor_field}</a>";
					}
					
					if($group->get_assessor()->get_name() == $account->get_name()){
						$assessor_background = "background-color:#BCE2BE";
					}else{
						$assessor_field = "<a href='view_account.php?a={$group->get_assessor()->sim_id}'>{$assessor_field}</a>";
					}
					
		?>
					<table id='view_group' class='basic_table' style='width:40%;'>
						<tr>
							<td colspan='2'>
								Group #<?= $group->id ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Name:
							</td>
							<td>
								<?= $group->get_name() ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Type:
							</td>
							<td>
								<?= (($group->get_type() == 1) ? "Full-Time" : "Part-Time") ?>
							</td>
						</tr>
						<tr>
							<td style='<?= $supervisor_background ?>'>
								Supervisor:
							</td>
								<td style='<?= $supervisor_background ?>'>
									<?= $supervisor_field ?>
							</td>
						</tr>
						<tr>
							<td style='<?= $assessor_background ?>'>
								Assessor:
							</td>
							<td style='<?= $assessor_background ?>'>
								<?= $assessor_field ?>
							</td>
						</tr>
						
						<tr>
							<td style='width:25%;'>
								Members:
							</td>
							<td>
								<?php
									foreach($group->get_members() as $member){
								?>
										<a href='view_account.php?a=<?= $member->details->sim_id ?>'>
											<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
										</a>
										<br />
								<?php
									}
								?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Project:
							</td>
							<td>
								<a href='view_project.php?p=<?= $group->get_project()->id ?>'>
									<?= $group->get_project()->id ?> - <?= $group->get_project()->get_name() ?>
								</a>
							</td>
						</tr>
					</table>
					<br />
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
						<td colspan='2'>
							Group #<?= $group->id ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Name:
						</td>
						<td>
							<?= $group->get_name() ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Type:
						</td>
						<td>
							<?= (($group->get_type() == 1) ? "Full-Time" : "Part-Time") ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Supervisor:
						</td>
						<td>
							<?= (is_null($group->get_supervisor()) ? "" : "<a href='view_account.php?a={$group->get_supervisor()->sim_id}'>{$group->get_supervisor()->get_name()} ({$group->get_supervisor()->sim_id})</a>") ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Assessor:
						</td>
						<td>
							<?= (is_null($group->get_assessor()) ? "" : "<a href='view_account.php?a={$group->get_assessor()->sim_id}'>{$group->get_assessor()->get_name()} ({$group->get_assessor()->sim_id})</a>") ?>
						</td>
					</tr>
					
					<tr>
						<td style='width:25%;'>
							Members:
						</td>
						<td>
							<?php
								foreach($group->get_members() as $member){
							?>
									<a href='view_account.php?a=<?= $member->details->sim_id ?>'>
										<?= $member->details->get_name() ?> (<?= $member->details->sim_id ?>)
									</a>
									<br />
							<?php
								}
							?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Project:
						</td>
						<td>
							<a href='view_project.php?p=<?= $group->get_project()->id ?>'>
								<?= $group->get_project()->id ?> - <?= $group->get_project()->get_name() ?>
							</a>
						</td>
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
								</td>
								<td>
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
					?>
					<tr>
						<td colspan='2'>
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
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>