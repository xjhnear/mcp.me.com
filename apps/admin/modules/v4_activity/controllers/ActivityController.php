<?php
namespace modules\v4_activity\controllers;
use Youxiduo\V4\Activity\ActivityService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\V4\User\UserService;
use Youxiduo\Task\TaskV3Service;
use Youxiduo\Helper\MyHelp;
use libraries\Helpers;
use Youxiduo\V4share\V4shareService;
use Youxiduo\Cache\CacheService;

//傅佳俊2015/08/13 V4_IOS_活动管理 1.0
class ActivityController extends BackendController{
    const GENRE = 1; //类型为IOS 
    public function _initialize(){
        $this->current_module = 'v4_activity';
    }

    //活动列表
    public function getList(){
        $data=array();

        $input = Input::all();
        $params=array(
             'selType'//0为全部 1进行中；2已经结束
            ,'activityStartTime'//开始时间
            ,'activityEndTime'//结束时间
            ,'name'//名称
            ,'gid'//游戏ID
            ,'activityId'//ID'
            ,'pageIndex'
            ,'pageSize'
            ,'activityType'
            ,'appName'
        );
        if(!Input::get('appName')){
            $input['appName'] = 'yxdjqb';
        }
        $inputinfo=MyHelp::get_Input_value($input,$params);
        if(!empty($inputinfo['activityStartTime'])){
            $inputinfo['activityStartTime']=date('Y-m-d H:i:s',strtotime($inputinfo['activityStartTime']));
        }
        if(!empty($inputinfo['activityEndTime'])){
            $inputinfo['activityEndTime']=date('Y-m-d H:i:s',strtotime($inputinfo['activityEndTime']));
        }
        
//        //根据游戏ID查询关联活动
//        if(!empty($inputinfo['gameId'])){
//            $data=ActivityService::get_activity_list_by_gid($inputinfo['gameId'],self::GENRE);
//            if($data['errorCode']==0){
//                //根据游戏ID获取活动ID
//                if(!empty($data['result'])) $inputinfo['activityId']=$data['result'];
//            }
//        }
        if(!empty($inputinfo['gameId'])&&empty($data['result'])){
            $data=MyHelp::processingInterface(array(),$inputinfo,$inputinfo['pageSize']);
            return $this->display('activity/activity-list',$data);
        }

        $inputinfo['activityType']='2,3,4';
        $result=ActivityService::get_activity_info_list_back_end($inputinfo,$params);
//        print_r($inputinfo);
//        print_r($result);die;
        if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$inputinfo,$inputinfo['pageSize']);
            $info=array();
            $info['genre']=self::GENRE;
            $info['isActive']='true';
            $info['activityId']=MyHelp::get_Ids($data['datalist'],'id');
            $data['datalist']=MyHelp::getGameInfoByGid($result);
//            print_r($data['datalist']);
            $data['platforms'] = array('yxdjqb' => 'IOS','youxiduojiu3' => 'IOS业内版','glwzry'=>'攻略');
            $data['appName'] = $input['appName'];
            return $this->display('activity/activity-list',$data);
        }

        return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
    }

    //编辑活动视图
    public function getActivityInfo($id='',$gid='',$gname='',$other_id="")
    {
        $info=array();
        $info['platforms'] = array('yxdjqb' => 'IOS','youxiduojiu3' => 'IOS业内版','glwzry'=>'攻略');
        
        if(empty($id)) return $this->display('activity/activity-add',$info);

        $info['activityId']=$id;
        $info['activityType']='2,3,4';
        $datainfo=ActivityService::get_activity_info_list_back_end($info,array('activityId','activityType'));
        //var_dump($datainfo);die;
        if($datainfo['errorCode'] == 0){
             $info['info']=$datainfo['result']['0'];
             $info['info']['gid']=!empty($gid)?$gid:'';
             $info['info']['gname']=!empty($gname)?$gname:'';
             $info['info']['other_id']=!empty($other_id)?$other_id:'';
             if ($info['info']['activityType']==4 && $info['info']['linkValue']) {
                 //获取任务名
                 $search = array('taskId'=>$info['info']['linkValue']);
                 $search['isSubTask'] = "false";
                 $task = TaskV3Service::task_list(array_filter($search));
                 $info['info']['task_name'] = $task['result'][0]['taskName'];
             } elseif ($info['info']['activityType']==3 && $info['info']['linkValue']) {
                 //获取分享名
                 $res = V4shareService::excute3(array('id'=>$info['info']['linkValue']),"get_share_config_detail");
//                  print_r($res);exit;
                 $info['info']['v4share_name'] = $res['data']['title'];
             }
             if (isset($info['info']['linkId']) && $info['info']['linkId']=='1061') {
                 $info['info']['linkType'] = 3;
             }
             return $this->display('activity/activity-edit',$info);
        }
        return $this->redirect('v4activity/activity/list')->with('global_tips','操作失败');
    }
    //关闭
    public function getClose($id,$fid="")
    {
        if(empty($id)) return $this->redirect('v4activity/activity/list')->with('global_tips','参数丢失');
        $result=$this->edit($id,'close');
        //添加修改“更新活动游戏接口”
        if($fid){
           $res = ActivityService::update_activity_game(array('gid'=>"",'id'=>$fid,'createTime'=>""));
        }

        if($result['errorCode'] == 0){
            return $this->redirect('v4activity/activity/list')->with('global_tips','操作成功');
        }else{
            return $this->redirect('/v4activity/activity/list')->with('global_tips','操作失败');
        }
    }
    //开启
    public function getEnable($id)
    {
        if(empty($id)) return $this->redirect('v4activity/activity/list')->with('global_tips','参数丢失');
        $result=$this->edit($id,'enable');
        if($result['errorCode'] == 0){
            return $this->redirect('v4activity/activity/list')->with('global_tips','操作成功');
        }else{
            return $this->redirect('/v4activity/activity/list')->with('global_tips','操作失败');
        }
    }

    //置顶
    public function getTop($id,$val)
    {
        if(empty($id)) return $this->redirect('v4activity/activity/list')->with('global_tips','参数丢失');
        $result=$this->edit($id,'top',$val);
        if($result['errorCode'] == 0){ 
            return $this->redirect('v4activity/activity/list')->with('global_tips','操作成功');
        }else{
            return $this->redirect('/v4activity/activity/list')->with('global_tips','操作失败');   
        }
    }

    private function edit($id,$type,$is_top=0)
    {      
        $params=array();
        $params['id']=$id;
        switch ($type) {
            case 'close':
                $params['isActive']='false';
                break;
             case 'enable':
                $params['isActive']='true';
                break;
             case 'top':
                $params['isTop']=!empty($is_top)?'true':'false';
                break;
            default:
                # code...
                break;
        }
        return ActivityService::update_activity_info($params,array_keys($params));
        
    }

    //编辑活动添加
    public function postAdd()
    {
         $input = Input::all();
         $rule = $prompt = $inputinfo =array();
         $valid = Validator::make($input,$rule,$prompt);
         if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
         $params=array(
             'pic'//图片
            ,'topPic'//图片
            ,'activityStartTime'//开始时间
            ,'activityEndTime'//结束时间
            ,'name'//名称
            ,'isTopIntMax'//是否置顶
            ,'top'//排序
            ,'linkType' //目标类型（0:内部的id 1：外部safiri链接 2：内容webview）
            ,'linkId'//目标ID
            ,'linkValue'//目标ID的值
            ,'linkValue2'//目标ID的值2
            //,'activityId'//ID'
             ,'gid'
            ,'isTop'
            ,'activityType'
            ,'createTime'
            ,'descHtml'
            ,'isOnOff'
            ,'isAutoLogin'
            ,'appName'
            ,'pushStatus'
        );
         $inputinfo=MyHelp::get_Input_value($input,$params,0);
         $inputinfo['activityType']=$input['Type'];
         if ($inputinfo['activityType'] == 2) {
             $inputinfo['linkId'] =1017;
             if(!empty($inputinfo['linkValue_']) && !empty($inputinfo['linkType'])){
                 $inputinfo['linkValue']=$inputinfo['linkValue_'];
                 unset($inputinfo['linkId'],$inputinfo['linkValue_']);
             }
             if (!empty($inputinfo['linkType']) && $inputinfo['linkType']==3) {
                 $inputinfo['linkType'] = 0;
                 $inputinfo['linkId'] = 1061;
             }
             $inputinfo['linkValue'] = $input['linkValue']?$input['linkValue']:$input['linkValue_'];
         } elseif ($inputinfo['activityType'] == 3) {
             $inputinfo['linkValue'] = $input['linkValue_s']?$input['linkValue_s']:"";
             $inputinfo['descHtml'] = $input['descHtml']?$input['descHtml']:"";
         } elseif ($inputinfo['activityType'] == 4) {
             $inputinfo['linkValue'] = $input['linkValue_t']?$input['linkValue_t']:"";
             $inputinfo['descHtml'] = $input['descHtml_t']?$input['descHtml_t']:"";
         }
         unset($input['linkValue_s']);
         unset($input['linkValue_t']);
         
         if(!empty($inputinfo['isTopIntMax'])){
//            unset($inputinfo['top']);
            $inputinfo['isTopIntMax']=1;
            $inputinfo['isTop']='true';
         }
         if(!empty($input['pic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['pic']);
            $inputinfo['pic'] = $path;
         }
        if(!empty($input['topPic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['topPic']);
            $inputinfo['topPic'] = $path;
        }

         if(!empty($inputinfo['activityStartTime'])){
            $inputinfo['activityStartTime']=date('Y-m-d H:i:s',strtotime($inputinfo['activityStartTime']));
         }
         if(!empty($inputinfo['activityEndTime'])){
            $inputinfo['activityEndTime']=date('Y-m-d H:i:s',strtotime($inputinfo['activityEndTime']));
         }
        $inputinfo['createTime'] = date("Y-m-d H:i:s",time());
        if(Input::get("game_id")){
            $inputinfo['gid'] = Input::get("game_id");
        }

        //是否自动登录
        unset($inputinfo['isAutoLogin']);
        if($input['Type'] == 2){
            if($input['linkType'] == 1 || $input['linkType'] == 2){
                if($input['isAutoLogin'] == 1){
                    $inputinfo['isAutoLogin'] = "true";
                }else{
                    $inputinfo['isAutoLogin'] = "false";
                }
            }
        }

        //是否推送      待接口完成后放开
        if(isset($input['isPush'])){
            $inputinfo['pushStatus'] = '2';
        }else{
            $inputinfo['pushStatus'] = "1";
        }
        
        switch($input['shelf_set']){
            case '1': //上架
                $inputinfo['isOnOff'] = '1';
                break;
            case '2': //下架
                $inputinfo['isOnOff'] = '0';
                break;
        }

         $result = ActivityService::save_activity_info($inputinfo,$params);
         if($result['errorCode'] == 0){
            //添加关系
//            if(Input::get('game_id') && !empty($result['result'])){
//                //***createTime添加参数 ？ 这里是当前时间还是传过来的
//                $rel_data = array('gid'=>Input::get('game_id'),'activityId'=>$result['result'],'genre'=>self::GENRE,'createTime'=>$inputinfo['createTime']);
//                $res=ActivityService::save_activity_game($rel_data);
//            }
             //V4活动添加缓存
             $data = array();
             if(!empty($inputinfo['gid'])){
                 $data = CacheService::cache_add_type_count_act($inputinfo['gid'],'game_activity');
             }else{
                 return $this->redirect('v4activity/activity/list?appName='.$input['appName'])->with('global_tips','操作成功,缓存失败');
             }
             if(!isset($data['errorCode']) || $data['errorCode'] != 0){
                 return $this->redirect('v4activity/activity/list?appName='.$input['appName'])->with('global_tips','操作成功,缓存失败');
             }else{
                 return $this->redirect('v4activity/activity/list?appName='.$input['appName'])->with('global_tips','操作成功');
             }
         }elseif($result['errorCode'] == 400){
             return $this->redirect('v4activity/activity/list?appName='.$input['appName'])->with('global_tips',$result['errorDescription']);
         }else{
            return $this->redirect('v4activity/activity/list?appName='.$input['appName'])->with('global_tips','操作失败');
         }
    }
    //编辑活动修改
    public function postEdit()
    {
         $input = Input::all();
         $rule = $prompt = $inputinfo =array();
         $valid = Validator::make($input,$rule,$prompt);
         if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
         $params=array(
             'pic'//图片
            ,'topPic'//图片
            ,'activityStartTime'//开始时间
            ,'activityEndTime'//结束时间
            ,'name'//名称
            ,'isTopIntMax'//是否置顶
            ,'top'//排序
            ,'linkType' //目标类型（0:内部的id 1：外部safiri链接 2：内容webview）
            ,'linkId'//目标ID
            ,'linkValue'//目标ID的值
            ,'linkValue2'//目标ID的值2
            ,'id'//ID'
            ,'isTop'
            ,'activityType'
            ,'gid'
            ,'descHtml'
            ,'isOnOff'
             ,'isAutoLogin'//是否自动登录
             ,'appName'
        );
         $inputinfo=MyHelp::get_Input_value($input,$params,0);
         $inputinfo['activityType']=$input['Type'];
         if ($inputinfo['activityType'] == 2) {
             $inputinfo['linkId'] =1017;
             if(!empty($inputinfo['linkValue2']) && !empty($inputinfo['linkType'])){
                 $inputinfo['linkValue']=$inputinfo['linkValue2'];
                 unset($inputinfo['linkId'],$inputinfo['linkValue2']);
             }
             if (!empty($inputinfo['linkType']) && $inputinfo['linkType']==3) {
                 $inputinfo['linkType'] = 0;
                 $inputinfo['linkId'] = 1061;
             }
         } elseif ($inputinfo['activityType'] == 3) {
             $inputinfo['linkValue'] = $input['linkValue_s']?$input['linkValue_s']:"";
             $inputinfo['descHtml'] = $input['descHtml']?$input['descHtml']:"";
         } elseif ($inputinfo['activityType'] == 4) {
             $inputinfo['linkValue'] = $input['linkValue_t']?$input['linkValue_t']:"";
             $inputinfo['descHtml'] = $input['descHtml_t']?$input['descHtml_t']:"";
         }
         unset($input['linkValue_s']);
         unset($input['linkValue_t']);

         if(!empty($inputinfo['isTopIntMax'])){
//            unset($inputinfo['top']);
            $inputinfo['isTopIntMax']=1;
            $inputinfo['isTop']='true';
         }else{
          $this->edit($inputinfo['id'],'top',0);
         }
         if(!empty($input['pic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['pic']);
            $inputinfo['pic'] = $path;
         }
        if(!empty($input['topPic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['topPic']);
            $inputinfo['topPic'] = $path;
        }
         if(!empty($inputinfo['activityStartTime'])){
            $inputinfo['activityStartTime']=date('Y-m-d H:i:s',strtotime($inputinfo['activityStartTime']));
         }
         if(!empty($inputinfo['activityEndTime'])){
            $inputinfo['activityEndTime']=date('Y-m-d H:i:s',strtotime($inputinfo['activityEndTime']));
         }

         //linkType 为0时 会被过滤掉，所以要补上
         if(!isset($inputinfo['linkType'])){
             $inputinfo['linkType'] = 0;
         }

         //是否自动登录
        unset($inputinfo['isAutoLogin']);
        if($input['Type'] == 2){
            if($input['linkType'] == 1 || $input['linkType'] == 2){
                if($input['isAutoLogin'] == 1){
                    $inputinfo['isAutoLogin'] = "true";
                }else{
                    $inputinfo['isAutoLogin'] = "false";
                }
            }
        }

        switch($input['shelf_set']){
            case '1': //上架
                $inputinfo['isOnOff'] = '1';
                break;
            case '2': //下架
                $inputinfo['isOnOff'] = '0';
                break;
        }
        
        if(Input::get("game_id")){
            $inputinfo['gid'] = Input::get("game_id");
        }
        
        //添加关系
        //添加修改“更新活动游戏接口”
        $other_id = $input['other_id'];
        $old_gid = $input['old_gid'];
        $old_starttime = $input['old_starttime'];
        if($other_id){
            $arr = array('id' => $other_id);
//            if(!empty($input['game_id'])||$old_starttime!=$input['activityStartTime']){
//                $arr['gid'] = empty($input['game_id'])?"":$input['game_id'];
////                $arr['createTime'] = $old_starttime!=$input['activityStartTime']?$input['activityStartTime']:"";
//                $res = ActivityService::update_activity_game($arr);
//            }
        }else{
            $rel_data = array('gid'=>Input::get('game_id'),'activityId'=>$inputinfo['id'],'genre'=>self::GENRE);
            $res=ActivityService::save_activity_game($rel_data);
        }
        //var_dump($inputinfo);die;

         $result = ActivityService::update_activity_info($inputinfo,$params);
         if($result['errorCode'] == 0){
            return $this->redirect('v4activity/activity/list?appName='.$input['appName'])->with('global_tips','操作成功');
         }elseif($result['errorCode'] == 400){
            return $this->redirect('v4activity/activity/list?appName='.$input['appName'])->with('global_tips',$result['errorDescription']);
         }else{
            return $this->redirect('v4activity/activity/list?appName='.$input['appName'])->with('global_tips','操作失败');
         }
    }
    
    public function getAjaxShelf(){
        $p_code = Input::get('p_code',false);
        $state = Input::get('state',false);
        $params=array(
            'id'//ID'
            ,'isOnOff'
        );
        if(!$p_code || $state === false) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        if(!$state){
            //下架
            $result = ActivityService::update_activity_info(array('id'=>$p_code,'isOnOff'=>'0'),$params);
        }else{
            //上架
            $result = ActivityService::update_activity_info(array('id'=>$p_code,'isOnOff'=>'1'),$params);
        }
        if(!$result['errorCode']){
            return $this->json(array('state'=>1,'msg'=>'更新成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'更新失败，请重试'));
        }
    }
    
    
    //游戏列表
    public function getH5list(){
        $data=array();
    
        $input = Input::all();
        $params=array(
            'platform'
        );
        $result=ActivityService::get_h5_info_list($input,$params);

        if($result['errorCode']==0){
            $data['datalist']=$result['result'];
            $data['platforms'] = array('' => '全部', 'ios' => 'IOS','android'=>'Android');
            $data['search'] = $input;
            return $this->display('h5/game-list',$data);
        }
    
        return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
    }
    
    //编辑活动视图
    public function getH5Info($id='')
    {
        $info=array();
        $info['platforms'] = array('ios' => 'IOS','android'=>'Android');
    
        if(empty($id)) return $this->display('h5/game-add',$info);
    
        $info['gameid']=$id;
        $datainfo=ActivityService::get_h5_info_detail($info,array('gameid'));
//         var_dump($datainfo);die;
        if($datainfo['errorCode'] == 0){
            $info['info']=$datainfo['result'];
            return $this->display('h5/game-edit',$info);
        }
        return $this->redirect('v4activity/activity/activity-info')->with('global_tips','操作失败');
    }
    
    //编辑活动添加
    public function postH5Add()
    {
        $input = Input::all();
        $rule = $prompt = $inputinfo =array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $params=array(
            'gameid'//游戏ID
            ,'gametitle'//游戏标题
            ,'gamebackgroud'//游戏背景图
            ,'gamedownloadurl'//游戏下载地址
            ,'platform'//平台参数
            ,'orderbyvalue'//排序值
            ,'gametoppic' //游戏头图
        );
        $inputinfo=MyHelp::get_Input_value($input,$params,0);

        if(!empty($input['pic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['pic']);
            $inputinfo['gamebackgroud'] = $path;
        }
        if(!empty($input['topPic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['topPic']);
            $inputinfo['gametoppic'] = $path;
        }
        if(empty($input['orderbyvalue'])){
            $inputinfo['orderbyvalue'] = 0;
        }

        $result = ActivityService::save_h5_info($inputinfo,$params);
        if($result['errorCode'] == 0){
            return $this->redirect('v4activity/activity/h5list?platform='.$input['platform'])->with('global_tips','操作成功');
        }elseif($result['errorCode'] == 400){
            return $this->redirect('v4activity/activity/h5list?platform='.$input['platform'])->with('global_tips',$result['errorDescription']);
        }else{
            return $this->redirect('v4activity/activity/h5list?platform='.$input['platform'])->with('global_tips','操作失败');
        }
    }
    //编辑活动修改
    public function postH5Edit()
    {
        $input = Input::all();
        $rule = $prompt = $inputinfo =array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $params=array(
            'gameid'//游戏ID
            ,'gametitle'//游戏标题
            ,'gamebackgroud'//游戏背景图
            ,'gamedownloadurl'//游戏下载地址
            ,'platform'//平台参数
            ,'orderbyvalue'//排序值
            ,'gametoppic' //游戏头图
        );
        $inputinfo=MyHelp::get_Input_value($input,$params,0);
        
        if(!empty($input['pic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['pic']);
            $inputinfo['gamebackgroud'] = $path;
        }
        if(!empty($input['topPic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['topPic']);
            $inputinfo['gametoppic'] = $path;
        }
        if(empty($input['orderbyvalue'])){
            $inputinfo['orderbyvalue'] = 0;
        }  

        $result = ActivityService::update_h5_info($inputinfo,$params);
        if($result['errorCode'] == 0){
            return $this->redirect('v4activity/activity/h5list?platform='.$input['platform'])->with('global_tips','操作成功');
        }elseif($result['errorCode'] == 400){
            return $this->redirect('v4activity/activity/h5list?platform='.$input['platform'])->with('global_tips',$result['errorDescription']);
        }else{
            return $this->redirect('v4activity/activity/h5list?platform='.$input['platform'])->with('global_tips','操作失败');
        }
    }
    
    public function getAjaxH5Del($id=''){
        if(!$id) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        $info['gameid'] = $id;
        $result = ActivityService::del_h5_info($info,array('gameid'));
        if(!$result['errorCode']){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    }
    
    //编辑活动分享设置
    public function getH5share()
    {
        $info=array();

        $datainfo=ActivityService::get_h5_share_list($info,array('platform'));
//         print_r($datainfo);die;
        if($datainfo['errorCode'] == 0){
            $info['data']=$datainfo['result']['0'];
            return $this->display('h5/share-info',$info);
        }
        return $this->redirect('v4activity/activity/h5share')->with('global_tips','操作失败');
    }
    
    //编辑活动修改
    public function postH5share()
    {
        $input = Input::all();
        $rule = $prompt = $inputinfo =array();
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $params=array(
            'shareid'//分享ID
            ,'sharetitle'//分享标题
            ,'sharedes'//分享描述
            ,'sharebackgroud'//顶部Banner图片
        );
        $inputinfo=MyHelp::get_Input_value($input,$params,0);
    
        if(!empty($input['pic'])){
            $dir = '/userdirs/mall/product/'.date('ym',time()).'/';
            $path = Helpers::uploadPic($dir,$input['pic']);
            $inputinfo['sharebackgroud'] = $path;
        }
        
        if(empty($input['shareid'])){
            $result = ActivityService::save_h5_share($inputinfo,$params);
        } else {
            $result = ActivityService::update_h5_share($inputinfo,$params);
        }
        if($result['errorCode'] == 0){
            return $this->redirect('v4activity/activity/h5share')->with('global_tips','操作成功');
        }elseif($result['errorCode'] == 400){
            return $this->redirect('v4activity/activity/h5share')->with('global_tips',$result['errorDescription']);
        }else{
            return $this->redirect('v4activity/activity/h5share')->with('global_tips','操作失败');
        }
    }
    
}