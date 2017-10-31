<?php
namespace Yxd\Services;

use Yxd\Services\RelationService;

use Yxd\Services\UserService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Yxd\Services\Service;
use Yxd\Services\Cms\GameService;
use Yxd\Models\Cms\Game;
use Yxd\Services\Models\Games;
use Yxd\Services\Models\Like;
use Yxd\Services\Models\LikeLogs;
use Yxd\Services\Models\AccountFollow;

class LikeService extends Service
{
	protected static  $CONN = 'cms';
	const TOPIC = 0;
	const GAME = 6;
	const NEWS = 1;
	const GUIDE = 2;
	const OPINION = 3;
	const NEWGAME = 4;
	const VIDEO = 5;
	const XGAME = 7;
	
	/**
	 * 获取赞列表
	 */
	public static function getLikeList($id,$type,$uid=0,$page=1,$pagesize=10)
	{
		$like_type = Config::get('yxd.like_type');
		$table = $like_type[$type];		
		
		$f_uids = AccountFollow::db()->where('uid','=',$uid)->lists('fuid');
		$total = Like::db()->where('target_id','=',$id)->where('target_table','=',$table)->count();
		$uids = Like::db()
		->where('target_id','=',$id)
		->where('target_table','=',$table)
		->orderBy('ctime','desc')
		->forPage($page,$pagesize)
		->lists('uid');
		$users = UserService::getBatchUserInfo($uids);
		$out = array();
		foreach($users as $index=>$row){
			$user = array();
			$user['uid'] = $row['uid'];
			$user['nickname'] = $row['nickname'];
			$user['avatar'] = $row['avatar'];
			$user['level_name'] = $row['level_name'];
			$user['level_icon'] = $row['level_icon'];
			$user['summary'] = $row['summary'];
			$user['attention'] = in_array($row['uid'],$f_uids) ? 1 : 0;
			$out[] = $user;
		}
		
		return array('likes'=>$out,'total'=>$total);
	}
	
	/**
	 * 赞
	 */
	public static function doLike($tid,$type,$uid)
	{
		if(self::isLike($tid, $type, $uid)){
			return -1;
		}
		$user_identify = UserService::getUserAppleIdentify($uid);
		if($user_identify !==false){	
			$key = 'like::' . $type . '::' . $tid . '::' . md5($user_identify);
            /*
			if(self::redis()->get($key)){
				return -1;
			}
            */
            $like =  LikeLogs::db()->where('key','=',$key)->first();
            if($like){
                return -1;
            }
		}
		
		$like_type = Config::get('yxd.like_type');
		$table = $like_type[$type];
		$data = array();
		$data['uid'] = $uid;
		$data['target_id'] = $tid;
		$data['target_table'] = $table;
		$data['ctime'] = time();
		$id = Like::db()->insertGetId($data);
		if($table == 'topic'){
			ThreadService::updateLikes($tid);
		}
		if($id && $user_identify!==false) {
            //self::redis()->setex($key,3600*24*30,1);
            $log = array(
                'key'=>$key,
                'like_target_type'=>$table,
                'like_target_id'=>$tid,
                'identify'=>$user_identify,
                'ctime'=>time(),
                'num'=>1
            );
            LikeLogs::db()->insert($log);
        }
		return $id ? true : false;
	}
	
	/**
	 * 是否赞过
	 */
	public static function isLike($id,$type,$uid)
	{
		$like_type = Config::get('yxd.like_type');
		$table = $like_type[$type];
		$count = Like::db()->where('target_id','=',$id)->where('uid','=',$uid)->where('target_table','=',$table)->count();
		if($count>0) return true;
		
	    $user_identify = UserService::getUserAppleIdentify($uid);
		if($user_identify !==false){	
			$key = 'like::' . $type . '::' . $id . '::' . md5($user_identify);
            /*
			if(self::redis()->get($key)){
				return true;
			}
            */
            $like =  LikeLogs::db()->where('key','=',$key)->first();
            if($like){
                return true;
            }
		}
		return false;
	}
}