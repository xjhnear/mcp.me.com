<?php
namespace modules\v4_giftbag\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Cache\CacheService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Helper\Utility;
use Youxiduo\Imall\ProductService;
use Youxiduo\V4\Game\GameService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Response;
use libraries\Helpers;
use Youxiduo\MyService\CheckService;
use modules\web_forum\controllers\TopicController;
use Youxiduo\V4\User\UserService;
use modules\v4_adv\models\Core;

class GiftController extends BackendController
{
    const YXD_GID = '12776';    //默认游戏多id,记得改为查询
    const GENRE = 1;

	public function _initialize()
	{
		$this->current_module = 'v4_giftbag';
	}

	public function getSearch()
	{
		$search = Input::only('keyword','start_date','end_date','s_params','game_id','currencyType','platform','appname');
		$page = Input::get('page',1);
		$pagesize = 10;
		$params = array(
			'productType' => 2,
			'pageIndex' => $page,
			'pageSize' => $pagesize,
            'sortType' => 'Create_Time',
            'signer' => parent::getSessionUserUid()
		);
		isset($search['keyword']) && $search['keyword'] && $params['productName'] = $search['keyword'];
		if (isset($search['start_date']) && $search['start_date']) {
		    $params['createTimeBegin'] = $search['start_date'];
		} else {
		    $params['createTimeBegin'] = $search['start_date'] = date("Y-m-d H:i:s",strtotime("-14 day"));
		}
		if (isset($search['end_date']) && $search['end_date']) {
		    $params['createTimeEnd'] = $search['end_date'];
		} else {
		    $params['createTimeEnd'] = $search['end_date'] = date("Y-m-d H:i:s",time());
		}
        isset($search['currencyType']) && $params['currencyType'] = $search['currencyType'];
        if (isset($search['appname']) && $search['appname']) {
            $params['appname'] = $search['appname'];
        } else {
            $params['appname'] = 'all';
        }
        $params['platform'] = 'ios';
        if(($search['game_id']) && $search['game_id']){
            $rel_res = ProductService::getGiftGameRelation(array('gids'=>$search['game_id'],'genre'=>self::GENRE,'isActive'=>'true'));

            if(!$rel_res['errorCode'] && $rel_res['result']){
                $params['productCode'] = $rel_res['result'][0]['gfid'];
            }else{
                $params['productCode'] = "";
            }
        }
        $search['s_params'] = $search['s_params'];
        if($search['s_params']){
            in_array(1,$search['s_params']) && $params['productStock'] = 0;
            if(in_array(2,$search['s_params'])){
                $params['sign'] = 'true';
                $params['signer'] = parent::getSessionUserUid();
            }
            in_array(3,$search['s_params']) && $params['isExclusive'] = 'true';
            in_array(4,$search['s_params']) && $params['isAdd'] = 'true';
            in_array(5,$search['s_params']) && $params['isAdd'] = 'false';
            in_array(6,$search['s_params']) && $params['isTop'] = 'true';
            if(in_array(4,$search['s_params']) && in_array(5,$search['s_params'])) unset($params['isAdd']);
        }
        $params['productType']=2;
        $params['gids'] = Input::get('game_id');
		$result = ProductService::searchProductList($params,self::GENRE,'gift');
		$pager = Paginator::make(array(),$result['totalCount'],$pagesize);
		$pager->appends($search);
		$data = array(
			'search' => $search,
			'pagelinks' => $pager->links(),
			'totalcount' => 0,
			'datalist' => null
		);

		if(!$result['errorCode'] && $result['result']){
            foreach($result['result'] as &$row){
                $row['extraReq'] = json_decode($row['extraReq'],true);
                isset($row['extraReq']['freeContent']) && $row['extraReq']['freeContent'] = json_decode($row['extraReq']['freeContent'],true);
                isset($row['img']) && $row['img'] = json_decode($row['img'],true);
            }
			$data['totalcount'] = $result['totalCount'];
            $data['datalist'] = $result['result'];
		}
		$data['appnames'] = array('all' => '全端','yxdjqb' => 'IOS','youxiduojiu3' => 'IOS业内版');
		$data['currencyTypeList'] = array(''=>'全部礼包','0'=>'游币礼包','1'=>'钻石礼包');
		return $this->display('gift/gift-list',$data);
	}

