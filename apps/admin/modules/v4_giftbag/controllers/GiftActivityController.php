<?php
namespace modules\v4_giftbag\controllers;

use Illuminate\Support\Facades\Validator;
use Youxiduo\Mall\ProductService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Response;

class GiftActivityController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'v4_giftbag';
	}
	
	/**
	 * 
	 */
	public function getSearch()
	{
		$data = array();
		$search = Input::only('keyword','game_id');
		$page = Input::get('page',1);
		$pagesize = 10;
		$genre = 1;
		$params = array(
			'productType' => 2,
			'pageIndex' => $page,
			'pageSize' => $pagesize
		);
		if($search['keyword']) $params['productCode'] = $search['keyword'];
		$result = ProductService::searchProductActivityList($params);
		$pager = Paginator::make(array(),$result['totalCount'],$pagesize);
		$pager->appends($search);
		$data = array(
			'search' => $search,
			'pagelinks' => $pager->links(),
			'totalcount' => 0,
			'datalist' => null
		);

		if(!$result['errorCode']){
			$data['totalcount'] = $result['totalCount'];
            $data['datalist'] = $result['result'];
		}
		return $this->display('giftactivity-list',$data);
	}

	/**
	 * 添加礼包
	 */
	public function getAdd()
	{
		$data = array();
		return $this->display('giftactivity-add',$data);
	}

    /**
     * 执行添加礼包操作
     */
    public function postAdd(){
        $input = Input::all();
        $rule = array('start_time'=>'required','end_time'=>'required','product_code'=>'required','discount_price'=>'required_with:is_discount',
            'discount_game_price'=>'required_with:is_discount','total_number'=>'required_with:is_product_limit','limit_mode'=>'required_with:is_product_limit',
            'update_rest_time'=>'required_if:limit_mode,1');
        $prompt = array('start_time.required'=>'请选择开始时间','end_time.required'=>'请选择结束时间','product_code'=>'请选择礼包','discount_price.required_with'=>'请填写优惠价格（RMB）',
            'discount_game_price.required_with'=>'请填写优惠价格（游币）','total_number.required_with'=>'请填写限量数目','limit_mode.required_with'=>'请选择限量模式',
            'update_rest_time.required_if'=>'请选择更新时间');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }
        $data = array(
            'startTime' => $input['start_time'],
            'endTime' => $input['end_time'],
            'productCode' => $input['product_code'],
            'description' => $input['description'],
            'sort' => $input['sort'],
            'onOrOff' => isset($input['on_or_off']) ? 'true' : false,
            'offProduct' => isset($input['off_product']) ? 'true' : false
        );
        isset($input['is_discount']) && $data['isDiscount'] = 'true';
        isset($input['discount_price']) && $data['discountPrice'] = $input['discount_price']*100;
        isset($input['discount_game_price']) && $data['discountGamePrice'] = $input['discount_game_price'];
        isset($input['is_product_limit']) && $data['isProductLimit'] = 'true';
        isset($input['is_product_limit']) && $data['limitMode'] = $input['limit_mode'];
        isset($input['is_product_limit']) && $data['totalNumber'] = $input['total_number'];
        isset($input['is_product_limit']) && $input['limit_mode'] == 1 && $data['updateRestTime'] = $input['update_rest_time'];

        $result = ProductService::addProductActivity($data);
        if(isset($result['errorCode']) && $result['errorCode'] == '0'){
            return $this->redirect('v4giftbag/giftactivity/search')->with('global_tips','添加礼包活动成功');
        }else{
            return $this->redirect('v4giftbag/giftactivity/search')->with('global_tips','添加礼包活动失败');
        }
    }

    /**
     * 编辑礼包
     * @param $activity_id
     * @return
     */
	public function getEdit($activity_id)
	{
        //查询活动信息
        $result = ProductService::searchProductActivityList(array('activityId'=>$activity_id));
        if(isset($result['errorCode']) && !$result['errorCode']){
            $result = current($result['result']);
            $p_res=ProductService::searchProductList(array('productCode'=> $result['productCode']));
            if(isset($p_res['errorCode']) && !$p_res['errorCode']){
                $p_res = current($p_res['result']);
                $result['productName'] = $p_res['title'];
            }
            $result['discountPrice'] = $result['discountPrice']/100;
            return $this->display('giftactivity-edit',array('data'=>$result));
        }else{
            return $this->back('数据错误');
        }
	}

    public function postEdit(){
        $input = Input::all();
        $rule = array('activity_id'=>'required','start_time'=>'required','end_time'=>'required','discount_price'=>'required_with:is_discount',
            'discount_game_price'=>'required_with:is_discount','total_number'=>'required_with:is_product_limit','limit_mode'=>'required_with:is_product_limit',
            'update_rest_time'=>'required_if:limit_mode,1');
        $prompt = array('activity_id.required'=>'数据错误','start_time.required'=>'请选择开始时间','end_time.required'=>'请选择结束时间','discount_price.required_with'=>'请填写优惠价格（RMB）',
            'discount_game_price.required_with'=>'请填写优惠价格（游币）','total_number.required_with'=>'请填写限量数目','limit_mode.required_with'=>'请选择限量模式',
            'update_rest_time.required_if'=>'请选择更新时间');
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }
        $data = array(
            'activityId' => $input['activity_id'],
            'startTime' => $input['start_time'],
            'endTime' => $input['end_time'],
            'description' => $input['description'],
            'sort' => $input['sort'],
            'onOrOff' => isset($input['on_or_off']) ? 'true' : 'false',
            'offProduct' => isset($input['off_product']) ? 'true' : 'false'
        );
        $data['isDiscount'] = isset($input['is_discount']) ? 'true' : 'false';
        isset($input['discount_price']) && $data['discountPrice'] = $input['discount_price']*100;
        isset($input['discount_game_price']) && $data['discountGamePrice'] = $input['discount_game_price'];
        $data['isProductLimit'] = isset($input['is_product_limit']) ? 'true' : 'false';
        isset($input['is_product_limit']) && $data['limitMode'] = $input['limit_mode'];
        isset($input['is_product_limit']) && $data['totalNumber'] = $input['total_number'];
        isset($input['is_product_limit']) && $input['limit_mode'] == 1 && $data['updateRestTime'] = $input['update_rest_time'];
        $result  = ProductService::addProductActivity($data,'product/modify_productactivity');
        if(isset($result['errorCode']) && !$result['errorCode']){
            return $this->redirect('v4giftbag/giftactivity/search','修改礼包活动成功');
        }else{
            return $this->back('修改礼包活动失败');
        }
    }

    public function getOpenOrClose($activityId,$op=0){
        //查询活动信息
        $result_activity = ProductService::searchProductActivityList(array('activityId'=>$activityId));
        if($result_activity['errorCode'] != 0 || empty($result_activity['result'])) {
            $this->back('商品活动信息未找到');
        }
        $op = ($result_activity['result'][0]['onOrOff'] == '1') ? false : true;
       // print_r($activityId);exit;
        $result = ProductService::OpenOrCloseProductactivity(array('activityId'=>$activityId),$op);
        if($result['errorCode'] == '0'){
            return $this->back('状态修改成功');
        }elseif($result['errorCode'] == '500'){
            $tips = $result['errorDescription'];
            return $this->back($tips);
        }else{
            return $this->back('状态修改成功');
        }
    }


}