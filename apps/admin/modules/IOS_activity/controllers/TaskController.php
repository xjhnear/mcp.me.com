<?php
namespace modules\IOS_activity\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Helper\MyHelpLx;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Android\Model\ActivityTaskUser;
use Youxiduo\V4\Activity\ActivityService;
use Youxiduo\Helper\MyHelp;
use Youxiduo\Task\TaskV3Service;
use Youxiduo\V4\User\UserService;
use libraries\Helpers;
use Youxiduo\Cache\CacheService;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Session;
use Youxiduo\Imall\ProductService;
use Youxiduo\Bbs\TopicService;

class TaskController extends BackendController
{
    public static $stepArr = array('4'=>'提交苹果id邮箱','0'=>'图文','1'=>'截图','10'=>'上传随机等级截图','2'=>'下载','5'=>'玩家上传文字和图片','6'=>'游戏信息和截图','7'=>'游戏等级信息和截图','8'=>'充值额度与奖励设置','9'=>'任务物品信息');
    public static $approvalArr = array('account'=>'账号','password'=>'密码','areaServer'=>'区服','loginWay'=>'登录方式','rank'=>'等级','dsc'=>'文字','goodsId'=>'背包物品ID');
	public function _initialize()
	{
		$this->current_module = 'IOS_activity';
	}

