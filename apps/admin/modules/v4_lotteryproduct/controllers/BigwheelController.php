<?php
namespace modules\v4_lotteryproduct\controllers;
use Youxiduo\Helper\MyHelp;
use Youxiduo\Mall\ProductService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use libraries\Helpers;
use Youxiduo\V4\Lotteryproduct\LotteryproductService;
use Youxiduo\V4\Game\GameService;
class BigwheelController  extends BackendController{
    public function _initialize(){
        $this->current_module = 'v4_lotteryproduct';
    }
	//大转盘创建,修改方案
    /**
     * @param int $id
     * @return mixed
     */
    public function getList($id=0){
         $data=$inputinfo=array();
         if(empty($id)){
             return $this->display('/bigwheel/bigwheel-list',$data);
         }
         $inputinfo['schemeId']=$id;
         $result=LotteryproductService::wheel_query($inputinfo);
         if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$inputinfo,1);
             if(!empty($data['datalist']['0']['detailList'])){
                 $data['datalist']['0']['detailList']=MyHelp::getImgUrlforlist($data['datalist']['0']['detailList'],'prizeImg');
             }
         }

         return $this->display('/bigwheel/bigwheel-list',$data);
    }
    
    public function getDelete($id=0)
    {
        
        if(empty($id)){
            return $this->redirect('/v4lotteryproduct/bigwheel/list')->with('global_tips','参数丢失');
        }
        $params['detailId']=$id;
        $result=LotteryproductService::delectDetail($params);//print_r($result);exit;    
        if($result['errorCode'] == 0){
            return $this->redirect('/v4lotteryproduct/bigwheel/list')->with('global_tips','操作成功');
        }else{
            return $this->redirect('/v4lotteryproduct/bigwheel/list')->with('global_tips','操作失败');   
        }
    }



    public function getDeldetail($id)
    {
        if(empty($id)){
            echo  json_encode(array('errorCode'=>1,'msg'=>'删除失败－删除编号丢失'));
            exit;
        }
        $params['detailId']=$id;
        $result=LotteryproductService::delectDetail($params);//print_r($result);exit;
        $result=$result['errorCode'] == 0 ? array('errorCode'=>0,'msg'=>'删除成功') : array('errorCode'=>1,'msg'=>$result['errorDescription']);
        echo  json_encode($result);
        exit;
    }

    /***
        <td><a href="javascript:void(0);" class="select-uid-ajax" data-uid="<!--{{item.uid}}-->" data-nickname="<!--{{item.nickname}}-->">选定</a></td>
    
    ***/
    public function getAddSelectUser()
    {
        $uid = Input::get('uid');
        $nickname = Input::get('nickname');
        $admin_id = $this->current_user['id'];
        if($uid){
            $keyname = 'selected_' . $admin_id . '_uids';
            $selecteds = array();
            if(Session::has($keyname)){
                $selecteds = Session::get($keyname);
            }
            $selecteds[$uid]  = array('uid'=>$uid,'nickname'=>$nickname);
            Session::put($keyname,$selecteds);
            return $this->json(true);           
        }
        return $this->json(false);
    }



    public function getDeletescheme($id)
    {
        if(empty($id)){
             echo  json_encode(array('errorCode'=>1,'msg'=>$valid->messages()->first()));
             exit;
        }
        $params['schemeId']=$id;
        $result=LotteryproductService::deleteScheme($params);//print_r($result);exit;    
        $result=$result['errorCode'] == 0 ? array('errorCode'=>0,'msg'=>'删除成功') : array('errorCode'=>1,'msg'=>$result['errorDescription']); 
        echo  json_encode($result);
        exit;
    }

    //大转盘管理
    public function getSupervise()
    {     
        $data=array();
        $input = Input::all();
        $params=array(
            'pageIndex'
            ,'pageSize'
            ,'schemeName'
            ,'startTimeBegin'
        );
        $inputinfo=MyHelp::get_Input_value($input,$params);
        //$inputinfo['onOrOff']='false';
        $inputinfo['active']='true';
        $result=LotteryproductService::wheel_query($inputinfo);
        if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$inputinfo,$inputinfo['pageSize']);
        }
        $parms=array('onOrOff'=>'true','active'=>'true','platform'=>'ios');
        $result=LotteryproductService::detail_query($parms);
        if($result['errorCode']==0){
            $data['details']=$result['result'];
        }
        return $this->display('/bigwheel/supervise-list',$data);
    }

    //获奖名单
    public function getUsers()
    {   
         $data=array();
         $input = Input::all();
         $params=array(
             'userName'//用户名
         );
         $inputinfo=MyHelp::get_Input_value($input,$params);
         $result=LotteryproductService::querywin($inputinfo);
         if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$inputinfo,14);
            $data['userinfo']=MyHelp::getUser($data['datalist'],'uid');
            $data['inputinfo']=$inputinfo;
         }
        return $this->display('/bigwheel/users-list',$data);
    }


    /**
     * @return mixed
     */
    public function postAddwheel()
    {
        
        $input = Input::all();
        $params=array(
             'schemeName'//方案名
            ,'startTime'//方案执行时间
            ,'onOrOff'//是否开启：true开启 false关闭
            ,'editor'//编辑者
        );
        $inputinfo=MyHelp::get_Input_value($input,$params,0);
        $rule = array();
        $prompt = array();
        $valid = Validator::make($inputinfo,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');
        $inputinfo['editor']=$uid['id'];
        $inputinfo['onOrOff']=!empty($inputinfo['onOrOff']) ? 'true' : 'false' ;
        $detailList=array();
        if(empty($input['linktype'])) 
            return $this->back()->with('global_tips','添加失败！请填写添加方案');
        foreach($input['linktype'] as $key =>$val)
        {
            $detailList['linkType']=$val;
            $detailList['linkCode']=!empty($input['linkCode']) && !empty($input['linkCode'][$key])?$input['linkCode'][$key]:0;
            if(!empty($input['prizeName']) && !empty($input['prizeName'][$key])){
                switch($val){
                    case 0:
                        $detailList['prizeName']=$input['prizeName'][$key].'游币';
                    break;
                    case 1:
                        $detailList['prizeName']=$input['prizeName'][$key].'礼包卡密';
                    break;
                    case 2:
                        $detailList['prizeName']=$input['prizeName'][$key].'商品';
                    break;
                    case 3:
                        $detailList['prizeName']=$input['prizeName'][$key].'抽奖机会';
                    break;
                    case 4:
                        $detailList['prizeName']=$input['prizeName'][$key].'钻石';
                    break;
                }
            }
            //$detailList['prizeName']=!empty($input['prizeName']) && !empty($input['prizeName'][$key])?$input['prizeName'][$key]:'';
            //$detailList['prizeName']=$input['prizeName'][$key]
            $detailList['prizePrice']=$val==1 || $val==2 ? 0 : $input['prizeName'][$key];
            $detailList['prizeStock']=!empty($input['prizeStock']) && !empty($input['prizeStock'][$key])?$input['prizeStock'][$key]:0;
            $detailList['prizeProbability']=!empty($input['prizeProbability']) && !empty($input['prizeProbability'][$key])?$input['prizeProbability'][$key]:0;
            $detailList['onOrOff']='true';
            if(!empty($input['prizeImg']) && !empty($input['prizeImg'][$key])){
                $dir = '/userdirs/mall/prizeImg/'.date('ym',time()).'/';
                $path = Helpers::uploadPic($dir,$input['prizeImg'][$key]);
                $detailList['prizeImg'] = $path;
            }
            $inputinfo['detailList'][]=$detailList;

        }

        $result=LotteryproductService::wheel_add($inputinfo);
        return $this->redirect('/v4lotteryproduct/bigwheel/supervise')->with('global_tips','操作成功');
    }


    public function postUpdatawheel(){
        $input = Input::all();
        $params=array(
             'schemeId'//ID
            ,'schemeName'//方案名
            ,'startTime'//方案执行时间
            ,'onOrOff'
            ,'editor'//编辑者
        );
        $inputinfo=MyHelp::get_Input_value($input,$params,0);
        $rule = array();
        $prompt = array();
        $valid = Validator::make($inputinfo,$rule,$prompt);
        if($valid->fails()) return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        $uid=$this->getSessionData('youxiduo_admin');
        $inputinfo['editor']=$uid['id'];
        $inputinfo['onOrOff']=!empty($inputinfo['onOrOff']) ? 'true' : 'false' ;

        $detailList=array();
        if(!empty($input['linktype'])){
        //return $this->back()->with('global_tips','添加失败！请填写添加方案');
            foreach($input['linktype'] as $key =>$val)
            {

                $detailList['linkType']=$val;
                $detailList['linkCode']=!empty($input['linkCode']) && !empty($input['linkCode'][$key])?$input['linkCode'][$key]:'balance';
                if(!empty($input['prizeName']) && !empty($input['prizeName'][$key])){
                    switch(intval($val)){
                        case 0:
                            $detailList['prizeName']=$input['prizeName'][$key].'游币';
                            break;
                        case 1:
                            $detailList['prizeName']=$input['prizeName'][$key].'礼包卡密';
                            break;
                        case 2:
                            $detailList['prizeName']=$input['prizeName'][$key].'商品';
                            break;
                        case 3:
                            $detailList['prizeName']=$input['prizeName'][$key].'抽奖机会';
                            break;
                        case 4:
                            $detailList['prizeName']=$input['prizeName'][$key].'钻石';
                            break;
                    }
                }

                //$detailList['prizeName']=!empty($input['prizeName']) && !empty($input['prizeName'][$key])?$input['prizeName'][$key]:'';

                $detailList['prizePrice']=$val==1 || $val==2 ? 0 : $val;
                $detailList['prizeStock']=!empty($input['prizeStock']) && !empty($input['prizeStock'][$key])?$input['prizeStock'][$key]:0;
                $detailList['prizeProbability']=!empty($input['prizeProbability']) && !empty($input['prizeProbability'][$key])?$input['prizeProbability'][$key]:0;
                $detailList['onOrOff']='true';
                if(!empty($input['prizeImg']) && !empty($input['prizeImg'][$key])){
                    $dir = '/userdirs/mall/prizeImg/'.date('ym',time()).'/';
                    $path = Helpers::uploadPic($dir,$input['prizeImg'][$key]);
                    $detailList['prizeImg'] = $path;
                }
                $inputinfo['detailList'][]=$detailList;

            }
            if(!empty($input['del'])){
                $inputinfo['detailList'][]=array('detailId'=>$input['del'],'active'=>'false');
            }

        }

        $result=LotteryproductService::wheel_update($inputinfo);

        return $this->redirect('/v4lotteryproduct/bigwheel/supervise')->with('global_tips','操作成功');
    }

    //查询礼包
    public function getSelectPopGift(){
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =6;
        if(Input::get('keyword')) {
            $params['cardDesc']=Input::get('keyword');
        }
        $params['onOrOff'] ='true';
        $params['isActive']='true';
        $params['virtualUse']=4;
        $result=ProductService::getvirtualcardlist($params);//print_r($result);
        if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$params,6);
            if(!empty($params['cardDesc'])){
                $data['keyword']=$params['cardDesc'];
            }
            $html = $this->html('pop-giftbag-list',$data);
            return $this->json(array('html'=>$html));
        }
    }


    public function postQiyong($id=0)
    {
        $input = Input::all();
        $input['schemeId']=$id;
        $input['onOrOff'] ='true';
        $result=LotteryproductService::wheel_update($input);
        $result=$result['errorCode'] == 0 ? array('errorCode'=>0,'msg'=>'启用成功','value'=>$id) : array('errorCode'=>1,'msg'=>$result['errorDescription']); 
        echo  json_encode($result);
        exit;
    }

    /**视图 商品活动添加/修改中需要的PRODUCTCODE */
    public function getProductCode(){
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['pageSize'] =4;
        $params['isOnshelf'] ='true';
        $data['keyword']=$params['productName']=Input::get('keyword');
        $params['productType']='0,1,3,4';
        $result=ProductService::searchProductList($params);
        if($result['errorCode']==0){
            $data=MyHelp::processingInterface($result,$params,4);
            $arr=array(); 
            foreach($data['datalist'] as $key=>$value){
                $arr[]=$value['productCode'];
            }
            $ids=join(',',$arr);
            if(!empty($ids)){
                $arr['productCode']=$ids;
                $arr['genre']=1;
                $arr['isActive']=1;
                $result=ProductService::getMallGameRelation($arr); 
                if($result['errorCode']==0 && $result['result']){
                    $arr=array();
                    foreach($result['result'] as $val_){
                        $arr[$val_['productCode']]=$val_['gid']; 
                    }
                    foreach($data['datalist'] as $key=>&$value)
                    {
                        if(!empty($arr[$value['productCode']])){
                             $gameName=GameService::getOneInfoById($arr[$value['productCode']],'ios');
                             $value['gname']=!empty($gameName['gname']) && $gameName['gname']!='g'  ? $gameName['gname'] : '';
                        }
                        if(!empty($value['totalCount']) && !empty($value['restCount'])){
                              $value['tr']=$value['totalCount']-$value['restCount'];
                        }
                    }

                }
            }
            $html = $this->html('pop-productcode-list',$data);
            return $this->json(array('html'=>$html));
        }
    }



    
}