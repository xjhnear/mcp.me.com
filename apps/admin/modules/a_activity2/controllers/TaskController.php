<?php
namespace modules\a_activity2\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;

use Youxiduo\Task\TaskV3Service;
use Youxiduo\V4\User\UserService;
use Youxiduo\V4share\V4shareService;
use Youxiduo\Helper\MyHelpLx;

class TaskController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'a_activity2';
	}
	
	/**
	 * 任务列表
	 */
	public function getTaskList()
	{
//		$action_type = Input::get('action_type');//1|2|3
		$pageIndex = Input::get('page',1);
		$title = Input::get('title','');
        $counterTimeBegin  = Input::get('counterTimeBegin','');
        $counterTimeEnd = Input::get('counterTimeEnd','');
        if(isset($_REQUEST['lineId'])){
            $lineId = $_REQUEST['lineId'];
        }else{
            $lineId = Input::get('lineId','');
        }

//        print_r($_REQUEST);
		$complete_type = Input::get('complete_type')?Input::get('complete_type'):Input::get('taskType');
		$pageSize = 10;
		$data = array();
        if($lineId){
            $data['isSubTask'] = "true";
        }
//		$data['action_type'] = $action_type;
        $data['lineName'] =isset($_REQUEST['lineName'])?$_REQUEST['lineName']:"";
        $data['lineType'] =isset($_REQUEST['lineType'])?$_REQUEST['lineType']:"";
		$data['title'] = $title;
        $data['lineId'] = $lineId;
		$data['complete_type'] = $complete_type;
		$search = array(
            'isLine'=>'false',
            'pageSize'=>$pageSize,
            'pageIndex'=>$pageIndex,
            'isLoadCount'=>'true',
            'lineId'=>$lineId,
            'taskType'=>$complete_type,
            'taskName'=>$title,
            'isLoadPrize'=>"true",
            'platformType'=>'A',
		    'appType'=>'yxdAndroid',
            'isLoadStatistics'=>"true",
            'counterTimeBegin'=>$counterTimeBegin,
            'counterTimeEnd'=>$counterTimeEnd,
            'isLoadAuditFlag'=>'true',
            );
        $search['isSubTask'] = $lineId?"true":"false";
        if($search['isSubTask'] == "true"){
            $search['sortType'] = "1";
        }
//        print_r($search);
        $res = TaskV3Service::task_list($search);

        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            foreach($result as $k=>$row){
                if(isset($result[$k]['typeCondition']))
                $result[$k]['typeCondition'] = (array)json_decode($row['typeCondition']);
            }
        }else{
            $total = 0;
            $result= array();
        }
//        print_r($res);
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result;
		$data['conditions'] = array(''=>'选择类型','1'=>'试玩','2'=>'分享','3'=>'截图','4'=>'v4分享');

		return $this->display('task-list',$data);
	}

    /**
     * 连续任务列表
     */
    public function getTaskChainList()
    {
        $action_type = Input::get('action_type');//1|2|3
        $pageIndex = Input::get('page',1);
        $title = Input::get('title','');
        $line_type = Input::get('lineType');
        $pageSize = 10;
        $data = array();
        $data['action_type'] = $action_type;
        $data['title'] = $title;
        $data['lineType'] = $line_type;
        $search = array('isLine'=>'true','pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','isSubTask'=>"false",'lineType'=>$data['lineType'],'taskName'=>$data['title'],'isLoadStatistics'=>"true",'isLoadAuditFlag'=>'true','appType'=>'yxdAndroid','platformType'=>'A');

        $res = TaskV3Service::task_list($search);
//        print_r($res);
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
        }else{
            $total = 0;
            $result= array();
        }
