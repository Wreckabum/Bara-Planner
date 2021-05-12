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
	
	$err = "";
	
	if(isset($_GET['err'])){
		switch($_GET['err']){
			case 1:
				$err = "No file detected.";
				break;
			
			case 2:
				$err = "Please upload a valid file.";
				break;
			
			case 3:
				$err = "Please choose valid type.";
				break;
			
			case 4:
				$err = "Students cannot have multiple majors.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Import students</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include('include/templates/header.php'); ?><div class='container'>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<div class='container'>
			<h1>Upload CSV File</h1>
			<form method='POST' action='import_review.php' enctype='multipart/form-data'>
				<div class='form-group'>
					Choose File:
					<br />
					<input type='file' name='csv' class='form-control' />
				</div>
				<div class='form-group'>
					Account Type:
					<br />
				<select id='account_type' name='type' class='form-control' required>
					<option value='student' <?= ((isset($_GET['t'])) ? (($_GET['t'] == "student") ? "selected" : "") : "") ?>>Student</option>
					<option value='faculty' <?= ((isset($_GET['t'])) ? (($_GET['t'] == "faculty") ? "selected" : "") : "") ?>>Faculty</option>
					<option value='project' <?= ((isset($_GET['t'])) ? (($_GET['t'] == "project") ? "selected" : "") : "") ?>>Project</option>
				</select>
				</div>
				<div id='year' class='form-group'>
					Year:
					<br />
					<input type='number' name='year' value='<?= ((isset($_GET['y'])) ? $_GET['y'] : date('Y')) ?>' maxlength='4' class='form-control' required />
				</div>
				<div id='quarter' class='form-group'>
					Quarter:
					<br />
					<input type='number' name='quarter' value='<?= ((isset($_GET['q'])) ? $_GET['q'] : ceil(date('n') / 3)) ?>' min='1' max='4' class='form-control' required />
				</div>
				<div class='form-group'>
					<button type='submit' class='btn btn-success'>Upload</button>
				</div>
			</form>
		</div>
		<br />
		<a href='home.php'>Back to main page</a>
        </div>
	</body>
</html>
<script>
	$("#account_type").on("change", function(){
		if($(this).val() == "student" || $(this).val() == "project"){
			$("#year").show();
			$("#quarter").show();
		}else{
			$("#year").hide();
			$("#quarter").hide();
		}
	});
</script>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>