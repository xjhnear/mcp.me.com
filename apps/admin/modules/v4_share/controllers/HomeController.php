<?php
namespace modules\v4_share\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use modules\wcms\models\Article;
use modules\yxvl_eSports\controllers\HelpController;
use Youxiduo\V4share\V4shareService;
use Youxiduo\V4\User\UserService;
use Youxiduo\Imall\ProductService;


class HomeController extends BackendController
{
    public static $prizeArr = array('youb' => '游币','diamond' => '钻石','gift' => '礼包','good' => '商品','wheel'=>'大转盘次数');
    public static $platformArr = array('ios' => '游戏多IOS','glwzry'=>'王者大宝鉴','android'=>'安卓');
    public function _initialize()
    {
        $this->current_module = 'v4_share';
    }

    function getV4share()
    {
        $data = array();
        $search['pageIndex'] = (int)Input::get('page',1);
        $search['pageSize'] = 10;
        $search['title'] = Input::get("title");
        if(empty($search['title'])){
            unset($search['title']);
        }
        $search['platform'] = Input::get("platform");
        if($search['platform'] == ''){
            unset( $search['platform']);
        }
        $res = V4shareService::excute3($search,"get_share_config_list");
        if($res['success']) {
            $data['list'] = $res['data'];
            $total = $res['totalCount'];
            $pager = Paginator::make(array(), $total, $search['pageSize']);
            $pager->appends($search);
            $data['pagelinks'] = $pager->links();
            $data['totalcount'] = $total;
            $data['platforms'] = self::$platformArr;
            $data['search'] = $search;
        }
        return $this->display('v4share-list',$data);
    }

    public function getV4shareAdd()
    {
        $data = array();
        $input = Input::get();
        $id = Input::get('id',"");
        if($id){
            $res = V4shareService::excute3($input,"get_share_config_detail");
            if($res['data']){
                $params['platform'] = isset($res['data']['platform'])&&$res['data']['platform']=='android'?'android':'ios';
                if(isset($res['data']['newReward']['giftId'])&&$res['data']['newReward']['giftId'] && $res['data']['newReward']['rewardType'] == 3){
                    $params['cardCode'] = $res['data']['newReward']['giftId'];
                    $re = ProductService::getvirtualcardlist($params);
                    if($re['errorCode'] == 0&&!empty($re['result'])){
                        $res['data']['newReward']['cardnum'] = $re['result'][0]['cardStock']+$re['result'][0]['cardUsedStock']-$re['result'][0]['cardQuota'];
                    }
                }
                if(isset($res['data']['oldReward']['giftId'])&&$res['data']['oldReward']['giftId'] && $res['data']['oldReward']['rewardType'] == 3){
                    $params['cardCode'] = $res['data']['oldReward']['giftId'];
                    $re = ProductService::getvirtualcardlist($params);
                    if($re['errorCode'] == 0&&!empty($re['result'])){
                        $res['data']['oldReward']['cardnum'] = $re['result'][0]['cardStock']+$re['result'][0]['cardUsedStock']-$re['result'][0]['cardQuota'];
                    }
                }
                $data['data'] = $res['data'];
            }
        }
        //var_dump($data);die;
        return $this->display('task-add',$data);
    }


    public function getAjaxGetV4share()
    {
        $id = Input::get('shareId',"");
        if($id){
            $res = V4shareService::excute2(array('shareConfigId'=>$id),"GetShareConfigDetail");
            if($res['data']){
                return $res;
            }
        }
        return "false";

    }

