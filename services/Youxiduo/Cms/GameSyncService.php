<?php

namespace Youxiduo\Cms;
use Youxiduo\Cms\Model\Mtype;

use Illuminate\Support\Facades\Config;

use Youxiduo\Cms\Model\Addongame;
use Youxiduo\Cms\Model\Arctiny;
use Youxiduo\Cms\Model\Tagindex;
use Youxiduo\Cms\Model\Taglist;
use Youxiduo\Cms\Model\Arctype;
use Youxiduo\Cms\Model\Archives;

use Youxiduo\Base\BaseService;
use Youxiduo\Android\Model\Game as GameApk;
use Youxiduo\Android\Model\GamePlat;
use Youxiduo\Android\Model\GameTag;
use Youxiduo\Android\Model\GameType;
use Youxiduo\Game\Model\GameIos;
use Youxiduo\Game\Model\GamePicture;

class GameSyncService extends BaseService
{
    const OP_ADD = 'add';
	const OP_EDIT = 'edit';
	const OP_DELETE = 'delete';
	const OP_EDIT_ICON = 'edit_icon';
	const OP_EDIT_DOWNLOAD_URL = 'edit_download_url';
	
	/**
	 * 同步安卓游戏信息
	 * @param int $game_id 游戏ID
	 * @param string $op 操作
	 */
	public static function syncAndroid($game_id,$op)
	{
		$result = false;
		switch($op)
		{			
			case self::OP_ADD:
				$result = self::addAndroidData($game_id);
				break;
			case self::OP_EDIT:
				$result = self::editAndroidData($game_id);
				break;
			case self::OP_EDIT_ICON:
				$result = self::editAndroidIcon($game_id);
				break;
			case self::OP_EDIT_DOWNLOAD_URL:
				$result = self::saveAndroidDownloadURL($game_id);
				break;
			case self::OP_DELETE:
				//$result = self::deleteAndroidData($game_id);
				break;
			default:
				break;
		}
		if($result){
			self::writeSuccessLog('安卓游戏['.$game_id.']'.$op.'操作同步成功');
		}else{
		}
		return $result;
	}
	
    /**
	 * 同步IOS游戏信息
	 * @param int $game_id 游戏ID
	 * @param string $op 操作
	 */
	public static function syncIos($game_id,$op)
	{
		$result = false;
		switch($op)
		{			
			case self::OP_ADD:
				$result = self::addIosData($game_id);
				break;
			case self::OP_EDIT:
				$result = self::editIosData($game_id);
				break;
			case self::OP_EDIT_ICON:
				$result = self::editIosIcon($game_id);
				break;
			case self::OP_DELETE:
				//$result = self::deleteAndroidData($game_id);
				break;
			default:
				break;
		}
	    if($result){
			self::writeSuccessLog('IOS游戏['.$game_id.']'.$op.'操作同步成功');
		}
		return $result;
	}
	
	public static function addAndroidData($game_id)
	{
		$device = 'android';
		
		$game = GameApk::db()->where('id','=',$game_id)->where('isdel','=','0')->first();
		if(!$game) return false;
		//游戏标签
		$game_tags = GameTag::db()->where('agid','=',$game_id)->lists('tag');
					
		return self::addGameData($game, $device, $game_tags);		
	}
	
    public static function addIosData($game_id)
	{
		$device = 'ios';
		
		$game = GameIos::db()->where('id','=',$game_id)->where('isdel','=','0')->first();
		if(!$game) return false;
		//游戏标签
		$game_tags = GameTag::db()->where('gid','=',$game_id)->lists('tag');
			
		return self::addGameData($game, $device, $game_tags);		
	}
	
    public static function editIosData($game_id)
	{
	    $device = 'ios';
		
		$game = GameIos::db()->where('id','=',$game_id)->where('isdel','=','0')->first();
		if(!$game) return false;
		//游戏标签
		$game_tags = GameTag::db()->where('gid','=',$game_id)->lists('tag');
		return self::editGameData($game, $game_tags, $device);
	}	    
	
    public static function editAndroidData($game_id)
	{
	    $device = 'android';
	    
		$game = GameApk::db()->where('id','=',$game_id)->where('isdel','=','0')->first();
		if(!$game) return false;
		//游戏标签
		$game_tags = GameTag::db()->where('agid','=',$game_id)->lists('tag');
		
		return self::editGameData($game, $game_tags, $device);
	}
	
	public static function editAndroidIcon($game_id)
	{
	    $device = 'android';
		
		$game = GameApk::db()->where('id','=',$game_id)->where('isdel','=','0')->first();
		if(!$game) return false;	
        $icon = $game['ico'];
        $pics = array();
        $pics_res = $game['pics'] ? explode(',',$game['pics']) : array();
        foreach($pics_res as $row){
        	$pics[] = Config::get('app.image_url') . $row;
        } 
		self::saveArchivesIcon($game_id, $icon, $pics, $device);
	}
	
