<?php
	// Initialize the session
	session_start();
	
	//If there is no session
	if(!isset($_SESSION["loggedin"])){
		header("location: index.php");
		exit();
	}
	
	//Include main functions
	require_once("include/funcs/sql_funcs.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	
	$err = "";
	
	if(isset($_GET['err'])){
		switch($_GET['err']){
			case 1:
				$err = "Account already exists.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Management</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<style>
			.card{
				float:left;
			}
		</style>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<div class='container'>
			<div class='row'>
			<?php
				if(!$account->is_student()){
			?>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">View All</h5>
							<p class="card-text">Display All student list</p>
							<a href="view_all.php" class="btn btn-primary">View</a>
						</div>
					</div>
			<?php
				}
				
				if($account->is_student() || $account->is_faculty()){
					$s = ($account->is_faculty() ? "s" : "");
			?>

                    <div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">View Group<?= $s ?></h5>
							<p class="card-text">View your assigned group<?= $s ?></p>
							<a href="view_group.php" class="btn btn-primary">View</a>
						</div>
					</div>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">View All Projects</h5>
							<p class="card-text">View all available projects</p>
							<a href="list_projects.php" class="btn btn-primary">View</a>
						</div>
					</div>
			<?php
				}
				
				if($account->is_student()){
			?>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">View Your Choices</h5>
							<p class="card-text">View your choices</p>
							<a href="view_choices.php" class="btn btn-primary">View</a>
						</div>
					</div>
			<?php
				}
				
				if($account->is_admin()){
			?>
                    <div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">Add Student</h5>
							<p class="card-text">Add a new student</p>
							<a href="add_student.php" class="btn btn-primary">Add</a>
						</div>
					</div>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">Add Faculty</h5>
							<p class="card-text">Add a new faculty member</p>
							<a href="add_faculty.php" class="btn btn-primary">Add</a>
						</div>
					</div>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">Add Major</h5>
							<p class="card-text">Add a new major</p>
							<a href="add_major.php" class="btn btn-primary">Add</a>
						</div>
					</div>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">Add Project</h5>
							<p class="card-text">Add a new project</p>
							<a href="add_project.php" class="btn btn-primary">Add</a>
						</div>
					</div>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">Add Group</h5>
							<p class="card-text">Add a new group for existing students</p>
							<a href="add_group.php" class="btn btn-primary">Add</a>
						</div>
					</div>
					<?php
						if($account->is_super()){
					?>
							<div class="card" style="width: 33%;">
								<div class="card-body">
									<h5 class="card-title">Add Administrator</h5>
									<p class="card-text">Add an administrator</p>
									<a href="add_admin.php" class="btn btn-primary">Add</a>
								</div>
							</div>
					<?php
						}
					?>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">Import</h5>
							<p class="card-text">Import student via CSV</p>
							<a href="import.php" class="btn btn-primary">Import</a>
						</div>
					</div>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">Set Deadline For Choices</h5>
							<p class="card-text">Set deadline for students making their choices</p>
							<a href="set_deadline.php" class="btn btn-primary">Set</a>
						</div>
					</div>
					<div class="card" style="width: 33%;">
						<div class="card-body">
							<h5 class="card-title">Send E-Mail to Students</h5>
							<p class="card-text">E-mail students of a semester their passwords</p>
							<a href="send_email.php" class="btn btn-primary">Send</a>
						</div>
					</div>
			<?php
				}
			?>
		</div>
		<div class='row'>
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
<script>
	if(window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
</script>