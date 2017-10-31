<?php
namespace modules\statistics\models;
use Yxd\Models\BaseModel;
use Illuminate\Support\Facades\DB;
class ForumModel extends BaseModel{
	/**
	 * 获取一定时间内发帖总数
	 * @param unknown $start_time
	 * @param unknown $end_time
	 */
	public static function getForumTopicCount($start_time,$end_time){
		if(!$start_time || !$end_time) return 0;
		return self::dbClubMaster()->table('forum_topic')
									->where('dateline','>=',$start_time)
									->where('dateline','<',$end_time)
									->count();
	}
	
	/**
	 * 获取一定时间内发帖排行总数
	 * @param unknown $start_time
	 * @param unknown $end_time
	 * @return multitype:
	 */
	public static function getForumTopicRankListCount($start_time,$end_time){
		if(!$start_time || !$end_time) return 0;
		$result = self::dbClubMaster()->table('forum_topic')
		->where('dateline','>=',$start_time)
		->where('dateline','<',$end_time)
		->groupBy('author_uid')
		->get();
		return count($result);
	}
	
	/**
	 * 获取一定时间内发帖排行
	 * @param unknown $start_time
	 * @param unknown $end_time
	 * @return multitype:
	 */
	public static function getForumTopicRankList($start_time,$end_time,$page=1,$pagesize=10){
		if(!$start_time || !$end_time) return array();
		return self::dbClubMaster()->table('forum_topic')
									->where('dateline','>=',$start_time)
									->where('dateline','<',$end_time)
									->select(DB::raw('author_uid,count(*) as count'))
									->groupBy('author_uid')
									->orderBy('count','DESC')
									->forPage($page,$pagesize)->get();
	}
	
	/**
	 * 获取一定时间内回复总数
	 * @param unknown $start_time
	 * @param unknown $end_time
	 */
	public static function getForumCommentCount($start_time,$end_time){
		if(!$start_time || !$end_time) return 0;
		return self::dbClubMaster()->table('comment')
		->where('target_table','yxd_forum_topic')
		->where('addtime','>=',$start_time)
		->where('addtime','<',$end_time)
		->count();
	}
	
	/**
	 * 获取一定时间内回复排行总数
	 * @param unknown $start_time
	 * @param unknown $end_time
	 * @return multitype:
	 */
	public static function getForumCommentRankListCount($start_time,$end_time){
		if(!$start_time || !$end_time) return 0;
		$result = self::dbClubMaster()->table('comment')
		->where('target_table','yxd_forum_topic')
		->where('addtime','>=',$start_time)
		->where('addtime','<',$end_time)
		->groupBy('uid')
		->get();
		return count($result);
	}
	
	/**
	 * 获取一定时间内回复排行
	 * @param unknown $start_time
	 * @param unknown $end_time
	 * @return multitype:
	 */
	public static function getForumCommentRankList($start_time,$end_time,$page=1,$pagesize=10){
		if(!$start_time || !$end_time) return array();
		return self::dbClubMaster()->table('comment')
		->where('target_table','yxd_forum_topic')
		->where('addtime','>=',$start_time)
		->where('addtime','<',$end_time)
		->select(DB::raw('uid as author_uid,count(*) as count'))
		->groupBy('author_uid')
		->orderBy('count','DESC')
		->forPage($page,$pagesize)->get();
	}
}