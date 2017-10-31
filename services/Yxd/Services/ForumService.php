<?php
/**
 * @category Forum
 * @link http://www.youxiduo.com
 * @author mawenpei<mawenpei@cwan.com>
 * @since 2014-03-15
 * @version 3.0.0
 */
namespace Yxd\Services;

use Yxd\Models\Thread;

use Yxd\Services\Cms\AdvService;
use Yxd\Services\Cms\GameService;
use Yxd\Services\Cms\CommentService;
use Yxd\Utility\ForumUtility;
use Yxd\Models\Topic;

use Illuminate\Support\Facades\DB;
use Yxd\Models\Forum;

class ForumService extends Service
{
	const CHANNEL_TYPE_PEOPLE = 1;
	const CHANNEL_TYPE_QUESTION = 2;
	const CHANNEL_TYPE_GAMESHOW = 3;
	
	public static function getOpenForumGids()
	{
		$gids = self::dbClubSlave()->table('forum')->lists('gid');
		return $gids;
	}
	
	
	/**
	 * 获取版块信息
	 */
	public static function getChannelList($gid,$autoadd=false)
	{
		$channels = Forum::getChannelList($gid,$autoadd);
		return self::send(200,$channels);
	}
	
	public static function getChannelIcon($cid)
	{
		$icon = '';
		switch($cid){
			case 1:
				$icon = '/userdirs/common/forum/people@2x.png';
				break;
			case 2:
				$icon = '/userdirs/common/forum/ask@2x.png';
				break;
			case 3:
				$icon = '/userdirs/common/forum/gameshow@2x.png';
				break;
			default:
				$icon = '/userdirs/common/forum/channel@2x.png';
				break;
		}
		return $icon;
	}
	
	/**
	 * 获取通知列表
	 */
	public static function getNoticeList($gid)
	{
		$notices = DB::table('forum_topic')
		               ->whereIn('gid',array(0,$gid))		               
		               ->where('displayorder','>=',2)
		               ->where('status','=',1)
		               ->orderBy('gid','asc')
		               ->orderBy('dateline','desc')
		               ->get();
		return $notices;
	}
	
	/**
	 * 获取通知信息
	 */
	public static function getNoticeInfo($id)
	{
		$notice = Forum::getNoticeInfo($id);
		return self::send(200,$notice);
	}
	
	public static function getOpenStatus($game_id)
	{
		$count = DB::table('forum')->where('gid','=',$game_id)->count();
		return $count > 0 ? 1 : 0;
	}
	
	/**
	 * 获取圈子用户
	 */
	public static function getCircleUsers($gid,$page,$pagesize)
	{
		$uids = Forum::getCircleUsers($gid);
		$users = UserService::getBatchUserInfo($uids);
		$count = Forum::getCircleUserCount($gid);
		$data = array('total'=>$count,'users'=>$users);
		return self::send(200,$data);
	}
	
	public static function getCircleFriends($gid,$uid)
	{
		$c_uids = self::dbClubSlave()->table('account_circle')->where('game_id','=',$gid)->forPage(1,100)->orderBy('id','desc')->lists('uid');
		$f_uids = self::dbClubSlave()->table('account_follow')->where('uid','=',$uid)->lists('fuid');
		if(!$c_uids) return array('result'=>array(),'totalCount'=>0);
		$users = UserService::getBatchUserInfo($c_uids);
		$out = array();
		foreach($users as $row){
			$user = array();
			$user['userID'] = $row['uid'];
			$user['attention'] = in_array($row['uid'],$f_uids) ? 1 : 0;
			$user['level'] = $row['level_name'];
			$user['userName'] = $row['nickname'];
			$user['userAvator'] = self::joinImgUrl($row['avatar']);
			$user['userLevelImage'] = self::joinImgUrl($row['level_icon']);
			$user['signature'] = $row['summary'];
			$out[] = $user;
		}
		return array('result'=>$out,'totalCount'=>count($c_uids));
	}	
	
	public static function getTopicCount($gid)
	{
		return self::buildWhere($gid,0)->count();
	}
	
