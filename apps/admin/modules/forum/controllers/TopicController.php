<?php
namespace modules\forum\controllers;


use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Youxiduo\Cms\GameInfo;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Yxd\Services\Cms\GameService;
use Yxd\Services\UserService;
use modules\forum\models\TopicModel;
use modules\forum\models\LikeModel;
use modules\system\models\CreditModel;
use Youxiduo\Bbs\TopicService;

class TopicController extends BackendController
{
    private $bk_type = array();

    public function _initialize()
    {
        $this->current_module = 'forum';
        $board_result = TopicService::getBoardList();
        if(!$board_result['errorCode'] && is_array($board_result['result'])){
            foreach ($board_result['result'] as $k=>$board) {
                $this->bk_type[$board['bid']] = $board['name'];
            }
        }
    }

    public function getSearch()
    {
        $search = Input::only('game_id','startdate','enddate','keytype','keyword','sort','uid','recycle');
        $page = Input::get('page',1);
        $pagesize = 10;
        $sort = Input::get('sort','dateline');
        if(in_array($sort,array('dateline','replies','likes'))){
            $order = array($sort=>'desc');
        }else{
            $order = array('dateline'=>'desc');
        }
        $result = TopicModel::search($search,$page,$pagesize,$order);
        $totalcount = $result['total'];
        $data['datalist'] = $result['result'];
        $uids = $gids = array();
        foreach($result['result'] as $row){
            $uids[] = $row['author_uid'];
            $gids[] = $row['gid'];
        }

        $data['games'] = GameService::getGamesByIds($gids);
        $data['users'] = UserService::getBatchUserInfo($uids);
        $pager = Paginator::make(array(),$totalcount,$pagesize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $totalcount;
        return $this->display('topic-list',$data);
    }

    public function getAdd()
    {
        $data = array();
        $gid = Input::get('game_id',0);
        if($gid){
            $game = GameService::getGameInfo($gid);
            $data['game'] = $game;
        }
        return $this->display('topic-edit',$data);
    }

    public function getEdit($tid)
    {
        $topic = TopicModel::getTopicInfo($tid);
        if($topic){
            $game = GameService::getGameInfo($topic['gid']);
            $data['game'] = $game;
        }
        $data['topic'] = $topic;

        return $this->display('topic-edit',$data);
    }

    public function getResReply($rid=''){
        if(!$rid) return $this->back()->with('global_tips','数据错误');
        return $this->back()->with('global_tips','功能建设中');
    }

    public function getInfo($tid)
    {
        $page = Input::get('page',1);
        $pagesize = 20;
        $data = array();
        $result = TopicModel::getTopicFullInfo($tid,$page,$pagesize);
        if($result){
            $data['topic'] = $result['topic'];
            $data['replies'] = $result['replies'];
            $uids = array();
            $uids[] = $result['topic']['author_uid'];
            foreach($result['replies'] as $row){
                $uids[] = $row['uid'];
            }
            $users = UserService::getBatchUserInfo($uids);
            $data['users'] = $users;
            $total = $result['total'];
            $pager = Paginator::make(array(),$total,$pagesize);
            $data['pagelinks'] = $pager->links();
            $data['totalcount'] = $total;
        }
        return $this->display('topic-info',$data);
    }

    public function postSave()
    {
        $tid = Input::get('tid');
        $gid = Input::get('game_id');
        $cid = Input::get('cid');
        $subject = Input::get('subject');
        //$listpic = Input::get('listpic');
        $summary = Input::get('summary','');
        $reward = (int)Input::get('reward',0);
        $message = Input::get('format_message');
        $highlight = (int)Input::get('highlight',0);

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

        $uid = Input::get('author_uid');
        //验证数据
        $validator = Validator::make(array(
            'gid'=>$gid,
            'cid'=>$cid,
            'author_uid'=>$uid,
            'subject'=>$subject,
            'message'=>$message
        ),
            array(
                'gid'=>'required',
                'cid'=>'required|integer|min:1',
                'author_uid'=>'required|integer',
                'subject'=>'required',
                'message'=>'required',
            ));
        if($validator->fails()){
            if($validator->messages()->has('gid')){
                return $this->back()->with('global_tips','请选择游戏论坛');
            }
            if($validator->messages()->has('cid')){
                return $this->back()->with('global_tips','请选择论坛版块');
            }
            if($validator->messages()->has('subject')){
                return $this->back()->with('global_tips','标题不能为空');
            }
            if($validator->messages()->has('message')){
                return $this->back()->with('global_tips','内容不能为空');
            }
        }
        $user = UserService::getUserInfo($uid);
        if(!$user){
            return $this->back()->with('global_tips','发帖的用户不存在');
        }
        $tid = TopicModel::saveTopicInfo($tid, $gid, $cid, $subject, $message, $listpic, $reward,$uid,$highlight);
        if($tid){
            return $this->redirect('forum/topic/search')->with('global_tips','帖子修改成功');
        }else{
            return $this->back()->with('global_tips','帖子修改失败');
        }
    }

    /**
	 * 删帖
	 */
    public function getDel($tid,$back=true)
	{
		TopicModel::delTopic($tid);
		$this->operationPdoLog('帖子删除', $tid);
		if($back){
		    return $this->back();
		}else{
			return $this->redirect('forum/topic/search')->with('global_tips','帖子删除成功');
		}
	}

    /**
     * 批量删帖
     */
    public function postDel()
    {
        $tids = Input::get('tids');
        TopicModel::delTopic($tids);
        $this->operationPdoLog('帖子删除', $tids);
        return $this->json(array('status'=>200));
    }

    public function getRestore($tid)
    {
        TopicModel::restoreTopic($tid,0);
        return $this->back();
    }

    /**
     * 加精
     */
    public function postDigest()
    {
        $act= Input::get('act');
        $tid = Input::get('tid');
        if($act=='on'){
            TopicModel::updateTopicDigest($tid, 1);
        }elseif($act=='off'){
            TopicModel::updateTopicDigest($tid, 0);
        }
        return $this->json(array('status'=>200));
    }

    /**
     * 置顶
     */
    public function postStick()
    {
        $act= Input::get('act');
        $tid = Input::get('tid');
        if($act=='on'){
            TopicModel::updateTopicStick($tid, 1);
        }elseif($act=='off'){
            TopicModel::updateTopicStick($tid, 0);
        }
        return $this->json(array('status'=>200));
    }

    public function getRule()
    {
        $data = array();
        $data['topic'] = CreditModel::getRuleInfo();
        return $this->display('credit-rule',$data);
    }

    public function postSaveRule()
    {
        $tid = (int)Input::get('tid');
        $subject = Input::get('subject');
        $message = Input::get('format_message','');
        $uid = 1;
        $res = CreditModel::saveRule($tid, $subject, $message, $uid);
        if($res){
            return $this->redirect('forum/topic/rule')->with('global_tips','积分规则保存成功');
        }else{
            return $this->back()->with('global_tips','积分规则保存失败');
        }
    }
    /**
     * 获取点赞的用户列表
     */
    public function getLikes($target_id)
    {
        $pageindex = Input::get('page',1);
        $pagesize = 10;
        $result = LikeModel::getLikeList($target_id, 'topic', $pageindex, $pagesize);
        if(!$result) return $this->back();
        $total = $result['total'];
        $pager = Paginator::make(array(),$total,$pagesize);
        $data['pagelinks'] = $pager->links();
        $likeList = $result['topic_likes'];
        $out = TopicService::getOneTopiclikes($likeList);
        $data['out'] = $out;
        if(!$out) return $this->back();
        return $this->display('like-lists', $data);

    }

    public function getExport($tid,$type)
    {
        if($type=='reply_user'){
            TopicModel::exportDataToUser($tid);
        }elseif($type=='reply_user_floor'){
            TopicModel::exportDataToUserFloor($tid);
        }
        return $this->back();
    }
}