//        print_r($res);
        $pager = Paginator::make(array(),$total,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['line_type'] = array(''=>'选择类型','1'=>'默认','2'=>'持续任务线','3'=>'非连续任务线');//,'2'=>'累计性任务线 ','3'=>'主线任务',
        $game_ids = array();
        return $this->display('task-chain-list',$data);
    }

    /**
     * 子任务列表
     */
    public function getTaskChildrenList()
    {
        $pageIndex = Input::get('page',1);
        $title = Input::get('title','');
        $startTime = Input::get('createTimeBegin','');
        $endTime = Input::get('createTimeEnd','');
        $pageSize = 10;
        $data = array();
        $complete_type = Input::get('complete_type')?Input::get('complete_type'):Input::get('taskType');
        $data['complete_type'] = $complete_type;
        $data['title'] = $title;
//        print_r(Input::get());
        $search = array(
            'isLine'=>'false',
            'pageSize'=>$pageSize,
            'pageIndex'=>$pageIndex,
            'isLoadCount'=>'true',
            'taskType'=>$complete_type,
            'taskName'=>$title,
            'isLoadPrize'=>"true",
            'platformType'=>'A',
            'appType'=>'yxdAndroid',
            'isSubTask'=>"true",
            'createTimeBegin'=>$startTime,
            'createTimeEnd'=>$endTime,
            'isLoadStatistics'=>"true",
            'isRelateLine'=>"false");
//        print_r($search);
        $res = TaskV3Service::task_list($search);
//        print_r($res);
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            foreach($result as $k=>$row){
                if(isset($result[$k]['typeCondition']))
                    $result[$k]['typeCondition'] = json_decode($row['typeCondition'],true);
                if(isset($result[$k]['stepList'])){
                    foreach($result[$k]['stepList'] as $k1=>$v1){
                        $result[$k]['stepList'][$k1]['stepCondition'] = json_decode($result[$k]['stepList'][$k1]['stepCondition'],true);
                    }
                }
            }
        }else{
            $total = 0;
            $result= array();
        }
