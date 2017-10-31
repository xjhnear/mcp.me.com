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
namespace Youxiduo\V4\Advs;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\V4\User\Model\Relation;

use Youxiduo\Android\Model\Adv;
use Youxiduo\Android\Model\AdvPos;
use Youxiduo\Android\Model\AppAdv;
use Youxiduo\Android\Model\AppAdvStat;

class AdvSpaceService extends BaseService
{
    /**
	 * 获取启动页广告
	 */
	public static function getLaunch($appname,$channel,$version,$advSpaceId)
	{
		$adv = AppAdv::getInfo($appname,$version,5);
		$out = array();
		if($adv){
			$out['img'] = Config::get('app.image_url') . $adv['bigpic'];			
		}
		
		return $out;
	}
	
	/**
	 * 轮播广告
	 * @param string $appname
	 * @param string $channel
	 * @param string $version
	 * @param string $advSpaceId
	 * 
	 */
	public static function getSlide($appname,$channel,$version,$advSpaceId)
	{
		$adv_space = array();
		$adv_space = Adv::db()
		    ->where('version','=',$version)
	        ->where('appname','=',$appname)
	        //->where('channel','=',$channel)
	        ->forPage(1,5)
	        ->orderBy('sort','desc')
	        ->get();
	    $out = array();
	    foreach($adv_space as $row){
			$ad = array();
			$ad['title'] = $row['title'];
			$ad['type'] = $row['type'];
			$ad['linkid'] = $row['link_id'];
			$ad['img'] = Utility::getImageUrl($row['litpic']);	
            $out[] = $ad;			
		}
		
		$advs = AppAdv::db()
		    ->where('type','=',1)
		    ->where('appname','=',$appname)
		    ->where('version','=',$version)
		    ->orderBy('location','asc')
		    ->get();
	    $position_advs = array();
		foreach($advs as $key=>$row){
            $adv = self::filterCommonParams($row);
            $adv['title'] = $row['advname'];
            $adv['img'] = Utility::getImageUrl($row['litpic']);
        	$position_advs[$row['location']-1] = $adv;
        }
        
        foreach($out as $key=>$row){
        	if(isset($position_advs[$key])){        		
        		$out[$key] = $position_advs[$key];
        		$out[$key]['type'] = $row['type'];
        		$out[$key]['linkid'] = $row['linkid'];
        	}
        }
        return $out;
	}
	
	/**
	 * Banner广告
	 * @param string $appname
	 * @param string $channel
	 * @param string $version
	 * @param string $advSpaceId
	 */
	public static function getBanner($appname,$channel,$version,$advSpaceId)
	{
		$map_table = array('home_banner_1'=>1,'home_banner_2'=>2,'shop_banner_1'=>6);
		$postype = $map_table[$advSpaceId];
		$advpos = AdvPos::db()->where('appname','=',$appname)
			->where('version','=',$version)
			->where('postype','=',$postype)
			->orderBy('id','desc')
			->first();
		$out = array();
		
		$adv = AppAdv::db()->where('location','=',25)
	        ->where('appname','=',$appname)
	        ->where('version','=',$version)
	        ->first();
	    $adv = null;
	    if(!$adv && $advpos) {
			$out['title'] = $advpos['title'];
			$out['type'] = $advpos['type'];
			$out['linkid'] = $advpos['link_id'];
			$out['img'] = Utility::getImageUrl($advpos['litpic']);
			$out['words'] = $advpos['words'];
		}else{		
			$out = self::filterCommonParams($adv);
			$out && $out['title'] = $advpos['title'];
			$out && $out['img'] = Utility::getImageUrl($adv['litpic']);
			$out && $out['words'] = $advpos['words'];
		}	    
		return $out ? array($out) : array(); 
	}
	
    /**
	 * 推荐位广告
	 * @param string $appname
	 * @param string $channel
	 * @param string $version
	 * @param string $advSpaceId
	 */
	public static function getRecommendSpace($appname,$channel,$version,$advSpaceId)
	{
		$map_table = array('home_recommend'=>3,'shop_recommend'=>100,'ios_v4_task_center_ad'=>'200');
		$postype = $map_table[$advSpaceId];
		$advpos = AdvPos::db()->where('appname','=',$appname)
			->where('version','=',$version)
			->where('postype','=',$postype)
			->orderBy('tab','asc')
			->forPage(1,4)
			->get();
		$out = array();
	    if($advpos) {
	    	foreach($advpos as $row){
				$tmp['title'] = $row['title'];
				$tmp['type'] = $row['type'];
				$tmp['linkid'] = $row['link_id'];
				$tmp['img'] = Utility::getImageUrl($row['litpic']);
				$tmp['words'] = $row['words'];
				$out[] = $tmp;
	    	}
		}
		return $out;
	}	
	
	/**
	 * 推荐游戏广告
	 * @param string $appname
	 * @param string $channel
	 * @param string $version
	 * @param string $advSpaceId 标识[home_network|home_single|search_hot|guess_like|type_hot]
	 */
	public static function getRecommendGames($appname,$channel,$version,$advSpaceId)
	{
		$map_table = array('home_network'=>4,'home_single'=>5,'search_hot'=>6,);
		$postype = $map_table[$advSpaceId];
		$advpos = AdvPos::db()->where('appname','=',$appname)
			->where('version','=',$version)
			->where('postype','=',$postype)
			->orderBy('tab','asc')
			->forPage(1,5)
			->get();
		$out = array();
	    if($advpos) {
	    	foreach($advpos as $row){
				$tmp['title'] = $row['title'];
				$tmp['type'] = $row['type'];
				$tmp['linkid'] = $row['link_id'];
				$tmp['img'] = Utility::getImageUrl($row['litpic']);
				$tmp['words'] = $row['words'];
				$tmp['score'] = '4.0';
				$out[] = $tmp;
	    	}
		}
		return $out;
	}
	
    /**
	 * 过滤通用参数
	 */
	protected static function filterCommonParams($adv)
	{
		if(!$adv) return array();
		$out = array();
		$out['advtype'] = $adv ? 1 : 0;
		$out['staturl'] = $adv['url'];
		$out['advid'] = $adv['aid'];
		$out['location'] = $adv['location'];
		$out['tosafari'] = $adv['tosafari'];
		$out['sendmac'] = $adv['sendmac'];
		$out['sendidfa'] = $adv['sendidfa'];
		$out['sendudid'] = $adv['sendudid'];
		$out['sendos'] = $adv['sendos'];
		$out['sendplat'] = $adv['sendplat'];
		$out['sendactive'] = $adv['sendactive'];
		$out['downurl'] = $adv['downurl'];
		return $out;
	}
}