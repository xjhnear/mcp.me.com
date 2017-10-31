<?php
namespace modules\tuiguang\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Tuiguang\TuiguangService;
use Youxiduo\V4\User\UserService;
use Yxd\Services\Cms\GameService;
use Illuminate\Support\Facades\Config;
use modules\game\models\GameModel;


class HomeController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'tuiguang';
    }

    public function getCommission()
    {
        $data = $search = $input = array();
        $data['cashResult'] = array(''=>'全部','0'=>'未处理','1'=>'成功','2'=>'失败');
        $pageSize = 10;
        $total = 0;
        $input = Input::get();
        //$search = array_filter($input);//array_filter去空函数
        $pageIndex = (int)Input::get('page',1);
        $search['pageIndex'] = $pageIndex;
        $search['beginTime'] = Input::get('beginTime','');
        $search['endTime'] = Input::get('endTime','');
        $search['accountId'] = Input::get('accountId','');
        
        if(Input::get('accountMobile','')){
            $search['accountId'] = UserService::getUserIdByMobile(Input::get('accountMobile',''));
        }
        $search['sortField'] = 'RequireTime';
        $search['cashResult'] = Input::get('cashResult','');
        $search['currencyType']=1;
        $search['tradeFrom']='YXD_APP';
        $res = TuiguangService::excute($search,"cashlist");
       /*  var_dump($res['data']['list']);
        die(); */
        $search['accountMobile'] = Input::get('accountMobile','');
        if($res['success']){
            $data['datalist'] = $res['data']['list'];
            $data['totalAmount'] = $res['data']['totalAmount'];
            $total = $res['count'];

            $uids = array();
            foreach($res['data']['list'] as $row){
                $uids[] = $row['accountId'];
            }
            $tmp_users = UserService::getMultiUserInfoByUids($uids,'full');
            if(is_array($tmp_users)){
                foreach($tmp_users as $user){
                    $users[$user['uid']] = $user;
                }
                $data['users'] = $users;
            }
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;//回调函数
        $data['count']=isset($res['count'])?$res['count']:'';
        return $this->display('commission-list',$data);
    }

    public function getHandleCommission()
    {
        $data['data'] = Input::get();
        $data['result'] = array('1'=>'处理成功','2'=>'处理失败');
        $data['payType'] = array('ALIPAY '=>'支付宝转账');
        return $this->display('handle-commission',$data);
    }

//推广用户列表
    public function getUser()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $count = 0;
        $input = Input::get(); 
        $search = array_filter($input);//array_filter去空函数
        $pageIndex = (int)Input::get('page',1);
        $userActive ="true";
        $search['userActive'] = $userActive;
        $search['pageIndex'] = $pageIndex;
        $search['pageSize'] = $pageSize;
        $search['timeBegin'] = Input::get('timeBegin','');
        $search['timeEnd'] = Input::get('timeEnd','');
        $search['platform'] ="android";
        $search['actionId']='YXD_APP';
        $res = TuiguangService::excute($search,"promoteruser");
        if($res['success']){
            $data['datalist'] = $res['data'];
            $count = $res['count'];
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$count,$pageSize,$search);
        $data['search'] = $search;//回调函数
        return $this->display('user-list',$data);
    }

//推广员列表
    public function getPromoter()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $total = 0;
        $input = Input::get();
        $search = array_filter($input);//array_filter去空函数
        $pageIndex = (int)Input::get('page',1);
        $search['pageIndex'] = $pageIndex;
        $search['pageSize'] = $pageSize;
        $search['timeBegin'] = Input::get('timeBegin','');
        $search['timeEnd'] = Input::get('timeEnd','');
        $search['platform'] ="android";
        $res = TuiguangService::excute($search,"promoter");
        if($res['success']){
         foreach($res['data'] as &$v){
            $val['accountId']=$v['promoterYxdId'];
            $row= TuiguangService::v4excute($val,"account/query");
            
             if(count($row['data'])==0){
                 $v['balance']=0;
                 $v['cashTotal']=0;
                 $v['alipayAccount']='';
             }else {
                 $v['balance']=($row['data'][0]['balance'])/100;
                 $v['cashTotal']=($row['data'][0]['cashTotal'])/100;
                 $v['alipayAccount']=isset($row['data'][0]['alipayAccount'])?$row['data'][0]['alipayAccount']:'';
             }
         }
        }
        if($res['success'])
        {
            $data['datalist'] = $res['data'];
            $total = $res['count'];                           
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;//回调函数
        $data['count']=isset($res['count'])?$res['count']:'';
       
        return $this->display('promoter-list',$data);
    }