    public function postV4shareAdd()
    {
        $search = array();
        $input = Input::only('id','platforms','useType','title','startTime','endTime','downloadUrl','ruleDescription','minStandard','timeLimit','title_s','content','rewardType','totalCount','rewardName','rewardContent','totalCount_s','card_code','card_des','backpack_code','backpack_name','shareConfigId','id_new','id_old','backgroundPicUrl','card_code_old','card_change');

        //var_dump($input);die;
        //$search['id'] = $input['id'];
        $search['platform'] = $input['platforms'];
        $search['useType'] = $input['useType'];
        $search['title'] = $input['title'];
        $search['startTime'] = $input['startTime'];
        $search['endTime'] = $input['endTime'];
        $search['downloadUrl'] = $input['downloadUrl'];
        $search['ruleDescription'] = $input['ruleDescription'];
        $search['minStandard'] = $input['minStandard'];
        $search['timeLimit'] = $input['timeLimit'];
        if(Input::file('backgroundPic')){
            $input['backgroundPic'] = Input::file('backgroundPic');
            if($input['backgroundPic']){
                $search['backgroundPic'] = MyHelpLx::save_img($input['backgroundPic']);
            }
        }

        $input['icon'] = '';
        if(Input::file('icon')){
            $input['icon'] = Input::file('icon');
            if($input['icon']){
                $input['icon'] = MyHelpLx::save_img($input['icon']);
            }
        }else{
            if($input['backgroundPicUrl']){
                $input['icon'] = $input['backgroundPicUrl'];
            }
        }

        $input['shareContext'] = array('icon'=>$input['icon'],'title'=>$input['title_s'],'content'=>$input['content']);
        foreach($input['shareContext'] as $k => $v){
            if(empty($v)){
                unset($input['shareContext'][$k]);
            }
        }
        $search['shareContext'] = json_decode(json_encode($input['shareContext']));

        $input['iconUrl'] = array();
        if(Input::file('iconUrl')) {
            $input['iconUrl'] = Input::file('iconUrl');
            $input['iconUrl'][1] = MyHelpLx::save_img($input['iconUrl'][1]);
            $input['iconUrl'][2] = MyHelpLx::save_img($input['iconUrl'][2]);
        }

        $input['giftId'] = array();
        if($input['card_code']){
            if($input['rewardType'][1] == 3) {
                $input['giftId'][1] = $input['card_code'][1];
            }
            if($input['rewardType'][2] == 3) {
                $input['giftId'][2] = $input['card_code'][2];
            }
            unset($input['card_code']);
        }
        if($input['backpack_code']){
            if($input['rewardType'][1] == 6) {
                $input['giftId'][1] = $input['backpack_code'][1];
            }
            if($input['rewardType'][2] == 6) {
                $input['giftId'][2] = $input['backpack_code'][2];
            }
            unset($input['backpack_code']);
        }

        $input['giftName'] = array();
        if($input['card_des']){
            if($input['rewardType'][1] == 3){
                $input['giftName'][1] = $input['card_des'][1];
            }
            if($input['rewardType'][2] == 3) {
                $input['giftName'][2] = $input['card_des'][2];
            }
            unset($input['card_des']);
        }
        if($input['backpack_name']){
            if($input['rewardType'][1] == 6){
                $input['giftName'][1] = $input['backpack_name'][1];
            }
            if($input['rewardType'][2] == 6) {
                $input['giftName'][2] = $input['backpack_name'][2];
            }
            unset($input['backpack_name']);
        }

        if($input['rewardType'][1] == 3){
            $input['totalCount'][1] = $input['totalCount'][1];
        }else{
            if($input['totalCount_s'][1] == ''){
                $input['totalCount'][1] = -1;
            }else{
                $input['totalCount'][1] = $input['totalCount_s'][1];
            }
        }
        if($input['rewardType'][2] == 3){
            $input['totalCount'][2] = $input['totalCount'][2];
        }else{
            if($input['totalCount_s'][2] == ''){
                $input['totalCount'][2] = -1;
            }else{
                $input['totalCount'][2] = $input['totalCount_s'][2];
            }
        }

        $input['newReward'] = array('rewardType'=>$input['rewardType'][1],'userType'=>1,'iconUrl'=>$input['iconUrl'][1],'totalCount'=>$input['totalCount'][1],'rewardName'=>$input['rewardName'][1],'rewardContent'=>$input['rewardContent'][1]);
        $input['oldReward'] = array('rewardType'=>$input['rewardType'][2],'userType'=>2,'iconUrl'=>$input['iconUrl'][2],'totalCount'=>$input['totalCount'][2],'rewardName'=>$input['rewardName'][2],'rewardContent'=>$input['rewardContent'][2]);
        if($input['rewardType'][1] == 3){
            $input['newReward']['giftName'] = $input['giftName'][1];
            $input['newReward']['giftId'] = $input['giftId'][1];
            if (isset($input['card_code_old'][1]) && $input['card_change'][1]) {
                $params_n1['requestFrom'] = $input['card_code_old'][1];
                $result_n1=ProductService::release_distributioncard($params_n1);
                if($result_n1['errorCode']!=0){
                    return $this->back($result_n1['errorDescription']);
                }
            }
            if ($input['giftId'][1] && $input['card_change'][1]) {
                $params_n['cardCode'] = $input['giftId'][1];
                $params_n['cardNumber'] = $input['totalCount'][1];
                $params_n['requestFrom'] = Utility::getUUID();
                $result_n=ProductService::distributioncard($params_n);
                if($result_n['errorCode']!=0){
                    return $this->back($result_n['errorDescription']);
                }
                $input['newReward']['giftId'] = $params_n['requestFrom'];
            }
        }elseif($input['rewardType'][1] == 6){
            $input['newReward']['giftId'] = $input['giftId'][1];
            $input['newReward']['giftName'] = $input['giftName'][1];
        }else{
            $input['newReward']['giftId'] = '';
            $input['newReward']['giftName'] = '';
        }
        
        if($input['rewardType'][2] == 3){
            $input['oldReward']['giftName'] = $input['giftName'][2];
            $input['oldReward']['giftId'] = $input['giftId'][2];
            if (isset($input['card_code_old'][2]) && $input['card_change'][2]) {
                $params_o1['requestFrom'] = $input['card_code_old'][2];
                $result_o1=ProductService::release_distributioncard($params_o1);
                if($result_o1['errorCode']!=0){
                    return $this->back($result_o1['errorDescription']);
                }
            }
            if ($input['giftId'][2] && $input['card_change'][2]) {
                $params_o['cardCode'] = $input['giftId'][2];
                $params_o['cardNumber'] = $input['totalCount'][2];
                $params_o['requestFrom'] = Utility::getUUID();
                $result_o=ProductService::distributioncard($params_o);
                if($result_o['errorCode']!=0){
                    return $this->back($result_o['errorDescription']);
                }
                $input['oldReward']['giftId'] = $params_o['requestFrom'];
            }
        }elseif($input['rewardType'][2] == 6){
            $input['oldReward']['giftId'] = $input['giftId'][2];
            $input['oldReward']['giftName'] = $input['giftName'][2];
        }else{
            $input['oldReward']['giftId'] = '';
            $input['oldReward']['giftName'] = '';
        }

        if(!empty($input['id'])){
            $search['id'] = $input['id'];
            $input['newReward']['id'] = $input['id_new'];
            $input['oldReward']['id'] = $input['id_old'];
            $input['newReward']['shareConfigId'] = $input['shareConfigId'][1];
            $input['oldReward']['shareConfigId'] = $input['shareConfigId'][2];
        }
        foreach($input['newReward'] as $k=>$v){
            if(empty($v)){
                unset($input['newReward'][$k]);
            }
        }
        foreach($input['oldReward'] as $k=>$v){
            if(empty($v)){
                unset($input['oldReward'][$k]);
            }
        }
        $search['newReward'] = json_decode(json_encode($input['newReward']));
        $search['oldReward'] = json_decode(json_encode($input['oldReward']));
        //var_dump($search);die;
        $success = V4shareService::excute3($search,"add_update_share_config");
        if($success['success']){
           return $this->redirect('v4_share/home/v4share','数据保存成功');
        }else{
            return $this->back($success['error']);
        }

//        $id = Input::get("videoId");
//        $input = Input::all();
//        //奖励数组
//        $prize_img_arr = Input::get('prize_img',array());
//        $prizeId_arr = Input::get('prizeId',array());
//        if(Input::file('prize_pic')){
//            $prize_pic = $input['prize_pic'];
//        }else{
//            $prize_pic = array();
//        }
//        unset($input['prize_pic']);
//        $prize_img_arr_new = Input::get('prize_img_new',array());
//        $prizeId_arr_new = Input::get('prizeId_new',array());
//        if(Input::file('prize_pic_new')){
//            $prize_pic_new = $input['prize_pic_new'];
//        }else{
//            $prize_pic_new = array();
//        }
//        unset($input['prize_pic_new']);
//        $icon = MyHelpLx::save_img($input['share_pic']);
//        $input['wechatShareLogoUrl'] = $icon?$icon:$input['wechatShareLogoUrl'];unset($input['share_pic']);
//
//        $icon = MyHelpLx::save_img($input['top_pic']);
//        $input['backgroundPicUrl'] = $icon?$icon:$input['backgroundPicUrl'];unset($input['top_pic']);
//        //处理奖励内容
//        $prize_list = array();
//        $input['prize_type']= Input::get('prize_type',array());
//        foreach($input['prize_type'] as $k=>$v){
//            $arr = array();
//            if($v == 'rmbb'||$v == 'youb'||$v == 'diamond'||$v == 'wheel'){
//                $danwei = self::$prizeArr[$v];
//                $arr['rewardName'] = $input['youb_num'][$k].$danwei;
//                $arr['rewardType'] = $v;
//                $arr['amount'] = $input['youb_num'][$k];
//                if($danwei == "游币"||$danwei == "钻石"||$v == '大转盘次数'){
//                    $arr['rewardName'] = (int)$input['youb_num'][$k].$danwei;
//                    $arr['amount'] = (int)$input['youb_num'][$k];
//                }
//            }else{
//                $arr['rewardName'] = $input['card_des'][$k];
//                $arr['rewardType'] = $v;
//                if($v == 'gift'){
//                    $arr['giftId'] = $input['card_code'][$k];
//                    $arr['totalCount'] = $input['num_get'][$k];
//                }elseif($v == 'good'){
//                    $arr['goodId'] = $input['card_code'][$k];
//                    $arr['totalCount'] = $input['num_get'][$k];
//                }
//            }
//
//            if($prize_pic&&$prize_pic[$k]){
//                $arr['iconUrl'] = MyHelpLx::save_img($prize_pic[$k]);
//            }else{
//                $arr['iconUrl'] = $prize_img_arr[$k];
//            }
//            if($prizeId_arr[$k]){
//                $arr['rewardId'] = $prizeId_arr[$k];
//            }
//            $prize_list[] = $arr;
//        }
//        unset($input['prize_type']);
//        unset($input['youb_num']);
//        unset($input['card_code']);
//        unset($input['card_des']);
//        unset($input['num_get']);
//        unset($input['num_auto']);
//        unset($input['prize_gid']);
//
//        //处理新用户奖励内容
//        $prize_list_new = array();
//        $input['prize_type_new']= Input::get('prize_type_new',array());
//        foreach($input['prize_type_new'] as $k=>$v){
//            $arr = array();
//            if($v == 'rmbb'||$v == 'youb'||$v == 'diamond'||$v == 'wheel'){
//                $danwei = self::$prizeArr[$v];
//                $arr['rewardName'] = $input['youb_num_new'][$k].$danwei;
//                $arr['rewardType'] = $v;
//                $arr['amount'] = $input['youb_num_new'][$k];
//            }else{
//                $arr['rewardName'] = $input['card_des_new'][$k];
//                $arr['rewardType'] = $v;
//                if($v == 'gift'){
//                    $arr['giftId'] = $input['card_code_new'][$k];
//                    $arr['totalCount'] = $input['num_get_new'][$k];
//                }elseif($v == 'good'){
//                    $arr['goodId'] = $input['card_code_new'][$k];
//                    $arr['totalCount'] = $input['num_get_new'][$k];
//                }
//            }
//
//            if($prize_pic_new&&$prize_pic_new[$k]){
//                $arr['iconUrl'] = MyHelpLx::save_img($prize_pic_new[$k]);
//            }else{
//                $arr['iconUrl'] = $prize_img_arr_new[$k];
//            }
//            if($prizeId_arr_new[$k]){
//                $arr['rewardId'] = $prizeId_arr_new[$k];
//            }
//            $prize_list_new[] = $arr;
//        }
//
//        unset($input['prize_type_new']);
//        unset($input['youb_num_new']);
//        unset($input['card_code_new']);
//        unset($input['card_des_new']);
//        unset($input['num_get_new']);
//        unset($input['num_auto_new']);
//        unset($input['prize_gid_new']);
//
//        //编辑时
//        $id = Input::get('id','');
//
//        if($prize_list){
//            $input['reward'] = json_encode(array_merge($prize_list));
//        }
//
//        if($prize_list_new){
//            $input['bait'] = json_encode(array_merge($prize_list_new));
//        }
////        $input = array_filter($input);//去空值
//        if($input['startTime']){
//            $input['startTime'] = strtotime($input['startTime']);
//        }
//        if($input['endTime']){
//            $input['endTime'] = strtotime($input['endTime']);
//        }
//
//        unset($input['id']);
//        if($id){
//            $input['shareConfigId'] = $id;
//            $success = V4shareService::excute2($input,"UpdateShareConfig");
//        }else{
//            $input['enableStatus'] = "T";
//            $success = V4shareService::excute2($input,"AddShareConfig");
//        }
//        if($success['success']){
////            $act_info = VariationActivity::getInfo($success['data']);
////            $target_id=Input::get('bestEvent');
////            $target_title = Input::get('title');
////            $platform = 'android';
////            $tpl_ename = 'android_share_tpl_activity_info';
////            $title = Input::get('share_title');
////            $icon = Input::get('wechatShareLogoUrl',"");
////            $redirect_url = 'http://share.youxiduo.com/android/share/home?hashcode='.isset($act_info['hashcode'])?$act_info['hashcode']:sha1(str_random(8));
////            $content = Input::get('wechatShareDescription');
////            $start_time = strtotime(Input::get('startTime'));
////            $end_time = strtotime(Input::get('endTime'));
////            $is_show = isset($input['is_show']) ? 1 : 0;
////            $shareId_v4 = $success['data'];
////            $res = ShareService::saveAdvInfoByTargetId($target_id,$target_title,$platform,$tpl_ename,$title,$icon,$content,$redirect_url,$start_time,$end_time,$is_show,$shareId_v4);
//
////            print_r($res);die;
//            return $this->redirect('v4_share/home/v4share','数据保存成功');
//        }else{
//            return $this->back($success['error']);
//        }
    }

