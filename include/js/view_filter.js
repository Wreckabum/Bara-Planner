//On every keystroke in the search field
$("#view_all_filter").keyup(function(){
	filter_tickets(this.value);
	
	if(this.value.length > 0){;
		$("#cancel_search").show();
	}else{
		$("#cancel_search").hide();
	}
});

//On every change with the checkboxes
$(".search_checkbox").change(function(){
	filter_tickets($("#view_all_filter")[0].value);
});

//Clear search field
$("#cancel_search").click(function(){
	$("#view_all_filter")[0].value = ""; //Empty search field
	$("#cancel_search").hide(); //Hide 'X' field
	filter_tickets(); //Update table
});

/*
	Updates the table based on search options

	@param string
*/
function filter_tickets(search_text){
	let total = 0;
	
	//For each ticket row
	$("#filter_table tr:not(:first)").each(function(){
		//Set initial bool
		show_row = false;
		
		//Get array of each cell of the ticket row
		student_row = $(this).children("td");
		
		//For each field selected as per the checkboxes
		$(".search_checkbox").each(function(){			
			//Check if string exists for selected fields
			if($(this).is(":checked")){
				//If the string is found in a field
				if(new RegExp(search_text, "i").test(student_row[this.value].innerHTML)){
					show_row = true;
				}
			}
		});
		
		//Show/Hide accordingly
		if(show_row){
			$(this).show();
			total++;
		}else{
			$(this).hide();
		}
		
		if(total == 0){
			$('#filter_table').hide();
			$('#no_records').show();
		}else{
			$('#filter_table').show();
			$('#no_records').hide();
		}
	});
}