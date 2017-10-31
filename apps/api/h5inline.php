<?php
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use libraries\Helpers;
use Youxiduo\Helper\Utility;
use Yxd\Utility\ImageHelper;
use Youxiduo\Message\NoticeService;
use Youxiduo\User\AccountService;
use Youxiduo\Bbs\TopicService;
use Yxd\Services\UserService;
use Youxiduo\Chat\ChatService;

Route::get('h5inline/msgall',function(){
	$uid = Input::get('uid',0);
	$type = Input::get('type',0);
	$sys_msg_num = NoticeService::getUnreadMsgNum($uid,$type);
	$sys_msg_list = NoticeService::getSystemMsgList($uid);
	$back_data = $sysmsg_data = array();
	if($sys_msg_list && !isset($sys_msg_list['errorCode'])) {
		$last_msg = current($sys_msg_list);
		$sysmsg_data['title'] = '系统消息';
		$sysmsg_data['time_ago'] = Helpers::smarty_modifier_time_ago(strtotime($last_msg['addTime']));
		$sysmsg_data['content'] = $last_msg['content'];
		$sysmsg_data['num'] = $sys_msg_num['messageNum'];
	}
	
	$back_data['sysmsg'] = $sysmsg_data ? $sysmsg_data : false;
	$back_data['permsg'] = ChatService::getUnreadPersonalMsg($uid);
	
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::get('h5inline/sysmsglist',function(){
	$uid = Input::get('uid',0);
	$page = Input::get('page',1);
	$limit = 6;
	
	$sys_msg_list = NoticeService::getSystemMsgList($uid,$page,$limit);
	if($sys_msg_list){
		foreach ($sys_msg_list as &$row){
			$row['time_ago'] = Helpers::smarty_modifier_time_ago(strtotime($row['addTime']));
		}
	}
	$back_data = array(
		'sys_msg_list' => $sys_msg_list,
		'current_page' => $page,
		'no_data' => $sys_msg_list ? false : true
	);
	
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::get('h5inline/sysmsgdetail',function(){
	$msgid = Input::get('msgid',0);
	$uid = Input::get('uid');
	
	$sys_msg_det = NoticeService::getSystemMsgDetail($uid,$msgid);
	$back_data = array(
		'title' => $sys_msg_det['title'],
		'time_ago' => Helpers::smarty_modifier_time_ago(strtotime($sys_msg_det['addTime'])),
		'content' => $sys_msg_det['content']
	);
	
	return Response::json($back_data)->setCallback(Input::get('callback'));
});
//250527 250552
Route::get('h5inline/permsglist',function(){
	$uid = Input::get('uid');
	$toid = Input::get('toid');
	$page = Input::get('page',1);
	$back_data = array();
	$uinfo = UserService::getUserInfo($uid);
	$toinfo = UserService::getUserInfo($toid);
	if($uinfo && $toinfo){
		$hxuid = ChatService::registerHx($uid);
		$hxtoid = ChatService::registerHx($toid);
		$per_msg = ChatService::getChatRecords($hxuid['result'], $hxtoid['result'], $page, 10, time().'000');
		$back_data['chat_with'] = $toinfo['nickname'];
		$back_data['to_id'] = $toid;
		$back_data['to_name'] = $hxtoid['result'];
		$back_data['current_page'] = $page;
		$back_data['no_data'] = $per_msg['result'] ? false : true;
		if($per_msg['result']){
			$per_msg = array_reverse($per_msg['result']);
			foreach ($per_msg as $row){
				$bodies = current(json_decode($row['bodies'],true));
				if($bodies['type'] == 'txt'){
					$msg = $bodies['msg'];
				}elseif ($bodies['type'] == 'img'){
					$msg = '<img src="'.$bodies['url'].'"/>';
				}else{
					$msg = '暂时无法播放语音，请下载游戏多手机版';
				}
				$back_data['list'][] = array(
					'avatar' => $row['from'] == $hxuid['result'] ? Config::get('app.img_url').$uinfo['avatar'] : $toinfo['avatar'],
					'message' => $msg,
					'time' => date("Y-m-d H:i:s",substr($row['timestamp'],0,10)),
					'is_me' => $row['from'] == $hxuid['result'] ? true : false
				);
			}
		}
	}
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::get('h5inline/postshome',function(){
	header("Access-Control-Allow-Origin:*");
	$fid = Input::get('fid');
	$bid = Input::get('bid');
	$page = Input::get('page',1);
	//搜索
	$search_subject = Input::get('subject','');
	$limit = 6;
	$topic_list = TopicService::getPostsList($fid,$bid,'',$page,$limit,$search_subject);
	$back_data = $topicdata = $uids = array();
	$item_name = array('全部主题','游戏问答','八卦吐槽','寻找伙伴');
	if($topic_list && !isset($topic_list['errorCode'])) {
		foreach ($topic_list as $row){
			$uids[] = $row['uid'];
		}
		$users_info = UserService::getBatchUserInfo(array_unique($uids));
		foreach ($topic_list as $row){
			if(!array_key_exists($row['uid'], $users_info)) continue;  //如果该uid不存在，则此帖子直接不显示
			
			$topic['tid'] = $row['tid'];
			$topic['uname'] = $users_info[$row['uid']]['nickname'];
			$topic['avatar'] = Config::get('app.img_url').$users_info[$row['uid']]['avatar'];
			$topic['list_pic'] = isset($row['listpic']) ? Config::get('app.img_url').$row['listpic'] : false;
			$topic['title'] = $row['subject'];
			$topic['time_ago'] = Helpers::smarty_modifier_time_ago(strtotime($row['createTime']));
			$topic['replies'] = $row['replies'];
			$topic['praise'] = $row['watchs'];
			$topicdata[] = $topic;
		}
	}
	$back_data = array(
			'topic_list' => $topicdata,
			'fid' => $fid,
			'banner' => array(1,2,3,4),
			'current_page' => $page,
			'no_data' => $topicdata ? false : true,
			'select_name' => $fid ? ($bid ? $item_name[$bid] : $item_name[0]) : $item_name[0]
	);
 	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::get('h5inline/post-detail',function(){
	$tid = Input::get('tid');
	
	$post_data = TopicService::getPostDetail($tid);
    $reply_limit = 6;
	$reply = TopicService::getReplyList($tid,1,$reply_limit);
	$back_data = $first_page_rep = array();
	if($post_data){
		$uinfo = UserService::getUserInfo($post_data['uid']);
		if(!$uinfo) return Response::json(array('post_detail'=>false))->setCallback(Input::get('callback'));
		$back_data['post_detail'] = array(
			'tid' => $post_data['tid'],
			'version' => $post_data['version'],
			'title' => $post_data['subject'],
			'avatar' => Config::get('app.img_url').$uinfo['avatar'],
			'poster' => $uinfo['nickname'],
			'time_ago' => Helpers::smarty_modifier_time_ago(strtotime(($post_data['createTime']))),
			'replies' => $post_data['replies'],
			'praise' => $post_data['watchs'],
		);
		if(isset($post_data['formatContent']) && $post_data['formatContent']){
			$back_data['post_detail']['content'] = $post_data['formatContent'];
		}elseif(isset($post_data['content']) && $post_data['content']){
			$back_data['post_detail']['content'] = TopicService::formatTopicMessage($post_data['content']);
		}else{
			$back_data['post_detail']['content'] = '';
		}
		//第一页回复
		if($reply){
			$uids = array();
			foreach ($reply as $row){
				$uids[] = $row['replier'];
			}
			$replier_info = UserService::getBatchUserInfo(array_unique($uids));
			foreach ($reply as $row){
				if(!array_key_exists($row['replier'], $replier_info)) continue;  //如果该uid不存在，则此回复直接不显示
				
				$rep['tid'] = $row['tid'];
				$rep['uname'] = $replier_info[$row['replier']]['nickname'];
				$rep['avatar'] = Config::get('app.img_url').$replier_info[$row['replier']]['avatar'];
				$rep['time_ago'] = Helpers::smarty_modifier_time_ago(strtotime($row['createTime']));
				$rep['floor'] = ++$row['floor'];
				if(isset($row['formatContent']) && $row['formatContent']){
					$rep['content'] = $row['formatContent'];
				}elseif(isset($row['content']) && $row['content']){
					$rep['content'] = TopicService::formatTopicMessage(json_decode($row['content'],true));
				}else{
					$rep['content'] = '';
				}
				$first_page_rep[] = $rep;
			}
		}
		$back_data['first_page_rep'] = $first_page_rep;
	}
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::post('h5inline/post-add',function(){
	header("Access-Control-Allow-Origin:*");
	$input = Input::all();
	
	$rule = array('fid'=>'required','uid'=>'required','subject'=>'required','category'=>'required','content'=>'required');
	$message = array('fid.required'=>'数据错误','uid.required'=>'请先登录','subject.required' => '帖子标题不能为空','category.required'=>'帖子分类不能为空','content.required'=>'帖子内容不能为空');
	
	$valid = Validator::make($input,$rule,$message);
	if ($valid->fails()){
		return current($valid->messages()->all());
	}else{
		$list_pic = $input['listpic'] ? $input['listpic'] : false;
		$result = TopicService::doPostAdd($input['fid'], $input['uid'], $input['category'], $input['subject'],'','',3,
				0,false,true,false,false,false,false,false,false,$list_pic,$input['content']);
		if(!$result['isSuccess']) return '发帖失败！请重试！';
	}
	return '发帖成功！';
});

Route::post('h5inline/reply-add',function(){
	header("Access-Control-Allow-Origin:*");
	$input = Input::all();
	
	$rule = array('tid'=>'required','uid'=>'required','version'=>'required','content'=>'required');
	$message = array('tid.required'=>'数据错误','uid.required'=>'请先登录','version.required'=>'数据错误','content.required'=>'回复内容不能为空');
	
	$valid = Validator::make($input,$rule,$message);
	if($valid->fails()){
		return current($valid->messages()->all());
	}else{
		$result = TopicService::doReplyAdd($input['uid'], $input['tid'], $input['version'],'',$input['content'],'',true);
		if(!$result['isSuccess']) return '回帖失败！请重试！';
	}
	return '回帖成功！';
});

Route::get('h5inline/reply-list',function(){
	$tid = Input::get('tid');
	$page = Input::get('page');
	$limit = 6;
	
	$replies = TopicService::getReplyList($tid,$page,$limit);
	$back_data = $reply_data = $uids = array();
	if(!isset($replies['errorCode']) && $replies){
		foreach ($replies as $row){
			$uids[] = $row['replier'];
		}
		$users_info = UserService::getBatchUserInfo(array_unique($uids));
		foreach ($replies as $row){
			if(!array_key_exists($row['replier'], $users_info)) continue;  //如果该uid不存在，则此回复直接不显示
				
			$reply['tid'] = $row['tid'];
			$reply['uname'] = $users_info[$row['replier']]['nickname'];
			$reply['avatar'] = Config::get('app.img_url').$users_info[$row['replier']]['avatar'];
			$reply['time_ago'] = Helpers::smarty_modifier_time_ago(strtotime($row['createTime']));
			$reply['floor'] = ++$row['floor'];
			if(isset($row['formatContent']) && $row['formatContent']){
				$reply['content'] = $row['formatContent'];
			}elseif(isset($row['content']) && $row['content']){
				$reply['content'] = TopicService::formatTopicMessage(json_decode($row['content'],true));
			}else{
				$reply['content'] = '';
			}
			$reply_data[] = $reply;
		}
	}
	
	$back_data = array(
		'replies' => $reply_data,
		'current_page' => $page,
		'no_data' => $replies ? false : true
	);
	
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::post('h5inline/upload',function(){
	header("Access-Control-Allow-Origin:*");
	$postimg_url = null;
	if(Input::hasFile('postimg')){			
        $config = array(
    	    'savePath'=>'/club/postimg/',
    	    'driverConfig'=>array('autoSize'=>array(80)) //生成略缩图
    	);
    	$uploader = new ImageHelper($config);
    	$img = $uploader->upload('postimg');
    	if($img !== false){
    		$postimg_url = $img['filepath'] . '/' . $img['filename'];
    	}
	}
	return Response::json(array('img_url'=>Config::get('app.img_url').$postimg_url,'path_url'=>$postimg_url));
});

Route::get('h5inline/userinfo',function(){
	$uid = Input::get('uid');
	$userinfo = AccountService::h5GetUserInfo($uid);
	if($userinfo) $userinfo['avatar'] = Config::get('app.img_url').$userinfo['avatar'];
	return Response::json($userinfo)->setCallback(Input::get('callback',''));
});

Route::get('h5inline/friendlist',function(){
	$uid = Input::get('uid',0);
	$page = Input::get('page',1);
	$limit = 6;
	$back_data = $uids = $list = array();
	
	$userinfo = UserService::getUserInfo($uid);
	if($userinfo){
		$uuid = ChatService::registerHx($uid);
		$userlist = ChatService::getFriends($uuid['result']);
		if($userlist['result']){
			foreach ($userlist['result'] as $row){
				$uids[] = $row;
			}
			$users_info = UserService::getBatchUserInfo(array_unique($uids));
			foreach ($users_info as $id=>$user){
				if(in_array($id, $userlist['result'])){
					$list[] = array(
						'uid' => $user['uid'],
						'name' => $user['nickname'],
						'avatar' => $user['avatar'],
						'summary' => $user['summary'],
						'hxid' => array_keys($userlist['result'],$id)
					);
				}
			}
			if($list) $list = array_slice($list, ($page-1)*$limit,$limit);
			$back_data = array(
					'friendlist' => $list,
					'current_page' => $page,
					'no_data' => $list ? false : true
			);
		}
	}
	
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::get('h5inline/searchuser',function(){
	$page = Input::get('page',1);
	$nickname = Input::get('nickname');
	$pageSize=6;
	$back_data = array();
	$result = AccountService::h5SearchUserByNickName($nickname,$page,$pageSize);
	if($result['result']){
		foreach ($result['result'] as &$row){
			$hxrsp = ChatService::registerHx($row['uid']);
			$row['hxid'] = $hxrsp['result'];
		}
		$back_data = $result;
	}
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::get('h5inline/getnickname',function(){
	$hxid = Input::get('hxid');
	if(!$hxid) return false;
	$uinfo = null;
	$uidresp = ChatService::getYxdUid($hxid);
	if($uidresp['result']){
		$uinfo = UserService::getUserInfo(current($uidresp['result']));
	}
	return Response::json(array('nickname'=>$uinfo?$uinfo['nickname']:''))->setCallback(Input::get('callback'));
});

//添加好友（暂时无用）
Route::get('h5inline/addfriend',function(){
	$uid = Input::get('uid');
	$toid = Input::get('toid');
	$uinfo = UserService::getUserInfo($uid);
	$toinfo = UserService::getUserInfo($toid);
	$back_data = array('errorCode'=>1);
	if($uinfo && $toinfo){
		$hxuid = ChatService::registerHx($uid);
		$hxtoid = ChatService::registerHx($toid);
		$back_data = ChatService::getAddfriend($hxuid['result'], $hxtoid['result']);
	}
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::post('h5inline/edituserinfo',function(){
	header("Access-Control-Allow-Origin:*");
	$userinfo = Input::get('userinfo');
	$userinfo = json_decode($userinfo, true);
	$uid = $userinfo['uid'];
	if(!$uid) return Response::json(array('res'=>0));
	unset($userinfo['uid']);
	$res = UserService::updateUserInfo($uid, $userinfo);
	return Response::json(array('res'=>$res?1:0));
});

Route::post('h5inline/login',function(){
	header("Access-Control-Allow-Origin:*");
	$username = Input::get('account');
	$password = Input::get('passwd');
	$result = Youxiduo\User\AccountService::h5login($username, $password);
	if(!isset($result['errorCode'])) {
		$crypt_str = $result['result']['uid'].'|'.Utility::cryptPwd($password);
		$encrypt_login_ck = Crypt::encrypt($crypt_str);
		$hxrs = ChatService::registerHx($result['result']['uid']);
		$result['result']['hxid'] = $hxrs['result'];
		$result['result']['hxps'] = Config::get('app.huanxin.hxps');
		$result['result']['appkey'] = Config::get('app.huanxin.appkey');
		$result['result']['youxiduo_h5inline_hash'] = $encrypt_login_ck;
		$result['errorCode'] = 0;
	}
	return $result;
});

Route::post('h5inline/autologin',function(){
	header("Access-Control-Allow-Origin:*");
	$login_ck = Input::get('login_ck');
	$result = array('errorCode'=>1);
	if(!$login_ck) return $result;
	list($uid,$password) = explode('|',Crypt::decrypt($login_ck));
	if($uid && $password){
		$uinfo = AccountService::h5GetUserInfo($uid);
		if(!$uinfo || ($uinfo['password'] != $password)) return false;
		$hxrs = ChatService::registerHx($uinfo['uid']);
		$result['result']['uid'] = $uinfo['uid'];
		$result['result']['hxid'] = $hxrs['result'];
		$result['result']['hxps'] = Config::get('app.huanxin.hxps');
		$result['result']['appkey'] = Config::get('app.huanxin.appkey');
		$result['errorCode'] = 0;
	}
	return $result;
});

Route::post('h5inline/register',function(){
	header("Access-Control-Allow-Origin:*");
	$back_data = array('errorCode'=>1);
	$input = Input::all();
	
	$rule = array('email'=>'required','passwd'=>'required|confirmed','passwd_confirmation'=>'required');
	$message = array('email.required'=>'邮箱不能为空','passwd.required'=>'密码不能为空','passwd_confirmation.required' => '确认密码不能为空','passwd.confirmed'=>'密码与确认密码不一致');
	
	$valid = Validator::make($input,$rule,$message);
	if ($valid->fails()){
		return current($valid->messages()->all());
	}else{
		$reg_res = AccountService::h5CreateUserByEmail($input['email'],$input['passwd_confirmation']);
		if(isset($reg_res['errorCode'])) return array('errorCode'=>1,'message'=>$reg_res['message']);
		$uid = $reg_res['uid'];
		$uinfo = AccountService::h5GetUserInfo($uid);
		if(!$uinfo) return array('errorCode'=>1,'message'=>'获取用户失败');
		$hxrs = ChatService::registerHx($uinfo['uid']);
		$result['result']['uid'] = $uinfo['uid'];
		$result['result']['hxid'] = $hxrs['result'];
		$result['result']['hxps'] = Config::get('app.huanxin.hxps');
		$result['result']['appkey'] = Config::get('app.huanxin.appkey');
		$result['errorCode'] = 0;
		$back_data = $result;
	}
	return Response::json($back_data);
});

