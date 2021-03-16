<?php
	//Initialize the session
	session_start();
	
	//If already logged in
	if(!isset($_SESSION["loggedin"])){
		header("location: home.php");
		exit();
	}
	
	//Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	
	//Define variables and initialize with empty values
	$email = "";
	$email_error = "";
	$reset_error = "";
	
	// On form submission
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		//Extra layer of checks
		$email = str_clean($_POST["email"]);
		
		//If email is empty
		if(empty($email)){
			$email_error = "Please enter email.";
		}else{
			$email = trim($email);
		}
		
		if(!empty($email)){
			try{
				$to_reset = get_account_by_email($email);
				
				//Allow reset of self only
				if($to_reset->sim_id == $account->sim_id){	
					$old_password = mysqli_fetch_assoc((db_query("SELECT `password` FROM `accounts` WHERE `sim_id` = '{$to_reset->id}';")));
					$password = generate_password();
					$update = db_query("UPDATE `accounts` SET `password` = '{$password}' WHERE `sim_id` = '{$to_reset->id}';");
					
					if($update === True){
						//Password reset
						$sender = "noreply@fyp.com";
						$subject = "Reset Password";
						$message = "New Password: {$password}";
						$headers = "From:{$sender}\r\nCC:{$to_reset->get_personal_email()}";

						if(mail($email, $subject, $message, $headers)){
							//E-mail successfully sent
?>
							<script>
								window.alert('Password has been succesfuly reset.\nPlease check your E-mail and login with new password.');
								window.location.href='index.php';
							</script>
<?php
						}else{
							//E-mail failure; revert password change
							db_query("UPDATE `accounts` SET `password` = '{$old_password}' WHERE `sim_id` = '{$to_reset->id}';");
?>
							<script>
								window.alert('There was an error with the sending of the E-mail.\nThe password reset was reverted.');
								window.location.href='index.php';
							</script>
<?php
						}
					}else{
						//Password reset error
						$reset_error = "Password Reset Failed! Please try again later.";
					}
				}else{
					//Mismatch E-mail
					$reset_error = "Wrong E-Mail address.";
				}
			}catch(Exception $e){
				//User does not exist
				$reset_error = "Wrong E-Mail address.";
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
		<?php include("include/templates/header.php"); ?>
		<div class='wrapper' style='padding:0 20px;'>
			<h2>Reset Password</h2>
			<p>Please fill in your email to reset password.</p>
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<div class='form-group <?= (!empty($email_error)) ? 'has-error' : ''; ?>'>
					<label>Username</label>
					<input type='text' name='email' class='form-control' value='<?= $email; ?>'>
					<span class='help-block'><?= $email_error; ?></span>
				</div>	
				<span class='help-block'><?= $reset_error; ?></span>
				<div class='form-group'>
					<input type='submit' name='reset' class='btn btn-primary' value='Reset Password'>
				</div>
			</form>
		</div>
		<br />
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>