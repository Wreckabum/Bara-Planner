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
	
	if($account->is_student()){
		//For students, only allow viewing of own group
		$group = get_group_by_member($account->sim_id);
	}elseif(isset($_GET['g'])){
		//Non-students viewing specific group	
		$group = get_group($_GET['g']);
	}elseif($account->is_faculty()){
		//Faculty viewing their assigned groups
		$group_array = get_group_by_faculty($account->sim_id);
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
		<title>View Group - <?= $group['id'] ?></title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<?php
			if($account->is_faculty() && !isset($_GET['g'])){
				foreach($group_array as $id => $group){
		?>
					<table id='view_group' class='basic_table' style='width:40%;'>
						<tr>
							<td colspan='2'>
								Group #<?= $id ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Name:
							</td>
							<td>
								<?= $group['name'] ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Supervisor:
							</td>
							<td>
								<?= (is_null($group['supervisor']) ? "" : "{$group['supervisor']->get_name()} ({$group['supervisor']->sim_id})") ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Assessor:
							</td>
							<td>
								<?= (is_null($group['assessor']) ? "" : "{$group['assessor']->get_name()} ({$group['assessor']->sim_id})") ?>
							</td>
						</tr>
						
						<tr>
							<td style='width:25%;'>
								Members:
							</td>
							<td>
								<?php
									foreach($group['members'] as $member){
								?>
										<?= $member->get_name() ?> (<?= $member->sim_id ?>)
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
								<?= $group['project']['id'] ?> - <?= $group['project']['name'] ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Deadline:
							</td>
							<td>
								<?= $group['deadline'] ?>
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
		?>
				<table id='view_group' class='basic_table' style='width:40%;'>
					<tr>
						<td colspan='2'>
							Group #<?= $group['id'] ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Name:
						</td>
						<td>
							<?= $group['name'] ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Supervisor:
						</td>
						<td>
							<?= (is_null($group['supervisor']) ? "" : "{$group['supervisor']->get_name()} ({$group['supervisor']->sim_id})") ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Assessor:
						</td>
						<td>
							<?= (is_null($group['assessor']) ? "" : "{$group['assessor']->get_name()} ({$group['assessor']->sim_id})") ?>
						</td>
					</tr>
					
					<tr>
						<td style='width:25%;'>
							Members:
						</td>
						<td>
							<?php
								foreach($group['members'] as $member){
							?>
									<?= $member->get_name() ?> (<?= $member->sim_id ?>)
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
							<?= $group['project']['id'] ?> - <?= $group['project']['name'] ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Deadline:
						</td>
						<td>
							<?= $group['deadline'] ?>
						</td>
					</tr>
					<?php
						//Ensure acocunt is admin
						if($account->is_admin()){
					?>
							<tr>
								<td>
									<a href="edit_major.php?m=<?= $group['id'] ?>">
										Edit Group
									</a>
								</td>
								<td>
									<a id='delete_link' href='#' onClick="show_delete();">
										Delete Group
									</a>
									<form id='delete_form' action='delete_group.php' method='POST' style='display:none;'>
										<input type='checkbox' id='confirm_checkbox' name='delete_id' value='<?= $group['id'] ?>' required/>
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
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>