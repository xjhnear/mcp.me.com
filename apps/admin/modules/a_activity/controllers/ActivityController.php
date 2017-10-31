<?php
namespace modules\a_activity\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

use Youxiduo\Android\Model\Activity;
use Youxiduo\Android\Model\Game;
use Youxiduo\Message\Model\MessageType;
class ActivityController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_activity';
	}
	
	public function getIndex()
	{
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$search = Input::only('keyword','game_id','is_top');
		$result = Activity::m_searchList($search,$page,$pagesize);
		$total = Activity::m_searchCount($search);
		$pager = Paginator::make(array(),$total,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result;
	    $game_ids = array();
		foreach($result as $row){
			$game_ids[] = $row['agid'];
		}
		if($game_ids){
			$games = Game::getListByIds($game_ids);
			$data['games'] = $games;
		}
		
		return $this->display('activity-list',$data);
	}
	
	public function getPopSearch($no=0)
	{
		$keytype = Input::get('keytype');
		$keyword = Input::get('keyword');
		$search = array('keyword'=>$keyword);		
		$page = Input::get('page',1);
		$pagesize = 10;
		$data = array();
		$data['no'] = $no;	
		$data['keytype'] = $keytype;
		$data['keyword'] = $keyword;
		$result = Activity::m_searchList($search,$page,$pagesize);
		$total = Activity::m_searchCount($search);
		$pager = Paginator::make(array(),$total,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result;
		$html = $this->html('pop-activity-list',$data);
		return $this->json(array('html'=>$html));
	}
	
	public function getPopInfo($id)
	{
		$activity = array();
		if($id){
		    $activity = Activity::m_getInfo($id);
		}
		return $this->json(array('activity'=>$activity));
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		
		$result = MessageType::getList();
		$result = Config::get('linktype');
		$linkTypeListDesc = array();
		foreach($result as $key=>$row){
			$linkTypeList[$key] = $row['name'];
			$linkTypeListDesc[$key] = $row['description'];
		}
		
		$data['linkTypeList'] = $linkTypeList;
		$data['descs'] = json_encode($linkTypeListDesc);
		
		if($id){
			$activity = Activity::m_getInfo($id);
			$data['activity'] = $activity;
		}
		
		return $this->display('activity-edit',$data);
	}
	
	public function postEdit()
	{
		$input = Input::only('id','title','type','agid','starttime','endtime','istop','isshow','ishot','content','redirect_type','linktype','link','sort');
		
		$rule = array(
		    'title'=>'required',
		    'type'=>'required',
		    'starttime'=>'required',
		    'endtime'=>'required'
		);
		
	    $validator = Validator::make($input,$rule);
		if($validator->fails()){
			if($validator->messages()->has('title')){
				return $this->back()->with('global_tips','标题不能为空');
			}
		    if($validator->messages()->has('type')){
				return $this->back()->with('global_tips','请填写活动类型');
			}		    
		    if($validator->messages()->has('starttime')){
				return $this->back()->with('global_tips','请选择活动开始时间');
			}
		    if($validator->messages()->has('datetime')){
				return $this->back()->with('global_tips','请选择活动结束时间');
			}
		}
		
		$data['id'] = (int)$input['id'];
		$data['title'] = $input['title'];
		$data['type'] = $input['type'];
		$data['agid'] = $input['agid'];
		$data['starttime'] = strtotime($input['starttime']);
		$data['endtime'] = strtotime($input['endtime']);
		$data['istop'] = (int)$input['istop'];
		$data['ishot'] = (int)$input['ishot'];
		$data['isshow'] = (int)$input['isshow'];
		$data['content'] = $input['content'];
		$data['redirect_type'] = $input['redirect_type'];
		$data['linktype'] = $input['linktype'];
		$data['link'] = $input['link'];
		$data['sort'] = (int)$input['sort'];
		$data['apptype'] = 2;
		$data['adddate'] = date('Y-m-d');
		
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('listpic')){
	    	
			$file = Input::file('listpic'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$input['pic'] = $dir . $new_filename . '.' . $mime;
		}
		
		$success = Activity::m_save($data);
		if($success){
			return $this->redirect('a_activity/activity/index','活动保存成功');
		}
		return $this->back('活动保存失败');
	}
	
	public function getDoStatus()
	{
		$atid = Input::get('atid');
		$field = Input::get('field');
		$status = Input::get('status');
		if($field=='isshow' || $field=='istop'){
		    $success = Activity::m_save(array('id'=>$atid,$field=>$status));
		}
		return $this->json($status);
	}
	
	public function getDelete($id)
	{
		$success = Activity::m_delete($id);
		return $this->back('删除成功');
	}
}