	/**
	 * 保存安卓下载地址
	 */
    protected static function saveAndroidDownloadURL($game_id)
	{
		$platform = GamePlat::db()->where('agid','=',$game_id)->orderBy('istop','desc')->orderBy('sort','desc')->orderBy('id','desc')->first();
		if(!$platform) return false;
		$data = array('apkurl'=>$platform['downurl']);
		return self::updateArchiveGameInfo($game_id, $data, 'android');
	}
	
    public static function editIosIcon($game_id)
	{
	    $device = 'ios';
		
		$game = GameIos::db()->where('id','=',$game_id)->where('isdel','=','0')->first();
		if(!$game) return false;	
        $icon = $game['ico'];
        $pics = array();
        $pics_res = GamePicture::db()->where('game_id','=',$game_id)->where('type','=','ios')->get();
        foreach($pics_res as $row){
        	$pics[] = Config::get('app.image_url') . $row['litpic'];
        } 
		return self::saveArchivesIcon($game_id, $icon, $pics, $device);
	}
	
	protected static function addGameData($game,$device,$game_tags)
	{
	    //游戏库
		$dede_game_typeid = self::addArctypeByGameMenu();
		
		if($dede_game_typeid){
			$params = array('seotitle'=>$game['shortgname'],'game'=>$game);
			//游戏专区		    
			$dede_game_id = self::addArctypeByGame($dede_game_typeid,$game['shortgname'],$params);	
            if(!$dede_game_id){
            	self::rollBack($dede_game_typeid,0,0);
            	//记录错误
            	self::writeErrorLog('游戏专区同步失败:'.$game['id']);
            	return false;
            }										
			//攻略			
			$dede_guide_typeid = self::addArctypeByGuide($dede_game_typeid,$dede_game_id,$params);
			if(!$dede_guide_typeid){
				self::rollBack($dede_game_typeid,0,0);
				self::rollBack(0,$dede_game_id,0);
            	//记录错误
            	self::writeErrorLog('游戏攻略同步失败:'.$game['id']);
            	return false;
			}			
			//评测
			$dede_opinion_typeid = 6;
			//新闻
            $dede_news_typeid = self::addArctypeByNews($dede_game_typeid, $dede_game_id,$params);
		    if(!$dede_news_typeid){
				self::rollBack($dede_game_typeid,0,0);
				self::rollBack(0,$dede_game_id,0);
				self::rollBack(0,$dede_guide_typeid,0);
            	//记录错误
            	self::writeErrorLog('游戏新闻同步失败:'.$game['id']);
            	return false;
			}
			//视频
			$dede_video_typeid = self::addArctypeByVideo($dede_game_typeid, $dede_game_id,$params);
		    if(!$dede_video_typeid){
				self::rollBack($dede_game_typeid,0,0);
				self::rollBack(0,$dede_game_id,0);
				self::rollBack(0,$dede_guide_typeid,0);
				self::rollBack(0,$dede_news_typeid,0);
            	//记录错误
            	self::writeErrorLog('游戏视频同步失败:'.$game['id']);
            	return false;
			}
			//图鉴
			$dede_tujian_typeid = self::addArctypeByTujian($dede_game_typeid, $dede_game_id,$params);
		    if(!$dede_tujian_typeid){
				self::rollBack($dede_game_typeid,0,0);
				self::rollBack(0,$dede_game_id,0);
				self::rollBack(0,$dede_guide_typeid,0);
				self::rollBack(0,$dede_news_typeid,0);
				self::rollBack(0,$dede_video_typeid,0);
            	//记录错误
            	self::writeErrorLog('游戏图鉴同步失败:'.$game['id']);
            	return false;
			}
			//资料
			$dede_info_typeid = self::addArctypeByInfo($dede_game_typeid, $dede_game_id,$params);
		    if(!$dede_info_typeid){
				self::rollBack($dede_game_typeid,0,0);
				self::rollBack(0,$dede_game_id,0);
				self::rollBack(0,$dede_guide_typeid,0);
				self::rollBack(0,$dede_news_typeid,0);
				self::rollBack(0,$dede_video_typeid,0);
				self::rollBack(0,$dede_tujian_typeid,0);
            	//记录错误
            	self::writeErrorLog('游戏资料同步失败:'.$game['id']);
            	return false;
			}
			//图片
			$dede_picture_typeid = self::addArctypeByPicture($dede_game_typeid, $dede_game_id,$params);
		    if(!$dede_picture_typeid){
				self::rollBack($dede_game_typeid,0,0);
				self::rollBack(0,$dede_game_id,0);
				self::rollBack(0,$dede_guide_typeid,0);
				self::rollBack(0,$dede_news_typeid,0);
				self::rollBack(0,$dede_video_typeid,0);
				self::rollBack(0,$dede_tujian_typeid,0);
				self::rollBack(0,$dede_info_typeid,0);
            	//记录错误
            	self::writeErrorLog('游戏图片同步失败:'.$game['id']);
            	return false;
			}
			//文章属性
			$archive_result = self::addArchives($dede_game_typeid, $dede_game_id, $game,$device);
		    if(!$archive_result){
				self::rollBack($dede_game_typeid,0,0);
				self::rollBack(0,$dede_game_id,0);
				self::rollBack(0,$dede_guide_typeid,0);
				self::rollBack(0,$dede_news_typeid,0);
				self::rollBack(0,$dede_video_typeid,0);
				self::rollBack(0,$dede_tujian_typeid,0);
				self::rollBack(0,$dede_info_typeid,0);
				self::rollBack(0,$dede_picture_typeid,0);
				self::writeErrorLog('游戏文章同步失败:'.$game['id']);
            	//记录错误
            	return false;
			}
			//游戏属性
			$archive_result = self::addArchiveGameInfo($dede_game_typeid, $game,$device);
		    if(!$archive_result){
				self::rollBack($dede_game_typeid,0,0);
				self::rollBack(0,$dede_game_id,0);
				self::rollBack(0,$dede_guide_typeid,0);
				self::rollBack(0,$dede_news_typeid,0);
				self::rollBack(0,$dede_video_typeid,0);
				self::rollBack(0,$dede_tujian_typeid,0);
				self::rollBack(0,$dede_info_typeid,0);
				self::rollBack(0,$dede_picture_typeid,0);
				self::rollBack(0,0,$dede_game_typeid);
				self::writeErrorLog('游戏属性同步失败:'.$game['id']);
            	//记录错误
            	return false;
			}
			//Tag
			$tag_result = self::addTags($game_tags, $dede_game_typeid);
			
			$data = array(
				'gid'     => $device=='ios'? $game['id'] : 0,
			    'agid'    => $device=='android'? $game['id'] : 0,
				'news'    => (int)$dede_news_typeid,
				'guide'   => (int)$dede_guide_typeid,
				'opinion' => (int)$dede_opinion_typeid,
				'tujian'  => (int)$dede_tujian_typeid,
				'info'    => (int)$dede_info_typeid,
				'picture' => (int)$dede_picture_typeid,
				'video'   => (int)$dede_video_typeid
			);			
			
			$mt = Mtype::db()->insert($data);
			return $archive_result ? true : false;
		}else{
			return false;
		}
	}
	
