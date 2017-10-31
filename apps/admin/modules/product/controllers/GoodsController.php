<?php
namespace modules\product\controllers;
use modules\shop\models\CateModel;
use Youxiduo\V4\Game\GameService;
use Youxiduo\Mall\ProductService;
use Yxd\Modules\Message\PromptService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
//use modules\shop\models\GoodsModel;
/*
	fujiajun 4.0 后台商城 2015/3/2 
*/
class GoodsController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'product';
	}
	
	/**视图：商品管理列表**/

    public function getList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =10;
        $params['active'] ='true';
        $result=ProductService::searchProductList($params);

        if($result['errorCode']==0){

            $data=self::processingInterface($result,$data);
            return $this->display('goods-list',$data);
        }
        self::error_html($result);
    }

    /**视图：增加/修改商品 **/
    public function getProductAddEdit($goods_id=0)
    {
        $data= $params = array();
        $data['edit'] =0;
        $data['goods']['isNotice']='true';
        $data['goods']['isBelongUs']='true';
        $data['goods']['isExclusive'] ='true';
        if(!empty($goods_id)){
            $result=ProductService::searchProductList(array('id'=>$goods_id));
            if($result['errorCode']==0){
                $data['goods']=$result['result']['0'];
                if(!empty($data['goods']['img'])){
                    $img=json_decode($data['goods']['img'],true);
                    if($img['listPic']){
                        $data['goods']['listPic']=$img['listPic'];
                    }
                    if($img['detailPic']){
                        $data['goods']['bigpic_1']=$img['detailPic'];
                    }
                }
                $data['edit'] =1;
                $data['goods']['isBelongUs'] = !empty($data['goods']['isBelongUs']) ? 'true' : 'false';
                $data['goods']['isExclusive']  = !empty($data['goods']['isExclusive']) ? 'true' : 'false';
                $data['goods']['isNotice']      = !empty($data['goods']['isNotice']) ? 'true' : 'false';
                $result=ProductService::queryCategory(array('categoryId'=> !empty($data['goods']['categoryId']) ? $data['goods']['categoryId'] : ''));
                $data['categoryName'] = ($result['errorCode'] == 0) ? !empty($result['result']['0']) ? $result['result']['0']['categoryName'] : '' : error_html($result);
                $result=ProductService::getGameIdsByProductCode(array('productCode'=>$data['goods']['productCode'],'genre'=>'1'));

                if($result['errorCode']==0){
                    $data['goods']['gameid']=$result['result'];
                    $gameName=GameService::getOneInfoById($data['goods']['gameid'],'android');//print_r($gameName);
                    $data['goods']['gamename']= !empty($gameName['gname']) ? $gameName['gname'] : '';
                }
            }else{
                self::error_html($result);
            }
        }
        return $this->display('goods-edit',$data);
    }

    /**视图：商品种类列表**/
    public function getCateList()
    {
        $data = array('pageIndex'=>Input::get('page',1));
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
        $data=array('pageIndex'=>Input::get('page',1),'pageSize'=>10,'active'=>'true');
        $result=ProductService::ProductOrderList($data);
        if($result['errorCode']==0 ){
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
        $result=ProductService::queryCategory($data);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$data,$data['pageSize']);
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
            $result=ProductService::queryCategory($data);//print_r($result);
            if($result['errorCode'] == 0 ){//不为0就在查询一次

                $datainfo['cate']=$result['result']['0'];

                $datainfo['cate']['isBelongUs']=!empty($datainfo['isBelongUs']) ? 'true' : 'false';
                if(!empty($datainfo['cate']['parentId']) && $datainfo['cate']['parentId'] != 0){
                    $data['categoryId']=$datainfo['cate']['parentId'];
                    $result=ProductService::queryCategory($data);
                    if($result['errorCode'] != 0){
                        self::error_html($result);
                    }
                    $data['pcategoryName']=$result['result']['0']['categoryName'];
                }else{

                    //为0就是自身根节点
                    $data['pcategoryName']=$datainfo['cate']['categoryName'];
                }
            }else{
                self::error_html($result);
            }

        }
        //print_r( $datainfo);
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
        return $this->redirect('product/goods/list')->with('global_tips','商品属性修改成功');
    }



    /**视图 查询商品活动列表**/
    public function getProductActivityList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['pageSize'] =10;
        $result=ProductService::searchProductActivityList($params);
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
        $params['onOrOff'] ='true';
        if(Input::get('keyword')){
            $data['keyword']=$params['cardDesc']=Input::get('keyword');
        }
        $result=ProductService::getvirtualcardlist($params);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$data);
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
            default:
                # code...
                return $this->back()->with('global_tips','商品修改属性失败');
                break;
        }
        $result=ProductService::update_productextra($data);
        if($result['errorCode']==0){
            return $this->redirect('product/goods/list')->with('global_tips','商品类型修改成功');
        }
        self::error_html($result);
    }

    /**视图卡密 **/
    public function getCardList()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['pageSize'] =5;
        $params['onOrOff'] ='true';
        $result=ProductService::getvirtualcardlist($params);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$data);
            return $this->display('card-list',$data);
        }
        self::error_html($result);
    }
    /**视图卡密批量导入 **/
    public function getCardAddEdit()
    {

        $data=ProductService::getcardurl();
        return $this->display('card-edit',$data);
    }


    /** 卡密导入 */
    public function postImport()
    {   /**
    if(!Input::hasFile('filedata')){
    return $this->back()->with('global_tips','卡密文件不存在');
    }
    $file = Input::file('filedata');
    $tmpfile = $file->getRealPath();
    $filename = $file->getClientOriginalName();
    $ext = $file->getClientOriginalExtension();
    if(!in_array($ext,array('xls','xlsx'))) return $this->back()->with('global_tips','上传文件格式错误');
    $server_path = storage_path() . '/tmp/';
    $newfilename = date('YmdHis') . str_random(4). '.' . $ext;
    $target = $server_path . $newfilename;
    $file=$file->move($server_path,$newfilename);
    if($file){
    $input['cardCode']=Input::get('cardCode');;
    $input['importFile']=$target;
    $input['importDesc']=Input::get('importDesc');;
    $result=ProductService::importcard($input);
    print_r($result);exit;
    if($result['errorCode']==0){
    return $this->redirect('product/goods/card-list')->with('global_tips','导入成功');
    }else{
    self::error_html($result);
    }
    }else{
    return $this->back()->with('global_tips','上传文件失败');
    }
     **/
        if(!Input::hasFile('importFile')){
            return $this->back()->with('global_tips','卡密文件不存在');
        }
        $file = Input::file('importFile');
        $input['cardCode']=Input::get('cardCode');;
        $input['importFile']=$file;
        $input['importDesc']=Input::get('importDesc');;
        $result=ProductService::importcard($input,$_FILES);
        //print_r($result);exit;
        if($result['errorCode']==0){
            return $this->redirect('product/goods/card-list')->with('global_tips','导入成功');
        }else{
            self::error_html($result);
        }

    }




    /**增加/修改 商品POST操作 **/
    public function postProductAddEdit()
    {	$type='增加';
        $input = Input::only('productName','categoryId','cardCode','productType',
            'gameId','productGamePrice','productPrice','productCost','inventedType',
            'productStock','isNotice','isBelongUs','productSummary','productSort',
            'productInstruction','singleLimit','isTop','isHot','isNewest','isRecommend'
        );
        $biTian=array('productCode','productName','gameId','productGamePrice','productPrice','productStock');
        $input['isExclusive']='false';
        if(Input::get('productCode')){
            $input['productCode']=Input::get('productCode');
            $type='修改';
        }else{
            $input['productCode']=md5(uniqid('productCode'));
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
            $file = Input::file('bigpic_1');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            if($file_path) $input['productImgpath']['detailPic']=$dir.$new_filename . '.' . $mime;
        }
        //列表图
        if(Input::hasFile('listpic')){
            $file = Input::file('listpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path =$file->move($path,$new_filename . '.' . $mime );
            if($file_path)  $input['productImgpath']['listPic']=$dir.$new_filename . '.' . $mime;
        }
        if($type == '修改'){
            $result=ProductService::modifyProduct($input);
        }else{
            $result=ProductService::addProduct($input);
        }
        if($result['errorCode']==0){
            return $this->redirect('product/goods/list')->with('global_tips','商品'.$type.'成功');
        }else{
            self::error_html($result);
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
            if($file_path)  $input['categoryImgpath']=$dir.$new_filename . '.' . $mime;
        }
        $result=ProductService::addEditCate($input,$url);
        if($result['errorCode'] == 0){
            return $this->redirect('product/goods/cate-list')->with('global_tips','商品种类（增加/修改）成功');
        }else{
            self::errorHtml();
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
            $biTian=$biTian + array('updateRestTime'=>'required|date_format:H:i:s');
            $input['limitMode']=1;
            $message=$message+array('date_format'=>'更新剩余量时间日期格式不对');
        }else{
            $input['limitMode']=0;
            $input['updateRestTime'] ='00:00:00';
        }

        $url='product/add_productactivity';
        //如果是数据修改
        if(!empty($_POST['activityId'])){
            $input  = $input + array('activityId'=>Input::get('activityId'));
            $biTian = $biTian + array('activityId'=>'required');
            $url='product/modify_productactivity';
        }
        $validator = Validator::make($input,$biTian,$message);
        if ($validator->fails()){
            return $this->back()->with('global_tips','请正确填写相关数据');
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
            return $this->redirect('product/goods/product-activity-list')->with('global_tips','商品活动修改成功');
        }else{
            self::errorHtml();
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
            return $this->redirect('product/goods/rule')->with('global_tips','许愿规则保存/修改成功');
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
                return $this->redirect('product/goods/product-order-list')->with('global_tips','商品订单修改成功');
            }else{
                return $this->back()->with('global_tips','商品订单修改失败');
            }
        }
        return $this->back()->with('global_tips','商品订单编号获取失败');
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
    private static function processingInterface($result,$data,$pagesize=10){
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        unset($data['pageIndex']);
        $pager->appends($data);
        $data['pagelinks'] = $pager->links();
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
