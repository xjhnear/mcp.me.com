<?php
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use libraries\Helpers;
use Youxiduo\Message\NoticeService;
use Youxiduo\Bbs\TopicService;
use Yxd\Services\UserService;

Route::get('h5inline/msgall',function(){
	$uid = Input::get('uid',0);
	$type = Input::get('type',0);
	$sys_msg_num = NoticeService::getUnreadMsgNum($uid,$type);
	$sys_msg_list = NoticeService::getSystemMsgList($uid);
	$back_data = $sysmsg_data = array();
	if(!isset($sys_msg_list['errorCode'])) {
		$last_msg = current($sys_msg_list);
		$sysmsg_data['title'] = '系统消息';
		$sysmsg_data['time_ago'] = Helpers::smarty_modifier_time_ago(strtotime($last_msg['addTime']));
		$sysmsg_data['content'] = $last_msg['content'];
		$sysmsg_data['num'] = $sys_msg_num['messageNum'];
	}
	
	$back_data['sysmsg'] = $sysmsg_data ? $sysmsg_data : false;
	
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::get('h5inline/sysmsglist',function(){
	$uid = Input::get('uid',0);
	$page = Input::get('page',1);
	$limit = 10;
	
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
	
	$sys_msg_det = NoticeService::getSystemMsgDetail($msgid);
	$back_data = array(
		'title' => $sys_msg_det['title'],
		'time_ago' => Helpers::smarty_modifier_time_ago(strtotime($sys_msg_det['addTime'])),
		'content' => $sys_msg_det['content']
	);
	
	return Response::json($back_data)->setCallback(Input::get('callback'));
});

Route::get('h5inline/topichome',function(){
	$fid = Input::get('fid');
	$bid = Input::get('bid');
	$page = Input::get('page');
	$limit = 2;
	
	$topic_list = TopicService::getTopicList($fid,$bid,'',$page,$limit);
	$back_data = $topicdata = $uids = array();
	$item_name = array('全部主题','游戏问答','八卦吐槽','寻找伙伴');
	if(!isset($topic_list['errorCode'])) {
		foreach ($topic_list as $row){
			$uids[] = $row['uid'];
		}
		$users_info = UserService::getBatchUserInfo(array_unique($uids));
		foreach ($topic_list as $row){
			if(!array_key_exists($row['uid'], $users_info)) continue;  //如果该uid不存在，则此帖子直接不显示
			
			$topic['fid'] = $row['fid'];
			$topic['uname'] = $users_info[$row['uid']]['nickname'];
			$topic['avatar'] = Config::get('app.img_url').$users_info[$row['uid']]['avatar'];
			$topic['title'] = $row['subject'];
			$topic['time_ago'] = Helpers::smarty_modifier_time_ago(strtotime($row['createTime']));
			$topic['replies'] = $row['replies'];
			$topic['praise'] = $row['watchs'];
			$topicdata[] = $topic;
		}
	}
	
	$back_data = array(
			'topic_list' => $topicdata,
			'current_page' => $page,
			'no_data' => $topicdata ? false : true,
			'select_name' => $bid ? $item_name[$bid] : $item_name[0]
	);
	
	return Response::json($back_data)->setCallback(Input::get('callback'));
});