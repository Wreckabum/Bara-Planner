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
	
	//If deadline has passed
	if(!$account->can_make_choice()){
		header("location: view_choices.php");
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
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?><div class='container'>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<div id='projects_container' style='display:inline-block; width:48%; vertical-align:top;'>
			<?php
				foreach($all_projects as $project){
					if($project->get_year() == $account->get_year() && $project->get_quarter() == $account->get_quarter()){
			?>
						<table id='proj_<?= $project->id ?>_<?= $project->proj_id ?>' class='basic_table' style='width:100%;'>
							<tr>
								<td colspan='2'>
									(<?= $project->id ?>) <?= $project->proj_id ?>
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
						<?php
							for($i = 1; $i <= 3; $i++){
						?>
								<tr>
									<td style='width:5%; padding:5px; text-align:center;'>
										#<?= $i ?>
									</td>
									<td style='width:95%; padding:5px;'>
										<select name='choice_<?= $i ?>' style='width:97%;' required>
											<?php
												foreach($all_projects as $project){
													if($project->get_year() == $account->get_year() && $project->get_quarter() == $account->get_quarter()){
											?>
														<option value='<?= $project->id ?>'>(<?= $project->id ?>) <?= $project->get_name() ?></option>
											<?php
													}
												}
											?>
										</select>
									</td>
								</tr>
						<?php
							}
						?>
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
		</div>
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