//推广员分成设置
    public function  getPromoterAdd(){
        $data=array();
        $data['promoterYxdId']=Input::get('promoterYxdId','');
        $data['promoterMobile']=Input::get('promoterMobile','');
        $data['promoterNum']=Input::get('promoterNum','');
        $data['promoterId']=Input::get('promoterId','');
        $search['promoterId']=Input::get('promoterId','');
        $search['actionId']='SDK_ANDROID';
        $search['channelActive']='TRUE';
        $res = TuiguangService::v4excute($search,"promoterGame");
        $data['atask']=$res['data'];
        //var_dump($data['atask']);die();
        return $this->display('promoter-add',$data);
    }    
   public function postPromoterAdd(){ 
      $search=array();
      $search=Input::all();
      $count=isset($search['count'])?$search['count']:'';
      $countl=isset($search['countl'])?$search['countl']:'';   
      if(!empty($count)){
      for ($i=0; $i<$count; $i++) {  
          $data['gameId']=0;
          $search["rmbReward".$i]=str_replace(' ','',$search["rmbReward".$i]);
              if($search["rmbReward".$i]==''){
                  $search["feeValueShow"]=$search["rmbRate".$i];
                  $search['upperLimit']=$search["rmb".$i];
                  $data['gamePercentageBeanList'][$i]=array(
                      'feeValueShow'=>$search["feeValueShow"],
                      //'feeValue' =>$search["feeValueShow"],
                      //'percentageId'=>$search["percentageId".$i],
                      //'isActive'=>$search["isActive".$i],
                      'feeType' =>$search["feeType".$i],
                      'currencyType'=>2,
                      'upperLimitShow'=>$search["upperLimit"],
                      'translatType'=>2,
                      'actionId'=>'SDK_ANDROID',
                      'exclusive'=>'true',
                      'gameRefId'=>$search['game_id'.$i],
                      'gameIcon'=>$search['gameIcon'.$i],
                      'gameName'=>$search['game_name'.$i],
                      'gameDesc' =>$search['game_name'.$i],
                      'exclusivePromoterId' =>$search["exclusivePromoterId"]
                  );
              }else{
                  $search["feeValueShow"]=$search["rmbReward".$i];
                  $data['gamePercentageBeanList'][$i]=array(
                      'feeValueShow'=>$search["feeValueShow"],
                      //'feeValue' =>$search["feeValueShow"],
                      //'percentageId'=>$search["percentageId".$i],
                      //'isActive'=>$search["isActive".$i],
                      'feeType' =>$search["feeType".$i],
                      'currencyType'=>2,
                      'translatType'=>2,
                      'actionId'=>'SDK_ANDROID',
                      'exclusive'=>'true',
                      'gameRefId'=>$search['game_id'.$i],
                      'gameIcon'=>$search['gameIcon'.$i],
                      'gameName'=>$search['game_name'.$i],
                      'gameDesc' =>$search['game_name'.$i],
                      'exclusivePromoterId' =>$search["exclusivePromoterId"]
                  );
              }
    
      }
      }
     // var_dump($data);
     if(!empty($countl)){
        // $data=array();
         for ($j=0; $j<$countl; $j++) {
             $data['gameId']=!empty($search['gameIdl'.$j])?$search['gameIdl'.$j]:0;
                 $search["rmbRewardl".$j]=str_replace(' ','',$search["rmbRewardl".$j]);
                 if($search["rmbRewardl".$j]==''){
                     $search["feeValueShow"]=str_replace(' ','',$search["rmbRatel".$j]);
                     $data['gamePercentageBeanList'][$j+$count]=array(                   
                     'feeValueShow'=>$search["feeValueShow"],
                     //'feeValue' =>$search["feeValueShow"],
                     'percentageId'=>$search["percentageIdl".$j],
                     'isActive'=>$search["isActivel".$j],
                     'feeType' =>$search["feeTypel".$j],
                     'currencyType'=>2,
                     'upperLimitShow'=>$search["rmbl".$j],
                     'translatType'=>2,
                     'actionId'=>'SDK_ANDROID',
                     'exclusive'=>'true',
                     'gameRefId'=>$search['game_idl'.$j],
                     'gameIcon'=>$search['gameIconl'.$j],
                     'gameName'=>$search['game_namel'.$j],
                     'gameDesc' =>$search['game_namel'.$j],
                     'gameId'=>!empty($search['gameIdl'.$j])?$search['gameIdl'.$j]:0,
                     'exclusivePromoterId' =>$search["exclusivePromoterId"]
                 );
             }else{
                 $search["feeValueShow"]=str_replace(' ','',$search["rmbRewardl".$j]);
                 $data['gamePercentageBeanList'][$j+$count]=array(             
                     'feeValueShow'=>$search["feeValueShow"],
                    // 'feeValue' =>$search["feeValueShow"],
                     'percentageId'=>$search["percentageIdl".$j],
                     'isActive'=>$search["isActivel".$j],
                     'feeType' =>$search["feeTypel".$j],
                     'currencyType'=>2,
                     'translatType'=>2,
                     'actionId'=>'SDK_ANDROID',
                     'exclusive'=>'true',
                     'gameRefId'=>$search['game_idl'.$j],
                     'gameIcon'=>$search['gameIconl'.$j],
                     'gameName'=>$search['game_namel'.$j],
                     'gameDesc' =>$search['game_namel'.$j],
                     'gameId'=>!empty($search['gameIdl'.$j])?$search['gameIdl'.$j]:0,
                     'exclusivePromoterId' =>$search["exclusivePromoterId"]
                 );
             }
         
         }
         
         
     }
     $res = TuiguangService::v4excute($data,"updateGame");
     //var_dump($data); die();
     if($res['success']){
         return $this->redirect('tuiguang/home/promoter','操作成功');
     }else{
         return $this->back($res['error']);
     }
    /*  if($count!=0 && empty($countl)){   
      if($res['success']){
          return $this->redirect('tuiguang/home/promoter','操作成功');
      }else{
          return $this->back($res['error']);
      }
     }
     if($count==0 && $countl!=0){
         if($res1['success']){
             return $this->redirect('tuiguang/home/promoter','操作成功');
         }else{
             return $this->back($res1['error']);
         }
     }
     if($count!=0 && $countl!=0){
         if($res['success']){
             return $this->redirect('tuiguang/home/promoter','操作成功');
         }else{
             return $this->back($res['error']);
         }
     } */
   }
    
