<?php
namespace modules\chat\controllers;
use Youxiduo\Chat\ChatService;
use Youxiduo\Helper\Utility;

use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use libraries\Helpers;
use Yxd\Services\UserService;

class ChatroomController extends BackendController{
	public function _initialize(){
		$this->current_module = 'chat';
        $this->admin_hxid = Config::get('app.android_admin_huanxin_id');
        $this->api_url = Config::get('app.android_chat_api_url');
	}
	
	public function getIndex(){
		$this->getChatroomList();
	}
	
	/**
	 * 获取聊天室列表
	 */
	public function getChatroomList(){
		$itfc_url = Config::get('app.android_chat_api_url') . 'chatroom-list';
		$ext_para = '';
		$input = Input::all();
		
		$fileds = array(
			'keyword' => isset($input['name']) ? urlencode($input['name']) : false,
			'type' => isset($input['type']) ? urlencode($input['type']) : false,
			'pageIndex' => isset($input['page']) ? $input['page'] : false,
			'pageSize' => 10 //每页显示条数
		);
		
		foreach ($fileds as $key=>$val){
			if($val !== false) $ext_para .= $key .'='. $val .'&';
		}
		
		$ext_para  = $ext_para ? rtrim('?'.$ext_para,'&') : $ext_para;
		
		$url = $itfc_url.$ext_para;
		
		$result = Helpers::curlGet($url);
		$result = json_decode($result,true);
		$view_data = array();
		
		if($result['result']){
			$pager = Paginator::make(array(),$result['totalCount'],$fileds['pageSize']);
			
			if($fileds['keyword'] !== false){
				$pager->appends(array('name'=>$fileds['keyword']));
			}
			$view_data['chatrooms'] = $result['result'];
			$view_data['pagelink'] = $pager->links();
		}
		$view_data['search'] = array('name'=>urldecode($fileds['keyword']));
		
		return $this->display('chatroom-list',$view_data);
	}

    /**
     * 获取聊天室的聊天记录
     * @param bool|string $gid
     * @return
     */
	public function getChatRecord($gid=false){
		if(!$gid) return $this->getChatroomList();
		$itfc_url = $this->api_url . "chat-messages";
		$ext_para = '';
		$group_id = null;
		$game_circle = '';
		$now_page = Input::get('page',1);
		$fileds = array(
			'gid' => $gid,
			'startTime' => urlencode(Input::get('start_time',Null)),
			'endTime' => urlencode(Input::get('end_time',Null)),
			'uid' => Input::get('uid',Null),
			'pageIndex' => $now_page,
			'pageSize' => 6 //每页显示条数
		);
		
		foreach ($fileds as $key=>$val){
			if($val !== false) $ext_para .= $key .'='. $val .'&';
		}
		
		$ext_para  = $ext_para ? rtrim('?'.$ext_para,'&') : $ext_para;
		
		$url = $itfc_url.$ext_para;
		$result = Helpers::curlGet($url);
		$result = json_decode($result,true);
		$view_data = $uids = array();
		if($result['result']){
			$game_circle = $result['result'][0]['groupName'];
			$group_id = $result['result'][0]['groupId'];
			foreach ($result['result'] as &$row){
				if(!array_key_exists('uid', $row)) continue;
				$row['msgBody'] = json_decode($row['msgBody'],true);
				if($row['msgBody'][0]['type'] == 'txt') $row['msgBody'][0]['msg'] = $this->showtext($row['msgBody'][0]['msg']);
				$uids[] = $row['uid'];
			}
			$uids = array_unique($uids);
			$users = UserService::getBatchUserInfo($uids);
			foreach ($result['result'] as $k=>&$row){
				if(!array_key_exists('uid', $row)) continue;
				if(array_key_exists($row['uid'], $users)){
					$row['user_name'] = $users[$row['uid']]['nickname'];
					$row['user_avatar'] = Utility::getImageUrl($users[$row['uid']]['avatar']);
					$row['sendTime'] = date("Y-m-d H:i:s",substr($row['sendTime'],0,10));
				}
			}
		}
		$view_data['record'] = $result['result'];
		$base_data = $pre_data = $nex_data = array(
				'start_time' => Input::get('start_time',Null),
				'end_time' => Input::get('end_time',Null),
				'uid' => Input::get('uid',Null)
		);
		$pre_data['page'] = $now_page - 1 < 1 ? 1 : $now_page - 1;
		$nex_data['page'] = ++$now_page;
		
		$view_data['base'] = Request::url().'?'.http_build_query($base_data);
		$view_data['pre'] = Request::url().'?'.http_build_query($pre_data);
		$view_data['nex'] = Request::url().'?'.http_build_query($nex_data);
		
		$view_data['gid'] = $gid;
        $admin_uid = Utility::loadByHttp($this->api_url.'users',array('username'=>$this->admin_hxid));
        $view_data['admin_uid'] = $admin_uid['result'] ? $admin_uid['result'][$this->admin_hxid] : 0;
		$view_data['groupid'] = $group_id;
		$view_data['game_circle'] = $game_circle;
		$view_data['search'] = array('start_time'=>urldecode($fileds['startTime']),'end_time'=>urldecode($fileds['endTime']),'uid'=>$fileds['uid']);
		return $this->display('chatrecord',$view_data);
	}
	
