<?php
namespace Yxd\Services\Cms;

use Yxd\Modules\Core\CacheService;
use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;
use Illuminate\Support\Facades\Config;
use Yxd\Services\LikeService;
use Yxd\Services\Models\XyxGame;

class XgameService extends Service
{
	/**
	 * 小游戏列表
	 */
	public static function getList($page,$pagesize,$type,$keyword,$t='gname')
	{
		$section = 'xgame::lastupdate';
		$cachekey = 'xgame::lastupdate::list'.$type.$page.$pagesize;
		$games = array();
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$games = CacheService::section($section)->get($cachekey);
		}else{
			$where = array();
			/** (x[最新]|y[最热]|t[编辑推荐]| 编号[类型]|0[全部] )*/
			$dbSlave = XyxGame::db();
			if($keyword!=''){
				if($t=='gname'){
					$dbSlave =  $dbSlave->where('gamename','like',"%{$keyword}%");
				}elseif($t=='id'){
					$dbSlave =  $dbSlave->where('id','=',$keyword);
				}
				$dbSlave = $dbSlave ->orderBy('senddate','desc');
				$typetitle = $keyword;
			}else{
				switch ($type){
					case 'x':
						$typetitle = '最新';
						$dbSlave =  $dbSlave  ->orderBy('senddate','desc');
						break;
					case 'y':
						$typetitle = '最热';
						$dbSlave =  $dbSlave ->orderBy('hotsort','desc');
						break;
					case 't':
						$typetitle = '编辑推荐';
						$dbSlave =  $dbSlave ->where('editorrecommend','=',1) ->orderBy('editorsort','desc');
						break;
					case 0:
						$typetitle = '全部';
						$dbSlave =  $dbSlave ->orderBy('senddate','desc');
						break;
					default:
						$typetitle = self::getType($type);
						$typetitle = (is_array($typetitle))?$typetitle['title']:'';
						$dbSlave =  $dbSlave ->where('tid','=',$type) ->orderBy('senddate','desc');
				}
			}
			$total = $dbSlave ->count();
			$games =  $dbSlave ->forPage($page,$pagesize) ->get();
			
			CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$games);
		}
		
		$out = array();
		foreach($games as $index=>$row){
			$game = array();
			$game['id'] = $row['id'];
			$game['gamename'] = $row['gamename'];
			$game['litpic'] = str_replace('/mnt/data/www',Config::get('ueditor.imageUrlPrefix'),$row['litpic']); 
			if(!(strpos($game['litpic'],'http')===false)) {
				
			}else{
				$game['litpic'] = GameService::joinImgUrl($row['litpic']);
			}
			$game['tid'] = $row['tid'];
			$typename = self::getType($row['tid']);
			$game['typename'] = (is_array($typename))?$typename['title']:'';
			$phrase = trim($row['phrase']);
			$introduced = trim($row['introduced']);
			$game['phrase'] = !empty($phrase) ? $phrase : $introduced;
			$game['gameaddress'] = $row['gameaddress'];
			$out[] = $game;
			
		}

		return array('games'=>$out,'total'=>$total,'typetitle'=>$typetitle);
	}
	
	
	/**
	 * 小游戏banner列表
	 */
	public static function getBanner($ids='',$sortAsc = true)
	{
		$section = 'xgame::lastupdate';
		$cachekey = 'xgame::lastupdate::banner';
		if($ids!=''){
			$cachekey = $cachekey . json_encode($ids);
		}
		
		$games = array();
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$games = CacheService::section($section)->get($cachekey);
		}else{
			$dbSlave = self::dbCmsMaster() -> table('xyx_game_infopic');
			if($ids!=''){
				$dbSlave = $dbSlave -> whereIn('id',$ids);
			}
			$total = $dbSlave -> count();
			if($sortAsc){
				$sort = 'asc';
			}else{
				$sort = 'desc';
			}
			$games =  $dbSlave -> orderBy('sort',$sort) -> get();
			foreach($games as &$v){
				$v['litpic'] = str_replace('/mnt/data/www',Config::get('ueditor.imageUrlPrefix'),$v['litpic']);
				if((strpos($v['litpic'],'http')===false)){
					$v['litpic'] = GameService::joinImgUrl($v['litpic']);
				}
			}
			CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$games);
		}
		return array('result'=>$games,'total'=>$total);
	}
	
	/** 获取游戏类型*/
	public static function getType($tid=0)
	{
		$section = 'xgame::lastupdate';
		$cachekey = 'xgame::lastupdate::type'.$tid;
		$games = array();
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$data = CacheService::section($section)->get($cachekey);
		}else{
			$dbSlave = self::dbCmsMaster()->table('xyx_type');
			if(!$tid){
				$data =  $dbSlave  ->orderBy('sort','desc') ->lists('title','id');
			}else{
				$data =  $dbSlave ->where('id','=',$tid) ->first();
			}
		}
		return $data;
	}
	/** 获取游戏关联图片 */
	public static function getPic($gid)
	{
		$data = self::dbCmsMaster()->table('xyx_pic') ->where('gid','=',$gid) ->get();
		
		foreach($data as &$v){
			$v['url'] = str_replace('/mnt/data/www',Config::get('ueditor.imageUrlPrefix'),$v['url']);
			if((strpos($v['url'],'http')===false)){
				$v['url'] = GameService::joinImgUrl($v['url']);
			}
		} 
		return $data;
	}
	/** 获取游戏详情*/
	public static function getArticle($gid,$page=1,$pagesize=10,$uid=0)
	{
		$data = XyxGame::db() ->where('id','=',$gid) ->first();
		$out = array();
		$typename = self::getType($data['tid']);
		$data['typename'] = (is_array($typename))?$typename['title']:'';
		$data['pic'] = self::getPic($gid);
		$data['senddate'] = date('Y-m-d H:i:s',$data['senddate']);
		$data['litpic'] = str_replace('/mnt/data/www',Config::get('ueditor.imageUrlPrefix'),$data['litpic']); 
		if(!(strpos($data['litpic'],'http')===false)) {
			
		}else{
			$data['litpic'] = GameService::joinImgUrl($data['litpic']);
		}
		
		//喜欢
		$likes = LikeService::getLikeList($gid,LikeService::XGAME,0,1,5);
		
		//V3.1 增加是否已赞字段
		$data['likes']['islike'] = LikeService::isLike($gid,LikeService::XGAME,$uid)==true ? 1 : 0;
		$data['likes']['totalCount'] = $likes['total'];
		$data['likes']['likeInfos'] = array();
		foreach($likes['likes'] as $index=>$row){
			$like = array();
			$like['userBase']['userID'] = $row['uid'];
			$like['userBase']['userName'] = $row['nickname'];
			$like['userBase']['userAvator'] = self::joinImgUrl($row['avatar']);
			$like['userBase']['userLevel'] = $row['level_name'];
			$like['emotionType'] = 1;
			$data['likes']['likeInfos'][] = $like;
		}
		
		//评论
		$data['comments'] = array();
		$data['comments']['isQuestion'] = 0;
			
		$data['comments']['isFinish'] = 0;
		$data['comments']['commentInfos'] = array();
		$comments = CommentService::getAppOfList($gid,'m_xyx_game',$page,$pagesize);
		
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
		
			$data['comments']['commentInfos'][] = $comment;
			
		}
		$out['totalCount'] = count($comments['result']);
		
		$out['result'] = $data;
		return $out;
	}
	
	//点击开始加热度
	public static function doHot($gid)
	{
		$id = XyxGame::db()->where('id','=',$gid)->increment('hotsort');
		return $id ? true : false;
	}
	/**
	 * 判断今天该款游戏是否记录
	 * @param array $data
	 * @return boolean
	 */
	public static function getXgameCount($data){
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$result = self::dbCmsMaster()->table('xyx_count')->where('gid','=',$data['gid'])->where('ip','=',$data['ip'])->whereBetween('addtime', array($beginToday, $endToday))->first();
		return $result ? false : true;
	}
	
	//统计保存
	public static function doSaveCount($data)
	{
		$result = self::dbCmsMaster()->table('xyx_count')->insertGetId($data);
		/* $result = self::getXgameCount($data);
		if($result){
			$result = self::dbCmsMaster()->table('xyx_count')->insertGetId($data);
		} */
		return $result ? true : false ;
	}
	
	//保存数据
	protected static function setInfos($v,&$data){
		if(isset($data[$v['gamename']])){
			$data[$v['gamename']]['c'] = intval($data[$v['gamename']]['c']) + $v['c'];
		}else{
			$data[$v['gamename']] = $v;
		}
	}
	
	//解析web详情页地址
	protected static function parseWebDetail($v,&$detail){
		//判断是否已经拿到标题
		if(empty($v['gamename'])){
			$gid = str_replace(array('/main/details_', '.html'), '', $v['url']);
			$gameData = XyxGame::db() ->where('id','=',$gid) ->first();
			if(empty($gameData['gamename'])) return;
			$v['gamename'] = $gameData['gamename'];
		}
		$v['gamename'] = 'web站小游戏-' .$v['gamename']. '-游戏详情页';
		//保存详情页数据
		self::setInfos($v, $detail);
	}
	
	//解析H5详情页地址
	protected static function parseH5Detail($v,&$detail){
		//h5 处理详情页 地址
		$queryString = explode('?', $v['url']);
		parse_str($queryString[1]);
		//获取游戏的名称
        if(empty($gid) || $gid == 0 ) return;
		$gameData = XyxGame::db() ->where('id','=',$gid) ->first();
		if(empty($gameData['gamename'])) return;
		$v['gamename'] = '手机H5站小游戏-'.$gameData['gamename'].'-详情页';
		//保存详情页数据
		self::setInfos($v, $detail);
	}
	

	//解析小游戏开始地址
	protected static function parseStart($v,&$detail){
		//h5 处理详情页 地址
		$queryString = explode('?', $v['url']);
		parse_str($queryString[1]);
		//获取游戏的名称
        if(empty($ID) || $ID == 0) return;
		$gameData = XyxGame::db() ->where('id','=',$ID) ->first();
		//找到该款游戏 跳出循环继续下一次循环
		//if(empty($gameData['gamename'])) continue;
		if($v['platform'] =='h5'){
			$v['gamename'] = '手机H5站小游戏-'.$gameData['gamename'].'-开始页';
		}else{
			$v['gamename'] = 'WEB站小游戏-'.$gameData['gamename'].'-开始页';
		}
		//保存详情页数据
		self::setInfos($v, $detail);
	}
	
	//解析web下一页地址
	protected static function parseWebNextList($v,&$nextList){
		$tid = explode('_', $v['url']);
		if($tid[1]){
			$ttitle = self::getType($tid[1]);
			$v['gamename'] = 'web'. $ttitle['title'] .'列表下一页';
		}
		self::setInfos($v, $nextList);
	}
	
	//解析H5下一页地址
	protected static function parseH5NextList($v,&$nextList){
		//解析出列表类型  H5 列表页
		$queryString = explode('?', $v['url']);
		//parse_str("a=1&b=2");echo $b;exit;
		parse_str($queryString[1]);
        if(empty($type) || $type == 0) return;
		switch ($type){
			case 'x':
				$ttitle['title'] = '最新';
			case 'y':
				$ttitle['title'] = '最热';
				break;
			case '0':
				$ttitle['title'] = '全部';
				break;
			default:
				$ttitle = self::getType($type);
		}
		$v['gamename'] = '手机H5站小游戏'. $ttitle['title'] .'游戏列表下一页';
		self::setInfos($v, $nextList);
	}
	
	//获取映射表里面的对应项的统计
	public static function getCountMapping($data = array()){
		$prefix = Config::get('database.connections.cms.prefix', 'm_');
		$tb = self::dbCmsMaster()->table('xyx_count');
		$tb = $tb->join('xyx_count_mapping', 'xyx_count.url', '=', 'xyx_count_mapping.url');
		empty($data['type']) ? : $tb->where('xyx_count.platform','=',$data['type']);
		if(isset($data['flag']) && $data['flag'] == 'detail' && !empty($data['gid'])){
			$tb->where(function($query) use ($prefix,$data){
				$query->where('xyx_count.gid', '=', $data['gid'])
				->orWhere('xyx_count.url', 'like', '%gid='.$data['gid']);
			});
		}
		empty($data['begin']) ? : $tb->where('xyx_count.addtime','>',$data['begin']);
		empty($data['after']) ? : $tb->where('xyx_count.addtime','<',$data['after']);
		$out['totalSum'] = $tb->count();
		$tb = $tb->select(DB::raw('count(*) as c , '.$prefix.'xyx_count.gid , '.$prefix.'xyx_count_mapping.title as gamename , '.$prefix.'xyx_count.url'))->groupBy('xyx_count.url');
		if(isset($data['flag']) && $data['flag'] == 'mapping'){
			$out['total'] = count($tb->get());
			$out['result'] = $tb->forPage($data['page'],$data['pagesize'])->orderBy('c','desc')->get();
		}else{
			$out['result'] = $tb->orderBy('c','desc')->get();
			$out['total'] = count($out['result']);
		}
		
		return $out;
	}

	//获取映射表里面不存在的对应项的统计
	public static function getCountNotMapping($data = array()){
		$prefix = Config::get('database.connections.cms.prefix', 'm_');
		$tbmap = self::dbCmsMaster()->table('xyx_count_mapping')->get();
		$keyUrl = $nextList = $detail = $out = array();
		//key =》 url
		foreach ($tbmap as $k=>$v){
			$keyUrl[$k] = $v['url'];
		}
	
		$tb = self::dbCmsMaster()->table('xyx_count');
		$tb = $tb->leftJoin('xyx_game', 'xyx_count.gid', '=', 'xyx_game.id');
		empty($data['type']) ? : $tb->where('xyx_count.platform','=',$data['type']);
		if(isset($data['flag']) && $data['flag'] == 'detail' && !empty($data['gid'])){
			$tb->where(function($query) use ($prefix,$data){
				$query->where('xyx_count.gid', '=', $data['gid'])
				->orWhere('xyx_count.url', 'like', '%gid='.$data['gid']);
			});
		}
		empty($data['begin']) ? : $tb->where('xyx_count.addtime','>',$data['begin']);
		empty($data['after']) ? : $tb->where('xyx_count.addtime','<',$data['after']);
		$tb->whereNotIn('xyx_count.url', $keyUrl);
		//$out['totalSum'] = $tb->count();
		$tb = $tb->select(DB::raw('count(*) as c , '.$prefix.'xyx_count.gid , '.$prefix.'xyx_game.gamename , '.$prefix.'xyx_count.url , '.$prefix.'xyx_count.platform'))->groupBy('xyx_count.url');
		$result = $tb->orderBy('c','desc')->get();
		
		foreach ($result as $v){
			if(strpos($v['url'] , 'details_') !== false){
				//web详情页
				self::parseWebDetail($v, $detail);
			}else if(strpos($v['url'] , '/miniGameDetail.action') !== false){
				//H5详情页
				self::parseH5Detail($v, $detail);
			}else if(strpos($v['url'] , 'list_') !== false){
				//web下一页
				self::parseWebNextList($v, $nextList);
			}else if(strpos($v['url'] , '/miniGameList.action') !== false){
				//H5下一页
				self::parseH5NextList($v, $nextList);
			}else if(strpos($v['url'] , '/main/comment/start.php') !== false){
				//开始页
				self::parseStart($v, $detail);
			}
		}
		
		//echo $out['totalSum'];
		$sumArrList = $sumArrDetail = array();
		foreach ($nextList as $v){
			$sumArrList[] = $v['c'];
		}
		foreach ($detail as $v){
			$sumArrDetail[] = $v['c'];
		}
		
		//判断输出内容
		if(isset($data['flag']) && $data['flag'] == 'nextList'){
			$out['totalSum'] = array_sum($sumArrList);
			$out['result'] = $nextList;
		}elseif(isset($data['flag']) && $data['flag'] == 'detail'){
			$out['totalSum'] = array_sum($sumArrDetail);
			$out['result'] = $detail;
		}else{
			$out['totalSum'] = array_sum($sumArrList) + array_sum($sumArrDetail);
			$out['result'] = array_merge($nextList,$detail);
		}
		$out['total'] = count($out['result']);
		if(isset($data['flag']) && ($data['flag'] == 'nextList' || $data['flag'] == 'detail') && $out['total'] > $data['pagesize']){
			$start = ($data['page']-1) * $data['pagesize'];
			$out['result'] = array_slice($out['result'],$start,$data['pagesize'],true);
		}
		return $out;
	}
	
	/**
	 * 获取统计 
	 * flag : nextList | detail | mapping | 
	 * @param array $data
	 * @return multitype:
	 */
	public static function getXgameCountList($data = array()){
		if(isset($data['flag']) && ($data['flag'] == 'nextList' || $data['flag'] == 'detail')){
			$out = self::getCountNotMapping($data);
		}elseif(isset($data['flag']) && $data['flag'] == 'mapping'){
			$out = self::getCountMapping($data);
		}else{
			$outMapping = self::getCountMapping($data);
			$outNotMapping = self::getCountNotMapping($data);
			$out['totalSum'] = $outMapping['totalSum'] + $outNotMapping['totalSum']; 
			$out['result'] = array_merge($outMapping['result'],$outNotMapping['result']);
			$out['total'] = count($out['result']);
			if($out['total'] > $data['pagesize']){
				$start = ($data['page']-1) * $data['pagesize'];
				$out['result'] = array_slice($out['result'],$start,$data['pagesize'],true);
			}
		}
		return $out;
	}
	
}