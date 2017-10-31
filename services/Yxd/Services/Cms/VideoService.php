<?php
namespace Yxd\Services\Cms;

use Yxd\Modules\Core\CacheService;

use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;
use Yxd\Services\Models\News;
use Yxd\Services\Models\Gonglue;
use Yxd\Services\Models\Feedback;
use Yxd\Services\Models\NewGame;
use Yxd\Services\Models\GameNotice;
use Yxd\Services\Models\Videos;
use Yxd\Services\Models\VideosGames;

class VideoService extends Service
{
	/**
	 * 首页推荐
	 */
	public static function getHomeTop()
	{
		
	}
	
	/**
	 * 美女视频列表页
	 */
	public static function getVideoList($page=1,$pagesize=10,$type=0)
	{
		$tb = Videos::db()->where(function($query){
			    $query = $query->where('apptype','=',1)->orWhere('apptype','=',3);
			});
		if($type==0){
			$total = $tb->count();
			$videos = $tb->orderBy('addtime','desc')
			          ->forPage($page,$pagesize)
			          ->get();
		}else{
			
			$total = $tb->where('type','=',$type)->count();
			$videos = $tb->where('type','=',$type)
			          ->orderBy('addtime','desc')
			          ->forPage($page,$pagesize)
			          ->get();
		}
		$out = array();
		$ids = array();
		foreach($videos as $d){
			$ids[] = $d['id'];
		}
		$cmts = CommentService::getTotalByType($ids,'m_videos');
		foreach($videos as $index=>$row){
			$out[$index]['vid'] = $row['id'];
			$out[$index]['title'] = $row['vname'];
			$out[$index]['img'] = self::joinImgUrl($row['litpic']);
			$out[$index]['type'] = $row['type'];
			$out[$index]['editor'] = $row['writer'];
			$out[$index]['commentCount'] = isset($cmts[$row['id']]) ? $cmts[$row['id']]: 0;
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
		}
		return array('videos'=>$out,'total'=>$total);
	}
	
	/**
	 * 视频内容页
	 */
	public static function getVideoDetail($id,$page=1,$pagesize=10)
	{
		$tb = Videos::db()->where(function($query){
			    //$query = $query->where('apptype','=',1)->orWhere('apptype','=',3);
			});
		$cachekey_video = 'object::video::id::' . $id;
		if(CLOSE_CACHE===false && CacheService::has($cachekey_video)){
			$video = CacheService::get($cachekey_video);
		}else{
		    $video = $tb->where('id','=',$id)->first();
		    CLOSE_CACHE===false && CacheService::forever($cachekey_video,$video);
		}
		if(!$video) return null;
		$max_id = Videos::db()->max('id');
		$min_id = Videos::db()->min('id');
		$pre_id = $next_id = 0;
		if($id<$max_id){
			$res = Videos::db()->where('id','>',$id)->orderBy('id','desc')->first();			
			$pre_id = isset($res['id']) ? $res['id'] : 0;
		}
		if($id > $min_id){
			$res = Videos::db()->where('id','<',$id)->orderBy('id','asc')->first();			
			$next_id = isset($res['id']) ? $res['id'] : 0;
		}
		
		$out = array();
		$out['vid'] = $video['id'];
		$out['title'] = $video['vname'];
		$out['type'] = $video['type'];
		$out['score'] = $video['score'];
		$out['img'] = self::joinImgUrl($video['litpic']);
		$out['url'] = $video['video'];
		$out['editor'] = $video['writer'];
		$out['desc'] = $video['description'];
		$out['updatetime'] = date("Y-m-d", $video['addtime']);						
		$out['pre_vid'] = $pre_id;
		$out['next_vid'] = $next_id;
		$out['viewcount'] = $video['viewtimes'];
		
		$out['games'] = array();
		$out['gfid'] = $video['gfid'];
		$gids = VideosGames::db()->where('vid','=',$id)->where('gid','>',0)->forPage(1,6)->lists('gid');
		if($video['gid']>0){
			$gids[] = $video['gid'];
		}
		if($gids){
			$games = GameService::getGamesByIds($gids);
			foreach($games as $row){
				$game = array();
				$game['gid'] = $row['id'];
				$game['title'] = $row['shortgname'];
				$game['summary'] = $row['shortcomt'];
				$game['download'] = $row['downurl'];
				$game['score'] = $row['score'];
				$game['img'] = self::joinImgUrl($row['ico']);
				$game['commentcount'] = $row['commenttimes'];
				$out['games'][] = $game;
			}				    
		}		
		
	    //评论
		$out['comments'] = array();
		$out['comments']['isQuestion'] = 0;
		
		$out['comments']['isFinish'] = 0;
	    $out['comments']['commentInfos'] = array();
	    $comments = CommentService::getAppOfList($id,'m_videos',$page,$pagesize);
	    
	    foreach($comments['result'] as $row){
			$comment = array();
			$comment['cid'] = $row['id'];
			$comment['isBest'] = 0;
			$comment['floorIndex'] = $row['storey'];
			$row['content'] = json_decode($row['content'],true);
			$comment['replyInfo']['replyTopic'] = 0;
			if($row['content'] && count($row['content'])>0){									
			    $comment['replyInfo']['replyContent'] = $row['content'][0]['text'];
			    $comment['replyInfo']['replyImage'] = self::joinImgUrl($row['content'][0]['img']);				
			}
			$comment['replyInfo']['replyDate'] = date('Y-m-d H:i:s',$row['addtime']);
			$comment['replyInfo']['tocid'] = $row['pid'];
			
			$comment['replyInfo']['fromUser']['userID'] = $row['author']['uid'];
			$comment['replyInfo']['fromUser']['userName'] = $row['author']['nickname'];
			$comment['replyInfo']['fromUser']['userAvator'] = self::joinImgUrl($row['author']['avatar']);
			$comment['replyInfo']['fromUser']['userLevel'] = $row['author']['level_name'];
			$comment['replyInfo']['fromUser']['userLevelImage'] = self::joinImgUrl($row['author']['level_icon']);
			$comment['replyInfo']['fromUser']['isNewUser'] = UserService::isNewUser($row['author']);
			if(isset($row['quote']) && $row['quote']){
				$row['quote']['content'] = json_decode($row['quote']['content'],true);
				if($row['quote']['content'] && count($row['quote']['content'])>0){									
				    $comment['replyInfo']['toContent'] = $row['content'][0]['text'];
				    $comment['replyInfo']['toImage'] = self::joinImgUrl($row['content'][0]['img']);				
				}
				$comment['replyInfo']['toUser']['userID'] = $row['author']['uid'];
				$comment['replyInfo']['toUser']['userName'] = $row['author']['nickname'];
				$comment['replyInfo']['toUser']['isNewUser'] = UserService::isNewUser($row['quote']['author']);
			}
			
			$out['comments']['commentInfos'][] = $comment;
		}
		
		return array('result'=>$out,'totalCount'=>$comments['total']);		
	}	
}