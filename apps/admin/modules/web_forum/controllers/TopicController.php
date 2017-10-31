<?php
namespace modules\web_forum\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Youxiduo\V4\User\UserService;
use Youxiduo\Bbs\TopicService;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Bbs\Model\BbsAppend;

class TopicController extends BackendController
{
    private $bk_type = array();
    private $genre =1;
    public function _initialize()
    {
        $this->current_module = 'web_forum';
        $board_result = TopicService::getBoardList('','',null,null,false,1);
        if(!$board_result['errorCode'] && is_array($board_result['result'])){
            foreach ($board_result['result'] as $k=>$board) {
                $this->bk_type[$board['bid']] = $board['name'];
            }
            ksort($this->bk_type);
        }
    }

    public function getBbsSearch(){
        $search = Input::only('bbs_id','startdate','enddate','keytype','keyword','sort','uid','s_type');
        $notice = Input::get('notice',0);
        $page = Input::get('page',1);
        $pagesize = 10;
        $fid = $subj = $uid = $tid = '';
        $sort = $search['sort'];
        $keytype = $search['keytype'];
        $s_type = $search['s_type'];
        switch($sort){
            case 'dateline':
                $sort = 0;break;
            case 'replies':
                $sort = 1;break;
            default:
                $sort = 0;
        }

        switch($keytype){
            case 'title':
                $subj = $search['keyword'];break;
            case 'uid':
                $uid = $search['keyword'];break;
            case 'tid':
                $tid = $search['keyword'];break;
        }

        $isGood = $isTop = $isAdmin = null;
        $active = 'true';
        if($s_type){
            in_array(1,$s_type) && $isGood = 'true';
            in_array(2,$s_type) && $isTop = 'true';
            in_array(3,$s_type) && $active = 'false';
            in_array(4,$s_type) && $isAdmin = 'true';
        }

        if($search['startdate']) $search['startdate'] = date('Y-m-d H:i:s',strtotime($search['startdate']));
        if($search['enddate']) $search['enddate'] = date('Y-m-d H:i:s',strtotime($search['enddate']));
        if($search['bbs_id']) $fid = $search['bbs_id'];
        $display_order = $notice==1 ? false : 0;
        $result = TopicService::getPostsList($fid,'','',$page,$pagesize,$subj,$sort,false,false,$isGood,$active,$uid,$tid,$search['startdate'],$search['enddate'],$display_order,$isTop,0,'true',false,false,$isAdmin);
        $totalcount = TopicService::getPostsNum($fid,'',$uid,$tid,$isTop,$active,$isGood,'','',$sort,'',$search['startdate'],$search['enddate'],$subj,'true',false,false,$display_order);

        if(!$result['errorCode'] && $result['result']) {
            $uids = $fids = $gids = $fid_gid = $games = array();
            foreach ($result['result'] as $row) {
                $uids[] = $row['uid'];
                $row['fid'] && $fids[] = $row['fid'];
            }
            $fids = implode(',', array_unique($fids));
            $bbs_res = Topicservice::getForums('',1,10,1,$fids);
            if (!$bbs_res['errorCode'] && $bbs_res['result']) {
                $bbs = array();
                foreach ($bbs_res['result'] as &$item) {
                    $item['logo'] = Utility::getImageUrl($item['logo']);
                    $bbs[$item['fid']] = $item;
                }

                foreach ($result['result'] as &$topic) {
                    if(array_key_exists($topic['fid'],$bbs)){
                        $topic['forum'] = $bbs[$topic['fid']];
                    }else{
                        $topic['forum'] = false;
                    }
                }
            }

            $uinfos = UserService::getMultiUserInfoByUids($uids);
            if (is_array($uinfos)) {
                foreach ($uinfos as $row) {
                    $uinfos[$row['uid']] = $row;
                }
            }
            $data['users'] = $uinfos;
        }
        $data['datalist'] = $result['errorCode'] ? array() : $result['result'];
        $totalcount = $totalcount['errorCode'] ? 0 : $totalcount['totalCount'];
        $pager = Paginator::make(array(),$totalcount,$pagesize);
        $pager->appends($search);
        $data['bk_type'] = $this->bk_type;
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $totalcount;
        $data['notice'] = $notice;
        return $this->display('/4web/topic-list',$data);
    }

