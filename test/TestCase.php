<?php
//require_once 'PHPUnit2/Framework/TestCase.php';

require_once 'PHPUnit.php';

class TestCase extends \PHPUnit_Framework_TestCase
{
	protected $url = '';
	public function setUp()
	{
		parent::setUp();
		require_once __DIR__ . '/http.php';
		$this->url = 'http://test.open.youxiduo.com/';
	}
}