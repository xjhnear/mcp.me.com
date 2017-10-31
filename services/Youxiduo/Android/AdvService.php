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
use Youxiduo\Android\Model\Adv;
use Youxiduo\Android\Model\AppAdv;
use Youxiduo\Android\Model\AppAdvStat;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Youxiduo\Android\Model\AdvImage;
use Youxiduo\Android\Model\AdvAppLink;
use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\GameType;

class AdvService extends BaseService
{
	public static function getSlide($appname,$version,$channel,$advSpaceId)
	{
		//return self::getAndroidSlide($appname,$version,$channel,$advSpaceId);
		if($version=='2.9.3'){
			$version = '2.9.3';
		}elseif($version != '2.9.2' && $version != '2.9.2beta' && $version != '2.9.2.1' && $version != '2.9.2.2'){
			$version = '2.9';
		}else{
			$version = '2.9.2';
		}
	    $adv_space = array();
		$adv_space = Adv::db()
		    ->where('version','=',$version)
	        ->where('appname','=',$appname)
	        //->where('channel','=',$channel)
	        ->where('slide_pos',$advSpaceId)
	        ->forPage(1,5)
	        ->orderBy('sort','desc')
	        ->get();
	    $out = array();
	    foreach($adv_space as $row){
			$ad = array();
			$ad['title'] = $row['title'];
			$ad['type'] = $row['type'];
			$ad['linkid'] = $row['link_id'];
			$ad['linktype'] = $row['type'];
			$ad['link'] = $row['link_id'];
			$ad['img'] = Utility::getImageUrl($row['litpic']);	
            $out[] = $ad;			
		}
		
		return self::trace_result(array('result'=>$out));
	}
	
	public static function getAndroidSlide($appname,$version,$channel,$advSpaceId)
	{
		$place_type_badge = '';
		switch($advSpaceId){
			case 1:
				$place_type_badge = 'slide_place_11';
				break;
			case 2:
				$place_type_badge = 'slide_place_21';
				break;
			case 3:
				$place_type_badge = 'slide_place_31';
				break;
			default:
				break;
		}
		if(!empty($place_type_badge)){
			$adv_ids = AdvAppLink::getAdvIds($place_type_badge,'yxdandroid','',$version);
			if($adv_ids && is_array($adv_ids) && count($adv_ids)>0){
				$adv_ids = array_unique($adv_ids);
				$search = array('in_id'=>$adv_ids,'effective'=>true,'is_show'=>1);
		        $adv_spaces = AdvImage::searchList($search,1,10,array('place_id'=>'asc'));
		        $out = array();
			    foreach($adv_spaces as $row){
					$ad = array();
					$ad['title'] = $row['title'];
					$ad['type'] = $row['linktype'];
					$ad['linkid'] = $row['link'];
					$ad['linktype'] = $row['linktype'];
					$ad['link'] = $row['link'];
					$ad['img'] = Utility::getImageUrl($row['img']);	
		            $out[] = $ad;			
				}
				
				return self::trace_result(array('result'=>$out));
			}
		}
		return self::trace_result(array('result'=>array()));
	}
	
	/**
	 * 首页轮播广告
	 */
	public static function getMainAdv($appname,$version)
	{
        $limit = 5;
        $out = array();
        $result = array();
        $type = Config::get('yxd.adv.INDEX_LUNBO_ADV');
        $for_list = function($list){
            $out = array();
            foreach ($list as $k=>$v) {
                $out[$k]['title'] = $v['title'];
                $out[$k]['img'] = Config::get('app.img_url').$v['litpic'];
                $out[$k]['advtype'] = 1;
                $out[$k]['downurl'] = $v['downurl'];
                $out[$k]['staturl'] = $v['url'];
                $out[$k]['location'] = $v['location'];
                $out[$k]['advid'] = $v['aid'];
                $out[$k]['sendmac'] = $v['sendmac'];
                $out[$k]['sendidfa'] = $v['sendidfa'];
                $out[$k]['sendudid'] = $v['sendudid'];
                $out[$k]['sendos'] = $v['sendos'];
                $out[$k]['sendplat'] = $v['sendplat'];
                $out[$k]['sendactive'] = $v['sendactive'];
                $out[$k]['tosafari'] = $v['tosafari'];
            }
            return $out;
        };
        $list = AppAdv::getList($appname,$version,$type,$limit);
        $advCount = count($list);
        if ($advCount >= $limit) {
            $out = $for_list($list);
            $result = $out;
        }else{
            $out = $for_list($list);
            $num = $limit-$advCount;
            $res = Adv::getList($appname,$version,$num);
            if ($res){
                foreach ($res as $v){
                    $out[$advCount]['aid'] = $v['id'];
                    $out[$advCount]['title'] = $v['title'];
                    $out[$advCount]['img'] = Config::get('app.img_url').$v['litpic'];
                    $out[$advCount]['type'] = $v['type'];
                    $out[$advCount]['linkid'] = $v['link_id'];
                    $advCount++;
                }
            }
            $fill_count = $limit;
            if(count($res) < $num){
                $fill_count = count($list) + count($res);
            }
            $result = $fill_count ? array_fill(0, $fill_count, '') : array();
            foreach ($out as $k => $v) {
                if (isset($v['location'])) {
                    $result[$v['location']-1] = $out[$k];
                } else {
                    $key = array_search('', $result);
                    $result[$key] = $out[$k];
                }
            }
        }
        return self::trace_result(array('result'=>$result));
	}
	
