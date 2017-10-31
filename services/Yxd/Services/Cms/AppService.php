<?php
namespace Yxd\Services\Cms;

use Yxd\Modules\Core\CacheService;
use Yxd\Services\Service;

class AppService extends Service
{	
	/**
	 * 获取配置
	 */
	public static function getConfig($appname,$version,$format=false)
	{
		$cachekey = 'appconfig::' . $appname . '::' . $version . '::data';
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$config = CacheService::get($cachekey);
		}else{
			$config =  self::dbCmsSlave()->table('version')
			    ->where('channel','=','')
			    ->where('appname','=',$appname)
			    ->where('version','=',$version)
			    ->first();
			CLOSE_CACHE===false && CacheService::forever($cachekey,$config);
		}
		if($format==true){
			$config['append'] = json_decode($config['append'],true);
		}
		return $config;
	}
	
	public static function getSimpleConfig($gid,$type,$version)
	{
		$config = self::dbClubSlave()->table('game_control')->where('game_id','=',$gid)->where('zone_type','=',$type)->where('version','=',$version)->first();
		if(!$config) return array();
		$info = unserialize($config['control_data']);
		return $info;
	}
	
	/**
	 * 检查版本
	 */
	public static function checkVersion($appname,$version)
	{
		$app =  self::dbCmsSlave()->table('version')->where('appname','=',$appname)->where('version','=',$version)->first();
		if($app && $app['append']){
			$data = json_decode($app['append'],true);
		    $result = array();
			$result['word'] = $data['updateword'];
			$result['isforce'] = $data['isforce'];
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
		return null;
	}    
}