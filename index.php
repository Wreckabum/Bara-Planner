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
	
	//Testing new student for demo
	$new_student = "";
		
	$q = db_query("SELECT `password` FROM `accounts` WHERE `sim_email` = 'oisin.donal@mymail.sim.edu.sg';");
	
	if(mysqli_num_rows($q) > 0){
		$new_student = 
			"<br />
			<form action='". htmlspecialchars($_SERVER['PHP_SELF']) ."' method='post'>
				<input type='hidden' name='email' value='oisin.donal@mymail.sim.edu.sg'>
				<input type='hidden' name='password' value='". mysqli_fetch_assoc($q)['password'] ."'>
				<input type='submit' value='Newly Added Student'>
			</form>";
	}
	
	// On form submission
	if($_SERVER["REQUEST_METHOD"] == "POST"){
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
		<style type='text/css'>
			body{ font: 14px sans-serif; }
			.wrapper{ 
				width: 350px; 
				padding: 20px; 
				z-index: 10;
				
			}
			.container{
				background-color:white;
				position:relative;
				width: 370px;
				margin-top: 10%;
				border-radius: 7px;
				box-shadow: 0 10px 16px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19) ;
			}

			.cb-slideshow,
			.cb-slideshow:after { 
				position: fixed;
				width: 100%;
				height: 100%;
				top: 0px;
				left: 0px;
				z-index: 0; 
			}
			.cb-slideshow:after { 
				content: '';
				/* background: transparent url(include/images/pattern.png) repeat top left;  */
			}
			ul{
				list-style: none;
			}
			.cb-slideshow li span { 
				width: 100%;
				height: 100%;
				position: absolute;
				top: 0px;
				left: 0px;
				color: transparent;
				background-size: cover;
				background-position: 50% 50%;
				background-repeat: none;
				opacity: 0;
				z-index: 0;
				-webkit-backface-visibility: hidden;
				-webkit-animation: imageAnimation 36s linear infinite 0s;
				-moz-animation: imageAnimation 36s linear infinite 0s;
				-o-animation: imageAnimation 36s linear infinite 0s;
				-ms-animation: imageAnimation 36s linear infinite 0s;
				animation: imageAnimation 36s linear infinite 0s; 
			}
			/* .cb-slideshow li div { 
				z-index: 1000;
				position: absolute;
				bottom: 30px;
				left: 0px;
				width: 100%;
				text-align: center;
				opacity: 0;
				color: #fff;
				-webkit-animation: titleAnimation 36s linear infinite 0s;
				-moz-animation: titleAnimation 36s linear infinite 0s;
				-o-animation: titleAnimation 36s linear infinite 0s;
				-ms-animation: titleAnimation 36s linear infinite 0s;
				animation: titleAnimation 36s linear infinite 0s; 
			} */
			/* .cb-slideshow li div h3 { 
				font-family: 'BebasNeueRegular', 'Arial Narrow', Arial, sans-serif;
				font-size: 240px;
				padding: 0;
				line-height: 200px; 
			} */
			.cb-slideshow li:nth-child(1) span { 
				background-image: url(include/images/p1.jpg) 
			}
			.cb-slideshow li:nth-child(2) span { 
				background-image: url(include/images/p2.jpg);
				-webkit-animation-delay: 6s;
				-moz-animation-delay: 6s;
				-o-animation-delay: 6s;
				-ms-animation-delay: 6s;
				animation-delay: 6s; 
			}
			.cb-slideshow li:nth-child(3) span { 
				background-image: url(include/images/p3.jpg);
				-webkit-animation-delay: 12s;
				-moz-animation-delay: 12s;
				-o-animation-delay: 12s;
				-ms-animation-delay: 12s;
				animation-delay: 12s; 
			}
			.cb-slideshow li:nth-child(4) span { 
				background-image: url(include/images/p1.jpg);
				-webkit-animation-delay: 18s;
				-moz-animation-delay: 18s;
				-o-animation-delay: 18s;
				-ms-animation-delay: 18s;
				animation-delay: 18s; 
			}
			.cb-slideshow li:nth-child(5) span { 
				background-image: url(include/images/p2.jpg);
				-webkit-animation-delay: 24s;
				-moz-animation-delay: 24s;
				-o-animation-delay: 24s;
				-ms-animation-delay: 24s;
				animation-delay: 24s; 
			}
			.cb-slideshow li:nth-child(6) span { 
				background-image: url(include/images/p3.jpg);
				-webkit-animation-delay: 30s;
				-moz-animation-delay: 30s;
				-o-animation-delay: 30s;
				-ms-animation-delay: 30s;
				animation-delay: 30s; 
			}
			.cb-slideshow li:nth-child(1) div { 
				-webkit-animation-delay: 0s;
				-moz-animation-delay: 0s;
				-o-animation-delay: 0s;
				-ms-animation-delay: 0s;
				animation-delay: 2s; 
			}
			.cb-slideshow li:nth-child(2) div { 
				-webkit-animation-delay: 6s;
				-moz-animation-delay: 6s;
				-o-animation-delay: 6s;
				-ms-animation-delay: 6s;
				animation-delay: 6s; 
			}
			.cb-slideshow li:nth-child(3) div { 
				-webkit-animation-delay: 12s;
				-moz-animation-delay: 12s;
				-o-animation-delay: 12s;
				-ms-animation-delay: 12s;
				animation-delay: 12s; 
			}
			.cb-slideshow li:nth-child(4) div { 
				-webkit-animation-delay: 18s;
				-moz-animation-delay: 18s;
				-o-animation-delay: 18s;
				-ms-animation-delay: 18s;
				animation-delay: 18s; 
			}
			.cb-slideshow li:nth-child(5) div { 
				-webkit-animation-delay: 24s;
				-moz-animation-delay: 24s;
				-o-animation-delay: 24s;
				-ms-animation-delay: 24s;
				animation-delay: 24s; 
			}
			.cb-slideshow li:nth-child(6) div { 
				-webkit-animation-delay: 30s;
				-moz-animation-delay: 30s;
				-o-animation-delay: 30s;
				-ms-animation-delay: 30s;
				animation-delay: 30s; 
			}
			/* Animation for the slideshow images */
			@-webkit-keyframes imageAnimation { 
				0% { opacity: 0;
				-webkit-animation-timing-function: ease-in; }
				8% { opacity: 1;
					-webkit-animation-timing-function: ease-out; }
				17% { opacity: 1 }
				25% { opacity: 0 }
				100% { opacity: 0 }
			}
			@-moz-keyframes imageAnimation { 
				0% { opacity: 0;
				-moz-animation-timing-function: ease-in; }
				8% { opacity: 1;
					-moz-animation-timing-function: ease-out; }
				17% { opacity: 1 }
				25% { opacity: 0 }
				100% { opacity: 0 }
			}
			@-o-keyframes imageAnimation { 
				0% { opacity: 0;
				-o-animation-timing-function: ease-in; }
				8% { opacity: 1;
					-o-animation-timing-function: ease-out; }
				17% { opacity: 1 }
				25% { opacity: 0 }
				100% { opacity: 0 }
			}
			@-ms-keyframes imageAnimation { 
				0% { opacity: 0;
				-ms-animation-timing-function: ease-in; }
				8% { opacity: 1;
					-ms-animation-timing-function: ease-out; }
				17% { opacity: 1 }
				25% { opacity: 0 }
				100% { opacity: 0 }
			}
			@keyframes imageAnimation { 
				0% { opacity: 0;
				animation-timing-function: ease-in; }
				8% { opacity: 1;
					animation-timing-function: ease-out; }
				17% { opacity: 1 }
				25% { opacity: 0 }
				100% { opacity: 0 }
			}
			/* Animation for the title */
			@-webkit-keyframes titleAnimation { 
				0% { opacity: 0 }
				8% { opacity: 1 }
				17% { opacity: 1 }
				19% { opacity: 0 }
				100% { opacity: 0 }
			}
			@-moz-keyframes titleAnimation { 
				0% { opacity: 0 }
				8% { opacity: 1 }
				17% { opacity: 1 }
				19% { opacity: 0 }
				100% { opacity: 0 }
			}
			@-o-keyframes titleAnimation { 
				0% { opacity: 0 }
				8% { opacity: 1 }
				17% { opacity: 1 }
				19% { opacity: 0 }
				100% { opacity: 0 }
			}
			@-ms-keyframes titleAnimation { 
				0% { opacity: 0 }
				8% { opacity: 1 }
				17% { opacity: 1 }
				19% { opacity: 0 }
				100% { opacity: 0 }
			}
			@keyframes titleAnimation { 
				0% { opacity: 0 }
				8% { opacity: 1 }
				17% { opacity: 1 }
				19% { opacity: 0 }
				100% { opacity: 0 }
			}
			/* Show at least something when animations not supported */
			.no-cssanimations .cb-slideshow li span{
				opacity: 1;
			}

			@media screen and (max-width: 1140px) { 
				.cb-slideshow li div h3 { font-size: 140px }
			}
			@media screen and (max-width: 600px) { 
				.cb-slideshow li div h3 { font-size: 80px }
			}
		</style>
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
					<div class='form-group <?= (!empty($email_error)) ? 'has-error' : ''; ?>'>
						<label>Username</label>
						<input type='text' name='email' class='form-control' value='<?= $email; ?>'>
						<span class='help-block'><?= $email_error; ?></span>
					</div>	
					<div class='form-group <?= (!empty($password_error)) ? 'has-error' : ''; ?>'>
						<label>Password</label>
						<input type='password' name='password' class='form-control'>
						<span class='help-block'><?= $password_error; ?></span>
					</div>
					<span class='help-block'><?= $login_error; ?></span>
					<div class='form-group'>
						<input type='submit' name='login' class='btn btn-primary' value='Login'>
					</div>
				</form>
			</div>
			<br />
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<input type='hidden' name='email' value='carve.delah@mymail.sim.edu.sg'>
				<input type='hidden' name='password' value='FfI0M2Na'>
				<input type='submit' value='Super Admin'>
			</form>
			<br />
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<input type='hidden' name='email' value='abiga.attre@mymail.sim.edu.sg'>
				<input type='hidden' name='password' value='XiRSszCDo9iS'>
				<input type='submit' value='Admin'>
			</form>
			<br />
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<input type='hidden' name='email' value='erwin.haref@mymail.sim.edu.sg'>
				<input type='hidden' name='password' value='sZoS3rWaSJ'>
				<input type='submit' value='Faculty - Supervisor'>
			</form>
			<br />
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<input type='hidden' name='email' value='nil.mckea@mymail.sim.edu.sg'>
				<input type='hidden' name='password' value='wm5bvNjZSd'>
				<input type='submit' value='Faculty - Assessor'>
			</form>
			<br />
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<input type='hidden' name='email' value='tobia.bum@mymail.sim.edu.sg'>
				<input type='hidden' name='password' value='MDEcSDnQTP'>
				<input type='submit' value='Full-time Student'>
			</form>
			<br />
			<form action='<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>' method='post'>
				<input type='hidden' name='email' value='delan.abell@mymail.sim.edu.sg'>
				<input type='hidden' name='password' value='1FS3sT1bmx'>
				<input type='submit' value='Part-time Student'>
			</form>
			<?= $new_student ?>
		</div>
	</body>
</html>