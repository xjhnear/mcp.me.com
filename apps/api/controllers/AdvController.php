<?php
/**
 * 广告接口
 * 
 * 这里是广告接口描述
 * 
 * @version 3.0.0
 * @package youxiduo
 */
use Yxd\Services\Cms\GameService;
use Yxd\Modules\Core\CacheService;
use Yxd\Services\Cms\AdvService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\log;
use Yxd\Utility\OutLog;
/**
 * 广告接口
 * 
 * 这里是广告接口描述
 * 
 * @version 3.0.0
 * @package youxiduo
 */
class AdvController extends BaseController 
{
	/**
	 * 启动页广告
	 * 
	 * APP启动页广告
	 * 
	 * @param string $appname
	 * @param string version
	 * @param int isiphone5
	 * 
	 */
	public function launch()
	{
		$appname = Input::get('appname','');
		$version = $this->getComVersion();
		$isiphone5 = Input::get('isiphone5',0);
		$out = AdvService::getLaunch($appname, $version, $isiphone5);
		return $this->success(array('result'=>$out));
	}
	
	/**
	 * 弹窗广告
	 */
	public function openwin()
	{
		$appname = Input::get('appname','');
		$version = $this->getComVersion();
		$entrance = Input::get('entrance');		
		$out = AdvService::getOpenWin($appname, $version, $entrance,Input::get('version','3.0.0'));
		return $this->success(array('result'=>$out));
	}
	
	/**
	 * 广告统计
	 */
	public function advstat()
	{
		$appname = Input::get('appname','');
		$version = $this->getComVersion();
		$advid = Input::get('advid','');
		$mac = Input::get('mac');
		$idfa = Input::get('idfa');
		$osversion = Input::get('osversion','');
		$code = Input::get('code','');
		$linkid = Input::get('linkid',0);
		$location = Input::get('location','');
		$openudid = Input::get('openudid','');
		$source = Input::get('source','');
		$type = Input::get('type',0);
		$os = Input::get('os');
		$version = Input::get('version','3.0.0');
        $params = Input::all();
        OutLog::outLog($params);
		if($version=='3.0.0'){//只在3.0版本统计
		    GameService::download($linkid,0);
		}
		if($location){
			AdvService::stat($appname, $version, $location, $osversion, $advid, $code, $idfa, $openudid, $type, $linkid);
			return $this->success(array('result'=>null));
		}
		return $this->success(array('result'=>null));
	}
	
	/**
	 * 广告激活统计
	 */
	public function activestat()
	{
		$code = Input::get('mac','');
		$idfa = Input::get('idfa','');
		if (Input::get('adid')) {
		    $advid = Input::get('adid','');
		} else {
		    $advid = Input::get('advid','');
		}
		
		$res = AdvService::active($code, $idfa, $advid);
        $params = array(
            'mac'   => $code,
            'idfa'  => $idfa,
            'advid' => $advid,
        );

        OutLog::outLog($params);

		if($res === null){
			echo json_encode(array('errorCode'=>'1','errorMessage'=>''));
		    exit;
		}else{
		    echo json_encode(array('errorCode'=>'0'));
		    exit;
		}
		
	}

    public function testLog () {
        $str = array(
            'ceshi','测试'
        );
        OutLog::outLog($str);
    }
}