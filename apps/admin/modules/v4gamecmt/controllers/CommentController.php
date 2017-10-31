<?php
namespace modules\v4gamecmt\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

use Yxd\Services\UserService;
use Youxiduo\V4\User\UserService as v4UserService;
use Yxd\Services\Cms\GameService;
use Youxiduo\Bbs\TopicService;
use modules\v4gamecmt\models\Comment;
use modules\web_forum\controllers\TopicController;
use Youxiduo\Helper\MyHelpLx;
use modules\v4_adv\models\Core;
use Youxiduo\Helper\Utility;

class CommentController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4gamecmt';
    }

    public function getIndex()
    {
        $data = array();
        $page = Input::get('page', 1);
        $pagesize = 10;
        $search = Input::only('replier','tid','startTime','endTime','score','isBest');
        if (!$search['startTime']) {
            $search['startTime'] = date("Y-m-d H:i:s",strtotime("-14 day"));
        }
        if (!$search['endTime']) {
            $search['endTime'] = date("Y-m-d H:i:s",time());
        }
        $result = Comment::search($search,$page,$pagesize);
        $pager = Paginator::make(array(), $result['totalCount'], $pagesize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result['result'];
        $uids = array();
        $gids = array();
        foreach($result['result'] as $row){
            $uids[] = $row['replier'];
            $gids[] = $row['tid'];
        }
        $data['users'] = UserService::getBatchUserInfo($uids);
        $data['games'] = GameService::getGamesByIds($gids);
        return $this->display('comment-list', $data);
    }

    public function getRd($id,$tid,$replier)
    {
        $gameId = Input::get('gameId', '');
        $result = Comment::doRecommend($id,$tid,$replier);
        if(!$result['errorCode']){
            $data_del_cache = Core::delcache(array('type'=>3,'gid'=>$gameId));
            $input = Input::get();
            $input['type'] = '2018';
            $input['linkType'] = '1';
            $input['linkValue']= $input['gameId'];
            TopicController::system_send($input);
        }      
        return $this->back();
    }
    
    public function getDel($id)
    {
//         $uid = parent::getSessionUserUid();
        $uid = Input::get('uid', '');
        $gameId = Input::get('gameId', '');
        $result = Comment::doDel($id,$uid);
        $data_del_cache = Core::delcache(array('type'=>3,'gid'=>$gameId));
        return $this->back();
    }

    public function getUnrd($id,$tid,$replier)
    {
        $gameId = Input::get('gameId', '');
        $result = Comment::doUnRecommend($id,$tid,$replier);
        $data_del_cache = Core::delcache(array('type'=>3,'gid'=>$gameId));
        return $this->back();
    }

    public function getExcel (){
        $page = 1;
        $pagesize = 1000;
        $search = Input::only('replier','tid','startTime','endTime','score','isBest');
        $result = Comment::search($search,$page,$pagesize);
        $uids = array();
        $gids = array();
        foreach($result['result'] as $row){
            $uids[] = $row['replier'];
            $gids[] = $row['tid'];
        }
        $users = UserService::getBatchUserInfo($uids);
        $games = GameService::getGamesByIds($gids);

        require_once base_path() . '/libraries/PHPExcel.php';
        $excel = new \PHPExcel();
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->setTitle('玩家情报');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excel->getActiveSheet()->setCellValue('A1','用户信息');
        $excel->getActiveSheet()->setCellValue('B1','游戏信息');
        $excel->getActiveSheet()->setCellValue('C1','内容');
        $excel->getActiveSheet()->setCellValue('D1','星级');
        $excel->getActiveSheet()->setCellValue('E1','添加时间');
        $i = 2;
        foreach($result['result'] as $index => $row){
            if (!isset($users[$row['replier']]['nickname']) || !isset($games[$row['tid']]['shortgname'])) continue;
            $excel->getActiveSheet()->setCellValue('A'.$i, $users[$row['replier']]['nickname']);
            $excel->getActiveSheet()->setCellValue('B'.$i, $games[$row['tid']]['shortgname']);
            //内容
            $tempStr = '';
            foreach ($row['content'] as $v) {
                $tempStr .= $v['text'] . "\r\n";
            }

            $excel->getActiveSheet()->setCellValue('C'.$i, $tempStr);
            $excel->getActiveSheet()->setCellValue('D'.$i, $row['score']);
            $excel->getActiveSheet()->setCellValue('E'.$i, $row['createTime']);
            $i++;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. '玩家情报' .date('Y-m-d').'.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = new \PHPExcel_Writer_Excel2007($excel);
        $objWriter->save('php://output');
    }
    
    public function getAdd($id='')
    {
        $data = array('new'=>true);
        if($id){
            $result = Comment::getReplyDetail($id);
            if($result){
                $data['data'] = $result['result'];
                $data['imgs'] = explode(',',$data['data']['listpic']);
                $data['new'] = false;
            }else{
                return $this->back()->with('global_tips','查询出错');
            }
        }
        return $this->display('comment-add',$data);
    }
    
    public function postAdd()
    {
        $input = Input::all();
//        print_r($input);
        $input['type'] = "GAME_PLAYER_INFO";
        $input['isAdmin'] = "false";
        $input['fromTag'] = 1;
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
//        print_r($input);die;
        if($input['id']<>""){
            $input1['id'] = $input['id'];
            $input1['message'] = $input['message'];
            $input1['listpic'] = $input['listpic'];
            $input1['score'] = $input['score'];
            $input1['tid'] = $input['tid'];
            $res = Comment::update_reply($input1);
        }else{
            if($input['game_id']){
                $input['tid'] = $input['game_id'];
                unset($input['game_id']);
                unset($input['game_name']);
            }
            unset($input['id']);
            $res = Comment::add_reply($input);
        }

        if($res && !$res['errorCode']){
            $data_del_cache = Core::delcache(array('type'=>3,'gid'=>$input['tid']));
            return $this->redirect('v4gamecmt/comment/index','成功');
        }else{
            return $this->redirect('v4gamecmt/comment/index',$res['errorDescription']);
        }
    }
    
    public function getSubCommentList($rid=''){
        if(!$rid) return $this->redirect('/v4gamecmt/comment/index')->with('global_tips','数据错误');
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
                $uinfos = v4UserService::getMultiUserInfoByUids($uids);
                if(is_array($uinfos)){
                    foreach ($uinfos as $row) {
                        $uinfos[$row['uid']] = $row;
                    }
    
                }
                $vdata['comment_list'] = $this->filterCommentData($comments,$uinfos);
            }
        }
        $vdata['total_count'] = $total;
        $vdata['rid'] = $rid;
        $vdata['paginator'] = Paginator::make(array(),$total,$limit)->links();
        return $this->display('sub-comment-list',$vdata);
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
                'content' => isset($row['isAdmin']) && $row['isAdmin'] ? $row['formatContent'] : (isset($row['content']) ? $row['content'] : ''),
                'add_time' => $row['createTime'],
                'is_active' => $row['isActive'] ? true :false
            );
        }
        return $result_comments;
    }
    
    public function getDelComment($cid='',$uid=''){
        if(!$cid || !$uid) return $this->back()->with('global_tips','数据错误');
        $result = TopicService::delComment($cid,$uid);
        if($result['errorCode']){
            $input = Input::get();
            $input['type'] = '1';
            $input['linkType'] = '5';
            self::system_send($input);
            return $this->back()->with('global_tips','删除失败');
        }else{
            return $this->back()->with('global_tips','删除成功');
        }
    }

    public function getSubCommentAdd($rid=''){
        $data = array('rid'=>$rid);
        return $this->display('sub-comment-add',$data);
    }
    
    public function postSubCommentAdd(){
        $input = Input::all();
//                print_r($input);exit;
        $res = TopicService::doCommentReplyAdd($input['replyId'],$input['uid'],$input['content']);
    
        if($res && !$res['errorCode']){
            return $this->redirect('v4gamecmt/comment/sub-comment-list/'.$input['replyId'],'成功');
        }else{
            return $this->redirect('v4gamecmt/comment/sub-comment-list/'.$input['replyId'],$res['errorDescription']);
        }
    }
    
}