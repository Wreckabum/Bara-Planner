<?php
	//Initialize the session
	session_start();
	
	//If already logged in
	if(isset($_SESSION["loggedin"])){
		header("location: home.php");
		exit();
	}
	
	//Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	//Connect to database
	sql_connect();
	
	
	
	//Define variables and initialize with empty values
	$email = "";
	$error_text = "";
	
	// On form submission
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		//Extra layer of checks
		$email = str_clean($_POST["email"]);
		
		//If email is empty
		if(empty($email)){
			$error_text = "Please enter E-Mail.";
		}
		
		if(!empty($email)){
			try{
				$to_reset = get_account_by_email($email);
				
				//Allow reset of self only
				if(!empty($to_reset)){
					//Set new password
					db_query("START TRANSACTION;");
					
					$password = generate_password();
					$update = $to_reset->set_password($password);
					
					if($update === true){
						$sender = "noreply@fyp.com";
						$subject = "Reset Password";
						$headers = "From:{$sender}\r\nCC:{$to_reset->get_personal_email()}";
						$message = 
							"<html lang='en'>
								<body>
									<table style='border-collapse:collapse; border:1px #666666 solid; padding:10px;'>
										<tr>
											<td colspan='2' style='background-color:#EEEEEE; border:1px #666666 solid; padding:10px;'>
												<strong>Request to reset your password</strong>
											</td>
										</tr>
										<tr>
											<td style='border:1px #666666 solid; padding:10px;'>
												Name:
											</td>
											<td style='border:1px #666666 solid; padding:10px;'>
												{$to_reset->get_name()}
											</td>
										</tr>
										<tr>
											<td style='border:1px #666666 solid; padding:10px;'>
												Date of reset:
											</td>
											<td style='border:1px #666666 solid; padding:10px;'>
												". date('d M Y, D,  g:i a') ."
											</td>
										</tr>
										<tr>
											<td style='border:1px #666666 solid; padding:10px;'>
												Password:
											</td>
											<td style='border:1px #666666 solid; padding:10px;'>
												{$password}
											</td>
										</tr>
									</table>
									<h5>
										If you did not reset your password, please e-mail 
										<br />the <a href='mailto:Bernardsin@sim.edu.sg?subject=Wrongful reset of password'>FYP coordinator</a> with your SIM ID immediately.
									</h5>
								</body>
							</html>";

						if(mail($email, $subject, $message, $headers)){
							//E-mail successfully sent
							db_query("COMMIT;");
							echo 
							("<script LANGUAGE='JavaScript'>
								window.alert('Password has been succesfully reset.\\nPlease check your E-mail and login with new password.');
								window.location.href='index.php';
							</script>");
							@mysqli_close($GLOBALS['mysql_link']);
							exit();
						}else{
							//E-mail failure; revert password change
							db_query("ROLLBACK;");
							echo (" <script LANGUAGE='JavaScript'>
								window.alert('There was an error with the sending of the E-mail.\\nThe password reset was reverted.');
								window.location.href='index.php';
							</script>");
							@mysqli_close($GLOBALS['mysql_link']);
							exit();
						}
					}else{
						//Password reset error
						db_query("ROLLBACK;");
						$error_text = "Password Reset Failed! Please try again later.";
					}
				}else{
					//Mismatch E-mail
					$error_text = "Wrong E-Mail address.";
				}
			}catch(Exception $e){
				//User does not exist
				$error_text = "Wrong E-Mail address.";
			}
		}
	}
?>
<!-- For simplicity sake, form is from https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php -->
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Reset Password</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='stylesheet' href='include/css/bootstrap.min.css'>
		<style type='text/css'>
			body{ font: 14px sans-serif; }
			.wrapper{ width: 350px; padding: 20px; }
		</style>
	</head>
	<body>
		<div class='wrapper' style='padding:0 20px;'>
			<h2>Reset Password</h2>
			<p>Please fill in your E-Mail to reset password.</p>
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<div class='form-group <?= (!empty($error_text)) ? 'has-error' : ''; ?>'>
					<label>SIM E-Mail</label>
					<input type='text' name='email' class='form-control' value='<?= $email; ?>'>
					<span class='help-block'><?= $error_text; ?></span>
				</div>	</span>
				<div class='form-group'>
					<input type='submit' name='reset' class='btn btn-primary' value='Reset Password'>
				</div>
			</form>
		</div>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>