	/**
	 * 获取帖子列表
	 */
	public static function getTopicList($gid=0,$cid,$page=1,$pagesize=10,$sort=0)
	{
		$total = self::buildWhere($gid,$cid)->count();
		$tb = self::buildWhere($gid,$cid);
		if($sort==0){
			$gid==0 && $tb = $tb->orderBy('displayorder','desc')->orderBy('stick','desc');
			$tb = $tb->orderBy('lastpost','desc');
		}elseif($sort==1){
			$gid==0 && $tb = $tb->orderBy('displayorder','desc')->orderBy('stick','desc');
			$tb = $tb->orderBy('replies','desc')->orderBy('lastpost','desc');
		}
		$threads = $tb->forPage($page,$pagesize)->get();
		
		if($total==0) return array('list'=>array(),'total'=>0);
		$uids = $gids = array();		
		foreach($threads as $key=>$row){
			$uids[] = $row['author_uid'];
			$gids[] = $row['gid'];
		}
		$uids = array_unique($uids);
		$users = UserService::getBatchUserInfo($uids);
		$games = GameService::getGamesByIds($gids);
		$out = array();
		foreach($threads as $index=>$row){
			//if(!isset($games[$row['gid']])) continue;
			$topic = array();
			$topic['qid'] = $row['tid'];
			$topic['gameID'] = $row['gid'];
			$topic['gameIcon'] = isset($games[$row['gid']]) ? self::joinImgUrl($games[$row['gid']]['ico']) : self::joinImgUrl('/userdirs/common/yxd_gglogo.png');
			$topic['questionStatus'] = $row['askstatus'];
			$topic['questionDate'] = date('Y-m-d H:i:s',($row['lastpost'] ? $row['lastpost']  : $row['dateline']));
			$topic['title'] = $row['subject'];
		    $topic['img'] = self::joinImgUrl($row['listpic']);
		    $file = storage_path() . $row['listpic'];
			if(is_file($file)===true && is_readable($file)===true){
			    list($width,$height,$type,$att) = @getimagesize($file);
				$topic['imgWidth'] = $width;
			    $topic['imgHeight'] = $height;
			}else{
				$topic['imgWidth'] = 0;
				$topic['imgHeight'] = 0;
			}
			//增加字段stick/digest
			$topic['stick'] = $row['stick'];
			$topic['digest'] = $row['digest'];
			
			$topic['likes'] = $row['likes'];
			$topic['zancount'] = $row['likes'];
			$topic['userID'] = $row['author_uid'];
			$topic['userName'] = $users[$row['author_uid']]['nickname'];
			$topic['userLevel'] = $users[$row['author_uid']]['level_name'];
			$topic['userLevelImage'] = self::joinImgUrl($users[$row['author_uid']]['level_icon']);
			$topic['award'] = $row['award'];
			$topic['userAvatar'] = self::joinImgUrl($users[$row['author_uid']]['avatar'],120);
			$topic['commentCount'] = $row['replies'];	
			$out[] = $topic;		
		}
		return array('list'=>$out,'total'=>$total);
	}
	
	protected static function buildWhere($gid=0,$cid)
	{
		$tb = DB::table('forum_topic')->where('displayorder','>=',0);
		//gid=0表示取出所有游戏对应的帖子
		if($gid>0){
			$tb = $tb->where('gid','=',$gid);
		}else{
			$tb = $tb->where('status','=',1);
		}
		
		if($cid){
			//广场列表里显示公告
			if($gid==0){
				$tb = $tb->where(function($query)use($cid){
				    $query = $query->where('cid','=',$cid)->orWhere('cid','=',-1);
				});
			}else{
			    $tb = $tb->where('cid','=',$cid);
			}
			if($cid==2){
				//$tb = $tb->where('ask','=',1);
			}
		}
		return $tb;
	} 
	