	/**
	 * 获取启动页广告
	 */
	public static function getLaunch($appname,$version)
	{
		$adv = AppAdv::getInfo($appname,'2.9',5);
		$out = array();
		if($adv){
			$out['img'] = Config::get('app.image_url') . $adv['bigpic'];
			//$out['downurl'] = $adv['downurl'];
			
		}
		
		return self::trace_result(array('result'=>$out));
	}
	
	public static function getBanner($appname,$channel,$version,$advSpaceId)
	{
		$search = array('place_id'=>$advSpaceId,'effective'=>true);
		$adv = AdvImage::findOne($search);
		return $adv;
	}

    /**
     * 3.0新增接口
     * @param $appname
     * @param $channel
     * @param $version
     * @param $advSpaceId
     * @return array
     */
    public static function getBannerOut($appname,$channel,$version,$advSpaceId)
    {
        $out = array();
        $adv_ids = AdvAppLink::getAdvIds($advSpaceId,$appname,$channel,$version);
        //print_r($advSpaceId.$appname.$channel.$version);exit;
        if($adv_ids && is_array($adv_ids) && count($adv_ids)>0){
            $adv_ids = array_unique($adv_ids);
            $search = array('in_id'=>$adv_ids,'effective'=>true,'is_show'=>1);
            $adv_spaces = AdvImage::searchList($search,1,10,array('place_id'=>'asc'));

            foreach($adv_spaces as $row){
                $ad = array();
                $ad['title'] = $row['title'];
                $ad['linktype'] = $row['linktype'];
                $ad['link'] = $row['link'];
                $ad['img'] = Utility::getImageUrl($row['img']);
                $out[] = $ad;
            }
        }
        return self::trace_result(array('result'=>$out));
    }

    /**
     * 3.0新增接口
     * @param int $limit
     * @return array
     */
    public static function getRecommendGameOut($limit=4)
    {
        $out = array();
        $games = Game::db()->where('isdel','=',0)->where('flag','=',1)
            ->orderBy('isapptop','desc')
            ->orderBy('recommendsort','desc')
            ->orderBy('addtime','desc')
            ->forPage(1,$limit)
            ->get();

        $types = GameType::db()->orderBy('id','asc')->lists('typename','id');

        foreach($games as $row){
            $data = array();
            $data['gid'] = $row['id'];
            $data['title'] = $row['shortgname'];
            $data['img'] = Config::get('app.image_url') . $row['ico'];
            //$data['icon'] = Config::get('app.image_url') . $row['ico'];
            $data['comment'] = $row['shortcomt'];
            $data['free'] = $row['pricetype']==1 ? true : false;
            $data['limitfree'] = $row['pricetype']==2 ? true : false;
            $data['score'] = $row['score'];
            $data['first'] = $row['isstarting'];
            $data['hot'] = $row['ishot'];
            $data['linktype'] = $row['linktype'] ? : '';
            $data['link'] = $row['link'] ? : '';
            $data['typename'] = isset($types[$row['type']]) ? $types[$row['type']] : '';
            $data['size'] = $row['size'];

            $out[] = $data;
        }
        return self::trace_result(array('result'=>$out));
    }
	
	public static function getRecommendPlaceList($appname,$channel,$version,$place_type,$size=5)
	{
		$search = array('place_type'=>$place_type,'effective'=>true,'is_show'=>1);
		return AdvImage::searchList($search,1,$size,array('place_id'=>'asc'));
	}

    /**
     * 广告位数据统计
     */
    public static function advRecord($appname,$version,$location,$iosversion,$advid,$type,$linkid,$idcode)
    {
        if($location){
            if ($type != 13) {
                if ($location >=17 && $location<=20 ) return self::trace_error('E10');
            }
            $datetime = strtotime(date("Y-m-d",time()));
            if($advid){
                $adv = AppAdv::getDetailByAid($appname,$version,$advid,$location);
                if(!$adv) return self::trace_error('E10');
            }
                
            $id = AppAdvStat::appAdvStatCount($appname,$version,$datetime,$location,$advid,$type,$linkid);
            if($id){
                AppAdvStat::setIncById($id);
            }else{
                $data = array();
                $data['appname'] = $appname;
                $data['version'] = $version;
                $data['location'] = $location;
                $data['iosversion'] = $iosversion;
                $data['aid'] = $advid;
                $data['code'] = '';
                $data['idfa'] = $idcode;
                $data['openudid'] = '';
                $data['type'] = $type;
                $data['link_id'] = $linkid;
                $data['number'] = 1;
                $data['addtime'] = $datetime;
                $id = AppAdvStat::save($data);
            }
            
            return self::trace_result();
        }
        return self::trace_error('E50');
    }
}

