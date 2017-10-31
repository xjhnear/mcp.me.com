<?php
use Illuminate\Support\Facades\Input;

use Yxd\Modules\Core\CacheService;
use Yxd\Modules\System\SettingService;
use Yxd\Services\Cms\AdvService;
use Yxd\Services\Cms\GameService;

/**
 * 首页
 */
class HomeController extends BaseController
{
	/**
	 * 首页
	 */
	public function index()
	{
		$uid = Input::get('uid',0);
		$appname = Input::get('appname','');
		$version = $this->getComVersion();		
		$v = '2';
		$out = array();
		$config = SettingService::getConfig('home_picture_setting');
		$picture = $config ? $config['data'] : array();
		//新游前瞻
		$out['xyqz_coverurl'] = isset($picture['yugao']) ? 'http://img.youxiduo.com' . $picture['yugao'] : 'http://img.youxiduo.com/userdirs/home/xinyou@2x.png' . '?v=' . $v;
		//资讯中心
		$out['zxzx_coverurl'] = isset($picture['zixun']) ? 'http://img.youxiduo.com' . $picture['zixun'] : 'http://img.youxiduo.com/userdirs/home/zixun@2x.png' . '?v=' . $v;
		//美女视频
		$out['mnsp_coverurl'] = isset($picture['shipin']) ? 'http://img.youxiduo.com' . $picture['shipin'] : 'http://img.youxiduo.com/userdirs/home/meinvshipin@2x.png' . '?v=' . $v;
		//特色专题
		$out['tszt_coverurl'] = isset($picture['zhuanti']) ? 'http://img.youxiduo.com' . $picture['zhuanti'] : 'http://img.youxiduo.com/userdirs/home/tesezhuanti@2x.png' . '?v=' . $v;
		//经典必玩
		$out['jdbw_coverurl'] = isset($picture['biwan']) ? 'http://img.youxiduo.com' . $picture['biwan'] : 'http://img.youxiduo.com/userdirs/home/jindianbiwan@2x.png' . '?v=' . $v;
		//新人报道
		$out['xrbd_coverurl'] = isset($picture['plaza_3']) ? 'http://img.youxiduo.com' . $picture['plaza_3'] : 'http://img.youxiduo.com/userdirs/home/xinrenbaodao@2x.png' . '?v=' . $v;
		//游戏问答
		$out['yxwd_coverurl'] = isset($picture['plaza_1']) ? 'http://img.youxiduo.com' . $picture['plaza_1'] : 'http://img.youxiduo.com/userdirs/home/youxiwenda@2x.png' . '?v=' . $v;
		//游戏秀
		$out['yxx_coverurl'] = isset($picture['plaza_2']) ? 'http://img.youxiduo.com' . $picture['plaza_2'] : 'http://img.youxiduo.com/userdirs/home/youxixiu@2x.png' . '?v=' . $v;
		
		//幻灯
		$out['shuffles'] = AdvService::getHomeSlide($appname,$version,$uid);
		
		//今日推荐
		$out['hotgames'] = AdvService::getHomeHotGame($appname,$version,$uid);
		
		//最新更新
		$out['newgames'] = GameService::getHomeNew();
		
		//首页广告条
		$out['adverts'] = AdvService::getHomeBar($appname,Input::get('version','3.0.0'),$version);	
				
		return $this->success(array('result'=>$out));
	}	
}