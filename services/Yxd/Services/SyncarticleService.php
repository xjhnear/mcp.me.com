<?php
namespace Yxd\Services;

//use modules\game\models\GameModel;
//use Illuminate\Support\Facades\Config;
use Youxiduo\Cms\Model\News;
use Yxd\Modules\Core\BaseService;
use Yxd\Models\SyncgameModel;
use Youxiduo\Cms\Model\Gonglue;
use Youxiduo\Cms\Model\Other;
use Youxiduo\Cms\Model\Opinion;
use Youxiduo\Cms\Model\GamesVideo;
use Youxiduo\Cms\Model\Videos;
use Youxiduo\Cms\GameInfo;
//use Youxiduo\Helper\Utility;
/**
 * 同步文章服务
 * @author jfj
 *
 */

class SyncarticleService extends BaseService
{
	const OP_ADD = 'add';
	const OP_EDIT = 'edit';
	const OP_DELETE = 'delete';
	
	/**
	 * 操作新闻文章
	 * @param int $id
	 */
	public static function syncNews($id){
		//查询文章内容
		$article_type = 'news';
		$result = News::getDetails($id);
		
		if(empty($result)){
			return false;	
		}
		if($result['pid']=='0' && $result['zxtype']=='1'){
			//$type = '业内资讯';
			$typeid = 26819;
			$arc_yxdid = 'zx_'.$id;
		}elseif ($result['pid']=='0' && $result['zxtype']=='2'){
			//$type = '游戏新闻';
			$typeid = 26825;
			$arc_yxdid = 'zx_'.$id;
		}elseif($result['pid']=='-1'){
			//系列文章栏目   同步为栏目
			return self::addMune($id,$article_type);
		}elseif($result['pid']>0){
			//系列文章
			//查找游戏根目录栏目 子栏目
			$yxdid = self::getYxdId($result['pid'], $article_type);
			$type2 = self::checkMune($yxdid);
			if(!$type2){
				$typeid = self::addMune($result['pid'],$article_type);
			}else{
				$typeid = $type2['id'];
			}
			//获取文章标示
			$arc_yxdid = self::getYxdId($id, $article_type);
		}else{
			//游戏文章
			//查找游戏根目录栏目
			
			$typename = self::getTypeName($article_type);
			//获取mobile中游戏信息
			$gamearc = GameInfo::getMobileGame($result['gid'] , $result['agid']);
			if(empty($gamearc)){
				self::writeSuccessLog('新闻文章【'.$id.'】没有找到mobile游戏ID');
				return false;
			} 
			//查询游戏的ID
			$fristData = self::currFatherMune($gamearc['id'],$typename);
			$typeid = $fristData['id'];
			//获取文章标示
			$arc_yxdid = self::getYxdId($id, $article_type);
		}
		
		if(empty($typeid)){
			self::writeSuccessLog('新闻文章【'.$id.'】没有找到mobile栏目ID');
			return false;
		}
		$result['typeid'] = $typeid;
		$result['yxdid'] = $arc_yxdid;
		//查询是否同步此文章
		$rs = self::getArchivess($arc_yxdid);
		//处理键名不对称
		self::validData($result, $article_type);
		//生产ID
		if(empty($rs)){
			$op = self::OP_ADD;
			$aid = self::getTiny($result,$op);
			$result['id'] = $aid;
		}else{
			$op = self::OP_EDIT;
			$result['id'] = $rs['id'];
			$aid = self::getTiny($result,$op);
		}
		//操作主表数据
		$archivesRs = self::getArchives($result,$op);
		//操作附加表数据
		$addonmarticleRs = self::getAddonmarticle($result,$op);
		//返回操作结果
		if($aid == 0 && $archivesRs == 0 && $addonmarticleRs == 0){
			self::writeSuccessLog("新闻文章同步失败【{$id}】");
			return false;
		}
		return true;
		
	}
	/**
	 * 操作攻略文章
	 * @param int $id
	 */
	public static function syncGonglue($id){
		//查询文章内容
		$article_type = 'guide';
		$result = Gonglue::getDetails($id);
	
		//$bestArc = self::getBestAtcicle($id,$article_type);
		if(empty($result)){
			return false;
		}
		if($result['pid']=='-1'){
			//系列文章栏目   同步为栏目
			return self::addMune($id,$article_type);
		}elseif($result['pid']>0){
			//系列文章
			//查找游戏根目录栏目 子栏目
			$yxdid = self::getYxdId($result['pid'], $article_type);
			$type2 = self::checkMune($yxdid);
			if(!$type2){
				$typeid = self::addMune($result['pid'],$article_type);
			}else{
				$typeid = $type2['id']; 
			}
		}else{
			//游戏文章
			//查找游戏根目录栏目
			$typename = self::getTypeName($article_type);
			//获取mobile中游戏信息
			$gamearc = GameInfo::getMobileGame($result['gid'] , $result['agid']);
			if(empty($gamearc)){
				self::writeSuccessLog('攻略文章【'.$id.'】没有找到mobile游戏ID');
				return false;
			}
			//查询游戏的ID
			$fristData = self::currFatherMune($gamearc['id'],$typename);
			$typeid = $fristData['id'];
		}
		if(empty($typeid)){
			self::writeSuccessLog('攻略文章【'.$id.'】没有找到mobile栏目ID');
			return false;
		}
		//获取文章标示
		$arc_yxdid = self::getYxdId($id, $article_type);
		$result['typeid'] = $typeid;
		$result['yxdid'] = $arc_yxdid;
		//查询是否同步此文章
		$rs = self::getArchivess($arc_yxdid);
		//处理键名不对称
		self::validData($result, $article_type);
		//生产ID
		if(empty($rs)){
			$op = self::OP_ADD;
			$aid = self::getTiny($result,$op);
			$result['id'] = $aid;
		}else{
			$op = self::OP_EDIT;
			$result['id'] = $rs['id'];
			$aid = self::getTiny($result,$op);
		}
		
		//操作主表数据
		$archivesRs = self::getArchives($result,$op);
		//操作附加表数据
		$addonmarticleRs = self::getAddonmarticle($result,$op);
		//返回操作结果
		if($aid == 0 && $archivesRs == 0 && $addonmarticleRs == 0){
			self::writeSuccessLog("新闻文章同步失败【{$id}】");
			return false;
		}
		return true;
	
	}
	/*
	 * 操作其他文章
	 * @param int $id
	 * @param string $article_type
	 * @return boolean
	 */
	public static function syncOther($id,$article_type){
		//查询文章内容
		$result = Other::getDetails($id);
		
		if(empty($result)){
			return false;
		}
		
		if($result['pid']=='-1'){
			//系列文章栏目   同步为栏目
			return self::addMune($id,$article_type);
		}elseif($result['pid']>0){
			//系列文章
			//查找游戏根目录栏目 子栏目
			$yxdid = self::getYxdId($result['pid'], $article_type);
			$type2 = self::checkMune($yxdid);
			if(!$type2){
				$typeid = self::addMune($result['pid'],$article_type);
			}else{
				$typeid = $type2['id'];
			}
			//获取文章标示
			$arc_yxdid = self::getYxdId($id, $article_type);
		}else{
			//游戏文章
			//查找游戏根目录栏目
			$typename = self::getTypeName($article_type);
			//获取mobile中游戏信息
			$gamearc = GameInfo::getMobileGame($result['gid'] , $result['agid']);
			if(empty($gamearc)){
				self::writeSuccessLog('其他文章【'.$id.'】没有找到mobile游戏ID');
				return false;
			}
			//查询游戏的ID
			$fristData = self::currFatherMune($gamearc['id'],$typename);
			$typeid = $fristData['id'];
			//获取文章标示
			$arc_yxdid = self::getYxdId($id, $article_type);
		}
		if(empty($typeid)){
			self::writeSuccessLog('其他文章【'.$id.'】没有找到mobile栏目ID');
			return false;
		}
		$result['typeid'] = $typeid;
		$result['yxdid'] = $arc_yxdid;
		//查询是否同步此文章
		$rs = self::getArchivess($arc_yxdid);
		//处理键名不对称
		self::validData($result, $article_type);
		//生产ID
		if(empty($rs)){
			$op = self::OP_ADD;
			$aid = self::getTiny($result,$op);
			$result['id'] = $aid;
		}else{
			$op = self::OP_EDIT;
			$result['id'] = $rs['id'];
			$aid = self::getTiny($result,$op);
		}
		//操作主表数据
		$archivesRs = self::getArchives($result,$op);
		//操作附加表数据
		$addonmarticleRs = self::getAddonmarticle($result,$op);
		//返回操作结果
		if($aid == 0 && $archivesRs == 0 && $addonmarticleRs == 0){
			self::writeSuccessLog("其他文章同步失败【{$id}】");
			return false;
		}
		return true;
	}
	/**
	 * 操作评测文章
	 * @param int $id
	 */
	public static function syncOpinion($id){
		//查询文章内容
		$article_type = 'opinion';
		$result = Opinion::getDetails($id);
		if(empty($result)){
			return false;
		}
		$typeid = 6;
		//获取mobile中游戏信息
		$gamearc = GameInfo::getMobileGame($result['gid'] , $result['agid']);
		if(empty($gamearc)){
			self::writeSuccessLog('评测文章【'.$id.'】没有找到mobile游戏ID');
			return false;
		}
		//获取文章标示
		$arc_yxdid = self::getYxdId($id, $article_type);
		$result['typeid'] = $typeid;
		$result['yxdid'] = $arc_yxdid;
		$result['gameid'] = $gamearc['id'];
		$result['game'] = $gamearc['title'];
		//查询是否同步此文章
		$rs = self::getArchivess($arc_yxdid);
		//处理键名不对称
		self::validData($result, $article_type);
		//生产ID
		if(empty($rs)){
			$op = self::OP_ADD;
			$aid = self::getTiny($result,$op);
			$result['id'] = $aid;
		}else{
			$op = self::OP_EDIT;
			$result['id'] = $rs['id'];
			$aid = self::getTiny($result,$op);
		}
		//操作主表数据
		$archivesRs = self::getArchives($result,$op);
		//操作附加表数据
		$addonmarticleRs = self::getAddonmarticle($result,$op);
		//返回操作结果
		if($aid == 0 && $archivesRs == 0 && $addonmarticleRs == 0){
			self::writeSuccessLog("评测文章同步失败【{$id}】");
			return false;
		}
		return true;
	
	}

