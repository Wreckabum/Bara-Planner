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
	
	//If not student
	if(!$account->is_student()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Check choices made
	if(!isset($_POST['choice_1']) || !isset($_POST['choice_2']) || !isset($_POST['choice_3'])){
		header("location: make_choices.php?err=0");
		exit();
	}elseif($_POST['choice_1'] == $_POST['choice_2'] || $_POST['choice_1'] == $_POST['choice_3'] || $_POST['choice_2'] == $_POST['choice_3']){
		header("location: make_choices.php?err=1");
		exit();
	}
	
	$err = "";
	
	if(isset($_GET['err'])){
		switch($_GET['err']){
			case 0:
				$err = "Unexpected error.";
				break;
			
			case 1:
				$err = "Duplicate choices are not allowed.";
				break;
			
			case 2:
				$err = "Invalid choice made.";
				break;
			
			
			default:
				$err = "";
				break;
		}
	}
	
	$projects = get_project([$_POST['choice_1'], $_POST['choice_2'], $_POST['choice_3']]);
	
	//Ensure there are 3 valid choices
	if(count($projects) != 3){
		header("location: make_choices.php?err=2");
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Review choices</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
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
					<?= get_major($account->get_majors())['name'] ?>
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
		</table>
		<br />
		<?php
			$choice = 1;
			
			foreach($projects as $id => $project){
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
							<?= $project['proj_id'] ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Name:
						</td>
						<td>
							<?= $project['name'] ?>
						</td>
					</tr>
					<tr>
						<td style='width:25%;'>
							Description:
						</td>
						<td>
							<?= nl2br($project['description']) ?>
						</td>
					</tr>
				</table>
				<br />
		<?php
			}
		?>
		<form action='exec_choices.php' method='POST'>
			<label><input type='checkbox' id='confirm_details' value='0' required/> I have checked and confirmed my personal details.</label>
			<br />
			<label><input type='checkbox' id='confirm_choices' value='1' required/> I have checked and confirmed my 3 project preferences.</label>
			<br />
			<input type='hidden' name='choice_1' value='<?= $_POST['choice_1'] ?>'/>
			<input type='hidden' name='choice_2' value='<?= $_POST['choice_2'] ?>'/>
			<input type='hidden' name='choice_3' value='<?= $_POST['choice_3'] ?>'/>
			<input type='submit' name='confirm_choice' value='Confirm Choices'>
		</form>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>