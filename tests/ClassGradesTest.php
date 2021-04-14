<?php
	use PHPUnit\Framework\TestCase;

	require_once(dirname(__FILE__)."/../include/class/ClassGrades.php");
	
	/*
		Test if ticket exists as seen from triager
	*/
	class ClassGradesTest extends TestCase{
		public function test_get_grade() {
            $this->assertEquals(Grades::get_grade(85), "HD");
            $this->assertEquals(Grades::get_grade(100), "HD");
            $this->assertEquals(Grades::get_grade(84), "D");
            $this->assertEquals(Grades::get_grade(75), "D");
            $this->assertEquals(Grades::get_grade(74), "C");
            $this->assertEquals(Grades::get_grade(65), "C");
            $this->assertEquals(Grades::get_grade(64), "P");
            $this->assertEquals(Grades::get_grade(50), "P");
            $this->assertEquals(Grades::get_grade(49), "F");
            $this->assertEquals(Grades::get_grade(1), "F");
        }
	}
	
	//Close connection
	//@mysqli_close($GLOBALS['mysql_link']);
?>