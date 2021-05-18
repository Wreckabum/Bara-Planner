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
	
	/*
		Logs account changes
		
		@param	int
	*/
	function account_log($account_id){
		global $config;
		
		//Create logs directory if not exists
		if(!is_dir($config['logs'])){
			mkdir($config['logs'], 0755, true);
		}
		
		//Get latest updated details
		$account = mysqli_fetch_assoc(db_query("SELECT * FROM `accounts` WHERE `sim_id` = '{$account_id}';"));
		$account = ["timestamp" => date("H:i:s Y-m-j")] + $account; //Timestamp of update
		$account['password'] = "-"; //Hide password
		
		//Base file name for log
		$file = "Account_Log";
		
		//Get specific log file
		$full_path = "{$config['logs']}/{$file}.csv";
		
		$append_headers = false;
		
		//Check if new file
		if(!file_exists($full_path)){
			//Create new file
			$handle = fopen($full_path, 'w');
			
			$append_headers = true;
		}else{
			//Open the file
			$handle = fopen($full_path, 'a');
		}
		
		//Error if not writable
		if(!is_writable($full_path)){
			if(IS_LOCAL){
				exit("Log file '{$file}' is not writable.");
			}else{
				return false;
			}
		}
		
		//If general file error
		if(!$handle){
			if(IS_LOCAL){
				exit("Unable to open log file: {$file}");
			}else{
				return false;
			}
		}
		
		if($append_headers){
			//Enter header row
			if(fputcsv($handle, array_keys($account)) === false){
				//If failure
				if(IS_LOCAL){
					exit("Unable to write headers to file: {$file}");
				}else{
					return false;
				}
			}
		}
		
		//Write to file
		if(fputcsv($handle, $account) === false){
			//If failure
			if(IS_LOCAL){
				exit("Unable to write to file: {$file}");
			}else{
				return false;
			}
		}
		
		//Archive logs if reached 1MB
		if(filesize($full_path) > 1000000){
			//Create logs archive directory if not exists
			if(!is_dir("{$config['logs']}/archive_logs")){
				mkdir("{$config['logs']}/archive_logs", 0755, true);
			}
			
			//Copy file to archive folder
			copy($full_path, "{$config['logs']}/archive_logs/{$file}_". date("Y-m-d_H-i-s") .".csv");
			
			//Delete current file
			unlink($full_path);
		}
	}
	
	/*
		Returns list of all logs
		
		@return	2D Array
	*/
	function get_all_logs_path(){
		global $config;
		
		$output = [
			"archive" => [], 
			"current" => [], 
			"base_path" => $config['logs']
		];
		
		//If there are logs
		if(is_dir("{$config['logs']}")){
			$main_directory = array_diff(scandir($config['logs']), array(".", "..", ".htaccess", "archive_logs"));
			
			foreach($main_directory as $log){
				$output['current'][] = $log;
			}
			
			//If there are archives
			if(is_dir("{$config['logs']}/archive_logs")){
				$archive_directory = array_diff(scandir("{$config['logs']}/archive_logs"), array('..', '.'));
				
				foreach($archive_directory as $log){
					$output['archive'][] = $log;
				}
			}
		}
		
		return $output;
	}
?>