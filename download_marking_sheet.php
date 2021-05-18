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
	require_once("dompdf/autoload.inc.php");
	
	//Connect to database
	sql_connect();
	
	$account = get_account($_SESSION["id"]);
	
	//Clean parameter
	str_clean($_GET['g']);
	
	try{
		$group = get_group($_GET['g']);
	}catch(Exception $e){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//If not group supervisor/assessor/admin
	if(!$group->is_supervisor($account->sim_id) && !$group->is_assessor($account->sim_id) && !$account->is_admin()){
		header("location: view_group.php");
		@mysqli_close($GLOBALS['mysql_link']);
		exit();
	}
	
	//Get the output DOM
	ob_start();
	include "download_marking_sheet_content.php";
	$page = ob_get_contents();
	ob_get_clean();

	$doc = new DOMDocument();
	$doc->loadHTML($page);
	
	use Dompdf\Dompdf;
	
	//Set as PDF
	$dompdf = new Dompdf();
	$dompdf->loadHtml($doc->saveHTML());
	$dompdf->setPaper('A3', 'landscape');
	
	/* $options = $dompdf->getOptions();
	$options->isPhpEnabled(true);
	$dompdf->setOptions($options); */

	$dompdf->render();
	$dompdf->stream($group->get_name());
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>