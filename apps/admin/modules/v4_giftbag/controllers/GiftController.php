<?php
namespace modules\v4_giftbag\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Paginator;
use Youxiduo\Helper\Utility;
use Youxiduo\Imall\ProductService;
use Youxiduo\V4\Game\GameService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Response;
use libraries\Helpers;

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
		$search = Input::only('keyword','start_date','end_date','s_params','game_id');
		$page = Input::get('page',1);
		$pagesize = 10;
		$params = array(
			'productType' => 2,
			'pageIndex' => $page,
			'pageSize' => $pagesize,
            //'sortType' => 'Create_Time',
            'signer' => parent::getSessionUserUid()
		);
		isset($search['keyword']) && $search['keyword'] && $params['productName'] = $search['keyword'];
        isset($search['start_date']) && $search['start_date'] && $params['createTimeBegin'] = date('Y-m-d H:i:s',strtotime($search['start_date']));
        isset($search['end_date']) && $search['end_date'] && $params['createTimeEnd'] = date('Y-m-d H:i:s',strtotime($search['end_date']));
        if(($search['game_id']) && $search['game_id']){
            $rel_res = ProductService::getGiftGameRelation(array('gid'=>$search['game_id'],'genre'=>self::GENRE,'isActive'=>'true'));
            if(!$rel_res['errorCode'] && $rel_res['result']){
                $params['productCode'] = $rel_res['result'][0]['gfid'];
            }else{
                $params['productCode'] = -1;
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
            if(in_array(4,$search['s_params']) && in_array(5,$search['s_params'])) unset($params['isAdd']);
        }
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
                isset($row['img']) && $row['img'] = json_decode($row['img'],true);
            }
			$data['totalcount'] = $result['totalCount'];
            $data['datalist'] = $result['result'];
		}
		return $this->display('gift/gift-list',$data);
	}

	/**
	 * 添加礼包
	 */
	public function getAdd()
	{
		$data = array();
		$data['gift'] = array('is_show'=>1);
		return $this->display('gift/gift-add',$data);
	}

	public function postAdd(){
		$input = Input::all();
        $rule = array('title'=>'required','card_code'=>'required','start_time'=>'required','card_stock'=>'required|integer|min:1|checkstock:'.$input['card_code'],
                        'end_time'=>'required','account_ids'=>'required_if:type_set,2|checkuids','limit'=>'required_if:limit_type,3',
                        'coin'=>'required_with:fee','type_set'=>'required','limit_type'=>'required');
        $prompt = array('title.required'=>'名称不能为空','card_code.required'=>'请选择卡密','card_stock.required'=>'发布数目不能为空',
                        'card_stock.integer'=>'发布数目必须为整数','card_stock.min'=>'发布数目最小为1','card_stock.checkstock'=>'礼包卡无效或发布数超过限制',
                        'start_time.required'=>'请选择开始时间','end_time.required'=>'请选择结束时间',
                        'coin.integer'=>'游币必须为整数','account_ids.required_if'=>'请输入用户ID','coin.required_with'=>'付费礼包请填写游币数',
                        'checkuids'=>'用户UID输入有误');
        Validator::extend('checkuids',function($attr,$val){
            $uids = explode(',',$val);
            $res = true;
            foreach($uids as $row){
                if(!is_numeric($row)){
                    return false;
                }
            }
            return $res;
        });
        Validator::extend('checkstock',function($attr,$val,$param){
            $c_code = current($param);
            //获取当前礼包量
            $card_res = ProductService::getvirtualcardlist(array('cardCode'=>$c_code));
            if($card_res['errorCode'] || !$card_res['result']) return false;
            $card_res = current($card_res['result']);
            if($card_res['cardStock'] < $val) return false;
            return true;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }

		$params = array(
            'gameId' => $input['game_id'] ? $input['game_id'] : self::YXD_GID,
			'productName' => $input['title'],
            'productCode' => ProductService::getCode('g-code-','md5OrUniqid'),
			'productGamePrice' => $input['coin'] ? $input['coin'] : 0,
            'productPrice' => 0,
            'categoryId' => 0,
			'productType' => 2,
			'inventedType' => 1,
            'cardCode' => $input['card_code'],
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
            'exclusiveAccount' => $input['type_set'] == 2 ? $input['account_ids'] : false,
            'isNewUser' => $input['type_set'] == 3 ? 'true' : false,
            'limitType' => $input['limit_type'],
            'singleLimit' => $input['single_limit'] ? $input['single_limit'] : 0,
            'timeType' => $input['time_type'] ? $input['time_type'] : false,
            'timeValue' => $input['time_value'] ? $input['time_value'] : false,
            'ruleLimit' => $input['rule_limit'],
            'creator' => parent::getSessionUserName()
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

        //上架设定
        switch($input['shelf_set']){
            case '1': //上架
                $params['isOnshelf'] = 'true';
                $params['extraReq']['onshelfAtBegin'] = 'false';
                $params['startTime']=date('Y-m-d H:i:s',time());
                break;
            case '2': //下架
                $params['isOnshelf'] = 'false';
                $params['extraReq']['onshelfAtBegin'] = 'false';
                break;
            case '3': //自动上架
                $params['isOnshelf'] = 'false';
                $params['extraReq']['onshelfAtBegin'] = 'true';
        }

        //isset($input['is_top']) && $input['top_end_time'] &&        $params['extraReq']['topEndTime'] = date('Y-m-d H:i:s',strtotime($input['top_end_time']));

        $list_pic = $detail_pic = '';
        $game_info = GameService::getOneInfoById($params['gameId'],'ios');
        $game_info && $list_pic = $game_info['ico'];
        $game_info && $detail_pic = $game_info['ico'];
        $params['productImgpath'] = array('listPic'=>$list_pic,'detailPic'=>$detail_pic);
        $params['extraReq'] = json_encode($params['extraReq']);
        //追加礼包
        if($input['append_file']){
            $append_file_res = ProductService::importcard(array('cardCode'=>$input['card_code'],
                'expTimeStr'=>date('Y-m-d H:i:s',strtotime('2030-01-01 00:00:00'))),array('importFile'=>$_FILES['append_file']),'ios');
        }
        $result = ProductService::addProduct($params,true);

        if($result['errorCode']==0){
            return $this->redirect('v4giftbag/gift/search')->with('global_tips','添加礼包成功');
        }else{
            return $this->redirect('v4giftbag/gift/search')->with('global_tips','添加礼包失败');
        }
	}


	public function getEdit($id){
		$data = array();
        //查询商品信息
        $result = ProductService::searchProductList(array('id'=>$id),1);
        if($result['errorCode']) return $this->back('数据错误');
        reset($result['result']);
        $giftbag_info = current($result['result']);
        $giftbag_info['img'] = json_decode($giftbag_info['img'],true);
        isset($giftbag_info['accountList']) && $giftbag_info['accountList'] = implode(',',$giftbag_info['accountList']);
        isset($giftbag_info['extraReq']) && $giftbag_info['extraReq'] = json_decode($giftbag_info['extraReq'],true);
        isset($giftbag_info['extraReq']) && $giftbag_info['extraReq']['addType'] === 'addByPer' &&
        $giftbag_info['extraReq']['conditionNum'] *= 100;

        $data['giftbag'] = $giftbag_info;
        //查询卡密信息
        $card_res = ProductService::getvirtualcardlist(array('cardCode'=>$giftbag_info['cardCode']));
        !$card_res['errorCode'] && $data['card_info'] = $card_res['result'][0];
        //查询游戏关系
        $rel_res = ProductService::getGiftGameRelation(array('gfid'=>$giftbag_info['gfid'],'genre'=>self::GENRE,'isActive'=>'true'));

        if(!$rel_res['errorCode'] && $rel_res['result']){
            $game_info = GameService::getOneInfoById($rel_res['result'][0]['gid'],'ios');
            $game_info['ico'] = Utility::getImageUrl($game_info['ico']);
            $data['game_info'] = $game_info;
        }

		return $this->display('gift/gift-edit',$data);
	}

    /**
     * 修改提交
     */
    public function postEdit(){
        $input = Input::all();
        $rule = array('product_code'=>'required','title'=>'required','card_code'=>'required|checkstock','start_time'=>'required',
                        'end_time'=>'required','account_ids'=>'required_with:is_exclusive|checkuids',
                        'list_pic'=>'required_without:game_id','coin.required_with:fee');
        $prompt = array('product_code.required'=>'数据错误','title.required'=>'名称不能为空','card_code.required'=>'请选择卡密',
                        'start_time.required'=>'请选择开始时间','end_time.required'=>'请选择结束时间','coin.required_with'=>'付费礼包请填写游币数',
                        'coin.integer'=>'游币必须为整数','required_with'=>'请输入用户ID','checkuids'=>'用户UID输入有误',
                        'card_code.checkstock'=>'礼包卡信息错误');
        Validator::extend('checkuids',function($attr,$val){
            $uids = explode(',',$val);
            $res = true;
            foreach($uids as $row){
                if(!is_numeric($row)){
                    $res = false;
                    return;
                }
            }
            return $res;
        });
        Validator::extend('checkstock',function($attr,$val,$param){
            //获取当前礼包量
            $card_res = ProductService::getvirtualcardlist(array('cardCode'=>$val));
            if($card_res['errorCode'] || !$card_res['result']) return false;
            return true;
        });
        $valid = Validator::make($input,$rule,$prompt);
        if($valid->fails()){
            return $this->back()->withInput()->with('global_tips',$valid->messages()->first());
        }

		$params = array(
            'gameId' => $input['game_id'] ? $input['game_id'] : self::YXD_GID,
			'productName' => $input['title'],
            'productCode' => $input['product_code'],
			'productGamePrice' => $input['coin'],
			'productPrice' => 0,
            'categoryId' => 0,
			'productType' => 2,
			'inventedType' => 2,
            'cardCode' => $input['card_code'],
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
			'productStock' => 0,
            'isExclusive' => $input['type_set'] == 2 ? 'true' : 'false',
            'exclusiveAccount' => $input['type_set'] == 2 ? $input['account_ids'] : false,
            'isNewUser' => $input['type_set'] == 3 ? 'true' : 'false',
            'limitType' => $input['limit_type'],
            'singleLimit' => $input['single_limit'] ? $input['single_limit'] : 0,
            'timeType' => $input['time_type'] ? $input['time_type'] : false,
            'timeValue' => $input['time_value'] ? $input['time_value'] : false,
            'ruleLimit' => $input['rule_limit'],
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

        //上架设定
        switch($input['shelf_set']){
            case '1': //上架
                $params['isOnshelf'] = 'true';
                $params['extraReq']['onshelfAtBegin'] = 'false';
                $params['startTime']=date('Y-m-d H:i:s',time());
                break;
            case '2': //下架
                $params['isOnshelf'] = 'false';
                $params['extraReq']['onshelfAtBegin'] = 'false';
                break;
            case '3': //自动上架
                $params['isOnshelf'] = 'false';
                $params['extraReq']['onshelfAtBegin'] = 'true';
        }

        //isset($input['is_top']) && $input['top_end_time'] && $params['extraReq']['topEndTime'] = date('Y-m-d H:i:s',strtotime($input['top_end_time']));

        $list_pic = $input['old_list_pic'];
        $detail_pic = $input['old_detail_pic'];
        if($input['old_game_id'] != $params['gameId']){
            $game_info = GameService::getOneInfoById($params['gameId'],'ios');
            $game_info && $list_pic = $game_info['ico'];
            $game_info && $detail_pic = $list_pic;
        }
        $params['productImgpath'] = array('listPic'=>$list_pic,'detailPic'=>$detail_pic);
        $params['extraReq'] = json_encode($params['extraReq']);
        //追加礼包
        if($input['append_file']){
            $append_file_res = ProductService::importcard(array('cardCode'=>$input['card_code'],
                'expTimeStr'=>date('Y-m-d H:i:s',strtotime('2030-01-01 00:00:00'))),array('importFile'=>$_FILES['append_file']),'ios');
        }
        $result = ProductService::editProduct($params);

        if(!$input['old_game_id'] && $params['gameId']){
            //添加关系
            $rel_data = array('createTime' => $params['startTime'], 'gid' => $params['gameId'], 'gfid' => $params['productCode'], 'genre' => self::GENRE);
            $relat_res = Utility::preParamsOrCurlProcess($rel_data, array('gid', 'gfid', 'genre', 'createTime'), Config::get(ProductService::RLT_URL_CONF) . 'add_gift_game', 'POST');
            //}elseif($input['old_game_id'] && $params['gameId']) {
        }else{
            //更新
            $rel_data = array('gfid'=>$params['productCode'],'gid'=>$params['gameId'],'genre'=>self::GENRE);
            $relat_res = Utility::preParamsOrCurlProcess($rel_data,array('gid','gfid','genre'),Config::get(ProductService::RLT_URL_CONF).'update_gid_by_gfid','POST');
        }


        if($result['errorCode']==0  && $relat_res['errorCode']==0){
            return $this->redirect('v4giftbag/gift/search')->with('global_tips','更新礼包成功');
        }else{
            return $this->redirect('v4giftbag/gift/search')->with('global_tips','更新礼包失败');
        }
    }

    public function getAjaxShelf(){
        $p_code = Input::get('p_code',false);
        $state = Input::get('state',false);
        if(!$p_code || $state === false) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        if(!$state){
            //下架
            $result = ProductService::offsaleProduct(array('productCode'=>$p_code));
        }else{
            //上架
            $result = ProductService::onsaleProduct(array('productCode'=>$p_code));
        }
        if(!$result['errorCode']){
            return $this->json(array('state'=>1,'msg'=>'更新成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'更新失败，请重试'));
        }
    }

    public function getAjaxDel($p_code=''){
        if(!$p_code) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        //获取关系
        $rel_res = ProductService::getGiftGameRelation(array('gfid'=>$p_code,'genre'=>self::GENRE,'isActive'=>'true'));
        if(!$rel_res['errorCode'] && $rel_res['result']){
            ProductService::delGiftGameRelation($rel_res['result'][0]['gid'],$p_code,1);
        }
        $result = ProductService::DeleteProduct(array('productCode'=>$p_code));
        if(!$result['errorCode']){
            return $this->json(array('state'=>1,'msg'=>'删除成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'删除失败，请重试'));
        }
    }

    public function getAjaxHot(){
        $p_code = Input::get('p_code',false);
        $hot = Input::get('hot',false);
        if(!$p_code) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        $result = ProductService::is_top(array('productCode'=>$p_code,'isTop'=>$hot));
        if(!$result['errorCode']){
            return $this->json(array('state'=>1,'msg'=>'设置成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'设置失败，请重试'));
        }
    }

    public function getAjaxSign(){
        $p_code = Input::get('p_code',false);
        $sign = Input::get('sign',false);
        if(!$p_code) return $this->json(array('state'=>0,'msg'=>'数据错误'));
        $uid = parent::getSessionUserUid();
        if(!$uid) return $this->json(array('state'=>0,'msg'=>'当前用户错误'));
        $result = ProductService::setSign($uid,$p_code,$sign);
        if(!$result['errorCode']){
            return $this->json(array('state'=>1,'msg'=>'设置成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'设置失败，请重试'));
        }
    }

    public function getAjaxSend(){
        $input=Input::only('p_code',"uids");
        if(!$input['uids'] || !$input['p_code']) return $this->json(array('state'=>0,'msg'=>'数据错误!'));
        $result=ProductService::grant_product($input);
        if(!$result['errorCode'] && $result['result']){
            return $this->json(array('state'=>1,'msg'=>'发放成功'));
        }else{
            return $this->json(array('state'=>0,'msg'=>'发放失败'));
        }
    }

    public function getAjaxAuth(){
        $input=Input::only('p_code',"uids");
        if(!$input['uids'] || !$input['p_code']) return $this->json(array('state'=>0,'msg'=>'数据错误!'));
        $result=ProductService::editProduct(array('productCode'=>$input['p_code'],'isExclusive'=>'true','exclusiveAccount'=>$input['uids']));
        if(!$result['errorCode'] && $result['result']){
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
        /**
        $fp = fopen($file, 'r');
        $line = 0;
        while (!feof($fp)) {
            $row = trim(fgets($fp));
            if (strlen($row) < 1) continue;
            $line++;
        }**/
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
        $append_file_res = ProductService::importcard(array('type'=>$input['type'],'requestFrom'=>$input['dataid'],'needQuota'=>'true','cardCode'=>$input['card_code'],'expTimeStr'=>date('Y-m-d H:i:s',strtotime('2030-01-01 00:00:00'))),array('importFile'=>array('tmp_name'=>Input::get('tmp'),'type'=>$type,'name'=>$filename)),'ios');
        $params['productCode']=$input['datacode'];
        $params['productStock']=$append_file_res['result'];
        $result=ProductService::update_product_reform($params,array('productCode','productStock'));
        if($result['errorCode']==0){
            return json_encode(array("state"=>1,'msg'=>'追加成功'));
        }else{
            return json_encode(array('state'=>0,'msg'=>'追加失败'));
        }
    }

}
