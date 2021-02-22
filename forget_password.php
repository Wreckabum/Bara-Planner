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
	$email_error = "";
	$reset_error = "";
	
	// On form submission
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		

		//Connect to database
		sql_connect();
		
		//Extra layer of checks
		$email = str_clean($_POST["email"]);
		
		//If email is empty
		if(empty($email)){
			$email_error = "Please enter email.";
		}else{
			$email = trim($email);
		}
		
		if(!empty($email)){
			$query = db_query("SELECT * FROM `accounts` WHERE `email` = '{$email}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			if($result != NULL){
				
				//Store data in session variables
				$id = $result['id'];							
				$password = generate_password();
				$update = db_query("UPDATE `accounts` SET `password` = '{$password}' WHERE `id` = '{$id}';");
				if($update){
				//Redirect user to main landing page
					echo
		            ("<script LANGUAGE='JavaScript'>
							window.alert('Password Reset Successfully!\\nPlease login with new password.');
							window.location.href='simulate_email_send.php?password=".$password."';
						</script>");
				}
				else{
					$reset_error = "Password Reset Failed! Please try again later.";
				}
			}else{
				$reset_error = "User does not exist.";
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
			<p>Please fill in your email to reset password.</p>
			<form action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<div class='form-group <?php echo (!empty($email_error)) ? 'has-error' : ''; ?>'>
					<label>Username</label>
					<input type='text' name='email' class='form-control' value='<?php echo $email; ?>'>
					<span class='help-block'><?php echo $email_error; ?></span>
				</div>	
				<span class='help-block'><?php echo $reset_error; ?></span>
				<div class='form-group'>
					<input type='submit' name='reset' class='btn btn-primary' value='Reset Password'>
				</div>
			</form>
		</div>
		<br />
	</body>
</html>