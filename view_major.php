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
	
	try{
		$major = get_major($_GET['m']);
	}catch(Exception $e){
		header("location: view_all.php?t=majors");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
?>

<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Major - <?= $major->id ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<table id='view_major' class='basic_table' style='width:30%;'>
			<tr>
				<td colspan='2'>
					<?= $major->id ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Name:
				</td>
				<td>
					<?= $major->get_name() ?>
				</td>
			</tr>
			<tr>
				<td style='width:25%;'>
					Description:
				</td>
				<td>
					<?= nl2br($major->get_description()) ?>
				</td>
			</tr>
			<tr>
				<td>
					Type:
				</td>
				<td>
					<?= (($major->is_full_time()) ? "Full-time" : "Part-time") ?>
				</td>
			</tr>
			<?php
				//Ensure acocunt is admin
				if($account->is_admin()){
			?>
					<tr>
						<td>
							<a href="edit_major.php?m=<?= $major->id ?>">
								Edit Major
							</a>
						</td>
						<td>
							<a id='delete_link' href='#' onClick="show_delete();">
								Delete Major
							</a>
							<form id='delete_form' action='delete_major.php' method='POST' style='display:none;'>
								<input type='checkbox' id='confirm_checkbox' name='delete_id' value='<?= $major->id ?>' required/>
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