	public static function getGameArticleType($game_id,$device)
	{
		$out = array();
	    if($device=='ios'){
			$out = Mtype::db()->where('gid','=',$game_id)->first();
		}else{
		    $out = Mtype::db()->where('agid','=',$game_id)->first();
		}
		if($out) return $out;
		
		$yxdid = $device=='ios' ? 'g_'.$game_id : 'apk_'.$game_id;
		$archive = Archives::db()->where('yxdid','=',$yxdid)->select('id,writer,reftype')->first();
		if(!$archive) return null;
		$arctype = Arctype::db()->where('refarc'.'=',$archive['id'])->where('reid','=',4)->select('id,typename')->first();
		if(!$arctype) return null;
		$result = Arctype::db()->where('reid','=',$arctype['id'])->select('id,typename')->get();
		if(!$result) return null;
		$data = array();
		foreach($result as $row){
			switch($row['typename'])
			{
				case '新闻':
					$data['news'] = $row['id'];
					break;
				case '攻略':
					$data['guide'] = $row['id'];
					break;
				case '资料':
					$data['info'] = $row['id'];
					break;
				case '精彩视频':
					$data['video'] = $row['id'];
					break;
				case '图片':
					$data['picture'] = $row['id'];
					break;
				case '图鉴':
					$data['tujian'] = $row['id'];
					break;
				default:
					break;									
			}
		}		
		if(!$data) return null;
		if($device=='ios'){
			$data['gid'] = $game_id;
			$data['agid'] = 0;
		}else{
			$data['agid'] = $game_id;
			$data['gid'] = 0;
		}
		
		if($device=='ios'){
			if(!Mtype::db()->where('gid','=',$game_id)->first()){
				Mtype::db()->insert($data);
			}
		}else{
		    if(!Mtype::db()->where('agid','=',$game_id)->first()){
				Mtype::db()->insert($data);
			}
		}
		return $data;
	}
	
	/**
	 * 同步游戏分类
	 */
	protected static function addArctypeByGameMenu()
	{
		return Arctiny::db()->insertGetId(array('channel'=>3,'typeid'=>4,'senddate'=>time()));
	}
	
