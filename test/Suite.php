<?php
require_once 'PHPUnit2/Framework/TestSuite.php';
/*
require_once 'PHPUnit2/TextUI/TestRunner.php';

$suite = new PHPUnit2_Framework_TestSuite(); 

$suite->addTestFile('UserTest.php'); 

PHPUnit2_TextUI_TestRunner::run($suite);  
*/
class MyTestSuite extends PHPUnit2_Framework_TestSuite 
{  
	public function __construct()
	{  
			$this->addTestFile('UserTest.php');  
	}  
	
	public static function suite() 
	{  
		return new self();  
	}  
}  