	/**
	 * 操作游戏视频文章
	 * @param int $id
	 */
	public static function syncGamesVideo($id){
		//查询文章内容
		$article_type = 'gamevideo';
		$result = GamesVideo::getDetails($id);
		if(empty($result)){
			return false;
		}
		//$video_url	=	get_youku_video_url($result['body'],'3');
		$video_obj	=	self::make_new_video_block($result['video'],610,498,'2');
		$result['video']	=	"<p style='text-align: center'>".$video_obj."</p>";
		//查找游戏根目录栏目
		$typename = self::getTypeName($article_type);
		//获取mobile中游戏信息
		$gamearc = GameInfo::getMobileGame($result['gid'] , $result['agid']);
		if(empty($gamearc)){
			self::writeSuccessLog('游戏视频文章【'.$id.'】没有找到mobile游戏ID');
			return false;
		}
		
		//查询游戏的ID
		$fristData = self::currFatherMune($gamearc['id'],$typename);
		$typeid = $fristData['id'];
		if(empty($typeid)){
			self::writeSuccessLog('游戏视频文章【'.$id.'】没有找到mobile蓝，栏目ID');
			return false;
		}
		//获取文章标示
		$arc_yxdid = self::getYxdId($id, $article_type);
		$result['typeid'] = $typeid;
		$result['yxdid'] = $arc_yxdid;
		//查询是否同步此文章
		$rs = self::getArchivess($arc_yxdid);
		//处理键名不对称
		self::validData($result, $article_type);
		//生产ID
		if(empty($rs)){
			$op = self::OP_ADD;
			$aid = self::getTiny($result,$op);
			$result['id'] = $aid;
		}else{
			$op = self::OP_EDIT;
			$result['id'] = $rs['id'];
			$aid = self::getTiny($result,$op);
		}
	
		//操作主表数据
		$archivesRs = self::getArchives($result,$op);
		//操作附加表数据
		$addonmarticleRs = self::getAddonmarticle($result,$op);
		//返回操作结果
		if($aid == 0 && $archivesRs == 0 && $addonmarticleRs == 0){
			self::writeSuccessLog("新闻文章同步失败【{$id}】");
			return false;
		}
		return true;
	
	}
	