//        print_r($result);
        $pager = Paginator::make(array(),$total,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['conditions'] = array(''=>'选择类型','1'=>'试玩','2'=>'分享','3'=>'截图','4'=>'v4分享');
        $data['stepType'] = array('4'=>'提交苹果id邮箱','0'=>'图文','1'=>'截图','2'=>'下载','3'=>'设定试玩游戏时间');
        $data['sort'] = array('0'=>'降序','1'=>'升序');
        return $this->display('task-children-list',$data);
    }


    /**
     * 任务列表
     */
    public function getIframeChildrenTask()
    {
        $pageIndex = Input::get('page',1);
        $title = Input::get('taskName','');
        $gid = Input::get('gid','');
        $sortType = Input::get('sortType','');
        $pageSize = 5;
        $data = array();
        $search = array('isLine'=>'false','pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','isRelateLine'=>"false",'taskType'=>"",'taskName'=>$title,'isLoadPrize'=>"true",'platformType'=>'A','appType'=>'yxdAndroid','gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"true",'isRelateLine'=>"false");
//        print_r($search);
        $res = TaskV3Service::task_list($search);
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            foreach($result as $k=>$row){
                if(isset($result[$k]['typeCondition']))
                    $result[$k]['typeCondition'] = (array)json_decode($row['typeCondition']);
            }
        }else{
            $total = 0;
            $result= array();
        }
        $pager = Paginator::make(array(),$total,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['sort'] = array('0'=>'降序','1'=>'升序');
//
//        return $this->display('iframe-children-task',$data);
        $html = $this->html('iframe-children-task',$data);
        return $this->json(array('html'=>$html));
    }

	public function getTaskAdd()
	{
		$id = Input::get('id');
        $lineId = Input::get('lineId','');
        $lineName = Input::get('lineName','');
        $lineType= Input::get('lineType','');
		$action_type = Input::get('action_type');
        $isSubTask = Input::get('isSubTask','false');
		$data = array();
        if($lineId){
            $data['lineId'] = $lineId;
            $data['lineName'] = $lineName;
            $data['lineType'] = $lineType;
        }
		$data['action_types'] = array('1'=>'试玩任务','2'=>'分享任务','3'=>'代充任务');
		$data['conditions'] = array('1'=>'试玩','2'=>'分享','3'=>'截图','4'=>'v4分享');
        $data['appVersion'] = array('0'=>'不限','3.0.2'=>'3.0.2','3.1.0'=>'3.1.0','3.1.1'=>'3.1.1');
        $data['prize'] = array('0'=>'游币','4'=>'人民币','1'=>'礼包','2'=>'实物',);
		$data['formset'] = Config::get('yxd.charge_form');
        $data['isSubTask'] = $isSubTask;
		if($id){
			$info = TaskV3Service::task_get(array('taskId'=>$id));
            if($info&&$info['result']['typeCondition']){
                $info['result']['typeCondition'] = (array)json_decode($info['result']['typeCondition']);
            }
//            print_r($info);
			$data['atask'] = $info['result'];
		}else{
			$data['atask'] = array('action_type'=>$action_type,'is_show'=>1,'reward_type'=>'money');
		}
		return $this->display('task-add',$data);
	}
	
    public function postTaskAdd()
	{

        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
		$input = array();
        //是否是子任务
        $lineId = input::get("lineId");
        $lineType = input::get("lineType");
        $input['isLine'] = "false";
        if($lineId){
            $input['lineId'] = $lineId;
            $input['isSubTask'] = "true";

            $input['lineType'] = $lineType;
        }else{
            $input['isSubTask'] = "false";
        }

        //安卓端
        $input['platformType'] = 'A';
//		$input['gameDownloadUrl'] = Input::get('linkValue_');
		$input['taskName'] = Input::get('title');
		$input['gid'] = Input::get('game_id');
		$input['taskType'] = Input::get('complete_type');//1试玩 2.分享 3. 截图
        $input['gname'] = Input::get('game_name');
		$input['startTime'] = Input::get('start_time') ;
		$input['endTime'] = Input::get('end_time');
        $input['appVersion'] = Input::get('appVersion');
		$input['sortValue'] = (int)Input::get('sort',0);
		$input['taskDesc'] = Input::get('content');
		$input['isSubTask'] = Input::get('isSubTask',"false");
        $input['taskIcon'] = Input::get('icon');
        $input['forenotice'] = Input::get('forenotice',"");
        $input['dailyAmount'] = Input::get('dailyAmount',"");
        $input['attendRate'] = Input::get('attendRate',"");
        $input['lastDays'] = Input::get('lastDays',"");
        $input['intervalDays'] = Input::get('intervalDays',"");
        $input['kl'] = Input::get('kl',"");
        $input['fc'] = Input::get('fc',"");
        $input['rmbReward'] = Input::get('rmbReward',"");
        $input['rmbRate'] = Input::get('rmbRate',"");
        $input['balanceReward'] = Input::get('balanceReward',"");
        $input['isAppTask'] = Input::get('isAppTask',"false");
        if(!$input['forenotice'])unset($input['forenotice']);
        $input['isDefaultFee'] = Input::get("isDefaultFee");
        $input['ipLimit'] = Input::get('ipLimit',"");
        if($input['ipLimit']){
            $input['ipLimit'] = "true";
        }else{
            $input['ipLimit'] = "false";
        }

        if($input['kl'] == "count"){
            unset($input['limitProp']);
        }elseif($input['kl'] == "prop"){
            unset($input['dailyAmount']);
        }
        unset($input['kl']);
        if($input['fc'] == "count"){
            unset($input['rmbRate']);
        }elseif($input['fc'] == "prop"){
            unset($input['rmbReward']);
        }
        unset($input['fc']);
        //奖励数组
        $input['prize_type'] = Input::get('prize_type',array());
        $input['youb_num'] = Input::get('youb_num',array());
        $input['card_code'] = Input::get('card_code',array());
        $input['card_des'] = Input::get('card_des',array());
        $input['num_get'] = Input::get('num_get',array());
        $input['num_auto'] = Input::get('num_auto',array());
        $input['prize_gid'] = Input::get('prize_gid',array());
        $prize_img_arr = Input::get('prize_img',array());
        $prizeId_arr = Input::get('prizeId',array());

        if(Input::file('prize_pic')){
            $prize_pic = Input::file('prize_pic');
        }else{
            $prize_pic = array();
        }
//        print_r(Input::file('prize_pic'));
        if(Input::hasFile('task_icon')){
            $file = Input::file('task_icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['taskIcon'] = $dir . $new_filename . '.' . $mime;
        }else{
            $input['taskIcon'] = Input::get('task_img');
        }

        //处理任务类型
        $arr_typeCondition = array();
        if($input['taskType']=="1"){
            $arr_typeCondition = array('trialTime'=>Input::get('total_time'),'gameDownloadUrl'=>Input::get('linkValue_'),'gamePackageName'=>Input::get('game_package_name'),'gameName'=>Input::get('game_name'));
        }else if($input['taskType']=="2"){
            $arr_typeCondition = array('shareId'=>Input::get('activity_id'),'shareIcon'=>Input::get('share_icon'),'shareTitle'=>Input::get('share_title'),'shareDesc'=>Input::get('share_desc'),'shareUrl'=>Input::get('share_url')?Input::get('share_url'):"http://t.cn/RUc6uMT",'gameName'=>Input::get('game_name'));
        }else if($input['taskType']=="3"){
            $arr_typeCondition = array('gameDownloadUrl'=>Input::get('linkValue_'),'gamePackageName'=>Input::get('game_package_name'),'gameName'=>Input::get('game_name'));
        }else if($input['taskType']=="4"){
//            if(Input::get('share_url',"")){
//                $share_url = Input::get('share_url',"");
//            }else{
//                $shareRes = V4shareService::excute2(array('shareConfigId'=>Input::get('activity_id'),'upperUid'=>'5345536'),"GetShare");
//                $share_url =$shareRes['data'];
//            }

            $arr_typeCondition = array('shareId'=>Input::get('activity_id'),'shareIcon'=>Input::get('share_icon'),'shareTitle'=>Input::get('share_title'),'shareDesc'=>Input::get('share_desc'),'gameName'=>Input::get('game_name'));
        }
        if(Input::get('t_gameName',"")){
            $arr_typeCondition['gameName'] = Input::get('t_gameName',"");
        }
        if($arr_typeCondition){
            $input['typeCondition'] = json_encode($arr_typeCondition);
        }

//print_r(Input::get());
        //处理奖励内容
        $rewards = Input::get('rewards',"");
        if($rewards){
            $rewards = json_decode($rewards,"true");
        }else{
            $rewards = array();
        }
        foreach($rewards as $k=>&$v){
            if(isset($v['iconUrl'])){
                $v['prizeIcon'] = $v['iconUrl'];unset($v['iconUrl']);
            }
            if(isset($v['rewardName'])){
                $v['prizeName'] = $v['rewardName'];unset($v['rewardName']);
            }
            if(isset($v['rewardType'])){
                if($v['rewardType'] == "rmbb"){
                    $v['prizeType'] = 4;
                }
                if($v['rewardType'] == "youb"){
                    $v['prizeType'] = 0;
                }
                if($v['rewardType'] == "gift"){
                    $v['prizeType'] = 1;
                }
                if($v['rewardType'] == "good"){
                    $v['prizeType'] = 2;
                }
//
//                switch($v['rewardType'])
//                {
//                    case "rmbb":
//                        $v['prizeType'] = 4;
//                    case "yb":
//                        $v['prizeType'] = 0;
//                    case "gift":
//                        $v['prizeType'] = 1;
//                    case "goods":
//                        $v['prizeType'] = 2;
//                    default:
//                        $v['prizeType'] = -1;
//                }????????????????????????????cao
            }
                if($v['rewardType'] == "rmbb" || $v['rewardType'] == "youb"){
                    unset($v['lockCount'],$v['offerCount'],$v['totalCount']);
                    if(isset($v['amount'])){
                        $v['prizeKey'] = $v['rewardType'] == "youb"?(int)$v['amount']:$v['amount'];
                        unset($v['amount']);
                    }
                }else{
                    unset($v['lockCount'],$v['offerCount'],$v['amount']);
                    if(isset($v['giftId'])){
                        $v['prizeKey'] = $v['giftId'];unset($v['giftId']);
                    }
                    if(isset($v['goodId'])){
                        $v['prizeKey'] = $v['goodId'];unset($v['goodId']);
                    }
                    if(isset($v['totalCount'])){
                        $v['stock'] = $v['totalCount'];unset($v['totalCount']);
                    }
                }
                $v['gid'] = "0";
                unset($v['rewardType']);unset($v['rewardOrder']);unset($v['status']);unset($v['rewardDetail']);
        }
        $prize_list = array();
        foreach($input['prize_type'] as $k=>$v){
            $arr = array();
            if($v == '0'){
                $arr['prizeName'] = $input['youb_num'][$k].'游币';
                $arr['prizeType'] = $v;
                $arr['prizeKey'] = (int)$input['youb_num'][$k];
            }elseif($v == '4'){
                $arr['prizeName'] = $input['youb_num'][$k].'人民币';
                $arr['prizeType'] = $v;
                $arr['prizeKey'] = $input['youb_num'][$k];
            }else{
                $arr['prizeName'] = $input['card_des'][$k];
                $arr['prizeType'] = $v;
                $arr['prizeKey'] = $input['card_code'][$k];
                $arr['stock'] = $input['num_get'][$k];
                $arr['autoIncrease'] = $input['num_auto'][$k];
                $arr['gid'] = $input['prize_gid'][$k];
            }

            if($prize_pic&&$prize_pic[$k]){
                $file = $prize_pic[$k];
                $new_filename = date('YmdHis') . str_random(4);
                $mime = $file->getClientOriginalExtension();
                $file->move($path,$new_filename . '.' . $mime );
                $arr['prizeIcon'] = $dir . $new_filename . '.' . $mime;
            }else{
                $arr['prizeIcon'] = $prize_img_arr[$k];
            }
            if($prizeId_arr[$k]){
                $arr['prizeId'] = $prizeId_arr[$k];
            }
            $prize_list[] = $arr;
        }

        unset($input['prize_type']);
        unset($input['youb_num']);
        unset($input['card_code']);
        unset($input['card_des']);
        unset($input['num_get']);
        unset($input['num_auto']);
        unset($input['prize_gid']);

        //编辑时
        $id = Input::get('id','');
        if($id){
            $info = TaskV3Service::task_get(array('taskId'=>$id));
            if($info){
            if(!isset($info['result']['prizeList']))
                $info['result']['prizeList'] = array();
                $input = array_diff_assoc($input, $info['result']);//把没发生改变的基础数据删除
                foreach($prize_list as $k=>$v){
                    if(isset($v['prizeId'])) {
                        foreach ($info['result']['prizeList'] as $k1 => $v1) {
                            if ($v['prizeId'] == $v1['prizeId']) {
                                $diff = array_diff_assoc($v, $v1);
                                if ($diff) {
                                    $prize_list[$k]['actionType'] = "update";//比较后两个有出入则标记此奖励为修改
                                }else{
                                    unset($prize_list[$k]);//没有变化的就不传过去了
                                }
                                unset($info['result']['prizeList'][$k1]);//已经匹配到的奖励先删掉，把删除的奖励留下来
                                break;
                            }
                        }
                    }else{
                        $prize_list[$k]['actionType'] = "insert";//标记为添加新奖励
                    }
                }
                //剩下的就是已经删除的
                $arr_deleted = array();
                foreach($info['result']['prizeList'] as $k=>$v){
                    $arr_deleted['prizeId'] = $v['prizeId'];
                    $arr_deleted['actionType'] = "delete";//标记为删除的奖励传过去
                    $prize_list[] = $arr_deleted;
                }
            }
        }

        if($prize_list){
            foreach($prize_list as $k => $v){
                $prize_list[$k]['taskId'] = $id;
            }
            $input['prizeListStr'] = json_encode(array_merge($prize_list));
        }
        if($rewards){
            $input['prizeListStr'] = json_encode($rewards);
        }
//        $input = array_filter($input);//去空值
        if($id){
            $input['taskId'] = $id;
//            print_r($input);die;
            $success = TaskV3Service::task_edit($input);
        }else{

            $success = TaskV3Service::task_add($input);
        }
//        print_r($input);
//        print_r($success);die;
		if($success&&!$success['errorCode']){
            if(isset($input['isSubTask'])&&$input['isSubTask']=="true"&&!input::get("lineId"))
                return $this->redirect('a_activity2/task/task-children-list','数据保存成功');
            return $this->redirect('a_activity2/task/task-list?lineId='.input::get("lineId").'&lineName='.input::get("lineName").'&lineType='.input::get("lineType"),'数据保存成功');
		}else{
			return $this->back($success['errorDescription']);
		}
	}

    public function getTaskChainAdd()
    {
        $id = Input::get('id');
        $data = array();
        if($id){
            $info = TaskV3Service::task_get(array('taskId'=>$id));
            if($info&&$info['result']){
                $data['atask'] = $info['result'];
                if(isset($info['result']['subTaskList'])){
                    $data['task_children'] = $info['result']['subTaskList'];
                }else{
                    $data['task_children'] = array();
                }

            }
        }
        $data['id'] = $id;
        $data['task_types'] = array('1'=>'默认','2'=>'持续任务线','3'=>'非连续任务线');//,'2'=>'累计性任务线 ','3'=>'主线任务'
        $data['appVersion'] = array('0'=>'不限','3.0.2'=>'3.0.2','3.1.0'=>'3.1.0','3.1.1'=>'3.1.1');
        $data['formset'] = Config::get('yxd.charge_form');

        return $this->display('task-chain-add',$data);
    }

    public function postTaskChainSave()
    {
        $input = array();
        //是否是子任务
        $id = input::get("id");
        $input['platformType'] = 'A';
        $input['isLine'] = "true";
        $input['gid'] = "";
        $input['taskName'] = Input::get('title');
        $input['lineType'] = Input::get('task_type');//1: 持续性任务线 2: 累计性任务线 3:主线任务
		$input['sortValue'] = (int)Input::get('sort',0);
        $input['taskDesc'] = Input::get('content');
        $input['startTime'] = Input::get('start_time') ;
        $input['endTime'] = Input::get('end_time');
        $input['subTaskIds'] = Input::get('subTaskIds',"");
        $input['appVersion'] = Input::get('appVersion',"false");
        if($input['subTaskIds'] ){
            $input['subTaskIds'] = substr($input['subTaskIds'],0,-1);
        }
        $input['forenotice'] = Input::get('forenotice');

        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        if(Input::hasFile('task_icon')){
            $file = Input::file('task_icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['taskIcon'] = $dir . $new_filename . '.' . $mime;
        }else{
            $input['taskIcon'] = Input::get('task_img');
        }

//        print_r($input);
        if(isset($id)&&!empty($id)){
            $input['taskId'] = $id;
            $success = TaskV3Service::task_edit($input);
        }else{
            $success = TaskV3Service::task_add($input);
        }

//        print_r($success);die;
        if(!$success['errorCode']){
            return $this->redirect('a_activity2/task/task-chain-list','数据保存成功');
        }else{
            return $this->back('数据保存失败');
        }
    }
	
	public function postTaskOpen()
	{
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        $data = array('taskId'=>$id,'closeType'=>$type);
        if($type == "2"){
            $res = TaskV3Service::offline_task(array('taskId'=>$id));
        }else{
            $res = TaskV3Service::task_close($data);
        }

        
		if(!$res['errorCode']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$type));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>$type));
        }
	}

    public function getTaskDel($id)
    {
        $data = array('taskId'=>$id);
        $res = TaskV3Service::task_del($data);
        if($res) return $this->back('数据删除成功');
        return $this->back('数据删除失败');
    }

    public function getAuditPictureList()
    {
        $taskId = Input::get('id');
        $taskName = Input::get('taskName');
        $taskType = Input::get('taskType');
        $taskStatus = Input::get('taskStatus');
        $prizeStatus = Input::get('prizeStatus');
        $pageIndex = Input::get('page',1);
        $uid = Input::get('uid','');
        $startTime = Input::get('createTimeBegin') ;
        $endTime = Input::get('createTimeEnd');
        $prizeId = Input::get('prizeId',0);
        $pageSize = 20;
        $data = array();
        $search = array('taskType'=>$taskType,'taskId'=>$taskId,'uid'=>$uid,'pageSize'=>$pageSize,'createTimeBegin'=>$startTime,'createTimeEnd'=>$endTime,'pageIndex'=>$pageIndex,'taskStatus'=>$taskStatus,'prizeStatus'=>$prizeStatus,'isLoadCount'=>"true",'isLoadPrize'=>"true");
        if($prizeId){
            $search['prizeId'] = $prizeId;
        }
        $res = TaskV3Service::query_screenshot_list($search);
//print_r($res);
        $arr_user = array();
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            foreach($result as $k=>$row){
                $arr_user[] = $row['uid'];
                $result[$k]['picUrlArr'] = explode(',', $row['picUrl']);
            }
        }else{
            $total = 0;
            $result= array();
        }
        if($arr_user){
            $uinfos = UserService::getMultiUserInfoByUids(array_unique($arr_user));
            if (is_array($uinfos)) {
                foreach ($uinfos as $row) {
                    $uinfos[$row['uid']] = $row;
                }
            }
            $data['users'] = $uinfos;
        }else{
            $data['users'] = array();
        }
