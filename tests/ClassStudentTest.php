<?php
	use PHPUnit\Framework\TestCase;

	require_once(dirname(__FILE__)."/../include/class/ClassAccount.php");
    require_once(dirname(__FILE__)."/../include/class/ClassStudent.php");

	sql_connect();
	
	/*
		Test if ticket exists as seen from triager
	*/
	class ClassStudentTest extends TestCase{
		
        public $pt_student;
        public $ft_student;

		/*
			Tests the constructor
		*/
		function test_construct(){
			//Declare full-time student variables
			$ft_id = "10280958";
			
			$query = db_query("SELECT * FROM `accounts` WHERE `sim_id` = '{$ft_id}' LIMIT 1;");
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

            
            //Declare part-time student variables
            $pt_id = "10290953";

			$query = db_query("SELECT * FROM `accounts` WHERE `sim_id` = '{$pt_id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);

			$this->assertEquals($result['sim_id'], "10290953");
			$this->assertEquals($result['uow_id'], "6636167");
			$this->assertEquals($result['name'], "Gerri Lorincz");
			$this->assertEquals($result['password'], "Hs0vkZPZhi");
			$this->assertEquals($result['phone'], "84933406");
			$this->assertEquals($result['sim_email'], "gerri.lorin@mymail.sim.edu.sg");
			$this->assertEquals($result['personal_email'], "gerri.lorin@live.com");
			$this->assertEquals($result['type'], 2);
			$this->assertJson($result['majors'], "SG868");
			$this->assertNull($result['choices']);
			$this->assertEquals($result['year'], 2021);
			$this->assertEquals($result['quarter'], 1);            
		}
		
		/*
			Constructor
		*/
		public function setUp() : void{
			$ft_id = "10280958";
            $pt_id = "10290953";

            $this->ft_student = new Student($ft_id);
            $this->pt_student = new Student($pt_id);
		}
		
		/*
			Tests the method to get name
		*/
		function test_get_name(){
            $this->assertEquals($this->ft_student->get_name(), "Clement Tanby");
			$this->assertEquals($this->pt_student->get_name(), "Gerri Lorincz");
		}

		/*
			Tests the method to get phone number
		*/
		public function test_get_phone(){
			$this->assertEquals($this->ft_student->get_phone(), "92696203");
			$this->assertEquals($this->pt_student->get_phone(), "84933406");
		}
		
		/*
			Tests the method to get SIM E-mail
		*/
		public function test_get_sim_email(){
			$this->assertEquals($this->ft_student->get_sim_email(), "cleme.tanby@mymail.sim.edu.sg");
			$this->assertEquals($this->pt_student->get_sim_email(), "gerri.lorin@mymail.sim.edu.sg");
		}
		
		/*
			Tests the method to get personal E-mail
		*/
		public function test_get_personal_email(){
			$this->assertEquals($this->ft_student->get_personal_email(), "cleme.tanby@hotmail.com");
			$this->assertEquals($this->pt_student->get_personal_email(), "gerri.lorin@live.com");
		}
		
		/*
			Tests the method to get account type
		*/
		public function test_get_account_type(){
			$this->assertEquals($this->ft_student->get_account_type(), "Full-time Student");
			$this->assertEquals($this->pt_student->get_account_type(), "Part-time Student");
		}
		
		/*
			Checks if the account is a faculty member
			
			@return bool
		*/
		public function test_is_faculty(){
			$this->assertNotTrue($this->ft_student->is_faculty());
			$this->assertNotTrue($this->pt_student->is_faculty());
		}
		
		/*
			Checks if the account is a student
			
			@return bool
		*/
		public function test_is_student(){
			$this->assertTrue($this->ft_student->is_student());
			$this->assertTrue($this->pt_student->is_student());
		}
		
		/*
			Checks if the account is an admin
			
			@return bool
		*/
		public function test_is_admin(){
			$this->assertNotTrue($this->ft_student->is_admin());
			$this->assertNotTrue($this->pt_student->is_admin());
		}
		
		/*
			Checks if the account is a super admin
			
			@return bool
		*/
		public function test_is_super(){
			$this->assertNotTrue($this->ft_student->is_super());
			$this->assertNotTrue($this->pt_student->is_super());
		}

		/*
			Test the major of account
		*/        
        public function test_get_majors() {
            $this->assertEquals($this->ft_student->get_majors(true), "SG766");
            $this->assertEquals($this->pt_student->get_majors(true), "SG868");
        }        

		/*
			Test the year of account
		*/        
        public function test_get_year() {
            $this->assertEquals($this->ft_student->get_year(), 2021);
            $this->assertEquals($this->pt_student->get_year(), 2021);
        }        

		/*
			Test the quarter of account
		*/        
        public function test_get_quarter() {
            $this->assertEquals($this->ft_student->get_quarter(), 1);
            $this->assertEquals($this->pt_student->get_quarter(), 1);
        }        

		/*
			Test the full-time mode of account
		*/        
        public function test_is_full_time() {
            $this->assertTrue($this->ft_student->is_full_time());
            $this->assertNotTrue($this->pt_student->is_full_time());
        }        

		/*
			Test the part-time mode of account
		*/        
        public function test_is_part_time() {
            $this->assertNotTrue($this->ft_student->is_part_time());
            $this->assertTrue($this->pt_student->is_part_time());
        }        
        
	}
	
	//Close connection
	//@mysqli_close($GLOBALS['mysql_link']);
?>