	/**
	 * 操作视频文章
	 * @param int $id
	 */
	public static function syncVideos($id){
		//查询文章内容
		$article_type = 'video';
		$result = Videos::getDetails($id);
		if(empty($result)){
			return false;
		}
		$video_obj	=	self::make_new_video_block($result['video'],610,498,'2');
		$result['video']	=	"<p style='text-align: center'>".$video_obj."</p>";
		$cawn_video_id	=	array(
				"2"	=>	"356",	// 新游物语=> 美女
				"1"	=>	"357",	// 宅男游戏厅=>宅男
				"3"	=>	"358",	// 恶搞视频=>恶搞
				"4"	=>	"359"	// 其他视频=>其他
		);
		$cawn_video_name	=	array(
				"2"	=>	"新游物语",
				"1"	=>	"宅男游戏厅",
				"3"	=>	"恶搞",
				"4"	=>	"其他"
		);
		$result['tid']		=	$cawn_video_id[$result['type']];
		$result['aid']		=	'';
		$typeid = 5;
		$result['tag']		=	$cawn_video_name[$result['type']];
		$result['taggroup']	=	"视频类型";
		
		//获取文章标示
		$arc_yxdid = self::getYxdId($id, $article_type);
		$result['typeid'] = $typeid;
		$result['yxdid'] = $arc_yxdid;
		//查询是否同步此文章
		$rs = self::getArchivess($arc_yxdid);
		//处理键名不对称
		self::validData($result, $article_type);
		//生产ID
		if(empty($rs)){
			$op = self::OP_ADD;
			$aid = self::getTiny($result,$op);
			$result['id'] = $aid;
		}else{
			$op = self::OP_EDIT;
			$result['id'] = $rs['id'];
			$aid = self::getTiny($result,$op);
		}
		
		//操作主表数据
		$archivesRs = self::getArchives($result,$op);
		//操作附加表数据
		$addonmarticleRs = self::getAddonmarticle($result,$op);
		
		$tagListRs = self::getTagList($result,$op);
		//返回操作结果
		if($aid == 0 && $archivesRs == 0 && $addonmarticleRs == 0 && $tagListRs == 0){
			self::writeSuccessLog("视频同步失败【{$id}】");
			return false;
		}
		return $aid;
	
	}