	/**
 * 任务列表
 */
    public function getTaskList()
    {
        $pageIndex = Input::get('page',1);
        $title = Input::get('title','');
        $taskId = Input::get('taskId','');
        $lineId = Input::get('lineId','');
        $gid = Input::get('gameId','');
        $sortType = Input::get('sortType','');
        $isLine = Input::get('isLine','');
        $startTime = Input::get('createTimeBegin','');
        $endTime = Input::get('createTimeEnd','');
        $complete_type = Input::get('complete_type');
        $appType = Input::get('appType',1);
        $accessType = Input::get('accessType','');
        $proceedNum = Input::get('proceedNum','');
        $isRecommend = Input::get('isRecommend','');
        $pageSize = 10;
        $data = array();
//		$data['action_type'] = $action_type;
        $data['lineName'] =isset($_REQUEST['lineName'])?$_REQUEST['lineName']:"";
        $data['lineType'] =isset($_REQUEST['lineType'])?$_REQUEST['lineType']:"";
        $data['title'] = $title;
        $data['lineId'] = $lineId;
        $data['complete_type'] = $complete_type;
        $search = array('pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','lineId'=>$lineId,'taskType'=>$complete_type,'taskName'=>$title,'taskId'=>$taskId,'isLoadPrize'=>"true",'platformType'=>'I','isLoadStep'=>'true','gid'=>$gid,'sortType'=>$sortType,'isLine'=>$isLine,'counterTimeBegin'=>'2000-01-01 00:00:00','createTimeBegin'=>$startTime,'createTimeEnd'=>$endTime,'isLoadStatistics'=>"true",'appType'=>$appType,'proceedNum'=>$proceedNum,'isRecommend'=>$isRecommend);
        $search['isSubTask'] = $lineId?"true":"false";
//        print_r(array_filter($search));exit;
        $search = array_filter($search);
        $search['accessType'] = $accessType;
        $res = TaskV3Service::task_list($search);
//        print_r($res);
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            foreach($result as $k=>$row){
                if(isset($result[$k]['typeCondition']))
                $result[$k]['typeCondition'] = json_decode($row['typeCondition'],true);
                $result[$k]['step_click'] = false;
                if(isset($result[$k]['stepList'])){
                    foreach($result[$k]['stepList'] as $k1=>$v1){
                        if($v1['stepApprovalNum']>0){
                            $result[$k]['step_click'] = true;
                        }
                        if(isset($result[$k]['stepList'][$k1]['stepCondition']))
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
        $data['conditions'] = array(''=>'选择类型','1'=>'试玩','2'=>'分享','3'=>'截图');
        $data['stepType'] = self::$stepArr;//,'3'=>'设定试玩游戏时间'
        $data['sort'] = array('0'=>'降序','1'=>'升序');
        $data['au'] = array(''=>'全部任务','false'=>'普通任务','true'=>'连续任务');
        $data['app'] = array('1'=>'ios4.0app');
        $data['access'] = array(''=>'全部用途','0'=>'一般用途','1'=>'充值用途','2'=>'道具/物品用途');
        return $this->display('task-list',$data);
    }

    /**
     * 子任务列表
     */
    public function getTaskChildrenList()
    {
        $pageIndex = Input::get('page',1);
        $title = Input::get('title','');
        $gid = Input::get('gameId','');
        $sortType = Input::get('sortType','');
        $startTime = Input::get('activityStartTime','');
        $endTime = Input::get('activityEndTime','');
        $appType = Input::get('appType',1);
        $pageSize = 10;
        $data = array();
        $search = array('isLine'=>'false','pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','taskType'=>"0",'taskName'=>$title,'isLoadPrize'=>"true",'platformType'=>'I','gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"true",'counterTimeBegin'=>'2000-01-01 00:00:00','createTimeBegin'=>$startTime,'createTimeEnd'=>$endTime,'isLoadStatistics'=>"true",'appType'=>$appType);
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
        $data['conditions'] = array(''=>'选择类型','1'=>'试玩','2'=>'分享','3'=>'截图');
        $data['stepType'] = self::$stepArr;//,'3'=>'设定试玩游戏时间'
        $data['sort'] = array('0'=>'降序','1'=>'升序');
        $data['app'] = array('1'=>'ios4.0app');
        return $this->display('task-children-list',$data);
    }



    /**
     * 新手任务列表
     */
    public function getTaskNewList()
    {
        $data=array();
        $input = Input::all();
        $params=array(
             'selType'//0为全部 1进行中；2已经结束
            ,'name'//名称
            ,'pageIndex'
            ,'pageSize'
            ,'activityType'
        );
        $inputinfo=MyHelp::get_Input_value($input,$params);

        $inputinfo['activityType']='5';
        $result=ActivityService::get_activity_info_list_back_end($inputinfo,$params);
//        print_r($inputinfo);
//        print_r($result);die;
        if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$inputinfo,$inputinfo['pageSize']);
            $info=array();
            $info['activityId']=MyHelp::get_Ids($data['datalist'],'id');
            $data['datalist']=$result['result'];
//            print_r($data['datalist']);
            return $this->display('task-new-list',$data);
        }
        return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
    }
    
    
    /**
     * 任务列表
     */
    public function getIframeChildrenTask()
    {
        $pageIndex = Input::get('page',1);
        $title = Input::get('taskName','');
        $gid = Input::get('gameId','');
        $sortType = Input::get('sortType','');
        $shareType = Input::get('shareType','');
        $appType = Input::get('appType',0);
        $pageSize = 5;
        $data = array();
        $search = array('isLine'=>'false','pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','isRelateLine'=>"false",'taskType'=>"0",'taskName'=>$title,'isLoadPrize'=>"true",'platformType'=>'I','gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"true",'appType'=>$appType);
//        print_r($search);
        $res = TaskV3Service::task_list($search);
        $search['shareType'] = $shareType;
//        print_r($res);
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
        $data['shareType'] = $shareType;
        $data['sort'] = array('0'=>'降序','1'=>'升序');
//
//        return $this->display('iframe-children-task',$data);
        $html = $this->html('iframe-children-task',$data);
        return $this->json(array('html'=>$html));
    }


	
	public function getDoStatistic()
	{
		ActivityTaskUser::updateStatusNum();
		return $this->back('统计完成');
	}
	
	public function getTaskAdd()
	{

		$id = Input::get('id');
        $lineId = Input::get('lineId','');
        $lineName = Input::get('lineName','');
        $lineType= Input::get('lineType','');
		$data = array();
        if($lineId){
            $data['lineId'] = $lineId;
            $data['lineName'] = $lineName;
            $data['lineType'] = $lineType;
        }
        $data['stepType'] = self::$stepArr;//,'3'=>'设定试玩游戏时间'
        $data['uploadPicType'] = array('1'=>'等于','2'=>'小于','3'=>'大于');
        $data['backpackType'] = array('1'=>'实物物品','2'=>'虚拟物品','3'=>'支付宝物品');
        $data['appType'] = 1;
        $data['accessType'] = 0;
		if($id){
			$info = TaskV3Service::task_get(array('taskId'=>$id,'appname'=>'glwzry'));
            //任务类型
            if(isset($info['result']['isLine'])&&$info['result']['isLine']){
                $data['selType'] = "2";
            }else{
                if(isset($info['result']['isSubTask'])&&$info['result']['isSubTask']){
                    $data['selType'] = "1";
                }else{
                    $data['selType'] = "0";
                }
            }

            $data['isLimitin'] = 0;
            $data['autoQuotaOff'] = 0;
            if ((isset($info['result']['limitNumbers']) && $info['result']['limitNumbers'] >0) || (isset($info['result']['limitHours']) && $info['result']['limitHours'] >0)) {
                $data['isLimitin'] = 1;
                if (isset($info['result']['autoQuotaInfo'])) {
                    $data['autoQuotaOff'] = 1;
                    $autoQuotaInfo_arr = json_decode($info['result']['autoQuotaInfo'],true);
                    if (isset($autoQuotaInfo_arr['quotaStartTime'])) {
                        $info['result']['quotaStartTime'] = $autoQuotaInfo_arr['quotaStartTime'];
                    }
                    if (isset($autoQuotaInfo_arr['quotaEndTime'])) {
                        $info['result']['quotaEndTime'] = $autoQuotaInfo_arr['quotaEndTime'];
                    }
                    if (isset($autoQuotaInfo_arr['intervalTime'])) {
                        $info['result']['intervalTime'] = $autoQuotaInfo_arr['intervalTime'];
                    }
                    if (isset($autoQuotaInfo_arr['quotaNumber'])) {
                        $info['result']['quotaNumber'] = $autoQuotaInfo_arr['quotaNumber'];
                    }
                }
            } else {
                $data['isLimitin'] = 0;
                $info['result']['limitNumbers'] = '';
                $info['result']['limitHours'] = '';
            }
            $data['isLimit'] = 0;
            if (isset($info['result']['limitStartTime']) && $info['result']['limitStartTime'] !='1970-01-01 08:00:00' && isset($info['result']['limitEndTime']) && $info['result']['limitEndTime'] != '1970-01-01 08:00:00') {
                $data['isLimit'] = 1;
            } else {
                $data['isLimit'] = 0;
                $info['result']['limitStartTime'] = '';
                $info['result']['limitEndTime'] = '';
            }
//            print_r($info);
            if(isset($info['result']['typeCondition']))
                $info['result']['typeCondition'] = json_decode($info['result']['typeCondition'],true);
            if(isset($info['result']['stepList'])){
                foreach($info['result']['stepList'] as $k1=>$v1){
                    if(isset($info['result']['stepList'][$k1]['stepCondition']))
                    $info['result']['stepList'][$k1]['stepCondition'] = json_decode($info['result']['stepList'][$k1]['stepCondition'],true);
                }
            }
            if($data['selType'] == "2"){
                $search = array('taskType'=>"0",'platformType'=>'I','lineId'=>$id,'isSubTask'=>"true");
                $res_children = TaskV3Service::task_list($search);
                if(!$res_children['errorCode']&&$res_children['result']){
                    $data['task_children'] = $res_children['result'];
                }
            }
            if (isset($info['result']['limitHours'])) {
                $info['result']['limitHours'] = $info['result']['limitHours']/60/60/1000;
            }
            if (isset($info['result']['limitNumbers']) && $info['result']['limitNumbers']==0) {
                $info['result']['limitNumbers'] = '';
            }
            if (isset($info['result']['limitHours']) && $info['result']['limitHours']==0) {
                $info['result']['limitHours'] = '';
            }

//            print_r($res_children);
//            print_r($info['result']);
			$data['atask'] = $info['result'];
			$data['appType'] = isset($info['result']['appType'])?$info['result']['appType']:1;
			$data['auditType'] = isset($info['result']['auditType'])?$info['result']['auditType']:0;
			$data['accessType'] = isset($info['result']['accessType'])?$info['result']['accessType']:0;
			$data['shareType'] = isset($info['result']['shareType'])?$info['result']['shareType']:0;
		}else{
			$data['atask'] = array('is_show'=>1,'reward_type'=>'money');
		}
        if(!isset($data['atask']['prizeList'])){
            if($id){
                $data['atask']['prizeList'][0]['prizeType'] = -1;
            } else {
                $data['atask']['prizeList'][0]['prizeType'] = 3;
            }
        }
		return $this->display('task-add',$data);
	}
	
	public function getTaskNewAdd()
	{
	    $id = Input::get('id');
	    $data = array();
	    if($id){
	        $info=array();
            $info['activityId']=$id;
            $info['activityType']='5';
            $datainfo=ActivityService::get_activity_info_list_back_end($info,array('activityId','activityType'));
            if($datainfo['errorCode'] == 0){
                 $info['atask']=$datainfo['result']['0'];
                 return $this->display('task-new-add',$info);
            }
	    }
	    return $this->display('task-new-add',$data);
	}
	
	public function postTaskNewAdd()
	{
         $input = Input::all();
         $params=array(
             'pic'//图片
            ,'topPic'//图片
            ,'name'//名称
            ,'isOnOff'
            ,'activityType'
            ,'newHandType'
            ,'tour'
            ,'summary'
            ,'descHtml'
            ,'activityStartTime'
            ,'activityEndTime'
            ,'platform'
            ,'appName'
        );
         $inputinfo =array();
         $inputinfo=MyHelp::get_Input_value($input,$params,0);
         $inputinfo['activityType']=5;
         $inputinfo['platform']='ios';
         $inputinfo['appName']='yxdjqb';
         
         if(!empty($input['pic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['pic']);
            $inputinfo['pic'] = $path;
         }

         if(!empty($inputinfo['isOnOff'])){
             $inputinfo['isOnOff']=1;
         } else {
             $inputinfo['isOnOff']=0;
         }
         
         $inputinfo['activityStartTime']=date("Y-m-d 00:00:00");
         $inputinfo['activityEndTime']=date("Y-m-d 00:00:00",strtotime("+10 year"));
         if(!empty($input['id'])){
             $inputinfo['id'] = $input['id'];
             $params[] = 'id';
             $result = ActivityService::update_activity_info($inputinfo,$params);
         } else {
             $result = ActivityService::save_activity_info($inputinfo,$params);
//              print_r($result);exit;
         }
         if($result['errorCode'] == 0){
            return $this->redirect('IOS_activity/task/task-new-list')->with('global_tips','操作成功');
         }else{
            return $this->redirect('IOS_activity/task/task-new-list')->with('global_tips','操作失败:'.$result['errorDescription']);   
         }
    }
	
    public function postTaskAdd()
	{
	    $in = Input::all();
        $id = Input::get('taskId','');
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        $input = array();
        //是否是独立任务或连续任务
        $lineType = input::get("selTaskType");
        $lineId = input::get('lineId','');
        if($lineType=="2"){
            $input['isLine'] = "true";
            $input['lineType'] = "1";
        }else{
            $input['isLine'] = "false";
            $input['taskType'] = "0";
            if($lineType=="1"){
                $input['isSubTask'] = "true";
            }else{
                $input['isSubTask'] = "false";
            }
        }
        if(!$lineType){
            $lineType = input::get("lineType");//当编辑时，需要另外添加任务的类型，子任务--
        }
        //Ios端
        $input['platformType'] = 'I';
        $input['auditType'] = Input::get('auditType');
        $input['taskName'] = Input::get('taskName');
        $input['gid'] = Input::get('game_id');
        $input['startTime'] = Input::get('start_time') ;
        $input['endTime'] = Input::get('end_time');
        $input['sortValue'] = (int)Input::get('sort',0);
        $input['taskContent'] = Input::get('taskContent');
		$input['linkType'] = Input::get('selLinkType');
        $input['linkValue'] = Input::get('linkValue');
        $input['subTaskIds'] = substr(Input::get('ids'),0,-1);
        $input['gname'] = Input::get('game_name');
        $input['taskDesc'] = Input::get('taskDesc');
        $input['attendRate'] = Input::get('attendRate');
        $input['limitNumbers'] = Input::get('limitNumbers');
        $input['limitHours'] = Input::get('limitHours');
        $input['isRecommend'] = Input::get('isRecommend') ? true : false ;
        $input['appType'] = Input::get('appType',1);
        $input['accessType'] = Input::get('accessType',0);
        $input['isLimit'] = Input::get('isLimit');
        $input['shareType'] = (int)Input::get('shareType',0);
        $input['approvalReminderInfo'] = Input::get('approvalReminderInfo');
        if($input['isRecommend'] == true){
            if(Input::hasFile('task_pic')){
                $file_pic = Input::file('task_pic');
                $new_filename_pic = date('YmdHis') . str_random(4);
                $mime_pic = $file_pic->getClientOriginalExtension();
                $file_pic->move($path,$new_filename_pic . '.' . $mime_pic );
                $input['recommendBackgroundImg'] = $dir . $new_filename_pic . '.' . $mime_pic;
            }else{
                $input['recommendBackgroundImg'] = Input::get('task_pic');
            }
        }
        if (Input::get('isLimit')) {
            $input['limitStartTime'] = Input::get('limitStartTime');
            $input['limitEndTime'] = Input::get('limitEndTime');
        } else {
            $input['limitStartTime'] = null;
            $input['limitEndTime'] = null;
        }

        $input['isLoadDownload'] = 'false';
        if (Input::get('loaddownload',"")=="on") {
            $input['isLoadDownload'] = 'true';
        }
        if(Input::get('top',"")=="on"){
            $input['sortValue'] = "100";
        }
        if (Input::get('isLimitin')) {
            if(Input::get('limitNumbers')==""){
                $input['limitNumbers'] = 0;
            }
    	    if(Input::get('limitHours')==""){
                $input['limitHours'] = 0;
            }
            $autoQuotaInfo_arr = array();
            if (Input::get('quotaStartTime')) {
                $autoQuotaInfo_arr['quotaStartTime'] = Input::get('quotaStartTime');
            }
            if (Input::get('quotaEndTime')) {
                $autoQuotaInfo_arr['quotaEndTime'] = Input::get('quotaEndTime');
            }
            if (Input::get('intervalTime')) {
                $autoQuotaInfo_arr['intervalTime'] = Input::get('intervalTime');
            }
            if (Input::get('quotaNumber')) {
                $autoQuotaInfo_arr['quotaNumber'] = Input::get('quotaNumber');
            }
            if (Input::get('quotaNumberLimit')) {
                $autoQuotaInfo_arr['quotaNumberLimit'] = Input::get('quotaNumberLimit');
            }
            if (count($autoQuotaInfo_arr)>0) {
                $input['autoQuotaInfo'] = json_encode($autoQuotaInfo_arr);
            }
            if (!$in['autoQuotaOff']) {
                $input['autoQuotaOff'] = 1;
            }
        } else {
            $input['limitNumbers'] = 0;
            $input['limitHours'] = 0;
        }
        
        //步骤数组
        $prize_img_arr = Input::get('prize_img');
        $stepType_arr = Input::get('stepType');

        if(Input::hasFile('prize_pic')){
            $prize_pic = Input::file('prize_pic');
        }else{
            $prize_pic = array();
        }
        if(Input::hasFile('prizeIcon')){
            $file = Input::file('prizeIcon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $prizeIcon = $dir . $new_filename . '.' . $mime;
        }else{
            $prizeIcon = Input::get('prizeIcon');
        }
        
        if(Input::hasFile('task_icon')){
            $file = Input::file('task_icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['taskIcon'] = $dir . $new_filename . '.' . $mime;
        }else{
            $input['taskIcon'] = Input::get('task_img');
        }

//        print_r($steps);die;
        //游戏名称
        $arr_typeCondition = array('gameName'=>Input::get('game_name'));
        if($arr_typeCondition){
            $input['typeCondition'] = json_encode($arr_typeCondition);
        }

        //处理奖励内容
        $prize_list = array();
        $prize_list['prizeIcon'] = $prizeIcon;
        $prize_list['prizeKey']  = Input::get('prizeKey');
        if(Input::get('selPrizeType')=="0"){
            $prize_list['prizeType'] = "0";
            $prize_list['prizeName'] = Input::get('prizeKey')."游币";
        }elseif(Input::get('selPrizeType')=="3"){
            $prize_list['prizeType'] = "3";
            $prize_list['prizeName'] = Input::get('prizeKey')."钻石";
            if (Input::get('prizeKey') <= 0 && $lineType!="2") {
                return $this->back("钻石数量必须大于0");
            }
        }elseif(Input::get('selPrizeType')=="1"){
            $prize_list['prizeType'] = "1";
            $prize_list['prizeName'] = $in['card_des'][2];
            $prize_list['prizeKey'] = $in['card_code'][2];
            $prize_list['gid'] = $in['card_gid'][2];
            $prize_list['stock'] = $in['totalCount'][2];
            $prize_list['requestFrom'] = $in['requestFrom'][2];
            if (isset($in['card_code_old'][2]) && $in['card_change'][2]) {
                $params_n2['requestFrom'] = $in['requestFrom'][2];
                $result_n2=ProductService::release_distributioncard($params_n2);
                if($result_n2['errorCode']!=0){
                    return $this->back($result_n2['errorDescription']);
                }
            }
            if ($in['card_code'][2] && $in['card_change'][2]) {
                $params_n['cardCode'] = $in['card_code'][2];
                $params_n['cardNumber'] = $in['totalCount'][2];
                $params_n['requestFrom'] = Utility::getUUID();
//                 $result_n=ProductService::distributioncard($params_n);
//                 if($result_n['errorCode']!=0){
//                     return $this->back($result_n['errorDescription']);
//                 }
                $prize_list['requestFrom'] = $params_n['requestFrom'];
            }
        }else{
            $prize_list['prizeType'] = "-1";
            $prize_list['prizeName'] = "无奖励";
            $prize_list['prizeKey'] = 0;
        }
        if($id&&Input::get('prizeId')){
            $prize_list['actionType'] = 'update';
            $prize_list['prizeId'] = Input::get('prizeId');
        }else{
            $prize_list['actionType'] = 'insert';
        }

//         if($prize_list['prizeType']!="-1"){
            $prize[] = $prize_list;
            if($prize){
                $input['prizeListStr'] = json_encode($prize);
            }
//         }
        
        if(Input::get('share')){

            $input['taskDesc_2'] = Input::get('taskDesc_2');
            $input['subTaskIds_2'] = substr(Input::get('ids_2'),0,-1);
            
            //处理奖励内容
            $prize_list_2 = array();
            $prize_list_2['prizeIcon'] = $prizeIcon;
            $prize_list_2['prizeKey']  = Input::get('prizeKey_2');
            if(Input::get('selPrizeType_2')=="0"){
                $prize_list_2['prizeType'] = "0";
                $prize_list_2['prizeName'] = Input::get('prizeKey_2')."游币";
            }elseif(Input::get('selPrizeType_2')=="3"){
                $prize_list_2['prizeType'] = "3";
                $prize_list_2['prizeName'] = Input::get('prizeKey_2')."钻石";
                if (Input::get('prizeKey_2') <= 0 && $lineType!="2") {
                    return $this->back("钻石数量必须大于0");
                }
            }elseif(Input::get('selPrizeType_2')=="1"){
                $prize_list_2['prizeType'] = "1";
                $prize_list_2['prizeName'] = $in['card_des'][1];
                $prize_list_2['prizeKey'] = $in['card_code'][1];
                $prize_list_2['gid'] = $in['card_gid'][1];
                $prize_list_2['stock'] = $in['totalCount'][1];
                $prize_list_2['requestFrom'] = $in['requestFrom'][1];
//                 if (isset($in['card_code_old'][1]) && $in['card_change'][1]) {
//                     $params_n1['requestFrom'] = $in['requestFrom'][1];
//                     $result_n1=ProductService::release_distributioncard($params_n1);
//                     if($result_n1['errorCode']!=0){
//                         return $this->back($result_n1['errorDescription']);
//                     }
//                 }
//                 if ($in['card_code'][1] && $in['card_change'][1]) {
//                     $params_n['cardCode'] = $in['card_code'][1];
//                     $params_n['cardNumber'] = $in['totalCount'][1];
//                     $params_n['requestFrom'] = Utility::getUUID();
//                     $result_n=ProductService::distributioncard($params_n);
//                     if($result_n['errorCode']!=0){
//                         return $this->back($result_n['errorDescription']);
//                     }
//                     $prize_list_2['requestFrom'] = $params_n['requestFrom'];
//                 }
            }else{
                $prize_list_2['prizeType'] = "-1";
                $prize_list_2['prizeName'] = "无奖励";
                $prize_list_2['prizeKey'] = 0;
            }
            if($id&&Input::get('prizeId_2')){
                $prize_list_2['actionType'] = 'update';
                $prize_list_2['prizeId'] = Input::get('prizeId_2');
            }else{
                $prize_list_2['actionType'] = 'insert';
            }
            
//             if($prize_list_2['prizeType']!="-1"){
                $prize_2[] = $prize_list_2;
                if($prize_2){
                    $input['prizeListStr_2'] = json_encode($prize_2);
                }
//             }
            
        }
        
        if(Input::get('shareTypeList')){
            $input['shareType'] = Input::get('shareTypeList');
        }
        
        //var_dump( json_decode("[".Input::get("stepListStr")."]",true));die;
        //编辑时
        
        if($id){
            
            if (Input::get('mutexTaskId')) {
                
                $mutex_info = TaskV3Service::task_get(array('taskId'=>Input::get('mutexTaskId')));
                if(isset($mutex_info['result']['mutexTaskId'])&&$mutex_info['result']['mutexTaskId']){
                    return $this->back("添加失败,选定任务已互斥");
                }
                
                $input['taskId'] = $id;
                $input['mutexTaskId'] = Input::get('mutexTaskId');
                $TaskId_1 = $input['mutexTaskId'];
                $res = TaskV3Service::task_edit($input);
                $TaskId_2 = $id;
                unset($input);
                $input['taskId'] = $TaskId_1;
                $input['mutexTaskId'] = $TaskId_2;
                $res = TaskV3Service::task_edit($input);
            } else {
            
                $input['taskId'] = $id;
                $res = TaskV3Service::task_edit($input);
            
            }
            
        }else{
            if($lineType!="2"){
                //处理步骤json
                $steps = json_decode("[".Input::get("stepListStr")."]",true);
                foreach($steps as $k=>$v){
                    if($v['stepType']=="0"||$v['stepType']=="1"||$v['stepType']=="4"||$v['stepType']=="5"||$v['stepType']=="6"||$v['stepType']=="7"||$v['stepType']=="10"){
                        if (count($prize_pic)>0) {
                            $file = array_shift($prize_pic);//获取数组第一个元素
                            $new_filename = date('YmdHis') . str_random(4);
                            $mime = $file->getClientOriginalExtension();
                            $file->move($path,$new_filename . '.' . $mime );
                            $steps[$k]['stepCondition']['image'] = $dir . $new_filename . '.' . $mime;
                        }
                    }
                    if($steps[$k]['stepCondition']){
                        if (isset($steps[$k]['stepCondition']['gid'])) {
                            $game_info = GameService::getOneInfoById($steps[$k]['stepCondition']['gid'],'ios','basic');
                            $steps[$k]['stepCondition']['downloadUrl'] = isset($game_info['downurl'])?$game_info['downurl']:"";
                            $steps[$k]['stepCondition']['downurl_linkType'] = isset($game_info['tosafari'])?$game_info['tosafari']:"";
                        }
                        if ($v['stepType']=="9" && isset($steps[$k]['stepCondition']['templateId'])) {
                            $data_temp=ProductService::getTemplate(array('templateId'=>$steps[$k]['stepCondition']['templateId']));
                            if($data_temp['errorCode']==0){
                                $templateJson = array();
                                foreach ($data_temp['result'] as $item){
                                    $templateJson[$item['detailKey']] = isset($item['detailValue'])?$item['detailValue']:"";
                                }
                                $steps[$k]['stepCondition']['templateJson'] = json_encode($templateJson);
                            }
                        }
                        $steps[$k]['stepCondition'] = json_encode($steps[$k]['stepCondition']);
                    }else{
                        $steps[$k]['stepCondition'] = "{}";
                    }
                }
                if($steps){
                    $input['stepListStr'] = json_encode($steps);
                }
                
                if(Input::get('share')){
                    
                    //处理步骤json
                    $steps = json_decode("[".Input::get("stepListStr_2")."]",true);
                    foreach($steps as $k=>$v){
                        if($v['stepType']=="0"||$v['stepType']=="1"||$v['stepType']=="4"||$v['stepType']=="5"||$v['stepType']=="6"||$v['stepType']=="7"){
                            $file = array_shift($prize_pic);//获取数组第一个元素
                            $new_filename = date('YmdHis') . str_random(4);
                            $mime = $file->getClientOriginalExtension();
                            $file->move($path,$new_filename . '.' . $mime );
                            $steps[$k]['stepCondition']['image'] = $dir . $new_filename . '.' . $mime;
                        }
                        if($steps[$k]['stepCondition']){
                            if (isset($steps[$k]['stepCondition']['gid'])) {
                                $game_info = GameService::getOneInfoById($steps[$k]['stepCondition']['gid'],'ios','basic');
                                $steps[$k]['stepCondition']['downloadUrl'] = isset($game_info['downurl'])?$game_info['downurl']:"";
                                $steps[$k]['stepCondition']['downurl_linkType'] = isset($game_info['tosafari'])?$game_info['tosafari']:"";
                            }
                            if ($v['stepType']=="9" && isset($steps[$k]['stepCondition']['templateId'])) {
                                $data_temp=ProductService::getTemplate(array('templateId'=>$steps[$k]['stepCondition']['templateId']));
                                if($data_temp['errorCode']==0){
                                    $templateJson = array();
                                    foreach ($data_temp['result'] as $item){
                                        $templateJson[$item['detailKey']] = isset($item['detailValue'])?$item['detailValue']:"";
                                    }
                                    $steps[$k]['stepCondition']['templateJson'] = json_encode($templateJson);
                                }
                            }
                            $steps[$k]['stepCondition'] = json_encode($steps[$k]['stepCondition']);
                        }else{
                            $steps[$k]['stepCondition'] = "{}";
                        }
                    }
                    if($steps){
                        $input['stepListStr_2'] = json_encode($steps);
                    }
                }

            }
//             print_r($input);exit;
            if(Input::get('share')){
                if (isset($input['prizeListStr_2'])) {
                    $prizeListStr_2 = $input['prizeListStr_2'];
                    unset($input['prizeListStr_2']);
                }
                if (isset($input['taskDesc_2'])) {
                    $taskDesc_2 = $input['taskDesc_2'];
                    unset($input['taskDesc_2']);
                }
                if (isset($input['subTaskIds_2'])) {
                    $subTaskIds_2 = $input['subTaskIds_2'];
                    unset($input['subTaskIds_2']);
                }

                if($lineType!="2"){
                    $stepListStr_2 = $input['stepListStr_2'];unset($input['stepListStr_2']);
                }
                $input['shareType'] = 1;
                $res = TaskV3Service::task_add($input);
                $TaskId_1 = $res['extraResult'];
                
                $input['mutexTaskId'] = $res['result'];
                $input['taskName'] .= '[自分享]';
                $input['shareType'] = 2;
                if (isset($prizeListStr_2)) {
                    $input['prizeListStr'] = $prizeListStr_2;
                } elseif(isset($input['prizeListStr'])) {
                    unset($input['prizeListStr']);
                }
                if (isset($taskDesc_2)) {
                    $input['taskDesc'] = $taskDesc_2;
                } else {
                    $input['taskDesc'] = "";
                }
                if (isset($subTaskIds_2)) {
                    $input['subTaskIds'] = $subTaskIds_2;
                } else {
                    $input['subTaskIds'] = "";
                }
                if($lineType!="2"){
                    $input['stepListStr'] = $stepListStr_2;
                }
                $res = TaskV3Service::task_add($input);
                $TaskId_2 = $res['extraResult'];
                unset($input);
                $input['taskId'] = $TaskId_1;
                $input['mutexTaskId'] = $TaskId_2;
                $res = TaskV3Service::task_edit($input);
            } else {


                if (Input::get('mutexTaskId')) {
                    
                    $mutex_info = TaskV3Service::task_get(array('taskId'=>Input::get('mutexTaskId')));
                    if(isset($mutex_info['result']['mutexTaskId'])&&$mutex_info['result']['mutexTaskId']){
                        return $this->back("添加失败,选定任务已互斥");
                    }
                    
                    $input['mutexTaskId'] = Input::get('mutexTaskId');
                    $TaskId_1 = $input['mutexTaskId'];
                    $res = TaskV3Service::task_add($input);
                    $TaskId_2 = $res['extraResult'];
                    unset($input);
                    $input['taskId'] = $TaskId_1;
                    $input['mutexTaskId'] = $TaskId_2;
                    $res = TaskV3Service::task_edit($input);
                } else {
                    
                    $res = TaskV3Service::task_add($input);
                    
                }

                
            }

            
        }
//        print_r(input::all());
//        print_r($input);die;
//        print_r($res);die;
        if(!$res['errorCode']&&$res['result']){
            $data =array();
            if($lineType==1 && !$lineId){
//                 if(!empty($input['gid'])){
//                     $data = CacheService::cache_add_type_count_iostask($input['gid'],'game_task');
//                 }
//                 if(!isset($data['errorCode'])||$data['errorCode']!=0){
//                     return $this->redirect('IOS_activity/task/task-children-list','数据保存成功,缓存失败');
//                 }
                return $this->redirect('IOS_activity/task/task-children-list','数据保存成功');
            }
//             if(!empty($input['gid'])){
//                 $data = CacheService::cache_add_type_count_iostask($input['gid'],'game_task');
//             }
//             if(!isset($data['errorCode'])||$data['errorCode']!=0){
//                 return $this->redirect('IOS_activity/task/task-list?lineId='.$lineId,'数据保存成功,缓存失败');
//             }
			return $this->redirect('IOS_activity/task/task-list?lineId='.$lineId,'数据保存成功');
		}else{
			return $this->back($res['errorDescription']);
		}
	}

    public function postTaskOpen()
    {
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        $name = $_REQUEST['name'];
        $sharetype = $_REQUEST['sharetype'];
        $isline = $_REQUEST['isline'];
        $isHasPush = $_REQUEST['isHasPush'];
        $data = array('taskId'=>$id,'closeType'=>$type);
        if($type == "3"){
            $res = TaskV3Service::task_edit(array('taskId'=>$id,'sortValue'=>"100"));
        }elseif($type == "4"){
            $res = TaskV3Service::task_edit(array('taskId'=>$id,'sortValue'=>"50"));
        }else{
            if ($type == "0" && !$isHasPush) {
                $data['isHasPush'] = 'true';
            }
            $res = TaskV3Service::task_close($data);
        }
        

        if(!$res['errorCode']&&$res['result']){
            
            if ($type == "0" && $sharetype == "0" && !$isHasPush) {
                $template = TopicService::get_sys_mess_template(array('messageType'=>'3000_4'));
                if ($template['result']) {
                    $content = $template['result'][0]['content'];
                    $content = preg_replace("/i\+[0-9]/", $name, $content, 1);
//                     if ($isline == "1") {
//                         $linkType = '1064';
//                     } else {
//                         $linkType = '1037';
//                     }
//                     $linkType = '1036';
                    $data = array(
                        'alert' => $content,
                        'type' => 3,
//                         'linkType' => 0,
//                         'linkId' => $linkType,
//                         'linkValue' => $id,
                    );
                    $res = TopicService::system_push($data);
                }
            }

            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$type));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription'],'data'=>$type));
        }
    }
    
    //推荐
    public function postTaskRecommend()
    {
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'] ? true : false ;
        
        $data = array('taskId'=>$id,'isRecommend'=>$type);
        $res = TaskV3Service::task_edit($data);

        if(!$res['errorCode']&&$res['result']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$type));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription'],'data'=>$type));
        }
    }
    
    //互斥
    public function postTaskMutex()
    {
        $id = $_REQUEST['id'];
        $mutexid = $_REQUEST['mutexid'];
    
        $data = array('taskId'=>$id,'mutexTaskId'=>0);
        $res = TaskV3Service::task_edit($data);
        $data = array('taskId'=>$mutexid,'mutexTaskId'=>0);
        $res = TaskV3Service::task_edit($data);
    
        if(!$res['errorCode']&&$res['result']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功'));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription']));
        }
    }
    
