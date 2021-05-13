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
	
	$students = json_decode($_POST['students']);
	$errors = [];
	$success_count = 0;
	
	str_clean($_POST['year']);
	str_clean($_POST['quarter']);
	
	//Process each row
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
		$sender = "noreply@fyp.com";
		$headers = "From:{$sender}";
		$message =
			"<html lang='en'>
				<body>
					Dear {$to_email->get_name()},
					<br />
					<br />
					You are now able to log into the SIM/UOW FYP system to make your choices on which project you would like to particpate in.
					<br />
					<br />
					Thigns to take note of:
					<ul>
						<li>
							You can view all of the available projects from the system.
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
						If you have any enquiries about the FYP, please e-mail the <a href='mailto:Bernardsin@sim.edu.sg?subject=Wrongful reset of password'>FYP coordinator</a> using your SIM e-mail.
					</h5>
				</body>
			</html>";

		$email_sim = mail($email, $subject, $message, $headers);
		
		if($email_sim !== true){
			//Error sending email
			db_query("ROLLBACK;"); //Revert password change
			$errors[] = $to_email->sim_id;
			continue;
		}else{
			//Sucessfully added
			db_query("COMMIT;");
			$success_count++;
		}
	}
	
	$error_info = "";
	
	if(count($errors) > 0){
		$error_info = "&err=". json_encode($errors) ."&y={$_POST['year']}&q={$_POST['quarter']}";
	}
	
	header("location: email_result.php?c={$success_count}{$error_info}");
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>