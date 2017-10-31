<?php
namespace modules\weba_forum\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;
use Youxiduo\V4\User\UserService;
use Youxiduo\Bbs\TopicService;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Bbs\Model\BbsAppend;
use Youxiduo\Helper\MyHelpLx;

class TopicController extends BackendController
{
    private $bk_type = array();
    private $genre =2;
    public function _initialize()
    {
        $this->current_module = 'weba_forum';
        $board_result = TopicService::getBoardList();
        if(!$board_result['errorCode'] && is_array($board_result['result'])){
            foreach ($board_result['result'] as $k=>$board) {
                $this->bk_type[$board['bid']] = $board['name'];
            }
            ksort($this->bk_type);
        }
    }

    public function getBbsSearch()
    {
        $search = Input::only('game_id','startdate','enddate','keytype','keyword','sort','uid','recycle','fid');
        $page = Input::get('page',1);
        $pagesize = 10;
        $fid = $subj = $uid = $tid = '';
        if($search['fid']){
            $fid = $search['fid'];
        }
        $sort = $search['sort'];
        $keytype = $search['keytype'];
        switch($sort){
            case 'dateline':
                $sort = 0;break;
            case 'replies':
                $sort = 1;break;
            case 'likes':
                $sort = 2;break;
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

        $active = $search['recycle'] ? 'false' : 'true';
        if($search['startdate']) $search['startdate'] = date('Y-m-d H:i:s',strtotime($search['startdate']));
        if($search['enddate']) $search['enddate'] = date('Y-m-d H:i:s',strtotime($search['enddate']));
        if($search['game_id']){
            //查询该游戏下是否有社区关系
            $fid_result = TopicService::query_game_link_list("true",2,false,$search['game_id']);
            if($fid_result['errorCode'] || !$fid_result['result']) return $this->back()->with('global_tips',isset($fid_result['errorDescription'])?$fid_result['errorDescription']:"无关联游戏");
            $fid = $fid_result['result'][0]['fid'];
        }
        $result = TopicService::getPostsList($fid,'','',$page,$pagesize,$subj,$sort,false,false,false,$active,$uid,$tid,$search['startdate'],$search['enddate'],'',false,0,false,'true',false);
        $totalcount = TopicService::getPostsNum($fid,'',$uid,$tid,'',$active,'','','',0,'',$search['startdate'],$search['enddate'],$subj,false,'true',false);
        if(!$result['errorCode'] && $result['result']) {
            $uids = $fids = $gids = $fid_gid = $games = array();
            foreach ($result['result'] as $row) {
                $uids[] = $row['uid'];
                $row['fid'] && $fids[] = $row['fid'];
            }
            $fids = implode(',', array_unique($fids));
            $gids_result = TopicService::query_game_link_list("true", 2, $fids);
            if (!$gids_result['errorCode'] && $gids_result['result']) {
                foreach ($gids_result['result'] as $item) {
                    $gids[] = $item['gid'];
                    $fid_gid[$item['fid']] = $item['gid'];
                }

                $games = GameService::getMultiInfoById($gids, 'android', 'full');

                if (is_array($games)) {
                    foreach ($games as $game) {
                        $key = current(array_keys($fid_gid,$game['gid']));
                        $tmp[$key] = $game;
                        $tmp[$key]['litpic'] = Utility::getImageUrl($game['ico']);
                    }
                    $games = $tmp;
                }
            }

            $data['games'] = $games;
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
        return $this->display('/4web/topic-list',$data);
    }

    public function getTopicSearch()
    {
        $data = array();
        $pageIndex = Input::get('page',1);
        $pageSize = 5;
        $search = Input::get();
        $title = Input::get('keyword','');
        $type = Input::get('keytype','');
        $data['keyword'] = $title;
        $data['keytype'] = $type;
        $fid = $subj = $uid = $tid = '';
        switch($type){
            case 'title':
                $subj = $title;break;
            case 'uid':
                $uid = $title;break;
            case 'tid':
                $tid = $title;break;
        }
        $res = TopicService::getPostsList('','','',$pageIndex,$pageSize,$subj,'',false,false,false,'true',$uid,$tid,'','','',false,0,false,'true',false);
        $totalcount = TopicService::getPostsNum($fid,'',$uid,$tid,'','true','','','',0,'','','',$subj,false,'true',false);
        $totalcount = $totalcount['errorCode'] ? 0 : $totalcount['totalCount'];
//        print_r($search);
//        print_r($res);
        if(!$res['errorCode']&&$res['result']){
            $total = $totalcount;
            $data['list'] = $res['result'];
        }else{
            $total = 0;
            $data['list']= array();
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
//        print_r($data['list']);
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;
        $html = $this->html('pop-list',$data);
        return $this->json(array('html'=>$html));
    }

    public function getBbsSearchSelect(){
        $params=array();
        $params['name']='';
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =7;
        if(Input::get('name')) $params['name'] =Input::get('name');
        $result=TopicService::getForums($params['name'],$params['pageIndex'],$params['pageSize'],false,2);
        $result=self::getdatainfo($result);
        $datafid=array();
        
        foreach($result['result'] as $key=>$value){
            $datafid[]=!empty($value['fid'])?$value['fid']:'';
        }
         
          $resultinf=TopicService::query_game_link_list("true",2,$datafid);
               
        $resultinf=isset($resultinf['errorCode']) && $resultinf['result'] ? $resultinf['result'] : array();

        foreach ($resultinf as $key => $value){
            $datafid[]=$value['gid'];
            foreach($result['result'] as $key_=>$value_){
                if($value_['fid'] == $value['fid']){
                   $result['result'][$key_]['gid']=!empty($value['gid'])?$value['gid']:0;
                }
            }
        }
        $game=GameService::getMultiInfoById(array_flip(array_flip($datafid)),'android');
        if(!empty($game) && $game != 'game_not_exists'){
           foreach($game as $key => $value){
                $game_[$value['gid']]=$value['gname'];
            }
            foreach($result['result'] as $key=>&$value){
                if(array_key_exists("gid",$value)){
                    $value['gname']=isset($game_[$value['gid']])?$game_[$value['gid']]:"";
                }
            }
        }
        $count=TopicService::getForumsCount($params['name']);
        $count=self::getdatainfo($count);
        $result['totalCount']=!empty($count['totalCount'])?$count['totalCount']:0;
        return $this->json(array('html'=>$this->html('/4web/pop-bbs-list',self::processingInterface($result,$params))));
    }

    public function getCheckGameHasBbs(){
        $gameid = Input::get('gid');
        $data = array('state'=>1,'msg'=>'有效');
        if($gameid && is_numeric($gameid)){
            $result = TopicService::getForumAndGameRelation(1,2,false,$gameid);
            if($result['errorCode'] || !$result['result']) {
                $data['state'] = 0;
                $data['msg'] = '该游戏暂无社区，请重新选择';
                return response::json($data);
            }
        }
        return response::json($data);
    }

    public function getBbsAdd()
    {
        $vdata['bk_type'] = $this->bk_type;
        return $this->display('4web/topic-add',$vdata);
    }

    public function postAdd(){
        $input = Input::all();
        $uid = $input['author_uid'];
        if(!empty($input['limit']) && is_numeric($input['limit'])){
            $limit=$input['limit'];
        }
        unset($input['limit']);
        $rule = array('fid'=>'required','platform'=>'required|validplat','cid'=>'required|integer|min:1','reward'=>'required_if:cid,2','author_uid'=>'required|integer', 'subject'=>'required', 'message'=>'required');
        $prompt = array('fid.required'=>'数据错误，请刷新页面','platform.required'=>'请至少选择一个发布平台','platform.validpalt'=>'数据错误，请刷新页面重试','gid.required'=>'请选择游戏论坛','reward.required_if'=>'请输入游币值','gid.hasforum'=>'该游戏暂无社区，请重新选择','cid.required'=>'请选择论坛版块','author_uid'=>'发帖人ID不能为空','subject.required'=>'标题不能为空','message.required'=>'内容不能为空');
        Validator::extend('hasforum',function($attr,$val){
            $result = TopicService::getForumAndGameRelation(1,2,false,$val);
            return $result['errorCode'] ? false : true;
        });
        Validator::extend('validplat',function($attr,$val,$param){
            $validok = true;
            foreach ($val as $plat) {
                if(!intval($plat)) {
                    $validok = false;
                    break;
                }
            }
            return $validok;
        });
        $valid = Validator::make($input,$rule,$prompt);

        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }
        $user = UserService::getUserInfoByUid($uid);
        if(!$user){
            return $this->back()->withInput()->with('global_tips','发帖的用户不存在');
        }

        $fid=$input['fid'];
        $content = array();
        $txt = preg_replace('/<[^>]+>/i','',$input['message']);
        $img_arr = MyHelpLx::save_imgs($input['picFile']);
        $imgs = $img_arr;

        $content[] = array('text'=>$txt,'img'=>'');
        foreach($imgs as $img){
            $content[] = array('text'=>'','img'=>$img);
        }
        $content = json_encode($content);

        $bid = $input['cid'];
        $is_ask = $bid == 2 ? true : false;
        $coin = $input['reward'];
        $platform_arr = $input['platform'];
        $ios_disp = $and_disp = $web_disp = false;
        foreach ($platform_arr as $k=>$v) {
            $plat = intval($v);
            if(!$plat) unset($platform_arr[$k]);
            if($plat == 1) $ios_disp = 'true';
            if($plat == 2) $and_disp = 'true';
            if($plat == 3) $web_disp = 'true';
        }
        $cut_summary = mb_substr(preg_replace('/<[^>]+>/i','',$input['message']),0,130);
        $summary = strlen($cut_summary) > 50 ? $cut_summary.'...' : $cut_summary;
        $reply_invisible = isset($input['reply_invisible']) ? 'true' : 'false';
        $result = TopicService::doPostAdd($fid,$uid,$bid,$input['subject'],'',$coin,2,0,false,true,false,false,$is_ask,false,
            false,$summary,implode(',',$imgs),$input['message'],false,0,$ios_disp,$and_disp,$web_disp,false,false,
            $reply_invisible);        
        if(!$result['errorCode']){
            if((!empty($limit) || !empty($input['limitStatus']) || !empty($input['limitDeadline']) || !empty($input['limitRate']) || $input['limitStatus']==1) && !empty($result['result'])){
                    $params=array();
                    $params['createTime']= date('Y-m-d H:i:s');
                    $params['targetType']='TOPIC';
                    if(!empty($limit)){
                        $params['limitNum']=intval($limit);
                    }
                    if(!empty($input['limitRate'])){
                        $params['limitRate']=$input['limitRate'];
                    }
                    $params['limitStatus']='false';
                    if(!empty($input['limitStatus']) && $input['limitStatus']){
                        $params['limitStatus']='true';
                    }
                    if(!empty($input['limitDeadline'])){
                        $params['limitDeadline']=$input['limitDeadline'];
                    }
                    $params['targetId']=$result['result'];//print_r($params);exit;
                    //$result=TopicService::add_replylimit(array('limitRate'=>intval($input['limitRate']),'limitStatus'=>$input['limitStatus'],'limitDeadline'=>$input['limitDeadline'],'targetId'=>$result['result'],'targetType'=>'TOPIC','limitNum'=>intval($limit)));
                    $result=TopicService::add_replylimit($params);
                    if($result['errorCode']==0){
                        return $this->redirect('/weba_forum/topic/bbs-search','数据保存成功');
                    }else{
                         return $this->back()->with(array('global_tips'=>'发帖限制失败，请在修改时在次尝试','err'=>1));
                    }
             }
            return $this->redirect('/weba_forum/topic/bbs-search','数据保存成功');
        }else{

            return $this->back()->with(array('global_tips'=>$result['errorDescription'],'err'=>1));
        }
    }

    public function getBbsEdit($tid='')
    {
        if(!$tid) return Redirect::to('weba_forum/topic/bbs-search')->with('global_tips','数据错误');
        $topic_result = TopicService::getPostDetail($tid);
        if($topic_result['errorCode'] || !$topic_result['result']) return Redirect::to('weba_forum/topic/bbs-search')->with('global_tips','无效帖子');
        $topic = $topic_result['result'];
        $fid = $topic['fid'];
        $forum_result = TopicService::getForumDetail($fid);
        if($forum_result['errorCode'] || !$forum_result['result']) return Redirect::to('weba_forum/topic/bbs-search')->with('global_tips','无效帖子');
        $forum = $forum_result['result'];
        $data['forum'] = $forum;
        $data['topic'] = $topic;
        $data['imgs'] = explode(',',$data['topic']['listpic']);

        $data['bk_type'] = $this->bk_type;
        $params['targetId']=$tid;
        $result=TopicService::replylimitList($params);
        if($result['errorCode'] ==0 && !empty($result['result']) && !empty($result['result']['0']['limitNum'])){
            $data['topic']['limit']=$result['result']['0'];
        }
        return $this->display('/4web/topic-edit',$data);
    }

    public function postBbsEdit(){
        $input = Input::all();
        if(!empty($input['limit']) && is_numeric($input['limit'])){
            $limit=$input['limit'];
        }
        unset($input['limit']);
        $rule = array('tid'=>'required','fid'=>'required','platform'=>'required|validplat','cid'=>'required|integer|min:1','reward'=>'required_if:cid,2','author_uid'=>'required|integer', 'subject'=>'required', 'message'=>'required');
        $prompt = array('tid.required'=>'数据错误，请刷新页面','platform.required'=>'请至少选择一个发布平台','platform.validpalt'=>'数据错误，请刷新页面重试','gid.required'=>'请选择游戏论坛','reward.required_if'=>'请输入游币值','gid.hasforum'=>'该游戏暂无社区，请重新选择','cid.required'=>'请选择论坛版块','author_uid'=>'发帖人ID不能为空','subject.required'=>'标题不能为空','message.required'=>'内容不能为空');
        Validator::extend('validplat',function($attr,$val,$param){
            $validok = true;
            foreach ($val as $plat) {
                if(!intval($plat)) {
                    $validok = false;
                    break;
                }
            }
            return $validok;
        });
        $valid = Validator::make($input,$rule,$prompt);

        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }

        $dir = '/bbs/listpic/' . date('Y') .'/'. date('m').'/';
        $path = storage_path() . $dir;
        //列表图
//        if(Input::hasFile('listpic')){
//            $file = Input::file('listpic');
//            $new_filename = date('YmdHis') . str_random(4);
//            $mime = $file->getClientOriginalExtension();
//            $file->move($path,$new_filename . '.' . $mime );
//            $listpic = $dir . $new_filename . '.' . $mime;
//        }else{
//            $listpic = false;
//        }

//        $img_arr = MyHelpLx::save_imgs($input['picFile']);
        if($input['picFile']) {
            foreach ($input['picFile'] as $k => $v) {
                if (empty($v)) {
                    $img_arr[] = $input['img'][$k];
                } else {
                    $img_arr[] = MyHelpLx::save_img($v);
                }
            }
        }
        $listpic = implode(',',$img_arr);

        $user = UserService::getUserInfoByUid($input['author_uid']);
        if(!$user){
            return $this->back()->with('global_tips','发帖的用户不存在');
        }
        $award = Input::get('cid') == 2 ? Input::get('reward') : false;
        $fid=$input['fid'];
        $platform_arr = $input['platform'];
        $ios_disp = $and_disp = $web_disp = 'false';
        foreach ($platform_arr as $k=>$v) {
            $plat = intval($v);
            if(!$plat) unset($platform_arr[$k]);
            if($plat == 1) $ios_disp = 'true';
            if($plat == 2) $and_disp = 'true';
            if($plat == 3) $web_disp = 'true';
        }
        
        $reply_invisible = isset($input['reply_invisible']) ? 'true' : 'false';
        $result = TopicService::modifyTopic($input['tid'],$input['author_uid'],$fid,$input['cid'],$input['subject'],false,$award,$input['message'],$listpic,false,$ios_disp,$and_disp,$web_disp,false,false,$reply_invisible);

        if($result['errorCode'] == 0){
            $params=array();
            $params['createTime']= date('Y-m-d H:i:s');
            $params['targetType']='TOPIC';
            if(!empty($limit)){
                $params['limitNum']=intval($limit);
            }
            if(!empty($input['limitRate'])){
                $params['limitRate']=$input['limitRate'];
            }
            $params['limitStatus']='false';
            if(!empty($input['limitStatus']) && $input['limitStatus']){
                $params['limitStatus']='true';
            }
            if(!empty($input['limitDeadline'])){
                $params['limitDeadline']=$input['limitDeadline'];
            }
            $params['targetId']=$result['result'];
            
            if(!$input['limit_id']){
                //添加
                $result=TopicService::add_replylimit($params);
            }else{
                //编辑
                $result=TopicService::edit_replylimit($params);
            
            }
            
            return $this->redirect('/weba_forum/topic/bbs-search','数据保存成功');
        }
         return $this->back()->with('global_tips','帖子修改失败');
    }

    public function getReplyList($tid='',$recycle=0){
        if(!$tid) return $this->redirect('/weba_forum/topic/search')->with('global_tips','数据错误');
        $page = Input::get('page',1);
        $is_active = $recycle ? 'false' : 'true';
        $limit = 10;
        $result = TopicService::getReplyCommentList($tid,$page,$limit,1,'TOPIC',false,$is_active);
        $total_result = TopicService::getReplyTotalCount($tid,'',$is_active);
        $total = $total_result['errorCode'] ? 0 : $total_result['totalCount'];
        $vdata['reply_list'] = $uids = array();
        if(!$result['errorCode']){
            $replies = $result['result']['replys'];
            if($replies){
                foreach ($replies as $row) {
                    $uids[] = $row['replier'];
                }
                $uinfos = \Yxd\Services\UserService::getBatchUserInfo($uids);
//                if($uinfos){
//                    foreach($uinfos as $user){
//                        $tmp_uinfos[$user['uid']] = $user;
//                    }
//                    $uinfos = $tmp_uinfos;
//                }
                $vdata['reply_list'] = $this->filterReplyData($replies,$uinfos);
            }
        }
        $vdata['total_count'] = $total;
        $vdata['tid'] = $tid;
        $vdata['recycle'] = $recycle;
        $vdata['paginator'] = Paginator::make(array(),$total,$limit)->links();
        return $this->display('reply-list',$vdata);
    }

    public function getCommentList($rid=''){
        if(!$rid) return $this->redirect('/weba_forum/topic/search')->with('global_tips','数据错误');
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
                'level_icon' => $has_user ? Utility::getImageUrl($uinfos[$row['uid']]['level_icon']) : '',
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
            $row['comments'] = isset($row['comments'])?$row['comments']:"";
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
                'comments' => $row['comments'] ? true : false
            );
            $result_replies[] = $reply;
        }
        return $result_replies;
    }

    public function getReplyAdd($tid=''){
        $rid = Input::get("id","");
        $uid = Input::get("uid","");
        $tid = Input::get("tid",$tid);
        $data = array('tid'=>$tid);
        if($rid&&!$uid){
            $result = TopicService::getReplyDetail($rid);
            if($result && !$result['errorCode']){
                $data['data'] = $result['result'];
                $data['imgs'] = explode(',',$data['data']['listpic']);
            }else{
                return $this->back()->with('global_tips','查询出错');
            }
        }
        return $this->display('reply-add',$data);
    }

    public function postReplyAdd(){
        $input = Input::all();
//        print_r($input);
        $input['type'] = "TOPIC";
        $input['isAdmin'] = "false";
        $img_arr = array();
        if($input['picFile']){
            foreach($input['picFile'] as $k=>$v){
                if(empty($v)){
                    $img_arr[] = $input['img'][$k];
                }else{
                    $img_arr[] = MyHelpLx::save_img($v);
                }
        }
        unset($input['picFile']);
        unset($input['img']);
//        $img_arr = MyHelpLx::save_imgs($input['picFile']);unset($input['picFile']);
        }

        $input['listpic'] = implode(',',$img_arr);
        if($input['id']){
            $input1['id'] = $input['id'];
            $input1['message'] = $input['message'];
            $input1['listpic'] = $input['listpic'];
            $res = TopicService::update_reply($input1);
        }else{
            $res = TopicService::add_reply($input);
        }
        if($res && !$res['errorCode']){
            return $this->redirect('weba_forum/topic/reply-list/'.$input['tid'],'成功');
        }else{
            return $this->redirect('weba_forum/topic/reply-list/'.$input['tid'],$res['errorDescription']);
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
            return $this->redirect('weba_forum/topic/edit-reply/'.$id,'更新成功');
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

    public function postDelSomeReply() {
        $rids = Input::get('ids');
        if(count($rids) == 0) return $this->back()->with('global_tips','数据错误');
        $errorCode = '';
        foreach($rids as $row) {
            $temp = explode('/', $row);
            $tempRid = $temp[0];
            $tempUid = $temp[1];
            $res = TopicService::delReply($tempRid,'true','false',$tempUid);
            if ($res['errorCode']) {
                $errorCode = $res['errorCode'];
                break;
            }
        }

        if($errorCode){
            return $this->json(array('status'=>600));
        }else{
            return $this->json(array('status'=>200));
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
        return $this->redirect('weba_forum/topic/bbs-search')->with('global_tips',$result['errorCode'] ? '删除失败' : '删除成功');
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

    /** 2015/3/26 傅佳俊 论坛列表管理开始 **/
    //查询论坛列表
    public function getForumList()
    {
        $params=$datafid=array();
        $params['name']='';
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =15;
        if(Input::get('name')) $params['name'] =Input::get('name');
        $result=TopicService::getForums($params['name'],$params['pageIndex'],$params['pageSize'],false,2);
        $result=self::getdatainfo($result);
        foreach($result['result'] as $key=>$value){
            $datafid[]=!empty($value['fid'])?$value['fid']:'';
        }
        $resultinf=TopicService::query_game_link_list("true",$this->genre,$datafid);
        $resultinf=self::getdatainfo($resultinf);
        $datafid=array();
        foreach ($resultinf['result'] as $key => $value){
            $datafid[]=$value['gid'];
            foreach($result['result'] as $key_=>$value_){
                if($value_['fid'] == $value['fid']){
                   $result['result'][$key_]['gid']=!empty($value['gid'])?$value['gid']:0;
                }
            }
        }
        $game=GameService::getMultiInfoById(array_flip(array_flip($datafid)),($this->genre==1)?'ios':'android');
        if(!empty($game) && $game != 'game_not_exists'){
           foreach($game as $key => $value){
                $game_[$value['gid']]=$value['gname'];
            }
            foreach($result['result'] as $key=>&$value){
                if(array_key_exists("gid",$value)){
                    $value['gname']=isset($game_[$value['gid']])?$game_[$value['gid']]:"";
                }
            }
        }

        $count=TopicService::getForumsCount($params['name']);
        $count=self::getdatainfo($count);
        $result['totalCount']=!empty($count['totalCount'])?$count['totalCount']:0;
        $data = self::processingInterface($result,$params);
        $data['name'] = $params['name'];
        return $this->display('/4web/bbs-list',$data);//
    }

    
    //添加/修改论坛->视图
    public function getBbsAddEdit($fid='')
    {  
       $data['edit']=0;
       
       if(!empty($fid)){
            $result=TopicService::getForums('',1,10,false,2,$fid);
            $result=self::getdatainfo($result);
            $data['edit']=1;
            $bbs=BbsAppend::getBbsinfoByFid($fid);
            if (isset($result['result']['0'])) {
               $data['bbs']=$result['result']['0'];
               if(!empty($bbs['top_pic'])){
                    $data['bbs']['xtop_pic']=$bbs['top_pic'];
                    $data['bbs']['top_pic']=$bbs['top_pic'];
               }
               if(!empty($data['bbs']['logo'])){
                    $data['bbs']['xlogo']=strstr($data['bbs']['logo'], '/bbs/');
               }
               if(!empty($bbs['short_name'])){
                    $data['bbs']['short_name']=$bbs['short_name'];
               }
               if(!empty($bbs['forum_des'])){
                    $data['bbs']['forum_des']=$bbs['forum_des'];
               }
               if(!empty($bbs['short_des'])){
                    $data['bbs']['short_des']=$bbs['short_des'];
               }
            }
       }
       
       return $this->display('/4web/bbs-edit',$data);//    
    }
    
    
    //检查游戏是否在IOS和安卓中有关系
    /***
    public function getMyCheckGameHasBbs(){
        $gameid = Input::get('gid');
        $data = array('state'=>1,'msg'=>'有效');
        if($gameid && is_numeric($gameid)){
            $result = TopicService::getForumAndGameRelation(1,$this->genre=1,false,$gameid);
            if($result['errorCode']!=0){  $data['state'] = 500; $data['msg'] = '该游戏暂无社区，请重新选择'; return response::json($data); }
            if(empty($result['result']['0'])){
               $data['msg'] = '该游戏暂无论坛，可继续添加'; 
               return response::json($data);
            }else{
                //查询安卓是否有关联
                $gameName=Input::get('gameName');
            }
        }
        return response::json($data);
    }
    **/
    //添加/修改论坛->视图
    public function postBbsAddEdit()
    {  
        $input = Input::only('name','short_name','fid','short_des','forum_des');
        $append=array();
        if(empty($input['fid'])){
            $dir = '/bbs/logopic/' . date('Y') .'/'. date('m').'/';
            $path = storage_path() . $dir;
             //论坛LOGO 小图
            if(Input::hasFile('logo')){
//                        $file = Input::file('logo');
//                        $new_filename = date('YmdHis') . str_random(4);
//                        $mime = $file->getClientOriginalExtension();
//                        $file_path =$file->move($path,$new_filename . '.' . $mime );
//                        if($file_path)  $input['logo']=$dir.$new_filename . '.' . $mime;
                $input['logo'] =   MyHelpLx::save_img(Input::file('logo'));
            }else{
                        header("Content-type: text/html; charset=utf-8");
                        return $this->back()->with('global_tips','操作失败->请选择论坛LOGO'); 
            }
            $result=TopicService::getForums($input['name']);
            $result=self::getdatainfo($result);
            if(!empty($result['result']['0'])){ 
                $fid=$result['result']['0']['fid'];
            }else{
                 $result=TopicService::addForum($input['name'],$input['logo']);
                 $result=self::getdatainfo($result);
                 $fid=$result['result'];
            }
         }else{
            if(Input::get('xtop_pic')){
                $append['top_pic']=Input::get('xtop_pic');
            }
            $fid=$input['fid'];
                    
        }
        $append['short_name']=!empty($input['short_name'])?$input['short_name']:'';
        $dir = '/bbs/listpic/' . date('Y') .'/'. date('m').'/';
        $path = storage_path() . $dir;
        //论坛标题大图
        if(Input::hasFile('toppic')){
            $append['top_pic'] =   MyHelpLx::save_img(Input::file('toppic'));
//            $file = Input::file('toppic');
//            $new_filename = date('YmdHis') . str_random(4);
//            $mime = $file->getClientOriginalExtension();
//            $file_path =$file->move($path,$new_filename . '.' . $mime );
//            if($file_path)  $append['top_pic']=$dir.$new_filename . '.' . $mime;
        }
        $append['short_des']=!empty($input['short_des'])?$input['short_des']:'';
        $append['forum_des']=!empty($input['forum_des'])?$input['forum_des']:'';
        if(!empty($fid)){
             $datainfo=BbsAppend::getBbsinfoByFid($fid);
             if(!empty($datainfo)){
                    BbsAppend::update($append,$fid);
             }else{
                $append['fid']=$fid;
                BbsAppend::add($append);
             }
             
        }else{
             
        }
        return $this->redirect('weba_forum/topic/forum-list')->with('global_tips','操作成功');
        
    }


    //删除论坛
    public function getDeleteForum($fid)
    {
        
        $result_ios=TopicService::getForumAndGameRelation(true,1,$fid);
        $result_ios=self::getdatainfo($result_ios);
        $result_a=TopicService::getForumAndGameRelation(true,2,$fid);
        $result_a=self::getdatainfo($result_a);
        if(!empty($result_ios['result']['0']) && !empty($result_ios['result']['0']['gid'])){
            $result=TopicService::delForumAndGameRelation(!empty($fid)?$fid:'',$result_ios['result']['0']['gid'],1);
             $result=self::getdatainfo($result);  

        }
        if(!empty($result_a['result']['0']) && !empty($result_a['result']['0']['gid'])){
            $result=TopicService::delForumAndGameRelation(!empty($fid)?$fid:'',$result_a['result']['0']['gid'],2);
            $result=self::getdatainfo($result);
            
        }
        
        $result=TopicService::deleteForum(!empty($fid)?$fid:'');
        self::getdatainfo($result);
        return $this->redirect('weba_forum/topic/forum-list')->with('global_tips','操作成功');
    }
    //查询IOS游戏列表
    public function  getBbsRelationIos($fid='')
    {   
        $data['fid']=!empty($fid)?$fid:'';
        $data['type']=1;
        $result=TopicService::query_game_link_list("true",1,$fid,false,"IOS");
        $result=self::getdatainfo($result);
        if(empty($result['result'])){
            return $this->display('/4web/bbs-game-ios',$data);  
        }
        if(!empty($result['result']['0']['gid'])){
            $game=GameService::getMultiInfoById(array($result['result']['0']['gid']),'ios');
            $data['game']=!empty($game['0'])?$game['0']['gname']:'';
            $data['gid']=$result['result']['0']['gid'];
        }
        return $this->display('/4web/bbs-game-ios',$data); 
    }

    //建立关联
    public function postBbsGameRelation(){
        if(!Input::get('fid') || !Input::get('gid') || !Input::get('type')){
            header("Content-type: text/html; charset=utf-8");
            return $this->back()->with('global_tips','操作失败->操作编号丢失'); 
        }
        $result=TopicService::query_game_link_list("true",Input::get('type'),Input::get('fid'),Input::get('gid'));
        $result=self::getdatainfo($result);
        if(empty($result['result'])){
           $result=TopicService::add_game_link(Input::get('fid'),Input::get('gid'),Input::get('type'));//获取type为平台
           $result=self::getdatainfo($result);
           return $this->redirect('weba_forum/topic/forum-list')->with('global_tips','关联成功');
        }

        header("Content-type: text/html; charset=utf-8");
        return $this->back()->with('global_tips','该游戏和社区已有关联啦'); 
    }
    //查询安卓游戏列表
    public function  getBbsRelationAndroid($fid="",$game='',$gid='')
    {
        $data['fid']=!empty($fid)?$fid:'';
        $data['type']=2;
        $data['linkId'] = Input::get('linkId');
        $data['game'] = Input::get('game');
        $data['fid'] = Input::get('fid');
        if(!empty($data['linkId']) && !empty($data['fid'])){
            return $this->display('/4web/bbs-game-android',$data);
        }
        $result=TopicService::query_game_link_list("true",2,$fid);
        $result=self::getdatainfo($result);
        if(empty($result['result'])){
            return $this->display('/4web/bbs-game-android',$data);
        }
        if(!empty($result['result']['0']['gid'])){
            $game=GameService::getMultiInfoById(array($result['result']['0']['gid']),'android');
            if(isset($game['0']['gname'])){
                $data['game']=!empty($game['0'])?$game['0']['gname']:'';
                $data['gid']=$result['result']['0']['gid'];
            }
        }
        return $this->display('/4web/bbs-game-android',$data);  
    }


    //删除关联游戏
    public function getDelForumGame($fid=0,$gid=0,$type='')
    {
        $linkId = Input::get('linkId',"");
        $result=TopicService::del_game_link($linkId);
        $result=self::getdatainfo($result);
        return $this->redirect('weba_forum/topic/forum-list')->with('global_tips','操作成功');
    }
    
    //检查1个游戏是否有关联了
    public function getCheckGameRelationBbs(){
        if(!Input::get('gid')){
             $data['state'] = 0;
             $data['msg'] = '游戏编号丢失';
             return response::json($data);
        }
       $result=TopicService::getForumAndGameRelation(true,Input::get('type'),'',Input::get('gid'));
       if($result['errorCode'] != 0){
            $data['state'] = 0;
            $data['msg'] = '请求失败';
            return response::json($data);
        }
        if(!empty($result['result']['0'])){
                $data['state'] = 0;
                $data['msg'] = '该游戏已有关联';
                return response::json($data);
         }else{
                $data['state'] = 1;
                $data['msg'] = '';
                return response::json($data);
         }
            
    }

    public function getReplylimitlist()
    {
        $targetId=Input::get("targetId");
        $data['error']=1;
        if(empty($targetId)){
             return response::json(array('error'=>1));
        }
        $parms['targetId']=$targetId;
        $parms['targetType']='TOPIC';
        $parms['pageIndex']=$parms['pageSize']=1;
        $result=TopicService::replylimitList($parms);
        if($result['errorCode'] == 0){
              $data['error']=0;
              if(empty($result['result']['0'])){
                    return response::json($data);
              }
              $datainfo=$result['result']['0'];
              $data['result']['id']=$datainfo['id'];
              $data['result']['targetId']=$datainfo['targetId'];
              $data['result']['targetType']=$datainfo['targetType'];
              $data['result']['limitRate']=!empty($datainfo['limitRate'])?intval($datainfo['limitRate']):'';
              $data['result']['limitStatus']=!empty($datainfo['limitStatus'])?$datainfo['limitStatus']:'';
              $data['result']['limitDeadline']=!empty($datainfo['limitDeadline'])?$datainfo['limitDeadline']:'';
              $data['result']['limitNum']=!empty($datainfo['limitNum'])?intval($datainfo['limitNum']):'';//print_r($data);exit;
              return response::json($data);
        }

    }

    public function postHuifuedit()
    {
        $input=Input::only('id','targetId','targetType','limitRate','limitStatus','limitDeadline','limitNum');
        if(empty($input['targetId'])){
            return response::json(array('error'=>1,'text'=>'帖子ID丢失'));
        }
        $params=array();
        $params['createTime']= date('Y-m-d H:i:s');
        $params['targetType']='TOPIC';
        $params['targetId']=$input['targetId'];
        if(!empty($input['limitRate'])){
            $params['limitRate']=intval($input['limitRate']);
        }
        if(!empty($input['limitNum'])){
            $params['limitNum']=intval($input['limitNum']);
        }

        if(!empty($input['limitDeadline'])){
            $params['limitDeadline']=$input['limitDeadline'];
        }

        $params['limitStatus']='false';
        if(!empty($input['limitStatus']) == 1){
            $params['limitStatus']='true';
        }
        if(empty($input['id'])){
            unset($input['id']);
            $result=TopicService::add_replylimit($params); 
        }else{
            $params['id']=$input['id'];
            $result=TopicService::edit_replylimit($params); 
        }
        if($result['errorCode'] == 0){
               return response::json(array('error'=>0,'text'=>'操作成功'));
        }
        return response::json(array('error'=>1,'text'=>'操作失败'));
    }


    private function getdatainfo($result)
    {   
        
        
        if($result['errorCode'] != null){
              return !empty($result)?$result:array();
        }
        return array('result'=>array());
        //header("Content-type: text/html; charset=utf-8");
        //return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);

    }

    /**处理接口返回数据**/
    private static function processingInterface($result,$params)
    {
        $data['totalCount'] =!empty($result['totalCount'])?$result['totalCount']:0;
        $pager = Paginator::make(array(),$data['totalCount'],$params['pageSize']);
        unset($params['pageIndex']);
        $pager->appends($params);
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = !empty($result['result'])?$result['result']:array();//print_r($data['datalist']);
        return $data;
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
    function postSetHot(){
        $type = input::get('type');
        $data['targetId'] = input::get('id');
        if($type == "1"){
            $data['targetType'] = "forum";
            $data['place'] = "1";
            $res = TopicService::add_recommend($data);
        }else{
            $res = TopicService::del_recommend(array('recommendId'=>input::get('id')));
        }
        if(!$res['errorCode']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
        }
    }
    //查询论坛列表
    public function getHotForumList()
    {
        $params=$datafid=array();
        $params['name']='';
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =15;
        if(Input::get('name')) $params['name'] =Input::get('name');
        $search = array('fromTag'=>"2",'place'=>"1");
        $result=TopicService::forum_recommend_list($search);
//        $result=self::getdatainfo($result);
        foreach($result['result'] as $key=>$value){
            $datafid[]=!empty($value['gid'])?$value['gid']:'';
        }
//        $resultinf=TopicService::getForumAndGameRelation(1,$this->genre,$datafid);
//        $resultinf=self::getdatainfo($resultinf);
//        $datafid=array();
//        foreach ($resultinf['result'] as $key => $value){
//            $datafid[]=$value['gid'];
//            foreach($result['result'] as $key_=>$value_){
//                if($value_['fid'] == $value['fid']){
//                    $result['result'][$key_]['gid']=!empty($value['gid'])?$value['gid']:0;
//                }
//            }
//        }
        $game=GameService::getMultiInfoById(array_flip(array_flip($datafid)),($this->genre==1)?'ios':'android');
        if(!empty($game) && $game != 'game_not_exists'){
            foreach($game as $key => $value){
                $game_[$value['gid']]=$value['gname'];
            }
            foreach($result['result'] as $key=>&$value){
                if(array_key_exists("gid",$value)){
                    $value['gname']=isset($game_[$value['gid']])?$game_[$value['gid']]:"未找到";
                }
            }
        }

        $count=TopicService::getForumsCount($params['name']);
        $count=self::getdatainfo($count);
        $result['totalCount']=!empty($count['totalCount'])?$count['totalCount']:0;
        return $this->display('/4web/bbs-hot-list',self::processingInterface($result,$params));//
    }

}