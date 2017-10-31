<?php
namespace Yxd\Services\Cms;

use Yxd\Modules\Activity\GiftbagService;

use Yxd\Services\CircleFeedService;

use Yxd\Services\UserFeedService;

use Yxd\Services\RelationService;

use Yxd\Services\UserService;

use Yxd\Services\ThreadService;

use Yxd\Services\ForumService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Yxd\Services\Service;
use Yxd\Services\Cms\GameService;
use Yxd\Models\Cms\Game;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\DES;
use Illuminate\Support\Facades\Config;

class GameCircleService extends Service
{	
    const API_URL_CONF = 'app.mall_api_url';
    const MALL_API_ACCOUNT = 'app.account_api_url';
	/**
	 * 游戏圈主页
	 */
	public static function getHomePage($gid,$uid,$appname,$version)
	{		
		$out = array();
		$game = GameService::getGameInfo($gid);
		
		$gametype = GameService::getGameTypeOption();
		$out['gid'] = $game['id'];
		$out['url'] = self::joinImgUrl($game['ico']);
		$out['bg']  = GameService::joinImgUrl($game['ico']);
		$out['gname'] = $game['shortgname'];
		$out['gametype'] = isset($gametype[$game['type']]) ? $gametype[$game['type']] : '';		
		$out['free'] = $game['pricetype']==1?1:0;
		$out['limitfree'] = $game['pricetype']==2?1:0;
		$out['price'] = $game['price'];
		$out['oldprice'] = $game['oldprice'];
		$out['language'] = $game['language'];
		$out['downcount'] = $game['downtimes'];
		$out['download'] = $game['downurl'];
		$out['tosafari'] = isset($game['tosafari']) ? $game['tosafari'] : 0; 
		$out['star'] = $game['score'];
		
// 		$out['moneyCount'] = GameService::filterDownloadCredit($gid, $uid);
		
        //试玩
		$play = self::dbCmsSlave()->table('zone')->where('type','=',1)->where('gid','=',$gid)->orderBy('id','desc')->first();
		if($play){
			$out['playurl'] = $play['linkurl'];
			$out['playtosafari'] = $play['tosafari'];
			$out['newPlayImageUrl'] = self::joinImgUrl($play['litpic']);
		}else{
			$out['playurl'] = '';
			$out['playtosafari'] = 0;
			$out['newPlayImageUrl'] = '';
		}
		//游戏信息
		$out['Infointroduce'] = array(
		    'device'=>$game['platform'],
			'updatetime'=> date('Y-m-d H:i:s',$game['updatetime']),
			'developer'=>$game['company'],
			'language'=>$game['language'],
			'version'=>$game['version'],
		    'size'=>$game['size']
		);
		//
		//$cmts = CommentService::getTotalByType(array($gid),'m_games');
		//圈友数
		$out['commcount'] = self::getGameCircleUserCount($gid);
		//信息数=评论+发帖+回复数+资料大全的文章数+回复数
		$out['postcount'] = self::getGameCircleInfoCount($gid);
		if($uid>0){
		    $my_games = self::getMyGameIds($uid);
		    $in_circle = in_array($gid,$my_games);
		}
		$out['isin'] = isset($in_circle) ? (int)$in_circle : 0;	
		//预约礼包	
// 		$out['reservedgift'] = $uid ? (int)GiftService::isReserve($gid,$uid) : 0;
		
		//游戏介绍
		$out['appraise'] = $game['editorcomt'];
		//游戏背景图/截图
		$images = self::dbCmsSlave()->table('games_litpic')->where('gid','=',$gid)->get();
		foreach($images as $row){
			$out['images'][] = array('url'=>self::joinImgUrl($row['litpic']));
		}
		if(isset($images[0]['litpic']) && $images[0]['litpic'] && file_exists('/mnt/www/yxd_www/bestofbest/' . $images[0]['litpic'])){
			list($width,$height,$type,$ext) = getimagesize('/mnt/www/yxd_www/bestofbest/' . $images[0]['litpic']);
			if($width > $height){
				$out['vertical'] = 0;
			}else{
				$out['vertical'] = 1;
			}
		}
		//有奖问答
		$out['overList'] = array();
                /*
		$quize = GameAskService::getAskInfo($gid);
		if($quize){
			$ask['atid'] = $quize['id'];
			$ask['url'] = self::joinImgUrl($quize['listpic']);
			$ask['title'] = $quize['title'];
			$ask['startTime'] = date('Y-m-d H:i:s',$quize['startdate']);
			$ask['endTime'] = date('Y-m-d H:i:s',$quize['enddate']);
			$ask['type'] = $quize['type'];
			$ask['tid'] = $quize['rule_id'];
			$out['overList'][] = $ask;
		}
                */
		//礼包
		$out['gift'] = array();
		//$gifts = self::dbCmsSlave()->table('gift')->where('gid','=',$gid)->orderBy('addtime','desc')->forPage(1,1)->get();
// 		$gifts = GiftbagService::getDetailByGameID($gid);
		// 新版本
		// 获取礼包列表
		$params = array('productType'=>2,'pageIndex'=>1,'pageSize'=>1,'gids'=>$gid,'sortType'=>'Product_Sort','platform'=>'ios','currencyType'=>0,'isOnshelf'=>'TRUE','active'=>'TRUE');
		$params_ = array('productType','pageIndex','pageSize','gids','sortType','platform','currencyType','isOnshelf','active');
		$gifts = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_product');
		
		// 获取我的礼包
		if($uid){
		    $params = array('productType'=>2,'accountId'=>$uid,'platform'=>'ios');
		    $params_ = array('productType','accountId','platform');
		    $result_gift = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_API_ACCOUNT).'accountproduct/query');
		    foreach($result_gift['result'] as $index=>$row){
		        $mygift[$row['gfid']] = !empty($row['card'])?DES::decrypt($row['card'],11111111):'';
		    }
		}else{
		    $mygift = null;
		}
		
		$gifts = $gifts['result'];
	    foreach($gifts as $index=>$row){
			$out['gift'][$index]['gfid'] = $row['gfid'];
			$game = GameService::getGameInfo($row['gid']);
			$out['gift'][$index]['url'] = self::joinImgUrl($game['ico']);
			$out['gift'][$index]['gname'] = trim($game['shortgname']) ? trim($game['shortgname']) : $game['gname'];
			$out['gift'][$index]['title'] = $row['title'];
			$out['gift'][$index]['date'] = date("Y-m-d",strtotime($row['addTime']));
			$out['gift'][$index]['adddate'] = date("Y-m-d",strtotime($row['addTime']));
			$out['gift'][$index]['starttime'] = $row['startTimeStr'];
			$out['gift'][$index]['endtime'] = $row['endTimeStr'];
			$out['gift'][$index]['ishot'] = (int)$row['isHot'];
			$out['gift'][$index]['istop'] = (int)$row['isTop'];
			$out['gift'][$index]['cardcount'] = $row['totalCount'];
			$out['gift'][$index]['lastcount'] = $row['restCount'];
			$ishas = false;
			$number = '';
			if( $row['singleLimit']==1 && isset($mygift) && is_array($mygift)){
			    $mygift_ids = array_keys($mygift);
			    $ishas = in_array($row['gfid'],$mygift_ids);
			    if($ishas){
			        $number = $mygift[$row['gfid']];
			    }
			}

			$out['gift'][$index]['ishas'] = (int)$ishas;
			$out['gift'][$index]['number'] = $number;
			
		}
		//新闻
		$artlist = self::dbCmsSlave()->table('news')->where('gid','=',$gid)->where('pid','<=',0)->forPage(1,3)->orderBy('addtime','desc')->get();
		$newslist = array();
	    foreach($artlist as $index=>$row){
	    	$tmp = array();
			$tmp['gnid'] = $row['id'];
			$tmp['title'] = $row['title'];
			$tmp['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$tmp['series'] = $row['pid']==-1 ? 1 : 0;
			$tmp['ptitle'] = $row['title'];
			$tmp['video'] = 0;
			$newslist[] = $tmp;
		}
        //攻略
		$artlist = self::dbCmsSlave()->table('gonglue')->where('gid','=',$gid)->where('pid','<=',0)->forPage(1,3)->orderBy('addtime','desc')->get();
		$guidelist = array();
		foreach($artlist as $index=>$row){
			$tmp = array();
			$tmp['guid'] = $row['id'];
			$tmp['title'] = $row['gtitle'];
			$tmp['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$tmp['series'] = $row['pid']==-1 ? 1 : 0;
			$tmp['video'] = 0;
			$guidelist[] = $tmp;
		}
        //评测
		$artlist = self::dbCmsSlave()->table('feedback')->where('gid','=',$gid)->where('pid','<=',0)->forPage(1,3)->orderBy('addtime','desc')->get();
		$opinionlist = array();
		foreach($artlist as $index=>$row){
			$tmp['goid'] = $row['id'];
			$tmp['title'] = $row['ftitle'];
			$tmp['updatetime'] = date('Y-m-d H:i:s',$row['addtime']);
			$tmp['series'] = $row['pid']==-1 ? 1 : 0;
			$tmp['video'] = 0;
			$opinionlist[] = $tmp;
		}
		if(count($newslist)>0 && count($guidelist)>0 && count($opinionlist)>0){
		    
			$out['gameArticle']['news'][] = $newslist[0];
		    $out['gameArticle']['guides'][] = $guidelist[0];
		    $out['gameArticle']['opinions'][] = $opinionlist[0];
		    
		}elseif(count($newslist)>0 && count($guidelist)>0){
			$out['gameArticle']['news'][] = $newslist[0];
			$out['gameArticle']['guides'][] = $guidelist[0];
			if(count($newslist)>1){
				$out['gameArticle']['news'][] = $newslist[1];
			}elseif(count($guidelist)>1){
				$out['gameArticle']['guides'][] = $guidelist[1];
			}
		}elseif(count($guidelist)>0 && count($opinionlist)>0){
			$out['gameArticle']['guides'][] = $guidelist[0];
		    $out['gameArticle']['opinions'][] = $opinionlist[0];
		    if(count($guidelist)>1){
				$out['gameArticle']['guides'][] = $guidelist[1];
			}elseif(count($opinionlist)>1){
				$out['gameArticle']['opinions'][] = $opinionlist[1];
			}
		}elseif(count($newslist)>0 && count($opinionlist)>0){
			$out['gameArticle']['news'][] = $newslist[0];
		    $out['gameArticle']['opinions'][] = $opinionlist[0];
		    if(count($newslist)>1){
				$out['gameArticle']['news'][] = $newslist[1];
			}elseif(count($opinionlist)>1){
				$out['gameArticle']['opinions'][] = $opinionlist[1];
			}
		}		
		/*
		//视频
		$videos = self::dbCmsSlave()->table('games_video')->where('gid','=',$gid)->where('type','=','1')->orderBy('id','desc')
		                   ->forPage(1,2)
		                   ->get();
	    foreach($videos as $index=>$row){
			$out['gameArticle']['videos'][$index]['gvid'] = $row['id'];
			$out['gameArticle']['videos'][$index]['title'] = $row['title'];
			$out['gameArticle']['videos'][$index]['img'] = ArticleService::joinImgUrl($row['ico']);
		}
		*/
		$out['commentInfos'] = array();
	    $comments = CommentService::getAppOfList($gid,'m_games',1,3);
	    if($comments && is_array($comments['result'])){
		    $out['commentCount'] = $comments['total'];
		    foreach($comments['result'] as $row){
				$comment = array();
				$comment['cid'] = $row['id'];
				$comment['isBest'] = 0;
				$comment['floorIndex'] = $row['storey'];
				$row['content'] = json_decode($row['content'],true);
				$comment['replyInfo']['replyTopic'] = 0;
				if($row['content'] && count($row['content'])>0){									
				    $comment['replyInfo']['replyContent'] = $row['content'][0]['text'];
				    $comment['replyInfo']['replyImage'] = CommentService::joinImgUrl($row['content'][0]['img']);				
				}
				$comment['replyInfo']['replyDate'] = date('Y-m-d H:i:s',$row['addtime']);
				$comment['replyInfo']['tocid'] = $row['pid'];
				
				$comment['replyInfo']['fromUser']['userID'] = $row['author']['uid'];
				$comment['replyInfo']['fromUser']['userName'] = $row['author']['nickname'];
				$comment['replyInfo']['fromUser']['userAvator'] = CommentService::joinImgUrl($row['author']['avatar']);
				$comment['replyInfo']['fromUser']['userLevel'] = $row['author']['level_name'];
				$comment['replyInfo']['fromUser']['userLevelImage'] = CommentService::joinImgUrl($row['author']['level_icon']);
				if(isset($row['quote']) && $row['quote']){
					$row['quote']['content'] = json_decode($row['quote']['content'],true);
					if($row['quote']['content'] && count($row['quote']['content'])>0){									
					    $comment['replyInfo']['toContent'] = $row['quote']['content'][0]['text'];
					    $comment['replyInfo']['toImage'] = CommentService::joinImgUrl($row['quote']['content'][0]['img']);				
					}
					$comment['replyInfo']['toUser']['userID'] = $row['quote']['author']['uid'];
					$comment['replyInfo']['toUser']['userName'] = $row['quote']['author']['nickname'];
				}
				
				$out['commentInfos'][] = $comment;
			}
	    }else{
	    	$out['commentCount'] = 0;
	    	$out['commentInfos'] = array();
	    }
		$out['BBSOpenStatus'] = ForumService::getOpenStatus($gid);
	    $channels = ForumService::getChannelList($gid,true);
		foreach($channels['data'] as $row){	
			$channel['bType'] = $row['cid'];
			$channel['imageURL'] = '';
			$channel['title'] = $row['name'];	
			$out['BBSTags'][] = $channel;
		}
		$out['BBSArticles'] = array();
		$topics = ThreadService::showTopicList($gid,0,1,3);
		foreach($topics['topics'] as $row){
			$topic['articleID'] = $row['tid'];
			$topic['articleType'] = $row['cid'];
			$topic['articleTitle'] = $row['subject'];
			$topic['articleContent'] = $row['summary'];
			$topic['articleImage'] = ThreadService::joinImgUrl($row['listpic']);
			if($row['listpic'] && file_exists(storage_path() . $row['listpic'])){
				list($width,$height,$type,$attr) = getimagesize(storage_path() . $row['listpic']);
				$topic['imageWidth'] = $width;
				$topic['imageHeight'] = $height;
			}else{
				$topic['imageWidth'] = 0;
				$topic['imageHeight'] = 0;
			}
			$topic['authorName'] = $row['author']['nickname'];
			$topic['authorAvatar'] = self::joinImgUrl($row['author']['avatar']);
			$topic['authorLevelImage'] = self::joinImgUrl($row['author']['level_icon']);
			$topic['likes'] = $row['likes'];
			$topic['digest'] = $row['digest'];
			$topic['pubDate'] = date('Y-m-d H:i:s',$row['dateline']);		
			$topic['commentCount'] = $row['replies'];
			$topic['questionState'] = $row['askstatus'];
			$topic['reward'] = $row['award'];
			$out['BBSArticles'][] = $topic;
		}
	    $out['BBSMembersInfo'] = array();
		$members = RelationService::getCircleUserList($gid);
		
		$out['BBSMembersInfo']['memberCount'] = $out['commcount'];//$members['total'];
		$out['BBSMembersInfo']['userBaseInfos'] = array();
		foreach($members['users'] as $row){
			$member['userID'] = $row['uid'];
			$member['userName'] = $row['nickname'];
			$member['userAvator'] = RelationService::joinImgUrl($row['avatar']);
			$member['userLevel'] = $row['level_name'];
			$out['BBSMembersInfo']['userBaseInfos'][] = $member;
		}
		//猜你喜欢广告		
		$out['games'] = array();
	    $guessadv = AdvService::getGuessInfo($appname,$version);
	    if($guessadv){
	    	$out['games'][] = $guessadv;
	    }
	    /*
		if($games){
			$_game = array();
			$_game['gid'] = $games['id'];
			$_game['title']= $games['shortgname'];
			$_game['downurl'] = $games['downurl'];
			$_game['img'] = self::joinImgUrl($games['ico']);
			$_game['language'] = $games['language'];
			$_game['score'] = $games['score'];
			$_game['tname'] = $games['typename'];
			$_game['commentcount'] = $games['commenttimes'];
			$_game['viewcount'] = $games['circlecount'];
			$_game['downcount'] = $games['downtimes'];
			$out['games'][] = $_game;
		}
		*/
		//猜你喜欢	
		$out['recommand'] = array();	
		$games = GameService::getGuessGames($game['type']);	
		$guess = array_slice($games,0,4);	
		foreach($guess as $row){
			$_game = array();
		    $_game['gid'] = $row['id'];
			$_game['title'] = $row['shortgname'];
			$_game['img'] = self::joinImgUrl($row['ico']);
			//$_game['score'] = $row['score'];
			//$_game['tname'] = isset($gametype[$row['type']]) ? $gametype[$row['type']] : '';
			//$_game['language'] = GameService::$languages[$row['language']];
			$_game['downurl'] = $row['downurl'];
			$out['recommand'][] = $_game;
		}		
        //下载按钮广告
		$adv = AdvService::getGameDownload($gid,$appname,$version);
		if($adv){
			$out = array_merge($out,$adv);
		}
		//重置圈子动态消息数
		if($uid){
		    CircleFeedService::resetFeedCount($gid, $uid);
		}
		
		
		return $out;
	}
	/**
	 * 获取游戏圈成员
	 */
	public static function getCircleMembers($gid,$page=1,$pagesize=8)
	{
		$result = RelationService::getCircleUserList($gid,$page,$pagesize);
		$members = array();
		foreach($result['users'] as $index=>$row){
			$members[$index]['userID'] = $row['uid'];
			$members[$index]['userName'] = $row['nickname'];
			$members[$index]['userAvatar'] = self::joinImgUrl($row['avatar']);
			$members[$index]['level'] = $row['level_name'];
		}
		return array('users'=>array_values($members),'totalCount'=>$result['total']);
	}
	
	/**
	 * 匹配游戏
	 */
	public static function matchingGame($gnames,$uid)
	{
		if(!is_array($gnames)){
			$gnames = array($gnames);
		}
		$gids = self::dbCmsSlave()->table('games')->whereIn('shortgname',$gnames)
		->where('isdel','=',0)
		->select('id','shortgname')
		->lists('id');
		if($gids){
			self::createMyGameCircle($uid,$gids);
			return true;
		}
		return false;
	}
	
	/**
	 * 创建我的游戏圈
	 */
	public static function createMyGameCircle($uid,$game_ids=array())
	{
		if(!$game_ids){
			return false;
		}
		//删除已添加的游戏
		self::dbClubMaster()->table('account_circle')->where('uid','=',$uid)->delete();
		$data = array();
		$feed_count = array();
		foreach($game_ids as $game_id){
			$data[] = array('uid'=>$uid,'game_id'=>$game_id);
			$feed_count[] = array('uid'=>$uid,'gid'=>$game_id,'total'=>0,'last_update_time'=>time());
		}
		$rows = self::dbClubMaster()->table('account_circle')->insert($data);
		self::dbClubMaster()->table('feed_gamecircle_count')->insert($feed_count);
		if($rows){
			return true;
		}
		return false;
	}
	
	/**
	 * 添加游戏到我的游戏圈
	 */
	public static function addMyGameCircle($uid,$game_ids=array())
	{	    
		if(!is_array($game_ids)){
			$game_ids = array($game_ids);
		}
		$ower_gids = self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)->lists('game_id');
		$game_ids = array_diff($game_ids,$ower_gids);
	    if(!$game_ids){
			return -1;
		}
		$data = array();
		$gids = array();
		$feed_count = array();
		foreach($game_ids as $game_id){
			$data[] = array('uid'=>$uid,'game_id'=>$game_id);
			$feed_count[] = array('uid'=>$uid,'gid'=>$game_id,'total'=>0,'last_update_time'=>time());
			$gids[] = $game_id;
		}
		$rows = self::dbClubMaster()->table('account_circle')->insert($data);
		self::dbClubMaster()->table('feed_gamecircle_count')->insert($feed_count);
		if($rows){
			UserFeedService::makeFeedJoinCircle($uid, $gids);
			return true;
		}
		return 0;
	}
	
	/**
	 * 删除游戏
	 */
	public static function removeGameFromMyGameCircle($uid,$game_id)
	{
		$rows = self::dbClubMaster()->table('account_circle')->where('uid','=',$uid)->where('game_id','=',$game_id)->delete();
		$rows && CircleFeedService::resetFeedCount($game_id, $uid);
		return $rows>0 ? true : false;
	}
	
	/**
	 * 置顶游戏
	 */
	public static function stickGameToGameCircle($uid,$game_id,$istop)
	{
		$max = self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)->max('sort');
		$data = array('sort'=>$max+1,'istop'=>$istop);
		$rows = self::dbClubMaster()->table('account_circle')->where('uid','=',$uid)->where('game_id','=',$game_id)->update($data);
		return $rows>0 ? true : false;
	}
	
	/**
	 * 我的游戏
	 */
	public static function getMyGame($uid,$page=1,$pagesize=100,$is_forum=0)
	{
		$game_ids = self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)
		//->forPage($page,$pagesize)
		->lists('game_id');
		if($is_forum == 1){
			$forum_gids = ForumService::getOpenForumGids();
			$game_ids = array_intersect($game_ids,$forum_gids);
		}
		if($game_ids){
		    $games = array_values(GameService::getGamesByIds($game_ids));		    
		}else{
			$games = array();
		}
		$total = count($game_ids);
		$gametype = GameService::getGameTypeOption();   
		
		$out = array();
	    foreach($games as $index=>$row){
			/*$out[$index]['gid'] = $row['id'];
			$out[$index]['img'] = GameService::joinImgUrl($row['ico']);
			$out[$index]['gname'] = $row['gname'];
			$out[$index]['free'] = $row['pricetype']==1?1:0;
			$out[$index]['limitfree'] = $row['pricetype']==2?1:0;
			$out[$index]['price'] = $row['price'];
			$out[$index]['desc'] = $row['shortcomt'];			
			$out[$index]['adddate'] = date('Y-m-d H:i:s',$row['addtime']);			
			$out[$index]['score'] = $row['score'];
			$out[$index]['type'] = isset($gametype[$row['type']]) ? $gametype[$row['type']] : '';
			$out[$index]['language'] = self::$languages[$row['language']];
			$out[$index]['status'] = 0;*/
			$out[$index]['gid'] = $row['id'];
			$out[$index]['title'] = $row['shortgname'];
			$out[$index]['img'] = self::joinImgUrl($row['ico']);
			$out[$index]['star'] = $row['score'];
			$out[$index]['type'] = $gametype[$row['type']];
			$out[$index]['language'] = GameService::$languages[$row['language']];
			$out[$index]['incycle'] = 1;
		}
		return array('games'=>$out,'total'=>$total);
	}
	/**
	 * 我的游戏圈
	 * @deprecated
	 */
	public static function _getMyGameCircle($uid,$append_gids=null)
	{
		$add_games = array(); 
		
		$my_game_ids = self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)->orderBy('istop','desc')->orderBy('sort','desc')->lists('istop','game_id');
		//待添加的游戏
	    if($append_gids){
	    	$append_gids = array_diff($append_gids,array_keys($my_game_ids));
	    	
	    	if($append_gids){
				self::addMyGameCircle($uid,$append_gids);				
	    	}
		}
		$game_ids = self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)->orderBy('istop','desc')->orderBy('sort','desc')->lists('istop','game_id');
		$total = count($game_ids);
		
		$gametype = GameService::getGameTypeOption();   
		$games = GameService::getGamesByIds(array_keys($game_ids));
		$out = array();
		$first_section = $second_section = $third_section = array();
		foreach($game_ids as $gid=>$istop){
		    if(!isset($games[$gid])) continue;
		    $data = array();		    
		    $feed = CircleFeedService::getLastFeed($gid);		    
			$data['gid'] = $games[$gid]['id'];
			$data['title'] = $games[$gid]['shortgname'];
			$data['img'] = self::joinImgUrl($games[$gid]['ico']);
			$data['type'] = $feed ? $feed['type'] : 0;//isset($gametype[$games[$gid]['type']]) ? $gametype[$games[$gid]['type']] : '';
			$data['badgnum'] = '0';
			$data['istop'] = $istop;
			$data['subtitle'] = isset($feed['feed']['title']) ? $feed['feed']['title'] : '没有新动态';
			$data['date'] = date('Y-m-d H:i:s',isset($feed['feed']['updatetime']) ? $feed['feed']['updatetime'] : $games[$gid]['addtime']);	
			if($istop){
				$first_section[] = $data;
			}elseif($feed){
				$second_section[] = $data;
			}else{
				$third_section[] = $data;
			}		
		}
	    $out = array_merge($first_section,$second_section,$third_section);
		return array('games'=>$out,'total'=>$total);
	}	
	
    public static function getMyGameCircle($uid,$append_gids=null)
	{
		$add_games = array(); 
		
		$my_game_ids = self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)->orderBy('istop','desc')->orderBy('id','desc')->lists('istop','game_id');
		//待添加的游戏
	    if($append_gids){
	    	$append_gids = array_diff($append_gids,array_keys($my_game_ids));
	    	
	    	if($append_gids){
				self::addMyGameCircle($uid,$append_gids);				
	    	}
		}
		$game_ids = self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)->orderBy('istop','desc')->orderBy('id','desc')->lists('istop','game_id');
		
		$total = count($game_ids);
		
		$gametype = GameService::getGameTypeOption();   
		$games = GameService::getGamesByIds(array_keys($game_ids));
		$out = array();
		$first_section = $second_section = $third_section = array();
		foreach($game_ids as $gid=>$istop){
		    if(!isset($games[$gid])) continue;
		    $data = array();		    
		    //$feed = CircleFeedService::getLastFeed($gid);		    
			$data['gid'] = $games[$gid]['id'];
			$data['title'] = $games[$gid]['shortgname'];
			$data['img'] = self::joinImgUrl($games[$gid]['ico']);
			$data['language'] = isset(GameService::$languages[$games[$gid]['language']])? GameService::$languages[$games[$gid]['language']] : '其他';
			$data['star'] = $games[$gid]['score'];
			//$data['type'] = $feed ? $feed['type'] : 0;//isset($gametype[$games[$gid]['type']]) ? $gametype[$games[$gid]['type']] : '';
			$feedcount = CircleFeedService::getFeedCount($gid,$uid);
			$data['badgnum'] = $feedcount>99 ? 99 : $feedcount;
			$data['istop'] = $istop;
			//$data['subtitle'] = isset($feed['feed']['title']) ? $feed['feed']['title'] : '没有新动态';
			//$data['date'] = date('Y-m-d H:i:s',isset($feed['feed']['updatetime']) ? $feed['feed']['updatetime'] : $games[$gid]['addtime']);
			$data['personcount'] = self::getGameCircleUserCount($gid);//圈友数
			$data['commentcount'] = self::getGameCircleInfoCount($gid);
			$uids = self::dbClubSlave()->table('account_circle')->where('game_id','=',$gid)->forPage(1,5)->orderBy('id','desc')->lists('uid');
			$data['userBaseInfos'] = array();
			if($uids){
				$users = UserService::getBatchUserInfo($uids);
				foreach($uids as $_uid){
					if(!isset($users[$_uid])) continue;
					$row = $users[$_uid];
					$data['userBaseInfos'][] = array('userID'=>$row['uid'],'userAvator'=>self::joinImgUrl($row['avatar'],60));
				}
			}
				
			if($istop){
				$first_section[] = $data;
			}elseif($feedcount>0){
				$second_section[] = $data;
			}else{
				$third_section[] = $data;
			}		
		}
	    $out = array_merge($first_section,$second_section,$third_section);
		return array('games'=>$out,'total'=>$total);
	}
	
	
	/**
	 * 我的游戏
	 */
	public static function getMyGameIds($uid)
	{
		return self::dbClubSlave()->table('account_circle')->where('uid','=',$uid)->lists('game_id');
	}	
	
	public static function getGameCircleUserCount($game_id)
	{
		$base = 0;
		$base = self::dbCmsSlave()->table('games')->where('id','=',$game_id)->select(array('downtimes'))->pluck('downtimes');
		$base = ceil($base*0.75);
		if($game_id==12776) $base = $base + 5000000;
		return $base + self::dbClubSlave()->table('account_circle')->where('game_id','=',$game_id)->count();
	}
	
	/**
	 * 信息数=评论+发帖+回复数+资料大全的文章数+回复数
	 */
	public static function getGameCircleInfoCount($game_id)
	{
		$cmt = self::dbClubSlave()->table('comment')->where('target_table','m_games')->where('target_id','=',$game_id)->count();
		$topic = self::dbClubSlave()->table('forum_topic')->where('gid','=',$game_id)->where('displayorder','=',0)->count();
		$news = self::dbCmsSlave()->table('news')->where('gid','=',$game_id)->count();
		$guide = self::dbCmsSlave()->table('gonglue')->where('gid','=',$game_id)->count();
		$opinion = self::dbCmsSlave()->table('feedback')->where('gid','=',$game_id)->count();
		//$video = self::dbCmsSlave()->table('gonglue')->where('gid','=',$game_id)->count();
		$reply = (int)self::dbClubSlave()->table('forum')->where('gid','=',$game_id)->pluck('posts');
		return $cmt + $topic + $news + $guide + $opinion + $reply;
	}
}
