<?php
namespace modules\activity\controllers;


use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\V4\Activity\Model\Club;
use Youxiduo\V4\Activity\Model\ClubGame;

class ClubController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'activity';
	}
	
	public function getList()
	{
		$data = array();
		$search = Input::only('keyword');
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = Club::search($search,$pageIndex,$pageSize);
		$pager = Paginator::make(array(),$result['total'],$pageSize);
		$pager->appends($search);
		$data['datalist'] = $result['result'];
		$data['pagelinks'] = $pager->links();
		return $this->display('club-list',$data);
	}
	
	public function getEdit($id=0)
	{
		$data = array();
		if($id){
			$data['club'] = Club::getInfo($id);
		}
		return $this->display('club-info',$data);
	}
	
	public function postEdit()
	{
		$input = Input::only('id','name','qq','comqq','weixin','prompt','ename');
		Club::save($input);
		return $this->redirect('activity/club/list','公会信息保存成功');
	}
	
	public function getGameList($club_id=0)
	{
		$data = array();
		$search = Input::only('club_id','keyword');
		if($club_id) $search['club_id'] = $club_id;
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$result = ClubGame::search($search,$pageIndex,$pageSize);
		$pager = Paginator::make(array(),$result['total'],$pageSize);
		$pager->appends($search);
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result['result'];
		$data['club_id'] = $club_id;
		return $this->display('club-game-list',$data);
	}
	
	public function getEditGame($id=0)
	{
		$data = array();
	    if($id){
			$data['game'] = ClubGame::getInfo($id);
		}
		$club_id = Input::get('club_id',0);
		$data['club_id'] = $club_id;
		return $this->display('club-game-info',$data);
	}
	
	public function postEditGame()
	{
		$input = Input::only('id','game_name','club_id','download_url','sort','rebate_info');
		$input['is_show'] = (int)Input::get('is_show');
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    
	    //ICON
	    if(Input::hasFile('game_icon')){
	    	
			$file = Input::file('game_icon'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$input['game_icon'] = $dir . $new_filename . '.' . $mime;
		}
		
	    //大图
	    if(Input::hasFile('list_pic')){
	    	
			$file = Input::file('list_pic'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$input['list_pic'] = $dir . $new_filename . '.' . $mime;
		}
		
		ClubGame::save($input);
		return $this->redirect('activity/club/game-list/'.$input['club_id'],'公会信息保存成功');
	}
	
	public function getDeleteGame($id)
	{
		ClubGame::delete($id);
		return $this->back('游戏删除成功');
	}
}