<?php
namespace modules\forum\controllers;
use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use modules\forum\models\ChannelModel;
use modules\forum\models\TopicModel;

class NoticeController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'forum';
	}
	
	public function getList()
	{
		$data = array();
		$pageIndex = Input::get('page',1);
		$recycle = Input::get('recycle',0);
		$pageSize = 10;
		$datalist = TopicModel::getNoticeList($pageIndex,$pageSize,$recycle);
		$total = TopicModel::getNoticeCount($recycle);
		$gids = array();
		foreach($datalist as $row){
			if($row['gid']) $gids[] = $row['gid'];
		}
		$gids = array_unique($gids);
		$games = array();
		if($gids){
			$games = GameService::getGamesByIds($gids);
		}
		$games[0] = array('shortgname'=>'全局公告');
		$data['games'] = $games;
		$data['datalist'] = $datalist;
		$data['sticklist'] = array('-1'=>'全局置顶','0'=>'不置顶','1'=>'八卦吐槽','2'=>'游戏问答','3'=>'寻找伙伴');
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends(array('recycle'=>$recycle));
		$data['pagelinks'] = $pager->links();
		$data['recycle'] = $recycle;
		return $this->display('notice-list',$data);
	}
	
	public function getAdd($gid=0)
	{		
		$data = array();
		$data['notice'] = array('gid'=>$gid,'type'=>$gid);
		return $this->display('notice-edit',$data);
	}
	
	public function getEdit($tid)
	{
		$data = array();
		$notice = TopicModel::getNoticeInfo($tid);
		$notice['type'] = $notice['gid'];
		if($notice['gid']){
			$game = GameService::getGameInfo($notice['gid']);
			if($game){
				$notice['shortgname'] = $game['shortgname'];
			}
		}
		
		$data['notice'] = $notice;
		return $this->display('notice-edit',$data);
	}
	
	public function postSave()
	{
		$tid = (int)Input::get('tid');
		$type = (int)Input::get('type',0);
		if($type==1){
			$gid = 0;
		}else{
		    $gid = (int)Input::get('game_id');
		}
		$subject = Input::get('subject');
		$message = Input::get('format_message');
		$cid = (int)Input::get('cid',0);
	    $validator = Validator::make(array(
	        'subject'=>$subject,
	        'message'=>$message
	    ),
	    array(
		    'subject'=>'required',
		    'message'=>'required',
		));
		if($validator->fails()){
			if($validator->messages()->has('subject')){
				return $this->back()->with('global_tips','公告标题不能为空');
			}
		    if($validator->messages()->has('message')){
				return $this->back()->with('global_tips','公告内容不能为空');
			}
		}
		
		
		$res = TopicModel::saveNoticeTopic($tid, $gid,$cid , $subject, $message);
		if($res){
			return $this->redirect('forum/notice/list')->with('global_tips','公告保存成功');
		}else{
			return $this->back()->with('global_tips','公告保存失败');
		}
	}
	
	public function getDelete($tid)
	{
		$res = TopicModel::deleteNoticeInfo($tid);
		return $this->back();
	}
	
    public function getDoshow($tid,$status)
	{
		$res = TopicModel::doTopicStatus($tid, $status);
		return $this->back();
	}
	
    public function getRestore($tid)
	{
		$res = TopicModel::restoreTopic($tid,2);
		return $this->back();
	}
}