<?php
namespace modules\cms\controllers;

use Yxd\Modules\Core\BackendController;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
use Illuminate\Support\Facades\Config;
use libraries\Helpers;
use Yxd\Services\SyncarticleService;
use Youxiduo\Cms\Model\Arcatt;
use Youxiduo\Cms\Model\Archives;
use Youxiduo\Cms\Model\Addongame;
use Youxiduo\Cms\GameInfo;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Youxiduo\Helper\Utility;
use Youxiduo\Cms\Model\Arctype;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Cms\Model\Gonglue;
use Youxiduo\Android\Model\Opinion;
use Youxiduo\Cms\Model\Videos;
use Youxiduo\Cms\Model\GamesVideo;
use Youxiduo\Cms\Model\News;

class BestController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'cms';
	}
	
	/**
	 * 处理内容图片
	 * @param string $content
	 * @return string
	 */
	public function make_content($content){
		//replace img baseurl
		$content = str_replace("http://img.youxiduo.com", "", $content);
		$content = str_replace('src="/', 'src="http://img.youxiduo.com/', $content);
		//clear style
		//preg_match('/style=\"(.*?)\"/i', $v['content'],  $match[$k]);
		//$content[$k] = str_replace($match[$k][0], "", $v['content']);
		return $content;
	}
	/**
	 * 文章进行关联
	 * @param array $input
	 */
	public function artCorrelate(&$input){
		//关联游戏
		if($input['gametype']=='ios'){
			$iosGame = GameModel::getInfo($input['gid']);
			//$input['game_name'];
			//查询android 是否存在此游戏
			if(empty($iosGame)){
				return $this->back()->with('global_tips','参数错误');
				exit;
			}
			if($iosGame['agid']!=0){
				$input['agid'] = $iosGame['agid'] ;
			}else{
				//通过名称反查android 游戏ID 并执行游戏关联
				$androidGame = Game::mname_getInfo($iosGame['gname']);
				$input['agid'] = empty($androidGame) ? 0 : $androidGame['id'] ;
			}
			$input['game_name'] = $input['igame_name'] ;
		}elseif($input['gametype']=='android'){
			$androidGame = Game::m_getInfo($input['agid']);
			//$input['game_name'];
			//查询android 是否存在此游戏
			if(empty($androidGame)){
				return $this->back()->with('global_tips','参数错误');
				exit;
			}
			if($androidGame['igid']!=0){
				$input['gid'] = $androidGame['igid'] ;
			}else{
				//通过名称反查android 游戏ID 并执行游戏关联
				$iosGame = GameModel::getnameInfo($androidGame['gname']);
				$input['gid'] = empty($iosGame) ? 0 : $iosGame['id'] ;
			}
			$input['game_name'] = $input['agame_name'] ;
		}else{
			return $this->back()->with('global_tips','参数错误');
			exit;
		}
	}
	/**
	 * 处理pid
	 * @param array $input
	 */
	public function checkDir(&$input){
		if(!empty($input['isseries'])){
			//判断是否为系列栏目文章
			if($input['seriesid']==0 && empty($input['webcatedir'])){
				return $this->back()->with('global_tips','添加失败，web栏目目录名称不能为空');
				exit;
			}else if($input['seriesid']==0 && !empty($input['webcatedir'])){
				$input['pid'] = '-1';
				//检测目录是否符合规格 并不能重复
			}else{
				$input['pid'] = $input['seriesid'];
			}
			
		}else{
			$input['pid'] = 0;
		}
	}
	/**
	 * 执行操作 
	 * @param number $id
	 * @param number $aid
	 * @param string $dopost
	 * @return boolean
	 */
	public function checkShow($aid = 0 , $dopost = ''){
		if($aid <= 0) return false;
		$website = Config::get('app.mobile_service_path');
		//查询mobile端对应的文章ID
		$url = "{$website}archives_do.php?aid={$aid}&dopost={$dopost}&qstr={$aid}";
		$rs = Helpers::curlGet($url);
		SyncarticleService::writeSuccessLog("审核文章ID【{$aid}】,状态码：{$rs}");
	/* 	http://test.www.youxiduo.com/cms/archives_do.php?aid=94285&dopost=makeArchives&qstr=94285 */
		
// 		http://test.www.youxiduo.com/cms/archives_do.php?aid=94285&dopost=checkArchives&qstr=94285

		
	}
	
	
	public function anyProperty($id = ''){
		//查询所有flag
		$input = Input::all();
		$data['id'] = $id;
		
		if(!empty($input['tmpids'])){
			$data['id'] = $input['tmpids'];
		}
		$result = Arcatt::getLists();
		$data['flag'] = $result;
		$data['action'] = $input['act'];
		$data['type'] = $input['type'];
		$html = $this->html('pop-tag-list',$data);
		return $this->json(array('html'=>$html));
	}
	public function anySaveFlag(){
		$input = Input::all();
		$ids = explode(',', $input['tmpids']);
		$rsError = array();
		foreach ($ids as $id){
			//通过ID查找文章
			$arc_yxdid = SyncarticleService::getYxdId($id, $input['type']);
			//查询是否同步此文章
			$rs = SyncarticleService::getArchivess($arc_yxdid);
			//print_r($rs);exit;
			if($rs){
				//修改
				$data = array();
				$data['flagname'] = $input['flagname'];
				if($input['action'] == 'add-property'){
					$data['dopost'] = 'attsAdd';
					$data['qstr'] = $rs['id'];
					$data['tmpids'] = $rs['id'];
				}elseif($input['action'] == 'del-property'){
					$data['dopost'] = 'attsDel';
					$data['qstr'] = $rs['id'];
					$data['tmpids'] = $rs['id'];
				}
				//print_r($data);
				if(!empty($data)){
					$website = Config::get('app.mobile_service_path');
					//查询mobile端对应的文章ID
					$url = "{$website}archives_do.php";
					$rss = Utility::loadByHttp($url,$data,'POST','');
					//$rs = Helpers::curlPost($url,$data);
					SyncarticleService::writeSuccessLog("文章属性ID【{$rs['id']}】,消息：{$rss}");
				}
			}else{
				$rsError[] =  $arc_yxdid ; 
				continue;
			}
		}
		if($input['type'] == 'tujian' || $input['type'] == 'info' || $input['type'] == 'picture'){
			return Redirect::to('cms/other/search?type='.$input['type'])->with('global_tips','操作完成');
		}else{
			return Redirect::to('cms/'.$input['type'].'/search')->with('global_tips','操作完成');
		}
		
	}
	
	public function anyMove(){
		//查询所有flag
		$input = Input::all();
		$keytype = Input::get('keytype','id');
		$keyword = Input::get('keyword');
		$search = array();
		if($keytype=='id'){
			$search['id'] = $keyword;
		}else{
			$search['typename'] = $keyword;
		}
		$page = Input::get('page',1);
		$pagesize = 6;
		$data = array();
		$data['keytype'] = $keytype;
		$data['keyword'] = $keyword;
		//print_r($data);exit;
		$result = Arctype::m_search($search,$page,$pagesize);
	
		$data['types'] = $result['result'];
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
	
		$data['id'] = $input['tmpids'];
		$data['action'] = 'move';
		$data['type'] = $input['type'];
		//print_r($data);exit;
		$html = $this->html('pop-type-list',$data);
		return $this->json(array('html'=>$html));
		
	}

	public function anyMake($id = 0){
		$input = Input::all();
		$website = Config::get('app.mobile_service_path');
		$url = "{$website}archives_do.php";
		$data['dopost'] = $input['act'] . 'Archives';
		$rss = '';
		if($id != 0){
			$arc_yxdid = SyncarticleService::getYxdId($id, $input['type']);
			$rs = SyncarticleService::getArchivess($arc_yxdid);
			$data = array();
			$data['qstr'] = $rs['id'];
			$rss = Utility::loadByHttp($url,$data);
		}
		if($input['tmpids']!=''){
			$tmpids = explode(',', $input['tmpids']);
			$yxdidArr = array();
			foreach ($tmpids as $v){
				$yxdidArr[] = SyncarticleService::getYxdId($v, $input['type']);
			}
			$rs = SyncarticleService::getArchivesArr($yxdidArr);
			foreach ($rs as $v){
				$data['qstr']  = $v['id'];
				$result = Utility::loadByHttp($url,$data);
				$rss =  $data['qstr'].'生成成功';
				SyncarticleService::writeSuccessLog($rss);
			}		
		}
		return $this->json(array('html'=>'操作完成'));	
	}
}