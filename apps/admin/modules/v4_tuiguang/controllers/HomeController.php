<?php
namespace modules\v4_tuiguang\controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\MyHelpLx;
use Youxiduo\Tuiguang\TuiguangService;
use Youxiduo\V4\User\UserService;
use Illuminate\Redis\Database;


class HomeController extends BackendController
{
    public function _initialize()
    {
        $this->current_module = 'v4_tuiguang';
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

        $search['currencyType'] = 1;
//         $search['platform'] = 'ios';
//         $search['from'] = 'glwzry';
        if(Input::get('accountMobile','')){
            $search['accountId'] = UserService::getUserIdByMobile(Input::get('accountMobile',''));
        }
        $search['accountId'] = Input::get('accountId','')?'ios'.Input::get('accountId',''):'';
        $search['sortField'] = 'RequireTime';
        $search['cashResult'] = Input::get('cashResult','');
        $res = TuiguangService::v4excute($search,"cashlist");
        $search['accountMobile'] = Input::get('accountMobile','');

        if($search['accountId']){
            $search['accountId'] = str_replace('ios','',$search['accountId']);
        }

        if($res['success']){
            $data['datalist'] = MyHelpLx::insertUserhtmlIntoRes($res['data']['list']);
            $data['totalAmount'] = $res['data']['totalAmount'];
            $total = $res['count'];
//            $uids = array();
//            foreach($res['data']['list'] as $row){
//                $uids[] = $row['accountId'];
//            }
//            $tmp_users = UserService::getMultiUserInfoByUids($uids,'full');
//            print_r($tmp_users);
//            if(is_array($tmp_users)){
//                foreach($tmp_users as $user){
//                    $users[$user['uid']] = $user;
//                }
//                $data['users'] = $users;
//            }
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;//回调函数
       
        return $this->display('commission-list',$data);

    }

    public function getHandleCommission()
    {
        $data['data'] = Input::get();

        $data['result'] = array('1'=>'处理成功','2'=>'处理失败');
        $data['payType'] = array('ALIPAY '=>'支付宝转账');
        return $this->display('handle-commission',$data);
    }

    public function postHandleCommission()
    {
        $input = Input::all();

        $img1 = MyHelpLx::save_img($input['picUrl']);
        $img2 = MyHelpLx::save_img($input['videoPic']);
        $input['picUrl'] =$img1 ? $img1:$input['img'];unset($input['img']);
        $input['videoPic'] =$img2 ? $img2:$input['img2'];unset($input['img2']);
        $res= ESportsService::v4excute($input,"SaveIndexGameVideo",false);
        if($res){
            return $this->redirect('yxvl_eSports/home/game-video','添加成功');
        }else{
            return $this->back($res['error']);
        }
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
        $search['platform'] ="ios";
        $search['actionId']='TRADE_365_IOS';
        $res = TuiguangService::v4excute($search,"promoteruser/ios");
        if($res['success']){
            $data['datalist'] = $res['data'];
            $count = $res['count'];
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$count,$pageSize,$search);
        $data['search'] = $search;//回调函数
//        $data['feeAmount']=$res['data']['feeAmount'];
        $data['count']=isset($res['count'])?$res['count']:'';
        return $this->display('user-list',$data);
    }

//推广员列表
    public function getPromoter()
    {
        $data = $search = $input = array();
        $pageSize = 10;
        $total = 0;
        $input = Input::get();

        //var_dump($input);die();
        if(isset($input['file_url'])){
            if($input['file_url']){
            $file=file_get_contents($input['file_url']);
            $file=str_replace("\r\n",',',$file);
            $file=rtrim($file,',');
            $search['promoterYxdId']=$file;
            }
        }
        $search = array_filter($input);//array_filter去空函数
        $pageIndex = (int)Input::get('page',1);
        $search['pageIndex'] = $pageIndex;
        $search['pageSize'] = $pageSize;
        $search['platform'] ="ios";
        $res = TuiguangService::v4excute($search,"promoter/ios");
       /*  foreach($res['data'] as &$v){
            $val['accountId']=$v['promoterId'];
            $row= TuiguangService::v4excute($val,"diamond/query");
             $gal['promoterId']=$v['promoterId'];
             $rows= TuiguangService::v4excute($gal,"account/statistics");
             $v['yxd_money']=$rows['data'];
            if($row['count']==0){
                $v['balance']=0;

            }else {
                $v['balance']=$row['result']['balance'];

            }
        } */
        /*  var_dump($res);
         die();  */
        if($res['success'])
        {
            $data['datalist'] = $res['data'];
            $total = $res['count'];
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;//回调函数
//        $data['feeAmount']=$res['data']['feeAmount'];
        $data['count']=isset($res['count'])?$res['count']:'';
        return $this->display('promoter-list',$data);
    }
 //图片上传
    public function postFileCount(){
        if(!Input::hasFile('importFile')){
            //return $this->back()->with('global_tips','卡密文件不存在');
            echo json_encode(array('errorCode'=>1,'errortxt'=>'文件不存在'));
            exit;
        }
        $file = Input::file('importFile');
        $ext = $file->getClientOriginalExtension();
        $filename = $file->getClientOriginalName();
        if($ext != 'txt' ){
            echo json_encode(array('errorCode'=>1,'errortxt'=>'文件格式错误'));
            exit;
        }
        $dir = '/userdirs/filecount/';
        $path = storage_path() . $dir;
        $this->createFolder($path);
        $new_filename = date('YmdHis') . str_random(4);
        $file_path =$file->move($path,$new_filename . '.' . $ext);
        if(empty($file_path)){
            echo json_encode(array('errorCode'=>1,'errortxt'=>'上传失败!'));
            exit;
        }
         $str = file_get_contents($file_path);//获得内容
         if($ext == 'txt'){
            $arr=array_filter(explode("\r\n",trim($str)));
        }else{
            //mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $arr=array_filter(explode(",",trim($str)));
        }
        return json_encode($path);


    }
    private function createFolder($path)
    {
        if (!file_exists($path))
        {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }
//用户充值
    public function getMoneyRecord()
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
        $search['gameName'] = Input::get('gameName','');
        $search['currencyType'] = 2;
        $search['isPromotion'] = 'TRUE';
        $search['actionId']='TRADE_365_IOS';
        $search['hasFee']='TRUE';
        $res = TuiguangService::v4excute($search,"money/ios");
        if($res['success']){

            foreach($res['data'] ['list'] as &$v){
                if(isset($v['tradeExtra'])){
                    $v['tradeExtra'] = json_decode($v['tradeExtra'],true);
                }
            }
            $data['datalist'] = $res['data'] ['list'];
            $data['totalAmount'] = $res['data']['totalAmount'];
            $total = $res['count'];

        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;//回调函数
        return $this->display('money-list',$data);
    }

 //游戏分成设置列表查询
    public function getCommissionSetup()
    {
        $data = $search = $input = array();
//        $data['ChannelInfo'] = array();
//        $ChannelInfo = TuiguangService::GetAllChannelInfo();
//        if($ChannelInfo && isset($ChannelInfo['result'])){
//            $data['ChannelInfo'] = $ChannelInfo['result'];
//        }
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
        $search['transType'] = 1;
        $search['actionId'] = 'TRADE_365_IOS';
        $res = TuiguangService::v4excute($search,"queryGame/ios");
       /*  var_dump($res['data'][0]);
        die(); */
        if($res['success']){
            $data['datalist'] = $res['data'] ;
           // $data['totalAmount'] = $res['data']['totalAmount'];
            $total = $res['count'];
        }
        unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
        $data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
        $data['search'] = $search;//回调函数
        $data['search'] = $search;
        $data['count'] =isset($res['count'])?$res['count']:'';
        return $this->display('commission-setup',$data);

    }
    public function postAjaxCommissionSetup(){
        $data = Input::all();
        $res = TuiguangService::v4excute($data,"default/update/ios","false");
        echo json_encode($res);
    }

    public function postAjaxCashSetup(){
        $data = Input::all();
        $res = TuiguangService::v4excute($data,"account/setconfig","false");
        echo json_encode($res);
    }

    public function postAjaxRuleSetup(){
        $data = Input::all();
        $res = TuiguangService::v4excute($data,"config/update/ios","false");
        echo json_encode($res);
    }
    public function postAjaxUploadImg()
    {
        if(Input::file('prize_pic')){
           $icon = MyHelpLx::sav1e_img(Input::file('prize_pic'));
            echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>$icon));
        }else{
            echo json_encode(array('success'=>"false",'mess'=>'修改失败','data'=>"图片丢失"));
        }
    }

    public function postAjaxOpen()
    {
        $data = Input::all();
        $res=TuiguangService::v4excute($data,"update_promoter/ios","false");
        echo json_encode($res);
    }

    public function postAjaxDo()
    {
        $data = Input::all();
        $url = $data['url'];unset($data['url']);
        $v=parent::getSessionUserUid();
        $data['modifier']= $v = parent::getSessionUserName();
        $res=TuiguangService::v4excute($data,$url,"false");

        echo json_encode($res);
    }

 public function getCommissionSetupAdd(){
     $search['gameId']=Input::get('gameId','');
     $data=array();
//     $search['channelActive'] = "TRUE";
     if($search['gameId']){
     $row = TuiguangService::v4excute($search,"queryGame/ios");
     $data['atask']=$row['data'][0];
     }
     return $this->display('commission-setup-add',$data);

 }
//游戏渠道
  public function getCommissionGame(){
      $search['gameId']= Input::get('gameId','');
      $search['platform']= 'ios';
      $result=TuiguangService::v4GameExcute($search,"returnChannelsByGameId");
      echo json_encode($result);
  }
//游戏查询弹出框
   public function getGameSearch(){
        $search = array();
		$gameName = Input::get('gameName','');
		$search['gameName']=$gameName;
		$pageSize = 3;
		$total = 0;
		$input = Input::get();
		$pageIndex = (int)Input::get('page',1);
		$search['pageIndex'] = $pageIndex;
		$search['pageSize'] = $pageSize;
		$result= TuiguangService::v4GameExcute($search,"ReturnGameList4Page");
//	    $imgurl="http://test.ios.365jiaoyi.com/GameIcon/";// 图片路径   正式上线需改成线上路径  http://ios.youxiduo.com/GameIcon/
       $imgurl="http://ios.youxiduo.com/GameIcon/";
		$data['games'] =$result['recordList'];
		$data['imgurl']=$imgurl;
		if($result){
		    $total=$result['totalCount'];
		}
		unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
		$data['pagelinks'] = MyHelpLx::pager(array(),$total,$pageSize,$search);
		$data['search'] = $search;//回调函数
		$html = $this->html('pop-game-list',$data);
		return $this->json(array('html'=>$html));
	}

    //检测游戏是否存在
    public function getCheckGameName(){
        $search['gameName']   = Input::get('game_name', '');

        $search['pageIndex']  = 1;
        $search['pageSize']   = 10;
        $search['currencyType'] = 2;
        $search['gameRefId']  = '';
        $search['transType']  = 1;
        $search['actionId']   = 'TRADE_365_IOS';

        if ($search['gameName']) {
            $res = TuiguangService::v4excute($search, "queryGame/ios");
            if($res['success'] && $res['count'] > 0){
                echo json_encode(array('success'=>200, 'msg'=>'已添加过游戏'));
            } else {
                echo json_encode(array('success'=>400, 'msg'=>'可以添加'));
            }
        }

    }

//添加游戏分成配置操作
	public function postCommissionSetupAdd(){
		$search=Input::All();
	    $data=array();
	    $data['gameRefId']=$search['game_id'];
	    $data['gameName']=$search['game_name'];
	    $data['gameIcon']=$search['gameIcon'];
	    $data['displayIndex']=$search['displayIndex'];
	    $data['gameIcon']=$search['gameIcon'];
	    $data['currencyType']=2;
	    $data['translatType']=1;
	    $data['actionId']='TRADE_365_IOS';	    
	    $data['percentageBeanList']=array();
	    $count = count($search['channelName']);
	     for ($i=0; $i<$count; $i++) {
	     	$data['percentageBeanList'][$i]=array(
	     			'channelName'=>$search["channelName"][$i],
	     			'channelRefId'=>$search["channelRefId"][$i],
	     			'feeValueShow'=>$search["feeValueShow"][$i],
//                    'upperLimitShow'=>$search["upperLimitShow".$i],
	     	        'feeType' =>1,
	     			'currencyType'=>2,
//                    'upperLimit'=>$search["upperLimitShow".$i],
	     			'translatType'=>1
	     	);
        }
	   $res = TuiguangService::v4excute($data,"addGame/ios");

	   if($res['success']){
	       return $this->redirect('v4_tuiguang/home/commission-setup','添加成功');
	   }else{
	       return $this->back($res['error']);
	   }
	}
//修改游戏分成配置操作
  public function postCommissionSetupUpdete(){
  	$search=Input::All();
  	$data=array();
  	$data['gameId']=$search['gameId'];
  	$data['gameRefId']=$search['game_id'];
  	$data['gameName']=$search['game_name'];
  	$data['displayIndex']=$search['displayIndex'];
  	$data['gameIcon']=$search['gameIcon'];
  	$data['currencyType']=2;
  	$data['translatType']=1;
  	$data['actionId']='TRADE_365_IOS';
  	$data['percentageBeanList']=array(); 
  	$count = count($search['channelName']);
  	for ($i=0; $i<$count; $i++) {
  		$data['percentageBeanList'][$i]=array(
	     		'channelName'=>$search["channelName"][$i],
	     		'channelRefId'=>$search["channelRefId"][$i],
	     		'feeValueShow'=>$search["feeValueShow"][$i],
//                'upperLimitShow'=>$search["upperLimitShow".$i],
  			    'percentageId'=>$search["percentageId"][$i],
  				'isActive'=>$search["isActive"][$i]&&$search["isActive"][$i]!='false'?'true':'false',
  		        'feeType' =>1,
  				'currencyType'=>2,
//  		        'upperLimit'=>$search["upperLimitShow".$i],
  				'translatType'=>1
  		);
  	}
  	//var_dump($data);
  	$result= TuiguangService::v4excute($data,"updateGame/ios");
  	/* var_dump($data);
  	die(); */
  	if($result['success']){
  	    return $this->redirect('v4_tuiguang/home/commission-setup','修改成功');
  	}else{
  	    return $this->back($result['error']);
  	}
  }
//帐户收入
 public function getYhIncome(){
     $data=array();
     $pageSize = 10;
     $count = 0;
     $input = Input::get();
     $search = array_filter($input);//array_filter去空函数
     $pageIndex = (int)Input::get('page',1);
     $search['pageIndex'] = $pageIndex;
     $search['pageSize'] = $pageSize;
     $search['appBegin'] = Input::get('appBegin','');;
     $search['tradeBegin'] = Input::get('tradeBegin','');
     $search['balanceBegin'] = Input::get('balanceBegin','');
     $search['operationType'] ='promote_reward';
     $search['platform'] ='ios';
     $search['currencyType']=1;
     //var_dump($search);die();
     $res = TuiguangService::v4excute($search,"account/statistics");
     //var_dump( $res );die();

     if($res['success']){
         foreach($res['data'] as &$v){
             $res1 = TuiguangService::v4excute(array('promoterYxdId'=>$v['uid']),"promoter/ios");
             
             if($res1['success']&&isset($res1['data'][0])){
                 $v = array_merge($v,$res1['data'][0]);
             }
         }
         $data['datalist'] = $res['data'];
         $count = $res['count'];
     }
     unset($search['page']);unset($search['pageIndex']);//pager不能有‘page'参数
     $data['pagelinks'] = MyHelpLx::pager(array(),$count,$pageSize,$search);
     $data['search'] = $search;//回调函数
     return $this->display('yh-income-list',$data);
 }

    public function getSetupAll()
    {
        $data['data'] = Input::get();
        $data['task'] = $data['sdk'] = $data['trade'] =array();
        $res_acc = TuiguangService::excute(array('confKey'=>'diamondCashLimit'),"account/configlist");
        if($res_acc['success']&&$res_acc['data']){
            $data['cash'] = $res_acc['data'];
        }
        return $this->display('setup-all',$data);
    }

    public function getSetupGl()
    {
        $data['data'] = Input::get();
        $data['task'] = $data['sdk'] = $data['trade'] =array();
        $res_acc = TuiguangService::excute(array('confKey'=>'strategyDiamondCashLimit'),"account/configlist");
        if($res_acc['success']&&$res_acc['data']){
            $data['cash'] = $res_acc['data'];
        }
        return $this->display('setup-gl',$data);
    }

}
