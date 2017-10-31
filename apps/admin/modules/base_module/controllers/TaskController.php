<?php
namespace modules\IOS_activity\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Android\Model\ActivityTaskUser;
use Youxiduo\Task\TaskV3Service;
use Youxiduo\V4\User\UserService;
class TaskController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'IOS_activity';
	}
	
	/**
 * 任务列表
 */
    public function getTaskList()
    {
        $search = Input::get();
        $res = TaskV3Service::task_list(array_filter($search));
        $data['datalist'] = array();
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            $pager = Paginator::make(array(),$total,10);
            $pager->appends($search);
            $data['search'] = $search;
            $data['pagelinks'] = $pager->links();
            $data['datalist'] = $result;
        }
        $data['conditions'] = array(''=>'选择类型','1'=>'试玩','2'=>'分享','3'=>'截图');
        $data['stepType'] = array('4'=>'提交苹果id邮箱','0'=>'图文','1'=>'截图','2'=>'下载','3'=>'设定试玩游戏时间');
        $data['sort'] = array('0'=>'降序','1'=>'升序');
        return $this->display('task-list',$data);

        $pageIndex = Input::get('page',1);
        $title = Input::get('title','');
        $lineId = Input::get('lineId','');
        $gid = Input::get('gameId','');
        $sortType = Input::get('sortType','');
        $startTime = Input::get('activityStartTime','');
        $endTime = Input::get('activityEndTime','');
        $complete_type = Input::get('complete_type');
        $pageSize = 10;
        $data = array();
//		$data['action_type'] = $action_type;
        $data['lineName'] =isset($_REQUEST['lineName'])?$_REQUEST['lineName']:"";
        $data['lineType'] =isset($_REQUEST['lineType'])?$_REQUEST['lineType']:"";
        $data['title'] = $title;
        $data['lineId'] = $lineId;
        $data['complete_type'] = $complete_type;
        $search = array('pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','lineId'=>$lineId,'taskType'=>$complete_type,'taskName'=>$title,'isLoadPrize'=>"true",'platformType'=>'I','isLoadStep'=>'true','gid'=>$gid,'sortType'=>$sortType,'createTimeBegin'=>$startTime,'createTimeEnd'=>$endTime);
        $search['isSubTask'] = $lineId?"true":"false";
