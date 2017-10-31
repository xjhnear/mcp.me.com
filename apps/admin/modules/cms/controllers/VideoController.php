<?php
namespace modules\cms\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

use libraries\Helpers;
use Youxiduo\Cms\Model\Videos;
use Youxiduo\Cms\Model\VideosGames;
use modules\game\models\GameModel;
use Youxiduo\Android\Model\Game;
use Youxiduo\Cms\Model\VideosType;
use Yxd\Services\SyncarticleService;
use Illuminate\Support\Facades\Config;


class VideoController extends BestController
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
		$cond['keytype'] = empty($keytype)?'vname':$keytype;
		$cond['keytypes'] = array('id' => 'ID' , 'vname' => '名称');
		$data['type'] = $type;
		
		if(empty($keytype)){
			$result = Videos::getList($page,$pagesize);
		}else{
			$result = Videos::getList($page,$pagesize,$keyword,$keytype);
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
		return $this->display('video-list',$data);
	}
	
	/**
	 * 美女视频添加界面显示
	 */
    public function getAdd()
	{
		$data = array();
		$data['pid'] = 0;
		$data['editor'] = 1;
		$data['dosave'] = 'add';
		$data['videotype'] = VideosType::getAllTypeInfo();
		return $this->display('video-add',$data);
	}
	/**
	 * 美女视频编辑界面显示
	 * @param unknown $id
	 */
	public function getEdit($id)
	{
		$data = array();
		$data['gid'] = Input::get('gid',0);
		$data['agid'] = Input::get('agid',0);
		$data['result'] = Videos::getDetails($id);
		$data['dosave'] = 'edit';
		$data['videotype'] = VideosType::getAllTypeInfo();
		
		//获取关联游戏
		$gl = $this->getGlGame($id);
		$iosArr = $gl['ios'];
		$androidArr = $gl['android'];
		//查询游戏信息
		if(!empty($iosArr)) $iosgame = GameModel::getGameList($iosArr);
		if(!empty($androidArr)) $androidgame = Game::getGameList($androidArr);
		
		if(!empty($iosgame)){
			$idArr = $nameArr = array();
			foreach ($iosgame as $v){
				$idArr[] = $v['id'];
				$nameArr[] = $v['gname'];
			}
			$data['result']['gid'] = implode(',', $idArr);
			$data['result']['gname'] = implode(',', $nameArr);
		}
		if(!empty($androidgame)){
			$idArr = $nameArr = array();
			foreach ($androidgame as $v){
				$idArr[] = $v['id'];
				$nameArr[] = $v['gname'];
			}
			$data['result']['agid'] = implode(',', $idArr);
			$data['result']['agname'] = implode(',', $nameArr);
		}
		if($data['result']['apptype'] == 1 || $data['result']['apptype'] == 3){
			$data['result']['iosgid'] = true;
		}
		if($data['result']['apptype'] == 2 || $data['result']['apptype'] == 3){
			$data['result']['androidgid'] = true;
		}
		$website = Config::get('app.img_url');
		empty($data['result']['litpic']) ? : $data['result']['oldlitpic'] = $website . $data['result']['litpic'] ;
		return $this->display('video-edit',$data);
	}
	
	/**
	 * 美女视频保存
	 */
	public function anySave(){
		$input = Input::all();
		$old = array();
		$aid = 0 ;
		//缩略图
		$dir = '/u/article/' . date('Y') . date('m') . '/';
		$file_litpic = Input::file('litpic');
		$litpic = Helpers::uploadPic($dir, $file_litpic);
		$input['litpic'] = $litpic;
		
		//判断图片是否修改
		if($input['dosave']=='edit'){
			$aid = $input['id'];
			//查询当前文章信息
			$art = Videos::getDetails($aid);
			
			//获取关联游戏
			$gl = $this->getGlGame($aid);
			$iosArr = $gl['ios'];
			$androidArr = $gl['android'];
			if(empty($art)){
				return $this->back()->with('global_tips','参数错误！~~');
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
		$this->checkDir($input);
		$data['vname'] = $input['vname'];
		if (!empty($input['toggle'])){
			$data['flag'] = 1;
		}else{
			$data['flag'] = 0;
		}
		$apptype = 0;
		if(empty($input['iosgid']) && empty($input['androidgid'])){
			return $this->back()->with('global_tips',"IOS和安卓至少要选择一个");
			exit;
		}
		if(!empty($input['iosgid'])){
			$apptype += $input['iosgid'];
		}
		if(!empty($input['androidgid'])){
			$apptype += $input['androidgid'];
		}
		
		$data['apptype'] = $apptype;
		$data['type'] = $input['type'];
		$data['litpic'] = $input['litpic'];
		$data['gfid'] = (int)$input['gfid'];
		$video_html = $input['videourl'];
		if (strstr($video_html, "<iframe")){
			preg_match("'src=\"[^>]*?\"'si", $video_html, $video_str);
			$data['video'] = substr(substr($video_str[0], 5), 0, -1);
		}else{
			$data['video'] = $input['videourl'];
		}
		$data['writer'] = $input['writer'];
        $data['duration'] = $input['duration'];
		$data['score'] = $input['score'];
		$data['description'] = $input['editorcomt'];
		$gids = !empty($input['iosgid']) ? $input['gid'] : '';
		$agids = !empty($input['androidgid']) ? $input['agid'] : '';
		$data['editor'] = $input['editor'];
		empty($input['id']) ? : $data['id'] = $input['id'];
		
		$data['linkgame'] = '';
		$data['gid'] = '' ;
		$data['preview'] = '';
		$data['isapptop'] = 0;
		$data['updatetime'] = time();
		$data['sort'] = $input['sort'];
		$data['viewtimes'] = 0;
		$data['commenttimes'] = '';

		$rs = Videos::save($data);
		$aid = ($aid==0) ? $rs : $aid ;
		if($aid > 0){
			//清除关联信息
			$regl = VideosGames::delArticle($aid);
			//执行关联
			if ($gids != ""){
				$gid_arr = explode(",", $gids);
				foreach($gid_arr as $v){
					if ($v != ""){
						$Garr[] = VideosGames::save(array('vid'=>$aid, 'gid' => $v,'agid'=>0));
					}
				}
			}
			if ($agids != ""){
				$agid_arr = explode(",", $agids);
				foreach ($agid_arr as $v){
					if ($v != ""){
						$Aarr[] = VideosGames::save(array('vid'=>$aid, 'agid' => $v, 'gid'=>0));
					}
				}
			}
		}
		//执行同步
		$syncID = SyncarticleService::syncVideos($aid);
		if($syncID){
			$this->checkShow($syncID,'checkArchives');
			$tips .= '成功！';
		}else{
			$tips .= "成功,但同步失败。请记住文章【ID:{$aid}】-【video】并告诉技术解决";
		} 
		return Redirect::to('cms/video/search')->with('global_tips',$tips);
	}
	
	public function getAppTopOrDown($vid,$type=0){
		$tips = '';
		if($type == 0){
			$type = 1;
			$tips = '置顶';
		}elseif($type == 1){
			$type = 0;
			$tips = '取消置顶';
		}
		if ($vid == ''){
			return Redirect::to('cms/video/search')->with('global_tips',$tips."参数错误");
		}
		$data = array('isapptop' => $type , 'id' => $vid);
		$result = Videos::save($data);
		if(!$result){
			return Redirect::to('cms/video/search')->with('global_tips',$tips."错误，请联系技术。");
		}else{
			return Redirect::to('cms/video/search')->with('global_tips',$tips."成功");
		}
	}

	public function getIosOrAndroidGame($type=''){
		$gname = Input::get('name','');
		$data =array();
		if($type=='ios'){
			$result = GameModel::getnameInfo($gname);
		}else{
			$result = Game::mname_getInfo($gname);
		}
		if(!empty($result)){
			$data = array('id' => $result['id'],'name' => $result['gname']);
		}
		
		return Response:: json($data);
	}
	
	/**
	 * 删除
	 * @param int $id
	 */
	public function getDel($id)
	{
		$type = 'video';
		$data = array();
		$yxdid = SyncarticleService::getYxdId($id, $type);
		$arc = Videos::getDetails($id);
		$result = Video::delArticle($id);
		//同步删除
		if($result > 0){
			$website = Config::get('app.mobile_service_path');
			$url = $website . "delgame.php?type=arc&yxdid={$yxdid}";
			$rs = Helpers::curlGet($url);
			SyncarticleService::writeSuccessLog("删除文章ID【{$id}】,状态码：{$rs}");
		}
		return $this->redirect("cms/video/search");
	}
	/**
	 * 获取关联ID
	 * @param int $id
	 * @return multitype:multitype:unknown  multitype:
	 */
	protected function getGlGame($id){
		//获取关联游戏
		$gameArr = VideosGames::getLists($id);
		$iosArr = $androidArr = array();
		foreach ($gameArr as $v){
			if(!is_array($v)) continue;
			if($v['gid'] > 0) $iosArr[] = $v['gid'];
			if($v['agid'] > 0) $androidArr[] = $v['agid'];
		}
		$result = array('ios' => $iosArr , 'android' => $androidArr);
		return $result;
	}
}