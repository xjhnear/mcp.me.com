<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/8/10
 * Time: 11:53
 */
namespace modules\web_forum\controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use libraries\Helpers;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Youxiduo\Bbs\TopicService;
use Youxiduo\V4\Game\GameService;
use Illuminate\Support\Facades\Paginator;


class ForumController extends BackendController{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize(){
        $this->current_module = 'web_forum';
    }

    public function getForumList(){
        $vdata = array('list'=>array(),'totalcount'=>0);
        $name = Input::get('name','');
        $page = Input::get('page',1);
        
        $size = 15;
        if(Input::get('name')) $params['name'] =Input::get('name');
        $forum_res = TopicService::getForums($name,$page,$size);
        if(!$forum_res['errorCode'] && $forum_res['result']) $vdata['list'] = $forum_res['result'];
        $topic_num_res = TopicService::getForumsCount($name);
        if(!$topic_num_res['errorCode']) $vdata['totalcount'] = $topic_num_res['totalCount'];
        
        $search = array('name'=>$name);
        $pager = Paginator::make(array(),$vdata['totalcount'],$size);
		$pager->appends($search);
		$vdata['search'] = $search;
		$vdata['pagelinks'] = $pager->links();
        
        return $this->display('/4web/forum-list',$vdata);
    }
    
    public function getForumNumber()
    {
    	$data = array('topic_num'=>0,'reply_num'=>0);
    	$type = Input::get('type');
    	$search = array('type'=>$type);
    	$start_time = Input::get('start_time');
    	$end_time = Input::get('end_time');
        $s_time_start = !empty($start_time) ? date('Y-m-d H:i:s',strtotime($start_time)) : null;    	
    	$s_time_end = !empty($end_time) ? date('Y-m-d H:i:s',strtotime($end_time)) : null;
    	
        $topic_num_res = TopicService::getTopicNumber('','',$s_time_start,$s_time_end);
        if(!$topic_num_res['errorCode']) $data['topic_num'] = $topic_num_res['totalCount'];
        
        $reply_num_res = TopicService::getReplyTotalCount(null,'',false,false,'',1,$s_time_start,$s_time_end);
        if(!$topic_num_res['errorCode']) $data['reply_num'] = $reply_num_res['totalCount'];
        
        $search['start_time'] = $s_time_start;
        $search['end_time'] = $s_time_end;        
        $data['search'] = $search;
        return $this->display('/4web/forum-list',$data);
    }

    public function getAdd(){
        return $this->display('/4web/forum-add');
    }

    public function postAdd(){
        $input = Input::all();
        $rule = array('forum_name'=>'required','gid'=>'required');
        $prompt = array('forum_name.required'=>'请填写论坛名称','gid.required'=>'请选择游戏');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $game_info = GameService::getOneInfoById($input['gid'],self::GENRE_STR);
        $path = $game_info['ico'];
        if(Input::hasFile('top_banner')){
            $dir = '/userdirs/forum/top_banner/';
            $path = Helpers::uploadPic($dir,$input['top_banner']);
        }
        $add_res = TopicService::addForum($input['forum_name'],$path);
        if($add_res['errorCode'] || !$add_res['result']) return $this->back()->withInput()->with('global_tips','添加失败');
        $rel_res = TopicService::saveForumAndGameRelation($add_res['result'],$input['gid'],self::GENRE);
        if(!$rel_res['errorCode'] && $rel_res['result']){
            return $this->redirect('web_forum/forum/forum-list','添加成功');
        }else{
            return $this->back()->withInput()->with('global_tips','添加失败');
        }
    }

    public function getEdit($fid=''){
        if(!$fid) return $this->back('数据错误');
        $game_info = array();
        $info_res = TopicService::getForumDetail($fid);
        if($info_res['errorCode'] || !$info_res['result']) return $this->back('论坛不存在');
        $rel_res = Topicservice::getForumAndGameRelation(1,self::GENRE, $fid );
        if(!$rel_res['errorCode'] && $rel_res['result']){
            $game_info = GameService::getOneInfoById($rel_res['result'][0]['gid'],self::GENRE_STR);
        }
        return $this->display('/4web/forum-edit',array('finfo'=>$info_res['result'],'ginfo'=>$game_info));
    }

    public function postEdit(){
        $input = Input::all();
        $rule = array('fid'=>'required','forum_name'=>'required','gid'=>'required');
        $prompt = array('fid.required'=>'数据错误','forum_name.required'=>'请填写论坛名称','gid.required'=>'请选择游戏');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $game_info = GameService::getOneInfoById($input['gid'],self::GENRE_STR);
        $path = $game_info['ico'];
        if(Input::hasFile('top_banner')){
            $dir = '/userdirs/forum/top_banner/';
            $path = Helpers::uploadPic($dir,$input['top_banner']);
        }
        $add_res = TopicService::updateForum($input['fid'],$input['forum_name'],$path); //改成编辑，暂未提供
        if($add_res['errorCode'] || !$add_res['result']) return $this->back()->withInput()->with('global_tips','添加失败');
        $rel_res = TopicService::saveForumAndGameRelation($add_res['result'],$input['gid'],self::GENRE);
        if($add_res['errorCode']==0){
            return $this->redirect('web_forum/forum/forum-list','修改成功');
        }else{
            return $this->back()->withInput()->with('global_tips','修改失败');
        }
    }
    