    //关闭
    public function postTaskNewOpen()
    {
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        if(empty($id)) return $this->redirect('IOS_activity/task/task-new-list')->with('global_tips','参数丢失');
        $params=array();
        $params['id']=$id;
        $params['isOnOff']=$type;
        $params['activityType']=5;
        $result = ActivityService::update_activity_info($params,array_keys($params));
    
        if($result['errorCode'] == 0){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$type));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$result['errorDescription'],'data'=>$type));
        }
    }
    
    public function postTaskDel()
    {
        $id = $_REQUEST['id'];
        $data = array('taskId'=>$id);
        $res = TaskV3Service::task_del($data);
        if(!$res['errorCode']&&$res['result']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription'],'data'=>""));
        }
    }
    
    public function postTaskNewDel()
    {
        $id = $_REQUEST['id'];
        if(empty($id)) return $this->redirect('IOS_activity/task/task-new-list')->with('global_tips','参数丢失');
        $params=array();
        $params['id']=$id;
        $params['isActive']='false';
        $params['activityType']=5;
        $result = ActivityService::update_activity_info($params,array_keys($params));
    
        if($result['errorCode'] == 0){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$result['errorDescription'],'data'=>""));
        }
    }

    /**截图列表html**/
    public function postScreenshotList()
    {
        $taskId = Input::get('taskId');
        $stepId = Input::get('stepId');
        $pageSize = Input::get('count');
        $pageSize = $pageSize < 1 ? 1 : $pageSize;
        $uid = Input::get('uid');
        $data = array();
        $search = array('taskId'=>$taskId,'stepId'=>$stepId,'pageSize'=>$pageSize,'stepStatus'=>'-1','sortType'=>"1");
        if ($uid && $uid>0) {
            $search['uid'] = $uid;
        }
        $result = TaskV3Service::query_user_step_info_list($search);
//        print_r($result);die;


        $info = TaskV3Service::task_get(array('taskId'=>$taskId,'appname'=>'glwzry'));
        if ($info['result']['stepList']) {
            foreach ($info['result']['stepList'] as $step_arr) {
                if ($step_arr['stepType']==8) {
                    $stepId_re = $step_arr['stepId'];
                }
            }
        }
        
        if(!$result['errorCode']&&$result['result']){
            $user_arr = array();
            $pic_count = 1;
            foreach($result['result'] as $k=>$v){
                if($v['picUrl']){
                    $result['result'][$k]['picUrl'] = explode(',',$v['picUrl']);
                    $this_size =count($result['result'][$k]['picUrl']);
                    if($this_size>$pic_count){
                        $pic_count = $this_size;
                    }
                }
                $user_arr[] = $v['uid'];
            }
//            var_dump($result);die;
            $uinfos = UserService::getMultiUserInfoByUids(array_unique($user_arr));
            $re = array();
            if (is_array($uinfos)) {
                foreach ($uinfos as $row) {
                    $uinfos[$row['uid']] = $row;
                    
                    if (isset($stepId_re)) {
                        $search_re = array('taskId'=>$taskId,'stepId'=>$stepId_re,'uid'=>$row['uid']);
                        $result_re = TaskV3Service::query_user_step_info_list($search_re);
                        if (isset($result_re['result'][0]['prizeContent'])) {
                            $prizeContent_arr = explode('=', $result_re['result'][0]['prizeContent']);
                            $re[$row['uid']] = $prizeContent_arr[0];
                        }
                    }
                }
            }
            $data['users'] = $uinfos;
            $data['re'] = $re;
//            foreach($result['result'] as &$item){
//                $item['can_use'] = $item['materialStock'] + $item['materialUsedStock'] - $item['materialQuota'];
//            }
//            $data=self::processingInterface($result,$data,$params['pageSize']);
            $data['pic_count'] = $pic_count;
            $data['datalist'] = $result['result'];
            //var_dump($data['datalist']);die;
            foreach($data['datalist'] as $k => $v){
                if(isset($v['approvalContent'])){
                    if(is_null(json_decode($v['approvalContent']))){
                        $data['datalist'][$k]['autorContent'] = $v['approvalContent'];
                    }else {
                        $data['datalist'][$k]['approvalContent'] = json_decode($v['approvalContent'], true);
                        $arr = self::$approvalArr;
                        foreach ($data['datalist'][$k]['approvalContent'] as $k1 => $v2) {
                            if (isset($arr[$k1])) {
                                $data['datalist'][$k]['approvalContent'][$arr[$k1]] = $data['datalist'][$k]['approvalContent'][$k1];
                                unset($data['datalist'][$k]['approvalContent'][$k1]);
                            }
                        }
                    }
                }
            }
//             var_dump($data['datalist']);die;
            $html = $this->html('screenshot-list',$data);
            return $this->json(array('html'=>$html));
        }else{
            return $this->json(array('html'=>"未找到数据"));
        }

    }

    public function getApprovalList()
    {
        $data['taskId'] = Input::get('taskId');
        $data['taskName'] = Input::get('taskName');
        $data['approvalReminderInfo'] = Input::get('approvalReminderInfo');
        $data['stepId'] = Input::get('stepId');
        $data['stepType'] = Input::get('stepType');
        $data['step_content'] = Input::get('step_content');
        $data['step_img'] = Input::get('step_img');
        $data['appleId'] = Input::get('appleId');
        $data['appPasswordKey'] = Input::get('appPasswordKey');
        $data['title'] = Input::get('title');
        $data['minlevel'] = Input::get('minlevel');
        $data['maxlevel'] = Input::get('maxlevel');
        $result = TaskV3Service::query_user_step_info_count(array('stepId'=>$data['stepId'],'stepStatus'=>"-1"));
        
        if(!$result['errorCode']&&$result['totalCount']) {
            $data['totle'] = $result['totalCount'];
        }
//        var_dump($data);die;
        return $this->display('audit-picture-list',$data);
    }

    public function getApprovalOne()
    {
        $taskId = Input::get('taskId');
        $taskName = Input::get('taskName');
        $stepId = Input::get('stepId');

        $step_content = Input::get('step_content');
        $step_img = Input::get('step_img');
        $data = array();
//        print_r($search);
        $result = TaskV3Service::query_user_step_info_list(array('taskId'=>$taskId,'stepId'=>$stepId,'pageSize'=>"1",'stepStatus'=>'-1','sortType'=>"0"));
//        print_r($result);

        $info = TaskV3Service::task_get(array('taskId'=>$taskId,'appname'=>'glwzry'));
        if ($info['result']['stepList']) {
            foreach ($info['result']['stepList'] as $step_arr) {
                if ($step_arr['stepType']==8) {
                    $stepId_re = $step_arr['stepId'];
                }
            }
        }
        
        if(!$result['errorCode']&&$result['result']) {
            $user_arr = array();
            if ($result['result'][0]['picUrl']) {
                $result['result'][0]['picUrl'] = explode(',',$result['result'][0]['picUrl']);
            }
            $user_arr[] = $result['result'][0]['uid'];
            $uinfos = UserService::getMultiUserInfoByUids(array_unique($user_arr));
            $re = array();
            if (is_array($uinfos)) {
                foreach ($uinfos as $row) {
                    $uinfos[$row['uid']] = $row;
                    
                    if (isset($stepId_re)) {
                        $search_re = array('taskId'=>$taskId,'stepId'=>$stepId_re,'uid'=>$row['uid']);
                        $result_re = TaskV3Service::query_user_step_info_list($search_re);
                        if (isset($result_re['result'][0]['prizeContent'])) {
                            $prizeContent_arr = explode('=', $result_re['result'][0]['prizeContent']);
                            $re[$row['uid']] = $prizeContent_arr[0];
                        }
                    }
                }
            }
            $data['users'] = $uinfos;
            $data['re'] = $re;
            $data['data'] = $result['result'][0];
//            print_r(Input::get());
//            print_r($data);
        }
        $data['step_img'] = $step_img;
        $data['step_content'] = $step_content;
        $data['taskId'] = $taskId;
        $data['stepId'] = $stepId;
        $data['taskName'] = $taskName;
        $data['stepType'] = Input::get('stepType');
        $data['appleId'] = Input::get('appleId');
        $data['appPasswordKey'] = Input::get('appPasswordKey');
        $data['title'] = Input::get('title');
        $result = TaskV3Service::query_user_step_info_count(array('stepId'=>$data['stepId'],'stepStatus'=>"true"));
        if(!$result['errorCode']&&$result['totalCount']) {
            $data['totle'] = $result['totalCount'];
        }
        if(isset($data['data']['approvalContent'])&&!empty($data['data']['approvalContent'])){
            if(is_null(json_decode($data['data']['approvalContent']))){
                $data['data']['autorContent'] = $data['data']['approvalContent'];
            }else{
                $data['data']['approvalContent'] = json_decode($data['data']['approvalContent'],true);
                $arr = self::$approvalArr;
                foreach($data['data']['approvalContent'] as $k => $v){
                    if (isset($arr[$k])) {
                        $data['data']['approvalContent'][$arr[$k]] = $data['data']['approvalContent'][$k];
                        unset($data['data']['approvalContent'][$k]);
                    }
                }
            }
        }
        //var_dump($data);die;
        return $this->display('audit-picture-one',$data);

    }

    public function postApproval()
    {
        $taskId = Input::get('taskId');
        $stepId = Input::get('stepId');
        $passIds = Input::get('passIds');
        $notPassIds = Input::get('notPassIds');
        $notPassUids = Input::get('notPassUids');
        $notPassUids = substr($notPassUids,0,-1);
        $PassUids = Input::get('PassUids');
        $PassUids = substr($PassUids,0,-1);
        $notPassDeviceIds = Input::get('notPassDeviceIds');
        $notPassDeviceIds = substr($notPassDeviceIds,0,-1);
        $PassDeviceIds = Input::get('PassDeviceIds');
        $PassDeviceIds = substr($PassDeviceIds,0,-1);
        $success = "true";
        $mess = "修改失败！";
        $name = $_SESSION['_sf2_attributes']['youxiduo_admin']['realname'];
        if(!isset($name)){
            $name = '';
        }
        $data = array('stepId'=>$stepId,'taskId'=>$taskId,'operateName'=>$name,'operateTime'=>date('Y-m-d H:i:s', time()));
        if($passIds){
            $data['passIds'] = substr($passIds,0,-1);
            $data['passUids'] = $PassUids;
            $data['passDeviceIds'] = $PassDeviceIds;
            $res1 = TaskV3Service::approval_step_screenshot($data);unset($data['passIds']);unset($data['passUids']);unset($data['passDeviceIds']);
            if(isset($res1['errorCode'])&&$res1['errorCode']){
                $success = "false";$mess = $res1['errorDescription'];
            }
        }
        if($notPassIds){
            $data['againIds'] = substr($notPassIds,0,-1);
            $data['checkFailedDesc'] = Input::get('checkFailedDesc');
            $data['notPassUids'] = $notPassUids;
            $data['notPassDeviceIds'] = $notPassDeviceIds;
            $res2 = TaskV3Service::approval_step_screenshot($data);unset($data['notPassIds']);unset($data['notPassUids']);unset($data['checkFailedDesc']);unset($data['notPassDeviceIds']);
            if(isset($res2['errorCode'])&&$res2['errorCode']){
                $success = "false";$mess = $res2['errorDescription'];
            }
        }
//        $res = TaskV3Service::approval_step_screenshot($data);
//        print_r(Input::get());
//        echo $mess;die;
        if($success == "true"){
            
            if($notPassUids){
                $data_s = array(
                    'title' => '',
                    'content' => '',
                    'type' => '2012',
                    'linkType' => '3',
                    'link' =>  $taskId,
                    'toUid' => $notPassUids,
                    'sendTime' => date("Y-m-d H:i:s",time()),
                    'isTop' => "false",
                    'isPush' => "false",
                    'allUser' => "false",
                    'addTime' => date("Y-m-d H:i:s",time()),
                    'updateTime' => date("Y-m-d H:i:s",time()),
                );
                $res = TopicService::system_send($data_s);
                
                $template = TopicService::get_sys_mess_template(array('messageType'=>'2012_3'));
                if ($template['result']) {
                    $content = $template['result'][0]['content'];
                    $data = array(
                        'alert' => $content,
                        'toUid' => $notPassUids,
                        'type' => 3,
                        'linkType' => 0,
                        'linkId' => '1037',
                        'linkValue' => $taskId,
                    );
                    $res = TopicService::system_push($data);
                }   
            }
            
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$mess,'data'=>""));
        }
    }
    public function postApprovalAll()
    {
        $stepId = Input::get('stepId');
        $data = array('stepId'=>$stepId);
        $res = TaskV3Service::approval_step_all_screenshot($data);
        if(!$res['errorCode']&&$res['result']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
        }
    }

    public function postAjaxUploadImg()
    {
        if(Input::file('prize_pic')){
            $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
            $path = storage_path() . $dir;
            $file_arr = Input::file('prize_pic');
            $file = $file_arr[0];
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $icon = $dir . $new_filename . '.' . $mime;
            $icon = Utility::getImageUrl($icon);
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$icon));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>"图片丢失"));
        }
    }

    public function postStepSave(){
        $input = Input::get();
//        dd($input);
        $input['stepCondition'] = json_decode($input['stepCondition'],true);
        if (isset($input['stepCondition']['gid'])) {
            $game_info = GameService::getOneInfoById($input['stepCondition']['gid'],'ios','basic');
            $input['stepCondition']['downloadUrl'] = isset($game_info['downurl'])?$game_info['downurl']:"";
            $input['stepCondition']['downurl_linkType'] = isset($game_info['tosafari'])?$game_info['tosafari']:"";
        }
        if ($input['stepType']=="9" && isset($input['stepCondition']['templateId'])) {
            $data_temp=ProductService::getTemplate(array('templateId'=>$input['stepCondition']['templateId']));
            if($data_temp['errorCode']==0){
                $templateJson = array();
                foreach ($data_temp['result'] as $item){
                    $templateJson[$item['detailKey']] = isset($item['detailValue'])?$item['detailValue']:"";
                }
                $input['stepCondition']['templateJson'] = json_encode($templateJson);
                
            }
        }
        $input['stepCondition'] = json_encode($input['stepCondition']);
        if($input){
            if(isset($input['stepId'])&&!empty($input['stepId'])){
                $res = TaskV3Service::update_step_base_info($input);
            }else{
                $res = TaskV3Service::insert_step($input);
            }
            print_r($res);exit;
            if(!$res['errorCode']&&$res['result']){
                echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
            }else{
                echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
            }
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
        }

    }

    public function postStepDel(){
        $input = Input::get();
        if($input){
            $res = TaskV3Service::delete_step($input);
            if(!$res['errorCode']&&$res['result']){
                echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
            }else{
                echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
            }
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
        }

    }

    public function getTaskRule(){
        $data = array();
        $res = TaskV3Service::task_rule(array());
        if(!$res['errorCode']&&$res['result']){
            $data['data'] = $res['result'];
        }
        return $this->display('task-rule',$data);
    }

    public function postTaskRuleUpdate () {
        $data['taskRule'] = Input::get('content');
        $data['taskIdfaRule'] = Input::get('content2');
        if ($data['taskRule'] || $data['taskIdfaRule']) {
            $res = TaskV3Service::update_task_rule($data);
            if(!$res['errorCode'] && $res['result']){
                echo json_encode(array('success'=>"true",'msg'=>'修改成功','data'=>""));
            }else{
                echo json_encode(array('success'=>"false",'msg'=>'修改失败','data'=>""));
            }
        }
    }
    

    /**
     * 任务pop列表
     */
    public function getTaskPopList()
    {
        $pageIndex = Input::get('page',1);
        $keytype = Input::get('keytype','');
        $keyword = Input::get('keyword','');
        $appType = Input::get('appType',0);
        $taskType = Input::get('taskType',1);
        $offline = Input::get('offline','');
        $pageSize = 6;
        $data = array();
        switch ($keytype) {
            case 'id':
                $taskId = $keyword;
                $title = '';
                break;
            case 'title':
                $taskId = '';
                $title = $keyword;
                break;
            default:
                $taskId = '';
                $title = '';
                
        }
        $data['keyword'] = $keyword;
        $search = array('taskId'=>$taskId,'pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','taskName'=>$title,'isLoadPrize'=>"true",'platformType'=>'I','isLoadStep'=>'true','isLoadStatistics'=>"true",'appType'=>$appType,'offline'=>$offline);
        switch ($taskType) {
            case '1':
                $search['isLine'] = "false";
                $search['isSubTask'] = "false";
                break;
            default:
                $search['isLine'] = "true";
                $search['isSubTask'] = "false";
        }
        $res = TaskV3Service::task_list(array_filter($search));
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
        $pager = Paginator::make(array(),$total,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['app'] = array('0'=>'不区分app','1'=>'ios4.0app','2'=>'攻略app');
        $data['taskTypeList'] = array('1'=>'普通任务','2'=>'连续任务');
        $data['taskType'] = $taskType;
        $data['offline'] = $offline;
        $html = $this->html('pop-task-list',$data);
        return $this->json(array('html'=>$html));
    }


    /**
     * 子任务pop列表
     */
    public function getSubtaskPopList()
    {
        $pageIndex = Input::get('page',1);
        $keytype = Input::get('keytype','');
        $keyword = Input::get('keyword','');
        $offline = Input::get('offline','');
        $lineId = Input::get('lineId','');
        $pageSize = 6;
        $data = array();
        switch ($keytype) {
            case 'id':
                $taskId = $keyword;
                $title = '';
                break;
            case 'title':
                $taskId = '';
                $title = $keyword;
                break;
            default:
                $taskId = '';
                $title = '';
    
        }
        $data['keyword'] = $keyword;
        $search = array('taskId'=>$taskId,'lineId'=>$lineId,'pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','taskName'=>$title,'isLoadPrize'=>"true",'isSubTask'=>"true",'platformType'=>'I','isLoadStep'=>'true','isLoadStatistics'=>"true",'offline'=>$offline);
        $res = TaskV3Service::task_list(array_filter($search));
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
        $pager = Paginator::make(array(),$total,$pageSize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['offline'] = $offline;
        $data['lineId'] = $lineId;
        $html = $this->html('pop-subtask-list',$data);
        return $this->json(array('html'=>$html));
    }
    
    
    /**
     * 查看已审核用户
     */
    public function getUserChecked()
    {
        $data = array();
        $uid = '';
        $pageSize = 10;
        $taskId = '';
        $taskName = '';
        $pageIndex = Input::get('page',1);
        $stepId = Input::get('stepId','');
        $title = Input::get('title','');
        $stepStatus = Input::get('stepStatus','');
        $name = Input::get('name','');
        $add_user = Input::get('addUser','false');
        $isIssue = Input::get('isIssue','');
        if(!empty($name)){
            $arr_uid = array();
            $arr_uid_info = UserService::searchByUserName($name);
            foreach($arr_uid_info as $k =>$v){
                $arr_uid[$k] = $v['uid'];
            }
            $arr_uid_str = implode(',',$arr_uid);
            if(isset($arr_uid_str)){
                $uid = $arr_uid_str;
            }
        }
        $startTime = Input::get('startTime','');
        $endTime = Input::get('endTime','');
        $search = array('pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$uid,'stepStatus'=>$stepStatus,'add_user'=>$add_user,'isIssue'=>$isIssue);
        $res = TaskV3Service::task_checked(array_filter($search));
        if(!$res['errorCode']&&$res['result']){
            
            if($add_user=='true'){
                $search_all = array('pageSize'=>100000,'pageIndex'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$uid,'stepStatus'=>1,'add_user'=>$add_user,'isIssue'=>$isIssue);
                $res_all = TaskV3Service::task_checked(array_filter($search_all));
                if ($res_all['result']) {
                    $admin_id = $this->current_user['id'];
                    $keyname = 'selected_' . $admin_id . '_uids';
                    $selecteds = array();
                    if(Session::has($keyname)){
                        $selecteds = Session::get($keyname);
                    }
                    foreach($res_all['result'] as $uid){
                        if (isset($uid['uid'])) {
                            $selecteds[$uid['uid']]  = array('uid'=>$uid['uid'],'nickname'=>'玩家'.$uid['uid']);
                        }
                    }
                    Session::put($keyname,$selecteds);
                    sleep(3);
                }
            }
            
            $total = $res['totalCount'];
            $result = $res['result'];
            $taskId = '';
            $taskName = '';
            $arr_res = $arr_info = array();
            foreach($result as $k=>$row){
                $taskId = $row['taskId'];
                $taskName = $row['taskName'];
                $arr_res[$k]['uid'] = $row['uid'];
                $arr_info = MyHelpLx::insertUserhtmlIntoRes($arr_res,'ios');
                $result[$k]['userinfo'] = $arr_info[$k];
                
                $info = TaskV3Service::task_get(array('taskId'=>$row['taskId'],'appname'=>'glwzry'));
                if ($info['result']['stepList']) {
                    foreach ($info['result']['stepList'] as $step_arr) {
                        if ($step_arr['stepType']==8) {
                            $stepId_re = $step_arr['stepId'];
                        }
                    }
                }
                if (isset($stepId_re)) {
                    $search_re = array('taskId'=>$row['taskId'],'stepId'=>$stepId_re,'uid'=>$row['uid']);
                    $result_re = TaskV3Service::query_user_step_info_list($search_re);
                    if (isset($result_re['result'][0]['prizeContent'])) {
                        $prizeContent_arr = explode('=', $result_re['result'][0]['prizeContent']);
                        $re = $prizeContent_arr[0];
                    }
                }

                if(isset($row['approvalContent'])&&!empty($row['approvalContent'])){
                    if(is_null(json_decode($row['approvalContent']))){
                        $result[$k]['autorContent'] = $row['approvalContent'];
                    }else{
                        $result[$k]['approvalContent'] = json_decode($row['approvalContent'],true);
                        $arr = self::$approvalArr;
                        foreach($result[$k]['approvalContent'] as $j => $v){
                            if (isset($arr[$j])) {
                                $result[$k]['approvalContent'][$arr[$j]] = $result[$k]['approvalContent'][$j];
                                unset($result[$k]['approvalContent'][$j]);
                            }
                        }
                        if (isset($re)) {
                            $result[$k]['approvalContent']['充值额度'] = $re;
                        }
                        $result[$k]['approvalContent'] = json_encode($result[$k]['approvalContent']);
                    }
                } else {
                    if (isset($re)) {
                        $result[$k]['approvalContent']['充值额度'] = $re;
                        $result[$k]['approvalContent'] = json_encode($result[$k]['approvalContent']);
                    }
                }
            }
        }else{
            $total = 0;
            $result= array();
        }
        $pager = Paginator::make(array(),$total,$pageSize);
        $search['title'] = $title;
        $pager->appends($search);
        $data['search'] = $search;
        $data['search']['name'] = $name;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['taskStatus'] = array(''=>'审核状态','1'=>'通过','2'=>'不通过');
        $data['stepId'] = $stepId;
        $data['title'] = $title;
        $data['taskId'] = $taskId;
        $data['taskName'] = $taskName;
        //var_dump($data['datalist']);die;
        return $this->display('user-checked',$data);
    }

    /**
     * excel导出
     */
    public function getProductDataDownload()
    {
        $uid = '';
        $pageSize = 100000;
        $pageIndex = 1;
        $stepId = Input::get('stepId','');
        $title = Input::get('title','');
        $stepStatus = Input::get('stepStatus','');
        $name = Input::get('name','');
        $add_user = Input::get('addUser','false');
        if(!empty($name)){
            $arr_uid = array();
            $arr_uid_info = UserService::searchByUserName($name);
            foreach($arr_uid_info as $k =>$v){
                $arr_uid[$k] = $v['uid'];
            }
            $arr_uid_str = implode(',',$arr_uid);
            if(isset($arr_uid_str)){
                $uid = $arr_uid_str;
            }
        }
        $startTime = Input::get('startTime','');
        $endTime = Input::get('endTime','');
        $search = array('pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$uid,'stepStatus'=>$stepStatus,'add_user'=>$add_user);
        $res = TaskV3Service::task_checked(array_filter($search));
        if(!$res['errorCode']&&$res['result']){

            if($add_user=='true'){
                $search_all = array('pageSize'=>100000,'pageIndex'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$uid,'stepStatus'=>1,'add_user'=>$add_user);
                $res_all = TaskV3Service::task_checked(array_filter($search_all));
                if ($res_all['result']) {
                    $admin_id = $this->current_user['id'];
                    $keyname = 'selected_' . $admin_id . '_uids';
                    $selecteds = array();
                    if(Session::has($keyname)){
                        $selecteds = Session::get($keyname);
                    }
                    foreach($res_all['result'] as $uid){
                        if (isset($uid['uid'])) {
                            $selecteds[$uid['uid']]  = array('uid'=>$uid['uid'],'nickname'=>'玩家'.$uid['uid']);
                        }
                    }
                    Session::put($keyname,$selecteds);
                    sleep(3);
                }
            }
            $result = $res['result'];
            $arr_res = $arr_info = array();
            foreach($result as $k=>$row){
                $arr_res[$k]['uid'] = $row['uid'];
                $arr_info = MyHelpLx::insertUserhtmlIntoRes($arr_res,'ios');
                $result[$k]['userinfo'] = $arr_info[$k];

                $info = TaskV3Service::task_get(array('taskId'=>$row['taskId'],'appname'=>'glwzry'));
                if ($info['result']['stepList']) {
                    foreach ($info['result']['stepList'] as $step_arr) {
                        if ($step_arr['stepType']==8) {
                            $stepId_re = $step_arr['stepId'];
                        }
                    }
                }
                if (isset($stepId_re)) {
                    $search_re = array('taskId'=>$row['taskId'],'stepId'=>$stepId_re,'uid'=>$row['uid']);
                    $result_re = TaskV3Service::query_user_step_info_list($search_re);
                    if (isset($result_re['result'][0]['prizeContent'])) {
                        $prizeContent_arr = explode('=', $result_re['result'][0]['prizeContent']);
                        $re = $prizeContent_arr[0];
                    }
                }
                
                if(isset($row['approvalContent'])&&!empty($row['approvalContent'])){
                    if(is_null(json_decode($row['approvalContent']))){
                        $result[$k]['autorContent'] = $row['approvalContent'];
                    }else{
                        $result[$k]['approvalContent'] = json_decode($row['approvalContent'],true);
                        $arr = self::$approvalArr;
                        foreach($result[$k]['approvalContent'] as $j => $v){
                            if (isset($arr[$j])) {
                                $result[$k]['approvalContent'][$arr[$j]] = $result[$k]['approvalContent'][$j];
                                unset($result[$k]['approvalContent'][$j]);
                            }
                        }
                        if (isset($re)) {
                            $result[$k]['approvalContent']['充值额度'] = $re;
                        }
                        $result[$k]['approvalContent'] = json_encode($result[$k]['approvalContent']);
                    }
                } else {
                    if (isset($re)) {
                        $result[$k]['approvalContent']['充值额度'] = $re;
                        $result[$k]['approvalContent'] = json_encode($result[$k]['approvalContent']);
                    }
                }
            }
        }else{
            $result= array();
        }
        //var_dump($result);die;
        require_once base_path() . '/libraries/PHPExcel.php';
        $excel = new \PHPExcel();
        $excel->setActiveSheetIndex(0);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->setTitle('任务审核用户统计');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(100);
        $excel->getActiveSheet()->setCellValue('A1','任务标题');
        $excel->getActiveSheet()->setCellValue('B1','任务步骤');
        $excel->getActiveSheet()->setCellValue('C1','用户名');
        $excel->getActiveSheet()->setCellValue('D1','审核状态');
        $excel->getActiveSheet()->setCellValue('E1','上传截图时间');
        $excel->getActiveSheet()->setCellValue('F1','操作人员和时间');
        $excel->getActiveSheet()->setCellValue('G1','AppleId|appPasswordKey');
        $excel->getActiveSheet()->setCellValue('H1','玩家上传信息');
        $excel->getActiveSheet()->freezePane('A2');
        foreach($result as $index=>$row){
            $taskName = isset($row['taskName'])?$row['taskName']:'';
            $stepStatus = '';
            if(isset($row['stepStatus'])){
                if($row['stepStatus'] == '1'){
                    $stepStatus = '通过';
                }elseif($row['stepStatus'] == '2'){
                    $stepStatus = '不通过';
                }
            }
            $createTime = isset($row['createTime'])?$row['createTime']:'';
            $operateName = isset($row['operateName'])?$row['operateName']:'';
            $updateTime = isset($row['updateTime'])?$row['updateTime']:'';

            $id = isset($row['userinfo']['uid'])?$row['userinfo']['uid']:'';
            $nickname = isset($row['userinfo']['nickname'])?$row['userinfo']['nickname']:"";
            $mobile = isset($row['userinfo']['mobile'])?$row['userinfo']['mobile']:"";
            $appleId = isset($row['appleId'])?$row['appleId']:"";
            $appPasswordKey = isset($row['appPasswordKey'])?$row['appPasswordKey']:"";
            $approvalContent = '';
            if(isset($row['approvalContent'])){
                $approvalContent = json_decode($row['approvalContent'],true);
            }
            $excel->getActiveSheet()->setCellValue('A'.($index+2), $taskName);
            $excel->getActiveSheet()->setCellValue('B'.($index+2), $title);
            $excel->getActiveSheet()->setCellValue('C'.($index+2), $id.' | '.$nickname.' | '.$mobile);
            $excel->getActiveSheet()->setCellValue('D'.($index+2), $stepStatus);
            $excel->getActiveSheet()->setCellValue('E'.($index+2), $createTime);
            $excel->getActiveSheet()->setCellValue('F'.($index+2), $operateName."[".$updateTime."]");
            $excel->getActiveSheet()->setCellValue('G'.($index+2), $appleId.' | '.$appPasswordKey);
            $leng = '';
            if(!empty($approvalContent)){
                foreach($approvalContent as $k=>$v){
                    $leng .=  $k.":".$approvalContent[$k]."|";
                }
                $leng = substr($leng, 0, -1);
            }
            $excel->getActiveSheet()->setCellValue('H'.($index+2),$leng);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'任务审核用户统计.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
        $writer->save('php://output');
    }

    
    
    /**
     * excel导出
     */
    public function getProductUidDownload()
    {
        $uid = '';
        $pageSize = 100000;
        $pageIndex = 1;
        $stepId = Input::get('stepId','');
        $title = Input::get('title','');
        $stepStatus = Input::get('stepStatus','');

        $startTime = Input::get('startTime','');
        $endTime = Input::get('endTime','');
        $search = array('pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$uid,'stepStatus'=>$stepStatus);
        $res = TaskV3Service::task_checked(array_filter($search));
        if(!$res['errorCode']&&$res['result']){

            $result = $res['result'];
        }else{
            $result= array();
        }
        //var_dump($result);die;
        require_once base_path() . '/libraries/PHPExcel.php';
        $excel = new \PHPExcel();
        $excel->setActiveSheetIndex(0);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->setTitle('任务审核用户UID统计');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(100);
        $excel->getActiveSheet()->setCellValue('A1','任务标题');
        $excel->getActiveSheet()->setCellValue('B1','任务步骤');
        $excel->getActiveSheet()->setCellValue('C1','用户UID');
        $excel->getActiveSheet()->setCellValue('D1','审核状态');
        $excel->getActiveSheet()->setCellValue('E1','上传截图时间');
        $excel->getActiveSheet()->setCellValue('F1','操作人员和时间');
        $excel->getActiveSheet()->setCellValue('G1','AppleId|appPasswordKey');
        $excel->getActiveSheet()->setCellValue('H1','玩家上传信息');
        $excel->getActiveSheet()->freezePane('A2');
        foreach($result as $index=>$row){
            $taskName = isset($row['taskName'])?$row['taskName']:'';
            $stepStatus = '';
            if(isset($row['stepStatus'])){
                if($row['stepStatus'] == '1'){
                    $stepStatus = '通过';
                }elseif($row['stepStatus'] == '2'){
                    $stepStatus = '不通过';
                }
            }
            $createTime = isset($row['createTime'])?$row['createTime']:'';
            $operateName = isset($row['operateName'])?$row['operateName']:'';
            $updateTime = isset($row['updateTime'])?$row['updateTime']:'';
    
            $id = isset($row['uid'])?$row['uid']:'';
            $appleId = isset($row['appleId'])?$row['appleId']:"";
            $appPasswordKey = isset($row['appPasswordKey'])?$row['appPasswordKey']:"";
            $approvalContent = '';
            if(isset($row['approvalContent'])){
                $approvalContent = json_decode($row['approvalContent'],true);
            }
            $excel->getActiveSheet()->setCellValue('A'.($index+2), $taskName);
            $excel->getActiveSheet()->setCellValue('B'.($index+2), $title);
            $excel->getActiveSheet()->setCellValue('C'.($index+2), $id);
            $excel->getActiveSheet()->setCellValue('D'.($index+2), $stepStatus);
            $excel->getActiveSheet()->setCellValue('E'.($index+2), $createTime);
            $excel->getActiveSheet()->setCellValue('F'.($index+2), $operateName."[".$updateTime."]");
            $excel->getActiveSheet()->setCellValue('G'.($index+2), $appleId.' | '.$appPasswordKey);
            $leng = '';
            if(!empty($approvalContent)){
                foreach($approvalContent as $k=>$v){
                    $leng .=  $k.":".$approvalContent[$k]."|";
                }
                $leng = substr($leng, 0, -1);
            }
            $excel->getActiveSheet()->setCellValue('H'.($index+2),$leng);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'任务审核用户UID统计.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
        $writer->save('php://output');
    }
    
    public static function stringFromColumnIndex($pColumnIndex = 0)
    {
        //  Using a lookup cache adds a slight memory overhead, but boosts speed
        //  caching using a static within the method is faster than a class static,
        //      though it's additional memory overhead
        static $_indexCache = array();

        if (!isset($_indexCache[$pColumnIndex])) {
            // Determine column string
            if ($pColumnIndex < 26) {
                $_indexCache[$pColumnIndex] = chr(65 + $pColumnIndex);
            } elseif ($pColumnIndex < 702) {
                $_indexCache[$pColumnIndex] = chr(64 + ($pColumnIndex / 26)) . chr(65 + $pColumnIndex % 26);
            } else {
                $_indexCache[$pColumnIndex] = chr(64 + (($pColumnIndex - 26) / 676)) . chr(65 + ((($pColumnIndex - 26) % 676) / 26)) . chr(65 + $pColumnIndex % 26);
            }
        }
        return $_indexCache[$pColumnIndex];
    }
    
    public function postSgin()
    {
        $taskId = Input::get('taskid');
        $taskName = Input::get('taskname');
        $ids = Input::get('ids');
        $ids_str = implode(',', $ids);
        $uids = Input::get('uids');
        $uids_str = implode(',', $uids);
        $res = TaskV3Service::doSgin(array('userStepIds'=>$ids_str));
        if(!$res['errorCode']&&$res['result']){
            
            $data_s = array(
                'title' => '',
                'content' => $taskName,
                'type' => '2012',
                'linkType' => '4',
                'link' =>  $taskId,
                'toUid' => $uids_str,
                'sendTime' => date("Y-m-d H:i:s",time()),
                'isTop' => "false",
                'isPush' => "false",
                'allUser' => "false",
                'addTime' => date("Y-m-d H:i:s",time()),
                'updateTime' => date("Y-m-d H:i:s",time()),
            );
            $res = TopicService::system_send($data_s);
            
            $template = TopicService::get_sys_mess_template(array('messageType'=>'2012_4'));
            if ($template['result']) {
                $content = $template['result'][0]['content'];
                $content = preg_replace("/i\+[0-9]/", $taskName, $content, 1);
                $data = array(
                    'alert' => $content,
                    'toUid' => $uids_str,
                    'type' => 3,
                    'linkType' => 0,
                    'linkId' => '1037',
                    'linkValue' => $taskId,
                );
                $res = TopicService::system_push($data);
            }
            
            return $this->json(array('state'=>1,'msg'=>'更新成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'更新失败，请重试'));
        }
    }
    

}