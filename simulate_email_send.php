<?php
	//Initialize the session
	session_start();
	
	//If already logged in
	if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
		header("location: home.php");
		exit();
	}
	
	//Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	// On form submission
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		header("location: index.php");
		exit();
	}
?>
<!-- For simplicity sake, form is from https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php -->
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Reset Password</title>
		<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css'>
		<style type='text/css'>
			body{ font: 14px sans-serif; }
			.wrapper{ width: 350px; padding: 20px; }
		</style>
	</head>
	<body>
		<div class='wrapper' style='padding:0 20px;'>
			<h2>Reset Password</h2>
			<form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<div class='form-group'>
					<label>This page simulate email send to user.</label>
				</div>
				<div class='form-group'><label>The bottom is the new password after reset.</label></div>
			<div class='form-group'><label>Password: </label><span><?php echo $_GET['password']; ?></span></div>
				<div class='form-group'>
					<input type='submit' name='reset' class='btn btn-primary' value='Go Login Page'>
				</div>
			</form>
		</div>
		<br />
	</body>
</html>