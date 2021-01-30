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
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<?php
			require_once("include/funcs/sql_funcs.php");
			
			sql_connect();
			
			//Testing accounts
			$super = new Faculty(1);
			$admin = new Faculty(2);
			$prof = new Faculty(10);
			$ft = new Student(100);
			$pt = new Student(1000);
			
			//Close connection
			@mysqli_close($GLOBALS['mysql_link']);
		?>
		<table style='width:20%; border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Name:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $super->get_full_name() ?>
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
					E-Mail:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $super->get_email() ?>
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
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Major:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $super->get_majors(true) ?>
				</td>
			</tr>
		</table>
		<br /><table style='width:20%; border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Name:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $admin->get_full_name() ?>
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
					E-Mail:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $admin->get_email() ?>
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
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Major:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $admin->get_majors(true) ?>
				</td>
			</tr>
		</table>
		<br />
		<table style='width:20%; border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Name:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $prof->get_full_name() ?>
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
					E-Mail:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $prof->get_email() ?>
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
		<br />
		<table style='width:20%; border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Name:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $ft->get_full_name() ?>
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
					E-Mail:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $ft->get_email() ?>
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
		<br />
		<table style='width:20%; border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Name:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $pt->get_full_name() ?>
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
					E-Mail:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $pt->get_email() ?>
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
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
