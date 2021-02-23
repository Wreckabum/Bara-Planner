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
		
	<style>
		/*Add a black background color to the top navigation */
		.topnav {
		  background-color: #333;
		  overflow: hidden;
		}

		/* Style the links inside the navigation bar */
		.topnav a {
		 float: left;
		  color: #f2f2f2;
		  text-align: center;
		  padding: 14px 16px;
		  text-decoration: none;
		 font-size: 17px;
		}

		/* Change the color of links on hover */
		.topnav a:hover {
		 background-color: #ddd;
		  color: black;
		}

		/* Adda color to the active/current link */
		.topnava.active {
		  background-color: #4CAF50;
		  color: white;
		}
		
		
		
		table {
		  border-collapse: collapse;
		  width: 100%;
		}

		th, td {
		  padding: 8px;
		  text-align: left;
		  border-bottom: 1px solid #ddd;
		}

		tr:hover {background-color:#f5f5f5;}
	</style>
	</head>
	<body>
	<div class="topnav">
	  <a class="active" href="home.php">Home</a>
	  <a href="#news">News</a>
	  <a href="#contact">Contact</a>
	  <a href="#about">About</a>
	</div> 
	
		<?php include("include/templates/header.php"); ?>
		

		<table border='0';>
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
