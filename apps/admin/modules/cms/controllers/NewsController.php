<?php
namespace modules\cms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;

use libraries\Helpers;
use Youxiduo\Cms\Model\News;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
use Yxd\Services\SyncarticleService;
use Illuminate\Support\Facades\Config;
use Youxiduo\Cms\GameInfo;


class NewsController extends BestController
{
	public function _initialize()
	{
		$this->current_module = 'cms';
	}

    /**
     * 列表页
     * @return
     * @internal param string $type
     */
	public function getSearch()
	{
		$data = array();
		$cond = $search = Input::only('type','zonetype');
		$page = Input::get('page',1);
		$pagesize = 10;
		$keytype = Input::get('keytype','');
		$keyword = empty($keytype) ? '' : Input::get('keyword','') ;
		$cond['keyword'] = $keyword;
		$cond['keytype'] = empty($keytype)?'gname':$keytype;
		$cond['keytypes'] = array('id' => 'ID' , 'title' => '名称' , 'gname' => '游戏名称' , 'news' => '新闻');
		$data['keyword'] = $keyword;

		$result = News::getList($page,$pagesize,$keyword,$keytype);

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
		return $this->display('news-list',$data);
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
		$data['type'] = array('0'=>'游戏新闻','1'=>'全局业内资讯','2'=>'全局游戏新闻');
		return $this->display('news-add',$data);
	}
	
	/**
	 * 新闻编辑界面显示
	 * @param unknown $id
	 */
	public function getEdit($id)
	{
		$data = array();
		$data['gid'] = Input::get('gid',0);
		$data['agid'] = Input::get('agid',0);
		$data['result'] = News::getDetails($id);
		if(empty($data['result'])){
			return $this->back()->with('global_tips','参数错误！~~');
			exit;
		}
		//判断是否为游戏文章
		if($data['result']['gid']==0 && $data['result']['agid']==0){
			//全局新闻
			$data['type'] = array('1'=>'全局业内资讯','2'=>'全局游戏新闻');
			$data['result']['gametype'] = 'all';
		}else{
			$data['type'] = array('0'=>'游戏新闻');
			if($data['result']['pid']=='-1'){
				$data['result']['seriesid'][0] = '无';
			}
				
			if($data['result']['gid']>0){
				$data['result']['gametype'] = 'ios';
				//查询该游戏下面所有系列栏目
				$menu = News::getMenu($data['result']['gid']);
				//查询游戏name and pic
				$iosGame = GameModel::getInfo($data['result']['gid']);
				$data['result']['gamename'] = $iosGame['gname'];
				$data['result']['advpic'] = $iosGame['advpic'];
			}else{
				$data['result']['gametype'] = 'android';
				$menu = News::getMenu($data['result']['agid'],'agid');
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
		}
		$website = Config::get('app.img_url');
		empty($data['result']['litpic']) ? : $data['result']['oldlitpic'] = $website . $data['result']['litpic'] ;
		empty($data['result']['weblitpic']) ? : $data['result']['oldweblitpic'] = $website . $data['result']['weblitpic'] ;
		empty($data['result']['litpic2']) ? : $data['result']['oldlitpic2'] = $website . $data['result']['litpic2'] ;
		empty($data['result']['litpic3']) ? : $data['result']['oldlitpic3'] = $website . $data['result']['litpic3'] ;
		return $this->display('news-edit',$data);
	}
	/**
	 * 删除
	 * @param int $id
	 */
	public function getDel($id)
	{
		$type = 'news';
		$data = array();
		$yxdid = SyncarticleService::getYxdId($id, $type);
		$arc = News::getDetails($id);
		if($arc['zxtype']=='1' || $arc['zxtype']=='2'){
			$yxdid = 'zx_'.$id;
		}
		$result = News::delArticle($id);
		//同步删除
		if($result > 0){
			$website = Config::get('app.mobile_service_path');
			$url = $website . "delgame.php?type=arc&yxdid={$yxdid}";
			$rs = Helpers::curlGet($url);
			SyncarticleService::writeSuccessLog("删除文章ID【{$id}】,状态码：{$rs}");
		}
		return $this->redirect("cms/news/search");
	}
	/**
	 * 新闻保存
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
			
		$file_litpic2 = Input::file('litpic2');
		$litpic2 = Helpers::uploadPic($dir, $file_litpic2);
		$input['litpic2'] = $litpic2;
			
		$file_litpic3 = Input::file('litpic3');
		$litpic3 = Helpers::uploadPic($dir, $file_litpic3);
		$input['litpic3'] = $litpic3;
		//判断图片是否修改
		if($input['dosave']=='edit'){
			$aid = $input['id'];
			//查询当前文章信息
			$art = News::getDetails($input['id']);
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
			if(!empty($litpic2)){
				@unlink( storage_path() . $art['litpic2']);
			}
			if(!empty($litpic3)){
				@unlink( storage_path() . $art['litpic3']);
			}
			$tips = '修改';
		}else{
			$tips = '添加';
		}
		//游戏新闻
		if($input['zxtype']==0){
			if(empty($input['gid']) && empty($input['agid'])){
				return $this->back()->with('global_tips',$tips.'失败，请重试。请绑定关联游戏ID');
				exit;
			}
			//关联游戏
			$this->artCorrelate($input);
			//判断是否是系列文章
			$this->checkDir($input);
		}
	
		$data['title'] = $input['title'];
		$data['writer'] = $input['writer'];
		$data['content'] = empty($input['content']) ? '' : $input['content'];
		$data['gid'] = empty($input['gid']) ? 0 : $input['gid'];
		$data['agid'] = empty($input['agid']) ? 0 : $input['agid'];
			
		empty($input['id']) ? : $data['id'] = $input['id'];
		empty($input['editor']) ? : $data['editor'] = $input['editor'];
		empty($input['sort']) ? : $data['sort'] = $input['sort'];
		empty($input['pid']) ? : $data['pid'] = $input['pid'];
		empty($input['zxshow']) ? : $data['zxshow'] = $input['zxshow'];
		empty($input['zxtype']) ? : $data['zxtype'] = $input['zxtype'];
		empty($input['litpic']) ? : $data['litpic'] = $input['litpic'];
		empty($input['litpic2']) ? : $data['litpic2'] = $input['litpic2'];
		empty($input['litpic3']) ? : $data['litpic3'] = $input['litpic3'];
		empty($input['weblitpic']) ? : $data['weblitpic'] = $input['weblitpic'];
		empty($input['webkeywords']) ? : $data['webkeywords'] = $input['webkeywords'];
		empty($input['webdesc']) ? : $data['webdesc'] = $input['webdesc'];
		empty($input['shorttitle']) ? : $data['shorttitle'] = $input['shorttitle'];
		$rs = News::save($data);
		$aid = ($aid==0) ? $rs : $aid ;
		//执行同步
		$syncID = SyncarticleService::syncNews($aid);
		if($syncID){
			$this->checkShow($syncID,'checkArchives');
			$tips .= '成功！';
		}else{
			$tips .= "成功,但同步失败。请记住文章【ID:{$aid}】-【news】并告诉技术解决";
		}
		return Redirect::to('cms/news/search')->with('global_tips',$tips);
	}

}