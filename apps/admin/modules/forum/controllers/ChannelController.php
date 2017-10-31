<?php
namespace modules\forum\controllers;
use modules\forum\models\ForumModel;

use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use modules\forum\models\ChannelModel;

class ChannelController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'forum';
	}
	
	public function getList()
	{
		$data = array();
		$gid = Input::get('game_id',0);
		if($gid) {
			$data['datalist'] = ChannelModel::getList($gid);
			$data['game'] = GameService::getGameInfo($gid);
			$data['gid'] = $gid;
		}else{
			$data['datalist'] = ChannelModel::getList($gid);
			$data['game'] = array();
			$data['gid'] = $gid;
		}
		return $this->display('channel-list',$data);
	}
	
	public function getAdd($gid)
	{
		$data['game'] = GameService::getGameInfo($gid);
		$data['channel'] = array('gid'=>$gid,'allowpost'=>1,'displayorder'=>50);
		return $this->display('channel-edit',$data);
	}
	
	public function getEdit($cid)
	{
		$data = array();
		$data['channel'] = ChannelModel::getInfo($cid);
		$data['game'] = GameService::getGameInfo($data['channel']['gid']);
		return $this->display('channel-edit',$data);
	}
	
	public function postSave()
	{
		$input['cid'] = Input::get('cid');
		$input['gid'] = $gid = Input::get('gid');
		$input['channel_name'] = Input::get('channel_name');
		$input['allowpost'] = Input::get('allowpost',1);
		$input['displayorder'] = Input::get('displayorder',50);
		$input['type'] = 0;
		$success = ChannelModel::save($input);
	    if($success){
			return $this->redirect('forum/channel/list/?game_id=' . $gid)->with('global_tips','版块保存成功');
		}else{
			return $this->back()->with('global_tips','版块保存失败');
		}
	}
	
	public function getDelete($gid,$cid)
	{
		if($cid){
			$res  = ChannelModel::doDelete($gid, $cid);
			if($res === -1){
				return $this->back()->with('global_tips','版块已经有帖子了,请先删除版块下的帖子');
			}elseif($res>0){
				return $this->back()->with('global_tips','版块删除成功');
			}
			return $this->back()->with('global_tips','版块删除失败');
		}		
	}
	
	public function getApiChannelOption()
	{
		$gid = Input::get('game_id',0);
		if($gid){
			
			$channels = ChannelModel::getOptions($gid);
			$channels[0] = '选择版块';
			$out['status']=200;
			$out['data'] = $channels;
			return $this->json($out);
		}
	}
	
	public function getExpeditionList()
	{
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$result = ForumModel::getExpeditionList($page,$pagesize);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$data['datalist'] = $result['result'];
		$data['pagelinks'] = $pager->links();
		return $this->display('expedition-list',$data);
	}
	
    public function getExpeditionEdit($id=0,$gid=0)
	{
		$data = array();
		if($id){
			$data['epd'] = ForumModel::getExpeditionInfo($id);
			$data['game'] = GameService::getGameInfo($data['epd']['gid']);
		}elseif($gid){
			$data['game'] = GameService::getGameInfo($gid);
		}
		return $this->display('expedition-edit',$data);
	}
	
    public function postSaveExpedition()
	{
		$input = Input::only('id','gid','title','sort');
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图	    
	    if(Input::hasFile('litpic')){
	    	
			$file = Input::file('litpic'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$input['litpic'] = $dir . $new_filename . '.' . $mime;
		}
		ForumModel::saveExpedition($input);
		return $this->redirect('forum/channel/expedition-list')->with('global_tips','保存成功');
	}
	
	public function getExpeditionDelete($gid)
	{
		ForumModel::deleteExpedition($gid);
		return $this->back()->with('global_tips','删除成功');
	}
}