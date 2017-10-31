<?php
use Yxd\Modules\Core\CacheService;
use Yxd\Services\Cms\AppService;
use Illuminate\Support\Facades\Input;

class AppController extends BaseController 
{
	/**
	 * 配置
	 * 
	 */
	public function getConfig()
	{
		$appname = Input::get('appname','');
		$version = $this->getComVersion();	
		$cachekey = 'appconfig::' . $appname . '::' . $version;
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$out = CacheService::get($cachekey);
		}else{
		
			$result = AppService::getConfig($appname,$version);
			$config = array();
			$rateopen =	"";
			$betaopen =	"";
			if($result['scorestate'] == 1){
				$rateopen	=	$result['rateopen'];
			}
			if ($result['versionstate'] ==1)
			{
				$betaopen	=	$result['betaopen'];
			}
			$appstoreurl	=	$result['appstoreurl'];
	
			$config = array(
				'lyh' => $appstoreurl,
				'fs'  => $rateopen,
				'lzx' => $betaopen,
			);
			
			$append = json_decode($result['append'],true);
			
		    if (!isset($append['dl'])) {
				$append['dl'] = 1; //开启渠道下载
			}
			unset($append['updateversion']);
			unset($append['updateword']);
			unset($append['apkurl']);
			unset($append['isforce']);
			$out = array_merge($config,$append);
			CLOSE_CACHE===false && CacheService::forever($cachekey,$out);
		}
		return $this->success(array('result'=>$out));
	}
	
	public function simpleConfig()
	{
		$game_id = (int)Input::get('gid');
		$type = (int)Input::get('type',1);
		$version = Input::get('version');
		$result = AppService::getSimpleConfig($game_id, $type, $version);
		if($result){
			return $this->success(array('result'=>$result));
		}
		return $this->fail(11211,'配置不存在');
	}
	
	/**
	 * 检查版本
	 */
	public function checkVersion()
	{
		$appname = Input::get('appname','');
		$version = $this->getComVersion();
		$cachekey = 'appconfig::' . $appname . '::' . $version . '::update';
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$result = CacheService::get($cachekey);
		}else{
		    $result = AppService::checkVersion($appname, $version);
		    CLOSE_CACHE===false && CacheService::forever($cachekey,$result);
		}
		if($result==null) return $this->fail(500,'没有版本信息');
		return $this->success(array('result'=>$result));
	}	
}