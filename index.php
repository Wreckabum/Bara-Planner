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
	
	//Define variables and initialize with empty values
	$email = "";
	$password = "";
	$email_error = "";
	$password_error = "";
	$login_error = "";
	
	//Connect to database
	sql_connect();
	
	// On form submission
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		//Connect to database
		sql_connect();
		
		//Extra layer of checks
		$email = str_clean($_POST["email"]);
		$password = str_clean($_POST["password"]);
		
		//If email is empty
		if(empty($email)){
			$email_error = "Please enter your SIM email.";
		}else{
			$email = trim($email);
		}
		
		//Check if password is empty
		if(empty($password)){
			$password_error = "Please enter your password.";
		}else{
			$password = trim($password);
		}
		
		if(!empty($email) && !empty($password)){
			$query = db_query("SELECT * FROM `accounts` WHERE `sim_email` = '{$email}' AND password = '{$password}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			if($result != NULL){
				//Start a new session
				session_start();
				
				//Store data in session variables
				$_SESSION["loggedin"] = true;
				$_SESSION["id"] = $result['sim_id'];							
				
				//Redirect user to main landing page
				header("location: home.php");
			}else{
				$login_error = "Login failed.";
			}
		}
		
		//Close connection
		@mysqli_close($GLOBALS['mysql_link']);
	}
?>
<!-- For simplicity sake, form is from https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php -->
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Login</title>
		<link rel='stylesheet' href='include/css/bootstrap.min.css'>
		<link rel='stylesheet' href='include/css/index.css'>
	</head>
	<body>
		<ul class="cb-slideshow">
			<li><span>Image 01</span></li>
			<li><span>Image 02</span></li>
			<li><span>Image 03</span></li>
			<li><span>Image 04</span></li>
			<li><span>Image 05</span></li>
			<li><span>Image 06</span></li>
		</ul>
		<div class='container'>
			<div class='wrapper' style='padding:0 20px;'>
				<h2>Login</h2>
				<p>Please fill in your credentials to login.</p>
				<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
					<div class='form-group'>
						<label><strong>Username</strong></label>
						<input type='text' name='email' class='form-control' value='<?= $email; ?>'>
						<span class='help-block <?= (!empty($email_error)) ? 'has-error' : ''; ?>'><?= $email_error; ?></span>
					</div>	
					<div class='form-group'>
						<label><strong>Password</strong></label>
						<input type='password' name='password' class='form-control'>
						<span class='help-block <?= (!empty($email_error)) ? 'has-error' : ''; ?>'><?= $password_error; ?></span>
					</div>
					<span class='help-block'><?= $login_error; ?></span>
					<div class='form-group'>
						<input type='submit' name='login' class='btn btn-primary' value='Login'>
					</div>
					<sup>*If you have forgotten your password, please contact an SIM administrator to have it reset.</sup>
				</form>
			</div>
		</div>
	</body>
</html>