	/**
	 * 处理键名不对称
	 * @param array $data
	 * @param string $article_type
	 */
	public static function validData(&$data , $article_type){
	
		switch ($article_type)
		{
			case "opinion":
				$data['title'] = $data['ftitle'];
				unset($data['ftitle']);
				break;
			case "guide":
				$data['title'] = $data['gtitle'];
				unset($data['gtitle']);
				break;
			case "gamevideo":
				$data['content'] = $data['video'];
				$data['litpic'] = $data['ico'];
				unset($data['video']);
				unset($data['ico']);
				break;
			case "video":
				$data['title'] = $data['vname'];
				$data['content'] = $data['video'];
				unset($data['vname']);
				unset($data['video']);
				break;
				/* case "news":
				 case "tujian":
				case "info":
				case "picture":
				default: */
		}
	}
	/**
	 * 查询单个文章信息
	 * @param string $yxdid
	 */
	public static function getArchivess($yxdid){
		return self::dbYxdMaster()->table('archives')->where('yxdid',$yxdid)->where('channel',4)->first();
	}
	/**
	 * 查询多个文章信息
	 * @param array $yxdid
	 */
	public static function getArchivesArr($yxdid){
		return self::dbYxdMaster()->table('archives')->whereIn('yxdid',$yxdid)->where('channel',4)->get();
	}
	
