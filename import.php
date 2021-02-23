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
<body>
<div class="container">
	<h1>Upload Excel File</h1>
	<form method="POST" action="upload_csv.php" enctype="multipart/form-data">
		<div class="form-group">
			<label>Choose File</label>
			<input type="file" name="csv" class="form-control" />
		</div>
		<div class="form-group">
			<button type="submit" name="submit" class="btn btn-success">Upload</button>
		</div>
	</form>
</div>
</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