    public function postTaskOpen()
    {
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        $data = array('shareConfigId'=>$id);
        if($type=="1"){
            $url = "DisableShareConfig";
        }else{
            $url = "EnableShareConfig";
        }
        $res = V4shareService::excute2($data,$url);
        if(!$res['error']){
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$type));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>$type));
        }
    }

    public function getV4shareRecord()
    {
        $data = $input = array();
        $input = Input::get();
        $search['pageSize'] = 10;
        $search['pageIndex']  = (int)Input::get('page',1);
        if(!empty($input['shareConfigId'])){
            $search['shareConfigId'] = $input['shareConfigId'];
        }
        if(!empty($input['lowerMobile'])){
            $search['lowerMobile'] = $input['lowerMobile'];
        }
        if(!empty($input['upperUid'])){
            $search['upperUid'] = $input['upperUid'];
        }
        if(!empty($input['startTime'])){
            $search['startTime'] = $input['startTime'];
        }
        if(!empty($input['endTime'])){
            $search['endTime'] = $input['endTime'];
        }
        if($input){
            $res = V4shareService::excute3(array_filter($search),"get_record_list");
            if(isset($res['data'])){
                $data['data'] = $res['data'];
                $uids = array();
                foreach($data['data'] as $row){
                    $uids[] = $row['upperUid'];
                }
                $tmp_users = UserService::getMultiUserInfoByUids($uids,'full');
                if(is_array($tmp_users)){
                    foreach($tmp_users as $user){
                        $users[$user['uid']] = $user;
                    }
                    $data['users'] = $users;
                }
            }
        }
        if($res['success']){
            $data['list'] = isset($res['data'])?$res['data']:'';
            $total = $res['totalCount'];
            $pager = Paginator::make(array(), $total, $search['pageSize']);
            $pager->appends($search);
            $data['pagelinks'] = $pager->links();
            $data['totalcount'] = $total;
            $data['finishType'] = array('0' => '未完成','1' => '完成发放失败','2'=>'完成发放成功','3'=>'完成没有奖励');
            $data['finishTypeNew'] = array('0' => '未注册','1' => '完成发放失败','2'=>'完成发放成功','3'=>'完成没有奖励','4'=>'注册超时');
            $data['search'] = $search;
        }
        return $this->display('v4share-record',$data);
    }

    public function getV4shareUsers()
    {
        $data = array();
        $input = $search = Input::get();
        $search['pageSize'] = 10;
        $pageIndex = (int)Input::get('page',1);
        $search['offset'] = ($pageIndex-1)*$search['pageSize'];
        $total = 0;
        if($input){
            $res = V4shareService::excute2(array_filter($search),"GetShareRanking");
            if($res['data']&&isset($res['data']['list'])&&isset($res['data']['totalCount'])){
                $data['data'] = $res['data']['list'];
                $total = $res['data']['totalCount'];
                $uids = array();
                foreach($data['data'] as $row){
                    $uids[] = $row['upperUid'];
                }
                $tmp_users = UserService::getMultiUserInfoByUids($uids,'full');
                if(is_array($tmp_users)){
                    foreach($tmp_users as $user){
                        $users[$user['uid']] = $user;
                    }
                    $data['users'] = $users;
                }
            }
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagination'] = MyHelpLx::pager(array(),$total,$search['pageSize'],$search);
        $data['search'] = $search;
        $data['conditions'] = array('A' => '活动','T' => '任务');
        $data['prize'] = array('youb' => '游币','gift' => '礼包','good' => '商品');
        return $this->display('v4share-users',$data);
    }

    public function getV4shareReward()
    {
        $data = array();
        $input = Input::get();
        if($input){
            $res = V4shareService::excute2($input,"GetShareRewardList");
            if($res['data']){
                $data['data'] = $res['data'];
            }
        }
        return $this->display('v4share-reward',$data);
    }

    public function postAjaxGetUrl()
    {
        $data = Input::all();
        $res = V4shareService::excute2($data,"GetShare");
        echo json_encode($res);
    }

    public function postAjaxOpen()
    {
        $res =array();
        $data = Input::all();
        //var_dump($data);die;
        if(!empty($data)){
            $res = V4shareService::excute3($data,"add_update_share_config");
        }
        //$type = $data['type'];unset($data['type']);
//        if($type == "0"){
//            $res = V4shareService::excute2($data,"EnableShareConfig");
//        }else{
//            $res = V4shareService::excute2($data,"DisableShareConfig");
//        }
//        print_r($res);die;
        echo json_encode($res);
    }

    public function getV4shareSearch()
    {
        $keyword = Input::get('keyword');
        $data = $search = $input = array();
        $pageSize = 5;
        $search['pageSize'] = $pageSize;
        $totalPage = 0;
        $input = Input::get();
        $search['title'] = $keyword;
        $search['pageIndex'] = (int)Input::get('page',1);
        $data['keyword'] = $keyword;
        $search['platform'] = Input::get("platform");
        if($search['platform'] == ''){
            unset( $search['platform']);
        }
        $res = V4shareService::excute3($search,"get_share_config_list");
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        if($res['success']){
            $data['list'] = $res['data'];
            $total = $res['totalCount'];
            foreach($data['list'] as $k=>&$v){
                if(isset($v['rewards'])){
                    $v['rewards'] = json_encode($v['rewards']);
                }
            }
        }
//        print_r($data['list']);
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;
        $html = $this->html('pop-v4share-list',$data);
        return $this->json(array('html'=>$html));
    }
    
    /**
     * excel导出
     */
    public function getDataDownload()
    {
        $data = $input = array();
        $input = Input::get();
        $search['pageSize'] = 10000;
        $search['pageIndex']  = (int)Input::get('page',1);
        if(!empty($input['shareConfigId'])){
            $search['shareConfigId'] = $input['shareConfigId'];
        }
        if(!empty($input['lowerMobile'])){
            $search['lowerMobile'] = $input['lowerMobile'];
        }
        if(!empty($input['upperUid'])){
            $search['upperUid'] = $input['upperUid'];
        }
        if(!empty($input['startTime'])){
            $search['startTime'] = $input['startTime'];
        }
        if(!empty($input['endTime'])){
            $search['endTime'] = $input['endTime'];
        }
        if($input){
            $res = V4shareService::excute3(array_filter($search),"get_record_list");
            if(isset($res['data'])){
                $data['data'] = $res['data'];
                $uids = array();
                foreach($data['data'] as $row){
                    $uids[] = $row['upperUid'];
                }
                $tmp_users = UserService::getMultiUserInfoByUids($uids,'full');
                if(is_array($tmp_users)){
                    foreach($tmp_users as $user){
                        $users[$user['uid']] = $user;
                    }
                    $data['users'] = $users;
                }
            }
        }

        $finishType = array('0' => '未完成','1' => '完成发放失败','2'=>'完成发放成功','3'=>'完成没有奖励');
        $finishTypeNew = array('0' => '未注册','1' => '完成发放失败','2'=>'完成发放成功','3'=>'完成没有奖励','4'=>'注册超时');
    
        //         print_r($data);die;
        require_once base_path() . '/libraries/PHPExcel.php';
        $excel = new \PHPExcel();
        $excel->setActiveSheetIndex(0);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->setTitle('自分享奖励记录');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(80);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(80);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(80);
        $excel->getActiveSheet()->setCellValue('A1','老用户');
        $excel->getActiveSheet()->setCellValue('B1','老用户奖励');
        $excel->getActiveSheet()->setCellValue('C1','老用户完成时间');
        $excel->getActiveSheet()->setCellValue('D1','新用户完成时间');
        $excel->getActiveSheet()->setCellValue('E1','新用户');
        $excel->getActiveSheet()->setCellValue('F1','新用户奖励');
        $excel->getActiveSheet()->setCellValue('G1','参与时间');
        $excel->getActiveSheet()->setCellValue('H1','老用户完成状态');
        $excel->getActiveSheet()->setCellValue('I1','新用户完成状态');
        $excel->getActiveSheet()->freezePane('A2');
        foreach($data['data'] as $index=>$row){
            $uid = isset($row['upperUid'])?$row['upperUid']:'';
            $nickname = isset($users[$uid])?$users[$uid]['nickname']:'';
            $mobile = isset($users[$uid])?$users[$uid]['mobile']:'';
            $upperRewardName = isset($row['upperRewardName'])?$row['upperRewardName']:'';
            $uFinishTime = isset($row['uFinishTime'])?$row['uFinishTime']:'';
            $lFinishTime = isset($row['lFinishTime'])?$row['lFinishTime']:'';
            $lowerMobile = isset($row['lowerMobile'])?$row['lowerMobile']:'';
            $lowerRewardName = isset($row['lowerRewardName'])?$row['lowerRewardName']:'';
            $joinTime = isset($row['joinTime'])?$row['joinTime']:'';
            $uSendRewardStatus = isset($finishType[$row['uSendRewardStatus']])?$finishType[$row['uSendRewardStatus']]:'';
            $lSendRewardStatus = isset($finishTypeNew[$row['lSendRewardStatus']])?$finishTypeNew[$row['lSendRewardStatus']]:'';
    
            $excel->getActiveSheet()->setCellValue('A'.($index+2), $uid);
            $excel->getActiveSheet()->setCellValue('B'.($index+2), $upperRewardName);
            $excel->getActiveSheet()->setCellValue('C'.($index+2), $uFinishTime);
            $excel->getActiveSheet()->setCellValue('D'.($index+2), $lFinishTime);
            $excel->getActiveSheet()->setCellValue('E'.($index+2), $lowerMobile);
            $excel->getActiveSheet()->setCellValue('F'.($index+2), $lowerRewardName);
            $excel->getActiveSheet()->setCellValue('G'.($index+2), $joinTime);
            $excel->getActiveSheet()->setCellValue('H'.($index+2), $uSendRewardStatus);
            $excel->getActiveSheet()->setCellValue('I'.($index+2), $lSendRewardStatus);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'自分享奖励记录.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
        $writer->save('php://output');
    }
    
}