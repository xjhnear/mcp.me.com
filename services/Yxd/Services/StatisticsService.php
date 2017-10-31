<?php
namespace Yxd\Services;

use Yxd\Modules\Core\BaseService;
use Yxd\Services\UserService;
use modules\statistics\models\ForumModel;
use modules\user\models\UserModel;

class StatisticsService extends BaseService{
	
	/**
	 * 获取发帖总数
	 * @param int $start_time 开始时间
	 * @param int $end_time 结束时间
	 */
	public static function getPostCount($start_time,$end_time){
		return ForumModel::getForumTopicCount($start_time, $end_time);
	}
	
	/**
	 * 获取发布帖子排行总数
	 * @param int $start_time 开始时间
	 * @param int $end_time 结束时间
	 */
	public static function getPostRankListCount($start_time,$end_time){
		return ForumModel::getForumTopicRankListCount($start_time, $end_time);
	}
	
	/**
	 * 获取发布帖子排行（按帖子发布数）
	 * @param int $start_time 开始时间
	 * @param int $end_time 结束时间
	 */
	public static function getPostRankList($start_time,$end_time,$page,$pagesize){
		return self::joinUserInfoToRankList(ForumModel::getForumTopicRankList($start_time, $end_time, $page, $pagesize));
	}
	
	/**
	 * 获取回复总数
	 * @param int $start_time 开始时间
	 * @param int $end_time 结束时间
	 */
	public static function getReplyCount($start_time,$end_time){
		return ForumModel::getForumCommentCount($start_time, $end_time);
	}
	
	/**
	 * 获取回复帖子排行总数
	 * @param int $start_time 开始时间
	 * @param int $end_time 结束时间
	 */
	public static function getReplyRankListCount($start_time,$end_time){
		return ForumModel::getForumCommentRankListCount($start_time, $end_time);
	}
	
	/**
	 * 获取回复帖子排行（按帖子回复数）
	 * @param int $start_time 开始时间
	 * @param int $end_time 结束时间
	 */
	public static function getReplyRankList($start_time,$end_time,$page,$pagesize){
		return self::joinUserInfoToRankList(ForumModel::getForumCommentRankList($start_time, $end_time, $page, $pagesize));
	}
	
	private static function joinUserInfoToRankList($rank_list){
		if(!$rank_list) return array();
		$uids = array();
		foreach ($rank_list as $rl){
			$rank_list_bac[$rl['author_uid']] = $rl['count'];
			$uids[] = $rl['author_uid'];
		}
		$usersinfo = UserModel::getUsersInfo($uids);
		$usersgroups = UserModel::getUsersGroups($uids);
		if(!$usersinfo) return $rank_list;
		$userinfo_bac = array();
		foreach ($usersinfo as $user){
			$userinfo_bac[$user['uid']] = $user;
		}
		foreach ($rank_list as $k=>&$item){
			if(array_key_exists($item['author_uid'], $userinfo_bac)){
				array_key_exists($item['author_uid'], $usersgroups) ? $item['group_id'] = $usersgroups[$item['author_uid']] : $item['group_id'] = 0;
				$item['uid'] = $item['author_uid'];
				$item['nickname'] = $userinfo_bac[$item['author_uid']]['nickname'];
				$item['dateline'] = $userinfo_bac[$item['author_uid']]['dateline'];
				$item['avatar'] = UserService::joinImgUrl($userinfo_bac[$item['author_uid']]['avatar']);
			}else{
				unset($rank_list[$k]);
			}
		}
		return $rank_list;
	}
}