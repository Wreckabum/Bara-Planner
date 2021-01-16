<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Test</title>
	</head>
	<body>
		<?php
			require_once("include/funcs/sql_funcs.php");
			
			sql_connect();
			
			//Testing accounts
			$vader = new Faculty(1);
			$luke = new Student(2);
			$leia = new Student(3);
			
			var_dump($vader);
			var_dump($luke);
			var_dump($leia);
			
			//Close connection
			@mysqli_close($GLOBALS['mysql_link']);
		?>
		<table style='width:20%; border-collapse:collapse; border:1px #000 solid; padding-left:7px;'>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Name:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $vader->get_full_name() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Date of Birth:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $vader->get_dob() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					E-Mail:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $vader->get_email() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Account Type:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $vader->get_account_type() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Major:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $vader->get_majors(true) ?>
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
					<?= $luke->get_full_name() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Date of Birth:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $luke->get_dob() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					E-Mail:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $luke->get_email() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Account Type:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $luke->get_account_type() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Major:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $luke->get_majors() ?>
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
					<?= $leia->get_full_name() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Date of Birth:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $leia->get_dob() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					E-Mail:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $leia->get_email() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Account Type:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $leia->get_account_type() ?>
				</td>
			</tr>
			<tr>
				<td style='width:30%; border:1px #000 solid; padding-left:7px;'>
					Major:
				</td>
				<td style='width:70%; border:1px #000 solid; padding-left:7px;'>
					<?= $leia->get_majors() ?>
				</td>
			</tr>
		</table>
	</body>
</html>