	/**
	 * 添加礼包
	 */
	public function getAdd()
	{
		$data = array();
		$data['gift'] = array('is_show'=>1);
		$data['appnames'] = array('all' => '全端','yxdjqb' => 'IOS','youxiduojiu3' => 'IOS业内版');
		return $this->display('gift/gift-add',$data);
	}

	public function postAdd(){
		$input = Input::all();
//        print_r($input);
        $valid = Validator::make($input,array(),array());
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }

		$params = array(
            //'gameId' => $input['game_id'] ? $input['game_id'] : 0,
            'currencyType' => $input['currencyType'],
            'gid' => $input['game_id'] ? $input['game_id'] : 0,
            'gname' => $input['game_name'] ? $input['game_name'] : 0,
			'productName' => $input['title'],
            'productCode' => ProductService::getCode('g-code-','md5OrUniqid'),
			'productGamePrice' => $input['coin'] ? $input['coin'] : 0,
            'productPrice' => 0,
            'categoryId' => 0,
			'productType' => 2,
            'linkType' => isset($input['linkType']) ? $input['linkType'] : 0,
            'linkId' => isset($input['linkId']) ? $input['linkId'] : 0,
			'inventedType' => 1,
            'cardCode' =>  isset($input['card_code']) ? $input['card_code'] : '',
            'productSummary' => isset($input['summary']) ? preg_replace('/<[^>]+>/i','',$input['summary']) : false,
			'productInstruction' => isset($input['intro']) ? $input['intro'] : false,
            'productDesc' => isset($input['des']) ? preg_replace('/<[^>]+>/i','',$input['des']) : false,
			'isBelongUs' => isset($input['belong_us']) ? 'true' : 'false',
			'productSort' => $input['sort'],
			'isNotice' => 'false',
			'isTop' => isset($input['is_top']) ? 'true' : 'false',
            'isHot' => isset($input['isHot']) ? 'true' : 'false',
            'startTime' => date('Y-m-d H:i:s',strtotime($input['start_time'])),
            'endTime' => date('Y-m-d H:i:s',strtotime($input['end_time'])),
			'productStock' => $input['card_stock'],
            'isExclusive' => $input['type_set'] == 2 ? 'true' : 'false',
		    'isOffTogether' => isset($input['off_together']) ? 'true' : 'false',
            //'exclusiveAccount' => $input['type_set'] == 2 ? $input['account_ids'] : false,
            'isNewUser' => $input['type_set'] == 3 ? 'true' : false,
            'inventedType' => $input['type_set'] == 4 ? 1 : 0,
            'limitType' => intval($input['limit_type']),
            'singleLimit' => $input['single_limit'] ? $input['single_limit'] : 0,
            'timeType' => $input['time_type'] ? $input['time_type'] : false,
            'timeValue' => $input['time_value'] ? $input['time_value'] : false,
            'ruleLimit' => $input['rule_limit'],
            'creator' => parent::getSessionUserName(),
            'exclusiveAccount' => 0,
		    'platform' => 'ios',
		    'appname' => $input['appname'],
		);

        //追加
        if(isset($input['is_append'])){
            switch($input['append']){
                case '1': //时间
                    $params['extraReq'] = array('addType'=>'addByTime','addPeriod'=>$input['add_period'],'addPeriodType'=>$input['add_period_type'],'addNum'=>$input['add_num_1'],'addNumMax'=>$input['add_num_max_1']);
                    break;
                case '2': //剩余数
                    $params['extraReq'] = array('addType'=>'addByNum','conditionNum'=>$input['condition_num_1'],'addNum'=>$input['add_num_2'],'addNumMax'=>$input['add_num_max_2']);
                    break;
                case '3': //剩余率
                    $params['extraReq'] = array('addType'=>'addByPer','conditionNum'=>$input['condition_num_2']/100,'addNum'=>$input['add_num_3'],'addNumMax'=>$input['add_num_max_3']);
                    break;
            }
        }else{
            $params['extraReq']['addType'] = 0;
        }
        $params['extraReq']['testCode'] = $input['testCode'];

