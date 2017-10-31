<?php

require 'TestCase.php';

class AdvTest extends TestCase
{
	public function testLaunch()
	{
		$url = $this->url . 'adv/launch';
		$params = array('isiphone5'=>rand(0,1));
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}

    public function testOpenwin()
	{
		$url = $this->url . 'adv/launch';
		$params = array('entrance'=>rand(0,1));
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}
}