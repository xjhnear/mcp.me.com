<?php
namespace modules\cms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;

use libraries\Helpers;
use Youxiduo\Cms\Model\Other;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
use Yxd\Services\SyncarticleService;
use Illuminate\Support\Facades\Config;

class OtherController extends BestController
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
		$type = empty($type) ? Input::get('type') : $type ;
		
		$cond = $search = Input::only('type','zonetype');
		$page = Input::get('page',1);
		$pagesize = 10;
		$keytype = Input::get('keytype','');
		$keyword = empty($keytype) ? '' : Input::get('keyword','') ;
		$cond['keyword'] = $keyword;
		$cond['keytype'] = empty($keytype)?'title':$keytype;
		$cond['keytypes'] = array('id' => 'ID' , 'title' => '名称' , 'gname' => '游戏名称');
		$data['type'] = $type;
		$result = Other::getList($type,$page,$pagesize,$keyword,$keytype);
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
		return $this->display('other-list',$data);
	}
	
	/**
	 * 其他扩展添加界面显示
	 */
	public function getAdd($type)
	{
		$data = array();
		$data['pid'] = 0;
		$data['editor'] = 1;
		$data['dosave'] = 'add';
		$data['type'] = $type;
		return $this->display('other-add',$data);
	}
	/**
	 * 其他扩展编辑界面显示
	 * @param int $id
	 * @param string $type
	 */
	public function getEdit($id,$type)
	{
		$data = array();
		$data['gid'] = Input::get('gid',0);
		$data['agid'] = Input::get('agid',0);
		$data['type'] = $type;
		$data['result'] = Other::getDetails($id);
	
			
			if($data['result']['pid']=='-1'){
				$data['result']['seriesid'][0] = '无';
			}
				
			if($data['result']['gid']>0){
				$data['result']['gametype'] = 'ios';
				//查询该游戏下面所有系列栏目
				$menu = Other::getMenu($data['result']['gid']);
				//查询游戏name and pic
				$iosGame = GameModel::getInfo($data['result']['gid']);
				$data['result']['gamename'] = $iosGame['gname'];
				$data['result']['advpic'] = $iosGame['advpic'];
			}else{
				$data['result']['gametype'] = 'android';
				$menu = Other::getMenu($data['result']['agid'],'agid');
				//查询游戏name and pic
				$androidGame = Game::m_getInfo($data['result']['agid']);
				$data['result']['gamename'] = $androidGame['gname'];
				$data['result']['advpic'] = $androidGame['advpic'];
			}
				
			foreach ($menu as $m){
				$data['result']['seriesid'][$m['id']] = $m['title'];
			}
			if(empty($data['result']['seriesid'])){
				$data['result']['seriesid'][0] = '无';
			}
		$website = '';
		empty($data['result']['litpic']) ? : $data['result']['oldlitpic'] = $website . $data['result']['litpic'] ;
		empty($data['result']['weblitpic']) ? : $data['result']['oldweblitpic'] = $website . $data['result']['weblitpic'] ;
		return $this->display('other-edit',$data);
	}

	/**
	 * 其他类型文章保存
	 * @param string $type
	 */
	public function anySave($type){
		$input = Input::all();
		$aid = 0 ;
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
			$art = Other::getDetails($input['id']);
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
		}
		//游戏进行关联
		$this->artCorrelate($input);
		//处理pid 判断是否为系列文章 并检测目录是否符合规则
		$this->checkDir($input);
		//查询游戏信息
		$data = array(
				'title' => $input['title'],
				'shorttitle' => $input['shorttitle'],
				'writer' => $input['writer'],
				'content' => empty($input['content']) ? '' : $this->make_content($input['content']),
				'webkeywords' => $input['webkeywords'],
				'webdesc' => $input['webdesc'],
				'webcatedir' =>  empty($input['webcatedir']) ? '' : $input['webcatedir'],
				'pid' => $input['pid'],
				'gid' => $input['gid'],
				'agid' => $input['agid'],
				'editor' => $input['editor'],
				'sort' => empty($input['sort']) ? 0 : $input['sort'],
				'art_type' => Other::getExtentArticleType($type),
				'litpic' => $input['litpic'],
				'weblitpic' => $input['weblitpic']
		);

		if($input['dosave']=='edit'){
			$data['id'] = $input['id'];
			$aid = $input['id'];
			$tips = '修改';
		}else{
			$tips = '添加';
		}
	
		//print_r($data);exit;
		$rs = Other::save($data);
		//$tips .= '成功！';
		$aid = ($aid==0) ? $rs : $aid ;
		//执行同步
		$syncID = SyncarticleService::syncOther($aid,$type);
		if($syncID){
			$this->checkShow($syncID,'checkArchives');
			$tips .= '成功！';
		}else{
			$tips = "添加成功,但同步失败。请记住文章【ID:{$aid}】-【{$type}】并告诉技术解决";
		}
		return Redirect::to('cms/other/search?type='.$type)->with('global_tips',$tips);
	}
	
	
	/**
	 * 删除
	 * @param string $type
	 * @param int $id
	 */
	public function getDel($type,$id)
	{
		$data = array();
		$yxdid = SyncarticleService::getYxdId($id, $type);
		$arc = Other::getDetails($id);
		$result = Other::delArticle($id);
		//同步删除
		if($result > 0){
			$website = Config::get('app.mobile_service_path');
			$url = $website . "delgame.php?type=arc&yxdid={$yxdid}";
			$rs = Helpers::curlGet($url);
			SyncarticleService::writeSuccessLog("删除文章ID【{$id}】,状态码：{$rs}");
		}
		return $this->redirect("cms/other/search?type={$type}");
	}

}