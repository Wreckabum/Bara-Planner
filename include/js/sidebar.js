function toggle_active(){
	$("#active_records").toggle(350);
	$("#archived_records").hide(350);
	$("#archived_students").hide(350);
}

function toggle_active_students(){
	$("#active_students").toggle(350);
}

function toggle_archive(){
	$("#archived_records").toggle(350);
	$("#active_records").hide(350);
	$("#active_students").hide(350);
}

function toggle_archived_students(){
	$("#archived_students").toggle(350);
}

function toggle_archived_students(){
	$("#archived_students").toggle(350);
}

function toggle_sidebar(){
	if($.trim($("#sidebar_toggle").text()) == "<"){
		$("#active_records").hide(350);
		$("#active_students").hide(350);
		$("#archived_records").hide(350);
		$("#archived_students").hide(350);
		$("#sidebar_content").width("0").hide(350);
		$("#sidebar_wrapper").animate({width: $("#sidebar_toggle").get(0).scrollWidth}, 350);
		$("#sidebar_toggle").width("100%").html(">");
		$("#main_content").animate({width: "96%", "margin-left": "2%"}, 350);
	}else{
		$("#sidebar_content").width("90%").show(350);
		$("#sidebar_wrapper").animate({width: "12%"}, 350);
		$("#sidebar_toggle").width("9%").html("<");
		$("#main_content").animate({width: "86%", "margin-left": "13%"}, 350);
	}
}

function get_accounts(key, val){
	window.location.href = window.location.origin + window.location.pathname + "?" + key + "=" + val;
}