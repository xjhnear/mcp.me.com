<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\App;

use Youxiduo\V4\Game\Model\AndroidGame;
use Youxiduo\V4\Game\Model\IosGame;
use Youxiduo\V4\Game\Model\GameType;
use Youxiduo\V4\Cms\Model\VideoGame;
use Youxiduo\V4\Game\Model\GameCollectType;
use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\System\Model\AppConfig;

class ConfigService extends BaseService
{
	const ERROR_CONFIG_NOT_EXISTS = 'config_not_exists';
	
	public static function getVersionConfig($platform,$appname,$channel,$version,$format=true)
	{
		$result = AppConfig::getVersionInfo($appname, $version, $channel);
		if(!$result) return self::ERROR_CONFIG_NOT_EXISTS;
		
	    if($format==true){
			$result['append'] = json_decode($result['append'],true);
			if(version_compare($result['append']['updateversion'], $version) > 0){
				$result['append']['isupdate'] = true;
			}else{
				$result['append']['isupdate'] = false;
			}
		}		
		return $platform=='ios' ? self::outFormatToIos($result) : self::outFormatToAndroid($result);
	}
	
	public static function getcheckVersion($platform,$appname,$channel,$version)
	{
	    $app = AppConfig::getVersionInfo($appname, $version, $channel);
	    if(!$app) return self::ERROR_CONFIG_NOT_EXISTS;
	
	    if($app && $app['append']){
	        $data = json_decode($app['append'],true);
	        $result = array();
	        $result['word'] = $data['updateword'];
	        $result['isforce'] = $data['isforce'];
	        $result['intro_html'] = $app['intro'];
	        $result['sys_img'] = $app['sys_img'];
	        $result['appstore'] = $app['appstoreurl'];
	        if (version_compare($data['updateversion'], $version) > 0) {
	            $result['isupdate'] = 1;
	            $result['version'] = $data['updateversion'];
	            return $result;
	        } else {
	            $result['isupdate'] = 0;
	            $result['version'] = '';
	            return $result;
	        }
	    }

	}
	
	protected static function outFormatToIos($result)
	{
		$config = array(
			'appstore' => $result['appstoreurl'],
			'open_rate' => $result['scorestate']==1 ? true : false,
			'open_beta' =>  $result['versionstate']==1 ? true : false,
		    'intro_html' => $result['intro'],
		    'sys_img' => $result['sys_img'],
		);
		
		$append = $result['append'];
		
	    if (!isset($append['dl'])) {
			$append['dl'] = 1; //开启渠道下载
		}
		$config['h5_url'] = $append['lm'];
		$config['short_url'] = $append['ss'];
		$config['force_update'] = $append['isforce'] ? true : false;
		$config['isupdate'] = $append['isupdate'];
		$config['last_version'] = $append['updateversion'];
		$config['updateword'] = $append['updateword'];
		$config['open_download'] = $append['dl']==1 ? true : false;
		$config['detail_popwin'] = $append['adv'] ? true : false;
		$config['home_popwin'] = $append['gg'] ? true : false;
		$config['launch_time'] = (int)$append['lt'];
		$config['home_bar'] = $append['bar'] ? true : false;
		return $config;
	}
	
    protected static function outFormatToAndroid($result)
	{
		$config = array(
			'apkurl' => isset($result['apkurl']) ? $result['apkurl'] : '',
			'open_rate' => $result['scorestate']==1 ? true : false,
			'open_beta' =>  $result['versionstate']==1 ? true : false,
		);
		$append = $result['append'];
        $config['h5_url'] = $append['lm'];
		$config['short_url'] = $append['ss'];
		$config['force_update'] = $append['isforce'] ? true : false;
		$config['last_version'] = $append['updateversion'];
		$config['updateword'] = $append['updateword'];
		$config['giftbag_verifycode'] = false;
		return $config;
	}
}