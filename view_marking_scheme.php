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
	$marking_scheme = json_decode($semester_details->marking_scheme);
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Marking Scheme - Year <?= $_GET['y'] ?>, Quarter <?= $_GET['q'] ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<style>
			.basic_table td {
				text-align: center;
				white-space: nowrap;
			}
			
			.basic_table td.empty {
				background-color: #E4E4E4;
			}
			
			.supervisor, .assessor, .total, .average {
				width: 125px;
			}
			
			.supervisor {
				background-color: #E6ffE6;
			}
			
			.assessor {
				background-color: #CFCFFF;
			}
			
			.penalty {
				background-color: #FFCECE;
			}
		</style>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<table class='basic_table' style='width:auto%;'>
			<tr>
				<td colspan='2'>
					Item
				</td>
				<td>
					Assignment Items & Format
				</td>
				<td>
					Week Due
				</td>
				<td>
					Max Marks (%)
				</td>
				<td>
					Supervisor
				</td>
				<td>
					Assessor
				</td>
				<td>
					Total
				</td>
				<td>
					Average
				</td>
			</tr>
			<?php
				foreach($marking_scheme->faculty as $section => $section_details){
			?>
					<tr>
						<td colspan='2'>
							<?= $section ?>
						</td>
						<td>
							<?= $section_details->desc ?>
						</td>
						<?php
							if($section_details->desc == "Penalty"){
						?>
									<td class='due penalty'>-</td>
									<td class='weight penalty'>-%</td>
									<td class='supervisor penalty'></td>
									<td class='assessor penalty'></td>
									<td class='total penalty'></td>
									<td class='average penalty'></td>
								</tr>
						<?php
							}else{
						?>
								<td class='due' <?= ((isset($section_details->parts)) ? "rowspan='". (count((array)$section_details->parts) + 1) ."'" : "") ?>>
									<?= $section_details->week_due ?>
								</td>
								<?php
									if(isset($section_details->parts)){
								?>
											<td colspan='5' class='empty'>-</td>
										</tr>
										<?php
											foreach($section_details->parts as $part => $part_details){
										?>
												<tr>
													<td class='empty'>-</td>
													<td>
														<?= $part ?>
													</td>
													<td>
														<?= $part_details->desc ?>
													</td>
													<td class='weight'>
														<?= $part_details->weight ?>%
													</td>
													<td class='supervisor'></td>
													<td class='assessor'></td>
													<td class='total'></td>
													<td class='average'></td>
												</tr>
										<?php
											}
										?>
								<?php
									}else{
								?>
											<td class='weight'>
												<?= $section_details->weight ?>%
											</td>
											<td class='supervisor'></td>
											<td class='assessor'></td>
											<td class='total'></td>
											<td class='average'></td>
										</tr>
			<?php
									}
							}
				}
			?>
			<tr>
				<td colspan='2' class='empty'>-</td>
				<td>
					Individual Student
				</td>
				<td class='empty'>-</td>
				<td>
					<?= $marking_scheme->student->weight ?>%
				</td>
				<td colspan='4' class='empty'>-</td>
			</tr>
		</table>
		<br />
		<a href='semester_details.php'>Back to all semester details</a>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
	<script>
		
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