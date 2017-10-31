<?php
namespace modules\feedback\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Android\Model\SystemFeedback;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\User\UserService;
use Yxd\Modules\Core\BackendController;

class AchatController extends BackendController{
	public function _initialize(){
		$this->current_module = 'feedback';
	}

    public function getList(){
		$page = Input::get('page',1);
		$pagesize = 10;
        $total =  SystemFeedback::getAllListCount();
		$result = SystemFeedback::getAllList($page,$pagesize);
        $uids = $users = array();
        if($result){
            foreach($result as $row){
                $uids[] = $row['uid'];
            }
            $tmp_users = UserService::getMultiUserInfoByUids($uids);
            if($tmp_users){
                foreach($tmp_users as &$user){
                    $user['avatar'] = Utility::getImageUrl($user['avatar']);
                    $users[$user['uid']] = $user;
                }
            }
        }
		$pager = Paginator::make(array(),$total,$pagesize);
		$data['pagination'] = $pager->links();
		$data['list'] = $result;
        $data['users'] = $users;
		return $this->display('achat-users',$data);
	}

    public function getDialog($id=''){
        if(!$id) return false;
        $page = Input::get('page',1);
        $pagesize = 10;
        $total = SystemFeedback::getDialogListCountByPid($id);
        $result = SystemFeedback::getDialogListByPid($id,$page,$pagesize);
        $uids = $users = array();
        $to_uid = '';
        if($result){
            foreach($result as $row){
                if(!$to_uid && $row['is_admin'] == 0){
                    $to_uid = $row['uid'];
                }
                $uids[] = $row['uid'];
            }
            $tmp_users = UserService::getMultiUserInfoByUids($uids,'full');
            if($tmp_users){
                foreach($tmp_users as &$user){
                    $user['avatar'] = Utility::getImageUrl($user['avatar']);
                    $users[$user['uid']] = $user;
                }
            }
        }
        $pager = Paginator::make(array(),$total,$pagesize);
        $data['pagination'] = $pager->links();
        $data['list'] = $result;
        $data['users'] = $users;
        $data['pid'] = $id;
        $data['to_uid'] = $to_uid;
        return $this->display('achat-dialog',$data);
    }

    public function postSend(){
		$pid = Input::get('pid');
		$message = Input::get('message');
        $data = array(
            'pid' => $pid,
            'feedback' => $message,
            'addtime' => time(),
            'ostype' => 1,
            'uid' => 1,
            'is_admin' => 1
        );
        if(SystemFeedback::save($data)){
            return $this->redirect('feedback/achat/dialog/' . $pid,'回复成功');
        }else{
            return $this->redirect('feedback/achat/dialog/' . $pid,'回复失败');
        }
    }
}