	/**
	 * 同步游戏
	 */
	protected static function addArctypeByGame($dede_game_typeid,$gamename,$params=array())
	{
		$data_type = array();
		$data_type['reid'] = 4;
		$data_type['topid'] = 1;
		$data_type['typename'] = $gamename;
		$data_type['typedir'] = '/a/apple/game/' . $dede_game_typeid;
		$data_type['channeltype'] = 3;
		$data_type['isallow'] = 0;
		$data_type['templist'] = 'mobile/article_game.htm';
		$data_type['temparticle'] = '{"1":"mobile/article_game.htm"}';
		$data_type['namerule'] = '{"1":"{typedir}/{aid}.shtml"}';
		$data_type['namerule2'] = '{typedir}/list_{page}.shtml';
		$data_type['modname'] = 'default';
		$data_type['moresite'] = '1';
		$data_type['sitepath'] = '/a/apple';
		$data_type['siteurl'] = 'http://www.youxiduo.com';
		$data_type['urlrule'] = '{"1":"{typedir}/{aid}.shtml"';
		$data_type['mainrule'] = 1;
		$data_type['refarc'] = $dede_game_typeid;
		$data_type['yxdid'] = isset($params['game']['id']) ? $params['game']['id'] : 0;
		
	    if($params['seotitle']){
	    	$data_type['seotitle'] = '{arctitle}官网_{arctitle}攻略_{arctitle}礼包_激活码_下载_游戏多_{arctitle}攻略资料站';
	    	$data_type['keywords'] = '{arctitle}_{arctitle}官网_{arctitle}攻略_{arctitle}礼包_{arctitle}激活码_{arctitle}下载';
	    	$data_type['description'] = '游戏多{arctitle}攻略站为你提供最新最好的{arctitle}官方新闻公告、{arctitle}攻略、{arctitle}游戏评测、{arctitle}激活码、{arctitle}礼包、{arctitle}下载、{arctitle}游戏视频攻略的内容';
			$data_type['seotitle'] = str_replace('{arctitle}',$params['seotitle'],$data_type['seotitle']);
			$data_type['keywords'] = str_replace('{arctitle}',$params['seotitle'],$data_type['keywords']);
			$data_type['description'] = str_replace('{arctitle}',$params['seotitle'],$data_type['description']);
		}
		
		$dede_typeid = Arctype::db()->insertGetId($data_type);
		return $dede_typeid;
	}
	
	/**
	 * 同步游戏下的新闻栏目
	 */
	protected static function addArctypeByNews($dede_game_typeid,$dede_game_id,$params=array())
	{
		$data_type = array();
		$data_type['reid'] = $dede_game_id;
		$data_type['topid'] = 1;
		$data_type['typename'] = '新闻';
		$data_type['typedir'] = '/a/apple/game/' . $dede_game_typeid . '/xinwen';
		$data_type['channeltype'] = 4;
		$data_type['isallow'] = 1;
		$data_type['templist'] = 'mobile/list.htm';
		$data_type['temparticle'] = '{"1":"mobile/article.htm"}';
				
		$data_type['namerule'] = '{"1":"{typedir}/{aid}.shtml"}';
		$data_type['namerule2'] = '{typedir}/list_{page}.shtml';
		$data_type['modname'] = 'default';
		$data_type['moresite'] = '1';
		$data_type['sitepath'] = '/a/apple';
		$data_type['siteurl'] = 'http://www.youxiduo.com';
		$data_type['urlrule'] = '{"1":"{typedir}/{aid}.shtml"';
		$data_type['mainrule'] = 1;
		$data_type['refarc'] = $dede_game_typeid;

	    if($params['seotitle']){
	    	$data_type['seotitle'] = '{arctitle}资讯_{arctitle}新闻_{arctitle}公告_游戏多_{arctitle}专区';
	    	$data_type['keywords'] = '{arctitle}资讯,{arctitle}新闻,{arctitle}公告,{arctitle}更新';
	    	$data_type['description'] = '游戏多{arctitle}官方合作专区为你提供最新最好的{arctitle}官网资讯、{arctitle}新闻、{arctitle}礼包、{arctitle}激活码，最新最全的{arctitle}新闻就上游戏多';
			$data_type['seotitle'] = str_replace('{arctitle}',$params['seotitle'],$data_type['seotitle']);
			$data_type['keywords'] = str_replace('{arctitle}',$params['seotitle'],$data_type['keywords']);
			$data_type['description'] = str_replace('{arctitle}',$params['seotitle'],$data_type['description']);
		}
		
		$dede_typeid = Arctype::db()->insertGetId($data_type);
		return $dede_typeid;
	}
	
