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
				$err = "Total marks do not add up to 100%.";
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
			
			.basic_table td {
				text-align: center;
				white-space: nowrap;
			}
			
			.basic_table td.empty {
				background-color: #E4E4E4;
			}
			
			.item_desc {
				width: 450px;
			}
			
			.supervisor, .assessor, .total, .average {
				width: 112px;
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
			
			#marking_scheme .new_item, #actions_table .actions_row {
				height: 61px;
			}
		</style>
	</head>
	<body>
		<?php include("include/templates/header.php"); ?>
		<center>
			<div style='display:<?= (($err == "") ? "none" : "block" ) ?>; color:#E22C2C; padding:10px;'><?= $err ?></div>
		</center>
		<?php
			if($marking_scheme_table === false || (isset($_GET['update']) && $_GET['update'] == true)){
		?>
				<h4>
					Create new marking scheme
				</h4>
				<input type='button' id='toggle_default' value='Load Default Marking Scheme' />
				<br />
				<br />
				<div id='add_new_row_container'>
					<input type='button' id='add_new_row' value='Add Item' />
					<br />
					<br />
				</div>
				<form id='marking_scheme_form' action='exec_marking_scheme.php' method='POST'>
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
									Marks (%)
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
									<textarea name='desc[1][main]' rows='2' style='width:97%; resize:none; vertical-align:middle;' required></textarea>
								</td>
								<td class='due'>
									<input type='number' name='due[1]' min='1' max='20' required>
								</td>
								<td class='weight'>
									<input type='number' name='weight[1][main]' min='1' max='100' required>%
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
									<input id='student_weight' type='number' name='student' min='1' max='100' required>%
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
					<br />
					<input type='hidden' name='year' value='<?= $_GET['y'] ?>'/>
					<input type='hidden' name='quarter' value='<?= $_GET['q'] ?>'/>
					<input id='submit_marking_scheme' type='submit' name='submit' value='Update Marking Scheme' />
				</form>
		<?php
			}else{
		?>
			<?= $marking_scheme_table ?>
			<br />
			<br />
			<input id='update_existing' type='button' value='New Marking Scheme' />
		<?php
			}
		?>
		<br />
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
				$("#submit_marking_scheme").val("Update Marking Scheme");
				$("#add_new_row_container").show();
			}else{
				$("#toggle_default").val("Revert");
				$("#submit_marking_scheme").val("Use Default");
				$("#add_new_row_container").hide();
				
				saved_table = $("#marking_scheme_container").html();
				
				$("#marking_scheme_container").html(<?= json_encode(print_marking_scheme(0, 0, 0, true), JSON_HEX_TAG) ?>);
			}
		});
		
		$("#update_existing").click(function(){
			window.location.href = "update_marking_scheme.php?y=<?= $_GET['y'] ?>&q=<?= $_GET['q'] ?>&update=true";
		});
		
		let new_row = $("#row_1").clone().get(0).outerHTML;
		let new_sub_row = "<tr class='new_item' main_item='1' sub_item='1'><td class='empty'>-</td><td>a</td><td class='item_desc'><textarea name='desc[1][sub][a]' rows='2' style='width:97%; resize:none; vertical-align:middle;' required></textarea></td><td class='weight'><input type='number' name='weight[1][sub][a]' min='1' max='100' required>%</td><td class='supervisor'></td><td class='assessor'></td><td class='total'></td><td class='average'></td></tr>";
		let action_row = $("#actions_1").clone().get(0).outerHTML;
		let counter = 1;
		
		$("#add_new_row").click(function(){
			$("#marking_scheme").find('tr:nth-last-child(3)').after(new_row.replace(/1/g, ++counter));
			$("#marking_scheme").find('tr:nth-last-child(3)').find('td.due input').attr('min', 1);
			$("#actions_table tr").last().after(action_row.replace(/1/g, counter));
		});
		
		//Update all main-item row meta-data + item number when main-item is deleted
		$(document).on("click", ".del_row", function(){
			let row_id = $(this)[0].id.substr(4);
			
			//Cannot delete first row
			if(row_id != 1){
				$("#row_" + row_id).remove();
				$("#actions_" + row_id).remove();
				--counter;
				
				$('#marking_scheme tr[main_item=' + row_id + ']').remove();
				$('#actions_table tr[main_item=' + row_id + ']').remove();
				
				//Update all main-item rows
				let recount = 1;
				
				$(".new_item").each(function(e, val){
					//If main-item
					if(typeof $(this).attr('main_item') === "undefined"){
						$(this).attr('id', 'row_' + recount);
						$(this).children('td').first().text(recount);
						$(this).children('td.item_desc').children('textarea').attr('name', 'desc[' + recount + '][main]');
						$(this).children('td.due').children('input').attr('name', 'due[' + recount + ']');
						$(this).children('td.weight').children('input').attr('name', 'weight[' + recount++ + '][main]');
					}else{
						//If sub-item
						$(this).attr('main_item', (recount - 1));
						$(this).children('td.item_desc').children('input').attr('name', $(this).children('td.item_desc').children('textarea').attr('name').replace(/desc\[(\d+)]\[sub]\[(\d+)]/g, 'desc[' + (recount - 1) + '][sub][$2]'));
						$(this).children('td.weight').children('input').attr('name', $(this).children('td.weight').children('input').attr('name').replace(/weight\[(\d+)]\[sub]\[(\d+)]/g, 'weight[' + (recount - 1) + '][sub][$2]'));
					}
				});
				
				//Update all action rows
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
		
		//Update all sub-item row meta-data + item number when sub-item is deleted
		$(document).on("click", ".del_sub_row", function(){
			let main_id = $($(this)[0]).attr('main_item');
			let sub_id = $($(this)[0]).attr('sub_item');
			
			$('#marking_scheme tr[main_item=' + main_id + '][sub_item=' + sub_id + ']').remove(); //Remove main row
			$('#actions_table tr[main_item=' + main_id + '][sub_item=' + sub_id + ']').remove(); //Remove action row
			$("#row_" + main_id).find('td:nth-child(3)').attr('rowspan', ($("#row_" + main_id).find('td:nth-child(3)').attr('rowspan') - 1)); //Reduce week due rowspan
			
			//If removing the last sub-item
			if($("#row_" + main_id).find('td:nth-child(3)').attr('rowspan') == 1){
				$("#row_" + main_id).find('td:nth-child(4)').attr('colspan', 1).attr('class', "weight").html("<input type='number' name='weight[" + main_id + "][main]' min='1' max='100'>%"); //Revert weight cell
				$("#row_" + main_id).find('td:nth-child(4)').after("<td class='supervisor'></td><td class='assessor'></td><td class='total'></td><td class='average'></td>"); //Readd missing cols
			}else{
				let recount = 1;
				
				$('#marking_scheme tr[main_item=' + main_id + ']').each(function(e, val){
					$(this).attr('sub_item', recount);
					$(this).children('td.item_desc').children('textarea').attr('name', 'desc[' + main_id + '][sub][' + alphabet[(recount - 1)] + ']');
					$(this).children('td.weight').children('input').attr('name', 'weight[' + main_id + '][sub][' + alphabet[(recount++ - 1)] + ']');
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
				//First sub-item
				$("#row_" + row_id).after($($.parseHTML(new_sub_row)).attr('main_item', row_id).attr('sub_item', row_span - 1)); //Add new sub-item with proper meta-data
				$('#marking_scheme tr[main_item=' + row_id + ']').last().find('td:nth-child(2)').text(alphabet[(row_span - 2)]); //Update the row details
				$('#marking_scheme tr[main_item=' + row_id + ']').last().children('td.item_desc').children('textarea').attr('name', 'desc[' + row_id + '][sub][' + alphabet[((row_span - 2))] + ']'); //Update the row details
				$('#marking_scheme tr[main_item=' + row_id + ']').last().children('td.weight').children('input').attr('name', 'weight[' + row_id + '][sub][' + alphabet[((row_span - 2))] + ']'); //Update the row details
				$("#actions_" + row_id).after("<tr class='actions_row' main_item='" + row_id + "' sub_item='" + (row_span - 1) + "'><td><input type='button' main_item='" + row_id + "' sub_item='" + (row_span - 1) + "' class='del_sub_row' value='Delete Item' /></td></tr>"); //Add new action row
			}else{
				//Next consecutive sub-items
				$('#marking_scheme tr[main_item=' + row_id + ']').last().after($($.parseHTML(new_sub_row)).attr('main_item', row_id).attr('sub_item', row_span - 1)); //Add new sub-item with proper meta-data
				$('#marking_scheme tr[main_item=' + row_id + ']').last().find('td:nth-child(2)').text(alphabet[(row_span - 2)]); //Update the row details
				$('#marking_scheme tr[main_item=' + row_id + ']').last().children('td.item_desc').children('textarea').attr('name', 'desc[' + row_id + '][sub][' + alphabet[((row_span - 2))] + ']'); //Update the row details
				$('#marking_scheme tr[main_item=' + row_id + ']').last().children('td.weight').children('input').attr('name', 'weight[' + row_id + '][sub][' + alphabet[((row_span - 2))] + ']'); //Update the row details
				$('#actions_table tr[main_item=' + row_id + ']').last().after("<tr class='actions_row' main_item='" + row_id + "' sub_item='" + (row_span - 1) + "'><td><input type='button' main_item='" + row_id + "' sub_item='" + (row_span - 1) + "' class='del_sub_row' value='Delete Item' /></td></tr>"); //Add new action row
			}
		});
		
		$("#marking_scheme_form").submit(function(e){
			if($("#submit_marking_scheme").val() == "Use Default"){
				
			}else{
				let total_weight = 0;
				
				$('#marking_scheme input[name^=weight]').each(function(){
					total_weight += parseInt($(this).val());
				});
				
				total_weight += parseInt($("#student_weight").val());
				
				if(isNaN(total_weight) || total_weight != 100){
					alert("All marks must total up to 100.");
					e.preventDefault();
					
					return false;
				}
			}
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