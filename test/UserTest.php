<?php

require 'TestCase.php';

class UserTest extends TestCase
{
	public function testUserInfo()
	{
		$url = $this->url . 'user/info';
		$params = array('uid'=>'100135');
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}

	public function testMoney()
	{
		$url = $this->url . 'user/money';
		$params = array('uid'=>'100135');
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}
	
    public function testFeeds()
	{
		$url = $this->url . 'user/feeds';
		$params = array('uid'=>'100135');
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}
	
    public function testAtme()
	{
		$url = $this->url . 'user/atme';
		$params = array('uid'=>'100135');
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);		
		$this->assertEquals('0',$result['errorCode'],$result['errorMessage']);
	}
}