	private function showtext($text){
		$search = array('|(http://[^ ]+)|', '|(https://[^ ]+)|', '|(www.[^ ]+)|');
		$replace = array('<a href="$1" target="_blank">$1</a>', '<a href="$1" target="_blank">$1</a>', '<a href="http://$1" target="_blank">$1</a>');
		$text = preg_replace($search, $replace, $text);
		return $text;
	}

    /**
     * 发送组信息
     * @param $to_id
     * @param string $type
     * @return
     * @internal param int $group_id
     */
	public function postSendGroupMsg($to_id,$type='chatgroups'){
		if(!$to_id) return $this->back()->with('global_tips','发送失败，请刷新页面重试');
		$message = Input::get('message');
		if(!trim($message)) return $this->back()->with('global_tips','请输入消息内容');
		$itfc_url = $this->api_url . "send-message";
		$ext_para = '';
		$fileds = array(
			'targetType' => $type,
			'receiver' => $to_id,
			'msg' => urlencode($message),
            'fromUid' => $this->admin_hxid
		);
		foreach ($fileds as $key=>$val){
			if($val !== false) $ext_para .= $key .'='. $val .'&';
		}
		
		$ext_para  = $ext_para ? rtrim('?'.$ext_para,'&') : $ext_para;
		
		$url = $itfc_url.$ext_para;
		$result = Helpers::curlGet($url);
		$result = json_decode($result,true);
		if($result['errorCode'] == 0){
			return $this->redirect($_SERVER['HTTP_REFERER'])->with('global_tips','发送成功');
		}else{
			return $this->redirect($_SERVER['HTTP_REFERER'])->with('global_tips','发送失败');
		}
	}

