<?php
namespace Yxd\Services\Cms;

use Yxd\Modules\Core\CacheService;
use Yxd\Services\ShoppingService;
use Yxd\Services\ThreadService;
use Yxd\Modules\Activity\GiftbagService;
use Illuminate\Support\Facades\DB;
use Yxd\Services\Service;

class ShareService extends Service
{	
	//protected static $URL = 'http://m.youxiduo.com/';
	protected static $URL = 'http://h5.youxiduo.com/youxiduo_h5/share?';
	
	/**
	 * 分享游戏
	 */
	public static function shareGame($game_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		
		$game = GameService::getGameInfo($game_id);
		if(!$game) return false;
		$result  = array(
            'title' => $game['shortgname'],
            'pic'   => self::joinImgUrl($game['ico']),
            'weibo' => str_replace('{title}', $game['shortgname'], $tpl['weibo']),
            'weixin' => str_replace('{title}', $game['shortgname'], $tpl['weixin']),
            'url'  => $ishtml5 ? self::getShortURL(self::$URL . 'action=gameCircle&gid='.$game_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
	/**
	 * 分享视频
	 */
	public static function shareVideo($video_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$video = self::dbCmsSlave()->table('videos')->select('vname','litpic', 'writer','gid')->where('id','=',$video_id)->first();
		if(!$video) return false;
		$game = array();
		if($video['gid']){
			$game = GameService::getGameInfo($video['gid']);
		}
		//
		if(empty($game)){
			$v = self::dbCmsSlave()->table('videos_games')->where('vid','=',$video_id)->first();
			$game = GameService::getGameInfo($v['gid']);
		}
		$result  = array(
            'title' => $video['vname'],
            'pic'   => self::joinImgUrl($video['litpic']),
            'weibo' => str_replace(array('{title}','{editor}','{gtitle}'), array($video['vname'],$video['writer'],$game['shortgname']), $tpl['weibo']),
            'weixin' => str_replace(array('{title}','{editor}','{gtitle}'), array($video['vname'],$video['writer'],$game['shortgname']), $tpl['weixin']),
            'url'  => $ishtml5 ? self::getShortURL(self::$URL  . 'action=videoDetail&vid=' . $video_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
    /**
	 * 分享游戏视频
	 */
	public static function shareGameVideo($video_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$video = self::dbCmsSlave()->table('games_video')->select('gid','ico')->where('id','=',$video_id)->first();
		if(!$video) return false;
		$game = GameService::getGameInfo($video['gid']);
		
		$result  = array(
            'title' => $game['shortgname'],
            'pic'   => self::joinImgUrl($game['ico']),
            'weibo' => str_replace('{title}', $game['shortgname'], $tpl['weibo']),
            'weixin' => str_replace('{title}', $game['shortgname'], $tpl['weixin']),
            'url'  => $ishtml5 ? self::getShortURL(self::$URL . 'game/video/{$gvid}/'.$video['gid']) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
    /**
	 * 分享特色专题
	 */
	public static function shareSpecial($zt_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$topic = self::dbCmsSlave()->table('zt')->select('ztitle' , 'writer', 'litpic')->where('id','=',$zt_id)->first();
		if(!$topic) return false;
		$result  = array(
            'title' => $topic['ztitle'],
            'pic'   =>  self::joinImgUrl($topic['litpic']),
            'weibo' => str_replace('{title}', $topic['ztitle'], $tpl['weibo']),
            'weixin' => str_replace('{title}', $topic['ztitle'], $tpl['weixin']),
            'url'  => $ishtml5 ? self::getShortURL(self::$URL . 'action=topicDetail&tid='.$zt_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
    /**
	 * 分享新游预告
	 */
	public static function shareNewGame($game_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		
		$game = self::dbCmsSlave()->table('game_notice')->select('title','gname','pic')->where('id','=',$game_id)->first();
		if(!$game) return false;
		$result  = array(
            'title'  => $game['gname'],
            'pic'    => self::joinImgUrl($game['pic']),
            'weibo'  => str_replace('{title}' , $game['gname'], $tpl['weibo']),
            'weixin' => str_replace('{title}', $game['gname'], $tpl['weixin']),
            'url'    => $ishtml5 ? self::getShortURL(self::$URL . 'action=articleDetail&typeID=4&aid='.$game_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
    /**
	 * 分享攻略
	 */
	public static function shareGuide($guide_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$guide = self::dbCmsSlave()->table('gonglue')->select('gtitle','writer','gid', 'pid')->where('id','=',$guide_id)->first();
		if(!$guide) return null;
		$category = self::dbCmsSlave()->table('gonglue')->select('gtitle')->where('id','=',$guide['pid'])->first();
	    if ($category) {
        	$tpl['weibo'] = str_replace('{gcategory}', $category['gtitle'], $tpl['weibo']);
        	$tpl['weixin'] = str_replace('{gcategory}', $category['gtitle'], $tpl['weixin']);
        } else {
        	$tpl['weibo'] = str_replace('{gcategory}', '', $tpl['weibo']);
        	$tpl['weixin'] = str_replace('{gcategory}', '', $tpl['weixin']);
        }
        
        $game = GameService::getGameInfo($guide['gid']);
        
		$result  = array(
            'title'  => $guide['gtitle'],
            'pic'    => self::joinImgUrl($game['ico']),
            'weibo'  => str_replace(array('{title}','{gtitle}') , array($game['shortgname'],$guide['gtitle']), $tpl['weibo']),
            'weixin' => str_replace(array('{title}','{gtitle}'), array($game['shortgname'],$guide['gtitle']), $tpl['weixin']),
            'url'    => $ishtml5 ? self::getShortURL(self::$URL . 'action=articleDetail&typeID=2&aid=' . $guide_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
    /**
	 * 分享评测
	 */
	public static function shareOpinion($opinion_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$opinion = self::dbCmsSlave()->table('feedback')->select('ftitle','gid','writer')->where('id','=',$opinion_id)->first();
		if(!$opinion) return false;
		$game = GameService::getGameInfo($opinion['gid']);
	    if(!$game){
			$game['shortgname'] = $opinion['title'];
		}
		$result  = array(
            'title'  => $opinion['ftitle'],
            'pic'    => self::joinImgUrl($game['ico']),
            'weibo'  => str_replace('{title}' , $opinion['ftitle'], $tpl['weibo']),
            'weixin' => str_replace('{title}', $opinion['ftitle'], $tpl['weixin']),
            'url'    => $ishtml5 ? self::getShortURL(self::$URL . 'action=articleDetail&typeID=3&aid=' . $opinion_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
    /**
	 * 分享新闻
	 */
	public static function shareNews($news_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$news = self::dbCmsSlave()->table('news')->select('gid', 'title', 'writer','litpic')->where('id','=',$news_id)->first();
		if(!$news) return false;
		$game = GameService::getGameInfo($news['gid']);
		if(!$game){
			$game['shortgname'] = $news['title'];
		}
		$result  = array(
            'title'  => $news['title'],
            'pic'    => $news['litpic'] ? self::joinImgUrl($news['litpic']) : self::joinImgUrl($game['ico']),
            'weibo'  => str_replace(array('{title}','{ntitle}'), array($game['shortgname'],$news['title']), $tpl['weibo']),
            'weixin' => str_replace(array('{title}','{ntitle}'), array($game['shortgname'],$news['title']), $tpl['weixin']),
            'url'    => $ishtml5 ? self::getShortURL(self::$URL . 'action=articleDetail&typeID=1&aid=' . $news_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
    /**
	 * 分享活动
	 */
	public static function shareActivity($activity_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		
		//$activity = self::dbCmsSlave()->table('hot_activity')->select('gid', 'title', 'writer')->where('id','=',$activity_id)->first();		
		//$game = GameService::getGameInfo($activity['gid']);
		$activity = DB::table('activity')->where('id','=',$activity_id)->first();
		if(!$activity) return false;
		$game = GameService::getGameInfo($activity['game_id']);
		if($activity['type'] == 1){
			$url = $ishtml5 ? self::$URL . 'action=activityDetail&atid=' . $activity_id : 'http://m.youxiduo.com';
		}else{
			$url = $ishtml5 ? self::$URL . 'action=articleDetail&typeID=0&aid=' . $activity['rule_id'] : 'http://m.youxiduo.com';
		}
		$result  = array(
            'title'  => ($game ? '《'.$game['shortgname'].'》' : '') . $activity['title'],
            'pic'    => self::joinImgUrl($game['ico']),
            'weibo'  => str_replace(array('{title}','{atitle}') , array($game['shortgname'],$activity['title']), $tpl['weibo']),
            'weixin' => str_replace(array('{title}','{atitle}'), array($game['shortgname'],$activity['title']), $tpl['weixin']),
            //'url'    => self::getShortURL(self::$URL . 'activity/activityshow/'.$activity_id),
            'url'=>self::getShortURL($url)
        );
		return $result;
	}
	
    /**
	 * 分享礼包
	 */
	public static function shareGift($gift_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$gift = GiftbagService::getInfo($gift_id);
		if(!$gift){
			return null;
		}
		if($gift['game_id']){
			$game = GameService::getGameInfo($gift['game_id']);
		}else{
			$game['ico'] = $gift['pic'];
			$game['shortgname'] = $gift['gname'];
		}
		$result  = array(
            'title'  => '《'.$game['shortgname'].'》' . $gift['title'],
            'pic'    => self::joinImgUrl($game['ico']),
            'weibo'  => str_replace(array('{title}','{ltitle}') , array($game['shortgname'],$gift['title']), $tpl['weibo']),
            'weixin' => str_replace(array('{title}','{ltitle}'), array($game['shortgname'],$gift['title']), $tpl['weixin']),
            'url'    => $ishtml5 ? self::getShortURL(self::$URL . 'action=giftDetail&gfid='.$gift_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
	public static function shareGoods($good_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$goods = ShoppingService::getGoodsInfo($good_id,0);
		if(!$goods) return false;
		$result  = array(
            'title'  => $goods['name'],
            'pic'    => self::joinImgUrl($goods['listpic']),
            'weibo'  => str_replace(array('{title}'), array($goods['name']), $tpl['weibo']),
            'weixin' => str_replace(array('{title}'), array($goods['name']), $tpl['weixin']),
            'url'    => $ishtml5 ? self::getShortURL(self::$URL . 'action=goodsDetail&atid='.$good_id) : self::getShortURL('http://m.youxiduo.com'),
        );
		return $result;
	}
	
    public static function shareHunt($hunt_id,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		if($tpl==14){
		    //$goods = ShoppingService::getGoodsInfo($hunt_id,0);
		    $goods = array('name'=>'','listpic'=>'');
		}else{
			$goods = array('name'=>'','listpic'=>'');
		}		
		$result  = array(
            'title'  => $goods['name'],
            'pic'    => self::joinImgUrl($goods['listpic']),
            'weibo'  => str_replace(array('{title}'), array($goods['name']), $tpl['weibo']),
            'weixin' => str_replace(array('{title}'), array($goods['name']), $tpl['weixin']),
            'url'    => self::getShortURL('http://www.youxiduo.com'),
        );
		return $result;
	}
	
    /**
	 * 分享关于我们
	 */
	public static function shareAbout($tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		
		$result = array(
		    'title'=>'游戏多',
		    'pic'=>'http://img.youxiduo.com/userdirs/common/yxd_logo_share.png?time=' . time(),
		    'weibo'=>$tpl['weibo'],
		    'weixin'=>$tpl['weixin'],
		    'url'=>self::getShortURL('http://www.youxiduo.com')
		);
		return $result;
	}
	
	public static function shareTopic($tid,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$topic = ThreadService::showTopicInfo($tid);
		if(!$topic) return false;
		$game = GameService::getGameInfo($topic['gid']);
		$result = array(
		    'title'=>$topic['subject'],
		    'pic'=>self::joinImgUrl($game['ico']),
		    'weibo'=>str_replace(array('{title}','{subject}') , array($game['shortgname'],$topic['subject']), $tpl['weibo']),
		    'weixin'=>str_replace(array('{title}','{subject}') , array($game['shortgname'],$topic['subject']), $tpl['weixin']),
		    'url'=> $ishtml5 ? self::getShortURL(self::$URL.'action=articleDetail&typeID=0&aid='.$tid) : self::getShortURL('http://m.youxiduo.com')
		);
		return $result;
	}
	
	public static function shareForum($gid,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$game = GameService::getGameInfo($gid);
		$result = array(
		    'title'=>$game['shortgname'],
		    'pic'=>self::joinImgUrl($game['ico']),
		    'weibo'=>str_replace(array('{title}') , array($game['shortgname']), $tpl['weibo']),
		    'weixin'=>str_replace(array('{title}') , array($game['shortgname']), $tpl['weixin']),
		    'url'=>$ishtml5 ? self::getShortURL(self::$URL.'action=forumHome&bid=0&gid='.$gid) : self::getShortURL('http://m.youxiduo.com')
		);
		return $result;
	}
	
    public static function shareXyxHome($tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$result = array(
		    'title'=>'手机H5小游戏 | 游戏多小游戏站',
		    'pic'=>self::joinImgUrl('/userdirs/common/xyx_logo_share.png'),
		    'weibo'=>'@游戏多 手机小游戏-H5小游戏-在线试玩-手机在线小游戏-手机游戏免下载直接玩',//str_replace(array('{title}') , array($game['shortgname']), $tpl['weibo']),
		    'weixin'=>'@游戏多 手机小游戏-H5小游戏-在线试玩-手机在线小游戏-手机游戏免下载直接玩',//str_replace(array('{title}') , array($game['shortgname']), $tpl['weixin']),
		    'url'=>$ishtml5 ? self::getShortURL(self::$URL.'action=miniGameIndex&flag=0') : self::getShortURL('http://m.youxiduo.com')
		);
		return $result;
	}
	
    public static function shareXyxList($typeid,$tpl,$ishtml5=0)
	{		
		$result = array(
		    'title'=>'',
		    'pic'=>self::joinImgUrl('/userdirs/common/xyx_logo_share.png'),
		    'weibo'=>'@游戏多 手机小游戏-H5小游戏-在线试玩-手机在线小游戏-手机游戏免下载直接玩',//str_replace(array('{title}') , array($game['gamename']), $tpl['weibo']),
		    'weixin'=>'@游戏多 手机小游戏-H5小游戏-在线试玩-手机在线小游戏-手机游戏免下载直接玩',//str_replace(array('{title}') , array($game['gamename']), $tpl['weixin']),
		    'url'=>$ishtml5 ? self::getShortURL(self::$URL.'action=miniGameList&flag=0&type='.$typeid) : self::getShortURL('http://m.youxiduo.com')
		);
		return $result;
	}
	
    public static function shareXyxDetail($gid,$tpl,$ishtml5=0)
	{
		$tpl = self::getShareTpl($tpl);
		$out = XgameService::getArticle($gid);
		if($out){
			$game = $out['result'];
		}else{
			return false;
		}
		$result = array(
		    'title'=>$game['gamename'] . ' | 游戏多小游戏站',
		    'pic'=>$game['litpic'],
		    'weibo'=>str_replace(array('{title}') , array($game['gamename']), $tpl['weibo']),
		    'weixin'=>str_replace(array('{title}') , array($game['gamename']), $tpl['weixin']),
		    'url'=>$ishtml5 ? self::getShortURL(self::$URL.'action=miniGameDetail&gid='.$gid) : self::getShortURL('http://m.youxiduo.com')
		);
		return $result;
	}
	
	/**
	 * 获取短网址
	 */
	protected static function getShortURL($url)
	{
		$cachekey = 'share::shorturl::' . md5($url);
		if(CLOSE_CACHE==false && CacheService::has($cachekey)){
			return CacheService::get($cachekey);
		}else{
			$apiUrl='https://api.weibo.com/2/short_url/shorten.json?source='.'1866242735'.'&url_long='.urlencode($url);
			try{
			$response = file_get_contents($apiUrl);
			$json = json_decode($response, true);
			$data = $json['urls'][0]['url_short'] ? $json['urls'][0]['url_short'] : '';
			}catch(\Exception $e){
				//$data = 'http://www.youxiduo.com';
				$data = $url;
			}
			CLOSE_CACHE==false && CacheService::forever($cachekey,$data);
			return $data;
		}
	}
	
	/**
	 * 获取分享模板
	 */
	protected static function getShareTpl($typeid)
	{
		$cachekey = 'commend::share_tpl::' . $typeid;
		if(CLOSE_CACHE==false && CacheService::has($cachekey)){
			$tpl = CacheService::get($cachekey);
		}else{
		    $tpl = self::dbCmsSlave()->table('share')->select('weibo','weixin')->where('typeid','=',$typeid)->first();
		    CLOSE_CACHE==false && CacheService::forever($cachekey,$tpl);
		}		
		return $tpl;
	}
}