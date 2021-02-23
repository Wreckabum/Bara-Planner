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
<html>
<head>
	<title>PHP import Excel data</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<?php include("include/templates/header.php"); ?>
</head>
<style>

</style>
<body>
<div class="container">
<?php  

if(isset($_POST['submit'])) {
	 
	$csv = array();
	$tmpName = $_FILES['csv']['tmp_name'];	
	// check the file is a csv
	if(($handle = fopen($tmpName, 'r')) !== FALSE) {
		// necessary if a large csv file
		set_time_limit(0);
		$row = 0;
		while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
			// number of fields in the csv
			$col_count = count($data);

			for($i=0;$i<$col_count;$i++){
				$csv[$row][$i] = $data[$i];
			}
	
			// inc the row
			$row++;
		}
		fclose($handle);
	}

	// out the data
	echo 'Sample data to be insert to Database:';
	echo '<table>';
	foreach ($csv as $r) {
		echo "<tr>";
		for($j=0;$j<$col_count;$j++){
			echo "<td>" . $r[$j] . "</td>";
		}
		echo "</tr>";
		// SQL insert statement
	}
	echo '</table>';
	
		
		
} else {
	echo '<span class="msg">Please upload excel file.</span>';
}

?>
</div>
</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
