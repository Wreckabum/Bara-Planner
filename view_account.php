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
	
	//For non-admins, only allow viewing of own accounts
	if(!$account->is_admin() && $account->sim_id != $_GET['a']){
		header("location: home.php");
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
		<title>View Account - <?= $view_account->get_name() ?></title>
		<link rel='stylesheet' href='include/css/main.css'>
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_account' class='basic_table container' style='width:auto;'>
			<tr>
				<td colspan='2'>
					<?= $view_account->get_name() ?>'s Profile
				</td>
			</tr>
			<tr>
				<td>
					Name:
				</td>
				<td>
					<?= ucfirst($view_account->get_name()) ?>
				</td>
			</tr>
			<tr>
				<td>
					SIM E-mail:
				</td>
				<td>
					<?= $view_account->get_sim_email() ?>
				</td>
			</tr>
			<tr>
				<td>
					Personal E-mail:
				</td>
				<td>
					<?= $view_account->get_personal_email() ?>
				</td>
			</tr>
			<tr>
				<td>
					Phone:
				</td>
				<td>
					<?= $view_account->get_phone() ?>
				</td>
			</tr>
			<tr>
				<td>
					Type:
				</td>
				<td>
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
							<td>
								<?php						
									$major = get_major($view_account->get_majors());
								?>
								<?= $major['id'] ?> - <?= $major['name'] ?>
							</td>
						</tr>
						<tr>
							<td>
								Year:
							</td>
							<td>
								<?= $view_account->get_year() ?>
							</td>
						</tr>
						<tr>
							<td>
								Quarter:
							</td>
							<td>
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
							<td>
								<?php						
									$majors = get_major($view_account->get_majors());
									
									foreach($majors as $id => $major){
								?>
										<?= $id ?> - <?= $major['name'] ?>
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
					$go_to = "edit_admin";
				}elseif($view_account->is_faculty()){
					$go_to = "edit_faculty";
				}elseif($view_account->is_student()){
					$go_to = "edit_student";
				}
				
				//If own account
				if($account->sim_id == $view_account->sim_id){
			?>
					<tr>
						<td colspan='2'>
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
						<td>
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
				<td colspan='2'>
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