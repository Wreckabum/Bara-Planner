<?php
	/*
		Score to grade
	*/
	class Grades{
		public static function get_grade($score){
			if(is_null($score)){
				return false;
			}
			
			$score = (int)$score;
			
			if($score >= 85){
				return "HD";
			}elseif($score >= 75){
				return "D";
			}elseif($score >= 65){
				return "C";
			}elseif($score >= 50){
				return "P";
			}else{
				return "F";
			}
		}
	}
?>