	/**
	 * 获取best端数据
	 * @param int $article_id	文章ID
	 * @param string $article_type	所属栏目
	 */
	public static function getBestAtcicle($article_id,$article_type){
		$results = array();
		switch ($article_type)
		{
			case "opinion":
				$results	= 	self::dbYxdMaster()->table('feedback');
				break;
			case "guide":
				$Obj	= 	self::dbYxdMaster()->table('gonglue');
				break;
			case "news":
				$Obj	= 	self::dbYxdMaster()->table('news');
				break;
			case "tujian":
			case "info":
			case "picture":
				$Obj	= 	self::dbYxdMaster()->table('gamesarticle');
				break;
			case "gamevideo":
				$Obj	= 	self::dbYxdMaster()->table('gamesvideo');
				break;
			case "video":
				$Obj	= 	self::dbYxdMaster()->table('videos');
				break;
				//mobile 里面专区栏目信息
			case "mtype":
				$Obj	= 	self::dbYxdMaster()->table('mtype');
				break;
			default:
				$Obj = '';
				break;
		}
		if(!empty($Obj)){
			$results = $Obj -> where("id = '".$article_id."'")->find();
		}else{
			$results = '';
		}
		return $results;
	}
	
	/**
	 * 获取某款游戏再mobile中的所有栏目
	 * @param number $aid
	 * @return boolean or array
	 */
	public static function getMobileGameMuneID($aid=0){
		if($aid!=0){
			return self::dbYxdMaster()->table('arctype')->where('refarc',$aid)->get();
		}
		return false;	
	}
	/**
	 * 获取所要添加的栏目的父栏目的ID
	 * @param array $gameId
	 * @param string $typename
	 * @return boolean|array
	 */
	public static function currFatherMune($gameId , $typename){
		//$fristData = self::dbYxdMaster()->table('arctype')->where('refarc',$gameId['id'])->where('typename',$typename)->first();
		
		$fristData = GameInfo::currFatherMune($gameId, $typename);
		if(empty($fristData)){
			self::writeSuccessLog('同步系列['.$gameId['title'].']'.'获取父栏目ID失败');
			return false;
		}
		return $fristData;
	}
	
	/**
	 * 检查栏目是否同步
	 * @param string $yxdid
	 * @return boolean
	 */
	public static function checkMune($yxdid=''){
		if($yxdid!=''){
			//存在yxdid重复   导致 查找栏目ID出错 需给该字段值加前缀  需要优化
			return self::dbYxdMaster()->table('arctype')->where('yxdid',$yxdid)->first();
			//print_r(self::dbYxdMaster()->getQueryLog());exit;
		}
		return false;
	}
	
