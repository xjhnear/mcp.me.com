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
use Youxiduo\Activity\Duang\VariationService;
use Youxiduo\Activity\Model\Variation\VariationActivity;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;

use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\Comment;
use Youxiduo\Android\Model\Giftbag;
use Youxiduo\Android\Model\Activity;


class ActivityService extends BaseService
{
	public static function getList($pageIndex,$pageSize,$gid=0,$return_arr=false)
	{
		$total = Activity::getCount($gid);
		$result = Activity::getList($pageIndex,$pageSize,$gid);
		$out = array();
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['agid'];
		}
		$games = Game::getListByIds(array_unique($gids));

		foreach($result as $row){
			$data = array();
			$gid = $row['agid'];
			$data['atid'] = $row['id'];
			$data['title'] = $row['title'];
			$data['url'] = Config::get('app.image_url') . (empty($row['pic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $row['pic']);
            $data['g_icon'] = isset($games[$gid]) ? Utility::getImageUrl($games[$gid]['ico']) : '';
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : $row['gname'];
			$data['type'] = $row['type'];
			$data['adddate'] = $row['adddate'];
			$data['ishot'] = $row['ishot'] ? true : false;
			$data['istop'] = $row['istop'] ? true : false;
			$data['starttime'] = date('Y-m-d',$row['starttime']);
			$data['endtime'] = date('Y-m-d',$row['endtime']);
			$data['starttimenew'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtimenew'] = date('Y-m-d H:i:s',$row['endtime']);
			$data['redirect_type'] = $row['redirect_type'];
			$data['linktype'] = $row['linktype'];
			$data['link'] = $row['link'];
			
			$out[] = $data;
		}

        if($return_arr) return array('result'=>$out,'totalCount'=>$total);
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function search($keyword,$pageIndex=1,$pageSize=10,$return_arr=false)
	{
		$total = Activity::searchCount($keyword);
		$out = array();
		$result = Activity::searchResult($keyword,$pageIndex,$pageSize);
		
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['agid'];
		}
		$games = Game::getListByIds(array_unique($gids));
		
	    foreach($result as $row){
			$data = array();
			$gid = $row['agid'];
			$data['atid'] = $row['id'];
			$data['title'] = $row['title'];
			$data['url'] = Config::get('app.image_url') . (empty($row['pic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $row['pic']);
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : $row['gname'];
			$data['type'] = $row['type'];
			$data['adddate'] = $row['adddate'];
			$data['ishot'] = $row['ishot'] ? true : false;
			$data['istop'] = $row['istop'] ? true : false;
			$data['starttime'] = date('Y-m-d',$row['starttime']);
			$data['endtime'] = date('Y-m-d',$row['endtime']);
			$data['starttimenew'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtimenew'] = date('Y-m-d H:i:s',$row['endtime']);
			$data['redirect_type'] = $row['redirect_type'];
			$data['linktype'] = $row['linktype'];
			$data['link'] = $row['link'];
			
			$out[] = $data;
		}

        if($return_arr) return array('result'=>$out,'totalCount'=>$total);
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
    public static function getListByGameIds($pageIndex,$pageSize,$gids)
	{
		if(!$gids) return self::trace_result(array('result'=>array(),'totalCount'=>0));
		$total = Activity::getTotalCountByGameIds($gids);
		$result = Activity::getListByGameIds($pageIndex,$pageSize,$gids);
		$out = array();
		$gids = array();
		foreach($result as $row){
			$gids[] = $row['agid'];
		}
		$games = Game::getListByIds(array_unique($gids));
		
		foreach($result as $row){
			$data = array();
			$gid = $row['agid'];
			$data['atid'] = $row['id'];
			$data['title'] = $row['title'];
			$data['url'] = Config::get('app.image_url') . (empty($row['pic'])&&isset($games[$gid]) ? $games[$gid]['ico'] : $row['pic']);
			$data['gname'] = empty($row['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : $row['gname'];
			$data['type'] = $row['type'];
			$data['adddate'] = $row['adddate'];
			$data['ishot'] = $row['ishot'] ? true : false;
			$data['istop'] = $row['istop'] ? true : false;
			$data['starttime'] = date('Y-m-d',$row['starttime']);
			$data['endtime'] = date('Y-m-d',$row['endtime']);
			$data['starttimenew'] = date('Y-m-d H:i:s',$row['starttime']);
			$data['endtimenew'] = date('Y-m-d H:i:s',$row['endtime']);
			$data['redirect_type'] = $row['redirect_type'];
			$data['linktype'] = $row['linktype'];
			$data['link'] = $row['link'];
			
			$out[] = $data;
		}
		
		return self::trace_result(array('result'=>$out,'totalCount'=>$total));
	}
	
	public static function getDetail($id,$uid=null,$return_arr=false)
	{
		$info = Activity::getInfoById($id);
		$out = array();
		if($info){
			$games = Game::getListByIds(array($info['agid']));
			$time = time();
			$gid = $info['agid'];
			$out['atid'] = $id;
			$out['title'] = $info['title'];
			$out['type'] = $info['type'];
			$out['gid'] = $info['agid'] ? : 0;
			$out['gname'] = empty($info['gname'])&&isset($games[$gid]) ? $games[$gid]['shortgname'] : $info['gname'];
			$out['url'] = Config::get('app.image_url') . (isset($games[$gid]) ? $games[$gid]['ico'] : $info['pic']);
			$out['body'] = Utility::formatContent($info['content'], $info['video_url']);
			$out['starttime'] = date('Y-m-d H:i:s',$info['starttime']);
			$out['endtime'] = date('Y-m-d H:i:s',$info['endtime']);			
			$out['is_finished'] = ($info['starttime']>$time || $time>$info['endtime']) ? 1 : 0;
			$out['redirect_type'] = $info['redirect_type'];
			$out['linktype'] = $info['linktype'];
			$out['link'] = $info['link'];
            $out['v3data'] = VariationService::getV3NeedInfo($id,$uid);
		}
        if($return_arr) return array('result'=>$out);
		return self::trace_result(array('result'=>$out));
	}
}