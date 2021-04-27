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
	
	//If not admin
	if(!$account->is_admin()){
		header("location: home.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	$err = "";
	
	if(isset($_GET['err'])){
		switch($_GET['err']){
			case 0:
				$err = "Unexpected error.";
				break;
			
			case 1:
				$err = "Deadline is not yet set.";
				break;
			
			case 2:
				$err = "Date has passed.";
				break;
			
			case 3:
				$err = "Invalid date.";
				break;
			
			case 4:
				$err = "Error archiving.";
				break;
			
			case 9:
				$err = "Successfully added.";
				break;
			
			case 10:
				$err = "Successfully updated.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
	
	//Update any potentially missing deadlines
	add_missing_deadlines();
	
	$all_deadlines = get_all_semesters();
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>Set deadlines for student choices</title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='stylesheet' href='include/css/dataTables.min.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<script src='include/js/dataTables.min.js'></script>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:<?= (($_GET['err'] >= 9) ? "#0C7B0C" : "#E22C2C" ) ?>; padding:10px;'><?= $err ?></div>
		</center>
		<div style='width:50%;'>
			<table id='filter_table' class='display'>
				<thead>
					<tr>
						<th style='text-align:center;'>
							Year
						</th>
						<th style='text-align:center;'>
							Quarter
						</th>
						<th style='text-align:center;'>
							Deadline
						</th>
						<th style='text-align:center;'>
							Details
						</th>
						<th style='text-align:center;'>
							Marking Scheme
						</th>
						<th style='text-align:center;'>
							Archive
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach($all_deadlines as $semester){
					?>
							<tr>
								<td style='text-align:center;'>
									<?= $semester->year ?>
									<input type='hidden' name='year' value='<?= $semester->year ?>' />
								</td>
								<td style='text-align:center;'>
									<?= $semester->quarter ?>
									<input type='hidden' name='quarter' value='<?= $semester->quarter ?>' />
								</td>
								<td>
									<input type='date' name='deadline' <?= ((is_null($semester->deadline)) ? "" : "value='{$semester->deadline}'") ?> style='width:97%;' required />
								</td>
								<td style='text-align:center;'>
									<a href='view_semester.php?y=<?= $semester->year ?>&q=<?= $semester->quarter ?>'>[ View Details ]</a>
								</td>
								<td style='text-align:center;'>
									<?php
										if(is_null($semester->marking_scheme) || $semester->marking_scheme == "null"){
									?>
											<a href='update_marking_scheme.php?y=<?= $semester->year ?>&q=<?= $semester->quarter ?>'>[ Update ]</a>
									<?php
										}else{
									?>
											<a href='view_marking_scheme.php?y=<?= $semester->year ?>&q=<?= $semester->quarter ?>'>[ View ]</a>
									<?php
										}
									?>
								</td>
								<td style='text-align:center;'>
									<a id='archive_link_<?= $semester->year ?>_<?= $semester->quarter ?>' href='#' onClick="show_archive(<?= $semester->year ?>, <?= $semester->quarter ?>);">
										[ Archive Semester ]
									</a>
									<form id='archive_form_<?= $semester->year ?>_<?= $semester->quarter ?>' action='exec_archive.php' method='POST' style='display:none;' onSubmit="return confirm('Confirm: Archive all data pertaining to Year <?= $semester->year ?>, Q<?= $semester->quarter ?>?');">
										<input type='checkbox' class='confirm_checkbox' name='archive_confirm' value='1' required/>
										<input type='hidden' name='year' value='<?= $semester->year ?>'/>
										<input type='hidden' name='quarter' value='<?= $semester->quarter ?>'/>
										<input type='submit' name='archive_submit' class='archive_submit' value='Archive' disabled/>
									</form>
								</td>
							</tr>
					<?php
						}
					?>
				</tbody>
			</table>
		</div>
		<input type='button' id='update_all' name='update_all' value='Update' /> <input type='button' id='add' name='add' value='Add New' onClick="window.location.href='add_semester.php'" />
	</body>
	<script>
		function show_archive(year, quarter){
			$("#archive_link_" + year + "_" + quarter).hide();
			$("#archive_form_" + year + "_" + quarter).show();
		}
		
		$(".confirm_checkbox").change(function(){
			if($(this).is(":checked")){
				$(this).closest("form").find(":submit").attr("disabled", false);
			}else{
				$(this).closest("form").find(":submit").attr("disabled", true);
			}
		});
		
		var dt = $("#filter_table").DataTable({
			/* Disable initial sort */
			"aaSorting": [],
			"paging": false
		});
		
		$("#update_all").click(function(){
			let all_rows = [];
			
			dt.rows().nodes().each(function(row){
				let row_inputs = ($(row).find(":input").serialize());
				all_rows.push(row_inputs)
			});
			
			$.ajax({
				url: "exec_semester.php",
				type: "POST",
				data: {
					all_rows: all_rows,
					update_all: true
				},
				success: function(data){
					window.location.href = "semester_details.php?err=" + data;
				},
				error: function(jqXHR,textStatus,errorThrown){
					console.log("Error with AJAX request.");
					//console.log(jqXHR); console.log(textStatus); console.log(errorThrown); //For testing
				}
			});
		});
	</script>
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