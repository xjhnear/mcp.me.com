<?php
namespace modules\v4_task\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Helper\MyHelpLx;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Android\Model\ActivityTaskUser;
use Youxiduo\V4\Activity\ActivityService;
use Youxiduo\Helper\MyHelp;
use Youxiduo\Task\TaskLionService;
use Youxiduo\Task\TaskV3Service;
use Youxiduo\V4\User\UserService;
use libraries\Helpers;
use Youxiduo\Cache\CacheService;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Session;
use Youxiduo\Imall\ProductService;
use Youxiduo\Bbs\TopicService;
use Illuminate\Support\Facades\Config;

class TaskController extends BackendController
{
//     public static $stepArr = array('4'=>'提交苹果id邮箱','0'=>'图文','1'=>'截图','10'=>'上传随机等级截图','2'=>'下载','5'=>'玩家上传文字和图片','6'=>'游戏信息和截图','7'=>'游戏等级信息和截图','8'=>'充值额度与奖励设置','9'=>'任务物品信息');
    public static $stepArr = array('1'=>'输入框','2'=>'纯文字','4'=>'上传图片','5'=>'多选择框');
    public static $stepArr_b = array('noButton'=>'无按钮','bigButton'=>'单按钮居中','dualButton'=>'双按钮','leftButton'=>'单按钮左对齐','rightButton'=>'单按钮右对齐','video'=>'视频','inmobile'=>'inmobile');
    public static $submitTypeArr = array('ranking'=>'排行榜','submit'=>'提交信息');
    public static $approvalArr = array('account'=>'账号','password'=>'密码','areaServer'=>'区服','loginWay'=>'登录方式','rank'=>'等级','dsc'=>'文字','goodsId'=>'背包物品ID');
	public function _initialize()
	{
		$this->current_module = 'v4_task';
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
        $sortType = Input::get('sortType','0');
        $isLine = Input::get('isLine','');
        $startTime = Input::get('createTimeBegin','');
        $endTime = Input::get('createTimeEnd','');
        $complete_type = Input::get('complete_type');
        $appType = Input::get('appType',3);
        $platformType = Input::get('platformType','I');
        if ($platformType == 'I') {
            $gid = Input::get('gameId_I','');
        } else {
            $gid = Input::get('gameId_A','');
        }
        $accessType = Input::get('accessType','');
        $counterState = Input::get('counterState','');
        $isRecommend = Input::get('isRecommend','');
        $pageSize = 10;
        $data = array();
//		$data['action_type'] = $action_type;
        $data['lineName'] =isset($_REQUEST['lineName'])?$_REQUEST['lineName']:"";
        $data['lineType'] =isset($_REQUEST['lineType'])?$_REQUEST['lineType']:"";
        $data['title'] = $title;
        $data['lineId'] = $lineId;
        $data['complete_type'] = $complete_type;
        $search = array('isPage'=>1,'pageSize'=>$pageSize,'pageNow'=>$pageIndex,'lineId'=>$lineId,'taskType'=>$complete_type,'taskName'=>$title,'taskId'=>$taskId,'isLoadPrize'=>"true",'platform'=>$platformType,'isLoadStep'=>'true','gid'=>$gid,'sortType'=>$sortType,'isLine'=>$isLine,'counterTimeBegin'=>'2000-01-01 00:00:00','createTimeBegin'=>$startTime,'createTimeEnd'=>$endTime,'isLoadStatistics'=>"true",'appType'=>$appType,'counterState'=>$counterState,'isRecommend'=>$isRecommend);
        $search['isSubTask'] = $lineId?"1":"0";
//         print_r(array_filter($search));exit;
//         $search = array_filter($search);
//         print_r($search);exit;
        $search['accessType'] = $accessType;
        $res = TaskLionService::task_list($search);
//        dd($res['result']);
        $search['platformType'] = $platformType;
//        print_r($res);exit;
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            foreach($result as $k=>$row){
                if(isset($result[$k]['typeCondition']))
                $result[$k]['typeCondition'] = json_decode($row['typeCondition'],true);
                $result[$k]['step_click'] = false;
                if($result[$k]['processingCount']>0){
                    $result[$k]['step_click'] = true;
                }
                if(isset($result[$k]['stepList'])){
                    foreach($result[$k]['stepList'] as $k1=>$v1){
//                         if($v1['stepApprovalNum']>0){
//                             $result[$k]['step_click'] = true;
//                         }
                        $result[$k]['lionStepId'] = $result[$k]['stepList'][0]['stepId'];
                        if(isset($result[$k]['stepList'][$k1]['stepCondition'])) {
                            $result[$k]['stepList'][$k1]['stepCondition'] = json_decode($result[$k]['stepList'][$k1]['stepCondition'],true);
                            foreach ($result[$k]['stepList'][$k1]['stepCondition'] as $item) {
                                if (isset($item['stepCondition'])) {
                                    if ($item['stepType']==4) {
//                                         $item['stepCondition'] = json_decode($item['stepCondition'],true);
                                        $result[$k]['lionmaxlevel'] = isset($item['stepCondition']['maxlevel'])?$item['stepCondition']['maxlevel']:'';
                                        $result[$k]['lionminlevel'] = isset($item['stepCondition']['minlevel'])?$item['stepCondition']['minlevel']:'';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }else{
            $total = 0;
            $result= array();
        }
//        print_r($result);
        $pager = Paginator::make(array(),$total,$pageSize);
        $search['title'] = $title;
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['conditions'] = array(''=>'选择类型','1'=>'试玩','2'=>'分享','3'=>'截图');
        $data['stepType'] = self::$stepArr;//,'3'=>'设定试玩游戏时间'
        $data['stepType_b'] = self::$stepArr_b;
        $data['sort'] = array('0'=>'降序','1'=>'升序');
        $data['au'] = array(''=>'全部任务','0'=>'普通任务','1'=>'连续任务');
        $data['app'] = array('3'=>'狮吼');
        $data['platformtype'] = array('I'=>'IOS','A'=>'Android');
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
        $sortType = Input::get('sortType','0');
        $startTime = Input::get('activityStartTime','');
        $endTime = Input::get('activityEndTime','');
        $appType = Input::get('appType',3);
        $platformType = Input::get('platformType','I');
        if ($platformType == 'I') {
            $gid = Input::get('gameId_I','');
        } else {
            $gid = Input::get('gameId_A','');
        }
        $pageSize = 10;
        $data = array();
        $search = array('isPage'=>1,'pageSize'=>$pageSize,'pageNow'=>$pageIndex,'isLoadCount'=>'true','taskType'=>"0",'taskName'=>$title,'isLoadPrize'=>"true",'platform'=>$platformType,'gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"1",'counterTimeBegin'=>'2000-01-01 00:00:00','createTimeBegin'=>$startTime,'createTimeEnd'=>$endTime,'isLoadStatistics'=>"true",'appType'=>$appType);
        $res = TaskLionService::task_list($search);
//        dd($search,$res);
        $search['platformType'] = $platformType;
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
        $data['stepType_b'] = self::$stepArr_b;
        $data['sort'] = array('0'=>'降序','1'=>'升序');
        $data['app'] = array('3'=>'狮吼');
        $data['platformtype'] = array('I'=>'IOS','A'=>'Android');
        return $this->display('task-children-list',$data);
    }

    /**
     * 子任务列表1.4
     */
    public function getSubTaskList()
    {
        $pageIndex = Input::get('page',1);
        $title = Input::get('title','');
        $sortType = Input::get('sortType','0');
        $startTime = Input::get('activityStartTime','');
        $endTime = Input::get('activityEndTime','');
        $appType = Input::get('appType',3);
        $platformType = Input::get('platformType','I');
        if ($platformType == 'I') {
            $gid = Input::get('gameId_I','');
        } else {
            $gid = Input::get('gameId_A','');
        }
        $pageSize = 10;
        $data = array();
        $search = array('isPage'=>1,'pageSize'=>$pageSize,'currenPage'=>$pageIndex,'isLoadCount'=>'true','taskType'=>"0",'taskName'=>$title,'isLoadPrize'=>"true",'platform'=>$platformType,'gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"1",'counterTimeBegin'=>'2000-01-01 00:00:00','createTimeBegin'=>$startTime,'createTimeEnd'=>$endTime,'isLoadStatistics'=>"true",'appType'=>$appType);
//        print_r($search);exit;
        $res = TaskLionService::sub_task_1_4($search);
        $search['platformType'] = $platformType;
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
        $data['stepType_b'] = self::$stepArr_b;
        $data['sort'] = array('0'=>'降序','1'=>'升序');
        $data['app'] = array('3'=>'狮吼');
        $data['platformtype'] = array('I'=>'IOS','A'=>'Android');
        return $this->display('sub-task-list',$data);
    }

    /**
     * 任务列表
     */
    public function getIframeChildrenTask()
    {
        $pageIndex = Input::get('page',1);
        $title = Input::get('taskName','');
        $gid = Input::get('gameId','');
        $sortType = Input::get('sortType','0');
        $shareType = Input::get('shareType','');
        $appType = Input::get('appType',0);
        $platformType = Input::get('platformType','I');
        $pageSize = 5;
        $data = array();
        $search = array('emptySubTask'=>1,'isPage'=>1,'pageSize'=>$pageSize,'pageNow'=>$pageIndex,'isLoadCount'=>'true','isRelateLine'=>"false",'taskType'=>"0",'taskName'=>$title,'isLoadPrize'=>"true",'platform'=>$platformType,'gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"1",'appType'=>$appType);
//        print_r($search);
        $res = TaskLionService::task_list($search);
        $search['platformType'] = $platformType;
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
        $data['platformType'] = $platformType;
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

    /**
     * 子任务1.4添加
     */
	public function getSubTaskAdd()
    {
        $data['stepType'] = self::$stepArr;//,'3'=>'设定试玩游戏时间'
        $data['uploadPicType'] = array('1'=>'等于','2'=>'小于','3'=>'大于');
        $data['atask']['platformType'] = 'I';
        $data['img_url'] = Config::get('app.img_url');
        $data['atask'] = [];
        $id = Input::get('id');
        $data['subTaskTagArr'] = [];
        $subTaskTagArr = TaskLionService::queryTaskTag(array('type'=>'subTaskTag'));
        if (isset($subTaskTagArr['errorCode']) && isset($subTaskTagArr['result'])) {
            $data['subTaskTagArr'] = $subTaskTagArr['result'];
        }

        if($id){
            $info = TaskLionService::getPHPSubTask(array('taskId'=>$id));
            if(isset($info['result']['subStyle']['showSize'])){
                $info['result']['subStyle']['showSize'] = explode(',', $info['result']['subStyle']['showSize']);
            }
            if(isset($info['result']['subStyle']['size'])){
                $info['result']['subStyle']['size'] = explode(',', $info['result']['subStyle']['size']);
            }

            if(isset($info['result']['stepList'][0]['stepCondition'])){
                $info['result']['stepList'][0]['stepCondition'] = str_replace('\n', '[huanhang]', $info['result']['stepList'][0]['stepCondition']);
                $info['result']['lionStepId'] = $info['result']['stepList'][0]['stepId'];
                $info['result']['stepList'][0]['stepCondition'] = json_decode($info['result']['stepList'][0]['stepCondition'],true);
                foreach($info['result']['stepList'][0]['stepCondition'] as $k1=>&$v1){
//                     if(isset($v1['stepCondition']))
//                     $v1['stepCondition'] = json_decode($v1['stepCondition'],true);
                    if(isset($v1['stepCondition']['content']))
                        $v1['stepCondition']['content'] = str_replace('[huanhang]', '\\\n', $v1['stepCondition']['content']);
                    if(isset($v1['stepCondition']['image'])){
                        $image_arr = explode(',', $v1['stepCondition']['image']);
                        $image_show_arr = array();
                        foreach ($image_arr as $item) {
                            $image_show_arr[] = Utility::getImageUrl($item);
                        }
                        $v1['stepCondition']['image_show'] = implode(',', $image_show_arr);
                    }

                    if($v1['stepType']==5) {
                        foreach ($v1['stepCondition']['recharge'] as &$item) {
                            if (isset($item['prizeIcon'])) {
                                $item['prizeIcon_show'] = Utility::getImageUrl($item['prizeIcon']);
                            }
                        }
                        $v1['stepCondition']['recharge'] = json_encode($v1['stepCondition']['recharge']);
                    }
                }
                $info['result']['stepList'] = $info['result']['stepList'][0]['stepCondition'];
            }

            $data['atask'] = $info['result'];
        }

        return $this->display('sub-task-add',$data);
    }

    public function postSubTaskAdd()
    {
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;

        $input['version'] = Input::get('version','2.1.1');
        $input['taskId'] = Input::get('taskId');
        $input['platformType'] = Input::get('platformType','I');
        $input['taskName'] = Input::get('taskName');
        $input['gid'] = Input::get('gid');
        $input['gname'] = Input::get('gname');

        if(Input::hasFile('task_icon')){
            $file = Input::file('task_icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['taskIcon'] = $dir . $new_filename . '.' . $mime;
        }else{
            $input['taskIcon'] = Input::get('task_img');
        }

        //subStyle
        $input['subStyle']['wordSize'] = Input::get('wordSize');
        $input['subStyle']['titleColor'] = Input::get('titleColor');
        $input['subStyle']['showSize'] = implode(',', Input::get('showSize'));
        if(Input::hasFile('picPath')){
            $file = Input::file('picPath');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['subStyle']['picPath'] = $dir . $new_filename . '.' . $mime;
        }else{
            $input['subStyle']['picPath'] = Input::get('picPathOld');
        }
        $input['subStyle']['size'] = implode(',', Input::get('size'));
        $input['subStyle']['taskTags'] =  Input::get('taskTags', '');
        if ($input['subStyle']['taskTags'] == '') {
            $input['subStyle']['taskTags'] = 'normal';
        }
        $input['subStyle']['taskTagName'] =  Input::get('taskTagName');

        //stepListStr
        $steps = json_decode("[".Input::get("stepListStr")."]",true);
        if($steps){
            $input['stepList'][0]['stepCondition'] = json_encode($steps);
        }

        //prizeList
        $input['prizeList'][0]['prizeKey'] = Input::get('prizeKey');
        $input['prizeList'][0]['shimaoKey'] = Input::get('shimaoKey');
        $input['prizeList'][0]['experKey'] = Input::get('experKey');

        $input['prizeList'][0]['prizeName'] = '';
        if ($input['prizeList'][0]['prizeKey']) {
            $input['prizeList'][0]['prizeName'] .= $input['prizeList'][0]['prizeKey'] . '狮牙,';
        }

        if ($input['prizeList'][0]['shimaoKey']) {
            $input['prizeList'][0]['prizeName'] .= $input['prizeList'][0]['shimaoKey'] . '狮毛,';
        }

        if ($input['prizeList'][0]['experKey']) {
            $input['prizeList'][0]['prizeName'] .= $input['prizeList'][0]['experKey'] . '经验,';
        }
        $input['prizeList'][0]['prizeName'] = rtrim($input['prizeList'][0]['prizeName'], ',');

        if(Input::hasFile('prizeIcon')){
            $file = Input::file('prizeIcon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['prizeList'][0]['prizeIcon'] = $dir . $new_filename . '.' . $mime;
        }else{
            $input['prizeList'][0]['prizeIcon'] = Input::get('prizeIconOld');
        }

        $id = $input['taskId'];
        if ($input['taskId']) {
            //stepList
            $input['stepList'][0]['opearteMode'] = "MODIFED";
            $input['stepList'][0]['stepId'] = Input::get('stepId');
            $input['stepList'][0]['taskId'] = $input['taskId'];

            //prizeList
            $input['prizeList'][0]['opearteMode'] = 'MODIFED';
            $input['prizeList'][0]['prizeId'] = Input::get('prizeId');
            $input['prizeList'][0]['taskId'] = $input['taskId'];

            $input['opearteMode'] = 'MODIFED';


        } else {
            unset($input['taskId']);
            $input['stepList'][0]['opearteMode'] = "NEW";
            $input['prizeList'][0]['opearteMode'] = 'NEW';

            $input['opearteMode'] = 'NEW';

        }

        if (Input::get('mutexTaskId')) {

            $mutex_info = TaskLionService::getPHPSubTask(array('taskId'=>Input::get('mutexTaskId')));
            if(isset($mutex_info['result']['mutexTaskId'])&&$mutex_info['result']['mutexTaskId']){
                return $this->back("添加失败,选定任务已互斥");
            }

            $input['mutexTaskId'] = Input::get('mutexTaskId');
            $res = TaskLionService::saveSubTask($input);

            $input2['taskId'] = $input['mutexTaskId'];
            $input2['mutexTaskId'] = $id;
            $res = TaskLionService::saveSubTask($input2);
        } else {

            $res = TaskLionService::saveSubTask($input);
        }

        if(isset($res['errorCode']) && $res['errorCode'] == 0){
            return $this->redirect('v4_task/task/task-children-list?&platformType='.$input['platformType'], '数据保存成功');
        }else{
            return $this->back($res['errorDescription']);
        }

    }

    public function getIframeSubTask()
    {
        $pageIndex = Input::get('page',1);
        $title = Input::get('taskName','');
        $gid = Input::get('gameId','');
        $sortType = Input::get('sortType','0');
        $shareType = Input::get('shareType','');
        $appType = Input::get('appType',3);
        $platformType = Input::get('platformType','I');
        $pageSize = 5;
        $data = array();
        $search = array('emptySubTask'=>1,'isPage'=>1,'pageSize'=>$pageSize,'currenPage'=>$pageIndex,'isLoadCount'=>'true','isRelateLine'=>"false",'taskType'=>"0",'taskName'=>$title,'isLoadPrize'=>"true",'platform'=>$platformType,'gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"1",'appType'=>$appType);
//        print_r($search);
        $res = TaskLionService::task_list($search);
        $search['platformType'] = $platformType;
        $search['shareType'] = $shareType;
//        print_r($res);
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            foreach($result as $k=>$row){
                if(isset($result[$k]['typeCondition']))
                    $result[$k]['typeCondition'] = json_decode($row['typeCondition'], true);
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
        $data['platformType'] = $platformType;
        $data['sort'] = array('0'=>'降序','1'=>'升序');
//
        $html = $this->html('iframe-sub-task',$data);
        return $this->json(array('html'=>$html));
    }

    public function getAddTaskLine()
    {
        $data['stepType_b'] = array('noButton'=>'纯图','bigButton'=>'单按钮居中','dualButton'=>'双按钮','video'=>'视频');
        $data['submitType'] = array('submit'=>'提交','screenshot'=>'截图','share'=>'分享','outUrl'=>'外链','ranking'=>'排行榜');
        $data['uploadPicType'] = array('1'=>'等于','2'=>'小于','3'=>'大于');
        $data['backpackType'] = array('1'=>'实物物品','2'=>'虚拟物品','3'=>'支付宝物品');
        $data['atask']['platformType'] = 'I';
        $data['img_url'] = Config::get('app.img_url');

        $id = Input::get('id');
        if ($id) {
            $res = TaskLionService::getPHPTaskLine(array('taskId'=>$id));
//            dd($res['result']['templateList'][0]['nativeTemplate']['buttons']);
            if (isset($res['errorCode']) && $res['errorCode'] == 0 && $res['result']) {
                $data['atask'] = $res['result'];
            }

        }

        $data['showTemplate']['templateName'] = 'bigPic';
        if (isset($data['atask']['showTemplate'])) {
            $data['showTemplate'] =  json_decode($data['atask']['showTemplate'], true);
        }

        //包名
        if (isset($data['atask']['typeCondition'])) {
            $data['atask']['typeCondition'] = json_decode($data['atask']['typeCondition'], true);
        }

        if (isset($data['atask']['templateList'])) {
            $data['templateListJson'] = json_encode($data['atask']['templateList']);
            foreach ($data['atask']['templateList'] as &$item) {
                $item['nativeTemplate']['size'] = explode(',', $item['nativeTemplate']['size']);
//                dd($item['nativeTemplate']['buttons']);
                foreach ($item['nativeTemplate']['buttons'] as &$button) {
                    $button['size'] = explode(',', $button['size']);
                }
            }
        }
//        dd($data['atask']['templateList'][0]['nativeTemplate']['buttons']);
//        print_r($data['templateListJson']);
//        dd();

        $data['downloadInfo'] = array();
        if(isset($data['atask']['downInfo'])){
            $data['downloadInfo'] = json_decode($data['atask']['downInfo'], true);
            $data['downloadInfo']['thirdCount'] = 0;
            if (isset($data['downloadInfo']['thirdVendorsList'])) {
                $data['downloadInfo']['thirdCount'] = count($data['downloadInfo']['thirdVendorsList']);
            }
            if (isset($data['downloadInfo']['prizeKey'])) {
                $data['downloadInfo']['prizeKey'] = explode(',', $data['downloadInfo']['prizeKey']);
            }
        }

        return $this->display('task-line', $data);
    }

    public function postAddTaskLine()
    {
        $id = Input::get('taskId','');
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;

        $input['platformType'] = Input::get('platformType','I');
        $platformType = $input['platformType'];
        $input['version'] = Input::get('version','2.1.1');
        $input['taskName'] = Input::get('taskName');
        $input['taskDesc'] = Input::get('taskDesc');
        $input['gid'] = Input::get('game_id');
        $input['gname'] = Input::get('gname');
        $input['preTaskId'] = Input::get('preTaskId');

        if(Input::hasFile('task_icon')){
            $file = Input::file('task_icon');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $input['taskIcon'] = $dir . $new_filename . '.' . $mime;
        }else{
            $input['taskIcon'] = Input::get('task_img');
        }

        $input['sortValue'] = (int)Input::get('sort',0);
        //展示模板
        $showTemplate = [];
        $showTemplate['templateName'] = Input::get('templateName');
        $picUrlsOld = Input::get('picUrlsOld', array());
        $tempPicUrls = [];
        foreach ($picUrlsOld as $k => $v) {
            if (Input::file('picUrls')[$k]) {
                $file = Input::file('picUrls')[$k];
                $new_filename = date('YmdHis') . str_random(4);
                $mime = $file->getClientOriginalExtension();
                $file->move($path,$new_filename . '.' . $mime );
                $tempPicUrls[] = $dir . $new_filename . '.' . $mime;
            } else {
                $tempPicUrls[] = $v;
            }
        }
        $showTemplate['picUrls'] = $tempPicUrls;
        $input['showTemplate'] = json_encode($showTemplate, JSON_UNESCAPED_SLASHES);
        if ($input['showTemplate']) {
            $input['showTemplate'] = str_replace('\\', "", $input['showTemplate']);
        }

        $input['startTime'] = Input::get('startTime');
        $input['endTime'] = Input::get('endTime');
        $input['templateList'] = json_decode("[".Input::get("stepListStr_b")."]", true);

        $downloadInfo['taskShowSwitch'] = Input::get('taskShowSwitch','0');
        if(Input::hasFile('backgroundPic')){
            $file = Input::file('backgroundPic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $downloadInfo['backgroundPic'] = $dir . $new_filename . '.' . $mime;
        }else{
            $downloadInfo['backgroundPic'] = Input::get('backgroundPic_img');
        }

        //安卓包名
        $arr_typeCondition = array('gameName'=>$input['gname']);
        $arr_typeCondition['gamePackageName'] = Input::get('game_package_name');
        if($arr_typeCondition){
            $input['typeCondition'] = json_encode($arr_typeCondition);
        }

        $downloadInfo['advid'] = Input::get('advid');
        $downloadInfo['gid'] = $input['gid'];
        $downloadInfo['linkInfo']['linkType'] = Input::get('linkType');
        $downloadInfo['linkInfo']['linkValue'] = Input::get('owmUrl');
        $thirdcount = Input::get('third-count',0);
        $thirdinput = Input::only('thirdUrl','mac','idfa','openudid','os','plat','callback','firstRecharge','firstCreateRole','thirdid','gameDownloadId');
        for ($i=0;$i<$thirdcount;$i++) {
//             $thirditem['id'] =  $thirdinput['thirdid'][$i];
            $thirditem['gameDownloadId'] =  $thirdinput['gameDownloadId'][$i];
            $thirditem['thirdUrl'] =  $thirdinput['thirdUrl'][$i];
            $thirditem['mac'] =  $thirdinput['mac'][$i];
            $thirditem['idfa'] =  $thirdinput['idfa'][$i];
            $thirditem['openudid'] =  $thirdinput['openudid'][$i];
            $thirditem['os'] =  $thirdinput['os'][$i];
            $thirditem['plat'] =  $thirdinput['plat'][$i];
            $thirditem['callback'] =  $thirdinput['callback'][$i];
            $thirditem['firstRecharge'] =  $thirdinput['firstRecharge'][$i];
            $thirditem['firstCreateRole'] =  $thirdinput['firstCreateRole'][$i];
            $downloadInfo['thirdVendorsList'][] = $thirditem;
        }

        $input['downInfo'] = json_encode($downloadInfo);

        if ($id) {
            $input['taskId'] = $id;
            $input['opearteMode'] = 'MODIFED';
        } else {
            $input['opearteMode'] = 'NEW';
        }

        $res = TaskLionService::saveTaskLine($input);
        print_r(json_encode($input));
        dd($res);

        if(isset($res['errorCode']) && $res['errorCode'] == 0){

            return $this->redirect('v4_task/task/task-list?platformType='.$platformType,'数据保存成功');
        }else{
            return $this->back($res['errorDescription']);
        }

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
        $data['img_url'] = Config::get('app.img_url');
        $data['stepType'] = self::$stepArr;//,'3'=>'设定试玩游戏时间'
        $data['stepType_b'] = self::$stepArr_b;
        $data['submitType'] = self::$submitTypeArr;
        $data['uploadPicType'] = array('1'=>'等于','2'=>'小于','3'=>'大于');
        $data['backpackType'] = array('1'=>'实物物品','2'=>'虚拟物品','3'=>'支付宝物品');
        $data['appType'] = 0;
        $data['accessType'] = 0;
        $data['platformType'] = 'I';
		if($id){
			$info = TaskLionService::task_get(array('taskId'=>$id,'appname'=>'glwzry'));
//            dd($info['result']);
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
            
            $data['platformType'] = $info['result']['platformType'];
            $data['isLimitin'] = 0;
            $data['autoQuotaOff'] = 0;
            if ((isset($info['result']['limitNumbers']) && $info['result']['limitNumbers'] >0) || (isset($info['result']['limitHours']) && $info['result']['limitHours'] >0)) {
                $data['isLimitin'] = 1;
                if (isset($info['result']['autoQuotaInfo'])) {
                    $data['autoQuotaOff'] = 1;
                    $autoQuotaInfo_arr = json_decode($info['result']['autoQuotaInfo'],true);
                    if ($autoQuotaInfo_arr['quotaStartTime']) {
                        $info['result']['quotaStartTime'] = $autoQuotaInfo_arr['quotaStartTime'];
                    }
                    if ($autoQuotaInfo_arr['quotaEndTime']) {
                        $info['result']['quotaEndTime'] = $autoQuotaInfo_arr['quotaEndTime'];
                    }
                    if ($autoQuotaInfo_arr['intervalTime']) {
                        $info['result']['intervalTime'] = $autoQuotaInfo_arr['intervalTime'];
                    }
                    if ($autoQuotaInfo_arr['quotaNumber']) {
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
            if(isset($info['result']['stepList'][0]['stepCondition'])){
                $info['result']['stepList'][0]['stepCondition'] = str_replace('\n', '[huanhang]', $info['result']['stepList'][0]['stepCondition']);
                $info['result']['lionStepId'] = $info['result']['stepList'][0]['stepId'];
                $info['result']['stepList'][0]['stepCondition'] = json_decode($info['result']['stepList'][0]['stepCondition'],true);
                foreach($info['result']['stepList'][0]['stepCondition'] as $k1=>&$v1){
//                     if(isset($v1['stepCondition']))
//                     $v1['stepCondition'] = json_decode($v1['stepCondition'],true);
                    if(isset($v1['stepCondition']['content']))
                    $v1['stepCondition']['content'] = str_replace('[huanhang]', '\\\n', $v1['stepCondition']['content']);
                    if(isset($v1['stepCondition']['image'])){
                        $image_arr = explode(',', $v1['stepCondition']['image']);
                        $image_show_arr = array();
                        foreach ($image_arr as $item) {
                            $image_show_arr[] = Utility::getImageUrl($item);
                        }
                        $v1['stepCondition']['image_show'] = implode(',', $image_show_arr);
                    }

                    if($v1['stepType']==5) {
                        foreach ($v1['stepCondition']['recharge'] as &$item) {
                            if (isset($item['prizeIcon'])) {
                                $item['prizeIcon_show'] = Utility::getImageUrl($item['prizeIcon']);
                            }
                        }
                        $v1['stepCondition']['recharge'] = json_encode($v1['stepCondition']['recharge']);
                    }
                }
                $info['result']['stepList'] = $info['result']['stepList'][0]['stepCondition'];
            }
//             print_r($info['result']['stepList']);exit;
            if($data['selType'] == "2"){
                $search = array('taskType'=>"0",'platform'=>$data['platformType'],'lineId'=>$id,'isSubTask'=>"1");
                $res_children = TaskLionService::task_list($search);
                if(!$res_children['errorCode']&&$res_children['result']){
                    $data['task_children'] = $res_children['result'];
                }
            }
            if (isset($info['result']['limitHours'])) {
                $info['result']['limitHours'] = $info['result']['limitHours']/60/60;
            }
            if (isset($info['result']['limitNumbers']) && $info['result']['limitNumbers']==0) {
                $info['result']['limitNumbers'] = '';
            }
            if (isset($info['result']['limitHours']) && $info['result']['limitHours']==0) {
                $info['result']['limitHours'] = '';
            }

//            print_r($res_children);
//            print_r($info['result']);
            $info['result']['stepList_b'] = array();
            $data_b = TaskLionService::get_task_template(array('taskId'=>$id));
            if(!$data_b['errorCode'] && isset($data_b['result'])){
                $data_template = json_decode($data_b['result']['template'],true);
                $info['result']['title_b'] = $data_template['title'];
                $info['result']['template_id'] = $data_b['result']['id'];
                if (is_array($data_template['nativeTemplates'])) {
                    foreach ($data_template['nativeTemplates'] as &$item) {
                        if (isset($item['size'])) {
                            $item['size'] = explode(',', $item['size']);
                        }
                        if (isset($item['buttons'])) {
                            foreach ($item['buttons'] as &$item_b) {
                                if (isset($item_b['size'])) {
                                    $item_b['size'] = explode(',', $item_b['size']);
                                }
                            }
                        }
                        if (isset($item['videoInfo'])) {
                            if (isset($item['videoInfo']['param']['size'])) {
                                $item['videoInfo']['param']['size'] = explode(',', $item['videoInfo']['param']['size']);
                            }
                        }
                    }
                }
                $info['result']['stepList_b'] = $data_template['nativeTemplates'];
            }
            
			$data['atask'] = $info['result'];
			$data['appType'] = isset($info['result']['appType'])?$info['result']['appType']:0;
			$data['platformType'] = isset($info['result']['platformType'])?$info['result']['platformType']:'I';
			$data['accessType'] = isset($info['result']['accessType'])?$info['result']['accessType']:0;
			$data['shareType'] = isset($info['result']['shareType'])?$info['result']['shareType']:0;
		}else{
			$data['atask'] = array('is_show'=>1,'reward_type'=>'money');
		}
        if(!isset($data['atask']['prizeList'])){
            if($id){
                $data['atask']['prizeList'][0]['prizeType'] = -1;
            } else {
                $data['atask']['prizeList'][0]['prizeType'] = 8;
            }
        }
        $data['downloadInfo'] = array();
        if(isset($data['atask']['downInfo'])){
            $data['downloadInfo'] = json_decode($data['atask']['downInfo'],true);
            $data['downloadInfo']['thirdCount'] = 0;
            if (isset($data['downloadInfo']['thirdVendorsList'])) {
                $data['downloadInfo']['thirdCount'] = count($data['downloadInfo']['thirdVendorsList']);
            }

            if (isset($data['downloadInfo']['prizeKey'])) {
                $data['downloadInfo']['prizeKey'] = explode(',', $data['downloadInfo']['prizeKey']);
            }
        }
		return $this->display('task-add',$data);
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
            $input['isSubTask'] = "false";
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
        $input['platformType'] = Input::get('platformType','I');
        $platformType = $input['platformType'];
        $input['taskName'] = Input::get('taskName');
        $input['version'] = Input::get('version');
        $input['gid'] = Input::get('game_id');
        $input['startTime'] = Input::get('start_time') ;
        $input['endTime'] = Input::get('end_time');
        $input['sortValue'] = (int)Input::get('sort',0);
		$input['linkType'] = Input::get('selLinkType');
        $input['linkValue'] = Input::get('linkValue');
        $input['subTaskIds'] = substr(Input::get('ids'),0,-1);
        $input['gname'] = Input::get('game_name');
        $input['attendRate'] = Input::get('attendRate');
        $input['limitNumbers'] = Input::get('limitNumbers');
        $input['limitHours'] = Input::get('limitHours');
        $input['isRecommend'] = Input::get('isRecommend') ? true : false ;
        $input['appType'] = 3;
        $input['accessType'] = Input::get('accessType',0);
        $input['isLimit'] = Input::get('isLimit');
        $input['shareType'] = (int)Input::get('shareType',0);
        $input['preTaskId'] = Input::get('preTaskId');


        $downloadInfo = array();
        // todo:测试没问题就删掉
//        $downloadInfo['activeBtnName'] = Input::get('activeBtnName','前去下载');
        $downloadInfo['taskDisInterTime'] = Input::get('taskDisInterTime','0');
        $downloadInfo['downPrizeSwitch'] = Input::get('downPrizeSwitch','0');
        $downloadInfo['taskShowSwitch'] = Input::get('taskShowSwitch','0');
        if (Input::get('downexper') || Input::get('downprize')) {
            $downloadInfo['prizeType'] = "8";
            $down_prize = array();
            $down_prize[] = Input::get('downexper')?Input::get('downexper','0'):'0';
            $down_prize[] = Input::get('downprize')?Input::get('downprize','0'):'0';
            $downloadInfo['prizeKey'] = implode(',', $down_prize);
        }
        if(Input::hasFile('backgroundPic')){
            $file = Input::file('backgroundPic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file->move($path,$new_filename . '.' . $mime );
            $downloadInfo['backgroundPic'] = $dir . $new_filename . '.' . $mime;
        }else{
            $downloadInfo['backgroundPic'] = Input::get('backgroundPic_img');
        }
        $downloadInfo['advid'] = Input::get('advid');
        $downloadInfo['gid'] = Input::get('game_id');
        $downloadInfo['linkInfo']['linkType'] = Input::get('linkType');
        $downloadInfo['linkInfo']['linkValue'] = Input::get('owmUrl');
        $downloadInfo['thirdVendorsList'] = array();
        $thirdcount = Input::get('third-count',0);
        $thirdinput = Input::only('thirdUrl','mac','idfa','openudid','os','plat','callback','firstRecharge','firstCreateRole','thirdid','gameDownloadId');
        for ($i=0;$i<$thirdcount;$i++) {
//             $thirditem['id'] =  $thirdinput['thirdid'][$i];
            $thirditem['gameDownloadId'] =  $thirdinput['gameDownloadId'][$i];
            $thirditem['thirdUrl'] =  $thirdinput['thirdUrl'][$i];
            $thirditem['mac'] =  $thirdinput['mac'][$i];
            $thirditem['idfa'] =  $thirdinput['idfa'][$i];
            $thirditem['openudid'] =  $thirdinput['openudid'][$i];
            $thirditem['os'] =  $thirdinput['os'][$i];
            $thirditem['plat'] =  $thirdinput['plat'][$i];
            $thirditem['callback'] =  $thirdinput['callback'][$i];
            $thirditem['firstRecharge'] =  $thirdinput['firstRecharge'][$i];
            $thirditem['firstCreateRole'] =  $thirdinput['firstCreateRole'][$i];
            $downloadInfo['thirdVendorsList'][] = $thirditem;
        }
        $input['downInfo'] = json_encode($downloadInfo);
        
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
            $input['sortValue'] = "2000";
        }
        if (Input::get('isLimitin')) {
            if ($lineType!="1") {
                if (Input::get('limitNumbers') =="" || Input::get('limitHours') =="") {
                    return $this->back("限定名额和时间必填");
                }
            }
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
        $arr_typeCondition['gamePackageName'] = Input::get('game_package_name');
        if($arr_typeCondition){
            $input['typeCondition'] = json_encode($arr_typeCondition);
        }

        //处理奖励内容
        $prize_list = array();
        $prize_list['prizeIcon'] = $prizeIcon;
        $prize_list['prizeKey']  = Input::get('prizeKey');
        $prize_list['experKey']  = Input::get('experKey');
        $prize_list['shimaoKey']  = Input::get('shimaoKey');
        if($lineType!="2"){
        if(Input::get('selPrizeType')=="0"){
            $prize_list['prizeType'] = "0";
            $prize_list['prizeName'] = Input::get('prizeKey')."游币";
        }elseif(Input::get('selPrizeType')=="3"){
            $prize_list['prizeType'] = "3";
            $prize_list['prizeName'] = Input::get('prizeKey')."钻石";
            if (Input::get('prizeKey') <= 0 && $lineType!="2") {
                return $this->back("钻石数量必须大于0");
            }
        }elseif(Input::get('selPrizeType')=="5"){
            $prize_list['prizeType'] = "5";
            $prize_list['prizeName'] = Input::get('experKey')."经验";
            if (Input::get('experKey') <= 0) {
                return $this->back("经验数量必须大于0");
            }
        }elseif(Input::get('selPrizeType')=="6"){
            $prize_list['prizeType'] = "6";
            $prize_list['prizeKey']  = Input::get('prizeKeySY');
            $prize_list['prizeName'] = Input::get('prizeKeySY')."狮牙,";
            if (Input::get('prizeKeySY') <= 0) {
                return $this->back("狮牙数量必须大于0");
            }
        }elseif(Input::get('selPrizeType')=="7"){
            $prize_list['prizeType'] = "7";
            $prize_list['prizeKey']  = Input::get('prizeKeySY');
            $prize_list['prizeName'] = Input::get('prizeKeySY')."狮牙,".Input::get('experKey')."经验";
            if (Input::get('experKey') <= 0 || Input::get('prizeKeySY') <= 0) {
                return $this->back("经验和狮牙数量都必须大于0");
            }
        }elseif(Input::get('selPrizeType')=="8"){
            $prize_list['prizeType'] = "8";
            $prize_list['prizeName'] = '';
            $prize_list['prizeKey'] = Input::get('prizeKeySY');
            if (Input::get('prizeKeySY') > 0) {
                $prize_list['prizeName'] .= Input::get('prizeKeySY')."狮牙,";
            }
            if (Input::get('shimaoKey') > 0) {
                $prize_list['prizeName'] .= Input::get('shimaoKey')."狮毛,";
            }
            if (Input::get('experKey') > 0) {
                $prize_list['prizeName'] .= Input::get('experKey')."经验,";
            }
            if ($prizeIcon) {
                $prize_list['prizeName'] .= "勋章,";
            }
            if (Input::get('prizeKeySY') <= 0 && Input::get('shimaoKey') <= 0 && Input::get('experKey') <= 0 && !$prizeIcon) {
                return $this->back("至少要设置一项奖励");
            }
            $prize_list['prizeName'] = substr($prize_list['prizeName'],0,strlen($prize_list['prizeName'])-1);
            
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
            $prize_list['experKey'] = 0;
        }
        } else {
            $prize_list['prizeType'] = "-1";
            $prize_list['prizeName'] = "无奖励";
            $prize_list['prizeKey'] = 0;
            $prize_list['experKey'] = 0;
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
        if($lineType!="2"){
            //处理步骤json
            $steps = json_decode("[".Input::get("stepListStr")."]",true);
            //                 foreach($steps as $k=>$v){
            //                     if($v['stepType']=="4"){
            //                         $img_arr = array();
            //                         for ($i=1;$i<=$steps[$k]['stepCondition']['pic_count'];$i++) {
            //                             $file = array_shift($prize_pic);//获取数组第一个元素
            //                             $new_filename = date('YmdHis') . str_random(4);
            //                             $mime = $file->getClientOriginalExtension();
            //                             $file->move($path,$new_filename . '.' . $mime );
            //                             $img_arr[] = $dir . $new_filename . '.' . $mime;
            //                         }
            //                         $steps[$k]['stepCondition']['image'] = implode(',', $img_arr);
            //                         unset($steps[$k]['stepCondition']['pic_count']);
            //                     }
            //                     if($v['stepType']=="5"){
            //                         foreach ($steps[$k]['stepCondition']['recharge'] as &$recharge) {
            //                             if (strpos($recharge['prizeIcon'], 'fileupload-exists')) {
            //                                 $file = array_shift($prize_pic);//获取数组第一个元素
            //                                 $new_filename = date('YmdHis') . str_random(4);
            //                                 $mime = $file->getClientOriginalExtension();
            //                                 $file->move($path,$new_filename . '.' . $mime );
            //                                 $recharge['prizeIcon'] = $dir . $new_filename . '.' . $mime;
            //                             } else {
            //                                 $recharge['prizeIcon'] = '';
            //                             }
            //                         }
            //                     }
            //                     if($steps[$k]['stepCondition']){
            //                         $steps[$k]['stepCondition'] = json_encode($steps[$k]['stepCondition']);
            //                     }else{
            //                         $steps[$k]['stepCondition'] = "{}";
            //                     }
            //                 }
            if($steps){
                $input['stepListStr'] = json_encode($steps);
            }
//             print_r($input['stepListStr']);exit;

            $steps_b = array();
            $steps_b['title'] = Input::get("title_b","");
            $steps_b_tmp = json_decode("[".Input::get("stepListStr_b")."]",true);
            $steps_b['nativeTemplates'] = $steps_b_tmp;
            
            $can_submit = true;
            foreach ($steps_b_tmp as $itme) {
                if ($itme['templateName']<>'noButton' && $itme['templateName']<>'video') {
                    if (isset($itme['buttons'])) {
                        foreach ($itme['buttons'] as $item_button) {
                            if ($item_button['submitType'] == 'submit') {
                                $can_submit = false;
                            }
                        }
                    }
                }
            }
            
            if (!$can_submit && !$steps) {
                return $this->back("图文结构不能为空");
            }
        }
//         print_r("[".Input::get("stepListStr_b")."]");exit;
        //编辑时
        if($id){
            $input['lionStepId'] = Input::get('lionStepId');
            if (Input::get('mutexTaskId')) {
                
                $mutex_info = TaskLionService::task_get(array('taskId'=>Input::get('mutexTaskId')));
                if(isset($mutex_info['result']['mutexTaskId'])&&$mutex_info['result']['mutexTaskId']){
                    return $this->back("添加失败,选定任务已互斥");
                }
                
                $input['taskId'] = $id;
                $input['mutexTaskId'] = Input::get('mutexTaskId');
                $TaskId_1 = $input['mutexTaskId'];
                $res = TaskLionService::task_edit($input);
                $TaskId_2 = $id;
                unset($input);
                $input['taskId'] = $TaskId_1;
                $input['mutexTaskId'] = $TaskId_2;
                $res = TaskLionService::task_edit($input);
            } else {
            
                $input['taskId'] = $id;
                $res = TaskLionService::task_edit($input);
            
            }

            if (isset($steps_b)) {
                $data_b = array();
                $template_id = Input::get('template_id');
                if ($template_id) {
                    $data_b['opearteMode'] = 'MODIFED';
                    $data_b['id'] = $template_id;
                } else {
                    $data_b['opearteMode'] = 'NEW';
                }
                $data_b['taskId'] = $id;
                $data_b['template'] = json_encode($steps_b);
                $res = TaskLionService::task_template_save($data_b);
            }
            
        }else{
            $input['offline'] = 1;
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
                $res = TaskLionService::task_add($input);
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
                $res = TaskLionService::task_add($input);
                $TaskId_2 = $res['result'];
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
                    $res = TaskLionService::task_add($input);
                    $TaskId_2 = $res['result'];
                    unset($input);
                    $input['taskId'] = $TaskId_1;
                    $input['mutexTaskId'] = $TaskId_2;
                    $res = TaskV3Service::task_edit($input);
                } else {
                    
                    $res = TaskLionService::task_add($input);
                    
                }

                
            }

            if (isset($steps_b)) {
                $data_b = array();
                $data_b['opearteMode'] = 'NEW';
                $data_b['taskId'] = $res['result'];
                $data_b['template'] = json_encode($steps_b);
                $res = TaskLionService::task_template_save($data_b);
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
//                     return $this->redirect('v4_task/task/task-children-list','数据保存成功,缓存失败');
//                 }
                return $this->redirect('v4_task/task/task-children-list?platformType='.$platformType,'数据保存成功');
            }
//             if(!empty($input['gid'])){
//                 $data = CacheService::cache_add_type_count_iostask($input['gid'],'game_task');
//             }
//             if(!isset($data['errorCode'])||$data['errorCode']!=0){
//                 return $this->redirect('v4_task/task/task-list?lineId='.$lineId,'数据保存成功,缓存失败');
//             }
			return $this->redirect('v4_task/task/task-list?lineId='.$lineId.'&platformType='.$platformType,'数据保存成功');
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
        if ($isline == 1) {
            $isline = 'true';
        } else {
            $isline = 'false';
        }
        $isHasPush = $_REQUEST['isHasPush'];
        $data = array('taskId'=>$id,'closeType'=>$type);
        if($type == "3"){
            $res = TaskLionService::task_edit(array('taskId'=>$id,'sortValue'=>"2000",'appType'=>3,'isLine'=>$isline));
        }elseif($type == "4"){
            $res = TaskLionService::task_edit(array('taskId'=>$id,'sortValue'=>"50",'appType'=>3,'isLine'=>$isline));
        }else{
            $data['isHasPush'] = '0';
            if ($type == "0" && !$isHasPush) {
                $data['isHasPush'] = '1';
            }
//             print_r($data);exit;
            $res = TaskLionService::task_close($data);
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
        $isline = $_REQUEST['isline'] ? true : false ;

        $data = array('taskId'=>$id,'isRecommend'=>$type,'isLine'=>$isline);
        $res = TaskLionService::task_edit($data);

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
        $res = TaskLionService::task_edit($data);
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
        if(empty($id)) return $this->redirect('v4_task/task/task-new-list')->with('global_tips','参数丢失');
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
        $res = TaskLionService::task_del($data);
        if(!$res['errorCode']&&$res['result']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription'],'data'=>""));
        }
    }
    
    public function postTaskNewDel()
    {
        $id = $_REQUEST['id'];
        if(empty($id)) return $this->redirect('v4_task/task/task-new-list')->with('global_tips','参数丢失');
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
//         print_r($search);die;
        $result = TaskLionService::query_user_step_info_list_lion($search);
//        print_r($result);die;

        if(!$result['errorCode']&&$result['result']){
            $user_arr = array();
            $re = array();
            $pic_count = 1;
            foreach($result['result'] as $k=>$v){
                if($v['picUrl']){
                    $result['result'][$k]['picUrl'] = explode(',',$v['picUrl']);
                    $this_size =count($result['result'][$k]['picUrl']);
                    if($this_size>$pic_count){
                        $pic_count = $this_size;
                    }
                }
                if(isset($v['prizeContent'])){
                    $re[$v['uid']] = explode(',',$v['prizeContent']);
                }
                $user_arr[$v['uid']]['uid'] = $v['uid'];
                $userinfo_shihou = TaskLionService::get_lion_user(array('user_id'=>$v['uid']));
                if ($userinfo_shihou['error'] == 0) {
                    $nick_name = $userinfo_shihou['data']['user']['nick_name'];
                } else {
                    $nick_name = $userinfo_shihou['msg'];
                }
                $user_arr[$v['uid']]['nick_name'] = $nick_name;
            }
            
            $data['users'] = $user_arr;
            $data['re'] = $re;
            $data['pic_count'] = $pic_count;
            $data['datalist'] = $result['result'];
            //var_dump($data['datalist']);die;
            foreach($data['datalist'] as $k => $v){
                if(isset($v['approvalContent'])){
                    if(is_null(json_decode($v['approvalContent']))){
                        $data['datalist'][$k]['autorContent'] = $v['approvalContent'];
                    }else {
                        $data['datalist'][$k]['approvalContent'] = json_decode($v['approvalContent'], true);
                        foreach ($data['datalist'][$k]['approvalContent'] as $k1 => $v2) {
                            if (is_array($v2)) {
                                $title = $v2['title'];unset($v2['title']);
                                $data['datalist'][$k]['approvalContent'][$title] = array_pop($v2);
                                unset($data['datalist'][$k]['approvalContent'][$k1]);
                            } else {
                                $data['datalist'][$k]['approvalContent'][] = $data['datalist'][$k]['approvalContent'][$k1];
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
        $data['appType'] = Input::get('appType');
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
        $result = TaskLionService::query_user_step_info_count_lion(array('taskId'=>$data['taskId'],'stepId'=>$data['stepId'],'stepStatus'=>"-1"));
        
        if(!$result['errorCode']&&$result['result']) {
            $data['totle'] = $result['result'];
        }
//        var_dump($data);die;
        return $this->display('audit-picture-list',$data);
    }

    public function getApprovalOne()
    {
        $taskId = Input::get('taskId');
        $taskName = Input::get('taskName');
        $appType = Input::get('appType');
        $stepId = Input::get('stepId');

        $step_content = Input::get('step_content');
        $step_img = Input::get('step_img');
        $data = array();
//        print_r($search);
        $result = TaskLionService::query_user_step_info_list(array('taskId'=>$taskId,'stepId'=>$stepId,'pageSize'=>"1",'stepStatus'=>'-1','sortType'=>"0"));
//        print_r($result);

        $info = TaskLionService::task_get(array('taskId'=>$taskId,'appname'=>'glwzry'));
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
                        $result_re = TaskLionService::query_user_step_info_list($search_re);
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
        $data['appType'] = $appType;
        $data['stepType'] = Input::get('stepType');
        $data['appleId'] = Input::get('appleId');
        $data['appPasswordKey'] = Input::get('appPasswordKey');
        $data['title'] = Input::get('title');
        $result = TaskLionService::query_user_step_info_count(array('stepId'=>$data['stepId'],'stepStatus'=>"true"));
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
        $taskName = Input::get('taskName');
        $appType = Input::get('appType');
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
            $res1 = TaskLionService::approval_step_screenshot_lion($data);unset($data['passIds']);unset($data['passUids']);unset($data['passDeviceIds']);
            if(isset($res1['errorCode'])&&$res1['errorCode']){
                $success = "false";$mess = $res1['errorDescription'];
            }
        }
        if($notPassIds){
            $data['againIds'] = substr($notPassIds,0,-1);
            $data['checkFailedDesc'] = Input::get('checkFailedDesc');
            $data['notPassUids'] = $notPassUids;
            $data['notPassDeviceIds'] = $notPassDeviceIds;
            $res2 = TaskLionService::approval_step_screenshot_lion($data);unset($data['notPassIds']);unset($data['notPassUids']);unset($data['checkFailedDesc']);unset($data['notPassDeviceIds']);
            if(isset($res2['errorCode'])&&$res2['errorCode']){
                $success = "false";$mess = $res2['errorDescription'];
            }
        }
//        $res = TaskLionService::approval_step_screenshot($data);
//        print_r(Input::get());
//        echo $mess;die;
        if($success == "true"){
            
        
                if($passIds){
                    $push_msg = '您已完成“'.$taskName.'”,请至“我的任务”领取奖励。';
                    $PassUids_arr = explode(',',$PassUids);
                    foreach ($PassUids_arr as $id){
                        $data_s = array(
                            'user_id' => $id,
                            'push_msg' => $push_msg,
                            'push_icon' => '',
                        );
                        ksort($data_s);
                        $array_sign = array();
                        foreach($data_s as $key => $value) {
                            $array_sign[] = "{$key}={$value}";
                        }
                        $str = implode('&', $array_sign).'&key='.Config::get("app.lion_token_key");
                        $token = md5($str);
                        $data_s['token'] = $token;
                        $res = TopicService::system_send_lion($data_s);
                    }
                }
                if($notPassIds){
                    $push_msg = '您在“'.$taskName.'”上传内容不符合要求，请重新上传。';
                    $notPassUids_arr = explode(',',$notPassUids);
                    foreach ($notPassUids_arr as $id){
                        $data_s = array(
                            'user_id' => $id,
                            'push_msg' => $push_msg,
                            'push_icon' => '',
                        );
                        ksort($data_s);
                        $array_sign = array();
                        foreach($data_s as $key => $value) {
                            $array_sign[] = "{$key}={$value}";
                        }
                        $str = implode('&', $array_sign).'&key='.Config::get("app.lion_token_key");
                        $token = md5($str);
                        $data_s['token'] = $token;
                        $res = TopicService::system_send_lion($data_s);
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
        $res = TaskLionService::approval_step_all_screenshot($data);
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
            $relative = Input::get('relative',0);
//             $icon = Utility::getImageUrl($icon);
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
                $res = TaskLionService::update_step_base_info($input);
            }else{
                $res = TaskLionService::insert_step($input);
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
            $res = TaskLionService::delete_step($input);
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
        $res = TaskLionService::task_rule(array());
        if(!$res['errorCode']&&$res['result']){
            $data['data'] = $res['result'];
        }
        return $this->display('task-rule',$data);
    }

    public function postTaskRuleUpdate () {
        $data['taskRule'] = Input::get('content');
        $data['taskIdfaRule'] = Input::get('content2');
        if ($data['taskRule'] || $data['taskIdfaRule']) {
            $res = TaskLionService::update_task_rule($data);
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
        $search = array('taskId'=>$taskId,'pageSize'=>$pageSize,'pageNow'=>$pageIndex,'isLoadCount'=>'true','taskName'=>$title,'isLoadPrize'=>"true",'platformType'=>'I','isLoadStep'=>'true','isLoadStatistics'=>"true",'appType'=>$appType,'offline'=>$offline);
        switch ($taskType) {
            case '1':
                $search['isLine'] = "false";
                $search['isSubTask'] = "false";
                break;
            default:
                $search['isLine'] = "true";
                $search['isSubTask'] = "false";
        }
        $res = TaskLionService::task_list(array_filter($search));
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
        $data['app'] = array('0'=>'不区分app','1'=>'ios4.0app','2'=>'攻略app','3'=>'狮吼');
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
        $search = array('taskId'=>$taskId,'lineId'=>$lineId,'pageSize'=>$pageSize,'pageNow'=>$pageIndex,'isLoadCount'=>'true','taskName'=>$title,'isLoadPrize'=>"true",'isSubTask'=>"true",'platformType'=>'I','isLoadStep'=>'true','isLoadStatistics'=>"true",'offline'=>$offline);
        $res = TaskLionService::task_list(array_filter($search));
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
        $search = array('pageSize'=>$pageSize,'pageNow'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$name,'stepStatus'=>$stepStatus,'add_user'=>$add_user,'isIssue'=>$isIssue);
        $res = TaskLionService::task_checked(array_filter($search));
        if(!$res['errorCode']&&$res['result']){
            
            if($add_user=='true'){
                $search_all = array('pageSize'=>100000,'pageNow'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$name,'stepStatus'=>1,'add_user'=>$add_user,'isIssue'=>$isIssue);
                $res_all = TaskLionService::task_checked(array_filter($search_all));
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
                $result[$k]['userinfo']['uid'] = $row['uid'];
                
                if(isset($row['approvalContent'])&&!empty($row['approvalContent'])){
                    if(is_null(json_decode($row['approvalContent']))){
                        $result[$k]['autorContent'] = $row['approvalContent'];
                    }else{
                        $result[$k]['approvalContent'] = json_decode($row['approvalContent'],true);
                        $arr = self::$approvalArr;
                        foreach($result[$k]['approvalContent'] as $j => $v){
                            if (is_array($v)) {
                                $title = $v['title'];unset($v['title']);
                                $result[$k]['approvalContent'][$title] = array_pop($v);
                                unset($result[$k]['approvalContent'][$j]);
                            } else {
                                $result[$k]['approvalContent'][] = $result[$k]['approvalContent'][$j];
                                unset($result[$k]['approvalContent'][$j]);
                            }
                        }
                    }
                    
                    if(isset($row['prizeContent'])&&!empty($row['prizeContent'])){
                        $re = explode(',', $row['prizeContent']);
                        @$result[$k]['approvalContent']['用户选择'] = $re[0];
                    }
                    
                    $result[$k]['approvalContent'] = json_encode($result[$k]['approvalContent']);
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
        $data['taskStatus'] = array(''=>'审核状态','1'=>'通过','-2'=>'不通过');
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
            $data = array();
        $uid = '';
        $pageSize = 2000;
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
        $search = array('pageSize'=>$pageSize,'pageNow'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$name,'stepStatus'=>$stepStatus,'add_user'=>$add_user,'isIssue'=>$isIssue);
        $res = TaskLionService::task_checked(array_filter($search));
        if(!$res['errorCode']&&$res['result']){
            
            if($add_user=='true'){
                $search_all = array('pageSize'=>$pageSize,'pageNow'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$name,'stepStatus'=>1,'add_user'=>$add_user,'isIssue'=>$isIssue);
                $res_all = TaskLionService::task_checked(array_filter($search_all));
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
                $result[$k]['userinfo']['uid'] = $row['uid'];
                
                if(isset($row['approvalContent'])&&!empty($row['approvalContent'])){
                    if(is_null(json_decode($row['approvalContent']))){
                        $result[$k]['autorContent'] = $row['approvalContent'];
                    }else{
                        $result[$k]['approvalContent'] = json_decode($row['approvalContent'],true);
                        $arr = self::$approvalArr;
                        foreach($result[$k]['approvalContent'] as $j => $v){
                            if (is_array($v)) {
                                $title = $v['title'];unset($v['title']);
                                $result[$k]['approvalContent'][$title] = array_pop($v);
                                unset($result[$k]['approvalContent'][$j]);
                            } else {
                                $result[$k]['approvalContent'][] = $result[$k]['approvalContent'][$j];
                                unset($result[$k]['approvalContent'][$j]);
                            }
                        }
                    }


                    if(isset($row['prizeContent'])&&!empty($row['prizeContent'])){
                        $re = explode(',', $row['prizeContent']);
                        @$result[$k]['approvalContent']['用户选择'] = $re[0];
                    }
                    
                    $result[$k]['approvalContent'] = json_encode($result[$k]['approvalContent']);
                }

            }
        }else{
            $total = 0;
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
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(100);
        $excel->getActiveSheet()->setCellValue('A1','任务标题');
        $excel->getActiveSheet()->setCellValue('B1','用户名');
        $excel->getActiveSheet()->setCellValue('C1','审核状态');
        $excel->getActiveSheet()->setCellValue('D1','上传截图时间');
        $excel->getActiveSheet()->setCellValue('E1','操作人员和时间');
        $excel->getActiveSheet()->setCellValue('F1','玩家上传信息');
        $excel->getActiveSheet()->freezePane('A2');
        foreach($result as $index=>$row){
            $taskName = isset($row['taskName'])?$row['taskName']:'';
            $stepStatus = '';
            if(isset($row['stepStatus'])){
                if($row['stepStatus'] == '1'){
                    $stepStatus = '通过';
                }elseif($row['stepStatus'] == '-2'){
                    $stepStatus = '不通过';
                }
            }
            $createTime = isset($row['createTime'])?$row['createTime']:'';
            $operateName = isset($row['operateName'])?$row['operateName']:'';
            $updateTime = isset($row['updateTime'])?$row['updateTime']:'';

            $id = isset($row['userinfo']['uid'])?$row['userinfo']['uid']:'';
            $approvalContent = '';
            if(isset($row['approvalContent'])){
                $approvalContent = json_decode($row['approvalContent'],true);
            }
            $excel->getActiveSheet()->setCellValue('A'.($index+2), $taskName);
            $excel->getActiveSheet()->setCellValue('B'.($index+2), $id);
            $excel->getActiveSheet()->setCellValue('C'.($index+2), $stepStatus);
            $excel->getActiveSheet()->setCellValue('D'.($index+2), $createTime);
            $excel->getActiveSheet()->setCellValue('E'.($index+2), $operateName."[".$updateTime."]");
            $leng = '';
            if(!empty($approvalContent)){
                foreach($approvalContent as $k=>$v){
                    $leng .=  $k.":".$approvalContent[$k]."|";
                }
                $leng = substr($leng, 0, -1);
            }
            $excel->getActiveSheet()->setCellValue('F'.($index+2),$leng);
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
        $search = array('pageSize'=>$pageSize,'pageNow'=>$pageIndex,'stepId'=>$stepId,'startTime'=>$startTime,'endTime'=>$endTime,'uid'=>$uid,'stepStatus'=>$stepStatus);
        $res = TaskLionService::task_checked(array_filter($search));
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
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(100);
        $excel->getActiveSheet()->setCellValue('A1','任务标题');
        $excel->getActiveSheet()->setCellValue('B1','用户UID');
        $excel->getActiveSheet()->setCellValue('C1','审核状态');
        $excel->getActiveSheet()->setCellValue('D1','上传截图时间');
        $excel->getActiveSheet()->setCellValue('E1','操作人员和时间');
        $excel->getActiveSheet()->setCellValue('F1','玩家上传信息');
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
            $approvalContent = '';
            if(isset($row['approvalContent'])){
                $approvalContent = json_decode($row['approvalContent'],true);
            }
            $excel->getActiveSheet()->setCellValue('A'.($index+2), $taskName);
            $excel->getActiveSheet()->setCellValue('B'.($index+2), $id);
            $excel->getActiveSheet()->setCellValue('C'.($index+2), $stepStatus);
            $excel->getActiveSheet()->setCellValue('D'.($index+2), $createTime);
            $excel->getActiveSheet()->setCellValue('E'.($index+2), $operateName."[".$updateTime."]");
            $leng = '';
            if(!empty($approvalContent)){
                foreach($approvalContent as $k=>$v){
                    $leng .=  $k.":".$approvalContent[$k]."|";
                }
                $leng = substr($leng, 0, -1);
            }
            $excel->getActiveSheet()->setCellValue('F'.($index+2),$leng);
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
        $res = TaskLionService::doSgin(array('userStepIds'=>$ids_str));
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
    
    public function getTaskTag(){
        $data = array('type'=>'taskTag');
        $res = TaskLionService::task_tag($data);
        if(!$res['errorCode']&&$res['result']){
            $data_out = array();
            foreach ($res['result'] as $item) {
                $data_out[$item['id']] = $item;
            }
            sort($data_out);
            $data['data'] = $data_out;
        }
        return $this->display('task-tag',$data);
    }
    
    public function postTaskTagUpdate () {
        $data = array();
        $id = Input::get('id');
        $sortValue = Input::get('sortValue');
        foreach ($id as $k=>$v) {
            $item['opearteMode'] = 'MODIFED';
            $item['type'] = 'taskTag';
            $item['id'] = $v;
            $item['sortValue'] = $sortValue[$k];
            $data[] = $item;
        }

        $res = TaskLionService::update_task_tag($data);
        if(!$res['errorCode'] && $res['result']){
            echo json_encode(array('success'=>"true",'msg'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'msg'=>'修改失败','data'=>""));
        }
    }
    
    public function getTaskRecommend(){
        $data = array('type'=>'recommend');
        $res = TaskLionService::task_tag($data);
        if(!$res['errorCode']&&$res['result']){
            $data_out = array();
            foreach ($res['result'] as $item) {
                $data_out[$item['id']] = $item;
            }
            sort($data_out);
            $data['data'] = $res['result'];
        }
        return $this->display('task-recommend',$data);
    }
    
    public function postTaskRecommendUpdate () {
        $data = array();
        $id = Input::get('id');
        $sortValue = Input::get('sortValue');
        foreach ($id as $k=>$v) {
            $item['opearteMode'] = 'MODIFED';
            $item['type'] = 'recommend';
            $item['id'] = $v;
            $item['sortValue'] = $sortValue[$k];
            $data[] = $item;
        }
    
        $res = TaskLionService::update_task_tag($data);
        if(!$res['errorCode'] && $res['result']){
            echo json_encode(array('success'=>"true",'msg'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'msg'=>'修改失败','data'=>""));
        }
    }

    /**
     * 每日签到统计
     * @author ganlin
     */
    public function getTaskSignStatistics(){
        $search['currenPage'] = Input::get('page',1);
        $search['beginTime'] = Input::get('beginTime');
        $search['endTime'] = Input::get('endTime');
        $search['pageSize'] = 10;
        $res = TaskLionService::daily_sign_report($search);

        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
        }else{
            $total = 0;
            $result= array();
        }
//        print_r($result);
        $pager = Paginator::make(array(),$total,$search['pageSize']);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = $result;
        $data['export_url'] = Config::get("app.task_lion_api_url").'ExportDoc/exportDailySign';

        return $this->display('task-list-statistic',$data);
    }
    
    public function getTaskSign(){
        $data = array();
        $data['img_url'] = Config::get('app.img_url');
        $res = TaskLionService::task_sign($data);
        if(!$res['errorCode']&&$res['result']){
            $data['data'] = $res['result'];
            if ($res['result']['signConfig']) {
                $data['data']['signConfig_str'] = $res['result']['signConfig'];
                $data['data']['signConfig'] = json_decode($res['result']['signConfig'],true);
                foreach ($data['data']['signConfig'] as $k=>&$item) {
                    if (isset($item['rewards']) && is_array($item['rewards'])) {
                        foreach ($item['rewards'] as $item_reward) {
                            switch ($item_reward['type']) {
                                case 2:
                                    $item['rewardValue1'] = $item_reward['rewardValue'];
                                    break;
                                case 3:
                                    $item['rewardValue2'] = $item_reward['rewardValue'];
                                    break;
                                case 5:
                                    $item['rewardValue3'] = $item_reward['rewardValue'];
                                    break;
                                case 4:
                                    $item['rewardValue4'] = 1;
                                    break;
                            }
                        }
                    }
                }
            }
        }
        return $this->display('task-sign',$data);
    }
    
    public function postTaskSignUpdate () {
        $in = Input::all();
        $data = array();
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        $data['opearteMode'] = 'MODIFED';
        $data['id'] = $in['id'];
        $signConfig = json_decode($in['signConfig_str'],true);
        $signConfig_tmp = array();
        foreach ($signConfig as $k=>$v) {
            $signConfig_tmp[$k]['icon'] = $in['prize_img'][$k];
            $signConfig_tmp[$k]['rewards'] = array();
            if (isset($in['rewardValue1_'.($k+1)]) && $in['rewardValue1_'.($k+1)]>0) {
                $signConfig_tmp[$k]['rewards'][] = array('type'=>2,'rewardValue'=>$in['rewardValue1_'.($k+1)]);
            }
            if (isset($in['rewardValue2_'.($k+1)]) && $in['rewardValue2_'.($k+1)]>0) {
                $signConfig_tmp[$k]['rewards'][] = array('type'=>3,'rewardValue'=>$in['rewardValue2_'.($k+1)]);
            }
            if (isset($in['rewardValue3_'.($k+1)]) && $in['rewardValue3_'.($k+1)]>0) {
                $signConfig_tmp[$k]['rewards'][] = array('type'=>5,'rewardValue'=>$in['rewardValue3_'.($k+1)]);
            }
            if (isset($in['rewardValue4_'.($k+1)]) && $in['rewardValue4_'.($k+1)]>0) {
                $signConfig_tmp[$k]['rewards'][] = array('type'=>4);
            }
        }
        $data['signConfig'] = json_encode($signConfig_tmp);
        $res = TaskLionService::update_task_sign($data);
        if(!$res['errorCode'] && $res['result']){
            echo json_encode(array('success'=>"true",'msg'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'msg'=>'修改失败','data'=>""));
        }
    }

    public function postAjaxUploadFile(){
        $tid = Input::get('tid');
        $footer = Input::get('footer');
        if(!Input::hasFile('append_file'))
            return json_encode(array('state'=>0,'msg'=>'文件不存在'));
        $file = Input::file('append_file');
        $tmpfile = $file->getRealPath();        
        $filename = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();
        if(!in_array($ext,array('xls','xlsx','csv'))) return $this->back()->with('global_tips','上传文件格式错误');
        $server_path = storage_path() . '/tmp/';
        $newfilename = microtime() . '.' . $ext;
        $target = $server_path . $newfilename;
        $file->move($server_path,$newfilename);
        require_once base_path() . '/libraries/PHPExcel.php';

        $inputFileType = \PHPExcel_IOFactory::identify($target);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $excel = $objReader->load($target,$encode='utf-8');

        $arrExcel = $excel->getSheet(0)->toArray();
        $data = array();
        $data_tmp = array();
        array_shift($arrExcel);
        foreach ($arrExcel as $item) {
            if (isset($data_tmp[$item[2]])) {
                $data_tmp[$item[2]]['broadcastTime'] += $item[9];
            } else {
                $data_tmp[$item[2]]['roomId'] = $item[2];
                $data_tmp[$item[2]]['taskId'] = $tid;
                $data_tmp[$item[2]]['broadcastTime'] = $item[9];
            }
        }
        $data_tmp = self::array_sort($data_tmp, 'broadcastTime', 'desc');
        $i = 1;
        foreach ($data_tmp as $item) {
            if ($i>10) break;
            $item['sortValue'] = $i;
            $data['rankingListBeans'][] = $item;
            $i++;
        }
        $data['footer'] = $footer;
        $res = TaskLionService::save_task_ranking($data);
        if(!$res['errorCode'] && $res['result']){
            return json_encode(array("state"=>1,'msg'=>'榜单添加成功'));
        }else{
            return json_encode(array('state'=>0,'msg'=>'榜单添加失败'));
        }
    }
    
    //$array 要排序的数组
    //$row  排序依据列
    //$type 排序类型[asc or desc]
    //return 排好序的数组
    private function array_sort($array,$row,$type){
        $array_temp = array();
        foreach($array as $v){
            $array_temp[$v[$row]] = $v;
        }
        if($type == 'asc'){
            ksort($array_temp);
        }elseif($type == 'desc'){
            krsort($array_temp);
        }else{
        }
        return $array_temp;
    }
}