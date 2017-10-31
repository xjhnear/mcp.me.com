<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Application Debug Mode
	|--------------------------------------------------------------------------
	|
	| When your application is in debug mode, detailed error messages with
	| stack traces will be shown on every error that occurs within your
	| application. If disabled, a simple generic error page is shown.
	|
	*/

	'debug' => true,
	'openlog'=>true,
	'firephp'=>true,
	'close_cache' => true,
    'close_redis_user'=>true,
    'api'=>'http://open.youxiduo.com',
    'domain'=>'youxiduo.com',
    'urlsecret'=>'ABCD1234YOUXIDUO',
    'des_secret'=>'11111111',
   
	/*add by jk,2014-07-16 for test*/
	'test_urlsecret'=>'ABCD1234YOUXIDUO',
	'apple_push_debug'=>false,
    'apple_push_pem'=> dirname(__FILE__) . '/push.pem',
    'apple_push_passphrase'=>'',
	'apple_push'=>array(
        array('apple_push_pem'=> dirname(__FILE__) . '/push_chaoren.pem','apple_push_passphrase'=>false),
        array('apple_push_pem'=> dirname(__FILE__) . '/push_jqb.pem','apple_push_passphrase'=>false),
        array('apple_push_pem'=> dirname(__FILE__) . '/push_20150415_1.pem','apple_push_passphrase'=>false),
		array('apple_push_pem'=> dirname(__FILE__) . '/push_fufei.pem','apple_push_passphrase'=>false),
        array('apple_push_pem'=> dirname(__FILE__) . '/push_20150129_1.pem','apple_push_passphrase'=>false),
	    array('apple_push_pem'=> dirname(__FILE__) . '/push_20150129_2.pem','apple_push_passphrase'=>false),
	    array('apple_push_pem'=> dirname(__FILE__) . '/push_20150129_3.pem','apple_push_passphrase'=>false),
        //array('apple_push_pem'=> dirname(__FILE__) . '/push_youxiduo.pem','apple_push_passphrase'=>false),
    ),

	/*
	|--------------------------------------------------------------------------
	| Application URL
	|--------------------------------------------------------------------------
	|
	| This URL is used by the console to properly generate URLs when using
	| the Artisan command line tool. You should set this to the root of
	| your application so that it is used when running Artisan tasks.
	|
	*/

	'url' => '',
    'img_url' => 'http://img.youxiduo.com',
    'image_url' => 'http://img.youxiduo.com',
    'game_icon_path' => dirname(dirname(dirname(__DIR__))).'/yxd_www/bestofbest',

    'android_core_api_url'=>'http://youxiduo-database-1:8080/',
    'android_chat_api_url'=>'http://chat.youxiduo.com/',
    'android_admin_huanxin_id'=>'yxdadmin1',
    'android_api_url'=>'http://youxiduo-database-1:8080/', //http://android.api.youxiduo.com
    'bbs_api_url'=>'http://youxiduo-java-slb-2:28080/module_forum/',
    //'mall_api_url'=>'http://localhost:8080/module_mall/',
    'mall_api_url'=>'http://youxiduo-java-slb-4:48080/module_mall/',
    'ios_mall_api_url'=>'http://youxiduo-java-slb-4:48080/module_mall/',
    'mall_rlt_api_url'=>'http://youxiduo-database-1:8080/module_relevance/',
    'ios_mall_rlt_api_url'=>'http://youxiduo-java-slb-2:28080/module_relevance/',
    //'mall_mml_api_url'=>'http://localhost:8080/module_mall/',
    'mall_mml_api_url'=>'http://youxiduo-java-slb-4:48080/module_mall/',
    'ios_mall_mml_api_url'=>'http://youxiduo-java-slb-4:48080/module_mall/',
    //'virtual_card_url'=>'http://localhost:8080/module_virtualcard/',
    'virtual_card_url'=>'http://youxiduo-java-slb-4:48080/module_virtualcard/',
	'ios_virtual_card_url'=>'http://youxiduo-java-slb-4:48080/module_virtualcard/',
    'game_forum_api_url'=>'http://youxiduo-java-slb-2:28080/module_relevance/',
    'ios_core_api_url'=>'http://youxiduo-java-slb-2:28080/',
    'module_data_url'=>'http://10.161.181.86:8080/',
    'push_api_url'=>'http://youxiduo-java-slb-5:58080/service_push/',
    'account_api_url'=>'http://youxiduo-java-slb-4:48080/module_account/',
    'material_api_url'=>'http://youxiduo-java-slb-4:48080/module_material/',
    'module_lottery_api_url'=>'http://youxiduo-java-slb-4:48080/module_lottery/',
    'module_wheel_api_url'=>'http://youxiduo-java-slb-4:48080/module_wheel/',
    'task_api_url'=>'http://youxiduo-java-slb-5:58080/module_task/',
    //'account_api_url'=>'http://localhost:8080/module_account/',
    'monitor_api_url'=>'http://10.161.181.86:8080/service_download_stats/',
    'ESports_api_url'=>'http://10.168.196.111:19011/',
	'ESports_api_url2'=>'http://10.168.196.111:29080/',
	'Tuiguang_api_url'=>'http://youxiduo-java-slb-4:48080/',
	'V4share_api_url'=>'http://youxiduo-java-slb-4:48080/module_share/',
	'liansai_api_url'=>'http://youxiduo-java-1:8010/',
	'user_api_url'=>'http://youxiduo-java-slb-4:48080/',

	/*
	|--------------------------------------------------------------------------
	| Application Timezone
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default timezone for your application, which
	| will be used by the PHP date and date-time functions. We have gone
	| ahead and set this to a sensible default for you out of the box.
	|
	*/

	'timezone' => 'Asia/Shanghai',

	/*
	|--------------------------------------------------------------------------
	| Application Locale Configuration
	|--------------------------------------------------------------------------
	|
	| The application locale determines the default locale that will be used
	| by the translation service provider. You are free to set this value
	| to any of the locales which will be supported by the application.
	|
	*/

	'locale' => 'cn',

	/*
	|--------------------------------------------------------------------------
	| Encryption Key
	|--------------------------------------------------------------------------
	|
	| This key is used by the Illuminate encrypter service and should be set
	| to a random, 32 character string, otherwise these encrypted strings
	| will not be safe. Please do this before deploying an application!
	|
	*/

	'key' => md5('zhonghuarenmingongheguo!!'),
    'verifycode'=>true,

    'oauth2'=>array(
        'driver'=>'redis',
        'pdo'=>array(
            'dsn'=>'mysql:dbname=yxd_club;host=localhost',
            'username'=>'yxd_club',
            'password'=>'zdETfuHtPtzMBAklKluF7Q=='
        ),
        'redis'=>array()
        
    ), 

	'huanxin' => array(
			'client_id'=>'YXA63IO58FsnEeS1RPu-EwfJhA',
			'client_secret'=>'YXA6s9DTMltbJ9Y3Jhk3QvxXfKvxBso',
			'org_name'=>'yxd',
			'app_name'=>'yxdtest1'
	),
	
	/*
	|--------------------------------------------------------------------------
	| Autoloaded Service Providers
	|--------------------------------------------------------------------------
	|
	| The service providers listed here will be automatically loaded on the
	| request to your application. Feel free to add your own services to
	| this array to grant expanded functionality to your applications.
	|
	*/

	'providers' => array(

		'Illuminate\Foundation\Providers\ArtisanServiceProvider',
		'Illuminate\Auth\AuthServiceProvider',
		'Illuminate\Cache\CacheServiceProvider',
		'Illuminate\Foundation\Providers\CommandCreatorServiceProvider',
		'Illuminate\Session\CommandsServiceProvider',
		'Illuminate\Foundation\Providers\ComposerServiceProvider',
		'Illuminate\Routing\ControllerServiceProvider',
		'Illuminate\Cookie\CookieServiceProvider',
		'Illuminate\Database\DatabaseServiceProvider',
		'Illuminate\Encryption\EncryptionServiceProvider',
		'Illuminate\Filesystem\FilesystemServiceProvider',
		'Illuminate\Hashing\HashServiceProvider',
		'Illuminate\Html\HtmlServiceProvider',
		'Illuminate\Foundation\Providers\KeyGeneratorServiceProvider',
		'Illuminate\Log\LogServiceProvider',
		'Illuminate\Mail\MailServiceProvider',
		'Illuminate\Foundation\Providers\MaintenanceServiceProvider',
		'Illuminate\Database\MigrationServiceProvider',
		'Illuminate\Foundation\Providers\OptimizeServiceProvider',
		'Illuminate\Pagination\PaginationServiceProvider',
		'Illuminate\Foundation\Providers\PublisherServiceProvider',
		'Illuminate\Queue\QueueServiceProvider',
		'Illuminate\Redis\RedisServiceProvider',
		'Illuminate\Auth\Reminders\ReminderServiceProvider',
		'Illuminate\Foundation\Providers\RouteListServiceProvider',
		'Illuminate\Database\SeedServiceProvider',
		'Illuminate\Foundation\Providers\ServerServiceProvider',
		'Illuminate\Session\SessionServiceProvider',
		'Illuminate\Foundation\Providers\TinkerServiceProvider',
		'Illuminate\Translation\TranslationServiceProvider',
		'Illuminate\Validation\ValidationServiceProvider',
		'Illuminate\View\ViewServiceProvider',
		'Illuminate\Workbench\WorkbenchServiceProvider',
        'TwigBridge\TwigServiceProvider',
        'LucaDegasperi\OAuth2Server\OAuth2ServerServiceProvider',
        'Mews\Captcha\CaptchaServiceProvider',
        'Yxd\Events\EventHandlerProvider'

	),

	/*
	|--------------------------------------------------------------------------
	| Service Provider Manifest
	|--------------------------------------------------------------------------
	|
	| The service provider manifest is used by Laravel to lazy load service
	| providers which are not needed for each request, as well to keep a
	| list of all of the services. Here, you may set its storage spot.
	|
	*/

	'manifest' => storage_path().'/meta',

	/*
	|--------------------------------------------------------------------------
	| Class Aliases
	|--------------------------------------------------------------------------
	|
	| This array of class aliases will be registered when this application
	| is started. However, feel free to register as many as you wish as
	| the aliases are "lazy" loaded so they don't hinder performance.
	|
	*/

	'aliases' => array(

		'App'             => 'Illuminate\Support\Facades\App',
		'Artisan'         => 'Illuminate\Support\Facades\Artisan',
		'Auth'            => 'Illuminate\Support\Facades\Auth',
		'Blade'           => 'Illuminate\Support\Facades\Blade',
		'Cache'           => 'Illuminate\Support\Facades\Cache',
		'ClassLoader'     => 'Illuminate\Support\ClassLoader',
		'Config'          => 'Illuminate\Support\Facades\Config',
		'Controller'      => 'Illuminate\Routing\Controllers\Controller',
		'Cookie'          => 'Illuminate\Support\Facades\Cookie',
		'Crypt'           => 'Illuminate\Support\Facades\Crypt',
		'DB'              => 'Illuminate\Support\Facades\DB',
		'Eloquent'        => 'Illuminate\Database\Eloquent\Model',
		'Event'           => 'Illuminate\Support\Facades\Event',
		'File'            => 'Illuminate\Support\Facades\File',
		'Form'            => 'Illuminate\Support\Facades\Form',
		'Hash'            => 'Illuminate\Support\Facades\Hash',
		'HTML'            => 'Illuminate\Support\Facades\HTML',
		'Input'           => 'Illuminate\Support\Facades\Input',
		'Lang'            => 'Illuminate\Support\Facades\Lang',
		'Log'             => 'Illuminate\Support\Facades\Log',
		'Mail'            => 'Illuminate\Support\Facades\Mail',
		'Paginator'       => 'Illuminate\Support\Facades\Paginator',
		'Password'        => 'Illuminate\Support\Facades\Password',
		'Queue'           => 'Illuminate\Support\Facades\Queue',
		'Redirect'        => 'Illuminate\Support\Facades\Redirect',
		'Redis'           => 'Illuminate\Support\Facades\Redis',
		'Request'         => 'Illuminate\Support\Facades\Request',
		'Response'        => 'Illuminate\Support\Facades\Response',
		'Route'           => 'Illuminate\Support\Facades\Route',
		'Schema'          => 'Illuminate\Support\Facades\Schema',
		'Seeder'          => 'Illuminate\Database\Seeder',
		'Session'         => 'Illuminate\Support\Facades\Session',
		'Str'             => 'Illuminate\Support\Str',
		'URL'             => 'Illuminate\Support\Facades\URL',
		'Validator'       => 'Illuminate\Support\Facades\Validator',
		'View'            => 'Illuminate\Support\Facades\View',
	    'AuthorizationServer' => 'LucaDegasperi\OAuth2Server\Facades\AuthorizationServerFacade',
        'ResourceServer' => 'LucaDegasperi\OAuth2Server\Facades\ResourceServerFacade',
	    'Captcha' => 'Mews\Captcha\Facades\Captcha',
	    'HttpQueue' => 'HTTPSQS\Queue',

	),

);
