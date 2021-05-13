<?php
	//Initialize the session
	session_start();
	
	//If already logged in
	if(!isset($_SESSION["loggedin"])){
		header("location: home.php");
		exit();
	}
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	
	//Include main functions
	require_once("include/funcs/sql_funcs.php");
	require_once("PHPMailer/src/Exception.php");
	require_once("PHPMailer/src/PHPMailer.php");
	require_once("PHPMailer/src/SMTP.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	
	//If not admin
	if(!$account->is_admin()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Define variables and initialize with empty values
	$sim_id = "";
	$uow_id = "";
	$email = "";
	$error_text = "";
	
	// On form submission
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		//Extra layer of checks
		$sim_id = str_clean($_POST["sim_id"]);
		$uow_id = str_clean($_POST["uow_id"]);
		$email = str_clean($_POST["email"]);
		
		//If email is empty
		if(empty($sim_id) || empty($uow_id) || empty($email)){
			$error_text = "Please enter all requried details.";
		}
		
		if(!empty($sim_id) && !empty($uow_id) && !empty($email)){
			try{
				$to_reset = get_account($sim_id);
				
				//If trying to reset admin account without being super admin
				if($to_reset->is_admin() && !$account->is_super()){
?>
					<script>
						window.alert("Insufficient privileges.");
						window.location.href = "view_account.php?a=" + <?= $to_reset->sim_id ?>;
					</script>
<?php
					@mysqli_close($GLOBALS['mysql_link']);
					exit();
				}
				
				//If correct details
				if($to_reset->uow_id == $uow_id && $to_reset->get_sim_email() == $email){
					//Set new password
					db_query("START TRANSACTION;");
					
					$password = generate_password();
					$update = $to_reset->set_password($password);
					
					if($update === true){
						$subject = "Reset Password";
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
										Your password has been manually reset by an 
										<br />administrator. Please
										E-Mail the <a href='mailto:Bernardsin@sim.edu.sg?subject=Manual reset of password'>FYP coordinator</a> 
										<br />for any enquiries.
									</h5>
								</body>
							</html>";
						
						$mail = new PHPMailer(true);
			
						try{
							global $config;
							
							//Server settings
							$mail->SMTPDebug = 0;
							$mail->isSMTP();
							$mail->Host = $config['email_host'];
							$mail->SMTPAuth = true;
							$mail->Username = $config['email'];
							$mail->Password = $config['email_pass'];
							$mail->SMTPSecure = 'ssl';
							$mail->Port = 465;

							//Recipients
							$mail->setFrom($config['email'], "UOW FYP Administration");
							$mail->addAddress($to_reset->get_sim_email(), $to_reset->get_name());

							//Content
							$mail->isHTML(true);
							$mail->Subject = $subject;
							$mail->Body	= $message;

							$mail->send();
							
							//E-mail successfully sent
							db_query("COMMIT;");
?>
							<script>
								window.alert("Password has been succesfuly reset.");
								window.location.href = "view_account.php?a=" + <?= $to_reset->sim_id ?>;
							</script>
<?php
							@mysqli_close($GLOBALS['mysql_link']);
							exit();
						}catch(Exception $e){
							//E-mail failure; revert password change
							db_query("ROLLBACK;");
?>
							<script>
								window.alert("There was an error with the sending of the E-mail.\nThe password reset was reverted.");
								window.location.href = "view_account.php?a=" + <?= $to_reset->sim_id ?>;
							</script>
<?php
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
					$error_text = "Wrong details.";
				}
			}catch(Exception $e){
				//User does not exist
				$error_text = "Wrong details.";
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
		<?php include("include/templates/header.php"); ?><div class='container'>
		<div class='wrapper' style='padding:0 20px;margin:auto;'>
			<h2>Reset Password</h2>
			<p>Please fill in the following user details to reset their password.</p>
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<div class='form-group <?= (!empty($error_text)) ? 'has-error' : ''; ?>'>
					<label>SIM ID</label>
					<input type='text' name='sim_id' class='form-control' value='<?= $sim_id; ?>'>
					<label>UOW ID</label>
					<input type='text' name='uow_id' class='form-control' value='<?= $uow_id; ?>'>
					<label>SIM E-Mail</label>
					<input type='text' name='email' class='form-control' value='<?= $email; ?>'>
					<span class='help-block'><?= $error_text; ?></span>
				</div>	</span>
				<div class='form-group'>
					<input type='submit' name='reset' class='btn btn-primary' value='Reset Password'>
				</div>
			</form>
			<br />
		    <a href='home.php'>Back to main page</a>
		</div>

		</div>
	</body>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>