	/**
	 * 同步游戏下的攻略栏目
	 */
	protected static function addArctypeByGuide($dede_game_typeid,$dede_game_id,$params=array())
	{
		$data_type = array();
		$data_type['reid'] = $dede_game_id;
		$data_type['topid'] = 1;
		$data_type['typename'] = '攻略';
		$data_type['typedir'] = '/a/apple/game/' . $dede_game_typeid . '/gonglue';
		$data_type['channeltype'] = 4;
		$data_type['isallow'] = 1;
		$data_type['templist'] = 'mobile/list.htm';
		$data_type['temparticle'] = '{"1":"mobile/article.htm"}';
		
		$data_type['namerule'] = '{"1":"{typedir}/{aid}.shtml"}';
		$data_type['namerule2'] = '{typedir}/list_{page}.shtml';
		$data_type['modname'] = 'default';
		$data_type['moresite'] = '1';
		$data_type['sitepath'] = '/a/apple';
		$data_type['siteurl'] = 'http://www.youxiduo.com';
		$data_type['urlrule'] = '{"1":"{typedir}/{aid}.shtml"';
		$data_type['mainrule'] = 1;
		$data_type['refarc'] = $dede_game_typeid;
		
	    if($params['seotitle']){
	    	$data_type['seotitle'] = '{arctitle}攻略_{arctitle}辅助_{arctitle}破解_游戏多_{arctitle}专区';
	    	$data_type['keywords'] = '{arctitle}攻略,{arctitle}辅助,{arctitle}破解';
	    	$data_type['description'] = '游戏多{arctitle}官方合作专区为你提供最新最好的{arctitle}攻略、{arctitle}辅助、{arctitle}破解、{arctitle}新手指南、{arctitle}辅助教程，告诉你{arctitle}应该怎么玩，最新最全的{arctitle}攻略就上游戏多';
			$data_type['seotitle'] = str_replace('{arctitle}',$params['seotitle'],$data_type['seotitle']);
			$data_type['keywords'] = str_replace('{arctitle}',$params['seotitle'],$data_type['keywords']);
			$data_type['description'] = str_replace('{arctitle}',$params['seotitle'],$data_type['description']);
		}
		$dede_typeid = Arctype::db()->insertGetId($data_type);
		return $dede_typeid;
	}
	
	/**
	 * 同步游戏下的视频栏目
	 */
	protected static function addArctypeByVideo($dede_game_typeid,$dede_game_id,$params=array())
	{
	    $data_type = array();
		$data_type['reid'] = $dede_game_id;
		$data_type['topid'] = 1;
		$data_type['typename'] = '精彩视频';
		$data_type['typedir'] = '/a/apple/game/' . $dede_game_typeid . '/shipin';
		$data_type['channeltype'] = 4;
		$data_type['isallow'] = 1;
		$data_type['templist'] = 'mobile/list_shipin.htm';
		$data_type['temparticle'] = '{"1":"mobile/article_shipin.htm"}';
		
		$data_type['namerule'] = '{"1":"{typedir}/{aid}.shtml"}';
		$data_type['namerule2'] = '{typedir}/list_{page}.shtml';
		$data_type['modname'] = 'default';
		$data_type['moresite'] = '1';
		$data_type['sitepath'] = '/a/apple';
		$data_type['siteurl'] = 'http://www.youxiduo.com';
		$data_type['urlrule'] = '{"1":"{typedir}/{aid}.shtml"';
		$data_type['mainrule'] = 1;
		$data_type['refarc'] = $dede_game_typeid;
		
	    if($params['seotitle']){
	    	$data_type['seotitle'] = '{arctitle}游戏资讯_{arctitle}玩法视频教程_{arctitle}视频攻略_游戏多_{arctitle}专区';
	    	$data_type['keywords'] = '{arctitle}玩法教程,{arctitle}视频教程,{arctitle}视频攻略,{arctitle}怎么玩';
	    	$data_type['description'] = '游戏多{arctitle}官方合作专区为你提供最新最好的{arctitle}玩法视频教程、{arctitle}游戏资讯、{arctitle}视频攻略、教你{arctitle}怎么玩，最新最全的{arctitle}攻略就上游戏多';
			$data_type['seotitle'] = str_replace('{arctitle}',$params['seotitle'],$data_type['seotitle']);
			$data_type['keywords'] = str_replace('{arctitle}',$params['seotitle'],$data_type['keywords']);
			$data_type['description'] = str_replace('{arctitle}',$params['seotitle'],$data_type['description']);
		}
		
		$dede_typeid = Arctype::db()->insertGetId($data_type);
		return $dede_typeid;
	}
	
