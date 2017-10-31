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

	'fetch' => PDO::FETCH_ASSOC,

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
			'host'      => 'youxiduo-database-club-1',
			'database'  => 'yxd_club_beta',
			'username'  => 'admin',
			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'yxd_',
		),
		'club' => array(
			'driver'    => 'mysql',
			'host'      => 'youxiduo-database-club-1',
			'database'  => 'yxd_club_beta',
			'username'  => 'admin',
			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'yxd_',
		),
		'android' => array(
			'driver'    => 'mysql',
			'host'      => 'youxiduo-database-android-1',
			'database'  => 'yxd_android',
			'username'  => 'admin',
			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'yxd_',
		),		
                'share_activity' => array(
                        'driver'    => 'mysql',
                        'host'      => 'youxiduo-database-share-1',
                        'database'  => 'yxd_share_activity_android',
                        'username'  => 'admin',
                        'password'  => 'somethingyouxiduo',
                        'charset'   => 'utf8',
                        'collation' => 'utf8_bin',
                        'prefix'    => 'yxd_',
                ),
		'system' => array(
			'driver'    => 'mysql',
			'host'      => 'youxiduo-database-club-1',
			'database'  => 'yxd_club_beta',
			'username'  => 'admin',
			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'core_',
		),
                'forum' => array(
                        'driver'    => 'mysql',
                        'host'      => 'youxiduo-database-1',
                        'database'  => 'module_forum',
                        'username'  => 'admin',
                        'password'  => 'somethingyouxiduo',
                        'charset'   => 'utf8',
                        'collation' => 'utf8_bin',
                        'prefix'    => '',
                ),
		'cms' => array(
		    'driver'    => 'mysql',
			'host'      => 'youxiduo-database-www-1',
			'database'  => 'yxd_www',
			'username'  => 'admin',
			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'm_',
		),
		'activity' => array(
				'driver'    => 'mysql',
				'host'      => 'youxiduo-database-activity-1',
				'database'  => 'yxd_activity',
				'username'  => 'admin',
				'password'  => 'somethingyouxiduo',
				'charset'   => 'utf8',
				'collation' => 'utf8_bin',
				'prefix'    => 'yxd_',
		),
		'report' => array(
            'driver'    => 'mysql',
            'host'      => 'youxiduo-database-1',
            'database'  => 'service_download_stats',
            'username'  => 'admin',
            'password'  => 'somethingyouxiduo',
            'charset'   => 'utf8',
            'collation' => 'utf8_bin',
            'prefix'    => '',
        ),
        'module_account' => array(
            'driver'    => 'mysql',
            'host'      => 'youxiduo-database-2',
            'database'  => 'module_account',
            'username'  => 'admin',
            'password'  => 'somethingyouxiduo',
            'charset'   => 'utf8',
            'collation' => 'utf8_bin',
            'prefix'    => '',
        ),
        'module_mall' => array(
            'driver'    => 'mysql',
            'host'      => 'youxiduo-database-2',
            'database'  => 'module_mall',
            'username'  => 'admin',
            'password'  => 'somethingyouxiduo',
            'charset'   => 'utf8',
            'collation' => 'utf8_bin',
            'prefix'    => '',
        ),
                'ios_club' => array(
            'driver'    => 'mysql',
            'host'      => 'youxiduo-database-ios-club-1',
            'database'  => 'yxd_ios_club',
            'username'  => 'admin',
            'password'  => 'somethingyouxiduo',
            'charset'   => 'utf8',
            'collation' => 'utf8_bin',
            'prefix'    => 'yxd_',
        ),
		'mobile' => array(
				'driver'    => 'mysql',
				'host'      => 'mobile.cwan.youxiduo.com',
				'database'  => 'cwan_mobile',
				'username'  => 'admin',
				'password'  => 'somethingyouxiduo',
				'charset'   => 'utf8',
				'collation' => 'utf8_bin',
				'prefix'    => 'cwan_',
		),
		
		'yxd' => array(
			'driver'    => 'mysql',
			'host'      => 'mobile.cwan.youxiduo.com',
			'database'  => 'cwan_mobile',
			'username'  => 'admin',
			'password'  => 'somethingyouxiduo',
			'charset'   => 'utf8',
			'collation' => 'utf8_bin',
			'prefix'    => 'cwan_',
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
			'host'     => 'youxiduo-database-www-1',
			'port'     => 6379,
			'database' => 0,
		),
                'queue' => array(
                        'host'     => 'youxiduo-database-www-1',
                        'port'     => 6379,
                        'database' => 0,
                ),

	),

);