        //上架设定
        switch($input['shelf_set']){
            case '1': //上架
                $params['isOnshelf'] = 'true';
                $params['extraReq']['onshelfAtBegin'] = 'false';
//                $params['startTime']=date('Y-m-d H:i:s',time());
                break;
            case '2': //下架
                $params['isOnshelf'] = 'false';
                $params['extraReq']['onshelfAtBegin'] = 'false';
                break;
            case '3': //自动上架
                $params['isOnshelf'] = 'false';
                $params['extraReq']['onshelfAtBegin'] = 'true';
                break;
            case '4': //自动下架
                $params['isOnshelf'] = 'true';
                $params['isOffTogether'] = 'true';
        }
        
        //自动推送
        if(isset($input['isPush'])){
            $freeContent['pushStatus'] = '1';
        } else {
            $freeContent['pushStatus'] = '2';
        }
        
        if(isset($input['downurl'])){
            $freeContent['downurl'] = $input['downurl'];
        }
        if(isset($input['downurl_linkType'])){
            $freeContent['downurl_linkType'] = $input['downurl_linkType'];
        }
        
        $params['extraReq']['freeContent'] = json_encode($freeContent);

        //isset($input['is_top']) && $input['top_end_time'] &&        $params['extraReq']['topEndTime'] = date('Y-m-d H:i:s',strtotime($input['top_end_time']));
        $params['extraReq'] = json_encode($params['extraReq']);
        if(!empty($input['icon'])){
            $input['icon']=strstr($input['icon'], '/u/');
            $params['productImgpath'] = array('listPic'=>$input['icon'],'detailPic'=>$input['icon']);
        }
        //print_r($params['productImgpath']);
        //追加礼包
        if(isset($input['append_file'])){
                ProductService::importcard(array('cardCode'=>$input['card_code'],
                'expTimeStr'=>date('Y-m-d H:i:s',strtotime('2030-01-01 00:00:00'))),array('importFile'=>$_FILES['append_file']),'ios');
        }

        $result = ProductService::addProduct($params,true);

