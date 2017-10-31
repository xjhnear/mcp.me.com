<?php

require 'TestCase.php';

class AccountTest extends TestCase
{
	public function testLogin()
	{
		$url = $this->url . 'account/login';
		$params = array('email'=>'push@163.com','pwd'=>'111111w');
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}

	
}