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
	
	//Test group
	$this_group = 5;
	
	//Get the output DOM
	ob_start();
	include "test.php";
	$page = ob_get_contents();
	ob_get_clean();

	$doc = new DOMDocument();
	$doc->loadHTML($page);
	
	//var_dump($doc->saveHTML());
	
	use Dompdf\Dompdf;
	
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