<?php
require 'TestCase.php';

class HomeTest extends TestCase
{
	public function testHome()
	{
		$url = $this->url . 'home';
		$params = array();
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}

	
}