//现金流水
    public function getMoneyRecord()
    {
        $data = $search = $input = array();
        $data['actionId'] = array('0'=>'YXD_APP','1'=>'Android SDK');
        $pageSize = 10;
        $total = 0;
        $input = Input::get();
        $search = array_filter($input);//array_filter去空函数
        $pageIndex = (int)Input::get('page',1);
        $search['pageIndex'] = $pageIndex;
        $search['pageSize'] = $pageSize;
        $search['timeBegin'] = Input::get('timeBegin','');
        $search['timeEnd'] = Input::get('timeEnd','');
        $search['affName'] = Input::get('affName','');
        $search['currencyType'] = 2;
        $search['isPromotion'] = 'TRUE';
        $search['actionId']=Input::get('actionId','');
        if($search['actionId']==0){
            $search['actionId']="YXD_APP";
        }else{
            $search['actionId']="SDK_ANDROID";    
        }
        $res = TuiguangService::excute($search,"money");

        if($res['success']){
            $data['datalist'] = $res['data'] ['list'];
            $data['totalAmount'] = $res['data']['totalAmount'];
            $total = $res['count'];
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;//回调函数
        $data['feeAmount']=$res['data']['feeAmount'];
        $data['count']=isset($res['count'])?$res['count']:'';
        return $this->display('money-list',$data);
    }
//游币流水
    public function getYbRecord()
    {
        $data = $search = $input = array();
        $data['actionId'] = array('0'=>'YXD_APP','1'=>'Android SDK');
        $pageSize = 10;
        $total = 0;
        $input = Input::get();
        $search = array_filter($input);//array_filter去空函数
        $pageIndex = (int)Input::get('page',1);
        $search['pageIndex'] = $pageIndex;
        $search['pageSize'] = $pageSize;
        $search['currencyType'] = 0;
        $search['timeBegin'] = Input::get('timeBegin','');
        $search['timeEnd'] = Input::get('timeEnd','');
        $search['affName'] = Input::get('affName','');
        $search['isPromotion'] = 'TRUE';
        $search['actionId']=Input::get('actionId','');
        if($search['actionId']==0){
            $search['actionId']="YXD_APP";
        }else{
            $search['actionId']="SDK_ANDROID";
        }
       // var_dump($search);die();
        $res = TuiguangService::excute($search,"yb");
       
        if($res['success']){
            $data['datalist'] = $res['data'] ['list'];
            $data['totalAmount'] = $res['data']['totalAmount'];
            $total = $res['count'];
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;//回调函数
        $data['search'] = $search;
        $data['feeAmount']=$res['data']['feeAmount'];
        $data['count']=isset($res['count'])?$res['count']:'';
        return $this->display('yb-list',$data);
    }

    public function getCommissionSetup()
    {
        $data['data'] = Input::get();
        $data['task'] = $data['sdk'] = $data['trade'] =array();
        $res_acc = TuiguangService::excute(array('confKey'=>'cashLimit'),"account/configlist");
        if($res_acc['success']&&$res_acc['data']){
            $data['cash'] = $res_acc['data']*0.01;
        }
       
        $res_rule = TuiguangService::excute(array('confKey'=>'ruleImg'),"config/list");

        if($res_rule['success']&&$res_rule['data']){
            $data['icon'] = $res_rule['data'];
        }
        $res =  TuiguangService::excute(array(),"default/list");
        //var_dump($res);die();
        if($res['success']&&$res['data']){
            foreach($res['data'] as $k=>$v){
                if($v['actionId']=="YXD_APP"){
                    if($v['currencyType']=="2"){
                        if($v['feeType']=="0"){
                            $data['task']['feeType'] = "0";
                            $data['task']['rmbReward'] = $v['feeValueShow'];
                            $data['task']['rmbRate'] = "";
                        }else{
                            $data['task']['feeType'] = "1";
                            $data['task']['rmbReward'] = "";
                            $data['task']['rmbRate'] = $v['feeValueShow'];
                            $data['task']['upperLimitShow'] = $v['upperLimitShow'];
                        }
                        $data['task']['defaultId1'] = $v['defaultId'];
                    }else{
                        $data['task']['yb'] = $v['feeValueShow'];
                        $data['task']['defaultId2'] = $v['defaultId'];
                    }

                }elseif($v['actionId']=="SDK_ANDROID"){
                    if($v['feeType']=="0"){
                        $data['sdk']['feeType'] = "0";
                        $data['sdk']['rmbReward'] = $v['feeValueShow'];
                        $data['sdk']['rmbRate'] = "";
                    }else{
                        $data['sdk']['feeType'] = "1";
                        $data['sdk']['rmbReward'] = "";
                        $data['sdk']['rmbRate'] = $v['feeValueShow'];
                        $data['sdk']['upperLimitShow'] = $v['upperLimitShow'];
                    }
                    $data['sdk']['defaultId'] = $v['defaultId'];

                }elseif($v['actionId']=="TRADE_365_IOS"){
                    if($v['feeType']=="0"){
                        $data['trade']['feeType'] = "0";
                        $data['trade']['rmbReward'] = $v['feeValueShow'];
                        $data['trade']['rmbRate'] = "";
                    }else{
                        $data['trade']['feeType'] = "1";
                        $data['trade']['rmbReward'] = "";
                        $data['trade']['rmbRate'] = $v['feeValueShow'];
                    }
                    $data['trade']['defaultId'] = $v['defaultId'];
                    $data['trade']['upperLimitShow'] = $v['upperLimitShow'];
                }
            }
        }
        return $this->display('commission-setup',$data);
    }

    public function postAjaxCommissionSetup(){
        $data = Input::all();
        $res = TuiguangService::excute($data,"default/update","false");

        echo json_encode($res);
    }

    public function postAjaxCashSetup(){
        $data = Input::all();
        $res = TuiguangService::excute($data,"account/setconfig","false");
        echo json_encode($res);
    }

    public function postAjaxRuleSetup(){
        $data = Input::all();
        $res = TuiguangService::excute($data,"config/update","false");
        echo json_encode($res);
    }
    public function postAjaxUploadImg()
    {
        if(Input::file('prize_pic')){
           $icon = MyHelpLx::save_img(Input::file('prize_pic'));
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$icon));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>"图片丢失"));
        }
    }

    public function postAjaxOpen()
    {
        $data = Input::all();
        $res=TuiguangService::excute($data,"update_promoter","false");
        echo json_encode($res);
    }

    public function postAjaxDo()
    {
        $data = Input::all();
        $url = $data['url'];unset($data['url']);
        $uid=$this->getSessionData('youxiduo_admin');
        if(isset($uid['username'])){
            $data['modifier'] = $uid['username'];
        }
        $res=TuiguangService::excute($data,$url,"false");
        echo json_encode($res);
    }
 
   public function getTransactionAndroid(){
       $data = $search = $input = array();
       $pageSize = 10;
       $total = 0;
       $input = Input::get();
       $search = array_filter($input);//array_filter去空函数
       $pageIndex = (int)Input::get('page',1);
       $search['pageIndex'] = $pageIndex;
       $search['pageSize'] = $pageSize;
       $search['currencyType'] = 2;
       $search['gameRefId'] = Input::get('gameRefId','');
       $search['gameName'] = Input::get('gameName','');
       $search['translatType'] = 2;
       $search['actionId']='SDK_ANDROID';
       $res = TuiguangService::v4excute($search,"queryGame");
       if($res['success']){
           $data['datalist'] = $res['data'] ;
           // $data['totalAmount'] = $res['data']['totalAmount'];
           $total = $res['count'];
       }
       unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
       $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
       $data['search'] = $search;//回调函数
       $data['search'] = $search;
       return $this->display('transaction-android',$data);
   }
 //添加游戏  
  public function getAddGame(){
      $data=array();
      $data['gameRefId']=Input::get('gameId','');
      $data['gameName']=Input::get('gameName','');
      $data['gameIcon']=Input::get('gameIcon','');
      $data['displayIndex']=0;
      $data['currencyType']=2;
      $data['translatType']=2;
      $data['actionId']='SDK_ANDROID';
 /*    
      $res = TuiguangService::excute(array(),"default/list");
      foreach($res['data'] as $k=>$v){
          if($v['actionId']=="SDK"){
              $data['gamePercentageBeanList'][0]=array(
                  'currencyType'=>2,
                  'translatType'=>2,
                  'upperLimit'=>999999999,
                  'gameRefId'=>Input::get('gameId',''),
                  'gameName' =>Input::get('gameName',''),
                  'actionId'=>'SDK',
                  'feeType'=>$v['feeType'],
                  'feeValue'=>$v['feeValue'],
                  'feeValueShow'=>$v['feeValueShow']    
              );
          }              
      } */
      $res1 = TuiguangService::v4excute($data,"addGame");
      if($res1['success']){
	       return 'ok';
	  }else{
	       return 'no';
	  } 
  }
 //sdk设置游戏  渠道查询
 public function getTransactionUpdata(){
     $data=array();
     $data=Input::all();
     $search=array();
     $search['pageSize']=20000;
     $search['channelActive']='TRUE';
     $res = TuiguangService::excute3($search,"GetAgentInfoList");
     //var_dump($res['result']);die();
     $data['actionId'] = array();
       foreach($res['result']['list'] as $k=>$v){
           $data['actionId'][$v['agentNum']]=$v['agentName'] ;
         }
     $search['actionId']='SDK_ANDROID';
     $search['gameId']=$data['gameId'] ;
     $row = TuiguangService::v4excute($search,"queryGame");
     if($row['success']){
     $data['atask']=$row['data'][0];
     }
     //var_dump($data);die();
     return $this->display('transaction-updata',$data);
 }
 //sdk设置游戏渠道分成
 public function postTransactionUpdata(){
     $search=Input::all();
     $data=array();
     $data['gameId']=$search['gameId'];
     $data['gameRefId']=$search['game_id'];
     $data['gameName']=$search['game_name'];
     $data['displayIndex']=0;
     $data['gameIcon']=$search['gameIcon'];
     $data['currencyType']=2;
     $data['translatType']=2;
     $data['actionId']='SDK_ANDROID';
     $count=isset($search['count'])?$search['count']:'';
     $countl=isset($search['countl'])?$search['countl']:'';
     $data['percentageBeanList']=array();
     if(!empty($count)){
         for ($i=0; $i<$count; $i++) {
             if($search["channelName".$i]!=''){
                 if($search["rmbReward".$i]==''){
                     $search["feeValueShow"]=$search["rmbRate".$i];
                     $search['upperLimit']=$search["rmb".$i];
                     $data['percentageBeanList'][$i]=array(
                         'channelName'=>$search["channelName".$i],
                         'channelRefId'=>$search["channelId".$i],
                         'feeValueShow'=>$search["feeValueShow"],
                         //'percentageId'=>$search["percentageId".$i],
                         //'isActive'=>$search["isActive".$i],
                         'feeType' =>$search["feeType".$i],
                         'currencyType'=>2,
                         'upperLimitShow'=>$search["upperLimit"],
                         'translatType'=>2
                     );
                 }else{
                     $search["feeValueShow"]=$search["rmbReward".$i];
                     $data['percentageBeanList'][$i]=array(
                         'channelName'=>$search["channelName".$i],
                         'channelRefId'=>$search["channelId".$i],
                         'feeValueShow'=>$search["feeValueShow"],
                         //'percentageId'=>$search["percentageId".$i],
                         //'isActive'=>$search["isActive".$i],
                         'feeType' =>$search["feeType".$i],
                         'currencyType'=>2,
                         'translatType'=>2
                     );
                 }
             }
         }
     }
     //var_Dump($data);die();
     //var_Dump($search);die();
     if(!empty($countl)){
         for ($j=0; $j<$countl; $j++) {
             $search["rmbRewardl".$j]=str_replace(' ','',$search["rmbRewardl".$j]);
             if($search["rmbRewardl".$j]==''){
                 $search["feeValueShow"]=str_replace(' ','',$search["rmbRatel".$j]);
                 $data['percentageBeanList'][$j+$count]=array(
                     'channelName'=>$search["channelNamel".$j],
                     'channelRefId'=>$search["channelIdl".$j],
                     'feeValueShow'=>$search["feeValueShow"],
                     'percentageId'=>$search["percentageId".$j],
                     'translatType'=>2,
                     'feeType' =>$search["feeTypel".$j],
                     'currencyType'=>2,
                     'upperLimitShow'=>$search["rmbl".$j],
                     'isActive'=>$search["isActive".$j]
 
                 );
                  
             }else{
                 $search["feeValueShow"]=str_replace(' ','',$search["rmbRewardl".$j]);
                 $data['percentageBeanList'][$j+$count]=array(
                     'channelName'=>$search["channelNamel".$j],
                     'channelRefId'=>$search["channelIdl".$j],
                     'feeValueShow'=>$search["feeValueShow"],
                     'percentageId'=>$search["percentageId".$j],
                     'translatType'=>2,
                     'feeType' =>$search["feeTypel".$j],
                     'currencyType'=>2,
                     // 'upperLimit'=>999999999,
                     'isActive'=>$search["isActive".$j]
 
                 );
             }
              
         }
     }
     //var_Dump($data);die();
     $res = TuiguangService::v4excute($data,"updateGame");
 
     if($res['success']){
         return $this->redirect('tuiguang/home/transaction-android','操作成功');
     }else{
         return $this->back($res['error']);
     }
 }
  
  //365交易分成
  public function getTransactionList(){
     $data = $search = $input = array();
       $pageSize = 10;
       $total = 0;
       $input = Input::get();
       $search = array_filter($input);//array_filter去空函数
       $pageIndex = (int)Input::get('page',1);
       $search['pageIndex'] = $pageIndex;
       $search['pageSize'] = $pageSize;
       $search['currencyType'] = 2;
       $search['gameRefId'] = Input::get('gameId','');
       $search['gameName'] = Input::get('gameName','');
       $search['transType'] = 1;
       $search['actionId']='TRADE_365_IOS';
       $res = TuiguangService::v4excute($search,"queryGame");
       if($res['success']){
           $data['datalist'] = $res['data'] ;
           // $data['totalAmount'] = $res['data']['totalAmount'];
           $total = $res['count'];
       }
       unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
       $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
       $data['search'] = $search;//回调函数
       $data['search'] = $search;
       //var_dump($data);die();
      return $this->display('transaction-list',$data);   
  }
  public function getAddGames(){
      $data=array();
      $data['gameRefId']=Input::get('gameId','');
      $data['gameName']=Input::get('gameName','');
      $data['gameIcon']=Input::get('gameIcon','');
      $data['displayIndex']=0;
      $data['currencyType']=2;
      $data['translatType']=1;
      $data['actionId']='TRADE_365_IOS';    
      $res1 = TuiguangService::v4excute($data,"addGame");
      if($res1['success']){
          return 'ok';
      }else{
          return 'no';
      }   
  }
  public function getTransactionListUpdata(){
      $data=array();
      $data=Input::all();
      $search=array();
      $search['pageSize']=20000;
      $res = TuiguangService::excute3($search,"GetAgentInfoList");
      $data['actionId'] = array();
      foreach($res['result']['list'] as $k=>$v){
          $data['actionId'][$k]=$v['agentName'] ;
           
      }
      $search['actionId']='TRADE_365_IOS';
      $search['gameId']=$data['gameId'] ;
      $row = TuiguangService::v4excute($search,"queryGame");
     
      $data['atask']=$row['data'][0];

     // var_dump($data['atask']);die();
      return $this->display('transaction-list-updata',$data);
  }
  public function postTransactionListUpdata(){
      $search=Input::all();
      $data=array();
      $data['gameId']=$search['gameId'];
      $data['gameRefId']=$search['game_id'];
      $data['gameName']=$search['game_name'];
      $data['displayIndex']=0;
      $data['gameIcon']=$search['gameIcon'];
      $data['currencyType']=2;
      $data['translatType']=1;
      $data['actionId']='TRADE_365_IOS';
      $count=isset($search['count'])?$search['count']:'';
      $countl=isset($search['countl'])?$search['countl']:'';
      $data['percentageBeanList']=array();
      if(!empty($count)){
          for ($i=0; $i<$count; $i++) {
              if($search["channelName".$i]!=''){
                  if($search["rmbReward".$i]==''){
                      $search["feeValueShow"]=$search["rmbRate".$i];
                      $search['upperLimitShow']=$search["rmb".$i];
                      $data['percentageBeanList'][$i]=array(
                          'channelName'=>$search["channelName".$i],
                          'channelRefId'=>$search["channelId".$i],
                          'feeValueShow'=>$search["feeValueShow"],
                          //'percentageId'=>$search["percentageId".$i],
                          //'isActive'=>$search["isActive".$i],
                          'feeType' =>1,
                          'currencyType'=>2,
                          'upperLimitShow'=>$search["upperLimitShow"],
                          'translatType'=>1
                      );
                  }else{
                      $search["feeValueShow"]=$search["rmbReward".$i];
                      $data['percentageBeanList'][$i]=array(
                          'channelName'=>$search["channelName".$i],
                          'channelRefId'=>$search["channelId".$i],
                          'feeValueShow'=>$search["feeValueShow"],
                          //'percentageId'=>$search["percentageId".$i],
                          //'isActive'=>$search["isActive".$i],
                          'feeType' =>1,
                          'currencyType'=>2,
                          'translatType'=>1
                      );
                  }
              }
          }
      }
      if(!empty($countl)){
          for ($j=0; $j<$countl; $j++) {
              $search["rmbRewardl".$j]=str_replace(' ','',$search["rmbRewardl".$j]);
              if($search["rmbRewardl".$j]==''){
                  $search["feeValueShow"]=str_replace(' ','',$search["rmbRatel".$j]);
                  $data['percentageBeanList'][$j+$count]=array(
                      'channelName'=>$search["channelNamel".$j],
                      'channelRefId'=>$search["channelIdl".$j],
                      'feeValueShow'=>$search["feeValueShow"],
                      'percentageId'=>$search["percentageId".$j],
                      'translatType'=>1,
                      'feeType' =>1,
                      'currencyType'=>2,
                      'upperLimitShow'=>$search["rmbl".$j],
                      'isActive'=>$search["isActive".$j]
  
                  );
                   
              }else{
                  $search["feeValueShow"]=str_replace(' ','',$search["rmbRewardl".$j]);
                  $data['percentageBeanList'][$j+$count]=array(
                      'channelName'=>$search["channelNamel".$j],
                      'channelRefId'=>$search["channelIdl".$j],
                      'feeValueShow'=>$search["feeValueShow"],
                      'percentageId'=>$search["percentageId".$j],
                      'translatType'=>1,
                      'feeType' =>1,
                      'currencyType'=>2,
                      // 'upperLimit'=>999999999,
                      'isActive'=>$search["isActive".$j]
  
                  );
              }
               
          }
      }
      $res = TuiguangService::v4excute($data,"updateGame");
      //var_Dump($data);die();
      if($res['success']){
          return $this->redirect('tuiguang/home/transaction-list','操作成功');
      }else{
          return $this->back($res['error']);
      }
  }
  public function getGameSearch(){
      $search = array();
      $pageSize =6;
      $total = 0;
      $input = Input::get();
      $data['i']=Input::get('i','0');
      $data['keyword'] = Input::get('keyword','');
      $keytype = $data['keytype'] = Input::get('keytype');
      $pageIndex = (int)Input::get('page',1);
      $search['pageSize'] = $pageSize;
      $search['offset'] = ($pageIndex-1)*$pageSize;
      if($keytype == 'id'){
          $search['appId']=Input::get('keyword','');
      }elseif($keytype == 'gname'){
          $search['appName']=Input::get('keyword','');
      }


      $result= TuiguangService::Excute3($search,"GetAdminAppList");
      $data['games'] =$result['result']['list'];
      if($result){
          $total=$result['result']['totalCount'];
      }
      //var_dump($result['result']['list']);die();
      $search['i'] =Input::get('i','0');
      unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
      $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
      $data['search'] = $search;//回调函数
      $html = $this->html('pop-game-list',$data);
      return $this->json(array('html'=>$html));
  }
  
}