    public function getBbsAdd(){
        $vdata['bk_type'] = $this->bk_type;
        return $this->display('4web/topic-add',$vdata);
    }

    public function postBbsAdd(){
        $input = Input::all();
        $rule = array(
            'fid'=>'required',
            'cid'=>'required|integer|min:1',
            'reward'=>'required_if:cid,2',
            'author_uid'=>'required',
            'subject'=>'required',
            'message'=>'required'
        );
        $prompt = array(
            'fid.required'=>'请选择论坛',
            'reward.required_if'=>'请输入游币值',
            'cid.required'=>'请选择论坛版块',
            'author_uid'=>'发帖人不能为空',
            'subject.required'=>'标题不能为空',
            'message.required'=>'内容不能为空',
        );
        $valid = Validator::make($input,$rule,$prompt);

        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }

        $fid=$input['fid'];
        $content = array();
        $txt = preg_replace('/<[^>]+>/i','',$input['message']);
        $imgs = self::getCoverPic($input['message']);
        $content[] = array('text'=>$txt,'img'=>'');
        foreach($imgs as $img){
            $content[] = array('text'=>'','img'=>$img);
        }
        $content = json_encode($content);

        $bid = $input['cid'];
        $is_ask = $bid == 2 ? 'true' : false;
        $coin = $input['reward'];
        $cut_summary = mb_substr(preg_replace('/<[^>]+>/i','',$input['message']),0,130);
        $summary = strlen($cut_summary) > 130 ? $cut_summary.'...' : $cut_summary;

