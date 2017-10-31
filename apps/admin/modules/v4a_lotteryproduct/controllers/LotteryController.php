<?php
namespace modules\v4_lotteryproduct\controllers;
use Youxiduo\Helper\MyHelp;
use Youxiduo\V4\Lotteryproduct\LotteryproductService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;

class LotteryController  extends BackendController{
    public function _initialize(){
        $this->current_module = 'v4_lotteryproduct';
    }
	//天天彩	
    public function getList(){
        $data=array();
        $input = Input::all();
        $params=array(
             'periodId'//彩票ID
            ,'lotteryNumber'//3位数彩票号码
            ,'isCurrent'//是否是当期彩票
            ,'isPrevious'//是否是上期彩票
            ,'isExpire'//是否是往期彩票
            ,'isRun'//是否开奖
            ,'isLoadCount'
            ,'pageIndex'
            ,'pageSize'
            ,'lotteryTime'//当期彩票到期时间
        );
        $inputinfo=MyHelp::get_Input_value($input,$params);//$inputinfo['activityStartTime']=date('Y-m-d H:i:s',strtotime($inputinfo['activityStartTime']));
        $inputinfo['isLoadCount']='true';
        $result=LotteryproductService::query_lottery($inputinfo,$params);
        if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$inputinfo,$inputinfo['pageSize']);
            $data['prize']=LotteryproductService::query_prize();
            if(!empty($data['prize']['result'])){
                $str='( ';
                foreach($data['prize']['result'] as $val_){
                    $str.=$val_['prizeName'].'：<span id="prize_'.$val_['prizeId'].'">'.$val_['bonus'].'</span> ';
                }
                $data['prizeStr']= $str.')';
                $data['select']=MyHelp::array_select($data['prize']['result'],'prizeId','prizeName');
            }
            
            $current=LotteryproductService::current_lottery();//当前期数
            $data['periodId']=$current['errorCode'] == 0 && !empty($current['result']) ?  $current['result']['periodId'] : '';
            //配置字典
            $dic=LotteryproductService::query_dic();
            if($dic['errorCode'] == 0 && !empty($dic['result'])){
                foreach($dic['result'] as $key=>$val){
                    if($val['dicType'] == 'each_lottery_prize')
                       $data['each_lottery_prize']=array('dicId'=>$val['id'],'dicValue'=>$val['dicValue']);
                    if($val['dicType'] == 'user_max_purchase_number')
                       $data['user_max_purchase_number']=array('dicId'=>$val['id'],'dicValue'=>$val['dicValue']);
                }
            }
            return $this->display('lottery/lottery-list',$data);
        }
	}

    //发布中奖号码
    public function postZjhm()
    {   
        $input = Input::all();
        $rule = array('periodId'=>'required','lotteryNumber'=>array('integer','min:100','max:999'));
        $prompt = array('periodId.required'=>'彩票ID为空','lotteryNumber'=>'请填写3位数的彩票号码');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) {
            echo  json_encode(array('errorCode'=>1,'msg'=>$valid->messages()->first()));
            exit; //
        } 
        $result=LotteryproductService::publish_lottery_number($input);
        $result=$result['errorCode'] == 0 ? array('errorCode'=>0,'msg'=>'更新成功') : array('errorCode'=>1,'msg'=>$result['errorDescription']); 
        echo  json_encode($result);
        exit;
    }   
    //发布中奖金额
    public function postZjje()
    {
        $input = Input::all();
        $rule = array('prizeId'=>'required');
        $prompt = array('prizeId.required'=>'奖励ID为空');
         $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) {
            echo  json_encode(array('errorCode'=>1,'msg'=>$valid->messages()->first()));
            exit; //
        } 
        $result=LotteryproductService::update_prize($input);
        $result=$result['errorCode'] == 0 ? array('errorCode'=>0,'msg'=>'更新成功','value'=>!empty($input['bonus'])?$input['bonus']:0) : array('errorCode'=>1,'msg'=>$result['errorDescription']); 
        echo  json_encode($result);
        exit;
    }
    //更新字典常量
    public function postUpdatedic()
    {
        $input = Input::all();
        $rule = array('dicId'=>'required');
        $prompt = array('dicId.required'=>'主键ID为空');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()) {
            echo  json_encode(array('errorCode'=>2,'msg'=>$valid->messages()->first()));
            exit; //
        } 
        $result=LotteryproductService::update_dic($input);
        $result=$result['errorCode'] == 0 ? array('errorCode'=>0,'msg'=>'更新成功','value'=>!empty($input['dicValue'])?$input['dicValue']:0) : array('errorCode'=>1,'msg'=>$result['errorDescription']); 
        echo  json_encode($result);
        exit;
    }


    //天天彩中奖名单   
    public function getUsers($id=0)
    {
        if(empty($id)){
           return $this->back()->with('global_tips','参数丢失');
        }
        $data=array();
        $input = Input::all();
        $params=array(
            'recordId'//记录ID
            ,'uid'//用户ID
            ,'periodId'//彩票ID
            ,'prizeLevel'//中奖类型，1：直选; 2：组选3; 3：组选6
            ,'bettingNumber'//用户投注号码
            ,'isWinning'//是否中奖
            ,'isSend'//是否发奖
            ,'sort'//排序，默认时间降序，1：中奖降序，中奖类型升序，时间降序
            ,'isOnshelf'
        );
        $inputinfo=MyHelp::get_Input_value($input,$params);
        if(!empty($inputinfo['isOnshelf'])){
            switch ($inputinfo['isOnshelf']) {
                case '1':
                    $inputinfo['isSend']='true';
                    break;
                case '2':
                    $inputinfo['isSend']='false';
                    # code...
                    break;
            }
             unset($inputinfo['isOnshelf']);
        }
       
        $inputinfo['periodId']=$id;
        $inputinfo['isWinning']='true';
        $result=LotteryproductService::query_record($inputinfo,$params);
        if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$inputinfo,$inputinfo['pageSize']);
            $data['userinfo']=MyHelp::getUser($data['datalist'],'uid');
            $data['periodId']=$id;
            return $this->display('lottery/users-list',$data);
        }
        
    }

    //补发奖
    public function getRunlottery($id=0)
    {
        if(empty($id)){
           return $this->back()->with('global_tips','设置失败');
        }
        $inputinfo['periodId']=$id;
        $result=LotteryproductService::run_lottery($inputinfo);
        if($result['errorCode']==0){
            return $this->redirect('/v4lotteryproduct/lottery/users/'.$id)->with('global_tips','操作成功');
        }
        return $this->redirect('/v4lotteryproduct/lottery/users/'.$id)->with('global_tips','操作失败');
    }
    
}