<?php
	use PHPUnit\Framework\TestCase;

	require_once(dirname(__FILE__)."/../include/class/ClassAccount.php");
    require_once(dirname(__FILE__)."/../include/class/ClassFaculty.php");

	sql_connect();
	
	/*
		Test if ticket exists as seen from triager
	*/
	class ClassFacultyTest extends TestCase{
		
		public	$sim_id;
		public	$uow_id;
		
		private	$name;
		private $password;
		private	$phone;
		private	$sim_email;
		private	$personal_email;
		private	$show_phone;
		private	$show_email;
		protected $account_type;
		
		protected $majors;

		/*
			Tests the constructor
		*/
		function test_construct(){
			//Declare test variables
			$id = "10293074";
			
			$query = db_query("SELECT * FROM `accounts` WHERE `sim_id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			$this->assertEquals($result['sim_id'], "10293074");
			$this->assertEquals($result['uow_id'], "6652584");
			$this->assertEquals($result['name'], "Malissia Creyke");
			$this->assertEquals($result['password'], "idZ8AkMH1O");
			$this->assertEquals($result['phone'], "83987712");
			$this->assertEquals($result['sim_email'], "malis.creyk@mymail.sim.edu.sg");
			$this->assertEquals($result['personal_email'], "malis.creyk@gmail.com");
			$this->assertNotTrue($result['show_phone']);
			$this->assertNotTrue($result['show_email']);
			$this->assertEquals($result['type'], 0);
			$this->assertJson($result['majors'], "BCSF, BDF, BSSF, BSSP, BIT, BBIS");
			$this->assertNull($result['choices']);
			$this->assertNull($result['year']);
			$this->assertNull($result['quarter']);
		}
		
		/*
			Constructor
		*/
		public function setUp() : void{
			$id = "10293074";

			$query = db_query("SELECT * FROM `accounts` WHERE `sim_id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the account exists
			if(mysqli_num_rows($query) == 1){
				$this->sim_id = $result['sim_id'];
				$this->uow_id = $result['uow_id'];
				$this->name = $result['name'];
				$this->password = $result['password'];
				$this->phone = $result['phone'];
				$this->sim_email = $result['sim_email'];
				$this->personal_email = $result['personal_email'];
				$this->show_phone = $result['show_phone'];
				$this->show_email = $result['show_email'];
				$this->account_type = $result['type'];
				$this->raw = $result;
				$this->majors = json_decode($this->raw['majors']);

			}else{
				throw new Exception("Account not found.");
			}
		}
		
		/*
			Tests the method to get name
		*/
		function test_get_name(){
			return $this->assertEquals($this->name, "Malissia Creyke");
		}
        
		/*
			Tests the method to get phone number
		*/
		public function test_get_phone(){
			return $this->assertEquals($this->phone, "83987712");
		}
		
		/*
			Tests the method to get SIM E-mail
		*/
		public function test_get_sim_email(){
			return $this->assertEquals($this->sim_email, "malis.creyk@mymail.sim.edu.sg");
		}
		
		/*
			Tests the method to get personal E-mail
		*/
		public function test_get_personal_email(){
			return $this->assertEquals($this->personal_email, "malis.creyk@gmail.com");
		}
		
		/*
			Tests the method to get account type in int
		*/
		public function test_get_type_int(){
			return $this->assertEquals($this->account_type, 0);
		}		
		/*
			Tests the method to get account type
		*/
		public function test_get_account_type(){
			return $this->assertEquals($this->account_type, 0);
		}
		
		/*
			Checks if the account is a faculty member
			
			@return bool
		*/
		public function test_is_faculty(){
			return $this->assertTrue($this->account_type == 0);
		}
		
		/*
			Checks if the account is a full time student
			
			@return bool
		*/
		public function test_is_full_time_student(){
			return $this->assertNotTrue($this->account_type == 1);
		}

		/*
			Checks if the account is a part time student
			
			@return bool
		*/
		public function test_is_part_time_student(){
			return $this->assertNotTrue($this->account_type == 2);
		}
		
		/*
			Checks if the account is an admin
			
			@return bool
		*/
		public function test_is_admin(){
			return $this->assertNotTrue($this->account_type == 8);
		}
		
		/*
			Checks if the account is a super admin
			
			@return bool
		*/
		public function test_is_super(){
			return $this->assertNotTrue($this->account_type == 9);
		}

		/*
			Checks show phone feature
			
			@return bool
		*/
		public function test_show_phone(){
			return $this->assertNotTrue($this->show_phone);
		}	

		/*
			Checks show email feature
			
			@return bool
		*/
		public function test_show_email(){
			return $this->assertNotTrue($this->show_email);
		}
		
		/*
			Checks set password
			
			@return bool
		*/
		public function test_set_password(){
			$id = "10293074";
			$ori_password = $this->password;
			$temp_String = generate_password();
			
			// set new password equals to temp_String
			db_query("UPDATE `accounts` SET `password` = '{$temp_String}' WHERE `sim_id` = '{$id}'");

			// get the account with same id and new password
			$query = db_query("SELECT * FROM `accounts` WHERE `sim_id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);

			// If the account exists
			if(mysqli_num_rows($query) == 1){
				$this->password = $result['password'];
			}else{
				throw new Exception("Account not found.");
			}

			// get the new password
			$new_password = $this->password;

			// set password back to original password
			db_query("UPDATE `accounts` SET `password` = '{$ori_password}' WHERE `sim_id` = '{$id}'");

			return $this->assertEquals($new_password, $temp_String);

		}		

		/*
			Checks major as string
			
			@return bool
		*/
        public function test_get_majors_as_String() {
            return $this->assertEquals(implode(", ", $this->majors), "BCSF, BDF, BSSF, BSSP, BIT, BBIS");
        }

		/*
			Checks major as JSON
			
			@return bool
		*/
        public function test_get_majors_as_JSON() {
            return $this->assertJson(json_encode($this->majors), "BCSF, BDF, BSSF, BSSP, BIT, BBIS");
        }
	}
	
	//Close connection
	//@mysqli_close($GLOBALS['mysql_link']);
?>