	/**
	 * 获取帖子详情
	 * 
	 */
	public static function getTopicDetail($tid,$page=1,$pagesize=10,$uid=0)
	{
		$detail = Thread::getFullTopic($tid);
		$out = array();
		if($detail){
			$out['newsInfo']['nid'] = $detail['tid'];
			if($detail['is_admin']){
				$html = $detail['format_message'];
			}else{
				$content = json_decode($detail['message'],true);
				$html = '';
				foreach($content as $row){
					if($row['img']){
						$html .= '<p style="text-align:center">' . '<img src="' . self::joinImgUrl($row['img']) . '" />' . '</p>';
					}
					if($row['text']){
						$html .= '<p>' . $row['text'] . '</p>';
					}
				}
			}
			$out['newsInfo']['htmlBody'] = ForumUtility::doDetailContent($html);
			
			$out['newsInfo']['title'] = $detail['subject'];						
			$out['newsInfo']['newsDate'] = date('Y-m-d H:i:s',$detail['dateline']);			
			//$out['newsInfo']['editor'] = $detail['writer'];
						
			$out['newsInfo']['articleType'] = $detail['cid'];
			$channels = Forum::getChannelKV($detail['gid']);
			$out['newsInfo']['articleTypeName'] = isset($channels[$detail['cid']]) ? $channels[$detail['cid']] : '';
			$out['newsInfo']['reward'] = $detail['award'];
			//3.1版新增字段：加精
			$out['newsInfo']['digest'] = $detail['digest'];
			//3.1版新增字段：加精奖励的游币
			$out['newsInfo']['rate'] = $detail['rate'];
			$out['newsInfo']['questionState'] = $detail['askstatus'];
			$user = UserService::getUserInfo($detail['author_uid']);
			$out['newsInfo']['userBase'] = array(
			    'userID'=>$user['uid'],
			    'userName'=>$user['nickname'],
			    'userAvatar'=>self::joinImgUrl($user['avatar']),
			    'userLevel'=>$user['level_name'],
			    'userLevelImage'=>self::joinImgUrl($user['level_icon']),
			);			
            
			$out['advertisementInfo'] = array();
            $adv = AdvService::getDetailAdv(AdvService::DETAIL_ADV_TOPIC,$detail['gid']);
            if($adv){
				$out['advertisementInfo']['gid'] = $adv['game_id'];
				$out['advertisementInfo']['name'] = $adv['game_name'];
				$out['advertisementInfo']['type'] = $adv['game_type'];
				$out['advertisementInfo']['language'] = $adv['game_language'];
				$out['advertisementInfo']['size'] = $adv['game_size'];
				$out['advertisementInfo']['iconURL'] = $adv['game_ico'];
				$out['advertisementInfo']['downloadURL'] = $adv['game_downloadurl'];
            }else{
            	$out['advertisementInfo'] = (object)null;
            }
			//喜欢
			$likes = LikeService::getLikeList($tid,LikeService::TOPIC,0,1,5);
			//V3.1 增加是否已赞字段
			$out['likes']['islike'] = LikeService::isLike($tid,LikeService::TOPIC,$uid)==true ? 1 : 0;
			$out['likes']['totalCount'] = $likes['total'];
			$out['likes']['likeInfos'] = array();
			foreach($likes['likes'] as $index=>$row){
				$like = array();
				$like['userBase']['userID'] = $row['uid'];
				$like['userBase']['userName'] = $row['nickname'];
				$like['userBase']['userAvator'] = self::joinImgUrl($row['avatar']);
				$like['userBase']['userLevel'] = $row['level_name'];
				$like['emotionType'] = 1;
				$out['likes']['likeInfos'][] = $like;
			}	
			/*
			//游戏
			$game = GameService::getGameInfo($detail['gid']);
			$out['url'] = self::joinImgUrl($game['ico']);
			$out['games']['gid'] = $game['id'];
			$out['games']['title'] = $game['shortgname'];
			$out['games']['summary'] = $game['shortcomt'];
			$out['games']['download'] = $game['downtimes'];
			$out['games']['score'] = $game['score'];
			$out['games']['img'] = self::joinImgUrl($game['ico']);
			$out['games']['commentcount'] = 0;//$game[''];
			*/
			//评论
			$out['comments'] = array();
			$out['comments']['isQuestion'] = $detail['ask'];
			
			$out['comments']['isFinish'] = 0 ;
		    $out['comments']['commentInfos'] = array();
		    $comments = CommentService::getAppOfList($tid,'yxd_forum_topic',$page,$pagesize);
		    
		    foreach($comments['result'] as $row){
				$comment = array();
				$comment['cid'] = $row['id'];
				$comment['isBest'] = $row['best'];
				if($row['best']){
					$out['comments']['isFinish'] = 1 ;
				}
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
				if(isset($row['quote']) && $row['quote']){
					$row['quote']['content'] = json_decode($row['quote']['content'],true);
					if($row['quote']['content'] && count($row['quote']['content'])>0){									
					    $comment['replyInfo']['toContent'] = $row['quote']['content'][0]['text'];
					    $comment['replyInfo']['toImage'] = self::joinImgUrl($row['quote']['content'][0]['img']);				
					}
					$comment['replyInfo']['toUser']['userID'] = $row['quote']['author']['uid'];
					$comment['replyInfo']['toUser']['userName'] = $row['quote']['author']['nickname'];
				}
				
				$out['comments']['commentInfos'][] = $comment;
			}
			//检查寻宝箱活动中奖情况
			
			$prize = \Yxd\Modules\Activity\HuntService::checkIsWinPrize($uid, $detail['gid'], $tid);
			if($prize === false){
				$out['is_win'] = -1;				
			}elseif($prize === 0){
				$out['is_win'] = 0;
			}else{
				$out['is_win'] = 1;
				$out['prize'] = $prize;
			}
			return array('result'=>$out,'totalCount'=>$comments['total']);
		}
		return null;
	}
	
       
}