//        print_r(array_filter($search));
        $res = TaskV3Service::task_list(array_filter($search));
        print_r($res);
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
        $data['stepType'] = array('4'=>'提交苹果id邮箱','0'=>'图文','1'=>'截图','2'=>'下载','3'=>'设定试玩游戏时间');
        $data['sort'] = array('0'=>'降序','1'=>'升序');
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
        $pageSize = 10;
        $data = array();
        $search = array('isLine'=>'false','pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','isRelateLine'=>"false",'taskType'=>"0",'taskName'=>$title,'isLoadPrize'=>"true",'platformType'=>'I','gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"true",'createTimeBegin'=>$startTime,'createTimeEnd'=>$endTime);
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
        $gid = Input::get('gameId','');
        $sortType = Input::get('sortType','');
        $pageSize = 5;
        $data = array();
        $search = array('isLine'=>'false','pageSize'=>$pageSize,'pageIndex'=>$pageIndex,'isLoadCount'=>'true','isRelateLine'=>"false",'taskType'=>"0",'taskName'=>$title,'isLoadPrize'=>"true",'platformType'=>'I','gid'=>$gid,'sortType'=>$sortType,'isSubTask'=>"true");
//        print_r($search);
        $res = TaskV3Service::task_list($search);
//        print_r($res);
        if(!$res['errorCode']&&$res['result']){
            $total = $res['totalCount'];
            $result = $res['result'];
            foreach($result as $k=>$row){
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
        $data['stepType'] = array('4'=>'提交苹果id邮箱','0'=>'图文','1'=>'截图','2'=>'下载','3'=>'设定试玩游戏时间');
		if($id){
			$info = TaskV3Service::task_get(array('taskId'=>$id));
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
            print_r($info);
            if(isset($info['result']['typeCondition']))
                $info['result']['typeCondition'] = json_decode($info['result']['typeCondition'],true);
            if(isset($info['result']['stepList'])){
                foreach($info['result']['stepList'] as $k1=>$v1){
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

//            print_r($res_children);
//            print_r($info['result']);
			$data['atask'] = $info['result'];
		}else{
			$data['atask'] = array('is_show'=>1,'reward_type'=>'money');
		}
		return $this->display('task-add',$data);
	}
	
    public function postTaskAdd()
	{
        $id = Input::get('taskId','');
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        $input = array();
        //是否是独立任务或连续任务
        $lineType = input::get("selTaskType");
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
        //Ios端
        $input['platformType'] = 'I';
        $input['taskName'] = Input::get('taskName');
        $input['gid'] = Input::get('game_id');
        $input['startTime'] = Input::get('start_time') ;
        $input['endTime'] = Input::get('end_time');
        $input['sortValue'] = (int)Input::get('sort',0);
        $input['taskContent'] = Input::get('taskContent');
		$input['linkType'] = Input::get('selLinkType');
        $input['linkValue'] = Input::get('linkValue');
        $input['subTaskIds'] = substr(Input::get('ids'),0,-1);
        if(Input::get('top',"")=="on"){
            $input['sortValue'] = "100";
        }
        //步骤数组
        $prize_img_arr = Input::get('prize_img');
        $stepType_arr = Input::get('stepType');

        if(Input::hasFile('prize_pic')){
            $prize_pic = Input::file('prize_pic');
        }else{
            $prize_pic = array();
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
        if(Input::get('selPrizeType')=="0"){
            $prize_list['prizeType'] = "0";
            $prize_list['prizeName'] = Input::get('prizeKey')."游币";
        }else{
            $prize_list['prizeType'] = "3";
            $prize_list['prizeName'] = Input::get('prizeKey')."钻石";
        }
        $prize_list['prizeIcon'] = '';
        $prize_list['prizeKey']  = Input::get('prizeKey');
        if($id){
            $prize_list['actionType'] = 'update';
            $prize_list['prizeId'] = Input::get('prizeId');
        }else{
            $prize_list['actionType'] = 'insert';
        }
        $prize[] = $prize_list;
        if($prize){
            $input['prizeListStr'] = json_encode($prize);
        }
        //编辑时
        if($id){
            $input['taskId'] = $id;
            $res = TaskV3Service::task_edit($input);
        }else{
            if($lineType!="2"){
                //处理步骤json
                $steps = json_decode("[".Input::get("stepListStr")."]",true);
                foreach($steps as $k=>$v){
                    if($v['stepType']=="0"||$v['stepType']=="1"){
                        $file = array_shift($prize_pic);//获取数组第一个元素
                        $new_filename = date('YmdHis') . str_random(4);
                        $mime = $file->getClientOriginalExtension();
                        $file->move($path,$new_filename . '.' . $mime );
                        $steps[$k]['stepCondition']['image'] = $dir . $new_filename . '.' . $mime;
                    }
                    if($steps[$k]['stepCondition']){
                        $steps[$k]['stepCondition'] = json_encode($steps[$k]['stepCondition']);
                    }else{
                        $steps[$k]['stepCondition'] = "{}";
                    }

                }
                if($steps){
                    $input['stepListStr'] = json_encode($steps);
                }
            }
            $res = TaskV3Service::task_add($input);
        }
        print_r(input::get());
        print_r($input);
        print_r($res);die;
        if(!$res['errorCode']&&$res['result']){
			return $this->redirect('IOS_activity/task/task-list','数据保存成功');
		}else{
			return $this->back('数据保存失败');
		}
	}

    public function postTaskOpen()
    {
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        $data = array('taskId'=>$id,'closeType'=>$type);
        if($type == "3"){
            $res = TaskV3Service::task_edit(array('taskId'=>$id,'sortValue'=>"100"));
        }elseif($type == "4"){
            $res = TaskV3Service::task_edit(array('taskId'=>$id,'sortValue'=>"50"));
        }else{
            $res = TaskV3Service::task_close($data);
        }

//        print_r($res);
        if(!$res['errorCode']&&$res['result']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$type));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>$type));
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
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
        }
    }

    /**截图列表html**/
    public function postScreenshotList()
    {
        $taskId = Input::get('taskId');
        $stepId = Input::get('stepId');
        $pageSize = Input::get('count');
        $data = array();
        $search = array('taskId'=>$taskId,'stepId'=>$stepId,'pageSize'=>$pageSize,'taskStatus'=>"0");
//        print_r($search);
        $result = TaskV3Service::query_user_step_info_list(array('taskId'=>$taskId,'stepId'=>$stepId,'pageSize'=>$pageSize));

//        print_r($result);die;
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
            $uinfos = UserService::getMultiUserInfoByUids(array_unique($user_arr));
            if (is_array($uinfos)) {
                foreach ($uinfos as $row) {
                    $uinfos[$row['uid']] = $row;
                }
            }
            $data['users'] = $uinfos;
//            foreach($result['result'] as &$item){
//                $item['can_use'] = $item['materialStock'] + $item['materialUsedStock'] - $item['materialQuota'];
//            }
//            $data=self::processingInterface($result,$data,$params['pageSize']);
            $data['pic_count'] = $pic_count;
            $data['datalist'] = $result['result'];
//            print_r($data);
            $html = $this->html('screenshot-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }

    public function getApprovalList()
    {
        $data['taskId'] = Input::get('taskId');
        $data['taskName'] = Input::get('taskName');
        $data['stepId'] = Input::get('stepId');
        $data['stepType'] = Input::get('stepType');
        $data['step_content'] = Input::get('step_content');
        $data['step_img'] = Input::get('step_img');
        $data['appleId'] = Input::get('appleId');
        $data['title'] = Input::get('title');
//        print_r($data);
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
        $search = array('taskId'=>$taskId,'stepId'=>$stepId,'pageSize'=>"1",'taskStatus'=>"0");
//        print_r($search);
        $result = TaskV3Service::query_user_step_info_list(array('taskId'=>$taskId,'stepId'=>$stepId,'pageSize'=>"1"));
//        print_r($result);
        if(!$result['errorCode']&&$result['result']) {
            $user_arr = array();
            if ($result['result'][0]['picUrl']) {
                $result['result'][0]['picUrl'] = explode(',',$result['result'][0]['picUrl']);
            }
            $user_arr[] = $result['result'][0]['uid'];
            $uinfos = UserService::getMultiUserInfoByUids(array_unique($user_arr));
            if (is_array($uinfos)) {
                foreach ($uinfos as $row) {
                    $uinfos[$row['uid']] = $row;
                }
            }
            $data['users'] = $uinfos;
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
        $data['title'] = Input::get('title');
        return $this->display('audit-picture-one',$data);

    }

    public function postApproval()
    {
        $stepId = Input::get('stepId');
        $passIds = Input::get('passIds');
        $notPassIds = Input::get('notPassIds');
        $success = "true";
        $data = array('stepId'=>$stepId);
        if($passIds){
            $data['passIds'] = substr($passIds,0,-1);
            $res1 = TaskV3Service::approval_step_screenshot($data);
            if(isset($res1['errorCode'])&&$res1['errorCode']){
                $success = "false";
            }
        }
        if($notPassIds){
            $data['notPassIds'] = substr($notPassIds,0,-1);
            $res2 = TaskV3Service::approval_step_screenshot($data);
            if(isset($res2['errorCode'])&&$res2['errorCode']){
                $success = "false";
            }
        }
        $res = TaskV3Service::approval_step_screenshot($data);
//        print_r(Input::get());
//        print_r($data);
//        print_r($res);die;
        if($success == "true"){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>""));
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
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$icon));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>"图片丢失"));
        }


    }

    public function postStepSave(){
        $input = Input::get();

        if($input){
            if(isset($input['stepId'])&&!empty($input['stepId'])){
                $res = TaskV3Service::update_step_base_info($input);
            }else{
                $res = TaskV3Service::insert_step($input);
            }
//            print_r($input);
//            print_r($res);die;
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

}