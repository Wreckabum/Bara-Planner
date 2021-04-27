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
			
			case 9:
				$err = "Successfully updated.";
				break;
			
			default:
				$err = "";
				break;
		}
	}
	
	//Update any potentially missing deadlines
	add_missing_deadlines();
	
	$marking_scheme_table = print_marking_scheme($_GET['y'], $_GET['q']);
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<meta charset='UTF-8'>
		<title>View Marking Scheme - Year <?= $_GET['y'] ?>, Quarter <?= $_GET['q'] ?></title>
		<link rel='stylesheet' href='include/css/main.css' />
		<link rel='shortcut icon' href='#' /> <!-- Resolving favicon.ico error -->
		<script src='include/js/jquery-light-v3.5.1.js'></script>
		<style>
			.basic_table {
				display: inline-table;
			}
			
			#marking_scheme tr:not(:first-child) td {
				padding-top: 6px;
				padding-bottom: 6px;
			}
			
			.basic_table td {
				text-align: center;
				white-space: nowrap;
			}
			
			.basic_table td.empty {
				background-color: #E4E4E4;
			}
			
			.supervisor, .assessor, .total, .average {
				width: 125px;
			}
			
			.supervisor {
				background-color: #E6ffE6;
			}
			
			.assessor {
				background-color: #CFCFFF;
			}
			
			.penalty {
				background-color: #FFCECE;
			}
		</style>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<?php
			if($marking_scheme_table === false){
		?>
				<h4>
					Create new marking scheme
				</h4>
				<input type='button' id='toggle_default' value='Load Default Marking Scheme' />
				<br />
				<br />
				<input type='button' id='add_new_row' value='Add Item' />
				<br />
				<br />
				<div id='marking_scheme_container'>
					<table id='marking_scheme' class='basic_table' style='width:auto%;'>
						<tr class='header'>
							<td colspan='2'>
								Item
							</td>
							<td>
								Assignment Items & Format
							</td>
							<td>
								Week Due
							</td>
							<td>
								Max Marks (%)
							</td>
							<td>
								Supervisor
							</td>
							<td>
								Assessor
							</td>
							<td>
								Total
							</td>
							<td>
								Average
							</td>
						</tr>
						<tr id='row_1' class='new_item'>
							<td colspan='2'>
								1
							</td>
							<td class='item_desc'>
								<input type='text' name='~~~' style='width:97%;' required />
							</td>
							<td class='due'>
								<input type='number' name='~~~' min='1' max='20'>
							</td>
							<td class='weight'>
								<input type='number' name='~~~' min='1' max='100'>%
							</td>
							<td class='supervisor'></td>
							<td class='assessor'></td>
							<td class='total'></td>
							<td class='average'></td>
						</tr>
						<tr class='fixed'>
							<td colspan='2' class='empty'>
								-
							</td>
							<td>
								Penalty
							</td>
							<td class='due penalty'>-</td>
							<td class='weight penalty'>-%</td>
							<td class='supervisor penalty'></td>
							<td class='assessor penalty'></td>
							<td class='total penalty'></td>
							<td class='average penalty'></td>
						</tr>
						<tr class='fixed'>
							<td colspan='2' class='empty'>-</td>
							<td>
								Individual Student
							</td>
							<td class='empty'>-</td>
							<td>
								<input type='number' name='~~~' min='1' max='100'>%
							</td>
							<td colspan='4' class='empty'>-</td>
						</tr>
					</table>
					<table id='actions_table' class='basic_table'>
						<tr>
							<td>
								Actions
							</td>
						</tr>
						<tr id='actions_1' class='actions_row'>
							<td>
								<input type='button' id='add_sub_1' class='add_sub' value='Add Sub-item' /> <input type='button' id='del_1' class='del_row' value='Delete Item' />
							</td>
						</tr>
					</table>
				</div>
		<?php
			}else{
				echo $marking_scheme_table;
			}
		?>
		<br />
		<a href='semester_details.php'>Back to all semester details</a>
		<br />
		<a href='home.php'>Back to main page</a>
	</body>
	<script>
		var saved_table;
		var alphabet = "abcdefghijklmnopqrstuvwxyz".split("");
		
		$("#toggle_default").click(function(){
			if($("#toggle_default").val() == "Revert"){
				$("#toggle_default").val("Load Default Marking Scheme");
				$("#marking_scheme_container").html(saved_table);
			}else{
				$("#toggle_default").val("Revert");
				
				saved_table = $("#marking_scheme_container").html();
				
				$("#marking_scheme_container").html(<?= json_encode(print_marking_scheme(0, 0, 0, true), JSON_HEX_TAG) ?>);
			}
		});
		
		let new_row = $("#row_1").clone().get(0).outerHTML;
		let new_sub_row = "<tr class='new_item' main_item='1' sub_item='1'><td class='empty'>-</td><td></td><td class='item_desc'><input type='text' name='~~~' style='width:97%;' required /></td><td class='weight'><input type='number' name='~~~' min='1' max='100'>%</td><td class='supervisor'></td><td class='assessor'></td><td class='total'></td><td class='average'></td></tr>";
		let action_row = $("#actions_1").clone().get(0).outerHTML;
		let counter = 1;
		
		$("#add_new_row").click(function(){
			$("#marking_scheme").find('tr:nth-last-child(3)').after(new_row.replace(/1/g, ++counter));
			$("#actions_table tr").last().after(action_row.replace(/1/g, counter));
		});
		
		//Update all row meta-data + item number when row is deleted
		$(document).on("click", ".del_row", function(){
			let row_id = $(this)[0].id.substr(4);
			
			//Cannot delete first row
			if(row_id != 1){
				$("#row_" + row_id).remove();
				$("#actions_" + row_id).remove();
				--counter;
				
				$('#marking_scheme tr[main_item=' + row_id + ']').remove();
				$('#actions_table tr[main_item=' + row_id + ']').remove();
				
				let recount = 1;
				
				$(".new_item").each(function(e, val){
					if(typeof $(this).attr('main_item') === "undefined"){
						$(this).attr('id', 'row_' + recount);
						$(this).children('td').first().text(recount++);
					}else{
						$(this).attr('main_item', (recount - 1));
					}
				});
				
				recount = 1;
				
				$(".actions_row").each(function(e, val){
					if(typeof $(this).attr('main_item') === "undefined"){
						$(this).attr('id', 'actions_' + recount);
						$(this).find('.add_sub').attr('id', 'add_sub_' + recount);
						$(this).find('.del_row').attr('id', 'del_' + recount++);						
					}else{
						$(this).attr('main_item', (recount - 1));
						$(this).find('input').attr('main_item', (recount - 1));
					}
				});
			}
		});
		
		//Update all row meta-data + item number when row is deleted
		$(document).on("click", ".del_sub_row", function(){
			let main_id = $($(this)[0]).attr('main_item');
			let sub_id = $($(this)[0]).attr('sub_item');
			
			$('#marking_scheme tr[main_item=' + main_id + '][sub_item=' + sub_id + ']').remove(); //Remove main row
			$('#actions_table tr[main_item=' + main_id + '][sub_item=' + sub_id + ']').remove(); //Remove action row
			$("#row_" + main_id).find('td:nth-child(3)').attr('rowspan', ($("#row_" + main_id).find('td:nth-child(3)').attr('rowspan') - 1)); //Reduce week due rowspan
			
			//If removing the last sub-item
			if($("#row_" + main_id).find('td:nth-child(3)').attr('rowspan') == 1){
				$("#row_" + main_id).find('td:nth-child(4)').attr('colspan', 1).attr('class', "weight").html("<input type='number' name='~~~' min='1' max='100'>%"); //Revert weight cell
				$("#row_" + main_id).find('td:nth-child(4)').after("<td class='supervisor'></td><td class='assessor'></td><td class='total'></td><td class='average'></td>"); //Readd missing cols
			}else{
				let recount = 1;
				
				$('#marking_scheme tr[main_item=' + main_id + ']').each(function(e, val){
					$(this).attr('sub_item', recount++);
				});
				
				recount = 1;
				
				$('#actions_table tr[main_item=' + main_id + ']').each(function(e, val){
					$(this).find('input.del_sub_row').attr('sub_item', recount);
					$(this).attr('sub_item', recount++);
				});
			}
			
			let recount = 0;
			
			$('#marking_scheme tr[main_item=' + main_id + ']').each(function(e, val){
				$(this).find('td:nth-child(2)').text(alphabet[recount++]);
			});
		});
		
		//Add a new sub-item
		$(document).on("click", ".add_sub", function(){
			let row_id = $(this)[0].id.substr(8);
			let row_span = ((typeof  $("#row_" + row_id).find('td:nth-child(3)').attr('rowspan') === "undefined") ? 1 : $("#row_" + row_id).find('td:nth-child(3)').attr('rowspan'));
			
			$("#row_" + row_id).find('td:nth-child(3)').attr('rowspan', ++row_span); //Increase week due rowspan
			$("#row_" + row_id).find('td:nth-child(4)').attr('colspan', 5).attr('class', "empty").text("-"); //Merge last 5 cols as empty
			$("#row_" + row_id).find('td:nth-child(n+5)').remove(); //Remove excess columns
			
			if(row_span == 2){
				$("#row_" + row_id).after($($.parseHTML(new_sub_row)).attr('main_item', row_id).attr('sub_item', row_span - 1)); //Add new sub-item with proper meta-data
				$("#actions_" + row_id).after("<tr class='actions_row' main_item='" + row_id + "' sub_item='" + (row_span - 1) + "'><td><input type='button' main_item='" + row_id + "' sub_item='" + (row_span - 1) + "' class='del_sub_row' value='Delete Item' /></td></tr>"); //Add new action row
			}else{
				$('#marking_scheme tr[main_item=' + row_id + ']').last().after($($.parseHTML(new_sub_row)).attr('main_item', row_id).attr('sub_item', row_span - 1)); //Add new sub-item with proper meta-data
				$('#actions_table tr[main_item=' + row_id + ']').last().after("<tr class='actions_row' main_item='" + row_id + "' sub_item='" + (row_span - 1) + "'><td><input type='button' main_item='" + row_id + "' sub_item='" + (row_span - 1) + "' class='del_sub_row' value='Delete Item' /></td></tr>"); //Add new action row
			}
			
			let recount = 0;
			
			$('#marking_scheme tr[main_item=' + row_id + ']').each(function(e, val){
				$(this).find('td:nth-child(2)').text(alphabet[recount++]);
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