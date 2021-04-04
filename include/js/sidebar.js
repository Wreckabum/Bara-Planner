function show_student_options(){
	$("#view_student").show();
}

function get_accounts(type){
	window.location.href = window.location.origin + window.location.pathname + "?t=" + type;
}