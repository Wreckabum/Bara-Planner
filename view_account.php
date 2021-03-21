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
	
	//Check permissions
	if(
		!$account->is_admin() && //Not admin
		$account->sim_id != $_GET['a'] && //Not own account
		!check_same_group_member($account->sim_id, $_GET['a']) && //Not member of same group
		!check_same_group_faculty($account->sim_id, $_GET['a']) //Not supervisor/assessor of group
	){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	try{
		$view_account = get_account($_GET['a']);
	}catch(Exception $e){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title><?= (($account->sim_id == $view_account->sim_id) ? "Your account" : "View Account - {$view_account->get_name()}") ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_account' class='basic_table container' style='width:auto;'>
			<tr>
				<td colspan='3'>
					<?= (($account->sim_id == $view_account->sim_id) ? "Your account" : "{$view_account->get_name()}'s Profile") ?>
				</td>
			</tr>
			<tr>
				<td>
					SIM ID:
				</td>
				<td colspan='2'>
					<?= ucfirst($view_account->sim_id) ?>
				</td>
			</tr>
			<tr>
				<td>
					UOW ID:
				</td>
				<td colspan='2'>
					<?= $view_account->uow_id ?>
				</td>
			</tr>
			<tr>
				<td>
					Name:
				</td>
				<td colspan='2'>
					<?= ucfirst($view_account->get_name()) ?>
				</td>
			</tr>
			<tr>
				<td>
					SIM E-mail:
				</td>
				<td colspan='2'>
					<?= $view_account->get_sim_email() ?>
				</td>
			</tr>
			<tr>
				<td>
					Personal E-mail:
				</td>
				<?php
					if(
						$view_account->show_email() || 
						$account->is_admin() || 
						$account->is_faculty() || 
						$account->sim_id == $view_account->sim_id
					){
				?>
						<td>
							<?= $view_account->get_personal_email() ?>
						</td>
						<td style='background-color:<?= (($view_account->show_email()) ? "#C7E8C7" : "#E28D8D") ?>'>
							<?= (($view_account->show_email()) ? "Shown" : "Hidden") ?>
						</td>
				<?php
					}else{
				?>
						<td colspan='2'>
							-
						</td>
				<?php
					}
				?>
			</tr>
			<tr>
				<td>
					Phone:
				</td>
				<?php
					if(
						$view_account->show_phone() || 
						$account->is_admin() || 
						$account->is_faculty() || 
						$account->sim_id == $view_account->sim_id
					){
				?>
						<td>
							<?= $view_account->get_phone() ?>
						</td>
						<td style='background-color:<?= (($view_account->show_phone()) ? "#C7E8C7" : "#E28D8D") ?>'>
							<?= (($view_account->show_phone()) ? "Shown" : "Hidden") ?>
						</td>
				<?php
					}else{
				?>
						<td colspan='2'>
							-
						</td>
				<?php
					}
				?>
			</tr>
			<tr>
				<td>
					Type:
				</td>
				<td colspan='2'>
					<?= $view_account->get_account_type() ?>
				</td>
			</tr>
			<?php
				//For non admin acocunts, show extra details
				if(!$view_account->is_admin()){
					if($view_account->is_student()){
			?>
						<tr>
							<td>
								Major:
							</td>
							<td colspan='2'>
								<?php						
									$major = get_major($view_account->get_majors());
								?>
								<?= $major->id ?> - <?= $major->get_name() ?>
							</td>
						</tr>
						<tr>
							<td>
								Year:
							</td>
							<td colspan='2'>
								<?= $view_account->get_year() ?>
							</td>
						</tr>
						<tr>
							<td>
								Quarter:
							</td>
							<td colspan='2'>
								<?= $view_account->get_quarter() ?>
							</td>
						</tr>
			<?php
					}elseif($view_account->is_faculty()){
			?>
						<tr>
							<td>
								Majors:
							</td>
							<td colspan='2'>
								<?php						
									$majors = get_all_majors();
									
									foreach($majors as $major){
								?>
										<?= $major->id ?> - <?= $major->get_name() ?>
										<br />
								<?php
									}
								?>
							</td>
						</tr>
			<?php
					}
				}
				
				$go_to = "";
					
				if($view_account->is_admin()){
					$go_to = "edit_admin.php";
				}elseif($view_account->is_faculty()){
					$go_to = "edit_faculty.php";
				}elseif($view_account->is_student()){
					$go_to = "edit_student.php";
				}
				
				//If own account
				if($account->sim_id == $view_account->sim_id){
			?>
					<tr>
						<td colspan='3'>
							<a href="<?= $go_to ?>?a=<?= $view_account->sim_id ?>">
								Update
							</a>
						</td>
					</tr>
			<?php		
				}elseif(
					($account->is_admin() && !$view_account->is_admin()) || //Admin editing non-admin accounts
					($account->is_super() && $view_account->is_admin())  //Super admin editing admin accounts
				){
			?>
					<tr>
						<td>
							<a href="<?= $go_to ?>?a=<?= $view_account->sim_id ?>">
								Edit Account
							</a>
						</td>
						<td colspan='2'>
							<a id='delete_link' href='#' onClick="show_delete();">
								Delete Account
							</a>
							<form id='delete_form' action='delete_account.php' method='POST' style='display:none;'>
								<input type='checkbox' id='confirm_checkbox' name='delete_id' value='<?= $view_account->sim_id ?>' required/>
								<input type='submit' name='delete_account' id='delete_submit' value='Delete' disabled/>
							</form>
						</td>
					</tr>
			<?php
				}
			?>
			<tr>
				<td colspan='3'>
					<a href='home.php'>
						Back to main page
					</a>
				</td>
			</tr>
		</table>
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