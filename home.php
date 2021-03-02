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
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Test</title>
		<link rel='stylesheet' href='include/css/main.css' />
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<?php
			//Testing accounts
			$super = get_account(10261688);
			$admin = get_account(10274631);
			$prof = get_account(10284263);
			$ft = get_account(10280958);
			$pt = get_account(10213701);
		?>
		<div class='container'>
			<div class='col-md-6' style='float:left'>
				<table class='profile_details' style=' border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Name:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $super->get_name() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Phone Number:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $super->get_phone() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							SIM E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $super->get_sim_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Personal E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $super->get_personal_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Account Type:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $super->get_account_type() ?>
						</td>
					</tr>
					<tr>
				</table>
				</div>
			<div class='col-md-6' style='float:left'>
				<table class='profile_details' style=' border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Name:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $admin->get_name() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Phone Number:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $admin->get_phone() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							SIM E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $admin->get_sim_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Personal E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $admin->get_personal_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Account Type:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $admin->get_account_type() ?>
						</td>
					</tr>
				</table>
			</div>
			<div  class='col-md-6' style='float:left'>
				<table class='profile_details' style=' border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Name:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $prof->get_name() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Phone Number:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $prof->get_phone() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							SIM E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $prof->get_sim_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Personal E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $prof->get_personal_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Account Type:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $prof->get_account_type() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Major:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $prof->get_majors(true) ?>
						</td>
					</tr>
				</table>
			</div>
			<div  class='col-md-6' style='float:left'>
				<table class='profile_details' style=' border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Name:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_name() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Phone Number:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_phone() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							SIM E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_sim_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Personal E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_personal_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Account Type:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_account_type() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Major:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_majors() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Year:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_year() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Quarter:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_quarter() ?>
						</td>
					</tr>
				</table>
            </div>

			<div  class='col-md-6' style='float:left'>
				<table class='profile_details' style=' border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Name:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $pt->get_name() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Phone Number:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $pt->get_phone() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							SIM E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $pt->get_sim_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Personal E-Mail:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $pt->get_personal_email() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Account Type:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $pt->get_account_type() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Major:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $pt->get_majors() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Year:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_year() ?>
						</td>
					</tr>
					<tr>
						<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
							Quarter:
						</td>
						<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
							<?= $ft->get_quarter() ?>
						</td>
					</tr>
				</table>
            </div>
		</div>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>