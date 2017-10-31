<?php
namespace Yxd\Services\Cms;

use Yxd\Services\LikeService;
use Yxd\Utility\ForumUtility;
use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;

class InfoService extends Service
{
	const NEWS = 1;
	const GUIDE = 2;
	const OPINION = 3;
	const NEWGAME = 4;
	const TOPIC = 0;
	const VIDEO = 5;
    /**
	 * 获取新闻
	 */
	public static function getNewsList($page=1,$pagesize=10,$sort=0)
	{
		$orderby = $sort==0 ? 'addtime' : 'commenttimes';
		$total = self::dbCmsSlave()->table('news')->where('pid','>=',0)->where('litpic','!=','')->where('zxshow','=',1)->count();
		$artlist = self::dbCmsSlave()->table('news')->where('pid','>=',0)->where('litpic','!=','')->where('zxshow','=',1)->forPage($page,$pagesize)->orderBy($orderby,'desc')->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['gnid'] = $row['id'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['commentcount'] = $row['commenttimes'];
			$out[$index]['pictures'] = array();
			$pic1 = trim($row['litpic']); 
			if($pic1){
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic']));
			}
			
		    if(trim($row['litpic2']) && trim($row['litpic3'])){
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic2']));
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic3']));
			}
			
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 获取多类型新闻列表
	 */
	public static function getNewsListss2($gnid,$page=1,$pagesize=10)
	{
		$tb = self::dbCmsSlave()->table('news');
		$total = $tb->whereIn('pid',$gnid)->count();
		$artlist = $tb->whereIn('pid',$gnid)->forPage($page,$pagesize)->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['gnid'] = $row['id'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['litpic'] = 'http://img.youxiduo.com'.$row['litpic'];
			$out[$index]['keywords'] = $row['webkeywords'];
			$out[$index]['description'] = $row['webdesc'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['video'] = '0';
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	
    /**
	 * 攻略合集
	 */
	public static function getGuideCollect($page=1,$pagesize=10,$sort=0)
	{
		$orderby = $sort==0 ? 'addtime' : 'commenttimes';
		$tb = self::dbCmsSlave()->table('gonglue');
		$total = $tb->where('pid','<=',0)->count();
		$artlist = $tb->where('pid','<=',0)->forPage($page,$pagesize)->orderBy($orderby,'desc')->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $key=>$row){
			$out[$key]['guid'] = $row['id'];
			$out[$key]['gid'] = $row['gid'];
			$out[$key]['title'] = $row['gtitle'];
			if($row['pid']==0){
				$out[$key]['series'] = '0';
			}else{
				$out[$key]['series'] = '1';
			}
			if($row['gid']){
				$game = GameService::getGameInfo($row['gid']);
				if($game){
			        $out[$key]['img'] = self::joinImgUrl($game['ico']);
				}else{
					$out[$key]['img'] = '';
				}
			}else{
				$out[$key]['img'] = '';
			}
			$art = self::dbCmsSlave()->table('gonglue')->where('pid','=',$row['id'])->orderBy('addtime','desc')->first();
			if($art){
			    $out[$key]['subtitle'] = $art['gtitle'];
			    $out[$key]['adddate'] = date('Y-m-d H:i:s',$art['addtime']);
			}else{
				$out[$key]['subtitle'] = '';
				$out[$key]['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			}
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
    /**
	 * 获取攻略列表
	 */
	public static function getGuideList($guid,$page=1,$pagesize=10)
	{
		$tb = self::dbCmsSlave()->table('gonglue');
		$total = $tb->where('pid','=',$guid)->count();
		$artlist = $tb->where('pid','=',$guid)->forPage($page,$pagesize)->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['guid'] = $row['id'];
			$out[$index]['title'] = $row['gtitle'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['video'] = '0';
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
    /**
	 * 获取新闻列表
	 */
	public static function getNewsList2($gnid,$page=1,$pagesize=10)
	{
		$tb = self::dbCmsSlave()->table('news');
		$total = $tb->where('pid','=',$gnid)->count();
		$artlist = $tb->where('pid','=',$gnid)->forPage($page,$pagesize)->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['gnid'] = $row['id'];
			$out[$index]['title'] = $row['title'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['video'] = '0';
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
    /**
	 * 获取评测列表
	 */
	public static function getOpinionList2($goid,$page=1,$pagesize=10)
	{
		$tb = self::dbCmsSlave()->table('feedback');
		$total = $tb->where('pid','=',$goid)->count();
		$artlist = $tb->where('pid','=',$goid)->forPage($page,$pagesize)->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['goid'] = $row['id'];
			$out[$index]['title'] = $row['ftitle'];
			$out[$index]['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['video'] = '0';
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	/**
	 * 获取评测
	 */
	public static function getOpinionList($page=1,$pagesize=10,$sort=0)
	{
		$orderby = $sort==0 ? 'addtime' : 'commenttimes';
		$tb = self::dbCmsSlave()->table('feedback');
		$total = $tb->where('gid','>',0)->count();
		$artlist = $tb->where('gid','>',0)->forPage($page,$pagesize)->orderBy($orderby,'desc')->orderBy('addtime','desc')->get();
		$out = array();
		foreach($artlist as $index=>$row){
			$out[$index]['goid'] = $row['id'];
			$out[$index]['title'] = $row['ftitle'];
		    $out[$index]['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['commentcount'] = $row['commenttimes'];
			$out[$index]['pictures'] = array();
		    if(trim($row['litpic'])){		    	
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic']));
			}else{
				$out[$index]['pictures'][] = array('pic'=>self::extractImg($row['content']));
			}
			
		    if(trim($row['litpic2']) && trim($row['litpic3'])){
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic2']));
			    $out[$index]['pictures'][] = array('pic'=>self::joinImgUrl($row['litpic3']));
			}
		}
		return array('result'=>$out,'totalCount'=>$total);
	}
	
	public static function extractImg($content)
	{
		$pic_url = '';
		if(empty($content) || !strstr($content,'<img')){
			return '';
		}
		$pattern = '/<img[^>]*\/>/is';
		preg_match($pattern, $content, $pic);
		if(empty($pic[0])){
			return '';
		}
		//提取‘src’值
		preg_match("'src=\"[^>]*?\"'si", $pic[0], $src_str);
		$pic_url = substr(substr($src_str[0], 5), 0, -1);
		return $pic_url;
	}

    /**
	 * 新闻
	 */
	public static function getNewsDetail($id,$page=1,$pagesize=10,$uid=0)
	{
		$detail = self::dbCmsSlave()->table('news')->where('id','=',$id)->first();
		$out = array();
		if($detail){
			$out['newsInfo']['nid'] = $detail['id'];
			$out['newsInfo']['htmlBody'] = ForumUtility::doDetailContent($detail['content']);
			$out['newsInfo']['title'] = $detail['title'];			
			//$out['newsInfo']['img'] = self::joinImgUrl($detail['litpic']);			
			$out['newsInfo']['newsDate'] = date('Y-m-d H:i:s',$detail['addtime']);			
			//$out['newsInfo']['editor'] = $detail['writer'];			
            //广告
            
            $out['advertisementInfo'] = array();
            $adv = AdvService::getDetailAdv(AdvService::DETAIL_ADV_NEWS,$id,$detail['gid']);
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
			$likes = LikeService::getLikeList($id,LikeService::NEWS,0,1,5);
			//V3.1 增加是否已赞字段
			$out['likes']['islike'] = LikeService::isLike($id,LikeService::NEWS,$uid)==true ? 1 : 0;
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
			$out['comments']['isQuestion'] = 0;
			
			$out['comments']['isFinish'] = 0;
		    $out['comments']['commentInfos'] = array();
		    $comments = CommentService::getAppOfList($id,'m_news',$page,$pagesize);
		    
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
			return array('result'=>$out,'totalCount'=>$comments['total']);
		}
		return null;
	}
	
	/**
	 * 攻略
	 */
    public static function getGuideDetail($id,$page=1,$pagesize=10,$uid=0)
	{
		$detail = self::dbCmsSlave()->table('gonglue')->where('id','=',$id)->first();
		$out = array();
		if($detail){
			$out['newsInfo']['nid'] = $detail['id'];
			$out['newsInfo']['htmlBody'] = ForumUtility::doDetailContent($detail['content']);
			$out['newsInfo']['title'] = $detail['gtitle'];			
			//$out['newsInfo']['img'] = self::joinImgUrl($detail['litpic']);			
			$out['newsInfo']['newsDate'] = date('Y-m-d H:i:s',$detail['addtime']);			
			//$out['newsInfo']['editor'] = $detail['writer'];			
            //广告
            $out['advertisementInfo'] = array();
		    $adv = AdvService::getDetailAdv(AdvService::DETAIL_ADV_GUIDE,$id,$detail['gid']);
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
			$likes = LikeService::getLikeList($id,LikeService::GUIDE,0,1,5);
			//V3.1 增加是否已赞字段
			$out['likes']['islike'] = LikeService::isLike($id,LikeService::GUIDE,$uid)==true ? 1 : 0;
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
			$out['comments']['isQuestion'] = 0;
			
			$out['comments']['isFinish'] = 0;
		    $out['comments']['commentInfos'] = array();
		    $comments = CommentService::getAppOfList($id,'m_gonglue',$page,$pagesize);
		    
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
			return array('result'=>$out,'totalCount'=>$comments['total']);
		}
		return null;
	}
	
	/**
	 * 评测
	 */
    public static function getOpinionDetail($id,$page=1,$pagesize=10,$uid=0)
	{
		$detail = self::dbCmsSlave()->table('feedback')->where('id','=',$id)->first();
		$out = array();
		if($detail){
			$out['newsInfo']['nid'] = $detail['id'];
			$out['newsInfo']['htmlBody'] = ForumUtility::doDetailContent($detail['content']);;
			$out['newsInfo']['title'] = $detail['ftitle'];			
			//$out['newsInfo']['img'] = self::joinImgUrl($detail['litpic']);			
			$out['newsInfo']['newsDate'] = date('Y-m-d H:i:s',$detail['addtime']);			
			//$out['newsInfo']['editor'] = $detail['writer'];			
            //广告
            $out['advertisementInfo'] = array();
		    $adv = AdvService::getDetailAdv(AdvService::DETAIL_ADV_OPINION,$id,$detail['gid']);
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
			$likes = LikeService::getLikeList($id,LikeService::OPINION,0,1,5);
			//V3.1 增加是否已赞字段
			$out['likes']['islike'] = LikeService::isLike($id,LikeService::OPINION,$uid)==true ? 1 : 0;
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
			$out['comments']['isQuestion'] = 0;
			
			$out['comments']['isFinish'] = 0;
		    $out['comments']['commentInfos'] = array();
		    $comments = CommentService::getAppOfList($id,'m_feedback',$page,$pagesize);
		    
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
			return array('result'=>$out,'totalCount'=>$comments['total']);
		}
		return null;
	}
	
	/**
	 * 新游
	 */
	public static function getNewGameDetail($id,$page=1,$pagesize=10,$uid=0)
	{
		$detail = self::dbCmsSlave()->table('game_notice')->where('id','=',$id)->first();
		$out = array();
		if($detail){
			$out['newsInfo']['nid'] = $detail['id'];
			$out['newsInfo']['htmlBody'] = ForumUtility::doDetailContent($detail['art_content']);
			$out['newsInfo']['title'] = $detail['title'] ? : $detail['gname'];			
			//$out['newsInfo']['img'] = self::joinImgUrl($detail['litpic']);			
			$out['newsInfo']['newsDate'] = date('Y-m-d H:i:s',$detail['addtime']);			
			//$out['newsInfo']['editor'] = $detail['writer'];			
            //广告
            $out['advertisementInfo'] = array();
		    $adv = AdvService::getDetailAdv(AdvService::DETAIL_ADV_NEWGAME,$id);
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
			$likes = LikeService::getLikeList($id,LikeService::NEWGAME,0,1,5);
			//V3.1 增加是否已赞字段
			$out['likes']['islike'] = LikeService::isLike($id,LikeService::NEWGAME,$uid)==true ? 1 : 0;
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
			$out['comments']['isQuestion'] = 0;
			
			$out['comments']['isFinish'] = 0;
		    $out['comments']['commentInfos'] = array();
		    $comments = CommentService::getAppOfList($id,'m_game_notice',$page,$pagesize);
		    
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
			return array('result'=>$out,'totalCount'=>$comments['total']);
		}
		return null;
	}
	
	public static function getNewsInfo($id)
	{
		$info = self::dbCmsSlave()->table('news')->where('id','=',$id)->first();
		
		return $info;
	}
	
	public static function getGuideInfo($id)
	{
		$info = self::dbCmsSlave()->table('gonglue')->where('id','=',$id)->first();
		
		return $info;
	}
	
	public static function getOpinionInfo($id)
	{
		$info = self::dbCmsSlave()->table('feedback')->where('id','=',$id)->first();
		
		return $info;
	}
	
	public static function getNewGameInfo($id)
	{
		$info = self::dbCmsSlave()->table('game_notice')->where('id','=',$id)->first();
		
		return $info;
	}
	
	public static function getVideoInfo($id)
	{
		$info = self::dbCmsSlave()->table('videos')->where('id','=',$id)->first();
		
		return $info;
	}
	
	public static function getShortInfo($id,$table)
	{
		$out = array('cate'=>'','title'=>'');
		switch(strtolower($table))
		{
			case 'm_news':
				$info = self::dbCmsSlave()->table('news')->where('id','=',$id)->select('title')->first();
				$out['cate'] = '新闻';
				$out['title'] = $info['title'];
				break;
			case 'm_gonglue':
				$info = self::dbCmsSlave()->table('gonglue')->where('id','=',$id)->select('gtitle')->first();
				$out['cate'] = '攻略';
				$out['title'] = $info['gtitle'];
				break;
			case 'm_feedback':
				$info = self::dbCmsSlave()->table('feedback')->where('id','=',$id)->select('ftitle')->first();
				$out['cate'] = '评测';
				$out['title'] = $info['ftitle'];
				break;
			case 'm_games':
				$info = GameService::getGameInfo($id);
				$out['cate'] = '游戏';
				$out['title'] = $info['shortgname'];
				break;
			case 'm_game_notice':
				$info = self::dbCmsSlave()->table('game_notice')->where('id','=',$id)->select('title')->first();
				$out['cate'] = '新游';
				$out['title'] = $info['title'];
				break;
			case 'm_videos':
				$info = self::dbCmsSlave()->table('videos')->where('id','=',$id)->select('vname')->first();
				$out['cate'] = '视频';
				$out['title'] = $info['vname'];
				break;
			case 'yxd_forum_topic':
				$info = self::dbClubSlave()->table('forum_topic')->where('tid','=',$id)->select('subject','gid')->first();
				if($info){
					$game = GameService::getGameInfo($info['gid']);
					$out['cate'] = $game ? $game['shortgname'] : '论坛';
					$out['title'] = $info['subject'];
				}
				break;
			case 'm_xyx_game':
				$info = self::dbCmsSlave()->table('xyx_game')->where('id','=',$id)->select('gamename','id')->first();
				if($info){
					$out['cate'] = '小游戏';
					$out['title'] = $info['gamename'];
				}
				break;
		}
		return $out;
	}
	
}