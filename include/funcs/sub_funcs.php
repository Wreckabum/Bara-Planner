<?php

	function generate_password(){
			$password_list = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!?~@#-_+<>[]{}';
		    $password_list_length = strlen($password_list) - 1; //strlen starts from 0 so to get number of characters deduct 1
		     
		    $lower_case = 'abcdefghijklmnopqrstuvwxyz';
		    $upper_case = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		    $numbers = '1234567890';
		    $special_symbols = '!?~@#-_+<>[]{}';

		    $password = $lower_case[rand(0, (strlen($lower_case) - 1))];
		    $password .= $upper_case[rand(0, (strlen($upper_case) - 1))];
		    $password .= $numbers[rand(0, (strlen($numbers) - 1))];
		    $password .= $special_symbols[rand(0, (strlen($special_symbols) - 1))];

		    for ($i = 0; $i < 4; $i++) {
		        $n = rand(0, $password_list_length); // get a random character from the string with all characters
		        $password .= $password_list[$n]; // add the character to the password string
		    }
    		$pass = str_shuffle($password);
     
    		return $pass; // return the generated password
		}
	
?>