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
	
	$all_projects = get_all_projects();
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Choose preferred projects</title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<div id='projects_container' style='display:inline-block; width:48%; vertical-align:top;'>
			<?php
				foreach($all_projects as $id => $project){
					if((int)$project['year'] == $account->get_year() && (int)$project['quarter'] == $account->get_quarter()){
			?>
						<table id='proj_<?= $id ?>_<?= $project['proj_id'] ?>' class='basic_table' style='width:100%;'>
							<tr>
								<td colspan='2'>
									(<?= $id ?>) <?= $project['proj_id'] ?>
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
				}
			?>
		</div>
		<div id='choices_container' style='display:inline-block; width:48%; vertical-align:top;'>
			<div id='choices_inner_container'>
				<form action='review_choices.php' method='POST'>
					<table id='make_choices' class='basic_table' style='width:100%;'>
						<tr>
							<td colspan='2'>
								Your Choices
							</td>
						</tr>
						<tr>
							<td style='width:5%; padding:5px; text-align:center;'>
								#1
							</td>
							<td style='width:95%; padding:5px;'>
								<select name='choice_1' style='width:97%;' required>
									<?php
										foreach($all_projects as $id => $project){
											if((int)$project['year'] == $account->get_year() && (int)$project['quarter'] == $account->get_quarter()){
									?>
												<option value='<?= $id ?>'>(<?= $id ?>) <?= $project['name'] ?></option>
									<?php
											}
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td style='width:5%; padding:5px; text-align:center;'>
								#2
							</td>
							<td style='width:95%; padding:5px;'>
								<select name='choice_2' style='width:97%;' required>
									<?php
										foreach($all_projects as $id => $project){
											if((int)$project['year'] == $account->get_year() && (int)$project['quarter'] == $account->get_quarter()){
									?>
												<option value='<?= $id ?>'>(<?= $id ?>) <?= $project['name'] ?></option>
									<?php
											}
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td style='width:5%; padding:5px; text-align:center;'>
								#3
							</td>
							<td style='width:95%; padding:5px;'>
								<select name='choice_3' style='width:97%;' required>
									<?php
										foreach($all_projects as $id => $project){
											if((int)$project['year'] == $account->get_year() && (int)$project['quarter'] == $account->get_quarter()){
									?>
												<option value='<?= $id ?>'>(<?= $id ?>) <?= $project['name'] ?></option>
									<?php
											}
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan='2' style='padding:5px;'>
								<input type='submit' name='make_choice' value='Review Choices'>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
	<script>
		//Enable choices container scroll
		var original_height = $('#choices_inner_container').offset().top;
		
		$(window).scroll(function(){
			if($(window).scrollTop() >= original_height){
				$('#choices_inner_container').css('position', 'fixed').css('top', '0');
			}else if(original_height >= $(window).scrollTop()){
				$('#choices_inner_container').css('position', '').css('top', '');
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