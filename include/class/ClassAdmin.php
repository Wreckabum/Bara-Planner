<?php
	require_once (dirname(__FILE__)."/../funcs/sql_funcs.php");
	
	sql_connect();
	
	/*
		Base information for all accounts
	*/
	class Admin extends Account{
		
		/*
			Constructor
		*/
		public function __construct($id) {
			parent::__construct($id);
			
			unset($this->raw);
		}
	}
	
	//Close connection
	@mysqli_close($GLOBALS['mysql_link']);
?>