	public static function addMune($id,$article_type){
		switch ($article_type)
		{
			case "guide":
				$result = Gonglue::getDetails($id);
				break;
			case "news":
				$result = News::getDetails($id);
				break;
			case "tujian":
			case "info":
			case "picture":
				$result = Other::getDetails($id);
				break;
		}
		
		if(empty($result)){
			return false; 
		}
		
		//处理键名不对称
		self::validData($result, $article_type);
		$typename = self::getTypeName($article_type);
		//获取mobile中游戏信息
		$gamearc = GameInfo::getMobileGame($result['gid'] , $result['agid']);
		if(empty($gamearc)){
			self::writeSuccessLog('系列文章【'.$id.'】没有找到mobile游戏ID');
			return false;
		}
		//查询游戏的ID
		$fristData = self::currFatherMune($gamearc['id'],$typename);
		//栏目标示
		$yxdid = self::getYxdId($id, $article_type);
		//组装返回数据
		$data_type = $fristData;
		$data_type['reid'] = $fristData['id'];
		$data_type['typename'] = $result['title'];
		$data_type['typedir'] = $fristData['typedir'] . '/'.$result['webcatedir'];
		$data_type['yxdid'] = $yxdid;
		if($result['title']){
			$data_type['seotitle'] = '{arctitle}图鉴_{arctitle}截图_{arctitle}画面_游戏多_{arctitle}专区';
			$data_type['keywords'] = '{arctitle}图鉴,{arctitle}截图,{arctitle}画面怎么样,{arctitle}数据库';
			$data_type['description'] = '游戏多{arctitle}官方合作专区为你提供最全面的{arctitle}游戏截图、{arctitle}图鉴，{arctitle}游戏画面，更多的{arctitle}游戏图片就上游戏多';
			$data_type['seotitle'] = str_replace('{arctitle}',$result['title'],$data_type['seotitle']);
			$data_type['keywords'] = str_replace('{arctitle}',$result['title'],$data_type['keywords']);
			$data_type['description'] = str_replace('{arctitle}',$result['title'],$data_type['description']);
		}
		unset($data_type['id']);
		//查询是否存在改栏目不存在创建
		$type2 = self::checkMune($yxdid);
		if($type2){
			return SyncgameModel::updateArctype($gamearc['id'], $type2['id'], $data_type);
		}else{
			return SyncgameModel::addArctype($data_type);
		}
	}

	/**
	 * 获取栏目名称
	 * @param string $article_type
	 * @return string
	 */
	public static function getTypeName($article_type) {
		$typeArr =  array("gamevideo"=>"精彩视频","guide"=>"攻略","news"=>"新闻","tujian"=>"图鉴","info"=>"资料","picture"=>"图片");
		//$typeName = array_search($article_type,$typeArr) ? array_search($article_type,$typeArr) : '';
		$typeName = isset($typeArr[$article_type]) ? $typeArr[$article_type] : '';
		return $typeName;
	}
	
	/**
	 * 获取mobile 里的关联字段值
	 * @param unknown $old_aid  文章ID
	 * @param unknown $old_artType 文章类型
	 * @return string
	 */
	public static function getYxdId($article_id,$article_type) {
		$typeArr =  array("gamevideo"=>"vg_","video"=>"v_","opinion"=>"a_","guide"=>"gl_","news"=>"f_","tujian"=>"tj_","info"=>"zl_","picture"=>"tp_");
		$old_yxdid = isset($typeArr[$article_type]) ? $typeArr[$article_type] . $article_id : '';
		return $old_yxdid;
	}

