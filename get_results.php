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
	
	//If student
	if($account->is_student()){
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
			
			default:
				$err = "";
				break;
		}
	}
	
	//Prompt for semester
	if(!isset($_GET['semester']) || !isset($_GET['type'])){
?>
		<!DOCTYPE html>
		<html lang='en'>
			<head>
				<meta charset='UTF-8'>
				<title>Choose the year/quarter and type</title>
				<link rel='stylesheet' href='include/css/main.css' />
				<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
			</head>
			<body>
				<?php include("include/templates/header.php"); ?>
				<div class='container'>				
					<form action='' method='GET'>
						<table id='choose_semester' class='basic_table' style='width:auto;'>
							<tr>
								<td colspan='3'>
									Select details
								</td>
							</tr>
							<tr>
								<td>
									Choose semester and type:
								</td>
								<td style='width:250px'>
									<select name='semester' style='width:97%;' required>
										<?php
											$available_semesters = get_all_semesters_quarters();
											
											foreach($available_semesters as $year => $quarter_array){
												foreach($quarter_array as $quarter){
										?>
													<option value='<?= $year ?>_<?= $quarter ?>'>Year <?= $year ?>, Quarter <?= $quarter ?></option>
										<?php
												}
											}
										?>
									</select>
								</td>
							</tr>
							<?php
								if($account->is_admin()){
							?>
									<tr>
										<td>
											Choose document to retrieve:
										</td>
										<td>
											<select name='type' style='width:97%;' required>
												<option value='1'>Results (CSV)</option>
												<option value='2'>Marking Sheets (PDF)</option>
											</select>
										</td>
									</tr>
							<?php
								}
							?>
							<tr>
								<td colspan='2'>
									<input type='submit' value='Select'>
								</td>
							</tr>
						</table>
					</form>
				</div>
			</body>
		</html>
<?php
	}else{
		//Semester selected
		str_clean($_GET['semester']);
		str_clean($_GET['type']);
		
		list($year, $quarter) = explode("_", $_GET['semester']);
		
		//Only admin can get results CSV
		if(!$account->is_admin()){
			$_GET['type'] = 2;
		}
		
		//Results CSV for admins
		if($_GET['type'] == 1){
			$all_groups = get_all_groups($year, $quarter);
			
			//Create temp directory if not exists
			$temp_name = "temp_". date("Y-m-d_H-i-s");
			
			//Make temporary directory
			mkdir($temp_name, 0755, true);
			
			//Create temporary CSV file
			$full_path = "{$temp_name}/{$temp_name}.csv";
			$handle = fopen($full_path, 'w');
			
			//Error if not writable
			if(!is_writable($full_path)){
				if(IS_LOCAL){
					exit("File '{$temp_name}' is not writable.");
				}else{
					return false;
				}
			}
			
			//If general file error
			if(!$handle){
				if(IS_LOCAL){
					exit("Unable to open file: {$temp_name}");
				}else{
					return false;
				}
			}
			
			$headers = [
				"Year", 
				"Quarter", 
				"Group Name", 
				"Project Name", 
				"Supervisor", 
				"Assessor", 
				"SIM ID", 
				"UOW ID", 
				"Name", 
				"SIM Email", 
				"Personal Email", 
				"Phone", 
				"Type", 
				"Major", 
				"Score"
			];
			
			//Insert headers
			if(fputcsv($handle, $headers) === false){
				//If failure
				if(IS_LOCAL){
					exit("Unable to write headers to file: {$temp_name}");
				}else{
					return false;
				}
			}
			
			foreach($all_groups as $group){
				foreach($group->get_members() as $member){
					$details = [
						$group->get_year(), 
						$group->get_quarter(), 
						$group->get_name(), 
						$group->get_project()->get_name(), 
						$group->get_supervisor()->get_name(), 
						$group->get_assessor()->get_name(), 
						$member->details->sim_id, 
						$member->details->uow_id, 
						$member->details->get_name(), 
						$member->details->get_sim_email(), 
						$member->details->get_personal_email(), 
						$member->details->get_phone(), 
						$member->details->get_account_type(), 
						$member->details->get_majors(), 
						((is_null($member->score) || $member->score == "null") ? "N/A" : $member->score)
					];
					
					//Write to file
					if(fputcsv($handle, $details) === false){
						//If failure
						if(IS_LOCAL){
							exit("Unable to write to file: {$temp_name}");
						}else{
							return false;
						}
					}
				}
			}
			
			//Point back to start of file and close
			fseek($handle, 0);
			
			//Download the file
			header('Content-Type: application/csv');
			header('Content-Disposition: attachment; filename="'. $year .'_'. $quarter .'_grades.csv";');
			readfile($full_path);
			
			//Remove the temporary file and direcotry
			register_shutdown_function("fclose", $handle);			
			register_shutdown_function("unlink", __DIR__ . "/{$full_path}");
			register_shutdown_function("rmdir", __DIR__ . "/{$temp_name}");
			
			exit();
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>