        $disply_order = (int)$input['displayOrder'];
        $is_top = isset($input['isTop']) && $input['isTop'] ? 'true' : 'false';
        if($input['top_deadline']){
            $top_end_time = date('Y-m-d H:i:s',strtotime($input['top_deadline']));
        }else{
        	$top_end_time = null;
        }
        $reply_invisible = isset($input['reply_invisible']) ? 'true' : 'false';
        $result = TopicService::doPostAdd($fid,$input['author_uid'],$bid,$input['subject'],$content,$coin,1,$disply_order,false,'true',
            false,false,$is_ask,false, false,$summary,implode(',',$imgs),$input['message'],false,0,'true',false,false,$is_top,$top_end_time,
            $reply_invisible);
        if($result['errorCode']==0){
            $limit_res = TopicService::add_replylimit(array('targetId'=>$result['result'],'targetType'=>'TOPIC','limitNum'=>$input['limit'],
                'limitDeadline'=>date('Y-m-d H:i:s',strtotime($input['limit_deadline'])),'limitRate'=>$input['limit_rate'],'limitStatus'=>$input['limit_status'] ? 'true' : 'false'));
            return $this->redirect('/web_forum/topic/bbs-search','发帖成功');
        }else{
            return $this->back()->with(array('global_tips'=>'发帖失败，请稍后重试','err'=>1));
        }
    }

    public function getBbsEdit($tid=''){
        if(!$tid) return Redirect::to('web_forum/topic/bbs-search')->with('global_tips','数据错误');
        $topic_result = TopicService::getPostDetail($tid);
        if($topic_result['errorCode'] || !$topic_result['result']) return Redirect::to('web_forum/topic/bbs-search')->with('global_tips','无效帖子');
        $topic = $topic_result['result'];
        $uinfo = UserService::getUserInfoByUid($topic['uid']);
        $fid = $topic['fid'];
        $forum_result = TopicService::getForumDetail($fid);
        //if($forum_result['errorCode']) return Redirect::to('web_forum/topic/bbs-search')->with('global_tips','无效帖子');
        if($forum_result['errorCode']==0 && isset($forum_result['result'])){
            $forum = $forum_result['result'];
        }else{
        	$forum = array('fid'=>0);
        }
        $data['forum'] = $forum;
        $data['topic'] = $topic;
        $data['uinfo'] = $uinfo;

        $data['bk_type'] = $this->bk_type;
        $params = array();
        $params['targetId']=$tid;
        $params['targetType']='TOPIC';
        $result=TopicService::replylimitList($params);
        if($result['errorCode']==0 && $result['result']){
            $data['topic']['limit']=$result['result']['0'];
        }

        return $this->display('/4web/topic-edit',$data);
    }

    public function postBbsEdit()
    {
        $input = Input::all();        
        $rule = array(
            'tid'=>'required',
            'fid'=>'required',
            'cid'=>'required|integer|min:1',
            'reward'=>'required_if:cid,2',
            'author_uid'=>'required',
            'subject'=>'required',
            'message'=>'required'
        );
        $prompt = array(
            'tid'=>'数据错误',
            'fid.required'=>'请选择论坛',
            'reward.required_if'=>'请输入游币值',
            'cid.required'=>'请选择论坛版块',
            'author_uid'=>'发帖人不能为空',
            'subject.required'=>'标题不能为空',
            'message.required'=>'内容不能为空'
        );
        $valid = Validator::make($input,$rule,$prompt);

        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }

        $tid = $input['tid'];
        $content = array();
        $txt = preg_replace('/<[^>]+>/i','',$input['message']);
        $imgs = self::getCoverPic($input['message']);
        $content[] = array('text'=>$txt,'img'=>'');
        foreach($imgs as $img){
            $content[] = array('text'=>'','img'=>$img);
        }
        $content = json_encode($content);

        $bid = $input['cid'];
        $is_ask = $bid == 2 ? 'true' : false;
        $coin = $input['reward'];
        $cut_summary = mb_substr(preg_replace('/<[^>]+>/i','',$input['message']),0,130);
        $summary = strlen($cut_summary) > 130 ? $cut_summary.'...' : $cut_summary;

        $disply_order = (int)$input['displayOrder'];
        $is_top = isset($input['isTop']) && $input['isTop'] ? 'true' : 'false';
        if($input['top_deadline']){
            $top_end_time = date('Y-m-d H:i:s',strtotime($input['top_deadline']));
        }else{
        	$top_end_time = null;
        }
        $reply_invisible = isset($input['reply_invisible']) ? 'true' : 'false';
        $result = TopicService::modifyTopic($tid,$input['author_uid'],$input['fid'],$bid,$input['subject'],$content,$coin,$input['message'],implode(',',$imgs),false,'true',
            'false','false',$is_top,$top_end_time,$reply_invisible,$disply_order);
        if(!$result['errorCode']){
            if(!$input['limit_id']){
                //添加
                TopicService::add_replylimit(array('targetId'=>$tid,'targetType'=>'TOPIC','limitNum'=>$input['limit'],
                'limitDeadline'=>date('Y-m-d H:i:s',strtotime($input['limit_deadline'])),'limitRate'=>$input['limit_rate'],'limitStatus'=>$input['limit_status'] ? 'true' : 'false'));
            }else{
                //编辑
                TopicService::edit_replylimit(array('id'=>$input['limit_id'],'targetId'=>$tid,'targetType'=>'TOPIC','limitNum'=>$input['limit'],
                    'limitDeadline'=>date('Y-m-d H:i:s',strtotime($input['limit_deadline'])),'limitRate'=>$input['limit_rate'],'limitStatus'=>$input['limit_status'] ? 'true' : 'false',
                    'createTime'=>date('Y-m-d H:i:s',time())));
            }
            return $this->redirect('/web_forum/topic/bbs-search','修改成功');
        }else{
            return $this->back()->with(array('global_tips'=>'修改失败，请稍后重试','err'=>1));
        }
    }

    public function getBbsSearchSelect(){
        $params=array();
        $params['name']='';
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =7;
        if(Input::get('name')) $params['name'] =Input::get('name');
        $result=TopicService::getForums($params['name'],$params['pageIndex'],$params['pageSize'],false,false,false,1);
        $result=self::getdatainfo($result);
        foreach($result['result'] as $key=>$value){
            $datafid[]=!empty($value['fid'])?$value['fid']:'';
        }
        $resultinf=TopicService::getForumAndGameRelation(1,1,$datafid);

        $resultinf=isset($resultinf['errorCode']) && $resultinf['result'] ? $resultinf['result'] : array();
        $datafid=array();
        foreach ($resultinf as $key => $value){
            $datafid[]=$value['gid'];
            foreach($result['result'] as $key_=>$value_){
                if($value_['fid'] == $value['fid']){
                   $result['result'][$key_]['gid']=!empty($value['gid'])?$value['gid']:0;
                }
            }
        }
        $game=GameService::getMultiInfoById(array_flip(array_flip($datafid)),'ios');
        if(!empty($game) && $game != 'game_not_exists'){
           foreach($game as $key => $value){
                $game_[$value['gid']]=$value['gname'];
            }
            foreach($result['result'] as $key=>&$value){
                if(array_key_exists("gid",$value)){
                    $value['gname']=$game_[$value['gid']];
                }
            }
        }
        $count=TopicService::getForumsCount($params['name'],false,1);
        $count=self::getdatainfo($count);
        $result['totalCount']=!empty($count['totalCount'])?$count['totalCount']:0;
        return $this->json(array('html'=>$this->html('/4web/pop-bbs-list',self::processingInterface($result,$params))));
    }

    public function getCheckGameHasBbs(){
        $gameid = Input::get('gid');
        $data = array('state'=>1,'msg'=>'有效');
        if($gameid && is_numeric($gameid)){
            $result = TopicService::getForumAndGameRelation(1,1,false,$gameid);
            if($result['errorCode'] || !$result['result']) {
                $data['state'] = 0;
                $data['msg'] = '该游戏暂无社区，请重新选择';
                return response::json($data);
            }
        }
        return response::json($data);
    }

    public function getReplyList($tid='',$recycle=0){
        if(!$tid) return $this->redirect('/web_forum/topic/search')->with('global_tips','数据错误');
        $topic_result = TopicService::getPostDetail($tid);
        if($topic_result['errorCode'] || !$topic_result['result']) return Redirect::to('web_forum/topic/bbs-search')->with('global_tips','无效帖子');
        $topic = $topic_result['result'];
        $uinfo = UserService::getUserInfoByUid($topic['uid']);
        $page = Input::get('page',1);
        $is_active = $recycle ? 'false' : 'true';
        $limit = 10;
        /* 搜索条件相关 */
        $input = Input::all();
        $keyword = isset($input['keyword']) ? $input['keyword'] : '';
        $floor = isset($input['floor']) ? $input['floor'] : false;
        
        $has_pic = (isset($input['hasPic']) && $input['hasPic']) ? 'true':null;        
        $add_user = (isset($input['addUser']) && $input['addUser']) ? 'true':'false';
        
        $result = TopicService::getReplyCommentList($tid,$page,$limit,1,'TOPIC',false,$is_active,false,'','','false',
            $keyword,$has_pic,$floor,$add_user);
        $total_result = TopicService::getReplyTotalCount($tid,'',$is_active,false,'',null,null,null,$keyword,$floor,$has_pic);
        $total = $total_result['errorCode'] ? 0 : $total_result['totalCount'];
        $vdata['reply_list'] = $uids = array();
        if(!$result['errorCode']){
            $replies = $result['result']['replys'];
            $wait_add_uids = isset($result['result']['replierList']) ? $result['result']['replierList'] : array();
            //print_r($wait_add_uids);exit;
            if($add_user==true && $wait_add_uids){
	            $admin_id = $this->current_user['id'];
	            $keyname = 'selected_' . $admin_id . '_uids';
				$selecteds = array();
				if(Session::has($keyname)){
					$selecteds = Session::get($keyname);
				}
				foreach($wait_add_uids as $uid){
				    $selecteds[$uid]  = array('uid'=>$uid,'nickname'=>'玩家'.$uid);
				}
				Session::put($keyname,$selecteds);
            }
            if($replies){
                foreach ($replies as $row) {
                    $uids[] = $row['replier'];
                }
                $uinfos = \Yxd\Services\UserService::getBatchUserInfo($uids);
                $vdata['reply_list'] = $this->filterReplyData($replies,$uinfos);
            }
        }
        $vdata['total_count'] = $total;
        $vdata['all_total_count'] = $result['totalCount'];
        $vdata['tid'] = $tid;
        $vdata['recycle'] = $recycle;
        $vdata['topic'] = $topic;
        $vdata['uinfo'] = $uinfo;
        $vdata['search'] = array('keyword'=>$keyword,'floor'=>$floor,'addUser'=>$add_user,'hasPic'=>$has_pic);
        $vdata['paginator'] = Paginator::make(array(),$total,$limit)->links();
        return $this->display('reply-list',$vdata);
    }

    public function getCommentList($rid=''){
        if(!$rid) return $this->redirect('/web_forum/topic/search')->with('global_tips','数据错误');
        $page = Input::get('page',1);
        $limit = 10;
        $vdata['comments'] = $uids = array();
        $result = TopicService::getComments($rid,$page,$limit);
        $total_result = TopicService::getCommentsCount($rid);
        $total = $total_result['errorCode'] ? 0 : $total_result['totalCount'];
        if(!$result['errorCode']){
            $comments = $result['result'];
            if($comments){
                foreach ($comments as $row) {
                    $uids[] = $row['uid'];
                }
                $uinfos = UserService::getMultiUserInfoByUids($uids);
                if(is_array($uinfos)){
                    foreach ($uinfos as $row) {
                        $uinfos[$row['uid']] = $row;
                    }

                }
                $vdata['comment_list'] = $this->filterCommentData($comments,$uinfos);
            }
        }
        $vdata['total_count'] = $total;
        $vdata['paginator'] = Paginator::make(array(),$total,$limit)->links();
        return $this->display('comment-list',$vdata);
    }

    private function filterCommentData($comments,$uinfos){
        if(!$comments || !$uinfos) return array();
        $result_comments = array();
        foreach($comments as $row){
            $has_user = array_key_exists($row['uid'],$uinfos) ? true : false;
            $result_comments[] = array(
                'cid' => $row['id'],
                'uid' => $row['uid'],
                'name' => $has_user ? $uinfos[$row['uid']]['nickname'] : '该用户不存在或已删除',
                'avatar' => $has_user ? Utility::getImageUrl($uinfos[$row['uid']]['avatar']) : Config::get('app.bbs_default_avatar'),
                //'level_icon' => $has_user ? Utility::getImageUrl($uinfos[$row['uid']]['level_icon']) : '',
                'content' => isset($row['isAdmin']) && $row['isAdmin'] ? $row['formatContent'] : (isset($row['content']) ? TopicService::formatTopicMessage($row['content']) : ''),
                'add_time' => $row['createTime'],
                'is_active' => $row['isActive'] ? true :false
            );
        }
        return $result_comments;
    }

    private function filterReplyData($replies,$uinfos){
        if(!$replies || !$uinfos) return array();
        $result_replies = array();
        foreach($replies as $row){
            $has_user = array_key_exists($row['replier'],$uinfos) ? true : false;
            $reply = array(
                'rid' => $row['id'],
                'uid' => $row['replier'],
                'rname' => $has_user ? $uinfos[$row['replier']]['nickname'] : '用户不存在或已删除',
                'avatar' => $has_user ? Utility::getImageUrl($uinfos[$row['replier']]['avatar']) : Config::get('app.bbs_default_avatar'),
                'level_icon' => $has_user ? Utility::getImageUrl($uinfos[$row['replier']]['level_icon']) : '',
                'content' => isset($row['isAdmin']) && $row['isAdmin'] ? $row['formatContent'] : (isset($row['content']) ? TopicService::formatTopicMessage($row['content']) : ''),
                'add_time' => $row['createTime'],
                'floor' => $row['floor'],
                'is_best' => $row['isBest'] ? true : false,
                'is_active' => $row['isActive'] ? true : false,
//                'comments' => $row['comments'] ? true : false
            );
            $result_replies[] = $reply;
        }
        return $result_replies;
    }

    public function getReplyAdd($tid=''){
        if(!$tid) return $this->back()->with('global_tips','数据错误');
        return $this->display('reply-add',array('tid'=>$tid));
    }

    public function postReplyAdd(){
        $input = Input::all();
        $rule = array('tid'=>'required','replier_id'=>'required|uservalid|numeric','message'=>'required');
        $prompt = array('tid.required'=>'数据错误','replier_id.required'=>'请填写回复人','replier_id.numeric'=>'用户ID格式错误','replier_id.uservalid'=>'用户不存在','message.required'=>'请输入回复内容');
        Validator::extend('uservalid',function($attr,$val){
            $uinfo = UserService::getUserInfoByUid($val);
            return is_array($uinfo) ? true : false;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }
        $content = array();
        $txt = preg_replace('/<[^>]+>/i','',$input['message']);
        $imgs = self::getCoverPic($input['message']);
        $content[] = array('text'=>$txt,'img'=>'');
        foreach($imgs as $img){
            $content[] = array('text'=>'','img'=>$img);
        }
        $content = json_encode($content);
        $format_content = $txt;
        $result = TopicService::doReplyAdd($input['replier_id'],$input['tid'],$content,$format_content,'',false,'TOPIC',$input['platform'],implode(',',$imgs));
        if($result && !$result['errorCode']){
            return $this->redirect('web_forum/topic/reply-list/'.$input['tid'],'回复成功');
        }else{
            return $this->redirect('web_forum/topic/reply-list/'.$input['tid'],'回复失败');
        }
    }

    public function getEditReply($rid=''){
        if(!$rid) return $this->back()->with('global_tips','数据错误');
        $result = TopicService::getReplyCommentList('',1,1,0,'TOPIC','','true',false,$rid);
        if($result && !$result['errorCode']){
            $data = $result['result']['replys'][0];
            $data['content'] = isset($data['isAdmin']) && $data['isAdmin'] ? $data['formatContent'] : (isset($data['content']) ? TopicService::formatTopicMessage($data['content']) : '');
            return $this->display('reply-edit',array('data'=>$data));
        }else{
            return $this->back()->with('global_tips','查询出错');
        }
    }

    public function postEditReply(){
        $id = Input::get('id',false);
        if(!$id) return $this->back('数据错误');
        $input = Input::all();
        $rule = array('id'=>'required','replier_id'=>'required|uservalid|numeric','message'=>'required');
        $prompt = array('tid.required'=>'数据错误','replier_id.required'=>'请填写回复人','replier_id.numeric'=>'用户ID格式错误','replier_id.uservalid'=>'用户不存在','message.required'=>'请输入回复内容');
        Validator::extend('uservalid',function($attr,$val){
            $uinfo = UserService::getUserInfoByUid($val);
            return is_array($uinfo) ? true : false;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }
        $content = array();
        $txt = preg_replace('/<[^>]+>/i','',$input['message']);
        $imgs = self::getCoverPic($input['message']);
        $content[] = array('text'=>$txt,'img'=>'');
        foreach($imgs as $img){
            $content[] = array('text'=>'','img'=>$img);
        }
        $content = json_encode($content);
        $format_content = $txt;
        $result = TopicService::updateReply($id,$input['replier_id'],$content,$format_content,implode(',',$imgs),'false');
        if($result && !$result['errorCode']){
            return $this->redirect('web_forum/topic/edit-reply/'.$id,'更新成功');
        }else{
            return $this->back('更新失败');
        }
    }

    public function getDelReply($rid='',$uid=''){
        if(!$rid || !$uid) return $this->back()->with('global_tips','数据错误');
        $result = TopicService::delReply($rid,'true','false',$uid);
        if($result['errorCode']){
            return $this->back()->with('global_tips','删除失败');
        }else{
            return $this->back()->with('global_tips','删除成功');
        }
    }

    public function getResReply($rid='',$uid=''){
        if(!$rid) return $this->back()->with('global_tips','数据错误');
        $result = TopicService::delReply($rid,'true','true',$uid);
        if($result['errorCode']){
            return $this->back()->with('global_tips','恢复失败');
        }else{
            return $this->back()->with('global_tips','恢复成功');
        }
    }

    public function getDelComment($cid='',$uid=''){
        if(!$cid || !$uid) return $this->back()->with('global_tips','数据错误');
        $result = TopicService::delComment($cid,$uid);
        if($result['errorCode']){
            return $this->back()->with('global_tips','删除失败');
        }else{
            return $this->back()->with('global_tips','删除成功');
        }
    }

    /**
     * 删帖
     * @param $tid
     * @param bool $back
     * @return
     */
    public function getBbsDel($tid,$back=true){
        $result = TopicService::delTopic($tid);
        $this->operationPdoLog('帖子删除', $tid);
        return $this->redirect('web_forum/topic/bbs-search')->with('global_tips',$result['errorCode'] ? '删除失败' : '删除成功');
    }

    /**
     * 批量删帖
     */
    public function postBbsDel()
    {
        $tids = Input::get('tids');
        $tids_str = implode(',',$tids);
        if(!$tids_str) return $this->json(array('status'=>0,'msg'=>'数据错误,请刷新后重试'));
        $result = TopicService::delTopic($tids_str);
        $this->operationPdoLog('帖子删除', $tids);
        return $this->json(array('status'=>$result['errorCode'] ? 0 : 1,'msg'=>$result['errorCode'] ? '操作失败' : '操作成功'));
    }

    /**
     * 加精
     */
    public function postBbsDigest()
    {
        $act= Input::get('act');
        $tid = Input::get('tid');
        if($act=='on'){
            $result = TopicService::setTopicStatus($tid,false,'true');
        }elseif($act=='off'){
            $result = TopicService::setTopicStatus($tid,false,'false');
        }
        return $this->json(array('status'=>$result['errorCode'] ? 0 : 1,'msg'=>$result['errorCode'] ? '操作失败' : '操作成功'));
    }

    /**
     * 置顶
     */
    public function postBbsStick()
    {
        $act= Input::get('act');
        $tid = Input::get('tid');
        if($act=='on'){
            $result = TopicService::setTopicStatus($tid,'true');
        }elseif($act=='off'){
            $result = TopicService::setTopicStatus($tid,'false');
        }
        return $this->json(array('status'=>$result['errorCode'] ? 0 : 1,'msg'=>$result['errorCode'] ? '操作失败' : '操作成功'));
    }


     /**
     * 获取帖子内容中所有的图片
     * @param $content
     * @return array
     */
    private function getCoverPic($content){
        $cover_pics = array();
        if($content){
            preg_match_all("<img.*?src=\"(.*?.*?)\".*?>",$content,$matches);
            if($matches[1]){
                foreach ($matches[1] as $item) {
                    $pra_url_arr_1 = explode('?',$item);
                    $pra_url_arr_2 = explode('/',$pra_url_arr_1[0]);
                    $cover_pics[] = $pra_url_arr_2[count($pra_url_arr_2)-1];
                }
            }
        }
        return $cover_pics;
    }

    public function getBbsSelect(){
        $keyword = Input::get('q');
        $bbs_res = TopicService::getForums($keyword,1,10,1);
        $data = array();
        if(!$bbs_res['errorCode'] && $bbs_res['result']){
            foreach($bbs_res['result'] as $row){
                $data[] = array('id'=>$row['fid'],'text'=>$row['name']);
            }
        }
        return $this->json(array('bbs_list'=>$data));
    }

    public function getBbsInit(){
        $fid = Input::get('id');
        $bbs_res = TopicService::getForums('',1,10,1,$fid);
        $data = array('id'=>false,'text'=>false);
        if(!$bbs_res['errorCode'] && $bbs_res['result']){
            $data['id'] = $bbs_res['result'][0]['fid'];
            $data['text'] = $bbs_res['result'][0]['name'];
        }
        return $this->json($data);
    }

    private function getdatainfo($result){
        if($result['errorCode'] != null)    return !empty($result)?$result:array();
        return array('result'=>array());
    }

    private static function processingInterface($result,$params){
        $data['totalCount'] =!empty($result['totalCount'])?$result['totalCount']:0;
        $pager = Paginator::make(array(),$data['totalCount'],$params['pageSize']);
        unset($params['pageIndex']);
        $pager->appends($params);
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = !empty($result['result'])?$result['result']:array();
        return $data;
    }
}