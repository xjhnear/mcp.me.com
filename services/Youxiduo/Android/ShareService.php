<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Android;

use Youxiduo\Base\BaseService;
use Youxiduo\Android\Model\Activity;
use Youxiduo\Android\Model\Game;
use Youxiduo\Android\Model\GameCollect;
use Youxiduo\Android\Model\GameVideo;
use Youxiduo\Android\Model\Guide;
use Youxiduo\Android\Model\NewGame;
use Youxiduo\Android\Model\News;
use Youxiduo\Android\Model\Opinion;
use Youxiduo\Android\Model\Video;
use Youxiduo\Android\Model\VideoGame;
use Youxiduo\Android\Model\Giftbag;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Youxiduo\V4\Common\ShareService as ShareTo;
use Youxiduo\Activity\Duang\VariationService;

class ShareService extends BaseService
{
	const WEB_URL_GAME       = 'http://m.youxiduo.com/gameandroid/{0}';
	const WEB_URL_NEWGAME    = 'http://m.youxiduo.com/article/noticeshow/{0}';
	const WEB_URL_VIDEO      = 'http://m.youxiduo.com/video/info/{0}';
	const WEB_URL_GAME_VIDEO = 'http://m.youxiduo.com/gameandroid/{0}/{1}';
	const WEB_URL_GUIDE      = 'http://m.youxiduo.com/gameandroid/guide/{0}';
	const WEB_URL_NEWS       = 'http://m.youxiduo.com/gameandroid/news/{0}';
	const WEB_URL_OPINION    = 'http://m.youxiduo.com/gameandroid/defail/{0}';
	//const WEB_URL_GIFTBAG    = 'http://m.youxiduo.com/activity/giftdetail?id={0}';
	const WEB_URL_GIFTBAG    = 'http://free.youxiduo.com/info/giftbag/{0}';
	const WEB_URL_SPECIAL    = 'http://m.youxiduo.com/topic/info/{0}';
	const WEB_URL_TOPIC      = 'http://m.youxiduo.com/';
	const WEB_URL_FORUM      = 'http://m.youxiduo.com/';
	const WEB_URL_GOODS      = 'http://m.youxiduo.com/';
	//const WEB_URL_ACTIVITY   = 'http://m.youxiduo.com/activity/activityshow/{0}';
	const WEB_URL_ACTIVITY   = 'http://free.youxiduo.com/info/activity/{0}';
	const WEB_URL_ABOUT      = 'http://m.youxiduo.com';
	
	public static function forward($params)
	{
		$keys = array('gid','vid','gvid','guid','goid','gnid','gfid','agnid','atid','tid','about','topic_id','goods_id','v3share');
		$response = null;
		foreach($keys as $key){
			if(isset($params[$key])){
				switch($key){
					case 'gid'://游戏
						$response =  self::shareToGame($params[$key]);
						break 2;
					case 'vid'://视频
						$response =  self::shareToVideo($params[$key]);
						break 2;
					case 'gvid'://游戏视频
						$response = self::shareToGameVideo($params[$key]);
						break 2;
					case 'guid'://攻略
						$response =  self::shareToGuide($params[$key]);
						break 2;
					case 'gnid'://新闻
						$response =  self::shareToNews($params[$key]);
						break 2;
					case 'gfid'://礼包
						$response =  self::shareToGiftbag($params[$key]);
						break 2;
					case 'agnid'://新游预告
						$response = self::shareToNewGame($params[$key]);
						break 2;
					case 'goid'://评测
						$response =  self::shareToOpinion($params[$key]);
						break 2;
					case 'tid'://专题
						$response =  self::shareToSpecial($params[$key]);
						break 2;
					case 'about'://
						$response =  self::shareToAbout();
						break 2;
					case 'topic_id'://帖子
						$response =  self::shareToTopic($params[$key]);
						break 2;
					case 'goods_id'://商品
						$response =  self::shareToGoods($params[$key]);
						break 2;
					case 'atid'://活动
						$response =  self::shareToActivity($params[$key]);
						break 2;
					case 'v3share':
						$response = self::shareToV3Activity($params);
						break 2;
					default:
						$response =  self::shareToAbout();
						break 2;
				}
			}
		}
		if($response===null){
			return self::trace_error('E1','参数错误');
		}
		return $response;
	}
	
	protected static function parseURL($url)
	{
		$args = func_get_args();
		if(count($args)>1){
			$params = array_slice($args,1);
			foreach($params as $key=>$val){
				$url = str_replace('{'.$key.'}',$val,$url);
			}
			return $url;
		}
		return $url;
	}
	
