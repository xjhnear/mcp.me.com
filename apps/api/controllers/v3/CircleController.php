<?php
use Yxd\Services\UserService;
use Yxd\Services\Cms\CommentService;
use Yxd\Services\ThreadService;
use Yxd\Services\CircleFeedService;
use Yxd\Services\Cms\GameCircleService;
use Illuminate\Support\Facades\Input;
use Yxd\Services\Cms\AdvService;
use Yxd\Services\Cms\GameService;
/**
 * 游戏
 */
class CircleController extends BaseController
{
	/**
	 * 游戏圈子主页
	 */
	public function home()
	{
		$gid = Input::get('gid');
		$uid = Input::get('uid');
		$appname = Input::get('appname','');
		$version = $this->getComVersion();
		if(empty($gid)) return $this->fail(1121,'游戏ID不能为空');
		$result = GameCircleService::getHomePage($gid,$uid,$appname,$version);
		return $this->success(array('result'=>$result));
	}
	/**
	 * 游戏圈类型
	 */
	public function types()
	{
		$uid = Input::get('uid',0);
		$result = GameService::getGameTypeList(true,$uid);
		return $this->success(array('result'=>$result));
	}
	
	/**
	 * 游戏圈游戏
	 */
	public function games()
	{
		$tid = Input::get('gtid');
		$uid = Input::get('uid',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$addtype = Input::get('addtype',0);//
		$is_forum = Input::get('isForum',0); 
		if($tid==-1){//我的游戏
			$result = GameCircleService::getMyGame($uid,$page,$pagesize,$is_forum);
		}elseif($tid==0){//热门游戏
			$result = AdvService::getGameCircleHotGame($uid,$addtype,$is_forum);
		}else{//分类游戏
		    $result = GameService::getGamesByType($tid,$page,$pagesize,$uid,$addtype,$is_forum);
		}
		return $this->success(array('result'=>$result['games'],'totalCount'=>$result['total']));
	}
	/**
	 * 匹配游戏
	 * 
	 */
	public function matching()
	{
		$gnames = Input::get('gnames');
		$uid = Input::get('uid');
		if(!$gnames || !$uid){
			return $this->fail(1121,'参数错误');
		}
		
		if(is_string($gnames) && strpos($gnames,'|')!==false){
			$gnames = explode('|',$gnames);
		}
		
		$result = GameCircleService::matchingGame($gnames,$uid);
		if($result){
		    return $this->success(array('result'=>null));
		}else{
			return $this->fail(1121,'没有匹配到游戏');
		}
	}
	
	//public function 
	
	/**
	 * 添加游戏
	 */
	public function addgame()
	{
		$game_ids = Input::get('gids');
		$uid = Input::get('uid');
		if(empty($game_ids)){
			return $this->fail(1121,'没有选择任何游戏');
		}else{
			$game_ids = explode('|',$game_ids);
		}
		if(!$uid){
			return $this->fail(1121,'用户未登录');
		}
		$success = GameCircleService::addMyGameCircle($uid,$game_ids);
		if($success===true){
			return $this->success(null);
		}elseif($success===-1){
			return $this->fail(1121,'添加游戏的游戏已经存在');
		}else{
			return $this->fail(1121,'添加游戏失败');
		}
	} 
	
	/**
	 * 从我的游戏圈中移除游戏
	 */
	public function removegame()
	{
		$uid = Input::get('uid');
		$game_id = Input::get('gid');
		$success = GameCircleService::removeGameFromMyGameCircle($uid, $game_id);
		if($success===true){
			return $this->success(null);
		}else{
			return $this->fail(1121,'删除游戏失败');
		}
	}
	
	/**
	 * 游戏置顶
	 */
	public function gametostick()
	{
	    $uid = Input::get('uid');
		$game_id = Input::get('gid');
		$istop = Input::get('istop');
		$success = GameCircleService::stickGameToGameCircle($uid, $game_id,$istop);
		if($success===true){
			return $this->success(null);
		}else{
			return $this->fail(1121,'置顶游戏失败');
		}
	}
	
	
	/**
	 * 我的游戏圈
	 */	
	public function mygamecircle()
	{
		$uid = Input::get('uid',0);
		$gids = Input::get('gids',null);
	    if(!empty($gids)){
			$gids = explode('|',$gids);
		}	
		
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);		
		$result = GameCircleService::getMyGameCircle($uid,$gids);
		//$pages = array_chunk($result['games'],$pagesize,false);
		//$out = isset($pages[$page-1])?$pages[$page-1]:array();
		$out = $result['games'];
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	/**
	 * 圈子动态
	 */
	public function feeds()
	{
		$uid = Input::get('uid',0);
		if(!$uid){
			return $this->fail(11211,'用户不存在');
		}
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$result = CircleFeedService::getDataFeed($uid,$page,$pagesize);
		//print_r($result);exit;
		$out = array();
		foreach($result['feeds'] as $row){
			try{
			    $data = unserialize($row['data']);
			}catch(Exception $e){
				continue;
			}
			//print_r($data);exit;
			switch($data['type']){
				case 'topic':
					$feed = $this->feed_topic($uid,$data);
					$feed && $out[] = $feed;
					$feed==false &&  $result['total'] = $result['total']-1;
					break;
				case 'comment':
					$feed = $this->feed_comment($uid,$data);
					$feed && $out[] = $feed; 
					$feed==false &&  $result['total'] = $result['total']-1;
					break;
				default:
					break;
			}		
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	//发帖
	protected function feed_topic($uid,$row)
	{
		$del = ThreadService::isDeleted($row['topic']['tid']);
	    if($del == true) {
			//CircleFeedService::deleteFeed($uid, $row);
			//return false;
		}
		$feed = array();
		$feed['kind'] = 0;//分类：0-发帖1-评论
		$feed['tid'] = $row['topic']['tid'];//
		$feed['status'] = 0;//分类：0-发帖1-评论
		$feed['headimg'] = CircleFeedService::joinImgUrl($row['topic']['author']['avatar']);//头像
		$feed['nick'] = $row['topic']['author']['nickname'];//用户昵称
		$feed['level'] = $row['topic']['author']['level_name'];//用户等级
		$feed['commentNum'] = $row['topic']['replies'];//回复数
		$feed['img'] = CircleFeedService::joinImgUrl($row['game']['ico']);//游戏Icon
		$feed['title'] = $row['topic']['subject'];//
		$feed['date'] = date('Y-m-d H:i:s',$row['topic']['dateline']);//
		
		return $feed;
		
	}
	//评论
	protected function feed_comment($uid,$row)
	{
		$del = CommentService::isDeleted($row['comment']['id']);
		if($del == true) {
			//CircleFeedService::deleteFeed($uid, $row);
			//return false;
		}
		$feed = array();
		$feed['kind'] = 1;//分类：0-发帖1-评论
		$feed['gid'] = $row['game']['id'];
		$feed['status'] = 1;//分类：0-发帖1-评论
		$cmt = json_decode($row['comment']['content'],true);
		$feed['comment'] = $cmt[0]['text'];
		$feed['commentImg'] = CircleFeedService::joinImgUrl($cmt[0]['img']);
		$width = $height = 0;
		$file = storage_path() . $cmt[0]['img'];
		if($cmt[0]['img'] && file_exists($file)){
			list($width,$height,$type,$attr) = getimagesize($file);
		}
		$feed['imgWidth'] = $width;
		$feed['imgHeight'] = $height;		
		$user = UserService::getUserInfo($row['comment']['uid']);
		$feed['headimg'] = CircleFeedService::joinImgUrl($user['avatar']);
		$feed['nick'] = $user['nickname'];
		$feed['level'] = $user['level_name'];
		$feed['commentNum'] = $row['game']['commenttimes'];
		$feed['img'] = CircleFeedService::joinImgUrl($row['game']['ico']);
		$feed['gname'] = $row['game']['shortgname'];
		$feed['type'] = $row['game']['typename'];
		$feed['language'] = $row['game']['language'];
		$feed['star'] = $row['game']['score'];
		$feed['date'] = date('Y-m-d H:i:s',$row['comment']['addtime']);
		
		return $feed;
	}
}