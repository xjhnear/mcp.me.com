<?php
namespace modules\v4a_giftbag\controllers;

use Youxiduo\Mall\ProductService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Yxd\Modules\Core\BackendController;
use Youxiduo\V4\User\UserService;
class PackageController extends BackendController{
    public function _initialize(){
        $this->current_module = 'v4a_giftbag';
    }

    //礼包库
    public function getSearch(){
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =15;

        $arr=array('isEmpty','sign','editor','qEmpty');
        $uid=$this->getSessionData('youxiduo_admin');
        foreach($arr as $v){
            if(Input::get($v) && Input::get($v) != 'false'){
                $params[$v]="true";
            }
        }
        if(Input::get('cardDesc')){
            $params['cardDesc']=Input::get('cardDesc');
        }
        $params['gid']=Input::get('gid');
        $params['onOrOff'] ="true";
        $params['isActive']="true";
        $params['signer']= $uid['id'];
        if(Input::get('timeBegin')){
            $params['timeBegin']=date('Y-m-d H:i:s',strtotime(Input::get('timeBegin')));
        }
        if(Input::get('timeEnd')){
            $params['timeEnd']=date('Y-m-d H:i:s',strtotime(Input::get('timeEnd')));
        }
        if(!empty($params['editor']) && $params['editor'] == 'true') $params['editor']=$uid['id'];
        $result=ProductService::getvirtualcardlist($params,1);//print_r($result);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params,$params['pageSize']);
            $data['url']=ProductService::getReturnUrl();
            return $this->display('package/package-list',$data);
        }
        return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
    }

    //添加礼包库
    public function getAdd()
    {
        return $this->display('package/package-add');
    }

    public function getDelect($id)
    {

        if(empty($id)){
            return $this->redirect('v4agiftbag/package/search')->with('global_tips','参数丢失');
        }
        $params['id']=$id;
        $params['onOrOff']='false';
        $params['isActive']='false';
        $result=ProductService::changestatuscard($params);//print_r($result);exit;    
        if($result['errorCode'] == 0){
            return $this->redirect('v4agiftbag/package/search')->with('global_tips','操作成功');
        }else{
            return $this->redirect('v4agiftbag/package/search')->with('global_tips','操作失败');
        }
    }

    //实物库
    public function getSearchMaterial(){
        $data = $params = array();
        $params['pageIndex'] = Input::get('page',1);
        $params['pageSize'] =15;

        $arr=array('isEmpty','sign','editor','qEmpty');
        $uid=$this->getSessionData('youxiduo_admin');
        foreach($arr as $v){
            if(Input::get($v) && Input::get($v) != 'false'){
                $params[$v]="true";
            }
        }
        if(Input::get('materialDesc')){
            $params['materialDesc']=Input::get('materialDesc');
        }
        $params['onOrOff'] ="true";
        $params['isActive']="true";
        $params['signer']= $uid['id'];
        if(Input::get('timeBegin')){
            $params['timeBegin']=date('Y-m-d 00:00:00',strtotime(Input::get('timeBegin')));
        }
        if(Input::get('timeEnd')){
            $params['timeEnd']=date('Y-m-d 23:59:59',strtotime(Input::get('timeEnd')));
        }
        if(!empty($params['editor']) && $params['editor'] == 'true') $params['editor']=$uid['id'];
//        print_r($params);
        $result=ProductService::getmateriallist($params);
//        print_r($result);
        if($result['errorCode']==0){
            $data=self::processingInterface($result,$params,$params['pageSize']);
            $data['url']=ProductService::getReturnUrl();
            return $this->display('package/material-list',$data);
        }
        return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
    }

    //添加礼包库
    public function getAddMaterial()
    {

        $data = array();
        $data['materialUse'] = array('1'=>"活动",'2'=>'商品','3'=>'任务');
        if(Input::get('id',"")){
            $arr = array('materialCode'=>Input::get('id',""));
            $result=ProductService::getmateriallist($arr);
            if(!$result['errorCode']&&$result['result']){
                $data['data'] = $result['result'][0];
            }
        }
//        print_r($data);
        return $this->display('package/material-add',$data);
    }


    /** 卡密实物入库 */
    public function postAddMaterial()
    {
        $params['materialCode']=$input['materialCode']=md5(uniqid('materialCode'));
        $input['materialType']=0;
        $input['materialDesc']=Input::get('materialDesc');
        $input['materialStock']=Input::get('materialStock');
        $input['materialSummary']=Input::get('productSummary');
        $input['materialInstruction']=Input::get('productInstruction');
        $input['materialUse']=Input::get('materialUse');
        $input['gid']=Input::get('game_id');
        $input['gname']=Input::get('game_name');
        $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        //大图
        if(Input::hasFile('bigpic_1')){
            $file = Input::file('bigpic_1');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            //if($file_path) $input['productImgpath']['detailPic']=$this->myurl.$dir.$new_filename . '.' . $mime;
//            if($file_path) $input['imgPath']['detailPic']=$dir.$new_filename . '.' . $mime;
            if($file_path) $input['imgPath']['detailPicArray']['0']=$dir.$new_filename . '.' . $mime;
        }else{
            $input['imgPath']['detailPicArray']['0'] = Input::get("img1");
        }
        if(Input::hasFile('bigpic_2')){
            $file = Input::file('bigpic_2');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            if($file_path) $input['imgPath']['detailPicArray']['1']=$dir.$new_filename . '.' . $mime;
        }else{
            $input['imgPath']['detailPicArray']['1'] = Input::get("img2");
        }
        if(Input::hasFile('bigpic_3')){

            $file = Input::file('bigpic_3');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            if($file_path) $input['imgPath']['detailPicArray']['2']=$dir.$new_filename . '.' . $mime;
        }else{
            $input['imgPath']['detailPicArray']['2'] = Input::get("img3");
        }
        //列表图
        if(Input::hasFile('listpic')){
            $file = Input::file('listpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path =$file->move($path,$new_filename . '.' . $mime );
            //if($file_path)  $input['productImgpath']['listPic']=$this->myurl.$dir.$new_filename . '.' . $mime;
            if($file_path)  $input['imgPath']['listPic']=$dir.$new_filename . '.' . $mime;
        }else{
            $input['imgPath']['listPic'] = Input::get("img_list");
        }
//        print_r(Input::get());print_r($input);die;
        if(Input::get('materialCode')){
            $input['materialCode'] = Input::get('materialCode');
            $result=ProductService::updateMaterial($input);
        }else{
            $result=ProductService::addMaterial($input);
        }

//        print_r($input);print_r($result);die;
        if($result['errorCode']==0){
            return $this->redirect('v4agiftbag/package/search-material')->with('global_tips','创建成功!');
        }
        return $this->back()->with('global_tips','入库失败！');
    }

    public function getDelectMaterial($id)
    {
        if(empty($id)){
            return $this->redirect('v4agiftbag/package/search-material')->with('global_tips','参数丢失');
        }
        $params['id']=$id;
        $params['onOrOff']='false';
        $params['isActive']='false';
        $result=ProductService::changestatus($params);
        if($result['errorCode'] == 0){
            return $this->redirect('v4agiftbag/package/search-material')->with('global_tips','操作成功');
        }else{
            return $this->redirect('v4agiftbag/package/search-material')->with('global_tips','操作失败');
        }
    }
    /** 卡密入库 */
    public function postAdd()
    {
        $params['cardCode']=$input['cardCode']=md5(uniqid('cardCode'));
        $input['cardType']=1;
        $input['cardDesc']=Input::get('cardDesc');
        $input['virtualUse']=Input::get('virtualUse');
        $params['expTimeStr']= date('Y',time()) + 20 . '-' . date('m-d H:i:s'); //50年后日期
        $input['virtualInstruction']=Input::get('virtualInstruction');
        $input['virtualSummary']=Input::get('virtualSummary');
        $input['imgPath']=array('listPic'=>Input::get('virtualImgpath'),'detailPic'=>Input::get('virtualImgpath'));
        $input['onGift']=Input::get('onGift')=='on'?'true':'false';
        $input['gid']=Input::get('gid');
        $input['gname']=Input::get('gname');
        $result=ProductService::addeditcard($input,'virtualcard/add');
        if($result['errorCode']==0){
            if(!is_file(Input::get('tmp'))){
                return $this->back()->with('global_tips','卡密文件不存在');
            }
            $params['cardAmountStr']=1;
            $filename=Input::get('filename');
            $type=explode("." , $filename);
            $type=end($type);
            if($type == 'txt')
                $params['type']=$type;
            $file['importFile']=array('tmp_name'=>Input::get('tmp'),'type'=>$type,'name'=>$filename);

            $result=ProductService::importcard($params,$file);
            if($result['errorCode']==0){
                return $this->redirect('v4agiftbag/package/search')->with('global_tips','创建成功!');
            }
        }
        return $this->back()->with('global_tips','入库失败！');
    }

    public function getSign($cardCode,$sign)
    {
        $uid=parent::getSessionUserUid();
        if(empty($cardCode)  || empty($uid)){
            return $this->back()->with('global_tips','操作失败->参数缺失');
        }
        $result=ProductService::getSign($uid,$cardCode,$sign);
        if($result['errorCode']==0){
            return $this->redirect('v4agiftbag/package/search')->with('global_tips','操作成功');
        }
    }

    public function getCardDownload()
    {
        $status=Input::get('status');
        $cardNumber=Input::get('cardNumber');
        $data['url']=ProductService::getReturnUrl();
        $cardCode=Input::get('cardCode');
        $uid=$this->getSessionData('youxiduo_admin');
        if(!empty($cardNumber)){
            $data['url']=ProductService::getReturnUrl().'?status='.$status.'&cardNumber='.$cardNumber.'&cardCode='.$cardCode.'&modifier='.$uid['username'];

        }else{
            $data['url']=ProductService::getReturnUrl().'?status='.$status.'&cardCode='.$cardCode.'&modifier='.$uid['username'];
        }
        $str=date("YmdHis").'卡密数据提取.txt';
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=$str");
        readfile($data['url']);
        exit;

    }
    /***
    $file = Input::file('filedata');
    $tmpfile = $file->getRealPath();
    $filename = $file->getClientOriginalName();
    $ext = $file->getClientOriginalExtension();
     ***/
    public function postFileCount()
    {
        if(!Input::hasFile('importFile')){
            //return $this->back()->with('global_tips','卡密文件不存在');
            echo json_encode(array('errorCode'=>1,'errortxt'=>'礼包文件不存在'));
            exit;
        }
        $file = Input::file('importFile');
        $ext = $file->getClientOriginalExtension();
        $filename = $file->getClientOriginalName();
        if($ext != 'txt' && $ext != 'csv'){
            echo json_encode(array('errorCode'=>1,'errortxt'=>'礼包文件格式错误'));
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
        return json_encode(array('errorCode'=>0,'size'=>count($arr),'file'=>array('tmp'=>$path.$new_filename.'.'.$ext,'filename'=>$filename)));
        exit;
    }


    public function postAjaxUploadAppend(){
        if(!Input::get('card_code'))  return json_encode(array('state'=>0,'msg'=>'没有礼包码'));
        if(!Input::get('tmp'))  return json_encode(array('state'=>0,'msg'=>'卡密文件不存在'));
        $input = Input::all();
        //追加礼包
        $filename=Input::get('filename');
        $result=ProductService::importcard(array('cardAmountStr'=>0,'type'=>'txt','importDesc'=>'导入','cardCode'=>$input['card_code'],'expTimeStr'=>date('Y-m-d H:i:s',strtotime('2030-01-01 00:00:00'))),array('importFile'=>array('tmp_name'=>Input::get('tmp'),'type'=>'txt','name'=>$filename)));
        if($result['errorCode']==0){
            return json_encode(array("state"=>1,'msg'=>'追加成功'));
        }else{
            return json_encode(array('state'=>0,'msg'=>'追加失败'));
        }
    }



    /***
     * @param $path
     *
     *
     */
     private function createFolder($path)
    {
        if (!file_exists($path))
        {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }

    /**视图卡密种类修改添加 *
     * @param string $cardid
     * @return
     */
    public function getCardAddEdit($cardid='')
    {
        $data['edit']=0;
        if(!empty($cardid)){
            $data['edit']=1;
            $params['cardCode']=$cardid;
            $result=ProductService::getvirtualcardlist($params);
            if($result['errorCode']==0){
                if(!empty($result['result']['0'])){
                    $data['card']=$result['result']['0'];
                    return $this->display('package/card-edit',$data);
                }else{
                    return $this->back()->with('global_tips','查询失败');
                }
            }else{
                return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
            }
        }
        return $this->display('package/card-edit',$data);
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
        $result=ProductService::addeditcard($input,$url);
        if($result['errorCode']==0){
            return $this->redirect('v4agiftbag/package/search')->with('global_tips','操作成功');
        }else{
            return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
        }
    }


    /** 卡密导入 */
    public function postImport()
    {
        if(!Input::get('tmp')){
            return $this->back()->with('global_tips','卡密文件不存在');
        }
        $input['cardCode']=Input::get('cardCode');
        $input['importDesc']=Input::get('importDesc');
        $input['expTimeStr']=Input::get('expTimeStr');
        $input['cardAmountStr']=Input::get('cardAmountStr');
        $filename=Input::get('filename');
        $type=explode("." , $filename);
        $type=end($type);
        if($type == 'txt')
            $input['type']=$type;
        $input['type']=$type;
        $file['importFile']=array('tmp_name'=>Input::get('tmp'),'type'=>$type,'name'=>$filename);
        $result=ProductService::importcard($input,$file);
        if($result['errorCode']==0){
            return $this->redirect('v4agiftbag/package/search')->with('global_tips','导入成功');
        }else{
            return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
        }
    }
    /****
     * @return string
     *

     */

    /**视图 卡密导入
     * @param string $cardcode
     * @return
     */
    public function getImport($cardcode='')
    {
        $data['card']['cardCode']=$cardcode;
        return $this->display('package/card-import',$data);
    }

    /**
     * 卡密上架 下架方法
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
            return $this->redirect('v4agiftbag/package/search')->with('global_tips','卡密属性修改成功');
        }
        return $this->back()->with('global_tips','操作失败->'.$result['errorDescription']);
    }

    /**视图 获取卡编码列表**/
    public function getCateCardSelect()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['pageSize'] =5;
        $params['onOrOff'] ='true';
        $params['isActive'] = 'true';
        $params['VirtualUse'] = 2;
        if(Input::get('keyword')){
            $data['keyword']=$params['cardDesc']=Input::get('keyword');
        }
        $result=ProductService::getvirtualcardlist($params);
        if($result['errorCode'] !=null ){
            foreach($result['result'] as &$item){
                $item['can_use'] = $item['cardStock'] + $item['cardUsedStock'] - $item['cardQuota'];
            }
            $data=self::processingInterface($result,$data,$params['pageSize']);
            $html = $this->html('pop-card-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }
    /**实物列表 15/11/5**/
    public function getMaterialSelect()
    {

        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['platform'] = Input::get('platform',"");
        $params['materialUse'] = Input::get('materialUse',"");
        $params['pageSize'] =5;
        $params['onOrOff'] ='true';
        $params['isActive'] = 'true';
        if(Input::get('keyword')){
            $data['keyword']=$params['materialDesc']=Input::get('keyword');
        }
        if(Input::get('platform')){
            $data['platform'] = Input::get('platform');
        }
        $result=ProductService::getmateriallist($params);
        if($result['errorCode'] !=null ){
            foreach($result['result'] as &$item){
                $item['can_use'] = $item['materialStock'] + $item['materialUsedStock'] - $item['materialQuota'];
            }
            $data=self::processingInterface($result,$data,$params['pageSize']);
//            print_r($data['datalist']);
            $html = $this->html('pop-material-list',$data);
            return $this->json(array('html'=>$html));
        }
        self::error_html($result);
    }


    /**
     * 处理接口返回数据
     * @param $result
     * @param $data
     * @param int $pagesize
     * @return
     */
    private static function processingInterface($result,$data,$pagesize=10){
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);

        unset($data['pageIndex']);
        $pager->appends($data);

        $data['pagelinks'] = $pager->links();
        $data['datalist'] = !empty($result['result'])?$result['result']:array();
        return $data;
    }
}