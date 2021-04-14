<?php
	use PHPUnit\Framework\TestCase;
	require_once(dirname(__FILE__)."/../include/class/ClassMajor.php");
	require_once(dirname(__FILE__)."/../include/funcs/sql_funcs.php");

	sql_connect();
	
	/*
		Test if ticket exists as seen from triager
	*/
	class ClassMajorTest extends TestCase{
		public	$id;
		private	$name;
		private	$description;
		private $type;	

		function test_construct() {
			# Declare the major to test
			$id = "BCSF";
			$desc = "This programme provides understanding in the structure of data and the role it plays in delivering solutions to complex problems. In this major, you will learn to use current and emerging technologies related to cyber security, such as blockchain, cryptocurrency, multimedia security, Internet of Things (IoT) security and obfuscation.";

			$query = db_query("SELECT * FROM `majors` WHERE `id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);			

			$this->assertEquals($result['id'], "BCSF");
			$this->assertEquals($result['name'], "Bachelor of Computer Science (Cyber Security)");
			$this->assertEquals($result['description'], $desc);
			$this->assertEquals($result['type'], 1);
		}

		/*
			Constructor
		*/
		public function setUp() : void{
			$id = "BCSF";

			$query = db_query("SELECT * FROM `majors` WHERE `id` = '{$id}' LIMIT 1;");
			$result = mysqli_fetch_assoc($query);	
			
			//If the account exists
			if(mysqli_num_rows($query) == 1){
				$this->id = $result['id'];
				$this->name = $result['name'];
				$this->description = $result['description'];
				$this->type = $result['type'];

				$this->raw = $result;
			}else{
				throw new Exception("Major not found.");
			}
		}		

		/*
			Tests get name method
		*/		
		public function test_get_name() {
			return $this->assertEquals($this->name, "Bachelor of Computer Science (Cyber Security)");
		}

		/*
			Tests get description method
		*/		
		public function test_get_description() {
			$desc = "This programme provides understanding in the structure of data and the role it plays in delivering solutions to complex problems. In this major, you will learn to use current and emerging technologies related to cyber security, such as blockchain, cryptocurrency, multimedia security, Internet of Things (IoT) security and obfuscation.";

			return $this->assertEquals($this->description, $desc);
		}

		/*
			Tests get type method
		*/		
		public function test_get_type() {
			return $this->assertEquals($this->type, 1);
		}		

		/*
			Tests get student type method
		*/		
		public function test_get_student_type() {
			$major = new Major("BCSF");
			return $this->assertEquals((($major->is_full_time()) ? 1 : 2), 1);
		}			

		/*
			Tests is full time method
		*/		
		public function test_is_full_time() {
			return $this->assertTrue($this->type == 1);
		}			

		/*
			Tests is part time method
		*/		
		public function test_is_part_time() {
			return $this->assertNotTrue($this->type == 0);
		}					
	}
	
	//Close connection
	//@mysqli_close($GLOBALS['mysql_link']);
?>