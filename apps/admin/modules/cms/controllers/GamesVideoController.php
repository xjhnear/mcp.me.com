<?php
namespace modules\cms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;

use libraries\Helpers;
use Youxiduo\Cms\Model\GamesVideo;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
use Yxd\Services\SyncarticleService;
use Illuminate\Support\Facades\Config;


class GamesVideoController extends BestController
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
		$cond['keytype'] = empty($keytype)?'title':$keytype;
		$cond['keytypes'] = array('id' => 'ID' , 'title' => '名称' , 'gname' => '游戏名称');
		$data['keyword'] = $keyword;
		$data['type'] = $type;
		
		if(empty($keytype)){
			$result = GamesVideo::getList($page,$pagesize);
		}else{
			$result = GamesVideo::getList($page,$pagesize,$keyword,$keytype);
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
		return $this->display('gamevideo-list',$data);
	}
	
	/**
	 * 游戏视频添加界面显示
	 */
	public function getAdd()
	{
		$data = array();
		$data['editor'] = 1;
		$data['dosave'] = 'add';
		$data['type'] = array('1'=>'游戏视频','2'=>'攻略视频','3'=>'评测视频');
		return $this->display('gamevideo-add',$data);
	}
	
	
	/**
	 * 游戏视频编辑界面显示
	 * @param int $id
	 */
	public function getEdit($id)
	{
		$data = array();
		$data['gid'] = Input::get('gid',0);
		$data['agid'] = Input::get('agid',0);
		$data['editor'] = 1;
		$data['dosave'] = 'edit';
		$data['type'] = array('1'=>'游戏视频','2'=>'攻略视频','3'=>'评测视频');
		$data['result'] = GamesVideo::getDetails($id);
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
	
		return $this->display('gamevideo-edit',$data);
	}
	
	public function anySave(){
		$input = Input::all();
		$old = array();
		$aid = 0 ;
		//缩略图
		$dir = '/u/article/' . date('Y') . date('m') . '/';
		$file_ico = Input::file('ico');
		$ico = Helpers::uploadPic($dir, $file_ico);
		$input['ico'] = $ico;
		//判断图片是否修改
		if($input['dosave']=='edit'){
			$aid = $input['id'];
			//查询当前文章信息
			$art = GamesVideo::getDetails($input['id']);
			if(empty($art)){
				return $this->back()->with('global_tips','参数错误！~~');
				exit;
			}
			if(!empty($ico)){
				@unlink( storage_path() . $art['ico']);
			}
			$tips = '修改';
		}else{
			$tips = '添加';
			if(empty($input['ico'])){
				return $this->back()->with('global_tips',$tips.'失败，请重试。缩略图必须上传');
				exit;
			}
		}
	
		if(empty($input['gid']) && empty($input['agid'])){
			return $this->back()->with('global_tips',$tips.'失败，请重试。请绑定关联游戏ID');
			exit;
		}
		if(empty($input['video'])){
			return $this->back()->with('global_tips',$tips.'失败，请重试。请填写视频路径');
			exit;
		}
	
		//关联游戏
		$this->artCorrelate($input);
		empty($input['id']) ? : $data['id'] = $input['id'];
		$data['title'] = $input['title'];
		$data['writer'] = $input['writer'];
        $data['duration'] = $input['duration'];
		$data['gid'] = empty($input['gid']) ? 0 : $input['gid'];
		$data['agid'] = empty($input['agid']) ? 0 : $input['agid'];
		$data['video'] = $input['video'];
		$data['ico'] = $input['ico'];
		empty($input['editor']) ? : $data['editor'] = $input['editor'];
		empty($input['type']) ? : $data['type'] = $input['type'];
		$data['viewtimes'] = 0;
	
		$rs = GamesVideo::save($data);
		$aid = ($aid==0) ? $rs : $aid ;
		//执行同步
		$syncID = SyncarticleService::syncGamesVideo($aid);
		if($syncID){
			$this->checkShow($syncID,'checkArchives');
			$tips .= '成功！';
		}else{
			$tips .= "成功,但同步失败。请记住文章【ID:{$aid}】-【opinion】并告诉技术解决";
		}
		return Redirect::to('cms/gamevideo/search')->with('global_tips',$tips);
	}
	
	/**
	 * 删除
	 * @param int $id
	 */
	public function getDel($id)
	{
		$type = 'gamevideo';
		$yxdid = SyncarticleService::getYxdId($id, $type);
		$arc = GamesVideo::getDetails($id);
		$result = GamesVideo::delArticle($id);
		$result = 1;
		//同步删除
		if($result > 0){
			$website = Config::get('app.mobile_service_path');
			$url = $website . "delgame.php?type=arc&yxdid={$yxdid}";
			$rs = Helpers::curlGet($url);
			SyncarticleService::writeSuccessLog("删除文章ID【{$id}】,状态码：{$rs}");
		}
		return $this->redirect("cms/gamevideo/search");
	}
}