	/**
	 * 产生ID
	 * @param array $data
	 * @param string $op
	 * @return int
	 */
	public static function getTiny($data,$op = self::OP_ADD){
		$tinyData['typeid'] = $data['typeid'];
		if($op != self::OP_EDIT){
			$tinyData['typeid2'] = 0;
			$tinyData['arcrank'] = '-1';
			$tinyData['channel'] = 4;
			$tinyData['senddate'] = empty($data['addtime']) ? '' : $data['addtime'] ;
			$tinyData['sortrank'] = 0;
			$tinyData['mid'] = 0;
			//添加
			
			$id = self::dbYxdMaster()->table('arctiny')->insertGetId($tinyData);
			//$id = SyncgameModel::addArctiny($tinyData);
		}else{
			//修改
			$id = $data['id'] ; 
			$re = self::dbYxdMaster()->table('arctiny')->where('id',$data['id'])->update($tinyData);
		}
		if(!$id){
			self::writeSuccessLog("生产文章ID失败【{$data['id']}】");
			return false;
		}
		return $id;
	}
	/**
	 * 处理archives表数据
	 * @param unknown $data
	 * @return multitype:number unknown
	 */
	public static function getArchives($data , $op = self::OP_ADD){
		$archivesData = array();
		$archivesData['typeid'] = $data['typeid'];
		$archivesData['title'] = empty($data['title']) ? '' : $data['title'] ;
		$archivesData['shorttitle'] = empty($data['shorttitle']) ? ( empty($data['title']) ? '' : $data['title'] ) : $data['shorttitle'] ;
		$archivesData['writer'] = empty($data['id']) ? '' : $data['id'] ;
		$archivesData['source'] = empty($data['writer']) ? '游戏多' : $data['writer'] ;
	
		$archivesData['litpic'] = empty($data['weblitpic']) ? ( empty($data['litpic']) ? '' : 'http://img.youxiduo.com'.$data['litpic'] ) : 'http://img.youxiduo.com'.$data['weblitpic'] ;
		$archivesData['pubdate'] = empty($data['addtime']) ? 0 : $data['addtime'] ;
		$archivesData['keywords'] = empty($data['webkeywords']) ? '' : $data['webkeywords'] ;
		$archivesData['description'] = empty($data['webdesc']) ? ( empty($data['description']) ? '' : $data['description'] ) : $data['webdesc'] ;
		$archivesData['lastclick'] = empty($data['addtime']) ? 0 : $data['addtime'] ;
		$archivesData['yxdid'] = empty($data['yxdid']) ? '' : $data['yxdid'] ;
		//如果是编辑文章不修改以下属性
		if($op != self::OP_EDIT){
			$archivesData['id'] = $data['id'] ;
			$archivesData['senddate'] = empty($data['addtime']) ? 0 : $data['addtime'] ;
			$archivesData['typeid2'] = 0;
			$archivesData['arcrank'] = -1;
			$archivesData['channel'] = 4;
			$archivesData['flag'] = '';
			$archivesData['ismake'] = 0;
			$archivesData['click'] = 0;
			$archivesData['mid'] = 0;
			$archivesData['lastpost'] = 0;
			$archivesData['notpost'] = 0;
			$archivesData['filename'] = '';
			$archivesData['dutyadmin'] = 0;
			$archivesData['tackid'] = 0;
			$archivesData['mtype'] = 0;
			$archivesData['weight'] = 0;
			$archivesData['reftype'] = 0;
			$archivesData['goodpost'] = 0;
			$archivesData['badpost'] = 0;
			$archivesData['wclick'] = 0;
			$archivesData['mclick'] = 0;
			$archivesData['lclick'] = 0;
			$archivesData['sortrank'] = 0;
			$archivesData['mid'] = 0;
			//添加
			
			$id = self::dbYxdMaster()->table('archives')->insert($archivesData);
			
			if($id <= 0){
				self::writeSuccessLog("同步执行到主表失败标题是【" . $archivesData . "】");	
				//执行回退
				self::goBack($archivesData['id'],'arctiny');		
			}
		}else{
			//修改
			$id =  self::dbYxdMaster()->table('archives')->where('id',$data['id'])->update($archivesData);
		}
		return $id;
	}
	/**
	 * 处理附加表数据
	 * @param unknown $data
	 * @return multitype:string unknown
	 */
	public static function getAddonmarticle($data , $op = self::OP_ADD){
		$addonData = array();
		$addonData['typeid'] = $data['typeid'];
		$addonData['gameid'] = empty($data['gameid']) ? '' : $data['gameid'];
		$addonData['body'] = empty($data['content']) ? '' : $data['content'];
		$addonData['game'] = empty($data['game']) ? '' : $data['game'] ;
		$addonData['v_writer'] = empty($data['writer']) ? '' : $data['writer'] ;
		//如果是编辑文章不修改以下属性
		if( $op != self::OP_EDIT){
			$addonData['aid'] = $data['id'] ;
			$addonData['templet'] = '';
			$addonData['redirecturl'] = '';
			$addonData['userip'] = '';
			//添加
			$id = self::dbYxdMaster()->table('addonmarticle')->insert($addonData);
			if($id <= 0){
				self::writeSuccessLog("同步执行到附加表失败标题是【" . $addonData . "】");
				//执行回退
				self::goBack($addonData['aid'],'arctiny');
				self::goBack($addonData['aid'],'archives');
			}
		}else{
			//修改
			$id = self::dbYxdMaster()->table('addonmarticle')->where('aid',$data['id'])->update($addonData);
		}
		return $id;
	}
	
