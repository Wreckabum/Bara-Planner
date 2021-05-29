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
	
	$projects = get_project($account->get_choices());
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Choices</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<style>
			table {
				margin: 0;
			}
		</style>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='own_details' class='basic_table' style='width:40%;'>
			<tr>
				<td colspan='2'>
					Your Details
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					UOW ID:
				</td>
				<td>
					<?= $account->uow_id ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					SIM ID:
				</td>
				<td>
					<?= $account->sim_id ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Name:
				</td>
				<td>
					<?= $account->get_name() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					SIM E-mail:
				</td>
				<td>
					<?= $account->get_sim_email() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Major:
				</td>
				<td>
					<?= get_major($account->get_majors())->get_name() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Type:
				</td>
				<td>
					<?= $account->get_account_type() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Year:
				</td>
				<td>
					<?= $account->get_year() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Quarter:
				</td>
				<td>
					<?= $account->get_quarter() ?>
				</td>
			</tr>
		</table>
		<br />
		<?php			
			if(empty($projects)){
				if($account->can_make_choice()){
		?>
					<a href='make_choices.php'>Make Choices</a>
		<?php
				}else{
		?>
					Deadline of <?= date("d M Y", strtotime($account->get_choices_deadline())) ?> has passed.
		<?php
				}
			}else{
				$choice = 1;
				
				foreach($projects as $project){
	?>
					<table id='choice_<?= $choice ?>' class='basic_table' style='width:40%;'>
						<tr>
							<td colspan='2'>
								Choice #<?= $choice++ ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Project ID:
							</td>
							<td>
								<?= $project->proj_id ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Name:
							</td>
							<td>
								<?= $project->get_name() ?>
							</td>
						</tr>
						<tr>
							<td style='width:25%;'>
								Description:
							</td>
							<td>
								<?= nl2br($project->get_description()) ?>
							</td>
						</tr>
					</table>
					<br />
		<?php
				}
				
				if($account->can_make_choice()){
		?>
					<a href='make_choices.php'>Update Choices</a> - (Deadline is at <?= date("d M Y", strtotime($account->get_choices_deadline())) ?>)
		<?php
				}else{
		?>
					Deadline of <?= date("d M Y", strtotime($account->get_choices_deadline())) ?> has passed.
		<?php
				}
			}
		?>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>