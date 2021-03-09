<?php
	use PHPUnit\Framework\TestCase;

	require_once(dirname(__FILE__)."/../include/class/ClassAccount.php");
    require_once(dirname(__FILE__)."/../include/class/ClassFaculty.php");

	sql_connect();
	
	/*
		Test if ticket exists as seen from triager
	*/
	class ClassFacultyTest extends TestCase{
		
        public $faculty_member;

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
			$this->assertEquals($result['type'], 0);
			$this->assertJson($result['majors'], "SG111, SG766, SG122, SG133, SG868, SG144");
			$this->assertNull($result['choices']);
			$this->assertNull($result['year']);
			$this->assertNull($result['quarter']);
		}
		
		/*
			Constructor
		*/
		public function setUp() : void{
			$id = "10293074";

            $this->faculty_member = new Faculty($id);
		}
		
		/*
			Tests the method to get name
		*/
		function test_get_name(){
			return $this->assertEquals($this->faculty_member->get_name(), "Malissia Creyke");
		}
        
		/*
			Tests the method to get phone number
		*/
		public function test_get_phone(){
			return $this->assertEquals($this->faculty_member->get_phone(), "83987712");
		}
		
		/*
			Tests the method to get SIM E-mail
		*/
		public function test_get_sim_email(){
			return $this->assertEquals($this->faculty_member->get_sim_email(), "malis.creyk@mymail.sim.edu.sg");
		}
		
		/*
			Tests the method to get personal E-mail
		*/
		public function test_get_personal_email(){
			return $this->assertEquals($this->faculty_member->get_personal_email(), "malis.creyk@gmail.com");
		}
		
		/*
			Tests the method to get account type
		*/
		public function test_get_account_type(){
			return $this->assertEquals($this->faculty_member->get_account_type(), "Faculty");
		}
		
		/*
			Checks if the account is a faculty member
			
			@return bool
		*/
		public function test_is_faculty(){
			return $this->assertTrue($this->faculty_member->is_faculty());
		}
		
		/*
			Checks if the account is a student
			
			@return bool
		*/
		public function test_is_student(){
			return $this->assertNotTrue($this->faculty_member->is_student());
		}
		
		/*
			Checks if the account is an admin
			
			@return bool
		*/
		public function test_is_admin(){
			return $this->assertNotTrue($this->faculty_member->is_admin());
		}
		
		/*
			Checks if the account is a super admin
			
			@return bool
		*/
		public function test_is_super(){
			return $this->assertNotTrue($this->faculty_member->is_super());
		}

        public function test_get_majors() {
            return $this->assertEquals($this->faculty_member->get_majors(true), "SG111, SG766, SG122, SG133, SG868, SG144");
        }
	}
	
	//Close connection
	//@mysqli_close($GLOBALS['mysql_link']);
?>