    /**
	 * 同步游戏下的图鉴栏目
	 */
	protected static function addArctypeByTujian($dede_game_typeid,$dede_game_id,$params=array())
	{
	    $data_type = array();
		$data_type['reid'] = $dede_game_id;
		$data_type['topid'] = 1;
		$data_type['typename'] = '图鉴';
		$data_type['typedir'] = '/a/apple/game/' . $dede_game_typeid . '/tujian';
		
		$data_type['channeltype'] = 4;
		$data_type['isallow'] = 1;
		$data_type['templist'] = 'mobile/list.htm';
		$data_type['temparticle'] = '{"1":"mobile/article.htm"}';
		
		$data_type['namerule'] = '{"1":"{typedir}/{aid}.shtml"}';
		$data_type['namerule2'] = '{typedir}/list_{page}.shtml';
		$data_type['modname'] = 'default';
		$data_type['moresite'] = '1';
		$data_type['sitepath'] = '/a/apple';
		$data_type['siteurl'] = 'http://www.youxiduo.com';
		$data_type['urlrule'] = '{"1":"{typedir}/{aid}.shtml"';
		$data_type['mainrule'] = 1;
		$data_type['refarc'] = $dede_game_typeid;
		
	    if($params['seotitle']){
	    	$data_type['seotitle'] = '{arctitle}游戏大全_游戏多_{arctitle}专区';
	    	$data_type['keywords'] = '{arctitle}图鉴大全,{arctitle}资料大全,{arctitle}数据大全,{arctitle}数据库';
	    	$data_type['description'] = '游戏多{arctitle}官方合作专区为你提供最全面的{arctitle}图鉴、{arctitle}游戏资料，最新最全的{arctitle}图鉴数据查询就上游戏多';
			$data_type['seotitle'] = str_replace('{arctitle}',$params['seotitle'],$data_type['seotitle']);
			$data_type['keywords'] = str_replace('{arctitle}',$params['seotitle'],$data_type['keywords']);
			$data_type['description'] = str_replace('{arctitle}',$params['seotitle'],$data_type['description']);
		}
		
		$dede_typeid = Arctype::db()->insertGetId($data_type);
		return $dede_typeid;
	}
	
    /**
	 * 同步游戏下的资料栏目
	 */
	protected static function addArctypeByInfo($dede_game_typeid,$dede_game_id,$params=array())
	{
	    $data_type = array();
		$data_type['reid'] = $dede_game_id;
		$data_type['topid'] = 1;
		$data_type['typename'] = '资料';
		$data_type['typedir'] = '/a/apple/game/' . $dede_game_typeid . '/ziliao';
		
		$data_type['channeltype'] = 4;
		$data_type['isallow'] = 1;
		$data_type['templist'] = 'mobile/list.htm';
		$data_type['temparticle'] = '{"1":"mobile/article.htm"}';
		
		$data_type['namerule'] = '{"1":"{typedir}/{aid}.shtml"}';
		$data_type['namerule2'] = '{typedir}/list_{page}.shtml';
		$data_type['modname'] = 'default';
		$data_type['moresite'] = '1';
		$data_type['sitepath'] = '/a/apple';
		$data_type['siteurl'] = 'http://www.youxiduo.com';
		$data_type['urlrule'] = '{"1":"{typedir}/{aid}.shtml"';
		$data_type['mainrule'] = 1;
		$data_type['refarc'] = $dede_game_typeid;
		
	    if($params['seotitle']){
	    	$data_type['seotitle'] = '{arctitle}游戏指南_{arctitle}怎么玩_{arctitle}系统介绍_{arctitle}基础玩法_游戏多_{arctitle}专区';
	    	$data_type['keywords'] = '{arctitle}游戏指南,{arctitle}系统介绍,{arctitle}怎么玩,{arctitle}玩法攻略';
	    	$data_type['description'] = '游戏多{arctitle}官方合作专区为你提供最全面的{arctitle}系统介绍、{arctitle}新手指南、教你{arctitle}怎么玩，{arctitle}基础玩法，最新最全的{arctitle}图鉴数据查询就上游戏多';
			$data_type['seotitle'] = str_replace('{arctitle}',$params['seotitle'],$data_type['seotitle']);
			$data_type['keywords'] = str_replace('{arctitle}',$params['seotitle'],$data_type['keywords']);
			$data_type['description'] = str_replace('{arctitle}',$params['seotitle'],$data_type['description']);
		}
		
		$dede_typeid = Arctype::db()->insertGetId($data_type);
		return $dede_typeid;
	}
	
