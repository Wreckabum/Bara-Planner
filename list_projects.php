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
		

		<table border='0'; class='project_table' >
		<?php
		$test = get_all_projects();
		$counter = 0;
		foreach ($test as $data) {
			
        ?>
        <tr>
			<th>Project ID</th>
			<th>Name</th>
			<th>Description</th>
			<th>Year</th>
			<th>Quater</th>
		</tr>
		<tr>
            <td id="fcase1"><?php echo $data['proj_id']; ?></td>
            <td id="creat1"><?php echo $data['name']; ?></td>
            <td id="ftype1"><?php echo $data['description']; ?></td>
			<td id="creat1"><?php echo $data['year']; ?></td>
			<td id="creat1"><?php echo $data['quarter']; ?></td>
        </tr>
		<?php
			
		}		 
        ?>
		</table>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
