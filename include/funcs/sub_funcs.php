<?php
	/*
		Checks if a key exists in a array/multidemnsional-array
		
		@param	* (key)
		@param	Array/Multidemnsional-array
		@return	bool
	*/
	function nested_key_exists($key, $array){
		//Exists in first dimension
		if(array_key_exists($key, $array)){
			return true;
		}

		//Check each element
		foreach($array as $element){
			//If the element is an array
			if(is_array($element)){
				//Recursively call self
				if(nested_key_exists($key, $element)){
					return true;
				}
			}
		}

		return false;
	}
	
	/*
		Creates a random password
		
		@param	int (optional)
		@return	bool
	*/
	function generate_password($length = 8){
		$lower_case = "abcdefghijklmnopqrstuvwxyz";
		$upper_case = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$numbers = "1234567890";
		$special_symbols = "!?~@#-_+<>[]{}";
		
		$password_list = $lower_case . $upper_case . $numbers .$special_symbols;
		$password_list_length = strlen($password_list) - 1;
		
		//Include at least 1 of each type
		$password = $lower_case[rand(0, (strlen($lower_case) - 1))];
		$password .= $upper_case[rand(0, (strlen($upper_case) - 1))];
		$password .= $numbers[rand(0, (strlen($numbers) - 1))];
		$password .= $special_symbols[rand(0, (strlen($special_symbols) - 1))];
		
		//Get the remaining characters based on desired length of password
		for($i = 0; $i < ($length - 4); $i++){
			$n = rand(0, $password_list_length); //Get a random character from the string with all characters
			$password .= $password_list[$n]; //Add the character to the password string
		}
 
		return str_shuffle($password);
	}
?>