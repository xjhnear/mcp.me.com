<?php
namespace modules\comment\controllers;
use Yxd\Services\UserService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use modules\comment\models\CommentModel;
use Yxd\Services\Cms\CommentService;

class CommentsController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'comment';
	}
	
    public function getIndex()
	{		
		$search = Input::only('startdate','enddate','keyword','target_table','target_id','uid','recycle');
		$page = Input::get('page',1);
		$pagesize = 20;
		$types = array('all'=>'全部','m_news'=>'新闻','m_gonglue'=>'攻略','m_feedback'=>'评测','m_game_notice'=>'新游','m_games'=>'游戏','m_videos'=>'视频','yxd_forum_topic'=>'帖子','m_xyx_game'=>'小游戏');
		$data = array();
		$data['types'] = $types;		
		$totalcount = CommentModel::searchCount($search);
		$data['commentlist'] = $datalist = CommentModel::searchList($search,$page,$pagesize);
		$uids = $users = array();
		foreach($datalist as $row){
			$uids[] = $row['uid'];
		}		
		$uids = array_unique($uids);
		$users = UserService::getBatchUserInfo($uids);
		$data['users'] = $users;
		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalcount;	
		$data['show_post'] = $search['target_table'] && $search['target_id'] ? true : false;
		return $this->display('comment-list',$data);
	}
	
	public function getSearch()
	{
		return $this->getIndex();
	}
	
	public function getAdd($target_table,$target_id)
	{
		$data = array();
		$data['target_table'] = $target_table;
		$data['target_id'] = $target_id;
		return $this->display('comment-edit',$data);
	}
	
	public function postAdd()
	{
		$input = Input::only('target_id','target_table','uid','content');
		
		if($input['uid']){
		    $exists = UserService::getUserInfo($input['uid'],'short');
		    if(!$exists) return $this->back('UID错误，用户不存在');
		}else{
			return $this->back('UID错误，用户不存在');
		}
		
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('listpic')){
            $file = Input::file('listpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $listpic = $dir . $new_filename . '.' . $mime;
        }else{
            $listpic = '';
        }
		
		$comment = array(
			'uid'=>$input['uid'],
			'target_id'=>$input['target_id'],
			'target_table'=>$input['target_table'],
			'content'=>json_encode(array(array('text'=>$input['content'],'img'=>$listpic))),
		    'format_content'=>$input['content'],
		    'pid'=>0,
		    'is_admin'=>0,
		    'addtime'=>time()
		);
		
		$id = CommentService::createComment($comment,null);
		if(is_numeric($id) && $id>0){
			$route = 'comment/comments/search?target_table='.$input['target_table'].'&target_id='.$input['target_id'];
			return $this->redirect($route,'评论成功');
		}
		return $this->back('评论失败');
	}
	
	public function getDel($id)
	{
		$result = CommentModel::doDelete($id);
		$this->operationPdoLog('删除评论', $id);
		if($result){
			$tips = '删除成功';
		}else{
			$tips = '删除失败';
		}
		return $this->back()->with('global_tips',$tips);
	}
	
	public function postDel()
	{
		$ids = Input::get('ids');
	    $result = CommentModel::doDelete($ids);
		if($result){
			return $this->json(array('status'=>200));
		}else{
			return $this->json(array('status'=>600));
		}
	}
	
	
	
	public function getRestore($id)
	{
		CommentModel::restoreComment($id);
		return $this->back()->with('global_tips','恢复成功');
	}
	
	public function getWebSetting()
	{
		return $this->display('web-setting');
	}
}