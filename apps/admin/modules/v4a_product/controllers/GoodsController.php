<?php
namespace modules\v4a_product\controllers;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Mall\ProductService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\Android\BaiduPushService;
use Youxiduo\Android\Model\UserDevice;
use Youxiduo\Helper\Utility;
use Youxiduo\V4\User\UserService;
use Illuminate\Support\Facades\Session;
use Youxiduo\Android\Model\CreditAccount;
use Youxiduo\Android\Model\CreditLevel;
/*
	fujiajun 4.0 后台商城 2015/3/2
*/
class GoodsController extends BackendController
{
        
    private $myurl='';
    public function _initialize()
    {
        $this->current_module = 'v4a_product';
    }

    /**视图：商品管理列表**/
    public function getList()
    {
        $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        $params['active'] ='true';
        $input=Input::only("ascOrDesc","productName",'isOnshelf','categoryId','productStock');
        $params['ascOrDesc']='false';  
        if(!empty($input['ascOrDesc']) && $input['ascOrDesc']==1){
             $params['ascOrDesc']='true';   
        }elseif(!empty($input['ascOrDesc']) && $input['ascOrDesc']==2){
             $params['ascOrDesc']='false';   
        }else{
            unset($params['ascOrDesc']);
        }
        if(!empty($input['isOnshelf']) && $input['isOnshelf']=='true'){
            $params['isOnshelf']='true';  
        }elseif(!empty($input['isOnshelf']) && $input['isOnshelf']=='false'){
            $params['isOnshelf']='false';
        }else{
            unset($params['isOnshelf']);
        }
        if(!empty($input['productName'])){
            $params['productName']=$input['productName'];
        }
         if(!empty($input['categoryId'])){
            $params['categoryId']=$input['categoryId'];
        }
        $params['productType']='0,1,3,4';
        if($input['productStock']==1){
            $params['productStock']=0 ;
        }
        $result=ProductService::searchProductList($params);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            foreach($data['datalist'] as $key=>$value){
                if(!empty($value['img'])){
                    $img=json_decode($value['img'],true);
                    $data['datalist'][$key]['listpic']=!empty($img['listPic']) ? $img['listPic'] :'';
                }
            }
            $data['search']=$input;

            return $this->display('goods-list',$data);
        }
        //self::error_html($result);
    }

    /**视图：增加/修改商品 **/
    public function getProductAddEdit($goods_id=0)
    {
        $data= $params = array();
        $data['edit'] =0;
        $data['goods']['isNotice']='true';
        $data['goods']['isBelongUs']='true';
        $data['goods']['isExclusive'] ='true';
        $data['goods']['lightDelivery'] ='false';
        if(!empty($goods_id)){
            $result=ProductService::searchProductList(array('productCode'=>$goods_id));
            if($result['errorCode'] == 0){
                $data['goods']=$result['result']['0'];
                if(!empty($data['goods']['templateId'])){
                    $t=ProductService::getFormList(array('templateId'=>$data['goods']['templateId']));
                    if($t['errorCode'] == 0 && !empty($t['result'])){
                        $data['goods']['templateName']=$t['result']['0']['templateName'];
                    }
                }
                if(!empty($data['goods']['img'])){
                    $img=json_decode($data['goods']['img'],true);
                    if(!empty($img['listPic'])){
                        $data['goods']['listPic']=$img['listPic'];
                        $data['goods']['xlistPic_']=strstr($img['listPic'], '/userdirs/');
                    }
                    if(!empty($img['detailPicArray'])) {
                        if (!empty($img['detailPicArray']['0'])) {
                            $img_ = $img['detailPicArray']['0'];
                            $data['goods']['bigpic_1'] = $img_;
                            $data['goods']['xbigpic_1'] = strstr($img_, '/userdirs/');
                        }
                        if (!empty($img['detailPicArray']['1'])) {
                            $img_ = $img['detailPicArray']['1'];
                            $data['goods']['bigpic_2'] = $img_;
                            $data['goods']['xbigpic_2'] = strstr($img_, '/userdirs/');
                        }
                        if (!empty($img['detailPicArray']['2'])) {
                            $img_ = $img['detailPicArray']['2'];
                            $data['goods']['bigpic_3'] = $img_;
                            $data['goods']['xbigpic_3'] = strstr($img_, '/userdirs/');
                        }
                    }
                }
                if(!empty($data['goods']['cardCode'])){
                        $caeddesc=ProductService::getvirtualcardlist(array('cardCode'=>$data['goods']['cardCode']));
                        if($caeddesc['errorCode'] != null){
                            $data['goods']['cardDesc']=!empty($caeddesc['result']['0']['cardDesc'])? $caeddesc['result']['0']['cardDesc'] : '';
                        }
                    } 
                if(!empty($data['goods']['accountList'])){
                      $data['goods']['exclusiveAccount']=join(',',$data['goods']['accountList']);
                }
                $data['edit'] =1;
                $data['goods']['isBelongUs'] = !empty($data['goods']['isBelongUs']) ? 'true' : 'false';
                $data['goods']['isExclusive']  = !empty($data['goods']['isExclusive']) ? 'true' : 'false';
                $data['goods']['isNotice']  = !empty($data['goods']['isNotice']) ? 'true' : 'false';
                $result=ProductService::queryCategory(array('categoryId'=> !empty($data['goods']['categoryId']) ? $data['goods']['categoryId'] : ''));
                $data['goods']['categoryName'] = ($result['errorCode'] == 0) ? !empty($result['result']['0']) ? $result['result']['0']['categoryName'] : '' : error_html($result);

            }else{
                self::error_html($result);
            }
        }
        return $this->display('goods-edit',$data);
    }
    
    /**视图：商品种类列表**/
    public function getCateList()
    {
        $data = array('pageZIE'=>Input::get('page',1));
        $result=ProductService::queryCategory($data);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$data);
            return $this->display('cate-list',$data);
        }
        self::error_html($result);

    }

    /** 视图： 商品订单列表**/
    public function getProductOrderList()
    {   
        $input=Input::only('biller','orderStatus','orderDesc');
        $data=array('pageIndex'=>Input::get('page',1),'pageSize'=>10,'active'=>'true');
        if(!empty($input['biller'])){
            $data['biller'] =$input['biller'];
        }
        if(!empty($input['orderStatus']) || is_numeric($input['orderStatus'])){
            $data['orderStatus'] =$input['orderStatus'];
        }
        if(!empty($input['orderDesc'])){
            $data['orderDesc']=$input['orderDesc'];
        }
        //print_r($data);exit;
        $result=ProductService::ProductOrderList($data);
        if($result['errorCode']==0 ){
            $params=array();
            foreach($result['result'] as $val){
                $params[]=$val['biller']; 
            }
            $uids=array_flip(array_flip($params));
            $users_level = CreditAccount::getUserCreditByUids(array_flip(array_flip($uids)));
            foreach($result['result'] as &$row){
                if(isset($users_level[$row['biller']])){
                    $level = CreditLevel::getUserLevel($users_level[$row['biller']]['experience']);
                    $row['experience'] = $users_level[$row['biller']]['experience'];
                    $row['level_name'] = $level['name'];
                    $row['level_max'] = $level['end'];
                }else{
                    $row['experience'] = 0;
                    $row['level_name'] = '1';
                    $row['level_max'] = '50';
                }
                if(!empty($row['mobile'])){
                    $row['mobile'] = preg_replace('/(1[3578]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$row['mobile']);
                }
                $out[] = $row;
            }
            if(!empty($params)){
                $params=UserService::getMultiUserInfoByUids(array_flip(array_flip($params)),'full');
                if(!empty($params)){
                    $data['userinfo']=array();
                    foreach($params as $val_){
                        $data['userinfo'][$val_['uid']]=array('nickname'=>$val_['nickname'],'mobile'=>$val_['mobile']);
                    }   
                }
            }

            $data=self::processingInterface($result,$data);
            return $this->display('order-list',$data);
        }
        self::error_html($result);
    }

    /**视图：商品订单修改**/
    public function getOrderAddEdit($orderid="")
    {
        if(!empty($orderid)){
            $data=array('orderId'=>$orderid);
            $result=ProductService::ProductOrderList($data);

            if($result['errorCode']==0 ){
                $data['goods']=$result['result']['0'];
                return $this->display('order-edit',$data);
            }
            self::error_html($result);
        }
    }




    /**视图：父商品种类列表的查询**/
    public function  getCateListSelect()
    {
        $data = array('pageIndex'=>Input::get('page',1));
        $data['categoryName']=Input::get('keyword','');
        $data['pageSize']=6;
        if(Input::get('type')){
            $data['type']=Input::get('type');
        }
        $result=ProductService::queryCategory($data);//print_r($result);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$data,$data['pageSize']);
            if(Input::get('type')){
                $data['cate']['type']=Input::get('type');
            }else{
                foreach ($data['datalist'] as $key => $value) {
                    # code...
                    if($value['id']==0){
                        unset($data['datalist'][$key]);
                    }
                }
            }
            
            $html = $this->html('pop-cate-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }

    /**视图：添加/修改 商品种类  **/
    public function getCateAddEdit($cate_id=0)
    {
        
        $data=$datainfo= array();
        $datainfo['isBelongUs']='true';
        if(!empty($cate_id)){
            $data['categoryId']=$cate_id;
            $result=ProductService::queryCategory($data);
            if($result['errorCode'] == 0 ){//不为0就在查询一次
                
                $datainfo['cate']=$result['result']['0'];
                if(!empty($datainfo['cate']['categoryImgpath'])){
                    $datainfo['cate']['xcategoryImgpath']=strstr($datainfo['cate']['categoryImgpath'], '/userdirs/');
                }
                $datainfo['cate']['isBelongUs']=!empty($datainfo['isBelongUs']) ? 'true' : 'false';
                
                if(!empty($datainfo['cate']['parentId']) && $datainfo['cate']['parentId'] != 0){
                    $data['categoryId']=$datainfo['cate']['parentId'];
                    $result=ProductService::queryCategory($data);
                   
                    if($result['errorCode'] != 0){
                        self::error_html($result);
                    }
                    $datainfo['cate']['pcategoryName']=$result['result']['0']['categoryName'];
                }else{
                    //为0就是自身根节点
                    $datainfo['cate']['pcategoryName']=$datainfo['cate']['categoryName'];
                }
            }else{
                self::error_html($result);
            }

        }
        
        return $this->display('cate-edit',$datainfo);
    }

    /**视图 商品活动 添加 修改 **/
    public function  getActivityAddEdit($id=0)
    {
        $datainfo = array();
        $datainfo['add']=1;
        if(!empty($id)){
            //修改
            $datainfo['add']=0;
            $result=ProductService::searchProductActivityList(array('activityId'=>$id));
            if($result['errorCode'] != 0){
                self::error_html($result);
            }
            $datainfo['activity']=$result['result']['0'];
            $result=ProductService::searchProductList(array('productCode'=>$datainfo['activity']['productCode']));
            if($result['errorCode']==0){
                $datainfo['activity']['productName']=$result['result']['0']['title'];
            }else{
                self::error_html($result);
            }
        }
        return $this->display('activity-edit',$datainfo);
    }



    //商品上架 下架方法
    /**
     * @param $goods_id
     * @param $status
     * @return mixed
     */
    public function getStatus($goods_id,$status)
    {
        $params=array('productCode'=>$goods_id);
        if(!$status){
            # code...
            $result=ProductService::offsaleProduct($params);
        } else {
            # code...
            $result=ProductService::onsaleProduct($params);
        }
        return $this->redirect('v4aproduct/goods/list')->with('global_tips','商品属性修改成功');
    }



    /**视图 查询商品活动列表**/
    public function getProductActivityList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        $result=ProductService::searchProductActivityList($params);
        //print_r($result);//exit;  
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$data);
            return $this->display('activity-list',$data);
        }
        self::error_html($result);
    }

    /**视图 商品活动添加/修改中需要的PRODUCTCODE */
    public function getProductCode(){
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['pageSize'] =5;
        $params['active']=$params['isOnshelf'] ='true';
        $data['keyword']=$params['productName']=Input::get('keyword');
        $params['productType']='0,1,3,4';
        $result=ProductService::searchProductList($params);
        if($result['errorCode']==0){
            //print_r($result);
            $data=self::processingInterface($result,$data);
            $html = $this->html('pop-productcode-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }
    /**视图 商品活动添加/修改中需要的PRODUCTCODE */
    public function getProductCode2(){
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['pageSize'] =5;
        $params['active']='true';
        $data['keyword']=$params['productName']=Input::get('keyword');
        $params['productType']='0,1,3,4';
        $result=ProductService::searchProductList($params);
        if($result['errorCode']==0){
            //print_r($result);
            $data=self::processingInterface($result,$data);
            $html = $this->html('pop-productcode-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }

    /**视图：许愿帖 **/
    public function getRule()
    {
        $result=ProductService::getrule();
        if ($result['errorCode']==0) {
            # code...
            $data['topic']['subject']=!empty($result['result']['subject']) ? $result['result']['subject'] : '';
            $data['topic']['format_message']=!empty($result['result']['formatContent']) ? $result['result']['formatContent'] : '';
            $data['topic']['tid']=!empty($result['result']['tid']) ? $result['result']['tid'] : '';
        } else {
            # code...
            self::error_html($result);
        }
        return $this->display('wish-rule',$data);
    }

    /**视图 获取卡编码列表**/
    public function getCateCardSelect()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['pageSize'] =5;
        if(Input::get('keyword')){
            $data['keyword']=$params['cardDesc']=Input::get('keyword');
        }
        $result=ProductService::getvirtualcardlist($params);
        if($result['errorCode'] ==0 ){
            foreach($result['result'] as &$item){
                $item['can_use'] = $item['cardStock'] + $item['cardUsedStock'] - $item['cardQuota'];
            }
            $data=self::processingInterface($result,$data,$params['pageSize']);
            $html = $this->html('pop-card-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }

    //$params,array('productCode','top','hot','newest','recommend','lightDelivery')
    /**视图上修改该商品属性 **/
    public function getProductOperator($id,$type,$val=0){
        $val = (!empty($val)) ? 'false' : 'true' ;
        $data=array('productCode'=>$id);
        switch ($type) {
            case 'isrecommend':
                # code...
                $data['recommend']=$val;
                break;
            case 'ishot':
                # code...
                $data['hot']=$val;
                break;
            case 'isnewest':
                # code...
                $data['newest']=$val;
                break;
            case 'top':
                # code...
                $data['top']=$val;
                break;
            case 'lightDelivery':
                $data['lightDelivery']=$val;
                break;
            default:
                # code...
                return $this->back()->with('global_tips','商品修改属性失败');
                break;
        }
        $result=ProductService::update_productextra($data);
        if($result['errorCode']==0){
            return $this->redirect('v4aproduct/goods/list')->with('global_tips','商品类型修改成功');
        }
        self::error_html($result);
    }

    /**增加/修改 商品POST操作  'gameId' => $input['game_id'] ? $input['game_id'] : self::YXD_GID,
    'gid' => $input['game_id'] ? $input['game_id'] : self::YXD_GID,
    'gname' => $input['game_name'] ? $input['game_name'] : '',**/
    public function postProductAddEdit()
    {	 
        $type='增加';//

        $input = Input::only('productName','categoryId','gid','gname','cardCode','productType','isNeedTemplate','templateId','productGamePrice','productPrice','productCost','inventedType','isOnshelf','productStock','isNotice','isBelongUs','productSummary','productSort','exclusiveAccount','shelf_set','productInstruction','singleLimit','isTop','isHot','isNewest','isRecommend','lightDelivery','productDesc'
  );
        //$input['gid']=!empty($input['gameId'])?$input['gameId']:'';
        $biTian=array('productCode','productName','productGamePrice','productPrice','productStock');
        $input['isExclusive']='false';
        if(!empty($input['exclusiveAccount'])){
            $input['isExclusive']='true';
        }else{
            $input['exclusiveAccount']='';
        }

        if(Input::get('id')){
            $input['productCode']=Input::get('productCode');
            $input['productId']=Input::get('id');
            $type='修改';
            if(Input::get('xbigpic_1')) $input['productImgpath']['detailPicArray']['0']=Input::get('xbigpic_1');
            if(Input::get('xbigpic_2')) $input['productImgpath']['detailPicArray']['1']=Input::get('xbigpic_2');
            if(Input::get('xbigpic_3')) $input['productImgpath']['detailPicArray']['2']=Input::get('xbigpic_3');
            if(Input::get('xlistPic_')) $input['productImgpath']['listPic']=Input::get('xlistPic_');
        }else{
            $input['productCode']=md5(uniqid('productCode'));
        }



        if(empty($input['isNeedTemplate'])){
            $input['isNeedTemplate']='false';
        }
        foreach($biTian as $key => $value){
            if(empty($input[$value]) && $input[$value] !=0){
                return $this->back()->with('global_tips','商品'.$type.'失败');
            }
        }

        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        
        //大图
        if(Input::hasFile('bigpic_1')){
            //$input['productImgpath']['detailPic']='12345';
            $file = Input::file('bigpic_1');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            if($file_path) $input['productImgpath']['detailPicArray']['0']=$dir.$new_filename . '.' . $mime;
        }
        if(Input::hasFile('bigpic_2')){
            $file = Input::file('bigpic_2');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            if($file_path) $input['productImgpath']['detailPicArray']['1']=$dir.$new_filename . '.' . $mime;
        }
        if(Input::hasFile('bigpic_3')){

            $file = Input::file('bigpic_3');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            if($file_path) $input['productImgpath']['detailPicArray']['2']=$dir.$new_filename . '.' . $mime;
        }
        //列表图
        if(Input::hasFile('listpic')){
            $file = Input::file('listpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path =$file->move($path,$new_filename . '.' . $mime );
            //if($file_path)  $input['productImgpath']['listPic']=$this->myurl.$dir.$new_filename . '.' . $mime;
            if($file_path)  $input['productImgpath']['listPic']=$dir.$new_filename . '.' . $mime;
        }
        if(Input::get('startTime')){
            $input['startTime']=date('Y-m-d H:i:s',strtotime(Input::get('startTime')));
        }
        if(Input::get('endTime')){
            $input['endTime']=date('Y-m-d H:i:s',strtotime(Input::get('endTime')));
            if($input['endTime'] > '2037-12-31 23:59:59'){
                return $this->back()->withInput()->with('global_tips','抱歉，结束时间不能超过 2037-12-31 23:59:59');
            }
        }

        //$input['extraReq']['addType'] = 0;
        //上架设定
        switch($input['shelf_set']){
            case '1': //上架
                $input['isOnshelf'] = 'true';
                $input['extraReq']['onshelfAtBegin'] = 'false';
                $input['startTime']=date('Y-m-d H:i:s',time());
                break;
            case '2': //下架
                $input['isOnshelf'] = 'false';
                $input['extraReq']['onshelfAtBegin'] = 'false';
                break;
            case '3': //自动上架
                $input['isOnshelf'] = 'false';
                $input['extraReq']['onshelfAtBegin'] = 'true';
        }

        $input['extraReq'] = json_encode($input['extraReq']);
        if($type == '修改'){

            $result=ProductService::modifyProduct($input);
        }else{
            $result=ProductService::addProduct($input);
        }
        if($result['errorCode']==0){
            return $this->redirect('v4aproduct/goods/list')->with('global_tips','商品'.$type.'成功');
        }else{
            return $this->redirect('v4aproduct/goods/list')->with('global_tips','商品'.$type.'错误');
        }
    }

    



    /** 增加/修改 商品种类  **/
    public function postCateAddEdit($cate_id=0){
        $input = Input::only('categoryName','parentId','spUrl','isBelongUs','categorySort','categoryDesc','creator');
        $biTian=array('categoryName','parentId');
        $url='product/add_category';
        //如果是数据修改
        if(!empty($_POST['id'])){
            $input  = $input + array('Id'=>Input::get('id'));
            $biTian = $biTian + array('Id');
            $url='product/update_category';
            if(Input::get('xcategoryImgpath')) $input['categoryImgpath']=Input::get('xcategoryImgpath');
        }
        foreach($biTian as $key => $value){
            if(empty($input[$value]) && !is_numeric($input[$value])){
                return $this->back()->with('global_tips','商品种类（增加/修改）失败');
            }
        }
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //列表图
        if(Input::hasFile('categoryImgpath')){
            $file = Input::file('categoryImgpath'); 
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path =$file->move($path,$new_filename . '.' . $mime );
            //if($file_path)  $input['categoryImgpath']=$this->myurl.$dir.$new_filename . '.' . $mime;
            if($file_path)  $input['categoryImgpath']=$dir.$new_filename . '.' . $mime;
        }
        $input['currencyType']=0;
        $result=ProductService::addEditCate($input,$url);
        if($result['errorCode'] == 0){
            return $this->redirect('v4aproduct/goods/cate-list')->with('global_tips','商品种类（增加/修改）成功');
        }else{
            self::errorHtml();
        }
    }
    //关闭商品活动
    public function getActivityClose($activityid,$status){
        if(!empty($activityid)){
            $params['activityId']=$activityid;
            $result=ProductService::OpenOrCloseProductactivity($params,$status);
            if($result['errorCode'] == 0){
                return $this->redirect('v4aproduct/goods/product-activity-list')->with('global_tips','操作成功');
            }else{
                return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']); 
            }
        }else{
           header("Content-type: text/html; charset=utf-8");
           return $this->back()->with('global_tips','操作失败->编号丢失'); 
        }
    }
    //增加/修改 商品活动
    public function postActivityAddEdit(){
        $input = Input::only('isDiscount','discountPrice','discountGamePrice','startTime','endTime','productCode','isProductLimit','limitMode','totalNumber','updateRestTime','onOrOff','description');
        $biTian=array('productCode'=>'required','startTime'=>'required|date','endTime'=>'required|date');
        $message = array(
            'required' => '不能为空',
            'date' => '必须为日期',
        );

        if($input['limitMode'] == 'on'){
            $biTian['updateRestTime']='required|date_format:H:i:s';
            $input['limitMode']=1;
            $message['date_format']='更新剩余量时间日期格式不对';
        }else{
            $input['limitMode']=0;
            $input['updateRestTime'] ='00:00:00';
        }

        $url='product/add_productactivity';
        //如果是数据修改
        if(!empty($_POST['activityId'])){
            $input['activityId']  = Input::get('activityId');
            $biTian['activityId'] ='required';
            $url='product/modify_productactivity';
            if(empty($input['isDiscount']) ){
                $input['isDiscount']='false';
                $input['discountPrice'] = 0;
                $input['discountGamePrice'] = 0;
            }
        }
        $validator = Validator::make($input,$biTian,$message);
        if ($validator->fails()){
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                $strerror[]=$message;
            }
            return $this->back()->with('global_tips',join('-',$strerror));
        }

        if($input['isProductLimit'] == 'on'){
            $input['isProductLimit'] = 'true';
        }
        if($input['isDiscount'] == 'on'){
            $input['isDiscount'] = 'true';
        }
        if($input['onOrOff'] == 'on'){
            $input['onOrOff'] = 'true'; 
        }
        //统一做了 添加修改
        $result=ProductService::addProductActivity($input,$url);
        if($result['errorCode'] == 0){
            return $this->redirect('v4aproduct/goods/product-activity-list')->with('global_tips','操作成功');
        }else{
             header("Content-type: text/html; charset=utf-8");
             return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
        }

    }
    /**许愿帖 发帖**/
    public function postSaveRule()
    {
        $tid = (int)Input::get('tid');
        $subject = Input::get('subject');
        $message = Input::get('format_message','');
        if(!empty($tid)){
            $params=array(
                'subject' => $subject,
                'formatContent' => $message,
                'tid' => $tid
            );
        }else{
            $params=array(
                'fid' => 0,
                'bid' => 0,
                'subject' => $subject,
                'fromTag' => 3,
                'displayOrder' => 0,
                'isAdmin' =>'true',
                'formatContent' => $message
            );
        }
        $res=ProductService::saverule($params);
        if($res){
            return $this->redirect('v4aproduct/goods/rule')->with('global_tips','许愿规则保存/修改成功');
        }else{
            return $this->back()->with('global_tips','许愿规则保存/修改失败');
        }
    }


    /**商品订单修改**/
    public function postOrderAddEdit()
    {
        //product-order-list
        $input = Input::only('orderId','address','postCode','phone','orderDesc');//print_r($input);exit;
        if(!empty($input['orderId'])){
            $result=ProductService::modifyOrder($input);

            if($result['errorCode']==0){
                return $this->redirect('v4aproduct/goods/product-order-list')->with('global_tips','商品订单修改成功');
            }else{
                return $this->back()->with('global_tips','商品订单修改失败');
            }
        }
        return $this->back()->with('global_tips','商品订单编号获取失败');
    }

    /**视图卡密 **/
    public function getCardList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['pageSize'] =15;
        $params['onOrOff']=Input::get('onOrOff');
        $params['onOrOff'] ='true';
        if(Input::get('onOrOff')==0 && is_numeric(Input::get('onOrOff'))){
            $params['onOrOff']='false';
        }
        if(Input::get('cardDesc')){
            $params['cardDesc']=Input::get('cardDesc');
        }
        $result=ProductService::getvirtualcardlist($params);
        
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$data,$params['pageSize']);//print_r($data);
            $data['url']=ProductService::getReturnUrl();
            return $this->display('card-list',$data);
        }
        self::error_html($result);
    }
    /**视图卡密种类修改添加 **/
    public function getCardAddEdit($cardid='')
    {
        $data['edit']=0;
        if(!empty($cardid)){
                $data['edit']=1;
                $params['cardCode']=$cardid;
                $result=ProductService::getvirtualcardlist($params);//print_r($result);exit;
                if($result['errorCode']==0){
                    if(!empty($result['result']['0'])){
                        $data['card']=$result['result']['0'];
                        return $this->display('card-edit',$data);
                    }else{
                        return $this->back()->with('global_tips','查询失败');
                    }
                   
                }else{
                    return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
                }
        }
        
        return $this->display('card-edit',$data);

    }
    /**卡密种类修改添加 **/
    public function postCardAddEdit(){
           $input = Input::only('cardCode','cardType','cardDesc');
           $biTian=array('cardCode','cardType');
           $url='virtualcard/add';

           //如果是数据修改
           if(!empty($_POST['id'])){
                 $url='virtualcard/list_update';
                 $input['id']=$_POST['id'];
            }else{
                 $str='cardCode';
                 $input['cardCode']= md5(uniqid($str));
            }
            foreach($biTian as $key => $value){
                if(empty($input[$value]) && $input[$value] !=0){
                    return $this->back()->with('global_tips','操作失败');
                }
            }
           $result=ProductService::addeditcard($input,'virtualcard/add');
           if($result['errorCode']==0){
                return $this->redirect('v4aproduct/goods/card-list')->with('global_tips','操作成功');
           }else{
                header("Content-type: text/html; charset=utf-8");
                return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
           }
           
           
    }

    /** 卡密导入 */
    public function postImport()
    {   
        if(!Input::hasFile('importFile')){
             return $this->back()->with('global_tips','卡密文件不存在');
        }
        $input['cardCode']=Input::get('cardCode');
        $input['importDesc']=Input::get('importDesc');
        $input['expTimeStr']=Input::get('expTimeStr');
        $input['type']='txt';
        $input['cardAmountStr']=Input::get('cardAmountStr');
        
        $result=ProductService::importcard($input,$_FILES);
        if($result['errorCode']==0){
            return $this->redirect('v4aproduct/goods/card-list')->with('global_tips','导入成功');
        }else{
            self::error_html($result);
        }


    }

    /**视图 卡密导入*/
    public function getImport($cardcode='')
    {  
        $data['card']['cardCode']=$cardcode;
        return $this->display('card-import',$data);
    } 

    /** 卡密列表 date('Y-m-d H:i:s',strtotime($expTimeStr));**/
    public function getCardCodeList($cardcode=''){
            $data = $params = array();
            $params['pageIndex'] = Input::get('page',1);
            $params['pageSize'] =10;
            $params['cardCode'] = $cardcode;
            $result=ProductService::getvirtualcardcodelist($params);
            
            if($result['errorCode']==0){
                $data=self::processingInterface($result,$data);
                return $this->display('cardcode-list',$data);
            }
            header("Content-type: text/html; charset=utf-8");
            return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
    }

    public function getCardDel($cardinfoId='',$cardcode='')
    {
        $params=array();
        $params['cardinfoId']=$cardinfoId;
        if(!empty($params['cardinfoId'])){
            $result=ProductService::deleteCard($params);
            if($result['errorCode']==0){
                return $this->redirect('v4aproduct/goods/card-code-list/'.$cardcode)->with('global_tips','操作成功');
            }
            return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
        }
        header("Content-type: text/html; charset=utf-8");
        return $this->back()->with('global_tips','参数缺失!');
    }

    //卡密上架 下架方法
    /**
     * @param $goods_id
     * @param $status
     * @return mixed
     */
    public function getCardStatus($goods_id,$status)
    {
        $params=array('id'=>$goods_id);
        $params['onOrOff']=$status;
        $result=ProductService::changestatuscard($params);
        if($result['errorCode']==0){
              return $this->redirect('v4aproduct/goods/card-list')->with('global_tips','卡密属性修改成功');
        }
        header("Content-type: text/html; charset=utf-8");
        return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
    }
    
    /***商城数据*****/
    public function getProductDataList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =17;
        $arr=array('timeBegin','timeEnd','onOrOff','productName');
        foreach ($arr as $key => $value) {
             $val=Input::get($value);
             if(!empty($val) and ($value == 'timeBegin' or $value == 'timeEnd')){
                 $val=date('Y-m-d H:i:s',strtotime($val));
             }
             $data[$value]=$params[$value]=$val;
        }
        $result=ProductService::getExportquery($params);
        if($result['errorCode']==0){
            $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$params['pageSize']);
            unset($params['pageIndex']);
            $pager->appends($params);
            $data['pagelinks'] = $pager->links();
            $data['datalist'] = !empty($result['result'])?$result['result']:array();
            $data['url']=ProductService::getExportUrl();
            return $this->display('product-data',$data);
        }
    }
    public function getExport()
    {
        $params=array();
        $arr=array('timeBegin','timeEnd','onOrOff','productName');
        foreach ($arr as $key => $value) {
             $val=Input::get($value);
             if(!empty($val) && $value == 'timeBegin'){ 
                $val=date('Y-m-d H:i:s',strtotime($val));
             }elseif(!empty($val) &&  $value == 'timeEnd'){
                $val=date('Y-m-d H:i:s',strtotime($val));
             }
             $data[$value]=$params[$value]=$val;
        }
        $result=ProductService::getexport($params);
        if($result['errorCode']==0){
            return $this->json($result);
        }
        return $this->json($result);
    }

    public function getCardDownload()
    {   
        $status=Input::get('status');
        $cardNumber=Input::get('cardNumber'); 
        $data['url']=ProductService::getReturnUrl();
        $cardCode=Input::get('cardCode');
        if(!empty($cardNumber)){ 
           $data['url']=ProductService::getReturnUrl().'?status='.$status.'&cardNumber='.$cardNumber.'&cardCode='.$cardCode;
        }else{
           $data['url']=ProductService::getReturnUrl().'?status='.$status.'&cardCode='.$cardCode;
        } 
        $str=date("YmdHis").'卡密数据提取.txt';
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=$str"); 
        readfile($data['url']);
        exit;
    }
    
    public function getProductDataDownload()
    {   
        $params='';
        $arr=array('timeBegin','timeEnd','onOrOff','productName');
        foreach ($arr as $key => $value){
             $val=Input::get($value);
             if(!empty($val) && $value == 'timeBegin'){ 
                $val=date('Y-m-d H:i:s',strtotime($val));
                $val = urlencode($val);
             }elseif(!empty($val) &&  $value == 'timeEnd'){
                $val=date('Y-m-d H:i:s',strtotime($val));
                $val = urlencode($val);
             }
             if(!empty($val)){
                 $params.=$value.'='.$val.'&';
             }
            
        }
        $params=rtrim($params,'&');
        $data['url']=ProductService::getExportUrl();
        if(!empty($params)){
            $data['url']=ProductService::getExportUrl().'?'.$params;
            Log::info($data['url']);
        }
        $str=date("YmdHis").'商城数据提取.xls'; 
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=$str");
        readfile($data['url']);
        exit;
    }


    public function  getForm()
    {   
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        if(Input::get("templateName")){
             $params['templateName']=Input::get("templateName");
        }
        $result=ProductService::getFormList($params); 
        //print_r($result);exit;
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            return $this->display('form-list',$data);
        }
        self::error_html($result);    
    }
    /***
    [detailId] =&gt; 1
    [templateId] =&gt; 1
    [detailKey] =&gt; 电话
    [sort] =&gt; 1
    [notNull] =&gt; 
    [needEncrypt] =&gt; 
    [isActive] =&gt; 1
    ***/
    public function getAddEditForm($id=0,$name='')
    {   
        $data=array();$data['count']=0;
        if(empty($id)) return $this->display('add-edit-form',$data);
        $uid=$this->getSessionData('youxiduo_admin');
        if(empty($uid['id'])) return $this->redirect('v4aproduct/goods/form')->with('global_tips','用户名获取失败');  
        $data=ProductService::getTemplate(array('templateId'=>$id));
        if($data['errorCode']==0){
            $sort=end($data['result']); 
            return $this->display('add-edit-form',array('info'=>$data['result'],'count'=>!empty($sort['sort'])?$sort['sort']:0,'templateName'=>$name,'templateId'=>$id));
        }
        return $this->redirect('v4aproduct/goods/form')->with('global_tips','编辑接口调用失败。');  
    }

    public function postFormSave()
    {
        $input=Input::only('templateId','sort','notNull','detailId','templateName','input_name','needEncrypt','count');
        $params['templateName']=$input['templateName'];
        $size=sizeof($input['input_name']);
        
        for($i=0;$i<$size;$i++){
            if(!empty($input['input_name'][$i])){
                   
                    $params['templateDetailList'][$i]=array(
                       'detailKey'=>$input['input_name'][$i],
                       'sort'=>intval($input['sort'][$i]),
                       'notNull'=>!empty($input['notNull'][$i]) && $input['notNull'][$i]==1?'true':'false',
                       'needEncrypt'=>!empty($input['needEncrypt'][$i]) && $input['needEncrypt'][$i]==1?'true':'false',
                    );
                     if(!empty($input['detailId'][$i])){
                            $params['templateDetailList'][$i]['detailId']=$input['detailId'][$i];
                          //$params['templateDetailList'][$i]['sort']=intval($input['sort'][$i]);      
                    }
                    /****
                    if(!empty($input['detailId'][$i])){
                            $params['templateDetailList'][$i]['detailId']=$input['detailId'][$i];
                            $params['templateDetailList'][$i]['sort']=intval($input['sort'][$i]);
                    }else{  
                           $input['count']=$input['count']+1;
                           $params['templateDetailList'][$i]['sort'] =$input['count'];
                    }
                    ***/
                    //'sort'=>!empty($input['sort'][$i]) ? intval($input['sort'][$i])+1:intval($input['sort'][$i])++,
            }else{
                 return $this->back()->with('global_tips','操作失败');
            } 
       }
       if(!empty($input['templateId'])){
            $params['templateId']=$input['templateId'];
       }
      
       $result=ProductService::add_form($params);
       if($result['errorCode']==0)
       {
          return $this->redirect('v4aproduct/goods/form')->with('global_tips','操作成功');
       }
     }

     public function getDeleteForm($id=0)
     {
            if(empty($id)){
                return $this->back()->with('global_tips','操作失败-ID缺失');
            }
            $result=ProductService::deleteForm(array('templateId'=>$id));
            if($result['errorCode']==0){
                 return $this->redirect('v4aproduct/goods/form')->with('global_tips','操作成功');
            }
            return $this->back()->with('global_tips','操作失败');
     }


     //商品推荐位列表
     public function getRecommend()
     {
        $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        $params['location']=Input::get('location');
        $result=ProductService::RecommendList($params);

         if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            return $this->display('recommend-list',$data);
        }
        self::error_html($result);
    }

    public function getRecommendDelect($id=0)
    {
        $params['recommendId']=$id;
        $params['isActive']='false';
        $result=ProductService::modifyrecommend($params);
        if($result['errorCode'] == 0){
            return $this->redirect('v4aproduct/goods/recommend')->with('global_tips','操作成功');
        }
        return $this->redirect('v4aproduct/goods/recommend')->with('global_tips','操作失败');
    }

    //商品推荐位添加修改
    public function getRecommendAddEdit($id=0)
    {   
       $data=array();
       if(empty($id)) return $this->display('recommend-edit',$data);
       $params['recommendId']=$id;
       $result=ProductService::RecommendList($params);
       if($result['errorCode'] == 0){
            $data['recommend']=$result['result']['0'];
            return $this->display('recommend-edit',$data);
       }
       self::error_html($result);
    }

    
    public function postRecommendAddEdit()
    {
        $input=Input::only('recommendId','isTop','location','showTime','productCode','title','sort','description');
        $biTian=array('productCode'=>'required','sort'=>'required');
        $message = array(
            'required' => '不能为空',
        );
        $validator = Validator::make($input,$biTian,$message);
        if($validator->fails()){
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                $strerror[]=$message;
            }
            return $this->back()->with('global_tips','商品'.$type.'失败');
        }
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        if(Input::hasFile('imgUrl')){
            $file = Input::file('imgUrl');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            if($file_path) $input['imgUrl']=$dir.$new_filename . '.' . $mime;
        }
        if(!empty($input['isTop'])){
            $input['isTop']='true';
        }else{
            $input['isTop']='false';
        }
        if(!empty($input['showTime'])){
            $input['showTime']='true';
        }else{
            $input['showTime']='false';
        }


        if(empty($input['recommendId'])){
            $result=ProductService::addrecommend($input);
        }else{
            $result=ProductService::modifyrecommend($input);
        }

        if($result['errorCode']==0){
            return $this->redirect('v4aproduct/goods/recommend')->with('global_tips','操作成功');
        }else{
            return $this->redirect('v4aproduct/goods/recommend')->with('global_tips','操作失败');
        }
    }

    public function getTemplateSelectList()
    {
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =7;
        if(Input::get("templateName")){
             $params['templateName']=Input::get("templateName");
        }
        $result=ProductService::getFormList($params); 
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params,$params['pageSize']);

            $html = $this->html('pop-template-list',$data);
            return $this->json(array('html'=>$html));
        }
    }

    public function getQuery()
    {
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =15;
        $data=array("accountId","productCode","addTimeBegin","addTimeEnd","productPriceBegin","productPriceEnd");
        foreach($data as $value){
            if(Input::get($value)){
                $params[$value]=Input::get($value);
            }
        }
        $result=ProductService::query($params); 
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params);
            return $this->display('query-list',$data);
        }

    }
    /****商城列表删除***/
    public function getGoodsdelete($id=0)
    {   
        if(empty($id)){
            //return $this->redirect('v4aproduct/goods/list')->with('global_tips','删除出错-Code丢失');
            return $this->json(array('error'=>1));
        }
        $result=ProductService::DeleteProduct(array('productCode'=>$id)); 
        if($result['errorCode']==0){
            //return $this->redirect('v4aproduct/goods/list')->with('global_tips','商品删除成功');
            return $this->json(array('error'=>0));
        }
        return $this->json(array('error'=>1));
    }

    //需要处理的订单
    public function getNeedOrderList()
    {   

        $input=Input::all();
        $input['pageSize']=15;
        if(!empty($input['page'])) $input['pageIndex']=Input::get('page',1);
        $input['active']='true';
        $input['hasAddress']='true';
        $input['orderStatus']=1;
        if(!empty($input['billTimeBegin'])){
            $input['billTimeBegin'] = date('Y-m-d H:i:s',strtotime($input['billTimeBegin']));
        }
        if(!empty($input['billTimeEnd'])){
            $input['billTimeEnd'] = date('Y-m-d H:i:s',strtotime($input['billTimeEnd']));
        }
        if(!empty($input['biller'])){
            $input['biller']=$input['biller'];
        }

        $result=ProductService::ProductOrderList($input);

        if($result['errorCode']==0 ){

            $params=array();
            if(!empty($result['result'])) {
                foreach ($result['result'] as $val) {
                    $params[] = $val['biller'];
                }
            }
            $uids=array_flip(array_flip($params));
            $users_level = CreditAccount::getUserCreditByUids(array_flip(array_flip($uids)));
            foreach($result['result'] as &$row){
                if(isset($users_level[$row['biller']])){
                    $level = CreditLevel::getUserLevel($users_level[$row['biller']]['experience']);
                    $row['experience'] = $users_level[$row['biller']]['experience'];
                    $row['level_name'] = $level['name'];
                    $row['level_max'] = $level['end'];
                }else{
                    $row['experience'] = 0;
                    $row['level_name'] = '1';
                    $row['level_max'] = '50';
                }
                if(!empty($row['mobile'])){
                    $row['mobile'] = preg_replace('/(1[3578]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$row['mobile']);
                }
                $out[] = $row;
            }
            if(!empty($params)){
                $params=UserService::getMultiUserInfoByUids($uids,'full');
                if(!empty($params)){
                    $data['userinfo']=array();
                    foreach($params as $val_){
                        $data['userinfo'][$val_['uid']]=array('nickname'=>$val_['nickname'],'mobile'=>$val_['mobile']);
                    }   
                }
            }
            $data=self::processingInterface($result,$input,15);
            $data['search']=$input;
            return $this->display('need-list',$data);
        }
        self::error_html($result);
    
    }

    public function getDelivery($id='', $uid='',$productcode,$orderSmsInfo='')
    {

        if(empty($id) || empty($uid) || empty($productcode)){
             return $this->redirect('v4aproduct/goods/need-order-list')->with('global_tips','参数缺失');
        }

        $result=ProductService::update_orderdeliver(array('orderId'=>$id));
        if($result['errorCode']==0 ){
            $info = UserDevice::getNewestInfoByUid($uid);
            if(!$info){
                return $this->redirect('v4aproduct/goods/need-order-list')->with('global_tips','调用失败-没有用户');
            }
            $channelId = $info['channel_id'];
            $userId = $info['device_id'];
            $append = array('msg'=>'您购买的'.!empty($orderSmsInfo)?$orderSmsInfo:'商品'.'已经发货成功，如有问题请联系客服QQ：2985659531','linktype'=>16,'link'=>$productcode);
            BaiduPushService::pushUnicastMessage('商城购买','',16,-1,0,$uid, $channelId, $userId,$append,false,true);
            return $this->redirect('v4aproduct/goods/need-order-list')->with('global_tips','操作成功');
        }
        return $this->redirect('v4aproduct/goods/need-order-list')->with('global_tips',$result['errorDescription']);

    }
    public function getUpdataAll()
    {
        $input=Input::all();
        if(!empty($input['dpStart_updata'])){
            $str['billTimeBegin']=$input['dpStart_updata'];
        }
        if(!empty($input['dpEnd_updata'])){
            $str['billTimeEnd']=$input['dpEnd_updata'];
        }
        if(!empty($input['biller_updata'])){
            $str['biller']=$input['biller_updata'];
        }
        if(empty($input))  return $this->back()->with('global_tips','调用失败');
        unset($input['dpEnd_updata'],$input['dpStart_updata'],$input['biller_updata']);
        foreach ($input as $key => $value) {
            # code...
            $arr=explode("-",$value);
            if(empty($arr['0'])){ return $this->back()->with('global_tips','订单号失败'); }
            $result=ProductService::update_orderdeliver(array('orderId'=>$arr['0']));
            if($result['errorCode']!=0) return $this->back()->with('global_tips','订单号：'.$arr['0'].' 请求失败');
        }
        if(!empty($str)){
            return $this->redirect('v4aproduct/goods/need-order-list?'.http_build_query($str))->with('global_tips','操作成功');
        }
        return $this->redirect('v4aproduct/goods/need-order-list')->with('global_tips','操作成功');
    }

    public function getDeliveryAll(){
        $input=Input::all();
        if(empty($input))  return $this->back()->with('global_tips','调用失败');
        if(!empty($input['dpStart_delivery'])){
            $str['billTimeBegin']=$input['dpStart_delivery'];
        }
        if(!empty($input['dpEnd_delivery'])){
            $str['billTimeEnd']=$input['dpEnd_delivery'];
        }
        if(!empty($input['biller_delivery'])){
            $str['biller']=$input['biller_delivery'];
        }
        unset($input['dpEnd_delivery'],$input['dpStart_delivery'],$input['biller_delivery']);
        foreach ($input as $key => $value) {
            # code...
            $arr=explode("-",$value);
            if(empty($arr['0'])){ return $this->back()->with('global_tips','订单号失败'); }
            $result=ProductService::update_orderdeliver(array('orderId'=>$arr['0']));
            if($result['errorCode']==0){
                 $info = UserDevice::getNewestInfoByUid($arr['1']);
                 if(!$info) return $this->back()->with('global_tips','订单号：'.$arr['0'].' 调用失败-device_id获取失败');;
                 $channelId = $info['channel_id'];
                 $userId = $info['device_id'];
                 $append = array('msg'=>'您购买的'.empty($arr['3'])?$arr['3']:''.'已经发货成功，如有问题请联系客服QQ：2985659531','linktype'=>16,'link'=>$arr['2']);
                 BaiduPushService::pushUnicastMessage('商城购买(订单号:'.$arr['0'].')','',16,-1,0,$arr['1'], $channelId, $userId,$append,false,true);
            }else{
                return $this->back()->with('global_tips','订单号：'.$arr['0'].' 请求失败');
            }
        }
        if(!empty($str)){
            return $this->redirect('v4aproduct/goods/need-order-list?'.http_build_query($str))->with('global_tips','操作成功');
        }
        return $this->redirect('v4aproduct/goods/need-order-list')->with('global_tips','操作成功');
    }

   public function postRelease(){
       $value = Input::get('value');
       $code = Input::get('code');
       $uid=$this->getSessionData('youxiduo_admin');
       if(empty($uid['id'])){
           echo json_encode(array('success'=>"false",'mess'=>'需重新登录','data'=>""));
       };
       $data = array('productCode'=>$code,'productStock'=>$value,'uid'=>$uid['id']);
       $res =  ProductService::release($data);
       if(!$res['errorCode']&&$res['result']){
           echo json_encode(array('success'=>"true",'mess'=>'修改成功','data'=>""));
       }else{
           echo json_encode(array('success'=>"false",'mess'=>$res['errorDescription'],'data'=>""));
       }
   }


    //用于生成符合前台页面SELECT标签的数组
    private static function array_select($result,$id,$val)
    {
        if($result){
            $selectInfo=array();
            foreach($result as $key=>$value){
                $selectInfo[$value[$id]]=$value[$val];
            }
            return $selectInfo;
        }
        return $result;
    }

    /**处理接口返回数据**/
    private static function processingInterface($result,$data,$pagesize=10){//echo $result['totalCount'];//exit;
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);

        unset($data['pageIndex'],$data['page']);
        $pager->appends($data);
        $data['pagelinks'] = $pager->links();//exit;
        $data['datalist'] = !empty($result['result'])?$result['result']:array();
        return $data;
    }


    /**错误输出 **/
    private static function errorHtml($result=array()){
        header("Content-type: text/html; charset=utf-8");
        echo '出错啦->:'.json_encode($result);
        exit;
    }
}