	/**
	 * 获取视频tag
	 * @param array $data
	 * @param string $op
	 * @return multitype:string unknown Ambigous <number, unknown>
	 */
	public static function getTagList($data , $op){
		$tagListData = array();
		$tagListData['tid'] = $data['tid'];
		$tagListData['typeid'] = $data['typeid'];
		$tagListData['arcrank'] = empty($data['addtime']) ? 0 : $data['addtime'] ;
		$tagListData['tag'] = $data['tag'];
		$tagListData['taggroup'] = "视频类型";
		if( $op != self::OP_EDIT){
			$id = self::dbYxdMaster()->table('taglist')->insert($tagListData);
			if($id <= 0){
				self::writeSuccessLog("同步执行到tag表失败标题是【" . $tagListData . "】");
				//执行回退
				self::goBack($data['id'],'arctiny');
				self::goBack($data['id'],'archives');
				self::goBack($data['id'],'addonmarticle');
			}
		}else{
			//修改
			$id = self::dbYxdMaster()->table('taglist')->where('aid',$data['id'])->update($tagListData);
		}
		return $tagListData;
	}

	//替换视频地址
	public static function get_youku_video_url($h5url,$v_type='2')
	{
		//html5 url:http://v.youku.com/player/getRealM3U8/vid/XMzE4MjQzMTcy
		//iframe url:http://player.youku.com/embed/XNDcxMjAwMjk2
		//swf url:http://player.youku.com/player.php/sid/XNDk4NTQ2ODcy/v.swf
		$base = '';
		$web_video_url = '';
		$name = substr($h5url, strlen("http://v.youku.com/player/getRealM3U8/vid/"));
		if($v_type == '1'){
			$base = "http://player.youku.com/embed/";
			$web_video_url = $base.$name;
		}elseif($v_type== '2'){
			$base = 'http://player.youku.com/player.php/sid/';
			$web_video_url = $base.$name.'/v.swf';
		}elseif($v_type =='3'){
			$name = substr($h5url, strlen("http://player.youku.com/embed/"));
			$base = 'http://player.youku.com/player.php/sid/';
			$web_video_url = $base.$name.'/v.swf';
		}
	
	
		return $web_video_url;
	}
	
	//生成新的视频播放代码
	public static function make_new_video_block($new_url,$i_width,$i_height,$v_type='2')
	{
		if($new_url == '' || $i_width == '' || $i_height == ''){
			return '';
		}
		$new_video_html = '';
		if($v_type == '1'){
			$new_video_html = '<iframe src="'.$new_url.'" width="'.(int)$i_width.'" height="'.(int)$i_height.'" frameborder=0 allowfullscreen></iframe>';
		}else{
			$new_video_html = '<embed src="'.$new_url.'" allowFullScreen="true" quality="high" width="'.(int)$i_width.'" height="'.(int)$i_height.'" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash"></embed>';
		}
		return $new_video_html;
	}
	
	/**
	 * 执行回滚
	 * @param number $aid
	 * @param string $table
	 */
	public static function goBack($aid=0,$table=''){
		if($aid == 0 || $table == ''){
			return false;
		}
		if($table == 'arctiny' || $table == 'archives' ){
			return self::dbYxdMaster()->table($table)->where('id',$data['id'])->delete();
		}else{
			return self::dbYxdMaster()->table($table)->where('aid',$data['id'])->delete();
		}
	}
	
	/**
	 * 日志
	 * @param unknown $message
	 */
	public static function writeSuccessLog($message)
	{
		//echo $message;
		$log_doc = storage_path() . '/logs/';
		$file_suffix = date('Y-m-d',time());
		$log_file = $log_doc.'log_success_arc'.$file_suffix.'.txt';
		if(!file_exists($log_file)){ //检测log.txt是否存在
			touch($log_file);
			chmod($log_file, 0777);
		}
		$message = date('Y-m-d H:i:s') . ' ' . $message;
		@file_put_contents($log_file,$message."\r\n",FILE_APPEND);
	}
	
	
}