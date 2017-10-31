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
use Youxiduo\V4\User\UserService;
use modules\web_forum\controllers\TopicController;
use modules\game\models\GameModel;

class ForumController extends BackendController{
    const GENRE = 1;
    const GENRE_STR = 'ios';

    public function _initialize(){
        $this->current_module = 'web_forum';
    }

    public function getForumList(){
        $vdata = array('list'=>array(),'totalcount'=>0);
        $name = Input::get('name','');
        $game_id = Input::get('game_id','');
        $page = Input::get('page',1);
        $games = GameModel::search(array());
//        print_r($games);
        $size = 15;
        if(Input::get('name')) $params['name'] =Input::get('name');
        $forum_res = TopicService::getForums($name,$page,$size,$game_id,1);
        if(!$forum_res['errorCode'] && $forum_res['result']){
            foreach($forum_res['result'] as &$item){
                if(isset($item['gid'])){
                    $game = GameModel::getInfo($item['gid']);
                    if(isset($game['gname'])){
                        $item['gname'] = $game['gname'];
                    }
                }
            }
            $vdata['list'] = $forum_res['result'];
        }
        $topic_num_res = TopicService::getForumsCount($name,$game_id);
        if(!$topic_num_res['errorCode']) $vdata['totalcount'] = $topic_num_res['totalCount'];
        
        $search = array('name'=>$name,'game_id'=>$game_id);
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
        if($add_res['errorCode'] || !$add_res['result']) return $this->back()->withInput()->with('global_tips',$add_res['errorDescription']);
        $rel_res = TopicService::add_game_link($add_res['result'],$input['gid'],self::GENRE);
        if(!$rel_res['errorCode'] && $rel_res['result']){
            return $this->redirect('web_forum/forum/forum-list','添加成功');
        }else{
            return $this->back()->withInput()->with('global_tips',$rel_res['errorDescription']);
        }
    }

