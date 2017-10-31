<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| PDO Fetch Style
	|--------------------------------------------------------------------------
	|
	| By default, database results will be returned as instances of the PHP
	| stdClass object; however, you may desire to retrieve records in an
	| array format for simplicity. Here you can tweak the fetch style.
	|
	*/

	//'fetch' => PDO::FETCH_CLASS,

	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

	'default' => 'mysql',

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => array(

		'mysql' => array(
			'driver'    => 'mysql',
 			'host'      => 'test.youxiduo.com',
 			'database'  => 'yxd_club_beta',
 			'username'  => 'admin',
 			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => 'yxd_',
		),
		'club' => array(
			'driver'    => 'mysql',
 			'host'      => 'test.youxiduo.com',
 			'database'  => 'yxd_club_beta',
 			'username'  => 'admin',
 			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => 'yxd_',
		),
		'system' => array(
			'driver'    => 'mysql',
 			'host'      => 'test.youxiduo.com',
 			'database'  => 'yxd_club_beta',
 			'username'  => 'admin',
 			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => 'core_',
		),
		'cms' => array(
			'driver'    => 'mysql',
 			'host'      => 'test.youxiduo.com',
 			'database'  => 'yxd_www',
 			'username'  => 'admin',
 			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => 'm_',
		),
		'android' => array(
			'driver'    => 'mysql',
 			'host'      => 'test.youxiduo.com',
 			'database'  => 'yxd_android',
 			'username'  => 'admin',
 			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => 'yxd_',
		),
		'yxd' => array(
			'driver'    => 'mysql',
 			'host'      => 'test.youxiduo.com',
 			'database'  => 'cwan_mobile',
 			'username'  => 'admin',
 			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => 'cwan_',
		),
		'share_activity' => array(
			'driver'    => 'mysql',
 			'host'      => 'test.youxiduo.com',
 			'database'  => 'yxd_share_activity',
 			'username'  => 'admin',
 			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => 'yxd_',
		),
		'sqlite' => array(
			'driver'   => 'sqlite',
			//'database' => '/mnt/nfs/data/yxd_www/bestofbest/dbsqlite/youxiduo.db',
			'database' => storage_path() . '/logs/youxiduo.db',
			'prefix'   => 'm_',
		),
	),

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk have not actually be run in the databases.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer set of commands than a typical key-value systems
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => array(

		'cluster' => false,

		'default' => array(
 			'host'     => 'test.youxiduo.com',
//			'host'	   => '192.168.2.172',
			//'host'=>'192.168.2.222',
			'port'     => 6379,
			'database' => 0,
		),

	),

);