<?php
	// Initialize the session
	session_start();
	
	//If there is no session
	if(!isset($_SESSION["loggedin"])){
		header("location: index.php");
		exit();
	}
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	
	// Include main functions
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
	
	$students = json_decode($_POST['students']);
	$errors = [];
	$success_count = 0;
	
	$year = (int)$_POST['year'];
	$quarter = (int)$_POST['quarter'];
	
	//Process each row
	if(!is_null($students)){
		foreach($students as $student){
			//Prepare the strings for SQL insertion
			foreach($student as $key => &$value){
				str_clean($value);
			}
			
			$to_email = get_account($student->sim_id);
			
			//Set new password
			db_query("START TRANSACTION;");
			
			$password = generate_password();
			$update = $to_email->set_password($password);
			
			//E-Mail SIM E-Mail Only
			$email = $to_email->get_sim_email();
			$subject = "SIM FYP - Project Selection";
			$message =
				"<html lang='en'>
					<body>
						Dear {$to_email->get_name()},
						<br />
						<br />
						You are now able to log into the SIM/UOW FYP system to make your choices on which project you would like to participate in.
						<br />
						<br />
						Things to take note of:
						<ul>
							<li>
								You can view all the available projects from the system.
							</li>
							<li>
								You are to decide on <span style='color:#DC1B1B;'><u><strong>THREE</strong></u></span> topics from the available projects.
							</li>
							<li>
								<span style='color:#DC1B1B;'><u><strong>Confirm your personal particulars and your choices</strong></u></span>, and submit them via the system.
							</li>
							<li>
								You are allowed to update your choices at any point till the deadline.
							</li>
							<li>
								Failure to make any choices will result in the assignment of projects being at the discretion of the school.
							</li>
						</ul>
						<h3>
							Deadline for deciding on preferred topics: ". (date('d M Y, D,  g:i a', strtotime($to_email->get_choices_deadline()))) ."
						</h3>
						<table style='border-collapse:collapse; border:1px #666666 solid; padding:10px;'>
							<tr>
								<td colspan='2' style='background-color:#EEEEEE; border:1px #666666 solid; padding:10px;'>
									<strong>SIM/UOW FYP Account Details</strong>
								</td>
							</tr>
							<tr>
								<td style='border:1px #666666 solid; padding:10px;'>
									Name:
								</td>
								<td style='border:1px #666666 solid; padding:10px;'>
									{$to_email->get_name()}
								</td>
							</tr>
							<tr>
								<td style='border:1px #666666 solid; padding:10px;'>
									Login ID:
								</td>
								<td style='border:1px #666666 solid; padding:10px;'>
									{$email}
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
							If you have any inquiries about the FYP, please e-mail the <a href='mailto:Bernardsin@sim.edu.sg?subject=Wrongful reset of password'>FYP coordinator</a> using your SIM e-mail.
						</h5>
					</body>
				</html>";
			
			
			$mail = new PHPMailer(true);
			
			try{
				global $config;
				
				//Server settings
				$mail->SMTPDebug = 3;
				$mail->isSMTP();
				$mail->Host = 'mboxhosting.com';
				$mail->SMTPAuth = true;
				$mail->Username = $config['email'];
				$mail->Password = $config['email_pass'];
				$mail->SMTPSecure = 'ssl';
				$mail->Port = 465;

				//Recipients
				$mail->setFrom($config['email'], 'UOW FYP Administration');
				$mail->addAddress($email, $to_email->get_name());

				//Content
				$mail->isHTML(true);
				$mail->Subject = $subject;
				$mail->Body	= $message;

				$mail->send();
				
				db_query("COMMIT;");
				$success_count++;
			}catch(Exception $e){
				db_query("ROLLBACK;"); //Revert password change
				$errors[] = $to_email->sim_id;
				continue;
			}
		}
		
		$error_info = "";
		
		if(count($errors) > 0){
			$error_info = "&err=". json_encode($errors) ."&y={$year}&q={$quarter}";
		}
		
		header("location: email_result.php?c={$success_count}{$error_info}");
	}else{
		//If error
		header("location: email.php?err=0");
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>