    /**
	 * 同步游戏下的图片栏目
	 */
	protected static function addArctypeByPicture($dede_game_typeid,$dede_game_id,$params=array())
	{
	    $data_type = array();
		$data_type['reid'] = $dede_game_id;
		$data_type['topid'] = 1;
		$data_type['typename'] = '图片';
		$data_type['typedir'] = '/a/apple/game/' . $dede_game_typeid . '/tupian';
		
		$data_type['channeltype'] = 4;
		$data_type['isallow'] = 1;
		$data_type['templist'] = 'mobile/list_shipin.htm';
		$data_type['temparticle'] = '{"1":"mobile/article_shipin.htm"}';
		
		$data_type['namerule'] = '{"1":"{typedir}/{aid}.shtml"}';
		$data_type['namerule2'] = '{typedir}/list_{page}.shtml';
		$data_type['modname'] = 'default';
		$data_type['moresite'] = '1';
		$data_type['sitepath'] = '/a/apple';
		$data_type['siteurl'] = 'http://www.youxiduo.com';
		$data_type['urlrule'] = '{"1":"{typedir}/{aid}.shtml"';
		$data_type['mainrule'] = 1;
		$data_type['refarc'] = $dede_game_typeid;
		
	    if($params['seotitle']){
	    	$data_type['seotitle'] = '{arctitle}图鉴_{arctitle}截图_{arctitle}画面_游戏多_{arctitle}专区';
	    	$data_type['keywords'] = '{arctitle}图鉴,{arctitle}截图,{arctitle}画面怎么样,{arctitle}数据库';
	    	$data_type['description'] = '游戏多{arctitle}官方合作专区为你提供最全面的{arctitle}游戏截图、{arctitle}图鉴，{arctitle}游戏画面，更多的{arctitle}游戏图片就上游戏多';
			$data_type['seotitle'] = str_replace('{arctitle}',$params['seotitle'],$data_type['seotitle']);
			$data_type['keywords'] = str_replace('{arctitle}',$params['seotitle'],$data_type['keywords']);
			$data_type['description'] = str_replace('{arctitle}',$params['seotitle'],$data_type['description']);
		}
		
		$dede_typeid = Arctype::db()->insertGetId($data_type);
		return $dede_typeid;
	}
	
	/**
	 * 游戏基础信息
	 */
	protected static function addArchives($dede_game_typeid,$dede_game_id,$game,$device)
	{
		return self::saveArchives($dede_game_typeid, $dede_game_id, $game, $device,'add');
	}
	
	protected static function saveArchives($dede_game_typeid,$dede_game_id,$game,$device,$op)
	{
		$data = array();
				
		$data['title'] = $game['gname'];
		$data['shorttitle'] = $game['shortgname'];		
		$data['pubdate'] = $game['updatetime'];
		$data['description'] = $game['editorcomt'];
		$data['keywords'] = $game['shortgname'];
		$data['litpic'] = $game['ico'] ? Config::get('app.image_url') . $game['ico'] : '';
		
		if($op=='add'){
			$data['channel'] = 3;
			$data['typeid'] = 4;
			$data['senddate'] = time();
			$data['reftype'] = $dede_game_id;
			$data['writer'] = $game['id'];
			$data['goodpost'] = 0;
			$data['badpost'] = 0;		
			$data['ismake'] = 0;
			$data['arcrank'] = -1;		
			//$data['senddate'] = $game['addtime'];
			$data['yxdid'] = $device=='ios' ? 'g_'.$game['id'] : 'apk_'.$game['id'];//www库的游戏ID				
			$data['id'] = $dede_game_typeid;
		}
		$result = $op=='add' ? Archives::db()->insert($data) : Archives::db()->where('id','=',$dede_game_typeid)->update($data); 
		return  $result ? true : false;
	}
	
	protected static function saveArchivesIcon($game_id,$icon,array $pics,$device)
	{
		$yxdid = $device=='ios' ? 'g_'.$game_id : 'apk_'.$game_id;
		$archive = Archives::db()->where('yxdid','=',$yxdid)->first();
		if(!$archive) return false;
		Archives::db()->where('id','=',$archive['id'])->update(array('litpic'=>Config::get('app.image_url').$icon));
		$gamepic = array();
		if($pics){			
			foreach($pics as $pic){
				$gamepic[] = $pic;
			}
			//$gamepic = substr($gamepic,1);
			$gamepic = implode(';',$gamepic);
			Addongame::db()->where('aid','=',$archive['id'])->update(array('gamepic'=>$gamepic));
		}
		
		return true;
	}
	
	/**
	 * 同步游戏属性
	 */
	protected static function addArchiveGameInfo($dede_game_typeid,$game,$game_tags,$device)
	{
		 return self::saveArchiveGameInfo($dede_game_typeid, $game, $game_tags, $device, 'add');
	}
	
	protected static function saveArchiveGameInfo($dede_game_typeid,$game,$game_tags,$device,$op)
	{
		//游戏类型
		$game_type = GameType::db()->lists('typename','id');
		
		$data = array();
		$data['aid'] = $dede_game_typeid;
		$data['typeid'] = 4;
		$data['version'] = $game['version'];
		$data['size'] = $game['size'];
		$data['language'] = $game['language'] == '1' ? '中文' : ($game['language']=='2' ? '英文' : '其他');
		$data['pricetype'] = $game['pricetype'] == '1' ? '免费' : ($game['pricetype']=='2' ? '限免' : '收费');
		$data['price'] = $game['price'] ? : '';
		$data['downtimes'] = $game['downtimes'];
		$data['company'] = $game['company'];
		$data['score'] = $game['score'];
		$data['gametype'] = $game_type[$game['type']];
		$data['downurl'] = $game['downurl'] ? : '';
		//$data['apkurl'] = $device=='android' ? ($game['apkurl']?:'') : '';
		$data['oldprice'] = $game['oldprice'] ? : '';
		$data['body'] = $game['editorcomt'];
		$data['feature'] = is_array($game_tags) ? implode(',',$game_tags) : '';
		$data['weekdown'] = $game['weekdown'];
		$data['commenttimes'] = $game['commenttimes'];
		$data['alphabet'] = $dede_game_typeid;
		$result = $op=='add' ? Addongame::db()->insert($data) : Addongame::db()->where('aid','=',$dede_game_typeid)->update($data); 
		return $result ? true : false;  
	}
	
