<?php
	use PHPUnit\Framework\TestCase;

	require_once(dirname(__FILE__)."/../include/class/ClassAccount.php");
    require_once(dirname(__FILE__)."/../include/class/ClassStudent.php");

	sql_connect();
	
	/*
		Test if ticket exists as seen from triager
	*/
	class FullTimeStudentTest extends TestCase{
		
		public	$sim_id;
		public	$uow_id;
		
		private	$name;
		private	$phone;
		private	$sim_email;
		private	$personal_email;
		protected $account_type;
		
		protected $majors;
        private $choices;
		private $year;
		private $quarter;

		/*
			Tests the constructor
		*/
		function test_construct(){
            
            //Declare full-time student variable
			$id = "10280958";
			
			$query = db_query("SELECT * FROM `accounts` WHERE `sim_id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			$this->assertEquals($result['sim_id'], "10280958");
			$this->assertEquals($result['uow_id'], "6693834");
			$this->assertEquals($result['name'], "Clement Tanby");
			$this->assertEquals($result['password'], "OQGJrSSz6D");
			$this->assertEquals($result['phone'], "92696203");
			$this->assertEquals($result['sim_email'], "cleme.tanby@mymail.sim.edu.sg");
			$this->assertEquals($result['personal_email'], "cleme.tanby@hotmail.com");
			$this->assertEquals($result['type'], 1);
			$this->assertJson($result['majors'], "SG766");
			$this->assertNull($result['choices']);
			$this->assertEquals($result['year'], 2021);
			$this->assertEquals($result['quarter'], 1);   
		}
		
		/*
			Constructor
		*/
		public function setUp() : void{
			$id = "10280958";

			$query = db_query("SELECT * FROM `accounts` WHERE `sim_id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);
			
			//If the account exists
			if(mysqli_num_rows($query) == 1){
				$this->sim_id = $result['sim_id'];
				$this->uow_id = $result['uow_id'];
				$this->name = $result['name'];
				$this->phone = $result['phone'];
				$this->sim_email = $result['sim_email'];
				$this->personal_email = $result['personal_email'];
				$this->account_type = $result['type'];

				$this->raw = $result;
			}else{
				throw new Exception("Account not found.");
			}

            if($this->account_type != 1){
				throw new Exception("Account is not a full time student.");
			}else{
				$this->majors = json_decode($this->raw['majors'])[0];
				$this->choices = json_decode($this->raw['choices']);
				$this->year = json_decode($this->raw['year']);			
				$this->quarter = json_decode($this->raw['quarter']);
            }
		}

		function test_get_name(){
			return $this->assertEquals($this->name, "Clement Tanby");
		}
		
		/*
			Tests the method to get phone number
		*/
		public function test_get_phone(){
			return $this->assertEquals($this->phone, "92696203");
		}
		
		/*
			Tests the method to get SIM E-mail
		*/
		public function test_get_sim_email(){
			return $this->assertEquals($this->sim_email, "cleme.tanby@mymail.sim.edu.sg");
		}
		
		/*
			Tests the method to get personal E-mail
		*/
		public function test_get_personal_email(){
			return $this->assertEquals($this->personal_email, "cleme.tanby@hotmail.com");
		}
		
		/*
			Tests the method to get account type
		*/
		public function test_get_account_type(){
			return $this->assertEquals($this->account_type, 1);
		}
		
		/*
			Checks if the account is a faculty member
			
			@return bool
		*/
		public function test_is_faculty(){
			return $this->assertNotTrue($this->account_type == 0);
		}
		
		/*
			Checks if the account is a student
			
			@return bool
		*/
		public function test_is_student(){
			return $this->assertTrue($this->account_type == 1 || $this->account_type == 2);
		}

		/*
			Checks if the account is a full time student
			
			@return bool
		*/
		public function test_is_full_time(){
			return $this->assertTrue($this->account_type == 1);
		}

		/*
			Checks if the account is a part time student
			
			@return bool
		*/
        public function test_is_part_time() {
            return $this->assertNotTrue($this->account_type == 2);
        }
		
		/*
			Checks if the account is an admin
			
			@return bool
		*/
		public function test_is_admin(){
			return $this->assertNotTrue($this->account_type == 8 || $this->account_type == 9);
		}
		
		/*
			Checks if the account is a super admin
			
			@return bool
		*/
		public function test_is_super(){
			return $this->assertNotTrue($this->account_type == 9);
		}

        		/*
			Test the major of account
		*/        
        public function test_get_majors() {
            return $this->assertEquals($this->majors, "SG766");
        }        

		/*
			Test the year of account
		*/        
        public function test_get_year() {
            return $this->assertEquals($this->year, 2021);
        }        

		/*
			Test the quarter of account
		*/        
        public function test_get_quarter() {
            return $this->assertEquals($this->quarter, 1);
        }        
	}
	
	//Close connection
	//@mysqli_close($GLOBALS['mysql_link']);
?>