        if($result['errorCode']==0){
            //Core::delcache(array('type'=>2,'productCode'=>$params['productCode']));
//             $data =array();
//             if(!empty($input['game_id'])){
//                 $data = CacheService::cache_add_type_count_gif($input['game_id'],'game_gift');
//             }
//             if(!isset($data['errorCode'])||$data['errorCode']!=0){
//                 return $this->redirect('v4giftbag/gift/search')->with('global_tips','添加礼包成功,缓存失败');
//             }
            return $this->redirect('v4giftbag/gift/search?appname='.$input['appname'])->with('global_tips','添加礼包成功');
        }else{
            return $this->redirect('v4giftbag/gift/search?appname='.$input['appname'])->with('global_tips','添加礼包失败');
        }
	}


	public function getEdit($id=0,$platform='ios'){
		$data = array();
        //查询商品信息
        $result = ProductService::searchProductList(array('id'=>$id,'productType'=>2,'platform'=>$platform),1);
        if($result['errorCode']) return $this->back('数据错误');
        reset($result['result']);
        $giftbag_info = current($result['result']);
        $giftbag_info['img'] = json_decode($giftbag_info['img'],true);
        isset($giftbag_info['accountList']) && $giftbag_info['accountList'] = implode(',',$giftbag_info['accountList']);
        isset($giftbag_info['extraReq']) && $giftbag_info['extraReq'] = json_decode($giftbag_info['extraReq'],true);
        if (isset($giftbag_info['extraReq']['freeContent']) && $giftbag_info['extraReq']['freeContent']) {
            $data['freeContent'] = $giftbag_info['extraReq']['freeContent'];
            $giftbag_info['extraReq']['freeContent'] = json_decode($giftbag_info['extraReq']['freeContent'],true);
        }
        isset($giftbag_info['extraReq']) && $giftbag_info['extraReq']['addType'] === 'addByPer' &&
        $giftbag_info['extraReq']['conditionNum'] *= 100;

        $data['giftbag'] = $giftbag_info;

        //查询卡密信息
        $card_res = ProductService::getvirtualcardlist(array('cardCode'=>$giftbag_info['cardCode']));
        if (!$card_res['errorCode'] && isset($card_res['result'][0])) {
            $data['card_info'] = $card_res['result'][0];
        }
        $data['appnames'] = array('all' => '全端','yxdjqb' => 'IOS','youxiduojiu3' => 'IOS业内版');
        return $this->display('gift/gift-edit',$data);
	}

    /**
     * 修改提交
     */
    public function postEdit(){

        $input = Input::all();
        $valid = Validator::make($input,array(),array());
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }

		$params = array(
            'currencyType' => $input['currencyType'],
            //'gameId' => $input['game_id'] ? $input['game_id'] : 0,
            'gid' => $input['game_id'] ? $input['game_id'] : 0,
            'gname' => $input['game_name'] ? $input['game_name'] : 0,
			'productName' => $input['title'],

            'linkType' => isset($input['linkType']) ? $input['linkType'] : 0,
            'linkId' => isset($input['linkId']) ? $input['linkId'] : '',
		    'productId' => $input['product_id'],
            'productCode' => $input['product_code'],
			'productGamePrice' => $input['coin'],
			'productPrice' => 0,
            'categoryId' => 0,
			'productType' => 2,
			'inventedType' => 2,
            'cardCode' =>  isset($input['card_code']) ? $input['card_code'] : '',
            'productSummary' => isset($input['summary']) ? preg_replace('/<[^>]+>/i','',$input['summary']) : false,
			'productInstruction' => isset($input['intro']) ? $input['intro'] : false,
            'productDesc' => isset($input['des']) ? preg_replace('/<[^>]+>/i','',$input['des']) : false,
			'isBelongUs' => isset($input['belong_us']) ? 'true' : 'false',
			'productSort' => $input['sort'],
			'isNotice' => 'false',
			'isTop' => isset($input['is_top']) ? 'true' : 'false',
            'isHot' => isset($input['isHot']) ? 'true' : 'false',
            'startTime' => date('Y-m-d H:i:s',strtotime($input['start_time'])),
            'endTime' => date('Y-m-d H:i:s',strtotime($input['end_time'])),
		    'isOffTogether' => isset($input['off_together']) ? 'true' : 'false',
            'isExclusive' => $input['type_set'] == 2 ? 'true' : 'false',
            //'exclusiveAccount' => $input['type_set'] == 2 ? $input['account_ids'] : false,
            'isNewUser' => $input['type_set'] == 3 ? 'true' : 'false',
            'inventedType' => $input['type_set'] == 4 ? 1 : 0,
            'limitType' => $input['limit_type'],
            'singleLimit' => $input['single_limit'] ? $input['single_limit'] : 0,
            'timeType' => $input['time_type'] ? $input['time_type'] : false,
            'timeValue' => $input['time_value'] ? $input['time_value'] : false,
            'ruleLimit' => $input['rule_limit'],
//            'exclusiveAccount' => 0,
		    'platform' => 'ios',
		    'appname' => $input['appname'],
            'modifier' => parent::getSessionUserName()
		);




        //追加
        if(isset($input['is_append'])){
            switch($input['append']){
                case '1': //时间
                    $params['extraReq'] = array('addType'=>'addByTime','addPeriod'=>$input['add_period'],'addPeriodType'=>$input['add_period_type'],'addNum'=>$input['add_num_1'],'addNumMax'=>$input['add_num_max_1']);
                    break;
                case '2': //剩余数
                    $params['extraReq'] = array('addType'=>'addByNum','conditionNum'=>$input['condition_num_1'],'addNum'=>$input['add_num_2'],'addNumMax'=>$input['add_num_max_2']);
                    break;
                case '3': //剩余率
                    $params['extraReq'] = array('addType'=>'addByPer','conditionNum'=>$input['condition_num_2']/100,'addNum'=>$input['add_num_3'],'addNumMax'=>$input['add_num_max_3']);
                    break;
            }
        }else{
            $params['extraReq']['addType'] = 0;
        }
        //测试码
        $params['extraReq']['testCode'] = $input['testCode'];
        $params['productStock']=0;
        //上架设定
        switch($input['shelf_set']){
            case '1': //上架
                $params['isOnshelf'] = 'true';
                $params['extraReq']['onshelfAtBegin'] = 'false';
//                $params['startTime']=date('Y-m-d H:i:s',time());

                break;
            case '2': //下架
                $params['isOnshelf'] = 'false';
                $params['extraReq']['onshelfAtBegin'] = 'false';
//                 $params['productStock']=$input['card_stock'];

                break;
            case '3': //自动上架
                $params['isOnshelf'] = 'false';
                $params['extraReq']['onshelfAtBegin'] = 'true';
                break;
                
            case '4': //自动下架
                $params['isOnshelf'] = 'true';
//                 $params['isOffTogether'] = 'true';
        }

        //isset($input['is_top']) && $input['top_end_time'] && $params['extraReq']['topEndTime'] = date('Y-m-d H:i:s',strtotime($input['top_end_time']));

        if(!empty($input['icon'])){
            $input['icon']=strstr($input['icon'], '/u/');
            $params['productImgpath'] = array('listPic'=>$input['icon'],'detailPic'=>$input['icon']);
        }
        $freeContent = array();
        if(isset($input['freeContent'])){
            $freeContent = json_decode($input['freeContent'],true);
        }
        if(isset($input['downurl'])){
            $freeContent['downurl'] = $input['downurl'];
        }
        if(isset($input['downurl_linkType'])){
            $freeContent['downurl_linkType'] = $input['downurl_linkType'];
        }
        $params['extraReq']['freeContent'] = json_encode($freeContent);

        $params['extraReq'] = json_encode($params['extraReq']);
        //追加礼包
        if(isset($input['append_file'])){
            ProductService::importcard(array('cardCode'=>$input['card_code'],
                'expTimeStr'=>date('Y-m-d H:i:s',strtotime('2030-01-01 00:00:00'))),array('importFile'=>$_FILES['append_file']),'ios');
        }
        $result = ProductService::editProduct($params);

        if($result['errorCode']==0  ){
            //Core::delcache(array('type'=>2,'productCode'=>$params['productCode']));
            return $this->redirect('v4giftbag/gift/search?appname='.$input['appname'])->with('global_tips','更新礼包成功');
        }else{
            return $this->redirect('v4giftbag/gift/search?appname='.$input['appname'])->with('global_tips','更新礼包失败');
        }
    }

    public function getAjaxShelf(){
        $p_code = Input::get('p_code',false);
        $state = Input::get('state',false);
        $platform = Input::get('platform','ios');
        if(!$p_code || $state === false) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        if(!$state){
            //下架
            $result = ProductService::offsaleProduct(array('productCode'=>$p_code,'platform'=>$platform,'modifier' => parent::getSessionUserName()));
        }else{
            //上架
            $result = ProductService::onsaleProduct(array('productCode'=>$p_code,'platform'=>$platform,'modifier' => parent::getSessionUserName()));
        }
        if(!$result['errorCode']){
            //Core::delcache(array('type'=>2,'productCode'=>$p_code));
            return $this->json(array('state'=>1,'msg'=>'更新成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'更新失败，请重试'));
        }
    }

    public function getAjaxDel($p_code='',$platform='ios'){
        if(!$p_code) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        //获取关系
        $rel_res = ProductService::getGiftGameRelation(array('gfid'=>$p_code,'genre'=>self::GENRE,'isActive'=>'true'));
        if(!$rel_res['errorCode'] && $rel_res['result']){
            ProductService::delGiftGameRelation($rel_res['result'][0]['gid'],$p_code,1);
        }
        $result = ProductService::DeleteProduct(array('productCode'=>$p_code,'platform'=>$platform,'modifier' => parent::getSessionUserName()));
        Core::delcache(array('type'=>-1,'productCode'=>$p_code));
        if(!$result['errorCode']){
            //Core::delcache(array('type'=>2,'productCode'=>$p_code));
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    }

    public function getAjaxHot(){
        $p_code = Input::get('p_code',false);
        $hot = Input::get('hot',false);
        $platform = Input::get('platform','ios');
        if(!$p_code) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        $result = ProductService::is_top(array('productCode'=>$p_code,'platform'=>$platform,'isTop'=>$hot,'modifier' => parent::getSessionUserName()));
        if(!$result['errorCode']){
            //Core::delcache(array('type'=>2,'productCode'=>$p_code));
            return $this->json(array('state'=>1,'msg'=>'设置成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'设置失败，请重试'));
        }
    }

    public function getAjaxSign(){
        $p_code = Input::get('p_code',false);
        $sign = Input::get('sign',false);
        $platform = Input::get('platform','ios');
        if(!$p_code) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        $uid = parent::getSessionUserUid();
        if(!$uid) return $this->json(array('state'=>0,'msg'=>'当前用户错误'));
        $result = ProductService::setSign($uid,$p_code,$sign,$platform);
        if(!$result['errorCode']){
            //Core::delcache(array('type'=>2,'productCode'=>$p_code));
            return $this->json(array('state'=>1,'msg'=>'设置成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'设置失败，请重试'));
        }
    }

    public function getAjaxSend(){
        $input=Input::only('p_code',"uids","p_name","gameId","platform");
        if(!$input['uids'] || !$input['p_code']) return $this->json(array('state'=>0,'msg'=>'数据错误!'));
        $input['productCode'] = $input['p_code'];
        $input['number'] = 1;       
        $result=ProductService::grant_product($input);        
        if(!$result['errorCode'] && $result['result']){
            $uid_arr = explode(",", $input['uids']);
            foreach ($uid_arr as $uid) {
                $input['type'] = '2010';
                $input['linkType'] = '3';
                $input['link'] = $input['p_code'];
                $input['uid'] =  $uid;
                $input['content'] = $input['p_name'].','.$input['p_code'];
                TopicController::system_send($input);
            }
            return $this->json(array('state'=>1,'msg'=>'发放成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'发放失败'));
        }
    }

    public function postAjaxAuth(){
        $input=Input::only('p_code',"uids","p_name","gameId","uids_now","platform");
        if(!$input['uids'] || !$input['p_code']) return $this->json(array('state'=>0,'msg'=>'数据错误!'));
        $uid_arr = explode(",", $input['uids']);
        $uid_now_arr = explode(",", $input['uids_now']);
        $uid_both_arr = array_intersect($uid_arr,$uid_now_arr);
        $uid_arr = array_diff($uid_arr,$uid_both_arr);
        $input['uids'] = implode(",", $uid_arr);
        $result=ProductService::batchExclusive(array('productCode'=>$input['p_code'],'platform'=>$input['platform'],'isExclusive'=>'true','exclusiveAccount'=>$input['uids']));
        if(!$result['errorCode'] && $result['result']){
            foreach ($uid_arr as $uid) {
                $User_info = UserService::getUserInfoByUid($uid);
                $input['type'] = '2010';
                $input['linkType'] = '2';
                $input['link'] = $input['p_code'];
                $input['uid'] =  $uid;
                $input['content'] = $User_info['nickname'].','.$input['p_name'];
                TopicController::system_send($input);
            }
            return $this->json(array('state'=>1,'msg'=>'授权成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'授权失败'));
        }
    }
    private function createFolder($path)
    {
        if (!file_exists($path))
        {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }

    public function postAjaxCheckFile(){
        if(!Input::hasFile('append_file'))
            return json_encode(array('state'=>0,'msg'=>'文件不存在'));
        $file = Input::file('append_file');

        $ext = $file->getClientOriginalExtension();
        $filename = $file->getClientOriginalName();
        if($ext != 'txt' && $ext != 'csv')
            return json_encode(array('state'=>0,'msg'=>'文件格式错误'));
        $dir = '/userdirs/filecount/';
        $path = storage_path() . $dir;
        $this->createFolder($path);
        $new_filename = date('YmdHis') . str_random(4);
        $file_path =$file->move($path,$new_filename . '.' . $ext);
        if(empty($file_path)){
            echo json_encode(array('state'=>0,'msg'=>'上传失败!'));
            exit;
        }

        $str = file_get_contents($file_path);//获得内容
        if($ext == 'txt'){
            $arr=array_filter(explode("\r\n",trim($str)));
        }else{
            //mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $arr=array_filter(explode(",",trim($str)));
        }
        return json_encode(array('state'=>1,'msg'=>'读取成功','line'=>count($arr),'file'=>array('tmp'=>$path.$new_filename.'.'.$ext,'filename'=>$filename)));
    }



    public function postAjaxUploadAppend(){ //append_file
        if(!Input::get('dataid'))  return json_encode(array('state'=>0,'msg'=>'礼包编号错误'));
        if(!Input::get('card_code'))  return json_encode(array('state'=>0,'msg'=>'礼包错误'));
        if(!Input::get('tmp'))  return json_encode(array('state'=>0,'msg'=>'卡密文件不存在'));
        $input = Input::all();
        $filename=Input::get('filename');
        $type=explode("." , $filename);
        $type=end($type);
        $input['type']='';
        if($type == 'txt')
            $input['type']=$type;
        //追加礼包
        $append_file_res = ProductService::importcard(array('type'=>$input['type'],'requestFrom'=>$input['dataid'],'needQuota'=>'false','cardCode'=>$input['card_code'],'expTimeStr'=>date('Y-m-d H:i:s',strtotime('2030-01-01 00:00:00'))),array('importFile'=>array('tmp_name'=>Input::get('tmp'),'type'=>$type,'name'=>$filename)),'ios');
        $params['productCode']=$input['datacode'];
        $params['productStock']=$append_file_res['result'];
        $params['modifier']=parent::getSessionUserName();
        $result=ProductService::update_product_reform($params,array('productCode','productStock','modifier'));
        if($result['errorCode']==0){
            //$data = Core::delcache(array('type'=>2,'productCode'=>$params['productCode']));
//             if(!isset($data['errorCode'])||$data['errorCode']!=0){
//                 return json_encode(array("state"=>1,'msg'=>'追加成功,缓存失败'));
//             }
            return json_encode(array("state"=>1,'msg'=>'追加成功'));
        }else{
            return json_encode(array('state'=>0,'msg'=>'追加失败'));
        }
    }


    public function getReadbylist()
    {
        $data=array();
        $input=Input::all();
        $input['page']=Input::get('page',1);
        switch($input['needforType']){
            case 'users':
                $data=CheckService::NeedUserDataBylayer($input);
                break;
        }
        if(!empty($input['isPage'])){
            return $data['html_th'];
        }
        //$this->current_module='my_public';
        return $this->display('layers',$data);
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

    /**礼包列表 16/5/25**/
    public function getGiftSelect()
    {
        $data = $params = array();
        $params['pageIndex'] = Input::get('page');
        $params['platform'] = Input::get('platform',"");
        $params['pageSize'] =5;
        $params['productType'] ='2';
        $params['sortType'] = 'Create_Time';
        if(Input::get('keyword')){
            $data['keyword']=$params['productName']=Input::get('keyword');
        }
        if(Input::get('platform')){
            $data['platform'] = Input::get('platform');
        }
        $result=ProductService::searchProductList($params,self::GENRE,'gift');

        if($result['errorCode'] !=null ){
//            foreach($result['result'] as &$item){
//                $item['can_use'] = $item['materialStock'] + $item['materialUsedStock'] - $item['materialQuota'];
//            }
//            print_r($result);
            $data=self::processingInterface($result,$data,$params['pageSize']);
//            print_r($data['datalist']);
            $html = $this->html('pop-gift-list',$data);
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
