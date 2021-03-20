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
	
	str_clean($_POST['year']);
	str_clean($_POST['quarter']);
	
	//If no file uplaoded (or multiple)
	if(count($_FILES) != 1 && !isset($_FILES['csv'])){
		header("location: import.php?y={$_POST['year']}&q={$_POST['quarter']}&err=1");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$headers = [];
	$students = [];
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
					$students[$row][$headers[$col]] = $data[$col];
				}
			}
			
			$row++;
		}
		
		unset($row);
		
		fclose($handle);
	}else{
		//If not CSV
		header("location: import.php?y={$_POST['year']}&q={$_POST['quarter']}&err=2");
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
			Students for: Year <?= $_POST['year'] ?>, Quarter <?= $_POST['quarter'] ?>
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
					foreach($students as $student){
				?>
						<tr>
							<?php
								foreach($headers as $header){
							?>
									<td>
										<?= $student[$header] ?>
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
			<label><input type='checkbox' id='confirm_details' value='0' required/> I have checked and confirmed the student details to be imported.</label>
			<br />
			<input type='hidden' name='students' value='<?= json_encode($students) ?>'/>
			<input type='hidden' name='year' value='<?= $_POST['year'] ?>'/>
			<input type='hidden' name='quarter' value='<?= $_POST['quarter'] ?>'/>
			<input type='submit' name='import' value='Import Students'>
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