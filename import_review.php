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
	
	//If not admin
	if(!$account->is_admin()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	str_clean($_POST['type']);
	str_clean($_POST['year']);
	str_clean($_POST['quarter']);
	
	//If no file uplaoded (or multiple)
	if(count($_FILES) != 1 && !isset($_FILES['csv'])){
		header("location: import.php?t={$_POST['type']}&y={$_POST['year']}&q={$_POST['quarter']}&err=1");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$extension = pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION);
	$accepted_extensions = ["csv", "xls", "xlsx", "xlsm"];
	
	//Check if accepted extension
	if(!in_array($extension, $accepted_extensions)){
		header("location: import.php?t={$_POST['type']}&y={$_POST['year']}&q={$_POST['quarter']}&err=2");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Check type
	if($_POST['type'] != "student" && $_POST['type'] != "faculty"){
		header("location: import.php?t={$_POST['type']}&y={$_POST['year']}&q={$_POST['quarter']}&err=3");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$headers = [];
	$import_data = [];
	$tmpName = $_FILES["csv"]["tmp_name"];	
	
	//Ensure file is csv
	if(($handle = fopen($tmpName, "r")) !== FALSE){
		//Allow large files
		set_time_limit(0);
		
		$row = 0;
		
		while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){
			$col_count = count($data);
			
			for($col = 0; $col < $col_count; $col++){
				if($row == 0){
					$headers[] = $data[$col];
				}else{
					$import_data[$row][$headers[$col]] = $data[$col];
				}
			}
			
			$row++;
		}
		
		unset($row);
		
		fclose($handle);
	}else{
		//If cannot open file
		header("location: import.php?t={$_POST['type']}&y={$_POST['year']}&q={$_POST['quarter']}&err=2");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Review imported data</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='stylesheet' href='include/css/dataTables.min.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<script src='include/js/dataTables.min.js'></script>
	</head>
	<body>
		<?php include('include/templates/header.php'); ?>
		<h4>
			<?php
				if($_POST['type'] == "student"){
			?>
					Students for: Year <?= $_POST['year'] ?>, Quarter <?= $_POST['quarter'] ?>
			<?php
				}else{
			?>
					Faculty members
			<?php
				}
			?>
		</h4>
		<table id='filter_table' class='display'>
			<thead>
				<tr>
					<?php
						foreach($headers as $header){
					?>
							
							<th>
								<?= $header ?>
							</th>
					<?php
						}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach($import_data as $row){
				?>
						<tr>
							<?php
								foreach($headers as $header){
							?>
									<td>
										<?= $row[$header] ?>
									</td>
							<?php
								}
							?>
						</tr>
				<?php
					}
				?>
			</tbody>
		</table>
		<br />
		<form action='exec_import.php' method='POST'>
			<label><input type='checkbox' id='confirm_details' value='0' required/> I have checked and confirmed the details above for import.</label>
			<br />
			<input type='hidden' name='import_data' value='<?= json_encode($import_data) ?>'/>
			<input type='hidden' name='type' value='<?= $_POST['type'] ?>'/>
			<input type='hidden' name='year' value='<?= $_POST['year'] ?>'/>
			<input type='hidden' name='quarter' value='<?= $_POST['quarter'] ?>'/>
			<input type='submit' name='import' value='Import'>
		</form>
	</body>
	<script>
		$("#filter_table").DataTable({
			/* Disable initial sort */
			"aaSorting": [],
			"paging": false
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>