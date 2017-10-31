<?php
$config = require __DIR__ . '/../../../config/database.php';
$config['connections']['profile'] = array(
		    'driver'    => 'mysql',
			'host'      => '127.0.0.1',
			'database'  => 'mcp_profile',
			'username'  => 'root',
			'password'  => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'app_',
		);
/***		
$config['connections']['adv'] = array(
		    'driver'    => 'mysql',
			'host'      => 'localhost',
			'database'  => 'yxd_advert',
			'username'  => 'root',
			'password'  => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'yxd_advert_',
			
		);		
*****/
$config['connections']['adv'] = array(
		    'driver'    => 'mysql',
			//'host'      => 'localhost',
			'host'      => '127.0.0.1',
			'database'  => 'yxd_advert',
			//'username'  => 'root',
			//'password'  => '',
			'username'  => 'root',
			'password'  => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'yxd_advert_',
			
		);		
return $config;
/***
	'driver'    => 'mysql',
			'host'      => '127.0.0.1',
			'database'  => 'yxd_advert',
			'username'  => 'root',
			'password'  => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'yxd_advert_',
***/