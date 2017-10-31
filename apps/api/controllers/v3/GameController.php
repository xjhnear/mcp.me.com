<?php
use Yxd\Modules\Core\CacheService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Yxd\Services\Cms\AdvService;
use Yxd\Services\Cms\GameService;
use Yxd\Services\Cms\GameCircleService;
/**
 * 游戏
 */
class GameController extends BaseController
{
	/**
	 * 经典必玩
	 */
	public function mustplay()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$section = 'commend::mustplay';
		$cachekey = 'commend::mustplay::' . $page . ':' . $pagesize;
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
			$result = GameService::getMustPlay($page,$pagesize);
			CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$result);
		}
		return $this->success(array('result'=>$result['games'],'totalCount'=>$result['total']));
	}
	
	/**
	 * 特色专题
	 */
	public function collect()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$section = 'collect';
	    $cachekey = 'game::collect:' . $page . ':' . $pagesize;
		if(CLOSE_CACHE===false &&CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
			$result = GameService::getGameCollect($page,$pagesize);
			CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$result);
		}
		return $this->success(array('result'=>$result['collect'],'totalCount'=>$result['total']));
	}
	
	/**
	 * 特色专题详情
	 */
	public function collect_detail()
	{
		$tid = Input::get('tid');
		if(!$tid){
			return $this->fail(1121,'参数错误');
		}
		$cachekey = 'collect::detail::' . $tid;
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$result = CacheService::get($cachekey);
		}else{
			$result = GameService::getGameCollectDetail($tid);
			CLOSE_CACHE===false && CacheService::forever($cachekey,$result);
		}
		
		return $this->success(array('result'=>$result));
	}
	
	/**
	 * 新游预告
	 */
	public function newgame()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
				
		$section = 'commend::newgame';
	    $cachekey = 'commend::newgame::' . $page . '::' . $pagesize;
	    if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
			$result = GameService::getNewGame($page,$pagesize);
			CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$result);
		}
	    $out = array();
		foreach($result['games'] as $index=>$row){
			$out[$index]['agnid'] = $row['id'];
			$out[$index]['title'] = $row['title'] ? : $row['gname'];
			$out[$index]['adddate'] = date('Y-m-d H:i:s',$row['addtime']);
			$out[$index]['commentcount'] = $row['commenttimes'];			
			$out[$index]['pictures'][]['pic'] = GameService::joinImgUrl($row['litpic']?:$row['pic']);
			if(trim($row['litpic2']) && trim($row['litpic3'])){
				$out[$index]['pictures'][]['pic'] = GameService::joinImgUrl($row['litpic2']);
				$out[$index]['pictures'][]['pic'] = GameService::joinImgUrl($row['litpic3']);
			}
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	/**
	 * 最新更新
	 */
	public function lastupdate()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		
		$section = 'game::lastupdate';
		$cachekey = 'game::lastupdate::' . $page;
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
		    $result = GameService::getLastUpdate($page,$pagesize);
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$result);
		}
		return $this->success(array('result'=>$result['games'],'totalCount'=>$result['total']));
	}
	/**
	 * 热门游戏列表
	 */
	public function hotgame()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$uid = Input::get('uid',0);
		$result = GameService::getHotGame($page,$pagesize,$uid);
		return $this->success(array('result'=>$result['games'],'totalCount'=>$result['total']));
	}
	
	/**
	 * 测试表
	 */
	public function test_table()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$type = Input::get('type',0);
		
		$section = 'commend::testtable';
		$cachekey = 'commend::testtable::type::' . $type . '::list::'.$page;
		if(CLOSE_CACHE===false && CacheService::section($section)->has($cachekey)){
			$result = CacheService::section($section)->get($cachekey);
		}else{
		    $result = GameService::getTestTable($type,$page,$pagesize);
		    CLOSE_CACHE===false && CacheService::section($section)->forever($cachekey,$result);
		}
		
		$out = array();
	    $gametype = GameService::getGameTypeOption();
        $gids = array();
        foreach($result['games'] as $one){
        	$gids[] = $one['gid'];
        }
        $tags = GameService::getGameTagsByGameId($gids);
		foreach($result['games'] as $index=>$row){
			$game = array();
			$game['gid'] = $row['gid'];
			$game['title'] = $row['title'];
			$game['istop'] = $row['istop'];
			$game['state'] = $row['state'];
			$game['isfirst'] = $row['isfirst'];
			$game['gametype'] = isset($gametype[$row['type']]) ? $gametype[$row['type']] : '';
			$game['adddate'] = date('Y-m-d',$row['addtime']);
			$game['pic'] = GameService::joinImgUrl($row['ico']);
			$game['tips'] = isset($tags[$row['gid']]) ? implode(',',$tags[$row['gid']]) : '';
			$game['openbeta'] = $row['openbeta']; 
			$out[] = $game;
		} 
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	/**
	 * 信息介绍
	 */
	public function info()
	{
		$gid = Input::get('gid');
		
	    if(!$gid){
			return $this->fail(1121,'参数错误');
		}
		
		$game = GameService::getGameInfo($gid);
	    if(!$game){
			return $this->fail(1121,'游戏不存在');
		}
		$gametype = GameService::getGameTypeOption();		
		$out = array();
		$out['gid'] = $game['id'];
		$out['language'] = $game['language'];
		$out['updatetime'] = date('Y-m-d H:i:s',$game['addtime']);
		$out['version'] = $game['version'];
		$out['device'] = $game['platform'];
		$out['developer'] = $game['company'];
		$out['appraise'] = $game['editorcomt'];
		
		return $this->success(array('result'=>$out));
	}
	
	/**
	 * 搜索结果
	 */
	public function getSearch()
	{
		$keyword = Input::get('keyword');
		$addtype = Input::get('isforum',0);
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',1000);
		$uid = Input::get('uid',0);
		$cachekey = 'game::search::' . $uid . '::' . md5($keyword) . '::' . $addtype;
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$json = CacheService::get($cachekey);
		}else{
		    $result = GameService::search($keyword,$page,$pagesize,$uid,$addtype);
			if($uid){
				$my_game_ids = GameCircleService::getMyGameIds($uid);  
			}
			$out = array();		
			$gametype = GameService::getGameTypeOption();
			foreach($result['games'] as $index=>$row){			
				$out[$index]['gid'] = $row['id'];
				$out[$index]['title'] = $row['shortgname'] ? $row['shortgname'] : $row['gname'];
				$out[$index]['img'] = GameService::joinImgUrl($row['ico']);
				
				$out[$index]['tname'] = isset($gametype[$row['type']]) ? $gametype[$row['type']] : '';
				$out[$index]['free'] = $row['pricetype']==1 ? "1" : "0";
				$out[$index]['limitfree'] = $row['pricetype']==2 ? "1" : "0";
				$out[$index]['size'] = $row['size'];
				$out[$index]['score'] = $row['score'];
				$out[$index]['oldprice'] = strval($row['oldprice']);
				$out[$index]['price'] = $row['price'];
				//$out[$index]['guide'] = strval(0);//$row['guide'];
				//$out[$index]['opinion'] = strval(0);//$row['opinion'];
				//$out[$index]['zone'] = $row['zonetype'];
				$out[$index]['downcount'] = $row['downtimes'];
				//$out[$index]['commentcount'] = strval(0);//$row['zonetype'];
				//$out[$index]['hot'] = $row['ishot'];
				//$out[$index]['week'] = $row['weekdown'];
				//$out[$index]['language'] = self::$languages[$row['language']?:0];
				$out[$index]['incycle'] = isset($my_game_ids) && in_array($row['id'],$my_game_ids) ? "1" :"0";				
			}
			$json = array('result'=>$out,'totalCount'=>$result['total']);
			CLOSE_CACHE===false && CacheService::put($cachekey,$json,3600*24);
		}
	    		
		return $this->success($json);
	}
	/**
	 * 搜索匹配
	 */
	public function searchtip()
	{
		$keyword = Input::get('tip');
		$isforum = Input::get('isforum',0);
		$cachekey = 'game::searchtip::' . md5($keyword);
		if(CLOSE_CACHE===false && CacheService::has($cachekey)){
			$json = CacheService::get($cachekey);
		}else{
			$result = GameService::searchTip($keyword,$isforum);
		    $out = array();
			$gametype = GameService::getGameTypeOption();
			foreach($result['games'] as $index=>$row){
				$out[$index]['gid'] = $row['id'];
				$out[$index]['title'] = $row['shortgname'] ? $row['shortgname'] : $row['gname'];
				$out[$index]['img'] = GameService::joinImgUrl($row['ico']);
			}
			$json = array('result'=>$out,'totalCount'=>$result['total']);
			CLOSE_CACHE===false && CacheService::put($cachekey,$json,3600*24);
		}
		return $this->success($json);
	}
	
	/**
	 * 游戏识别码
	 */
	public function schemesurl()
	{
		$result = GameService::schemesurl();
		return $this->success($result);
	}
	
	/**
	 * 猜你喜欢
	 */
	public function guess()
	{
		$gid = Input::get('gid');
		$_game = GameService::getGameInfo($gid);
		if(!$_game){
			return $this->fail('11211','游戏不存在');
		}
		$type = $_game['type'];
		
		$games = GameService::getGuessGames($type,100);
		$gametype = GameService::getGameTypeOption();		
		$out = array();
		foreach($games as $row){
			$game = array();
			$game['gid'] = $row['id'];
			$game['title'] = $row['shortgname'];
			$game['img'] = GameService::joinImgUrl($row['ico']);
			$game['score'] = $row['score'];
			$game['tname'] = isset($gametype[$row['type']]) ? $gametype[$row['type']] : '';
			$game['language'] = GameService::$languages[$row['language']];
			$out['games'][] = $game;			
		}
		return $this->success(array('result'=>$out));
	}
	
	/**
	 * 玩家推荐应用
	 */
	public function recommend()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$result = GameService::getPlayerRecommand($page,$pagesize);
		$out = array();
		foreach($result['games'] as $row){
			$game['title'] = $row['appname'];
			$game['img'] = GameService::joinImgUrl($row['ico']);
			$game['downurl'] = $row['downurl'];
			$game['desc'] = $row['summary'];
			$out[] = $game;
		}
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	/**
	 * 星座
	 */
	public function discovery()
	{
		$tags = Config::get('yxd.discovery_tags');
	    $tag_tmp = array_rand($tags, 7);		
	    $tags_result = array();
		foreach ($tag_tmp as $k => $v){
			$tags_result[$k]['tag'] = $tags[$v]['tag'];
			$tags_result[$k]['name'] = $tags[$v]['name'];
		}
		return $this->success(array('result'=>$tags_result));
	}

	public function tags()
	{
		$tag = Input::get('tag');
		$result = GameService::getGamesByTag($tag);
		$out = array();
		foreach($result as $row){
			$game = array();
			$game['gid'] = $row['id'];
			$game['title'] = $row['shortgname'];
			$game['img'] = GameService::joinImgUrl($row['ico']);
			$game['price'] = $row['price'];
			$game['score'] = $row['score'];
			
			$out[] = $game;
		}
		return $this->success(array('result'=>$out));
	}
	
	public function relation()
	{
		$gid = Input::get('gid');
		$result = GameService::getRelationGamesByID($gid);
		
		foreach($result as $row){
			$game = array();
			$game['gid'] = $row['id'];
			$game['title'] = $row['shortgname'];
			$game['img'] = GameService::joinImgUrl($row['ico']);
			$game['price'] = $row['price'];
			$game['score'] = $row['score'];
			
			$out[] = $game;
		}
		return $this->success(array('result'=>$out));
	}
	
	/**
	 * 远征队
	 */
	public function expedition()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',10);
		$version = $this->getComVersion();
		if($version=='3.1.0'){
			return $this->success(array('result'=>array('forumId'=>'2')));
		}
		$result = GameService::getExpeditionTearm($page,$pagesize);				
		$out = array();
		foreach($result['games'] as $row){
			$tmp['gid'] = $row['gid'];
			$tmp['gameico'] = GameService::joinImgUrl($row['ico']);
			$tmp['title'] = $row['title'];
			$tmp['img'] = GameService::joinImgUrl($row['litpic']);
			$out[] = $tmp;
		}
		
		return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	public function downloadMoney()
	{
		$game_id = Input::get('gid');
		$uid = Input::get('uid');
		if(!$game_id || !$uid){
			return ;
		}
		$result = GameService::doDownloadCredit($game_id, $uid);
		if($result===0){
			return $this->fail('11211','该游戏无奖励游币');
		}elseif($result === 1){
		    return $this->fail('11211','该游戏今日已经奖励过了');
		}elseif($result === 2){
		    return $this->fail('11211','今日下载奖励已经达到最大上限3次');
		}elseif(is_array($result)){
			$score = (int)$result['score'];
			$info = '';
			$score && $info = '下载游戏奖励' . $score . '游币';
			if($score){
			    //return $this->fail('11211',$info);
			    return $this->success(array('result'=>null));
			}else{
			    return $this->success(array('result'=>null));
			}
		}
	}
	
	/**
	 * 下载统计
	 */
	public function download()
	{
		$game_id = (int)Input::get('gid');
		$uid = Input::get('uid');
		GameService::download($game_id,$uid);
		return $this->success(array('result'=>null));
	}
}