	public static function updateArchiveGameInfo($game_id,array $data,$device)
	{		
		$yxdid = $device=='ios' ? 'g_'.$game_id : 'apk_'.$game_id;
		$archive = Archives::db()->where('yxdid','=',$yxdid)->first();
		if(!$archive) return false;
		$fields = array('version','size','downurl','apkurl');
		$input = array();
		foreach($data as $field=>$value){
			if(in_array($field,$fields)){
				$input[$field] = $value;
			}
		}
		if($input){
			Addongame::db()->where('aid','=',$archive['id'])->update($input);
			return true;
		}
		return false;
	}
	
	protected static function rollBack($arctiny_id=0,$arctype_id=0,$archives_id=0)
	{		
		if($arctiny_id){
			Arctiny::db()->where('id','=',$arctiny_id)->delete();
		}
		if($arctype_id){
			Arctype::db()->where('id','=',$arctype_id)->delete();
		}
		if($archives_id){
			Archives::db()->where('id','=',$archives_id)->delete();
		}
		
	}
	
	protected static function writeErrorLog($message)
	{
		//echo $message;
		$log_doc = storage_path() . 'logs/';
		$file_suffix = date('Y-m-d',time());		
		$log_file = $log_doc.'log_error_'.$file_suffix.'.txt';	
		if(!file_exists($log_file)){ //检测log.txt是否存在			
			touch($log_file);
			chmod($log_file, 0777);
		}		
		$message = date('Y-m-d H:i:s') . ' ' . $message;
		@file_put_contents($log_file,$message."\r\n",FILE_APPEND);
	}
	
	protected static function writeSuccessLog($message)
	{
		//echo $message;
		$log_doc = storage_path() . 'logs/';
		$file_suffix = date('Y-m-d',time());		
		$log_file = $log_doc.'log_success_'.$file_suffix.'.txt';	
		if(!file_exists($log_file)){ //检测log.txt是否存在			
			touch($log_file);
			chmod($log_file, 0777);
		}	
		$message = date('Y-m-d H:i:s') . ' ' . $message;	
		@file_put_contents($log_file,$message."\r\n",FILE_APPEND);
	}
	
	/**
	 * 同步标签
	 */
	protected static function addTags($game_tags,$dede_game_typeid)
	{
		return self::saveTags($game_tags, $dede_game_typeid,'add');
	}
	
	protected static function saveTags($game_tags,$dede_game_typeid,$op)
	{		
		if($op=='edit') Taglist::db()->where('aid','=',$dede_game_typeid)->delete();
		if(!$game_tags) return false;
		$res = Tagindex::db()->where('taggroup','=','游戏特征')->whereIn('tag',$game_tags)->get();
		$data = array();
		foreach($res as $row){
			$tmp = array();
			$tmp['tid'] = $row['id'];
			$tmp['aid'] = $dede_game_typeid;
			$tmp['typeid'] = 4;
			$tmp['arcrank'] = -1;
			$tmp['tag'] = $row['tag'];
			$tmp['taggroup'] = '游戏特征';			
			$data[] = $tmp;
		}
		if($data){
			Taglist::db()->insert($data);
		}
		return false;
	}
	
	
	
	protected static function editGameData($game,$game_tags,$device)
	{		
		$yxdid = $device=='ios' ? 'g_'.$game['id'] : 'apk_'.$game['id'];
		$map = array('yxdid'=>$yxdid,'channel'=>3,'typeid'=>4);

		$archive = Archives::db()->where('yxdid','=',$yxdid)->where('channel','=',3)->where('typeid','=',4)->first();
		if($archive){
			if($archive['writer']!=$game['id']) return -1;
		}else{
			return self::addGameData($game, $device, $game_tags);
		}
		$dede_game_typeid = $archive['id'];
		self::saveArchives($dede_game_typeid,0, $game, $device,'edit');
		self::saveArchiveGameInfo($dede_game_typeid, $game, $game_tags, $device, 'edit');
		//同步修改栏目
		Arctype::db()->where('refarc','=',$dede_game_typeid)->where('id','=',$archive['reftype'])->update(array('typename'=>$game['shortgname']));
		self::saveTags($game_tags,$archive['id'],'edit');
		return true;
		
	}
}