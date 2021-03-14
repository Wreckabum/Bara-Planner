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
	$project = get_project($_GET['p']);
	
	//If no such project
	if(is_null($project)){
		header("location: view_all.php?t=projects");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Major - <?= $project['id'] ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_project' class='basic_table' style='width:40%;'>
			<tr>
				<td colspan='2'>
					<?= $project['proj_id'] ?> (<?= $project['id'] ?>)
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Name:
				</td>
				<td>
					<?= $project['name'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Description:
				</td>
				<td>
					<?= nl2br($project['description']) ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Year:
				</td>
				<td>
					<?= $project['year'] ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Quarter:
				</td>
				<td>
					<?= $project['quarter'] ?>
				</td>
			</tr>
			<?php
				//Ensure acocunt is admin
				if($account->is_admin()){
			?>
					<tr>
						<td>
							<a href="edit_project.php?p=<?= $project['id'] ?>">
								Edit Project
							</a>
						</td>
						<td>
							<a id='delete_link' href='#' onClick="show_delete();">
								Delete Project
							</a>
							<form id='delete_form' action='delete_project.php' method='POST' style='display:none;'>
								<input type='checkbox' id='confirm_checkbox' name='delete_id' value='<?= $project['id'] ?>' required/>
								<input type='submit' name='delete_account' id='delete_submit' value='Delete' disabled/>
							</form>
						</td>
					</tr>
			<?php
				}
			?>
			<tr>
				<td colspan='2'>
					<a href='home.php'>
						Back to main page
					</a>
				</td>
			</tr>
		</table>
	</body>
	<script>
		function show_delete(){
			$("#delete_link").hide();
			$("#delete_form").show();
		}
		
		$("#confirm_checkbox").change(function(){
			if($(this).is(":checked")){
				$("#delete_submit").attr("disabled", false);
			}else{
				$("#delete_submit").attr("disabled", true);
			}
		});
	</script>
</html>
<?php
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>