    public function getOpen($fid,$status)
    {
    	$status = $status==1 ? 'true' : 'false';
    	$result = TopicService::openForum($fid,$status);
    	if($result['errorCode']==0){
    		return $this->back('操作成功');
    	}
    	return $this->back('操作失败');
    }

    public function getAjaxDel($fid=''){
        if(!$fid) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        //获取关系
        $rel_res = TopicService::getForumAndGameRelation(1,self::GENRE,$fid);
        if(!$rel_res['errorCode'] && $rel_res['result']){
            TopicService::delForumAndGameRelation($fid,$rel_res['result'][0]['gid'],self::GENRE);
        }
        $result = TopicService::deleteForum($fid);
        if(!$result['errorCode'] && $result['result']){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    }

    public function getForumBoardList($fid=''){
        if(!$fid) return $this->back('数据错误');
        $forum_res = TopicService::getForumDetail($fid);
        if($forum_res['errorCode'] || !$forum_res['result']) return $this->back('论坛不存在');
        $result = TopicService::getForumBoardList($fid);
        if($result['errorCode'] || !$result['result']) return $this->back('数据错误');
        return $this->display('4web/forum-board-list',array('list'=>$result['result'],'forum_info'=>$forum_res['result']));
    }

    public function getAddBoard($fid=''){
        $forum_res = TopicService::getForumDetail($fid);
        $forum_info = $forum_res && !$forum_res['errorCode'] ? $forum_res['result'] : array();
        return $this->display('4web/forum-board-add',array('forum_info'=>$forum_info));
    }

    public function postAddBoard(){
        $input = Input::all();
        $rule = array('forum_name'=>'required');
        $prompt = array('forum_name.required'=>'请填写版块名称');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $type = isset($input['type']) ? 'true' : 'false';
        $fid = $input['fid'];
        $add_res = TopicService::addBoard($input['forum_name'],$type,$fid);
        if(!$add_res['errorCode'] && $add_res['result']){
            return $this->redirect('web_forum/forum/forum-board-list/'.$input['old_fid'],'添加成功');
        }else{
            return $this->redirect('web_forum/forum/forum-board-list/'.$input['old_fid'],'添加失败');
        }
    }
    
    public function getEditBoard($bid,$fid)
    {
    	$board_res = TopicService::getBoardDetail($bid);
    	if($board_res['errorCode']!=0) return $this->back('数据错误');
    	$board = $board_res['result'];
    	
    	$forum_res = TopicService::getForumDetail($board['fid']);
        $forum_info = $forum_res && !$forum_res['errorCode'] ? $forum_res['result'] : array();
        return $this->display('4web/forum-board-edit',array('forum_info'=>$forum_info,'board'=>$board,'old_fid'=>$fid));
    }
    
    public function postEditBoard(){
        $input = Input::all();
        $rule = array('forum_name'=>'required');
        $prompt = array('forum_name.required'=>'请填写版块名称');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $type = isset($input['type']) ? 'true' : 'false';
        $fid = $input['fid'];
        $add_res = TopicService::updateBoard($input['bid'],$input['forum_name'],$type,$fid);
        if($add_res['errorCode']==0){
            return $this->redirect('web_forum/forum/forum-board-list/'.$input['old_fid'],'添加成功');
        }else{
            return $this->redirect('web_forum/forum/forum-board-list/'.$input['old_fid'],'添加失败');
        }
    }

    public function getAjaxOpenCloseBoard(){
        $bid = Input::get('bid');
        if(!$bid) return $this->json(array('state'=>1,'msg'=>'数据错误'));
        $is_open = Input::get('is_open') ? 'true' : 'false';
        $res = TopicService::openCloseBoard($bid,$is_open);
        if(!$res['errorCode'] && $res['result']){
            return $this->json(array('state'=>1,'msg'=>'操作成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'操作失败'));
        }
    }

    public function getCheckGameHasForum(){
        $gameid = Input::get('gid');
        $data = array('state'=>1,'msg'=>'有效');
        if($gameid && is_numeric($gameid)){
            $result = TopicService::getForumAndGameRelation(1,1,false,$gameid);
            if(!$result['errorCode'] && $result['result']) {
                $data['state'] = 0;
                $data['msg'] = '游戏已关联了社区，请重新选择';
                return Response::json($data);
            }
        }
        return Response::json($data);
    }
}
