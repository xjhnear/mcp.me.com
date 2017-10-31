<?php
namespace modules\cms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;

use libraries\Helpers;
use Youxiduo\Cms\Model\Opinion;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
use Yxd\Services\SyncarticleService;
use Illuminate\Support\Facades\Config;


class OpinionController extends BestController
{
	public function _initialize()
	{
		$this->current_module = 'cms';
	}
	
	/**
	 * 列表页
	 * @param string $type
	 */
	public function getSearch($type='')
	{
		$data = array();
		$view = '';
		$type = empty($type) ? Input::get('type') : $type ;
		
		$cond = $search = Input::only('type','zonetype');
		$page = Input::get('page',1);
		$pagesize = 10;
		$keytype = Input::get('keytype','');
		$keyword = empty($keytype) ? '' : Input::get('keyword','') ;
		$cond['keyword'] = $keyword;
		$cond['keytype'] = empty($keytype)?'ftitle':$keytype;
		$cond['keytypes'] = array('id' => 'ID' , 'ftitle' => '名称' , 'gname' => '游戏名称');
		$data['type'] = $type;
		
		if(empty($keytype)){
			$result = Opinion::getList($page,$pagesize);
		}else{
			$result = Opinion::getList($page,$pagesize,$keyword,$keytype);
		}
		if(empty($result)){
			return $this->back()->with('global_tips','参数出错，请联系技术。');
			exit;
		}
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$pager->appends($cond);
		$data['cond'] = $cond;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $result['total'];
		$data['datalist'] = $result['results'];
		return $this->display('opinion-list',$data);
	}
	
	
	/**
	 * 新闻添加界面显示
	 */
	public function getAdd()
	{
		$data = array();
		$data['pid'] = 0;
		$data['editor'] = 1;
		$data['dosave'] = 'add';
		return $this->display('opinion-add',$data);
	}
	/**
	 * 评测编辑界面显示
	 * @param int $id
	 */
	public function getEdit($id)
	{
		$data = array();
		$data['gid'] = Input::get('gid',0);
		$data['agid'] = Input::get('agid',0);
		$data['result'] = Opinion::getDetails($id);
		if(empty($data['result'])){
			return $this->back()->with('global_tips','参数错误！~~');
			exit;
		}
		//关联游戏
		if($data['result']['gid']>0){
			$data['result']['gametype'] = 'ios';
			//查询游戏name and pic
			$iosGame = GameModel::getInfo($data['result']['gid']);
			$data['result']['gamename'] = $iosGame['gname'];
			$data['result']['advpic'] = $iosGame['advpic'];
		}else{
			$data['result']['gametype'] = 'android';
			//查询游戏name and pic
			$androidGame = Game::m_getInfo($data['result']['agid']);
			$data['result']['gamename'] = $androidGame['gname'];
			$data['result']['advpic'] = $androidGame['advpic'];
		}
		$website = '';
		empty($data['result']['litpic']) ? : $data['result']['oldlitpic'] = $website . $data['result']['litpic'] ;
		empty($data['result']['weblitpic']) ? : $data['result']['oldweblitpic'] = $website . $data['result']['weblitpic'] ;
	
		return $this->display('opinion-edit',$data);
	}
	
	/**
	 *	评测保存
	 */
	public function anySave(){
		$input = Input::all();
		$old = array();
		$aid = 0 ;
		//新闻文章
		//缩略图
		$dir = '/u/article/' . date('Y') . date('m') . '/';
		$file_weblitpic = Input::file('weblitpic');
		$weblitpic = Helpers::uploadPic($dir, $file_weblitpic);
		$input['weblitpic'] = $weblitpic;
			
		$file_litpic = Input::file('litpic');
		$litpic = Helpers::uploadPic($dir, $file_litpic);
		$input['litpic'] = $litpic;
	
		//判断图片是否修改
		if($input['dosave']=='edit'){
			$aid = $input['id'];
			//查询当前文章信息
			$art = Opinion::getDetails($input['id']);
			if(empty($art)){
				return $this->back()->with('global_tips','参数错误！~~');
				exit;
			}
			if(!empty($weblitpic)){
				@unlink( storage_path() . $art['weblitpic']);
			}
			if(!empty($litpic)){
				@unlink( storage_path() . $art['litpic']);
			}
			$tips = '修改';
		}else{
			$tips = '添加';
		}
		//游戏新闻
		if(empty($input['gid']) && empty($input['agid'])){
			return $this->back()->with('global_tips',$tips.'失败，请重试。请绑定关联游戏ID');
			exit;
		}
		//关联游戏
		$this->artCorrelate($input);
		//判断是否是系列文章
		$this->checkDir($input);
		empty($input['id']) ? : $data['id'] = $input['id'];
		$data['ftitle'] = $input['ftitle'];
		$data['shorttitle'] = $input['shorttitle'];
		$data['writer'] = $input['writer'];
		$data['content'] = empty($input['content']) ? '' : $input['content'];
		$data['gid'] = empty($input['gid']) ? 0 : $input['gid'];
		$data['agid'] = empty($input['agid']) ? 0 : $input['agid'];
		$data['sort'] = empty($input['sort']) ? 0 : $input['sort'];
		empty($input['editor']) ? : $data['editor'] = $input['editor'];
		$data['pid'] = 0;
		empty($input['commenttimes']) ? : $data['commenttimes'] = $input['commenttimes'];
		empty($input['litpic']) ? : $data['litpic'] = $input['litpic'];
		$data['litpic2'] = '';
		$data['litpic3'] = '';
		empty($input['weblitpic']) ? : $data['weblitpic'] = $input['weblitpic'];
		empty($input['webkeywords']) ? : $data['webkeywords'] = $input['webkeywords'];
		empty($input['webdesc']) ? : $data['webdesc'] = $input['webdesc'];
		$data['webcatedir'] = '';
		$rs = Opinion::save($data);
		$aid = ($aid==0) ? $rs : $aid ;
		//执行同步
		$syncID = SyncarticleService::syncOpinion($aid);
		if($syncID){
			$this->checkShow($syncID,'checkArchives');
			$tips .= '成功！';
		}else{
			$tips .= "成功,但同步失败。请记住文章【ID:{$aid}】-【opinion】并告诉技术解决";
		}
		return Redirect::to('cms/opinion/search')->with('global_tips',$tips);
	}
	
	
	
	/**
	 * 删除
	 * @param int $id
	 */
	public function getDel($id)
	{
		$type = 'opinion';
		$data = array();
		$yxdid = SyncarticleService::getYxdId($id, $type);
		$arc = Opinion::getDetails($id);
		$result = Opinion::delArticle($id);
		//同步删除
		if($result > 0){
			$website = Config::get('app.mobile_service_path');
			$url = $website . "delgame.php?type=arc&yxdid={$yxdid}";
			$rs = Helpers::curlGet($url);
			SyncarticleService::writeSuccessLog("删除文章ID【{$id}】,状态码：{$rs}");
		}
		return $this->redirect("cms/opinion/search");
	}
}