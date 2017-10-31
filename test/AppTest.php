<?php
require 'TestCase.php';

class AppTest extends TestCase
{
	public function testGetConfig()
	{
		$url = $this->url . 'app/config';
		$params = array();
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}

    public function testCheckVersion()
	{
		$url = $this->url . 'app/check-version';
		$params = array();
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}
}