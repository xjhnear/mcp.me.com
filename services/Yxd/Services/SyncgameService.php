<?php
namespace Yxd\Services;

use modules\game\models\GameModel;
use Yxd\Models\SyncgameModel;
use Illuminate\Support\Facades\Config;

/**
 * 游戏服务
 */
class SyncgameService extends Service{
	
	public static function addSyncData($gid){
		if(!$gid) return false;
		$gameinfo = GameModel::getInfo($gid);
		if(!$gameinfo) return false;
		
		self::dbYxdMaster()->transaction(function()use($gameinfo){
			$arctiny_data = array(
					'channel' => 3,
					'typeid' => 4,
					'senddate' => time()
			);
			$arctiny_id = SyncgameModel::addArctiny($arctiny_data);
			
			$arctype_data = array(
				'reid' => 4,
				'topid' => 1,
				'typename' => $gameinfo['shortgname'],
				'typedir' => '/a/apple/game/'.$arctiny_id,
				'channeltype' => 3,
				'templist' => 'mobile/article_game.htm',
				'temparticle' => '{"1":"mobile/article_game.htm"}',
			 	'namerule' => '{"1":"{typedir}/{aid}.shtml"}',
			 	'namerule2' => '{typedir}/list_{page}.shtml',
			 	'modname' => "default",
			 	'moresite' => "1",
			 	'sitepath' => "/a/apple",
			 	'siteurl' => "http://www.youxiduo.com",
			 	'urlrule' => '{"1":"{typedir}/{aid}.shtml"}',
			 	'mainrule' => 1,
			 	'refarc' =>	$arctiny_id,
			 	'isallow' => 0,
			 	'seotitle' => $gameinfo['shortgname']."官网_".$gameinfo['shortgname']."攻略_".$gameinfo['shortgname']."礼包_激活码_下载_游戏多".$gameinfo['shortgname']."攻略资料站",
	 			'keywords' => $gameinfo['shortgname'].",".$gameinfo['shortgname']."官网,".$gameinfo['shortgname']."攻略,".$gameinfo['shortgname']."礼包,".$gameinfo['shortgname']."下载,".$gameinfo['shortgname']."激活码",
	 			'description' => "游戏多".$gameinfo['shortgname']."攻略站为你提供最新最好的".$gameinfo['shortgname']."官方新闻公告、".$gameinfo['shortgname']."攻略、".$gameinfo['shortgname']."游戏测评，".$gameinfo['shortgname']."激活码，".$gameinfo['shortgname']."礼包，".$gameinfo['shortgname']."下载，".$gameinfo['shortgname']."游戏视频攻略等内容。",
			);
			$arctype_id = SyncgameModel::addArctype($arctype_data);
			
			//游戏攻略
			$arctype_data['reid']			=	$arctype_id;
			$arctype_data['channeltype']	=	4;
			$arctype_data['isallow']		=	1;
			$arctype_data['templist']		=	"mobile/list.htm";
			$arctype_data['temparticle'] 	= 	'{"1":"mobile/article.htm"}';
				
			$arctype_data['typename']		=	"攻略";
			$arctype_data['typedir']		=	"/a/apple/game/".$arctiny_id."/gonglue";
			$arctype_data['seotitle']    	= 	$gameinfo['shortgname']."攻略_".$gameinfo['shortgname']."辅助_".$gameinfo['shortgname']." 破解_游戏多".$gameinfo['shortgname']."专区";
			$arctype_data['keywords']    	= 	$gameinfo['shortgname']."攻略,".$gameinfo['shortgname']."辅助,".$gameinfo['shortgname']."破解";
			$arctype_data['description']	= 	"游戏多".$gameinfo['shortgname']."官网合作专区为你提供".$gameinfo['shortgname']."攻略、".$gameinfo['shortgname']."辅助、".$gameinfo['shortgname']."破解、".$gameinfo['shortgname']."新手指南、".$gameinfo['shortgname']."辅助教程，告诉你".$gameinfo['shortgname']."应该怎么玩，最新最全的".$gameinfo['shortgname']."攻略就上游戏多。";
			$gonglueid = SyncgameModel::addArctype($arctype_data);
			
			//游戏新闻
			$arctype_data['typename']		=	"新闻";
			$arctype_data['typedir']		=	"/a/apple/game/".$arctiny_id."/xinwen";
			$arctype_data['seotitle']    	= 	$gameinfo['shortgname']."资讯_".$gameinfo['shortgname']."新闻_".$gameinfo['shortgname']."公告_游戏多".$gameinfo['shortgname']."专区";
			$arctype_data['keywords']    	= 	$gameinfo['shortgname']."资讯,".$gameinfo['shortgname']."新闻,".$gameinfo['shortgname']."公告,".$gameinfo['shortgname']."更新";
			$arctype_data['description'] 	= 	"游戏多".$gameinfo['shortgname']."官网合作专区为你提供最新的".$gameinfo['shortgname']."官网资讯、".$gameinfo['shortgname']."新闻、".$gameinfo['shortgname']."礼包、".$gameinfo['shortgname']."激活码，更多".$gameinfo['shortgname']."游戏新闻就上游戏多。";
			$xinwenid = SyncgameModel::addArctype($arctype_data);
			
			//精彩视频
			$arctype_data['typename']		=	"精彩视频";
			$arctype_data['typedir']		=	"/a/apple/game/".$arctiny_id."/shipin";
			$arctype_data['templist']    	= 	"mobile/list_shipin.htm";
			$arctype_data['temparticle'] 	= 	'{"1":"mobile/article_shipin.htm"}';
			$arctype_data['seotitle']    	= 	$gameinfo['shortgname']."游戏资讯_".$gameinfo['shortgname']."玩法视频教程_".$gameinfo['shortgname']."视频攻略_游戏多".$gameinfo['shortgname']."专区";
			$arctype_data['keywords']	    = 	$gameinfo['shortgname']."玩法教程,".$gameinfo['shortgname']."视频教程,".$gameinfo['shortgname']."攻略,".$gameinfo['shortgname']."视频攻略,".$gameinfo['shortgname']."怎么玩";
			$arctype_data['description'] 	= 	"游戏多".$gameinfo['shortgname']."官网合作专区为你提供最新的".$gameinfo['shortgname']."玩法视频教程、".$gameinfo['shortgname']."游戏资讯、".$gameinfo['shortgname']."视频攻略、教你".$gameinfo['shortgname']."怎么玩、".$gameinfo['shortgname']."怎么过，更多".$gameinfo['shortgname']."游戏攻略就上游戏多。";
			$shipinid = SyncgameModel::addArctype($arctype_data);
		
			#扩展栏目开始
			//游戏图鉴
			$arctype_data['typename']		=	"图鉴";
			$arctype_data['typedir']		=	"/a/apple/game/".$arctiny_id."/tujian";
			$arctype_data['seotitle']    	= 	$gameinfo['shortgname']."资料大全_游戏多".$gameinfo['shortgname']."专区";
			$arctype_data['keywords']    	= 	$gameinfo['shortgname']."图鉴大全,".$gameinfo['shortgname']."数据大全,".$gameinfo['shortgname']."数据库";
			$arctype_data['description'] 	= 	"游戏多".$gameinfo['shortgname']."官网合作专区为你提供最全面的".$gameinfo['shortgname']."图鉴、".$gameinfo['shortgname']."游戏资料，更多".$gameinfo['shortgname']."图鉴数据查询就上游戏多。";
			$tujianid =	SyncgameModel::addArctype($arctype_data);
			
			//游戏资料
			$arctype_data['typename']		=	"资料";
			$arctype_data['typedir']		=	"/a/apple/game/".$arctiny_id."/ziliao";
			$arctype_data['seotitle']    	= 	$gameinfo['shortgname']."游戏指南_".$gameinfo['shortgname']."怎么玩_".$gameinfo['shortgname']."系统介绍_".$gameinfo['shortgname']."基础玩法_游戏多".$gameinfo['shortgname']."专区";
			$arctype_data['keywords']    	= 	$gameinfo['shortgname']."新手指南,".$gameinfo['shortgname']."系统介绍,".$gameinfo['shortgname']."怎么玩,".$gameinfo['shortgname']."玩法攻略";
			$arctype_data['description']	= 	"游戏多".$gameinfo['shortgname']."官网合作专区为你提供最全面的".$gameinfo['shortgname']."系统介绍、".$gameinfo['shortgname']."新手指南、教你".$gameinfo['shortgname']."怎么玩、".$gameinfo['shortgname']."基础玩法，更多".$gameinfo['shortgname']."新手教程就上游戏多。";
			$ziliaoid = SyncgameModel::addArctype($arctype_data);
			
			//游戏图片
			$arctype_data['typename']		=	"图片";
			$arctype_data['typedir']		=	"/a/apple/game/".$arctiny_id."/tupian";
			$arctype_data['seotitle']    	= 	$gameinfo['shortgname']."图鉴_".$gameinfo['shortgname']."截图_".$gameinfo['shortgname']."画面_游戏多".$gameinfo['shortgname']."专区";
			$arctype_data['keywords']    	= 	$gameinfo['shortgname']."图鉴,".$gameinfo['shortgname']."截图,".$gameinfo['shortgname']."画面怎么样";
			$arctype_data['description'] 	= 	"游戏多".$gameinfo['shortgname']."官网合作专区为你提供".$gameinfo['shortgname']."游戏截图、".$gameinfo['shortgname']."图鉴、".$gameinfo['shortgname']."游戏画面，更多".$gameinfo['shortgname']."游戏图片就上游戏多。";
			$tupianid = SyncgameModel::addArctype($arctype_data);
			
			$game_tags = GameModel::getGametags($gameinfo['id']);
			$game_tag =	$game_tags ? implode(",", $game_tags) : "";
			
			$archives_data = array(
				'channel' => 3,
				'typeid' => 4,
				'senddate' => time(),
				'reftype' => $arctype_id,
				'title'	=>	$gameinfo['gname'],
				'shorttitle' =>	$gameinfo['shortgname'],
				'senddate' => $gameinfo['addtime'],
				'pubdate' => $gameinfo['updatetime'],
				'writer' => $gameinfo['id'],
				'goodpost' => 0,
				'badpost' => 0,
				'description' => SyncgameService::filterCwanDesc($gameinfo['editorcomt']),
				'ismake' => 1,
				'arcrank' => -1,
				'keywords' => $gameinfo['shortgname'].",".$game_tag,
				'yxdid' => "g_".$gameinfo['id'],
				'id' => $arctiny_id
			);
			
			if ($gameinfo['ico']){
				$archives_data['litpic'] = Config::get('ueditor.imageUrlPrefix').$gameinfo['ico'];
			}else{
				$archives_data['litpic'] = "";
			}
			$archiveid = SyncgameModel::addArchives($archives_data);
			
			$game_type = Config::get('rule.game_types');
			
			$addongame_data = array(
				'aid' => $arctiny_id,
				'typeid' => 4,
				'version' => $gameinfo['version'],
				'size' => $gameinfo['size'],
				'downtimes' => $gameinfo['downtimes'],
				'company' => $gameinfo['company'],
				'score' => $gameinfo['score'],
				'gametype' => $game_type[$gameinfo['type']],
				'downurl' => $gameinfo['downurl'],
				'price' => $gameinfo['price'],
				'oldprice' => $gameinfo['oldprice'],
				'body' => $gameinfo['editorcomt'],
				'commenttimes' => $gameinfo['commenttimes'],
				'feature' => $game_tag,
				'weekdown' => $gameinfo['weekdown'],
				'alphabet' => $arctiny_id
			);
			if ($gameinfo['language'] == "1"){
				$addongame_data['language']	= "中文";
			}elseif($gameinfo['language'] == "2"){
				$addongame_data['language']	= "英文";
			}else{
				$addongame_data['language']	= "其他";
			}
			if ($gameinfo['pricetype'] == "1"){
				$addongame_data['pricetype'] = "免费";
			}elseif($gameinfo['pricetype'] == "2"){
				$addongame_data['pricetype'] = "限免";
			}else{
				$addongame_data['pricetype'] = "收费";
			}
			$addongameid = SyncgameModel::addAddongame($addongame_data);
			
			if($game_tags){
				foreach($game_tags as $row){
					$rs	= SyncgameModel::getTag($row);
					if ($rs['id']){
						$taglist = array(
							'tid' => $rs['id'],
							'aid' => $arctiny_id,
							'typeid' => 4,
							'arcrank' => -1,
							'tag' => $row,
							'taggroup' => "游戏特征"
						);
						SyncgameModel::addTaglist($taglist);
					}
				}
			}
		});
	}
	