    public function getEdit($fid=''){
        if(!$fid) return $this->back('数据错误');
        $game_info = array();
        $info_res = TopicService::getForumDetail($fid,self::GENRE);
        if($info_res['errorCode'] || !$info_res['result']) return $this->back('论坛不存在');
//        $rel_res = Topicservice::getForumAndGameRelation(1,self::GENRE, $fid );
//        print_r($info_res);
        if(!$info_res['errorCode'] && isset($info_res['result']['gid'])){
            $game_info = GameService::getOneInfoById($info_res['result']['gid'],self::GENRE_STR);
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
        $rel_res = TopicService::add_game_link($input['fid'],$input['gid'],self::GENRE);
        if($add_res['errorCode']==0){
            return $this->redirect('web_forum/forum/forum-list','修改成功');
        }else{
            return $this->back()->withInput()->with('global_tips','修改失败');
        }
    }
    
    public function getOpen($fid,$status,$gid="")
    {
    	$status = $status==1 ? 'true' : 'false';
    	$result = TopicService::openForum($fid,$status,1);
    	if($result['errorCode']==0){
            if($gid){
                if($status){
                    GameModel::updateGameInfo($gid,array('is_open_forum'=> 1));
                }else{
                    GameModel::updateGameInfo($gid,array('is_open_forum'=> 0));
                }
            }
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
        $rule = array('forum_name'=>'required','logo'=>'image');
        $prompt = array('forum_name.required'=>'请填写版块名称','logo.image'=>'icon必须为图片');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $type = isset($input['type']) ? 'true' : 'false';
        $fid = $input['fid'];
        $path = false;
        if(Input::hasFile('logo')){
            $dir = '/userdirs/forum/logo/';
            $path = Helpers::uploadPic($dir,$input['logo']);
        }
        $add_res = TopicService::addBoard($input['forum_name'],$type,$fid,$path);
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
        if($board['fid']!=0){
            $forum_res = TopicService::getForumDetail($board['fid']);
            $forum_info = $forum_res && !$forum_res['errorCode'] ? $forum_res['result'] : array();
        }else{
            $forum_info= array();
        }

        return $this->display('4web/forum-board-edit',array('forum_info'=>$forum_info,'board'=>$board,'old_fid'=>$fid));
    }
    
    public function postEditBoard(){
        $input = Input::all();
        $rule = array('forum_name'=>'required','logo'=>'image');
        $prompt = array('forum_name.required'=>'请填写版块名称','logo.image'=>'icon必须为图片');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $type = isset($input['type']) ? 'true' : 'false';
        $fid = !$input['fid']?"0":$input['fid'];
        if(Input::hasFile('logo')){
            $dir = '/userdirs/forum/logo/';
            $path = Helpers::uploadPic($dir,$input['logo']);
        }else{
            $path = $input['logo_1'];
        }
        $add_res = TopicService::updateBoard($input['bid'],$input['forum_name'],$type,$fid,$path);
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

    public function getMasterApplicationList(){
        $vdata = array('list'=>array(),'totalcount'=>0);
        $page = Input::get('page',1);
        $size = 10;
        $res = TopicService::getMasterpplicationAList($page,$size);
        $totle_count = $res['totalCount'];
        $result = array();
        if(!$res['errorCode'] && $res['result']) $result = $res['result'];
        $arr_forum = array();
        $arr_board = array();
        $arr_user = array();
        foreach($result as $k=>$v){
            if($v['targetType']=='forum'){
                $arr_forum[] = $v['targetId'];
            }elseif($v['targetType']=='board'){
                $arr_board[] = $v['targetId'];
            }
            $arr_user[] = $v['proposer'];
            //查询申请人是否是版主
            $master = TopicService::getWebMaster('',$v['targetId'],$v['targetType'],$v['proposer']);
            if(!$master['errorCode'] && $master['result']){
                if(!$master['result'][0]['isDeputy']){
                    $result[$k]['isMaster'] = "版主";
                }else{
                    $result[$k]['isMaster'] = "副版主";
                }
                $result[$k]['mid'] = $master['result'][0]['id'];
            }else{
                $result[$k]['isMaster'] = "";
                $result[$k]['mid'] = "";
            }
            //查询版主数和副版主 可以缓存**
            $president = 0;
            $vice_president = 0;
            $master = TopicService::getWebMaster('',$v['targetId'],$v['targetType']);
            if(!$master['errorCode'] && $master['result']){
                foreach($master['result'] as $k1=>$v1){
                    if(!$v1['isDeputy']){
                        $president++;
                    }else{
                        $vice_president++;
                    }
                }
            }
            $result[$k]['president'] = $president;
            $result[$k]['vice_president'] = $vice_president;
        }
        $uinfos = UserService::getMultiUserInfoByUids($arr_user);
        if (is_array($uinfos)) {
            foreach ($uinfos as $row) {
                $uinfos[$row['uid']] = $row;
            }
        }
        $vdata['users'] = $uinfos;
        //去重复
        $arr_forum = array_unique($arr_forum);
        $arr_board = array_unique($arr_board);
//        $arr_user = array_unique($arr_user);
        //合成字符串
        $str_forum = implode(',',$arr_forum);
        $str_board = implode(',',$arr_board);
//        $str_user = implode(',',$arr_user);
        //转换键，值
        $arr_forum = array_flip($arr_forum);
        $arr_board = array_flip($arr_board);
//        $arr_user = array_flip($arr_user);

        $board_res = TopicService:: getBoardList('','',$str_board);
        $forum_res = TopicService:: getForums('',1,10,false,false,$str_forum);
        //可以封装一下
        if(!$board_res['errorCode'] && $board_res['result']){

            foreach($board_res['result'] as $k=>$v){
                $fid = $v['bid'];
                $name = $v['name'];
                $arr_board[$fid] = $name;
            }
        }
        if(!$forum_res['errorCode'] && $forum_res['result']){
            foreach($forum_res['result'] as $k=>$v){
                $fid = $v['fid'];
                $name = $v['name'];
                //$arr_forum[$fid] = $name;
                $arr_forum_name[$fid] = $name;
                $arr_forum_gid[$fid] = $v['gid'];            }
        }
      /*   var_dump($result);
        die(); */
        foreach($result as $k=>$v){
            if($v['targetType']=='forum'){
                 //$result[$k]['f_or_b_name'] = $arr_forum[$v['targetId']]."(论坛)";
//                $result[$k]['f_name'] = $arr_forum_name[$v['targetId']];
//                $result[$k]['gid'] = $arr_forum_gid[$v['targetId']];
            }elseif($v['targetType']=='board'){
                 //$result[$k]['f_or_b_name'] = $arr_board[$v['targetId']]."(版块)";
                $result[$k]['f_name'] = $arr_board[$v['targetId']];
                $result[$k]['gid'] = '';
            }
        }
       
        if($totle_count){
            $vdata['totalcount'] = $totle_count;
        }


        $pager = Paginator::make(array(),$vdata['totalcount'],$size);
        $vdata['pagelinks'] = $pager->links();
        $vdata['list'] = $result;

        return $this->display('/4web/master-application-list',$vdata);
    }

    public function getSetMaster($id,$targetId,$targetType,$uid,$isDeputy){

        $res = TopicService::SetMaster($id,$targetId,$targetType,$uid,$isDeputy);
        $data = array(
            'uid' =>$uid,
            'delete' => 0,
            'medalType' => 'board_moderator',
            'typeExtend' => $targetId
        );
        if(!$res['errorCode'] && isset($res['result'])){
            $res2 = TopicService::grant_user_medal($data);
            $input = Input::get();
            $input['type'] = '2015';
            $input['linkType'] = '1';
            //$input['link'] = $targetId;
            //var_dump($input);die();
            if($targetType=='forum'){
                $input['link_Id']='1';
                $input['link'] =$input['f_name'].','.$input['gid'].','.$targetId;
                $input['content'] = $input['f_name'].',';
            } else {    
                $input['link_Id']='2';
                $input['link'] = $input['f_name'].','.$targetId;
                $input['content'] = $input['f_name'].',';
            }
           //var_dump($input);die();
            TopicController::system_send($input);
            return $this->redirect('web_forum/forum/master-application-list','操作成功');
        }else{
            return $this->back()->withInput()->with('global_tips','操作失败');
        }
    }

    public function getDelMaster($id,$uid){
        $data = array(
            'uid' =>$uid,
            'delete' => 1,
            'medalType' => 'board_moderator'
        );
        $res = TopicService::DelMaster($id,$uid);
        if(!$res['errorCode'] && isset($res['result'])){
            $res2 = TopicService::grant_user_medal($data);
            if($res['result'] == "1"){
                return $this->redirect('web_forum/forum/master-application-list','该用户在其他论坛任然是版主！');
            }
            return $this->redirect('web_forum/forum/master-application-list','操作成功');
        }else{
            return $this->back()->withInput()->with('global_tips','操作失败');
        }
    }

    public function getRefuseApplication($id){
        $res = TopicService::RefuseApplication($id);
        if(!$res['errorCode'] && $res['result']){
            return $this->redirect('web_forum/forum/master-application-list','操作成功');
        }else{
            return $this->back()->withInput()->with('global_tips','操作失败');
        }
    }

    public function getRecruitRuleList(){
        $data = array();
        $page = Input::get('page',1);
        $size = 10;
        $search = array('pageIndex'=>$page,'pageSize'=>$size);
        $totle = TopicService::recruit_rule_list(array('pageSize'=>"999"));
        $res = TopicService::recruit_rule_list($search);
        if(!$res['errorCode'] && $res['result']){     
            foreach($res['result'] as $k=>$v){
                if($v['targetId']  && $v['targetId']!="all"){
                    $bbs = TopicService::getForumDetail($v['targetId']);
                    if(!$bbs['errorCode'] && isset($bbs['result'])){
                        $res['result'][$k]['bbsName'] = $bbs['result']['name'];
                    }
                }
            }
            $data['list'] = $res['result'];
        }
        $data['search'] = $search;
        $pager = Paginator::make(array(),count($totle['result']),$size);
        $pager->appends($search);
        $data['pagelinks'] = $pager->links();
        return $this->display('/4web/rule-list',$data);

    }

    public function postAddRecruitRule(){
        $input = input::get();
        $input['targetId'] = Input::get('targetId')?Input::get('targetId'):"all";
        if($input['id']){
//            unset($input['targetType']);
            $res = TopicService::update_recruit_rule($input);
        }else{
            $res = TopicService::add_recruit_rule(array_filter($input));
        }
//        print_r(array_filter($input));
//        print_r($input);
//        print_r($res);die;
        if(!$res['errorCode']&&$res['result']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>$res['errorDescription']));
        }
    }

    public function postDelRecruitRule(){
        $input = input::get();
        $res = TopicService::delete_recruit_rule($input);
//        print_r($input);
//        print_r($res);die;
        if(!$res['errorCode']&&$res['result']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>$res['errorDescription']));
        }
    }

    function postSetHot(){
        $type = input::get('type');
        $data['targetId'] = input::get('id');
        if($type == "1"){
            $data['targetType'] = "forum";
            $data['place'] = "0";
            $res = TopicService::add_recommend($data);
        }else{
            $res = TopicService::del_recommend(array('recommendId'=>input::get('id')));
        }
        if(!$res['errorCode']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription'],'data'=>$res['errorDescription']));
        }
    }

    public function getHotForumList(){
        $vdata = array('list'=>array(),'totalcount'=>0);
        $name = Input::get('name','');

        $size = 15;
        if(Input::get('name')) $params['name'] =Input::get('name');

        $search = array('fromTag'=>"1",'place'=>"0");
        $forum_res=TopicService::forum_recommend_list($search);

        if(!$forum_res['errorCode'] && $forum_res['result']) $vdata['list'] = $forum_res['result'];
        $topic_num_res = TopicService::getForumsCount($name);
        if(!$topic_num_res['errorCode']) $vdata['totalcount'] = $topic_num_res['totalCount'];

        $search = array('name'=>$name);
        $pager = Paginator::make(array(),$vdata['totalcount'],$size);
        $pager->appends($search);
        $vdata['search'] = $search;
        $vdata['pagelinks'] = $pager->links();

        return $this->display('/4web/hot-forum-list',$vdata);
    }

    //查询游戏列表
    public function  getBbsRelationGame($fid="",$game='',$gid='')
    {
        $data['type'] = 1;
        $data['linkId'] = Input::get('linkId');
        $data['game'] = Input::get('game');
        $data['fid'] = Input::get('fid');
        $data['gid'] = Input::get('gid');
        if(!empty($data['linkId']) && !empty($data['fid'])){
            return $this->display('/4web/bbs-game-android',$data);
        }

        $result=TopicService::query_game_link_list(true,2,$fid);
        if(empty($result['result'])){
            return $this->display('/4web/bbs-game-android',$data);
        }
        if(!empty($result['result']['0']['gid'])){
            $game=GameService::getMultiInfoById(array($result['result']['0']['gid']),'android');
            $data['game']=!empty($game['0'])?$game['0']['gname']:'';
            $data['gid']=$result['result']['0']['gid'];
        }
        return $this->display('/4web/bbs-game-android',$data);
    }

    //建立关联
    public function postBbsGameRelation(){
        if(!Input::get('fid') || !Input::get('gid') || !Input::get('type')){
            header("Content-type: text/html; charset=utf-8");
            return $this->back()->with('global_tips','操作失败->操作编号丢失');
        }
        $result=TopicService::query_game_link_list(true,Input::get('type'),Input::get('fid'),Input::get('gid'));
        if(empty($result['result'])){
            $result=TopicService::add_game_link(Input::get('fid'),Input::get('gid'),Input::get('type'));//获取type为平台
            if(!$result['errorCode']){
                return $this->redirect('web_forum/forum/forum-list')->with('global_tips','关联成功');
            }else{
                return $this->back()->with('global_tips','操作失败');
            }

        }
        header("Content-type: text/html; charset=utf-8");
        return $this->back()->with('global_tips','该游戏和社区已有关联啦');
    }

    //删除关联游戏
    public function getDelForumGame($fid=0,$gid=0,$type='')
    {
        $linkId = Input::get('linkId',"");
        $gid = Input::get('gid');
        $result=TopicService::del_game_link($linkId);
        if(!$result['errorCode']){
            if($gid){
                GameModel::updateGameInfo($gid,array('is_open_forum'=> 0));
            }
            return $this->redirect('web_forum/forum/forum-list')->with('global_tips','操作成功');
        }else{
            return $this->back()->with('global_tips','操作失败');
        }

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


}
