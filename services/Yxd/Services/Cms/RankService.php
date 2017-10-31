<?php
namespace Yxd\Services\Cms;

use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;

class RankService extends Service
{	
	public static function getGameList($type,$gtid=0,$pricetype=null,$tag=null,$order='desc',$page=1,$pagesize=10)
	{
		$tb = self::dbCmsSlave()->table('games')->where('isdel','=',0);
		if($gtid>0){//筛选分类
			$tb = $tb->where('type','=',$gtid);
		}
		
		if($pricetype>0){//筛选收费类型
			$tb = $tb->where('pricetype','=',$pricetype);
		}
		
		if($tag){//筛选标签
			$game_ids = self::dbCmsSlave()->table('games_tag')->where('tag','=',$tag)->lists('gid');
			if($game_ids){
				$tb = $tb->whereIn('id',$game_ids);
			}
		}
		$total = $tb->count();
		if($type==0){//活跃榜
			$tb = $tb->orderBy('commenttimes',$order);
		}elseif($type==1){//周下载榜
			$tb = $tb->orderBy('weekdown',$order);
		}elseif($type==2){//最新更新榜
			$tb = $tb->orderBy('id',$order);
		}
		
		$tb = $tb->forPage($page,$pagesize);
		$games = $tb->get();
		return array('games'=>$games,'total'=>$total);
	}
	
}