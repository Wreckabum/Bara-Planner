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
	
	//For students, only allow viewing of own accounts
	if($account->is_student() && $account->id != $_GET['a']){
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
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_account' class='basic_table' style='width:auto;'>
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
					E-mail:
				</td>
				<td>
					<?= $view_account->get_email() ?>
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
			?>
		</table>
		<br />
		<?php
			if(($account->is_admin() && !$view_account->is_admin()) || ($account->is_super() && $view_account->is_admin()) || $account->id == $view_account->id){
				$go_to = "";
				
				if($view_account->is_admin()){
					$go_to = "edit_admin";
				}elseif($view_account->is_faculty()){
					$go_to = "edit_faculty";
				}elseif($view_account->is_student()){
					$go_to = "edit_student";
				}
		?>
				<a href="<?= $go_to ?>?a=<?= $view_account->id ?>">Edit Account</a>
				<br />
		<?php
			}
		?>
		<a href='home.php'>Back to main page</a>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>