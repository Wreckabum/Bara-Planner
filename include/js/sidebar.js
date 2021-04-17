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

function get_accounts(key, val){
	window.location.href = window.location.origin + window.location.pathname + "?" + key + "=" + val;
}