<?php
require 'TestCase.php';

class GiftbagTest extends TestCase{
	
	public function testGiftbagDetail(){
		$url = $this->url. 'gift/detail';
		$params = array('gfid'=>912,'uid'=>4);
		$method = 'GET';
		$file = false;
		$result = CwanHttp::request($url,$params,$method,$file);
		$this->assertEquals('1',$result['result']['btnshow'],$result['errorMessage']);
	}
}