	public static function editSyncData($gid){
		if(!$gid) return false;
		$gameinfo = GameModel::getInfo($gid);
		if(!$gameinfo) return false;
		$archiveinfo = SyncgameModel::getArchives($gameinfo['id']);
		if(!$archiveinfo) return false;
		$c_id = $archiveinfo['id'];
		
		$yxd_writer = $gameinfo['id'];
		if($yxd_writer != $archiveinfo['writer']) return false;
		
		self::dbYxdMaster()->transaction(function()use($gameinfo,$c_id,$archiveinfo){
			$game_tags = GameModel::getGametags($gameinfo['id']);
			$game_tag =	$game_tags ? implode(",", $game_tags) : "";
			
			$data = array(
				'title' => $gameinfo['gname'],
				'shorttitle' => $gameinfo['shortgname'],
				'pubdate' => $gameinfo['updatetime'],
				'description' => $gameinfo['editorcomt'],
				'keywords' => $gameinfo['shortgname'].",".$game_tag,
				'litpic' => $gameinfo['ico'] ? Config::get('ueditor.imageUrlPrefix').$gameinfo['ico'] : ''
			);

			SyncgameModel::updateArchives($c_id,$data);
			
			//修改时同步修改当前栏目名称
			SyncgameModel::updateArctype($c_id, $archiveinfo['reftype'],array('typename'=>$gameinfo['shortgname']));
			
			$game_type = Config::get('rule.game_types');
			
			$addondata = array(
				'version' => $gameinfo['version'],
				'size' => $gameinfo['size'],
				'downtimes' => $gameinfo['downtimes'],
				'company' => $gameinfo['company'],
				'score' => $gameinfo['score'],
				'gametype' => $game_type[$gameinfo['type']],
				'downurl' => $gameinfo['downurl'],
				'price' => $gameinfo['price'],
				'oldprice' => $gameinfo['oldprice'],
				'body' => $gameinfo['editorcomt'],
				'feature' => $game_tag
			);
			
			if ($gameinfo['language'] == "1"){
				$addondata['language'] = "中文";
			}elseif($gameinfo['language'] == "2"){
				$addondata['language'] = "英文";
			}else{
				$addondata['language'] = "其他";
			}
			
			if ($gameinfo['pricetype'] == "1"){
				$addondata['pricetype']	= "免费";
			}elseif($gameinfo['pricetype'] == "2"){
				$addondata['pricetype']	= "限免";
			}else{
				$addondata['pricetype']	= "收费";
			}

			SyncgameModel::updateAddongame($c_id,$addondata);
			SyncgameModel::delTaglist($c_id);
			
			if($game_tags){
				foreach($game_tags as $row){
					$rs	= SyncgameModel::getTag($row);
					if ($rs['id']){
						$taglist = array(
								'tid' => $rs['id'],
								'aid' => $c_id,
								'typeid' => 4,
								'arcrank' => -1,
								'tag' => $row,
								'taggroup' => "游戏特征"
						);
						SyncgameModel::addTaglist($taglist);
					}
				}
			}
		});
	}
	