    /**
     *  我的消息
     */
    public function getMyMessage(){
		$uid = Input::get('uid');
		if($uid){
			$uinfo = UserService::getUserInfo($uid);
			if($uinfo){
				$hxid_result = ChatService::registerHx($uinfo['uid']);
				$hxid = $hxid_result['result'];
				$vdata['list'] = array(array('uid'=>$uinfo['uid'],'nickname'=>$uinfo['nickname'],'avatar'=>$uinfo['avatar'],'last_time'=>'','hxid'=>$hxid));
				$vdata['paginator'] = false;
			}else{
				$vdata['list'] = false;
				$vdata['paginator'] = false;
			}
		}else{
            //获取用户列表
            $page = Input::get('page') && is_numeric(Input::get('page')) ? Input::get('page') : 1;
            $limit = 10;
            $api_url =  $this->api_url.'get-emUserlist';
            $vdata = array();
            $params = array('uuid'=>$this->admin_hxid,'pageIndex'=>$page,'pageSize'=>$limit);
            $ulist = Utility::loadByHttp($api_url,$params);
            $uids = array();
            if($ulist['result']){
                $utotal = $ulist['totalCount'];
                foreach ($ulist['result'] as $hxid => $yxdid) {
                    $uids[] = $yxdid;
                }
                $uinfos = UserService::getBatchUserInfo($uids);
                if($uinfos){
                    foreach ($uinfos as &$user) {
                        $user['avatar'] = Utility::getImageUrl($user['avatar']);
                    }

                }
                foreach ($ulist['result'] as $hxid => &$yxdid) {
                    if(array_key_exists($yxdid,$uinfos)){
                        $yxdid = $uinfos[$yxdid];
                        $yxdid['hxid'] = $hxid;
                        $node_params = array(
                            'fromUid' => $this->admin_hxid,
                            'toUid' => $hxid,
                            'pageIndex' => 1,
                            'pageSize' => 1
                        );
                        $last_msg = Utility::loadByHttp($this->api_url.'user-chatMessages',$node_params);
                        $last_msg = $last_msg['result'] ? current($last_msg['result']) : false;
                        $yxdid['last_time'] = $last_msg ? date('Y-m-d H:i:s',substr($last_msg['timestamp'],0,10)) : false;
                    }
                }
                $paginator = Paginator::make(array(),$utotal,$limit)->links();
            }

            $vdata['list'] = isset($ulist['result']) ? $ulist['result'] : false;
            $vdata['paginator'] = isset($paginator) ? $paginator : false;
		}

		$vdata['uid'] = $uid;
        return $this->display('mymsg',$vdata);
    }

    public function getPersonalRecord($with_hxid='',$with_yxdid=''){
        if(!$with_hxid || !$with_yxdid) return $this->redirect('chat/chatroom/chatroom-list');
        $admin_hxid = $this->admin_hxid;
        if(!$admin_hxid) return $this->redirect($_SERVER['HTTP_REFERER']);
        $page = Input::get('page') && is_numeric(Input::get('page')) ? Input::get('page') : 1;
        $limit = 6;
        $api_url = $this->api_url.'user-chatMessages';
        $params = array(
            'fromUid' => $admin_hxid,
            'toUid' => $with_hxid,
            'pageIndex' => $page,
            'pageSize' => $limit
        );
        $result = Utility::loadByHttp($api_url,$params);
        $view_data = $uhxids = $uids = array();
		if($result['result']){
            foreach ($result['result'] as $row) {
                $uhxids[] = $row['from'];
            }
            $uhxid_str = implode(',',array_unique($uhxids));
            $yxd_info = Utility::loadByHttp($this->api_url.'users',array('username'=>$uhxid_str));
            if($yxd_info){
                foreach ($yxd_info['result'] as $k => $v) {
                    $uids[] = $v;
                }
                $uinfos = UserService::getBatchUserInfo($uids);
            }

			foreach ($result['result'] as &$row){
                $row['uid'] = $yxd_info['result'][$row['from']];
				$row['msgBody'] = json_decode($row['bodies'],true);
				if($row['msgBody'][0]['type'] == 'txt') $row['msgBody'][0]['msg'] = $this->showtext($row['msgBody'][0]['msg']);
                $row['sendTime'] = date("Y-m-d H:i:s",substr($row['timestamp'],0,10));
                $row['user_name'] = $uinfos[$yxd_info['result'][$row['from']]]['nickname'];
                $row['user_avatar'] = Utility::getImageUrl($uinfos[$yxd_info['result'][$row['from']]]['avatar']);
			}
		}
		$view_data['record'] = $result['result'];
		$pre_data['page'] = $page - 1 < 1 ? 1 : $page - 1;
		$nex_data['page'] = ++$page;

        $view_data['to_hxid'] = $with_hxid;
        $view_data['to_yxdid'] = $with_yxdid;
        $view_data['admin_hxid'] = $this->admin_hxid;

		$view_data['pre'] = Request::url().'?'.http_build_query($pre_data);
		$view_data['nex'] = Request::url().'?'.http_build_query($nex_data);
		return $this->display('persrecord',$view_data);
    }
}