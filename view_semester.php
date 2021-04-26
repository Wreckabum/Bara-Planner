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
				$err = "Deadline is not yet set.";
				break;
			
			case 2:
				$err = "Date has passed.";
				break;
			
			case 3:
				$err = "Invalid date.";
				break;
			
			case 4:
				$err = "Already exists.";
				break;
			
			case 9:
				$err = "Successfully updated.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
	
	//Update any potentially missing deadlines
	add_missing_deadlines();
	
	$semester_details = get_semester($_GET['y'], $_GET['q']);
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View semester - Year <?= $_GET['y'] ?>, Quarter <?= $_GET['q'] ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<table id='update_semester' class='basic_table' style='width:auto;'>
			<tr>
				<td colspan='2'>
					Semester details
				</td>
			</tr>
			<tr>
				<td >
					Year
				</td>
				<td>
					<?= $semester_details->year ?>
				</td>
			</tr>
			<tr>
				<td>
					Quarter
				</td>
				<td>
					<?= $semester_details->quarter ?>
				</td>
			</tr>
			<tr>
				<td>
					Deadline
				</td>
				<td>
					<?= ((is_null($semester_details->deadline)) ? "" : date("d M Y", strtotime($semester_details->deadline))) ?>
				</td>
			</tr>
			<tr>
				<td>
					Details
				</td>
				<td style='width:600px;'>
					<?= nl2br($semester_details->details) ?>
				</td>
			</tr>
			<tr>
				<td>
					<a href='edit_semester.php?y=<?= $semester_details->year ?>&q=<?= $semester_details->quarter ?>'>
						Update
					</a>
				</td>
				<td>
					<a id='delete_link' href='#' onClick="show_delete();">
						Delete Semester
					</a>
					<form id='delete_form' action='delete_semester.php' method='POST' style='display:none;'>
						<input type='hidden' name='year' value='<?= $semester_details->year ?>' />
						<input type='hidden' name='quarter' value='<?= $semester_details->quarter ?>' />
						<input type='checkbox' id='confirm_checkbox' name='delete_confirmation' required />
						<input type='submit' name='delete_semester' id='delete_submit' value='Delete' disabled/>
					</form>
				</td>
			</tr>
		</table>
		<br />
		<a href='semester_details.php'>Back to all semester details</a>
		<br />
		<a href='home.php'>Back to main page</a>
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
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>