//        print_r($data['users']);
//        print_r($result);
//        print_r(Input::get());
        $search['prizeIds'] = Input::get('prizeIds');
        $search['prizeNames'] = Input::get('prizeNames');
        $prizeList = array();
        if($search['prizeIds']&&$search['prizeNames']){
        $prizeIds = explode(',',substr($search['prizeIds'],0,-1));
        $prizeNames = explode(',',substr($search['prizeNames'],0,-1));
            $prizeList = array_combine($prizeIds,$prizeNames);
        }

        $search['prizeList'] = array_merge(array('0'=>'所选奖励'),$prizeList);
        $search['id'] = $taskId;
        $search['taskName'] = $taskName;
        $pager = Paginator::make(array(),$total,$pageSize);
        $pager->appends($search);
        $data['total'] = $total;
        $data['search'] = $search;
        $data['taskId'] = $taskId;
        $data['taskType'] = $search['taskType'];
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['taskName'] = $taskName;
        $data['taskStatus'] = array(''=>'审核状态','-3'=>'参与超时 ','-2'=>'重发','-1'=>'进行中/审核中','0'=>'参与中','1'=>'完成','2'=>'失败');
        $data['prizeStatus'] = array(''=>'奖励状态','0'=>'未奖励','1'=>'已奖励');
        return $this->display('audit-picture-list',$data);

    }

    public function postApproval()
    {
        $taskId = Input::get('taskId');
        $userTaskId = Input::get('userTaskId');
        $type = Input::get('type');
        $data = array('taskId'=>$taskId);
        if($type == "approval_failure"){
            $data['notPassIds'] = $userTaskId;
        }elseif($type == "approval_success"){
            $data['passIds'] = $userTaskId;
        }elseif($type == "approval_again"){
            $data['againIds'] = $userTaskId;
        }
        $res = TaskV3Service::approval_screenshot($data);
//        print_r(Input::get());
//        print_r($res);die;
        if(!$res['errorCode']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription'],'data'=>""));
        }
    }
    
    public function postCheck()
    {
        $notPassIds = $passIds = $againIds = array();
        $taskId = Input::get('taskId');
        $userTaskId = Input::get('userTaskId');
        if (count($userTaskId)<=0) echo json_encode(array('success'=>"false",'mess'=>'无数据','data'=>""));
        foreach ($userTaskId as $item) {
            switch (Input::get('check_type_'.$item)) {
                case 1:
                    $notPassIds[] = $item;
                    break;
                case 2:
                    $againIds[] = $item;
                    break;
                default:
                    $passIds[] = $item;
                    break;
            }
        }
        $data = array('taskId'=>$taskId);
        $data['notPassIds'] = implode(',', $notPassIds);
        $data['passIds'] = implode(',', $passIds);
        $data['againIds'] = implode(',', $againIds);
//         print_r($data);exit;
        $res = TaskV3Service::approval_screenshot($data);
        //        print_r(Input::get());
        //        print_r($res);die;
        if(!$res['errorCode']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription'],'data'=>""));
        }
    }
    
    public function postApprovalAll()
    {
        $taskId = Input::get('taskId');
        $data = array('taskId'=>$taskId);
        $res = TaskV3Service::approval_task_all_screenshot($data);
        if(!$res['errorCode']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
        }
    }

    public function postApprovalByHand()
    {
        $taskId = Input::get('taskId');
        $data = array('taskId'=>$taskId);
        $res = TaskV3Service::reset_award_prize($data);
        if(!$res['errorCode']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
        }
    }

    public function postReleaseTaskStock()
    {
        $taskId = Input::get('taskId');
        $data = array('taskId'=>$taskId);
        $res = TaskV3Service::release_task_stock($data);
        if(!$res['errorCode']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
        }
    }

    public  function getAjaxTongbu()
    {
        $input = Input::get();
        $data = array('taskId'=>$input['taskId'],'prizeListStr'=>"");
        if (isset($input['shareId'])) {
            $res = V4shareService::excute2(array('shareConfigId' => $input['shareId']), "GetShareConfigDetail");
            if ($res['success']) {
                if (isset($res['data']['rewards'])) {
                    $rewards = $res['data']['rewards'];
                    foreach ($rewards as $k => &$v) {
                        if (isset($v['iconUrl'])) {
                            $v['prizeIcon'] = $v['iconUrl'];
                            unset($v['iconUrl']);
                        }
                        if (isset($v['rewardName'])) {
                            $v['prizeName'] = $v['rewardName'];
                            unset($v['rewardName']);
                        }
                        if (isset($v['rewardType'])) {
                            if ($v['rewardType'] == "rmbb") {
                                $v['prizeType'] = 4;
                            }
                            if ($v['rewardType'] == "youb") {
                                $v['prizeType'] = 0;
                            }
                            if ($v['rewardType'] == "gift") {
                                $v['prizeType'] = 1;
                            }
                            if ($v['rewardType'] == "good") {
                                $v['prizeType'] = 2;
                            }
                            if ($v['rewardType'] == "rmbb" || $v['rewardType'] == "youb") {
                                unset($v['lockCount'], $v['offerCount'], $v['totalCount']);
                                if (isset($v['amount'])) {
                                    $v['prizeKey'] = $v['rewardType'] == "youb"?(int)$v['amount']:$v['amount'];
                                    unset($v['amount']);
                                }
                            } else {
                                unset($v['lockCount'], $v['offerCount'], $v['amount']);
                                if (isset($v['giftId'])) {
                                    $v['prizeKey'] = $v['giftId'];
                                    unset($v['giftId']);
                                }
                                if (isset($v['goodId'])) {
                                    $v['prizeKey'] = $v['goodId'];
                                    unset($v['goodId']);
                                }
                                if (isset($v['totalCount'])) {
                                    $v['stock'] = $v['totalCount'];
                                    unset($v['totalCount']);
                                }
                            }
                            $v['gid'] = "0";
                            unset($v['rewardType']);
                            unset($v['rewardOrder']);
                            unset($v['status']);
                            unset($v['rewardDetail']);
                        }
                    }
//                    print_r($rewards);
                    $data['prizeListStr'] = json_encode($rewards);

                    $res_task = TaskV3Service::sync_v4_prize_list($data);
//                    print_r($res_task);
                    if(!$res_task['errorCode']){
                        echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));die;
                    }else{
                        echo json_encode(array('success'=>"false",'mess'=>$res_task['errorDescription'],'data'=>""));die;
                    }
                }
            }

        }
        echo json_encode(array('success'=>"false",'mess'=>"数据查询失败",'data'=>""));
    }
    public function getTaskSearch()
    {
        $data = array();
        $pageIndex = Input::get('page',1);
        $pageSize = 5;
        $title = Input::get('keyword','');
        $data['keyword'] = $title;
        if(isset($_REQUEST['lineId'])){
            $lineId = $_REQUEST['lineId'];
        }else{
            $lineId = Input::get('lineId','');
        }
        $search = array(
//            'isLine'=>'false',
            'pageSize'=>$pageSize,
            'pageIndex'=>$pageIndex,
            'isLoadCount'=>'true',
//            'lineId'=>$lineId,
            'taskName'=>$title,
//            'isLoadPrize'=>"true",
            'platformType'=>'A',
            'appType'=>'yxdAndroid',
//            'isLoadStatistics'=>"true",
//            'isLoadAuditFlag'=>'true'
        );
        $search['isSubTask'] = $lineId?"true":"false";
        if($search['isSubTask'] == "true"){
            $search['sortType'] = "1";
        }
        $res = TaskV3Service::task_list($search);
//        print_r($search);
//        print_r($res);
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
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

}