	/**
	 * 分享游戏信息
	 * @param string $game_id
	 */
    public static function shareToGame($game_id)
    {
    	$game = Game::db()->where('id','=',$game_id)->first();
    	if(!$game) return self::trace_error('E1','游戏不存在');
    	$data = array();
    	$data['{game_name}'] = $game['shortgname'];
    	$icon = $game['ico'];
    	$url = self::parseURL(self::WEB_URL_GAME,$game_id);
    	$target_id = $game_id;
    	$out = ShareTo::parseTplToContent('android_share_tpl_game_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享活动信息
	 * @param string $game_id
	 */
    public static function shareToActivity($activity_id)
    {
    	$info = Activity::db()->where('id','=',$activity_id)->first();
    	if(!$info) return self::trace_error('E1','活动不存在');
    	$data = array();
    	$data['{title}'] = $info['title'];
    	$icon = $info['pic'];
    	$url = self::parseURL(self::WEB_URL_ACTIVITY,$activity_id);
    	$target_id = $activity_id;
    	$out = ShareTo::parseTplToContent('android_share_tpl_activity_info', $data, $icon, $url,$target_id,true);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
     * 分享新闻信息
     */
    public static function shareToNews($aid)
    {
    	$info = News::db()->where('id','=',$aid)->select(News::raw('id,title'))->first();
    	if(!$info) return self::trace_error('E1','文章不存在');
    	$data = array();
    	$data['{title}'] = $info['title'];
    	$icon = isset($info['litpic']) ? $info['litpic'] : '';    	
    	$url = self::parseURL(self::WEB_URL_NEWS,$aid);
    	$target_id = $aid;
    	$out = ShareTo::parseTplToContent('android_share_tpl_news_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
     * 攻略信息
     */
	public static function shareToGuide($aid)
    {
    	$info = Guide::db()->where('id','=',$aid)->select(News::raw('id,agid,gtitle as title'))->first();
    	if(!$info) return self::trace_error('E1','文章不存在');
    	$game = Game::db()->where('id','=',$info['agid'])->first();
    	$data = array();
    	$data['{title}'] = $info['title'];
    	$icon = isset($game['ico']) ? $game['ico'] : '';    	
    	$url = self::parseURL(self::WEB_URL_GUIDE,$aid);
    	$target_id = $aid;
    	$out = ShareTo::parseTplToContent('android_share_tpl_guide_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
     * 分享评测信息
     */
    public static function shareToOpinion($aid)
    {
    	$info = Opinion::db()->where('id','=',$aid)->select(News::raw('id,ftitle as title'))->first();
    	if(!$info) return self::trace_error('E1','文章不存在');
    	$data = array();
    	$data['{title}'] = $info['title'];
    	$icon = isset($info['litpic']) ? $info['litpic'] : '';    
    	$url = self::parseURL(self::WEB_URL_OPINION,$aid);	
    	$target_id = $aid;
    	$out = ShareTo::parseTplToContent('android_share_tpl_opinion_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享新游信息
	 * @param string $game_id
	 */
    public static function shareToNewGame($game_id)
    {
    	$game = NewGame::db()->where('id','=',$game_id)->first();
    	if(!$game) return self::trace_error('E1','游戏不存在');
    	$data = array();
    	$data['{game_name}'] = $game['gname'];
    	$data['{title}'] = $game['gname'];
    	$icon = $game['pic'];
    	$url = self::parseURL(self::WEB_URL_NEWGAME,$game_id);
    	$target_id = $game_id;
    	$out = ShareTo::parseTplToContent('android_share_tpl_newgame_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享论坛信息
	 * @param string $game_id
	 */
    public static function shareToForum($game_id)
    {
    	$game = Game::db()->where('id','=',$game_id)->first();
    	if(!$game) return self::trace_error('E1','游戏不存在');
    	$data = array();
    	$icon = $game['ico'];
    	$url = self::parseURL(self::WEB_URL_FORUM,$game_id);
    	$target_id = $game_id;
    	$out = ShareTo::parseTplToContent('android_share_tpl_forum_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享帖子信息
	 * @param string $game_id
	 */
    public static function shareToTopic($topic_id)
    {
    	$json = file_get_contents(Config::get('app.bbs_api_url').'topic_detail?tid='.$topic_id);
    	if(!$json) return self::trace_error('E1','帖子不存在');
    	$json = json_decode($json,true);
    	if($json['errorCode']!='0') return self::trace_error('E1','帖子不存在');
    	$topic = $json['result'];
    	$data = array();
    	$data['{title}'] = $topic['subject'];
    	$icon = '';
    	$url = self::parseURL(self::WEB_URL_TOPIC,$topic_id);
    	$target_id = $topic_id;
    	$out = ShareTo::parseTplToContent('android_share_tpl_topic_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享视频信息
	 * @param string $game_id
	 */
    public static function shareToVideo($vid)
    {
    	$info = Video::db()->where('id','=',$vid)->select(array('vname','litpic','id'))->first();
    	if(!$info) return self::trace_error('E1','视频不存在');
    	$data = array();
    	$data['{title}'] = $info['vname'];
    	$icon = $info['litpic'];
    	$url = self::parseURL(self::WEB_URL_VIDEO,$vid);
    	$target_id = $vid;
    	$out = ShareTo::parseTplToContent('android_share_tpl_video_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享视频信息
	 * @param string $game_id
	 */
    public static function shareToGameVideo($vid)
    {
    	$info = GameVideo::db()->where('id','=',$vid)->where('agid','>',0)->select(array('ico','agid'))->first();
    	if(!$info) return self::trace_error('E1','视频不存在');
    	$game = Game::db()->where('id','=',$info['agid'])->first();
    	if(!$game) return self::trace_error('E1','游戏不存在');
    	$data = array();
    	$data['{title}'] = $game['shortgname'];
    	$data['{game_name}'] = '';
    	$icon = $info['ico'];
    	$url = self::parseURL(self::WEB_URL_GAME_VIDEO,$vid,$info['agid']);
    	$target_id = $vid;
    	$out = ShareTo::parseTplToContent('android_share_tpl_gamevideo_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享礼包信息
	 * @param string $game_id
	 */
    public static function shareToGiftbag($giftbag_id)
    {
    	$info = Giftbag::db()->where('id','=',$giftbag_id)->first();
    	if(!$info) return self::trace_error('E1','礼包不存在');
    	$game = Game::db()->where('id','=',$info['game_id'])->first();
    	$data = array();
    	$data['{game_name}'] = isset($game['shortgname']) ? $game['shortgname'] : '';
    	$data['{title}'] = $info['title'];
    	$icon = isset($game['ico']) ? $game['ico'] : '';
    	$url = self::parseURL(self::WEB_URL_GIFTBAG,$giftbag_id);
    	$target_id = $giftbag_id;
    	$out = ShareTo::parseTplToContent('android_share_tpl_giftbag_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享商品信息
	 * @param string $game_id
	 */
    public static function shareToGoods($goods_id)
    {
    	$json = file_get_contents(Config::get('app.mall_api_url').'product/query_product?id='.$goods_id);
    	if(!$json) return self::trace_error('E1','商品不存在');
    	$json = json_decode($json,true);
    	if($json['errorCode']!='0') return self::trace_error('E1','商品不存在');
    	$goods = $json['result'][0];
    	$goods['img'] = json_decode($goods['img'],true);
    	$data = array();
    	$data['{goods_name}'] = $goods['title'];
    	$icon = $goods['img']['listPic'];
    	$url = self::parseURL(self::WEB_URL_GOODS,$goods_id);
    	$target_id = $goods_id;
    	$out = ShareTo::parseTplToContent('android_share_tpl_goods_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享专题信息
	 * @param string $game_id
	 */
    public static function shareToSpecial($special_id)
    {
    	$info = GameCollect::db()->where('id','=',$special_id)->first();    	
    	if(!$info) return self::trace_error('E1','专题不存在');
    	$data = array();
    	$data['{title}'] = $info['ztitle'];
    	$icon = $info['litpic'];
    	$url = self::parseURL(self::WEB_URL_SPECIAL,$special_id);
    	$target_id = $special_id;
    	$out = ShareTo::parseTplToContent('android_share_tpl_special_info', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }
    
    /**
	 * 分享游戏多信息
	 * 
	 */
    public static function shareToAbout()
    {
    	$data = array();
    	$icon = 'http://img.youxiduo.com/userdirs/common/yxd_logo_share.png?time=' . time();
    	$url = self::parseURL(self::WEB_URL_ABOUT);
    	$target_id = 0;
    	$out = ShareTo::parseTplToContent('android_share_tpl_about', $data, $icon, $url,$target_id);
    	if($out===false) return self::trace_error('E1','模板解析错误');
    	return self::trace_result(array('result'=>$out));
    }

	/**
	 * @param $params
	 * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\JsonResponse
	 */
	public static function shareToV3Activity($params)
	{
		$out = array();
		$aid = $params['v3share'];
		$uid = $params['uid'];
		$sharetype = $params['sharetype'];
		$taskId = $params['taskId'];
		//$deviceId = $params[''];
		$out = VariationService::getV3ShareContent($aid,$uid,$sharetype,$taskId);
		return self::trace_result(array('result'=>$out));
	}
}