	public static function picSyncData($gid){
		if(!$gid) return false;
		$gameinfo = GameModel::getInfo($gid);
		if(!$gameinfo) return false;
		$archiveinfo = SyncgameModel::getArchives($gameinfo['id']);
		if(!$archiveinfo) return false;
		$c_id = $archiveinfo['id'];
		
		$_game_pic = GameModel::getGamelitpic(0,$gameinfo['id']);
		$gamepic	=	"";
		
		if ($_game_pic){
			foreach ($_game_pic as $pic){
				$gamepic =  $gamepic.";".Config::get('ueditor.imageUrlPrefix').$pic['litpic'];
			}
			$gamepic = substr($gamepic, 1);
		}else{
			$gamepic = "";
		}
		
		SyncgameModel::updateAddongame($c_id, array('gamepic'=>$gamepic));
	}
	
	public static function appSyncData($gid){
		if(!$gid) return false;
		$gameinfo = GameModel::getInfo($gid);
		if(!$gameinfo) return false;
		$archiveinfo = SyncgameModel::getArchives($gameinfo['id']);
		if(!$archiveinfo) return false;
		$c_id = $archiveinfo['id'];
		
		$data = array(
			'oldprice' => $gameinfo['oldprice'],
			'version' => $gameinfo['version'],
			'size' => $gameinfo['size'],
			'price' => $gameinfo['price']
		);
		
		if ($gameinfo['pricetype'] == "1"){
			$data['pricetype']	=	"免费";
		}elseif($gameinfo['pricetype'] == "2"){
			$data['pricetype']	=	"限免";
		}else{
			$data['pricetype']	=	"收费";
		}
		
		SyncgameModel::updateAddongame($c_id, $data);
	}
	
	
	//过滤CWAN 文章主表 description中的特殊字符( html,空格,空格(&nbsp;),空字符(' ','　')及经过htmlspecialchars转换的特殊字符)
	public static function filterCwanDesc($content = ''){
		if(empty($content)){
			return '';
		}
		$new_content = $content;
		//需要过滤的字符
		$filter_chars = array('&nbsp;','&amp;','&quot;','&#039;','&lt;','&gt;',' ','　');
		//替换的字符
		$replace_chars = '';
		//先过滤html标签
		$new_content = ltrim(strip_tags($new_content));
		//过滤指定的特殊字符
		$new_content = str_replace($filter_chars, $replace_chars, $new_content);
		return $new_content;
	}
}