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
	
	// On form submission
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		if(isset($_POST['forgetPassword'])){
			header("location: forgetPassword.php");
			exit();
		}

		//Connect to database
		sql_connect();
		
		//Extra layer of checks
		$email = str_clean($_POST["email"]);
		$password = str_clean($_POST["password"]);
		
		//If email is empty
		if(empty($email)){
			$email_error = "Please enter email.";
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
				$_SESSION["id"] = $result['id'];							
				
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
		<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css'>
		<style type='text/css'>
			body{ font: 14px sans-serif; }
			.wrapper{ width: 350px; padding: 20px; }
		</style>
	</head>
	<body>
		<div class='wrapper' style='padding:0 20px;'>
			<h2>Login</h2>
			<p>Please fill in your credentials to login.</p>
			<form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<div class='form-group <?php echo (!empty($email_error)) ? 'has-error' : ''; ?>'>
					<label>Username</label>
					<input type='text' name='email' class='form-control' value='<?php echo $email; ?>'>
					<span class='help-block'><?php echo $email_error; ?></span>
				</div>	
				<div class='form-group <?php echo (!empty($password_error)) ? 'has-error' : ''; ?>'>
					<label>Password</label>
					<input type='password' name='password' class='form-control'>
					<span class='help-block'><?php echo $password_error; ?></span>
				</div>
				<span class='help-block'><?php echo $login_error; ?></span>
				<div class='form-group'>
					<input type='submit' name='login' class='btn btn-primary' value='Login'>
					<input type='submit' name='forgetPassword' class='btn btn-primary' value='Forget Password'>
				</div>
			</form>
		</div>
		<br />
		<form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
			<input type='hidden' name='email' value='carve.delah@mymail.sim.edu.sg'>
			<input type='hidden' name='password' value='FfI0M2Na'>
			<input type='submit' value='Super Admin'>
		</form>
		<br />
		<form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
			<input type='hidden' name='email' value='abiga.attre@mymail.sim.edu.sg'>
			<input type='hidden' name='password' value='XiRSszCDo9iS'>
			<input type='submit' value='Admin'>
		</form>
		<br />
		<form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
			<input type='hidden' name='email' value='erwin.haref@mymail.sim.edu.sg'>
			<input type='hidden' name='password' value='sZoS3rWaSJ'>
			<input type='submit' value='Faculty'>
		</form>
		<br />
		<form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
			<input type='hidden' name='email' value='cleme.tanby@mymail.sim.edu.sg'>
			<input type='hidden' name='password' value='OQGJrSSz6D'>
			<input type='submit' value='Full-time Student'>
		</form>
		<br />
		<form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
			<input type='hidden' name='email' value='delan.abell@mymail.sim.edu.sg'>
			<input type='hidden' name='password' value='1FS3sT1bmx'>
			<input type='submit' value='Part-time Student'>
		</form>
	</body>
</html>