<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */

namespace Youxiduo\Android;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\System\Model\AppConfig;
use Yxd\Modules\System\SettingService;
use Youxiduo\Android\Model\UserDevice;

class AppService extends BaseService
{
	public static function getVersionInfo($appname,$version,$channel)
	{
		$config = AppConfig::getVersionInfo($appname, $version, $channel);
		if(!$config) return self::trace_error('E1','配置不存在');
		
		$append = json_decode($config['append'],true);
		unset($config['append']);
		$config = array_merge($config,$append);
		$config['giftbag_verifycode'] = false;
		return self::trace_result(array('result'=>$config));
		
	}
	
	public static function checkNewVersion($appname,$version,$channel)
	{
		$out = array(
		    'word'=>'',
		    'isforce'=>1,
		    'apkurl'=>'',
		    'isupdate'=>0
		);
		$config = AppConfig::getVersionInfo($appname, $version, $channel);
		if(!$config) return self::trace_result(array('result'=>$out));
		$append = json_decode($config['append'],true);
		unset($config['append']);
		$out['word'] = $append['updateword'];
		$out['isforce'] = $append['isforce'];
		$out['apkurl'] = $append['apkurl'] ? : '';
		$out['isupdate'] = version_compare($append['updateversion'],$version)>0 ? 1 : 0;
		return self::trace_result(array('result'=>$out));
	}
	
	public static function getFilterWords()
	{
		$config = SettingService::getConfig('android_setting');
	    if($config){
			return self::trace_result(array('result'=>$config['data']['filter_words']));
		}
		return self::trace_result(array('result'=>''));
	}
	
	public static function recordUserDevice($uid,$deviceId,$channelId,$idcode)
	{
		//$exists = UserDevice::db()->where('uid','=',$uid)->where('device_id','=',$deviceId)->where('channel_id','=',$channelId)->where('idcode','=',$idcode)->first();
		$exists = UserDevice::db()->where('uid','=',$uid)->first();
		if($exists){
			//$is_update = strtotime($exists['update_time']) > (time()-3600*3) ? true : false; 
			//$is_update && UserDevice::db()->where('id','=',$exists['id'])->update(array('update_time'=>date('Y-m-d H:i:s')));
			if($exists['device_id'] != $deviceId || $exists['channel_id'] != $channelId || $exists['idcode'] != $idcode){
				$data = array('device_id'=>$deviceId,'channel_id'=>$channelId,'idcode'=>$idcode,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s'));
				UserDevice::db()->where('id','=',$exists['id'])->update($data);
			}
			return self::trace_result(array('result'=>true));
		}
		$data = array(
		    'uid'=>$uid,'device_id'=>$deviceId,'channel_id'=>$channelId,'idcode'=>$idcode,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s')
		);
		UserDevice::db()->insertGetId($data);
		return self::trace_result(array('result'=>true));
	}
}