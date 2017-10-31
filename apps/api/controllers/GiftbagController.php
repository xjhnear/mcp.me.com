<?php
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Activity\GiftbagService;
use Yxd\Services\UserService;
use Yxd\Services\Cms\GameService;
use Yxd\Models\Passport;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\DES;
use Yxd\Modules\Core\BaseService;

class GiftbagController extends BaseController
{	
    const API_URL_CONF = 'app.mall_api_url';
    const MALL_API_ACCOUNT = 'app.account_api_url';
    const MALL_API_VIRTUALCARD = 'app.virtualcard_api_url';
    const REDIS_V4GIFTBAG = 'v4::giftbag';
/**
	 * 礼包首页
	 */
	public function home()
	{
		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$uid = Input::get('uid',0);
		$out = array();
		

		$gid = Input::get('gid');
		// 新版本
		// 获取礼包列表
		$params = array('productType'=>2,'pageIndex'=>$page,'pageSize'=>$pagesize,'gids'=>$gid,'sortType'=>'Create_Time','isTop'=>'FALSE','platform'=>'ios','currencyType'=>0,'isOnshelf'=>'TRUE','active'=>'TRUE');
		$params_ = array('productType','pageIndex','pageSize','gids','sortType','isTop','platform','currencyType','isOnshelf','active');
		$giftbags = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_product');
		// 		print_r($params);exit;
		// 获取我的礼包
		if($uid){
		    $params = array('productType'=>2,'accountId'=>$uid,'platform'=>'ios');
		    $params_ = array('productType','accountId','platform');
		    $result_gift = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_API_ACCOUNT).'accountproduct/query');
		    foreach($result_gift['result'] as $index=>$row){
		        $mygift[$row['gfid']] = !empty($row['card'])?DES::decrypt($row['card'],11111111):'';
		    }
		}else{
		    $mygift = null;
		}
		
		$hots['totalCount'] = 0;
		$hots['result'] = array();
		if($page==1 && $gid==0){
		    // 获取热门礼包
		    $params = array('productType'=>2,'pageIndex'=>$page,'pageSize'=>$pagesize,'gids'=>$gid,'sortType'=>'Create_Time','isTop'=>'TRUE','platform'=>'ios','currencyType'=>0,'isOnshelf'=>'TRUE','active'=>'TRUE');
		    $params_ = array('productType','pageIndex','pageSize','gids','sortType','isTop','platform','currencyType','isOnshelf','active');
		    $hots = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_product');
		
		}
		
		$total = (int)$hots['totalCount'] + (int)$giftbags['totalCount'];
		if ((int)$hots['totalCount'] == 0) $hots['result'] = array();
		if ((int)$giftbags['totalCount'] == 0) $giftbags['result'] = array();
		$giftbags = array_merge($hots['result'],$giftbags['result']);
		foreach($giftbags as $index=>$row){
		    $gift = array();
		    $gift['gfid'] = $row['gfid'];
		    $game = GameService::getGameInfo($row['gid']);
		    $gift['url'] = self::joinImgUrl($game['ico']);
		    $gift['gname'] = trim($game['shortgname']) ? trim($game['shortgname']) : $game['gname'];
		    $gift['title'] = $row['title'];
		    $gift['date'] = date("Y-m-d",strtotime($row['addTime']));
		    $gift['adddate'] = date("Y-m-d",strtotime($row['addTime']));
		    $gift['starttime'] = $row['startTimeStr'];
		    $gift['endtime'] = $row['endTimeStr'];
		    $gift['ishot'] = (int)$row['isHot'];
		    $gift['istop'] = (int)$row['isTop'];
		    $gift['cardcount'] = $row['totalCount'];
		    $gift['lastcount'] = $row['restCount'];
		    $ishas = false;
		    $number = '';
		    if( $row['singleLimit']==1 && isset($mygift) && is_array($mygift)){
		        $mygift_ids = array_keys($mygift);
		        $ishas = in_array($row['gfid'],$mygift_ids);
		        if($ishas){
		            $number = $mygift[$row['gfid']];
		        }
		    }
		    $gift['ishas'] = (int)$ishas;
		    $gift['numbers'] = $number;
		    $out[] = $gift;
		}
		
		return $this->success(array('result'=>$out,'totalCount'=>$total));
		
	}
	
	/**
	 * 搜索礼包
	 */
	public function search()
	{
	    $page = Input::get('pageIndex',1);
	    $pagesize = Input::get('pageSize',20);
	    $uid = Input::get('uid',0);
	    $keyword = Input::get('keyword');
	    if(empty($keyword)){
	        return $this->home();
	    }
	    $out = array();
	    
	    // 新版本
	    // 获取礼包列表
	    $params = array('productType'=>2,'pageIndex'=>$page,'pageSize'=>$pagesize,'gname'=>$keyword,'sortType'=>'Create_Time','platform'=>'ios','currencyType'=>0,'isOnshelf'=>'TRUE','active'=>'TRUE');
	    $params_ = array('productType','pageIndex','pageSize','gname','sortType','platform','currencyType','isOnshelf','active');
	    $giftbags = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_product');
	    // 获取我的礼包
	    if($uid){
	        $params = array('productType'=>2,'accountId'=>$uid,'platform'=>'ios');
	        $params_ = array('productType','accountId','platform');
	        $result_gift = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_API_ACCOUNT).'accountproduct/query');
	        foreach($result_gift['result'] as $index=>$row){
	            $mygift[$row['gfid']] = !empty($row['card'])?DES::decrypt($row['card'],11111111):'';
	        }
	    }else{
	        $mygift = null;
	    }
	    foreach($giftbags['result'] as $index=>$row){
	        $gift = array();
	        $gift['gfid'] = $row['gfid'];
	        $game = GameService::getGameInfo($row['gid']);
	        $gift['url'] = self::joinImgUrl($game['ico']);
	        $gift['gname'] = trim($game['shortgname']) ? trim($game['shortgname']) : $game['gname'];
	        $gift['title'] = $row['title'];
	        $gift['date'] = date("Y-m-d",strtotime($row['addTime']));
	        $gift['adddate'] = date("Y-m-d",strtotime($row['addTime']));
	        $gift['starttime'] = $row['startTimeStr'];
	        $gift['endtime'] = $row['endTimeStr'];
	        $gift['ishot'] = (int)$row['isHot'];
	        $gift['istop'] = (int)$row['isTop'];
	        $gift['cardcount'] = $row['totalCount'];
	        $gift['lastcount'] = $row['restCount'];
	        $ishas = false;
	        $number = '';
	        if( $row['singleLimit']==1 && isset($mygift) && is_array($mygift)){
	            $mygift_ids = array_keys($mygift);
	            $ishas = in_array($row['gfid'],$mygift_ids);
	            if($ishas){
	                $number = $mygift[$row['gfid']];
	            }
	        }
	        $gift['ishas'] = (int)$ishas;
	        $gift['numbers'] = $number;
	        $out[] = $gift;
	    }
	    
	    return $this->success(array('result'=>$out,'totalCount'=>$giftbags['totalCount']));

	}
	
	/**
	 * 礼包详情
	 */
	public function detail()
	{
		$gift_id = Input::get('gfid');
		$uid = Input::get('uid',0);
		$password = Input::get('password');
		$idfa = Input::get('idfa');
		


		if(!$gift_id){
		    return $this->fail(11211,'礼包不存在');
		}
		$check_version = true;
		//if($uid == 5542314 || $uid == 100240 || $uid == 100001){
		$version = Input::get('version');
		$vers = array('3.4.0','3.5.0','3.5.1','3.6.0');
		if(!in_array($version,$vers)){
		    $check_version = false;
		}
		
		if(in_array($version,$vers) && $uid>0 && $password && $this->checkUserStatus($uid, $password)==false){
		    $check_version = false;
		}
		//}
		$check_version = false;
		// 		    if (!BaseService::redis()->sismember(self::REDIS_V4GIFTBAG,$gift_id)) {
		$params = array('cardCode'=>$gift_id,'platform'=>'ios');
		$params_ = array('cardCode','platform');
		$new = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_API_VIRTUALCARD).'virtualcard/list');
		if ($new['totalCount']>0 || strpos($gift_id,'ios')!==0) {
		    BaseService::redis()->sadd(self::REDIS_V4GIFTBAG,$gift_id);
		}
		// 		    }
		
		// 		    if (BaseService::redis()->sismember(self::REDIS_V4GIFTBAG,$gift_id) || $new['result']) {
		if ($new['totalCount']>0 || strpos($gift_id,'ios')!==0) {
		    // 新版本
		    // 获取礼包详情
		    $params = array('productType'=>2,'productCode'=>$gift_id,'uid'=>$uid,'platform'=>'ios','currencyType'=>0);
		    $params_ = array('productType','productCode','uid','platform','currencyType');
		    $gift = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_product');
		    if($gift['result']) {
		        $gift = $gift['result'][0];
		    } else {
		        $gift = array();
		    }
		
		    if($gift){
		        $out = array();
		        $out['gfid']   = $gift_id;
		        $out['title']  = trim($gift['title']);
		        $out['gid']    = $gift['gid'] ? $gift['gid']  : 0;
		        $game = GameService::getGameInfo($gift['gid']);
		        $out['gname']  = trim($game['shortgname']) ? trim($game['shortgname']) : $game['gname'];
		        $out['url'] = self::joinImgUrl($game['ico']);
		        $out['starttime'] = $gift['startTimeStr'];
		        $out['endtime'] = $gift['endTimeStr'];
		        // 获取我的礼包
		        $params = array('productType'=>2,'accountId'=>$uid,'productCode'=>$gift_id,'platform'=>'ios');
		        $params_ = array('productType','accountId','productCode','platform');
		        $result_mygift = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_API_ACCOUNT).'accountproduct/query');
		        $my_cardno = '';
		        if ($result_mygift['totalCount'] > 0) {
		            $my_cardno = $result_mygift['result'][0]['card'] ? $result_mygift['result'][0]['card'] : '';
		            $my_cardno = !empty($my_cardno)?DES::decrypt($my_cardno,11111111):'';
		        }
		        $gift['ishas'] = $my_cardno ? 1 : 0;
		        $gift['cardno'] = $my_cardno;
		
		        $out['ishas']  = $gift['ishas'];
		        if($this->checkUserStatus($uid, $password)==true){
		            $out['number'] = $gift['cardno'] ? : '';
		        }else{
		            $out['number'] = '';
		        }
		
		        if ($gift['giftStatus']<>3 || $gift['giftStatus']<>4) {
		            $btnshow = 1;
		        } else {
		            $btnshow = 0;
		        }
		        $out['btnshow'] = $check_version ? $btnshow : 0;
		
		        if($gift['singleLimit']<>1) {
		            $out['btnshow'] = 1;
		            $out['ishas'] = 0;
		            $out['cardno'] = '';
		        }
		
		        //授权礼包
		        if ($gift['isExclusive'] == 1 ) {
		            $out['btnshow'] = 0;
		        }
		        if (isset($gift['accountList'])) {
		            $out['btnshow'] = in_array($uid,$gift['accountList'])? 1 : 0;
		        }
		
		        
		        $out['btnshow'] = 0; //3.0礼包停止领取
		        
		        $out['cardcount']	=	$gift['totalCount'];
		        $out['lastcount']	=	$gift['restCount'];
		        $out['needTourCurrency'] = $gift['price'];
		        $out['remainTourCurrency'] = $uid ? UserService::getUserRealTimeCredit($uid,'score') : 0;
		        $game = GameService::getGameInfo($gift['gid']);
		        $out['company'] = $game['company'];
		        $append = '<html><header><meta charset="utf-8"></header><style>body { line-height: 16pt;font-family:"helveticaneue-light";font-size: 13px;color:#777;} h3{margin:10px 0;font-weight: bold;}.video{width:290px; height:200px; padding:2px;text-align:center; background:#e9eaeb; margin:10px auto 2px; box-shadow:0px 0px 1px #666;}.article{ margin:3px auto;padding:0px 1px;font-size:14px;color:#666;}.article p{margin:10px 0;line-height:20px;}.article p strong{color:#333;font-size:14px;}.article img {max-width:100%;border:0;} img {max-width:100%;border:0;}</style><body>';
		        if($check_version==false){//小于3.4.0的版本则提示升级
		            $append = '<p style="white-space: normal;padding:10px 0px 2px 0px; text-align:center;"><span style="color: rgb(0, 0, 255);font-size:18px;">3.0礼包领取功能已关闭<br>请下载游戏多4.0版本领取</span></p>';
		            $append .= '<p style="padding:0px 0px 6px 0px; text-align:center;"><span style="color:#0000FF;font-size:24px;">☆</span><a href="https://itunes.apple.com/cn/app/you-xi-duo/id1140768186?l=zh&ls=1&mt=8" target="_blank" style="text-decoration:underline;"><span style="color:#FF6600;font-size:24px;">下载安装</span></a><span style="color:#0000FF;font-size:24px;">☆</span></p>';
		        }
		        $out['body'] = $append . $gift['productInstruction'];
		        $out['body'] .= '</body></html>';
		         
		        return $this->success(array('result'=>$out));
		    }
		    return $this->fail(11211,'礼包不存在');
		
		} else {
		    $gift_id = str_replace('ios', '', $gift_id);
		    $gift = GiftbagService::getDetailTest($gift_id,$uid);
		    if($gift){
		        $out = array();
		        $out['gfid']   = 'ios'.$gift_id;
		        $out['title']  = trim($gift['title']);
		        $out['gid']    = $gift['game_id'] ? $gift['game_id']  : 0;
		        $out['gname']  = trim($gift['game']['shortgname']) ? trim($gift['game']['shortgname']) : $gift['game']['gname'];
		        $out['url']    = self::joinImgUrl($gift['game']['ico']);
		        //$out['url'] = self::joinImgUrl($this->replaceFreeIcon($gift['is_charge'],$gift['game']['ico'],$gift['listpic']));
		        $out['starttime'] = date('Y-m-d H:i:s',$gift['starttime']);
		        $out['endtime'] = date('Y-m-d H:i:s',$gift['endtime']);
		        $out['ishas']  = $gift['ishas'];
		        if($this->checkUserStatus($uid, $password)==true){
		            $out['number'] = $gift['cardno'] ? : '';
		        }else{
		            $out['number'] = '';
		        }
		        $btnshow = 1;
		        if($uid){
		            if($gift['is_appoint']){//授权礼包
		                $btnshow = GiftbagService::isGiftbagAppointUser($gift_id, $uid);
		                $check_version = true;
		            }else{
		                $btnshow = GiftbagService::isGetGiftbagByAppleIdentify($gift_id, $uid) ? 0 : 1;
		                //非收费版则不显示领取按钮
		                if($gift['is_charge'] && !in_array($version,array('3.6.0'))){
		                    $btnshow = 0;
		                }
		            }
		        }else{
		            if($gift['is_charge'] && !in_array($version,array('3.6.0'))){
		                $btnshow = 0;
		            }
		        }
		        if($gift['ishas']) $btnshow = 1;
		        $out['btnshow'] = $check_version ? $btnshow : 0;
		        //无限领取逻辑
		        if($gift['is_not_limit']==1) $out['btnshow'] = 1;
		        //互斥礼包逻辑
		        if($gift['mutex_giftbag_id']>0 && $uid>0){
		            $out['btnshow'] = GiftbagService::isGetGiftbag($gift['mutex_giftbag_id'],$uid) ? 0 : 1;
		        }
		
		        if($gift['limit_register_time']>0 && $uid){
		            $user = UserService::getUserInfo($uid);
		            if($user){
		                if($user['dateline']<$gift['limit_register_time']){
		                    $out['btnshow'] = 0;
		                }
		            }
		        }
		        //限制领取次数
		        if($gift['limit_count']>0 && $uid>0){
		            $out['btnshow'] = GiftbagService::isGetGiftbagLimitByUID($gift['limit_count'],$gift_id,$uid) ? 0 : 1;
		        }
		
		        $out['btnshow'] = 0; //3.0礼包停止领取
		        
		        $out['cardcount']	=	$gift['total_num'];
		        $out['lastcount']	=	$gift['last_num'];
		        $out['needTourCurrency'] = isset($gift['condition']['score']) ? $gift['condition']['score'] : 0;
		        $out['remainTourCurrency'] = $uid ? UserService::getUserRealTimeCredit($uid,'score') : 0;
		        $out['company'] = $gift['game']['company'];
		        $append = '';
		        //收费礼包且当前版本未非收费版则提示升级
		        if($gift['is_charge'] && !in_array($version,array('3.6.0'))){
		            $append = '<p style="white-space: normal;padding:10px 0px 2px 0px;"><span style="color: rgb(0, 0, 255);font-size:18px;">免费版暂不支持本礼包领取，如有需要请下载付费版本领取本礼包！</span></p>';
		            $append .= '<p style="padding:0px 0px 6px 0px; text-align:center;"><span style="color:#0000FF;font-size:24px;">☆</span><a href="https://itunes.apple.com/us/app/you-xi-duo-shou-ji-you-xi/id953018137?l=zh&ls=1&mt=8" target="_blank" style="text-decoration:underline;"><span style="color:#FF6600;font-size:24px;">下载安装</span></a><span style="color:#0000FF;font-size:24px;">☆</span></p>';
		        }elseif($check_version==false){//小于3.4.0的版本则提示升级
		            $append = '<p style="white-space: normal;padding:10px 0px 2px 0px; text-align:center;"><span style="color: rgb(0, 0, 255);font-size:18px;">3.0礼包领取功能已关闭<br>请下载游戏多4.0版本领取</span></p>';
		            $append .= '<p style="padding:0px 0px 6px 0px; text-align:center;"><span style="color:#0000FF;font-size:24px;">☆</span><a href="https://itunes.apple.com/cn/app/you-xi-duo/id1140768186?l=zh&ls=1&mt=8" target="_blank" style="text-decoration:underline;"><span style="color:#FF6600;font-size:24px;">下载安装</span></a><span style="color:#0000FF;font-size:24px;">☆</span></p>';
		        }
		        $out['body'] = $append . $gift['content'];
		         
		        return $this->success(array('result'=>$out));
		    }
		    return $this->fail(11211,'礼包不存在');
		}
		
		    
	}
	
	/**
	 * 我的礼包
	 */
	public function myGift()
	{
		$uid = Input::get('uid',0);
		$password = Input::get('password');
		$idfa = Input::get('idfa');
		$version = Input::get('version');
		$vers = array('3.4.0','3.5.0','3.5.1');
		$check_version = true;
		

		$page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		
		// 新版本
		// 获取我的礼包
		$params = array('productType'=>2,'pageIndex'=>$page,'pageSize'=>$pagesize,'accountId'=>$uid,'platform'=>'ios');
		$params_ = array('productType','pageIndex','pageSize','accountId','platform');
		$result = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_API_ACCOUNT).'accountproduct/query');
		// 		$result = GiftbagService::getMyGift($uid,$page,$pagesize);
		$out = array();
		$games = $gifts = $gift_ids = $game_ids = array();
		foreach($result['result'] as $row){
		    $gift_ids[] = $row['gfid'];
		}
		$gid = implode(',', $gift_ids);
		// 		$_gifts = GiftbagService::getListByIds($gift_ids);
		// 获取礼包
		$params = array('productType'=>2,'pageIndex'=>$page,'pageSize'=>$pagesize,'gids'=>$gid,'sortType'=>'Create_Time','platform'=>'ios','currencyType'=>0);
		$params_ = array('productType','pageIndex','pageSize','gids','sortType','platform','currencyType');
		$_gifts = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::API_URL_CONF).'product/query_product');
		
		foreach($_gifts['result'] as $row){
		    $gifts[$row['gfid']] = $row;
		}
		
		foreach($result['result'] as $key=>$row){
		    $gift = array();
		    $gift['gfid'] = $row['gfid'];
		    $game = GameService::getGameInfo($row['gid']);
		    $gift['url'] = self::joinImgUrl($game['ico']);
		    $gift['gname'] =trim($game['shortgname']) ? trim($game['shortgname']) : $game['gname'];
		    $gift['title'] = $row['title'];
		    $gift['date'] = $row['getTime'];
		    $gift['adddate'] = $row['getTime'];
		    $my_cardno = !empty($row['card'])?DES::decrypt($row['card'],11111111):'';
		    $gift['number'] =  $check_version ? $my_cardno : '';
		    $out[] = $gift;
		}
		
		if((int)$result['totalCount']==0){
		    return $this->success(array('result'=>array(),'totalCount'=>0));
		}else{
		    return $this->success(array('result'=>$out,'totalCount'=>$result['totalCount']));
		}
		
	}
	
    /**
	 * 领取礼包
	 */
	public function getGift()
	{
		$uid = Input::get('uid');
		$gift_id = Input::get('gfid');
		$password = Input::get('password');
		$idfa = Input::get('idfa');		
		

		if(!$uid || !$gift_id){
		    return $this->fail(11200,'参数错误');
		}
		
		//if($uid == 5542314 || $uid == 100240 || $uid == 100001){
		$version = Input::get('version');
		$vers = array('3.4.0','3.5.0','3.5.1','3.6.0');
		
		// 		    if (!BaseService::redis()->sismember(self::REDIS_V4GIFTBAG,$gift_id)) {
		$params = array('cardCode'=>$gift_id,'platform'=>'ios');
		$params_ = array('cardCode','platform');
		$new = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_API_VIRTUALCARD).'virtualcard/list');
		if ($new['totalCount']>0 || strpos($gift_id,'ios')!==0) {
		    BaseService::redis()->sadd(self::REDIS_V4GIFTBAG,$gift_id);
		}
		// 		    }
		
		// 		    if (BaseService::redis()->sismember(self::REDIS_V4GIFTBAG,$gift_id) || $new['result']) {
		if ($new['totalCount']>0 || strpos($gift_id,'ios')!==0) {
		    // 新版本
		    if(!in_array($version,$vers)){
		        return $this->fail(11200,'您的应用版本过低,请升级新版本');
		    }
		    // 		    if(in_array($version,$vers) && $uid && $password && $this->checkUserStatus($uid, $password)==false){
		    // 		        return $this->fail(11211,'安全验证失败,请重新登录');
		    // 		    }
		
		    $card = GiftbagService::doMyGiftNew($gift_id, $uid);
		    if ($card['errorCode'] == 0) {
		        return $this->success(array('result'=>$card['result']));
		    } else {
		        return $this->fail(11200,$card['result']);
		    }
		
		} else {
		    $gift_id = str_replace('ios', '', $gift_id);
		    $gift = GiftbagService::getDetail($gift_id,0);
		    if($gift && $gift['is_appoint']==0){//授权礼包默认通过
		        if(!in_array($version,$vers)){
		            return $this->fail(11200,'您的应用版本过低,请升级新版本');
		        }
		         
		        if(in_array($version,$vers) && $uid && $password && $this->checkUserStatus($uid, $password)==false){
		            return $this->fail(11200,'安全验证失败,请重新登录');
		        }
		         
		        if(($uid == 100240 || $uid==100013) && $gift['is_charge'] && !in_array($version,array('3.6.0'))){
		            return $this->fail(11200,'您的应用版本过低,请升级新版本');
		        }
		    }
		    //}
		
		    $card = GiftbagService::doMyGift($gift_id, $uid);
		    if($card==-4){
		        return $this->fail(11200,'该礼包仅限新用户领取');
		    }elseif($card==-2){
		        return $this->fail(11200,'该礼包为活动专属礼包，只有参加活动的用户才能领取哦，如有问题请在“意见反馈”中及时和客服联系，谢谢！');
		    }elseif($card==-1){
		        return $this->fail(11200,'礼包不存在');
		    }elseif($card===0){
		        return $this->fail(11200,'礼包已经被领完');
		    }elseif($card===1){
		        return $this->fail(11200,'礼包领取失败');
		    }elseif($card===2){
		        return $this->fail(11200,'游币不足');
		    }elseif($card===-3){
		        return $this->fail(11200,'您的账号使用的设备今天已经领取过礼包');
		    }elseif($card===-5){
		        return $this->fail(11200,'您的账号无法再领取更多该礼包');
		    }else{
		        return $this->success(array('result'=>$card));
		    }
		}
		
		    
	}
	
	/**
	 * 我的预定
	 */
	public function myReserveGift()
	{
		$uid = Input::get('uid');
	    $page = Input::get('pageIndex',1);
		$pagesize = Input::get('pageSize',20);
		$result = GiftbagService::myReserve($uid,$page,$pagesize);		
		$out = array();
		foreach($result['result'] as $row){
			$reserve = array();			
			$reserve['gid'] = $row['game_id'];
			$reserve['url'] = self::joinImgUrl($row['game']['ico']);
			$reserve['gname'] = $row['game']['shortgname'];
			$reserve['bookdate'] = date('Y-m-d H:i:s',$row['addtime']);			
			$reserve['gfid'] = $row['gift_id'] ? : '';
			$out[] = $reserve;
		}
		
	    return $this->success(array('result'=>$out,'totalCount'=>$result['total']));
	}
	
	protected function checkUserStatus($uid,$password)
	{		
		if(!$password) return false;		
		$status = Passport::verifyLocalLogin($uid, $password,'uid');
		if($status===-1 || $status===null){
			return false;
		}
		return true;
	}
	
	protected function replaceFreeIcon($free_icon,$gift)
	{
		$uid = Input::get('uid',0);
		$version = Input::get('version');
		if(($uid == 100240 || $uid==100013) && $gift){
			$icon = $free_icon;
			if($gift['is_appoint'] && $gift['appoint_icon']){
				$icon = $gift['appoint_icon'];
			}
			if(!in_array($version,array('3.6.0')) && $gift['is_charge'] && $gift['charge_icon']){
				$icon = $gift['charge_icon'];
			}
			return $icon;
		}
		return $free_icon;		
	}
	
	/**
	 * 我的预定-删除
	 */
	public function removeMyReserveGift()
	{
		$game_id = Input::get('gid');
		$uid = Input::get('uid');
		GiftbagService::removeMyReserve($game_id, $uid);
		return $this->success(array('result'=>null));
	}
		
	/**
	 * 预定礼包
	 */
	public function reserveGift()
	{
		$uid = Input::get('uid',0);
		$game_id = Input::get('gid');
		$result = GiftbagService::doMyReserve($game_id, $uid);
		if($result>0){
			return $this->success(array('result'=>array()));
		}elseif($result===-1){
			return $this->fail('11211','该游戏礼包已经预定');
		}else{
			return $this->fail('11211','礼包预定失败');
		}
	}	
}
