<?php
namespace modules\v4user\controllers;

use modules\statistics\models\AndroidMoney;
use Youxiduo\User\Model\AccountSession;

use Youxiduo\User\Model\UserMobile;
use Yxd\Services\CreditService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Yxd\Modules\Core\BackendController;
use Yxd\Services\UserService;
use modules\v4user\models\UserModel;
use Yxd\Utility\ImageHelper;
use Yxd\Services\PassportService;
use Yxd\Modules\Message\NoticeService;
use Youxiduo\System\AuthService;
use Youxiduo\User\Model\Account;
use PHPImageWorkshop\ImageWorkshop;
use Youxiduo\User\Model\MobileSmsHistory;
use Youxiduo\User\Model\AccountAdmin;
use Youxiduo\V4\User\Model\LoginLimit;
use Youxiduo\Android\Model\Checkinfo;
use Youxiduo\V4\User\Model\MobileBlackList;
use Youxiduo\Helper\Utility;
use Youxiduo\Bbs\TopicService;
use Youxiduo\Base\AllService;
use Youxiduo\Helper\MyHelp;
use Youxiduo\User\Model\AccountReceiptAddress;

use modules\v4user\models\MoneyService;

class UsersController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'v4user';
	}
	
	public function getIndex()
	{
		$data = array();
		$page = Input::get('page',1);
		$search = Input::only('startdate','enddate','keytype','keyword');		
		$pagesize = 20;
	    $sort = Input::get('sort','dateline');
	    $search['sort'] = $sort;
		if(in_array($sort,array('dateline','score'))){
		    $order = array($sort=>'desc');
		}else{
			$order = array('dateline'=>'desc');
		}
		$total = UserModel::searchCount($search);
		$totalcount = $total;
		//$totalcount = $total*19;		
		//$page = $page > ceil($total/20) ? ceil($page/19) : $page;
		 
		$result = UserModel::searchList($search,$page,$pagesize,$order);
		$uids = array();
		$all_users = array();
		foreach($result['users'] as $row){
			$uids[] = $row['uid'];
			$all_users[$row['uid']] = $row['nickname'];
		}
		$data['all_users'] = json_encode($all_users);
		$data['datalist'] = $result['users'];
		$data['usergroups'] = $result['groups'];
		$data['bans'] = UserModel::getBanList();

		//安卓游币钻石
//		$moneys = MoneyService::listYouMoney($uids);
//		$diamond = MoneyService::listYouDiamond($uids);

		//ios游币钻石
		$moneys_ios = MoneyService::listYouMoneyios($uids);
		$diamond_ios = MoneyService::listYouDiamondios($uids);

		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalcount;
		$data['allow_edit'] = AuthService::verifyNodeAuth('v4user/users/edit');
		$data['allow_ban'] = AuthService::verifyNodeAuth('v4user/users/ban');
		$data['allow_pwd'] = AuthService::verifyNodeAuth('v4user/users/pwd');
		$data['allow_clear'] = AuthService::verifyNodeAuth('v4user/users/clear-post');
		$data['allow_shield_avatar'] = AuthService::verifyNodeAuth('v4user/users/shield-avatar');
		$data['allow_shield_nickname'] = AuthService::verifyNodeAuth('v4user/users/shield-nickname');
		$data['allow_ios_money'] = AuthService::verifyNodeAuth('v4user/users/ios-money');
//		$data['allow_android_money'] = AuthService::verifyNodeAuth('v4user/users/android-money');
//		$data['android_money'] = $moneys;
//		$data['android_diamond'] = $diamond;
		$data['ios_money'] = $moneys_ios;
		$data['ios_diamond'] = $diamond_ios;
		return $this->display('users-list',$data);
	}
	
	public function getCreate()
	{
		$data = array();
		$data['grouplist'] = UserModel::getUserGroupList();
		return $this->display('users-create',$data);
	}
	
	public function postCreate()
	{
		$time = (int)microtime(true);
		$params = array(
			'client_id'=>'youxiduo',
			'redirect_uri'=>'localhost',
			'timestamp'=>$time,
			'client_secret'=>'90909090'
		);
		
		$signature = md5(http_build_query($params));
		
		$client_id = 'youxiduo';
		$redirect_uri = 'localhost';
		$timestamp = $time;
		$account_type = 'youxiduo'; 
		
		$user['email'] = Input::get('email'); 
		$user['mobile'] = Input::get('mobile');
		$user['password'] = Input::get('password');
		$user['nickname'] = Input::get('nickname','');
		
		if(Account::isExistsByField($user['mobile'],Account::IDENTIFY_FIELD_MOBILE)){
			return $this->back('手机号已经占用');
		}
		
		$group_ids = Input::get('group_id',5);
		
	    if(Input::hasFile('avatar')){			
	        $config = array(
	    	    'savePath'=>'/userdirs/avatar/',
	    	    'driverConfig'=>array('autoSize'=>array(320,120,100,80,60,50))
	    	);
	    	$uploader = new ImageHelper($config);
	    	$avatar = $uploader->upload('avatar');
	    	if($avatar !== false){
	    		$user['avatar'] = $avatar['filepath'] . '/' . $avatar['filename'];
	    	}
		}
		$third_token = array();
		
		$result = PassportService::createUser($client_id, $redirect_uri, $timestamp, $signature, $user,$third_token);
		
		if($result['status']===200){
			UserModel::updateUserInfo($result['data']['uid'], null,$group_ids);
			UserMobile::saveVerifyCodeByPhone($user['mobile'],'',true);
			return $this->redirect('user/users/edit/' . $result['data']['uid'])->with('global_tips','用户创建成功');
		}else{
			return $this->back()->with('global_tips',$result['error_description']);
		}
	}
	
    public function getEdit($uid)
	{
		$data = array();
		$user = UserModel::getUserInfo($uid);
		if($user) $user['avatar'] = Utility::getImageUrl($user['avatar']);
		$data['user'] = $user;
		$data['address'] = "";
		$address = AccountReceiptAddress::searchReceiptAddress($uid,1);
		if (isset($address[0])) {
		    $data['address'] = $address[0]['region'].$address[0]['address'];
		    $data['consignee'] = $address[0]['consignee'];
		    $data['phone'] = $address[0]['phone'];
		}
		$moneys = MoneyService::listYouMoney(array($uid));
		$data['moneys'] = $moneys;
		$data['diamound'] = MoneyService::listYouDiamond(array($uid));
		$data['grouplist'] = UserModel::getUserGroupList();
		$data['allow_modify_user'] = AuthService::verifyNodeAuth('user/users/op-money');
		$data['is_valid'] = UserMobile::phoneVerifyStatus($user['mobile']);
		$data['admin_lv'] = AccountAdmin::isAdmin($uid)?1:0;
		return $this->display('users-edit',$data);
	}
	
	public function getInfo($uid)
	{
		$data = array();
		$data['user'] = UserService::getUserInfo($uid,'full');
		return $this->display('users-info',$data);
	}
	
    /**
	 * 编辑用户信息
	 */
	public function postEdit()
	{
		$input = Input::only('nickname','email','sex','mobile','birthday','password','summary','address','alipay_num','alipay_name');
		$area = Input::only('province','city','region');
		$uid = Input::get('uid');
		$user = UserModel::getUserInfo($uid);
		if(!$user) return $this->back()->with('gloable_tips','用户不存在');
		$admin_lv = (int)Input::get('admin_lv',0);
		if ($admin_lv == 1) {
		    AccountAdmin::insertAdmin($uid);
		}
		$group_ids = Input::get('group_id');
	    if(Input::hasFile('avatar')){			
	        $config = array(
	    	    'savePath'=>'/userdirs/avatar/',
	    	    'driverConfig'=>array('autoSize'=>array(320,120,100,80,60,50))
	    	);
	    	$uploader = new ImageHelper($config);
	    	$avatar = $uploader->upload('avatar');
	    	if($avatar !== false){
	    		$input['avatar'] = $avatar['filepath'] . '/' . $avatar['filename'];
	    	}
		}
		/*
		if(empty($input['password'])) {
			unset($input['password']);
		}else{
			UserModel::modifyPwd($uid, $input['password']);
		}
		*/
		if($user['email'] != $input['email']){
			UserModel::modifyEmail($uid, $input['email']);
		}
				
		UserModel::updateUserInfo($uid, $input,$group_ids);
		UserModel::updateArea($uid,$area);
		$is_valid = (int)Input::get('is_valid',0);
		if($input['mobile'] && Utility::validateMobile($input['mobile'])){
			if($is_valid && UserMobile::phoneVerifyStatus($input['mobile'])===false){
				UserMobile::passPhoneValid($input['mobile'], $uid,1);
			}elseif(!$is_valid){
				UserMobile::passPhoneValid($input['mobile'], $uid,0);
			}
		}
		/*
		if(AuthService::verifyNodeAuth('user/users/op-money')===true){
			$score = (int)Input::get('score',0);
			$experience = (int)Input::get('experience',0);
			$score_info = Input::get('score_info');
			$experience_info = Input::get('experience_info');
			$platform = Input::get('platform');
			
			if($score != 0 && empty($score_info)) return $this->back()->with('global_tips','请填写游币备注');
			if($experience != 0 && empty($experience_info)) return $this->back()->with('global_tips','请填写经验备注');
			if((is_numeric($score) && $score != 0) || (is_numeric($experience) && $experience != 0)){
				if($platform == 'ios' && $score && $score_info){
					$info = '管理员后台操作' . ($score>0 ? '加' : '减') . $score . '游币';
					NoticeService::sendInitiativeMessage(0,0,'',$score_info,$score_info,false,false,array($uid));
				}elseif($platform == 'android' && $score && $score_info){
					$info = '管理员后台操作' . ($score>0 ? '加' : '减') . $score . '游币';
					MoneyService::doAccount($uid,$score,'manage',$info);
				}
				
				if($platform == 'ios' && $experience && $experience_info){
					$info = '管理员后台操作' . ($score>0 ? '加' : '减') . $score . '经验';
					NoticeService::sendInitiativeMessage(0,0,'',$experience_info,$experience_info,false,false,array($uid));
				}elseif($platform == 'android' && $score && $score_info){
					MoneyService::doAccountExperience($uid, $experience);
				}
				
			    if($platform == 'ios'){
				    CreditService::handOpUserCredit($uid, $score, $experience,'admin_op',$score_info);
				}
		    }
		}
		*/
		return $this->back()->with('global_tips','用户信息修改成功');
	}
	
	public function getInitAvatar($page=1)
	{
		$result = UserModel::searchList(array(),$page,100);
		$users = $result['users'];
		foreach($users as $row){
			$uid = $row['uid'];
			$file = storage_path() . '/userdirs/avatar/' . $uid;
			if(is_readable($file . '.png')){
				$layer = ImageWorkshop::initFromPath($file . '.png');
				//默认320
		    	//$layer->resizeInPixel(320,null,true);
				//$layer->save($server_path,$new_filename_320,true,null,95);
			}
		    if(is_readable($file . '.jpg')){
				
			}
		}
	}
	
	public function getPwd($uid)
	{
		$data = array();
		$data['user'] = UserModel::getUserInfo($uid);
		return $this->display('users-pwd',$data);
	}
	
	public function postPwd()
	{
		$uid = Input::get('uid');
		$password = Input::get('newpassword');
	    if(empty($password)) {
	    	return $this->back()->with('global_tips','密码不能为空');
		}else{
			UserModel::modifyPwd($uid, $password);
			return $this->back()->with('global_tips','密码修改成功');
		}
	}
	
    /**
	 * 屏蔽头像
	 */
	public function getShieldAvatar($uid)
	{
		UserModel::shieldField($uid,'avatar','/userdirs/common/avatar@2x.png?v=' . time());
		return $this->back()->with('global_tips','屏蔽头像完成');
	}
	
	/**
	 * 屏蔽昵称
	 */
    public function getShieldNickname($uid)
	{
		UserModel::shieldField($uid,'nickname','玩家'.$uid);
		return $this->back()->with('global_tips','屏蔽昵称完成');
	}

	public function getClose($uid,$type="true")
	{
        $error = "";
		//删帖
		$res = MoneyService::del_game_file($uid,$type);

        $vuser = $type=="true"?0:1;

        //禁止登录
        $user_res = UserService::updateUserInfo($uid,array('vuser'=>$vuser));
        AccountSession::delSessionAll($uid);

        //删除论坛
        if($type="false")
        $forum_res =TopicService::deactivate_account(array('uid'=>$uid));

        //删除图片
        $user_res = UserService::del_user_pic($uid,$type);

		//清Session

        //帖子（删除）、
        // 评论内容（屏蔽，内容已被删除）、
        //上传图片（屏蔽，图替换成我们默认图片）、 yang
        // 玩家情报（屏蔽，内容已被删除）
        //禁止登陆、 ma
        //禁止别人查看它的用户信息 ma

		return $this->back('已完成操作');
	}

    public function getJiefeng()
    {

        //只允许用户登陆和别人可以查看他的用户信息，删除的信息不需要恢复
    }
	
	public function getBan($uid,$time=0)
	{
		$expired = $time ? (time() + (int)$time) : 0;
		UserModel::doBan($uid,1, $expired);
		return $this->back()->with('global_tips','禁言完成');
	}
	
    public function getUnban($uid)
	{
		UserModel::doUnban($uid,1);
		return $this->back()->with('global_tips','解除禁言完成');
	}
	
	public function getClearPost($uid)
	{
		UserModel::clearUserTopicAndComment($uid);
		return $this->back()->with('global_tips','删除用户发言完成');
	}
	
	public function getCreditHistory($uid)
	{
		$data = array();
		$page = Input::get('page',1);
		$pagesize = 10;
		$search['uid'] = $uid;
		$result = UserModel::getCreditHistory($search,$page, $pagesize);
		$data['datalist'] = $result['result'];
		$data['user'] = UserService::getUserInfo($uid);
		$pager = Paginator::make(array(),$result['total'],$pagesize);
		$data['pagelinks'] = $pager->links();
		return $this->display('credit-history',$data);
	}
	
	public function getMoneyHistory($uid)
	{
		$data = array();
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		$search['uid'] = $uid;
		$result = MoneyService::getHistory($search, $pageIndex, $pageSize);
		foreach($result['result'] as $key=>$row){
			$mtime = is_numeric($row['operationTime']) ? $row['operationTime']/1000: strtotime($row['operationTime']);
			$info = isset($row['operationDesc']) ? $row['operationDesc'] : '';
			$result['result'][$key] = array('uid'=>$row['accountId'],'credit'=>$row['balanceChange'],'mtime'=>$mtime,'info'=>$info);
		}
		$data['datalist'] = $result['result'];
		$data['user'] = UserService::getUserInfo($uid);
		$pager = Paginator::make(array(),$result['total'],$pageSize);
		$data['pagelinks'] = $pager->links();
		return $this->display('credit-history',$data);
	}
	
	/**
	 * 批量发送游币经验页面
	 */
	public function getBatchSend()
	{
		return $this->display('batch-send-credit');
	}
	
    /**
	 * 批量发送游币经验页面
	 */
	public function getBatchSendAndroid()
	{
		return $this->display('batch-send-android');
	}
	
	/**
	 * 批量发送游币经验提交
	 */
	public function postBatchSendAndroid()
	{
	    Input::flash();
		$input = Input::all();
		if(empty($input['score']) && empty($input['experience'])) return $this->back()->with('global_tips','游币或经验请至少填写一项');
		$rule = array(
				'score'=>'numeric',
				//'experience'=>'numeric',
				'score_info'=>'required_with:score',
				//'experience_info'=>'required_with:experience',
				'uids'=>'required|okid'
		);
		$msg = array(
				'uids.required'=>'用户id不能为空',
				'uids.okid'=>'用户id必须为整数',
				'score.numeric'=>'游币必须为整数',
				//'experience.numeric'=>'经验必须为整数',
				'score_info.required_with'=>'游币备注不能为空',
				//'experience_info.required_with'=>'游币备注不能为空'
		);
		Validator::extend('okid', function($attribute, $value, $parameters)
		{
			$okid = true;
			$ids_arr = explode(',',$value);
			foreach ($ids_arr as $id){
				if(!is_numeric($id)) {
					$okid = false;
					break;
				}
			}
			return $okid;
		});
		$validator = Validator::make($input,$rule,$msg);
		if($validator->fails()){
			return $this->back()->with('global_tips',$validator->messages()->first());
		}else{
			$score = $input['score'];
			$score_info = $input['score_info'];
			//$experience = $input['experience'];
			//$experience_info = $input['experience_info'];
			$uids_arr = explode(',',$input['uids']);
			$uids_arr = array_unique($uids_arr);
			
			if($score){
				foreach ($uids_arr as $uid){
					$user = UserModel::getUserInfo($uid);
					if(!$user) continue;
					if($score){
						//$info = '管理员后台操作,为UID为'.$uid.'的用户'. ($score>0 ? '加' : '减') . $score . '游币';
						$info = $score_info;
					    MoneyService::doAccount($uid,$score,'manage',$info);
					}					
				}
				return $this->back()->with('global_tips','发送成功');
			}
			return $this->back()->with('global_tips','发送失败');
		}
	}
	
	/**
	 * 批量发送游币经验提交
	 */
	public function postBatchSend()
	{
		Input::flash();
		$input = Input::all();
		if(empty($input['score']) && empty($input['experience'])) return $this->back()->with('global_tips','游币或经验请至少填写一项');
		$rule = array(
				'score'=>'numeric',
				'experience'=>'numeric',
				'score_info'=>'required_with:score',
				'experience_info'=>'required_with:experience',
				'uids'=>'required|okid'
		);
		$msg = array(
				'uids.required'=>'用户id不能为空',
				'uids.okid'=>'用户id必须为整数',
				'score.numeric'=>'游币必须为整数',
				'experience.numeric'=>'经验必须为整数',
				'score_info.required_with'=>'游币备注不能为空',
				'experience_info.required_with'=>'游币备注不能为空'
		);
		Validator::extend('okid', function($attribute, $value, $parameters)
		{
			$okid = true;
			$ids_arr = explode(',',$value);
			foreach ($ids_arr as $id){
				if(!is_numeric($id)) {
					$okid = false;
					break;
				}
			}
			return $okid;
		});
		$validator = Validator::make($input,$rule,$msg);
		if($validator->fails()){
			return $this->back()->with('global_tips',$validator->messages()->first());
		}else{
			$score = $input['score'];
			$score_info = $input['score_info'];
			$experience = $input['experience'];
			$experience_info = $input['experience_info'];
			$uids_arr = explode(',',$input['uids']);
			$uids_arr = array_unique($uids_arr);
			
			if($score || $experience){
				foreach ($uids_arr as $uid){
					if($uid==0) continue;
					$user = UserModel::getUserInfo($uid);
					if(!$user) continue;
					if($score){
						$info = '管理员后台操作,为UID为'.$uid.'的用户'. ($score>0 ? '加' : '减') . $score . '游币';
						NoticeService::sendInitiativeMessage(0,0,'',$score_info,$score_info,false,false,array($uid));
						//$this->operationPdoLog($info, $input);
					}
					if($experience){
						$info = '管理员后台操作,为UID为'.$uid.'的用户'. ($score>0 ? '加' : '减') . $experience . '经验';
						NoticeService::sendInitiativeMessage(0,0,'',$experience_info,$experience_info,false,false,array($uid));
						//$this->operationPdoLog($info, $input);
					}
					CreditService::handOpUserCredit($uid, $score, $experience,'admin_op',$score_info);
				}
			}
			return $this->back()->with('global_tips','发送成功');
		}
	}
	
	public function getAddMobileToBlacklist($mobile='')
	{
		$phone = Input::get('mobile',$mobile);
		if($phone && Utility::validateMobile($phone)===true){
			MobileBlackList::addMobile($phone);
			return $this->redirect('user/users/mobile-blacklist/'.$phone,'添加成功');
		}
		return $this->redirect('user/users/mobile-blacklist');
	}
	
    public function getDelMobileFromBlacklist($mobile='')
	{
		$phone = Input::get('mobile',$mobile);
		if($phone && Utility::validateMobile($phone)===true){
			MobileBlackList::deleteMobile($phone);
			return $this->back('删除成功');
		}
		return $this->redirect('user/users/mobile-blacklist');
	}
	
	public function getMobileBlacklist()
	{
		$pageIndex = (int)Input::get('page',1);
		$pageSize = 10;
		$mobile = Input::get('mobile');
		$result = MobileBlackList::searchMobile($mobile,$pageIndex,$pageSize);
		//print_r($result);
		$search['mobile'] = $mobile;
		$pager = Paginator::make(array(),$result['totalCount'],$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['datalist'] = $result['result'];
		$data['pagelinks'] = $pager->links();
		return $this->display('mobile-blacklist',$data);
	}
	
	public function getCheckinReport()
	{
		$days = (int)Input::get('days',5);
		$pageIndex = (int)Input::get('page',1);
		$pageSize = 100;
		$start = mktime(0,0,0,date('m'),date('d'),date('Y')) - 3600*24*($days-1);
		
		$result = Checkinfo::db()
		->select(Checkinfo::raw('uid,count(*) as times'))
		->where('ctime','>=',$start)
		->groupBy('uid')
		->havingRaw('`times`'.' >= '.$days)			
		->orderBy('times','desc')
		//->forPage($pageIndex,$pageSize)
		->get();		
		
		$uids = array();
		foreach($result as $row){
			$uids[] = $row['uid'];
		}
		$users = UserService::getBatchUserInfo($uids);
		$data = array();
		//$search = array();
		//$pager = Paginator::make(array(),$total,$pageSize);
		//$pager->appends($search);
		//$data['search'] = $search;
		$data['datalist'] = $result;
		$data['users'] = $users;
		$data['days'] = $days;
		$data['totalcount'] = count($result);
		return $this->display('checkin-report',$data);
	}
	
	public function getOpenAndroidMoney($uid)
	{
		MoneyService::registerAccount($uid);
		return $this->back('开通成功');
	}
	
	public function getTools()
	{
		return $this->display('user-tools');
	}
	
	public function getVerifycode()
	{
		$hashcode = Input::get('hashcode');		
		$captcha = \Mews\Captcha\CaptchaCache::instance();
		return $captcha::create($hashcode);
	}
	
	public function postTools()
	{
		$tool = Input::get('tool');
		$mobile = Input::get('mobile');
		if(!$mobile) return $this->back('手机号不能为空');
		if($tool=='clear-login-limit'){
			$device_list = LoginLimit::db()->where('login_name','=',$mobile)->lists('limit_field');
			if($device_list && is_array($device_list) && count($device_list)>0){
				LoginLimit::db()->whereIn('limit_field',$device_list)->delete();
			}
		}elseif($tool=='clear-sms-limit'){
			MobileSmsHistory::db()->where('mobile','=',$mobile)->delete();
		}elseif($tool =='verifycode'){
			$captcha = \Mews\Captcha\CaptchaCache::instance();		
			if($captcha::check('1234567890',$mobile)===false){
				return $this->back('验证码错误:' . $mobile);
			}else{
				return $this->back('验证成功');
			}
		}
		return $this->back('清除成功');
	}
	
	public function getSelectSearch()
	{
		$out = array();
		$keyword = Input::get('q');
		if(!$keyword) return $keyword;
		$result = array();
		if(Utility::validateMobile($keyword)==true){
			$info = Account::getUserInfoByField($keyword,'mobile');
			if($info) $result[] = $info;
		}elseif(is_numeric($keyword)){
			if(strlen($keyword)<6) return $this->json($out);
		    $info = Account::getUserInfoByField($keyword,'uid');
			if($info) $result[] = $info;	
		}else{			
		    $result = Account::searchUserByNickname($keyword,1,20);
		}
		foreach($result as $row){
			$out['user_list'][] = array(
		        'id'=>$row['uid'],
		        'text'=>$row['nickname']
		    );
		}	
		
		return $this->json($out);
	}
	
	public function getSelectSearchId()
	{
	    $out = array();
	    $keyword = Input::get('q');
	    if(!$keyword) return $keyword;
	    $result = array();
	        $info = Account::getUserInfoByField($keyword,'uid');
	        if($info) $result[] = $info;
	    foreach($result as $row){
	        $out['user_list'][] = array(
	            'id'=>$row['uid'],
	            'text'=>$row['nickname']
	        );
	    }
	
	    return $this->json($out);
	}
	
    public function getSelectInit()
	{
		$id = Input::get('id');
		if($id){
			$user = Account::getUserInfoByField($id,'uid');
			if($user){
				return $this->json(array('id'=>$user['uid'],'text'=>$user['nickname']));
			}
		}
	}
	
	public function getForceOut($uid)
	{
		AccountSession::saveSession($uid,'');
		return $this->back('强制下线成功');
	}
	
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
	
	public function getAddAllUser()
	{
	    $uids = Input::get('uids');
	    $admin_id = $this->current_user['id'];
	    $out_html = "";
	    if($uids){
	        $uid_arr = json_decode($uids,true);
	        foreach ($uid_arr as $k=>$v) {
	            $keyname = 'selected_' . $admin_id . '_uids';
	            $selecteds = array();
	            if(Session::has($keyname)){
	                $selecteds = Session::get($keyname);
	            }
	            $selecteds[$k]  = array('uid'=>$k,'nickname'=>$v);
	            Session::put($keyname,$selecteds);
	            $out_html .= '<li><a href="javascript:void(0)" data-uid="'.$k.'" class="delete-selected-uid-ajax">'.$v.'<i class="icon-trash"></i></a></li>';
	        }
	        return $out_html;
	    }
	    return $this->json(false);
	}
	
    public function getDeleteSelectUser()
	{
		$uid = Input::get('uid');
		$admin_id = $this->current_user['id'];
		if($uid){
			$keyname = 'selected_' . $admin_id . '_uids';
			$selecteds = array();
			if(Session::has($keyname)){
				$selecteds = Session::get($keyname);
				foreach($selecteds as $key=>$value){
					if($uid==$value['uid']){
						unset($selecteds[$key]);
					}
				}
			}
			Session::put($keyname,$selecteds);
			return $this->json(true);			
		}
		return $this->json(false);
	}
	
	public function getClearSelectUser()
	{
		$admin_id = $this->current_user['id'];
		$keyname = 'selected_' . $admin_id . '_uids';
		Session::forget($keyname);
		return $this->json(true);	
	}

	public function getAndroidReport()
	{
		$start = Input::get('startdate');
		$end = Input::get('enddate');
		if(!$start){
			$start = date('Y-m-d');
		}
		$search['startdate'] = $start;
		if(!$end){
			$end = date('Y-m-d');
		}
		$search['enddate'] = $end;
		$end = $end . '23:59:59';
		$data = array();
		$data['search'] = $search;
		$data['buy_sum'] = AndroidMoney::sumBuy($start,$end);
		$data['checkins_sum'] = AndroidMoney::sumCheckins($start,$end);
		$data['task_sum'] = AndroidMoney::sumTask($start,$end);
		$data['share_sum'] = AndroidMoney::sumShare($start,$end);
		$data['shop_sum'] = AndroidMoney::sumShop($start,$end);
		$data['manage_sum'] = AndroidMoney::sumManage($start,$end);
		return $this->display('android-report',$data);
	}
    public function getRewardManage(){
        $data = array();

        $input = Input::get();
        if(Input::get('uids',"")){
            $input['uids'] = substr($input['uids'],0,-1);
            $input['uids'] = explode(',',$input['uids']);
            unset($input['undefined']);
        }
        if(isset($input['yb'])){
            foreach($input['uids'] as $k=>$v){
                if(!(int)$v){
                    continue;
                }
                $form = array(
                    'rechargeAccountId' => $v,
                    'balanceChange' => $input['yb']=="jia"?$input['num_yb']:"-".$input['num_yb'],                  
                    'platform'=>'ios',  
                    'operationInfo' =>$input['yb']=="jia"?"系统发放游币":"系统扣除游币",
                    'type' => 'manage',
                    'operationPerson' => parent::getSessionUserName()
                );
                $res1 = AllService::excute("USER",$form,'updateaccount');

            }
        }

        if(isset($input['zs'])){
            foreach($input['uids'] as $k=>$v){
                if(!(int)$v){
                    continue;
                }
                $form = array(
                    'rechargeAccountId' => $v,
                    'platform'=>'ios',
                    'balanceChange' => $input['zs']=="jia"?$input['num_zs']:"-".$input['num_zs'],
                    'operationInfo' =>$input['zs']=="jia"?"系统发放钻石":"系统扣除钻石",
                    'type' => 'manage',
                    'currencyType' => 1,
                    'operationPerson' => parent::getSessionUserName()
                );
                $res2 = AllService::excute("USER",$form,'updatediamond');
            }
        }
        $sess = Session::all();
        if(end($sess)){
            $user_arr = end($sess);
            $users =  array_keys($user_arr);
            $moneys = MoneyService::listYouMoney($users);
            $diamond = MoneyService::listYouDiamond($users);
            if($moneys&&$diamond){
                $data['data'] = $user_arr;
                $data['money'] = $moneys;
                $data['diamond'] = $diamond;
            }
        }
        return $this->display('reward-list',$data);
    }
    public function getOperationList(){
        
        $data = array();
        $page = Input::get('page',1);
        $search = Input::only('startdate','enddate','keytype','keyword');
        $pagesize = 20;

        $result = MoneyService::listDiamondOperation($search,$page,$pagesize);
        $totalcount = $result['totalcount'];
        $data['data'] = $result['data'];
        $uids = array();
        $all_users = array();
        foreach($data['data'] as &$row){
            $uids[] = preg_replace('/\D/','',$row['accountId']);
            $row['real_accountId'] = preg_replace('/\D/','',$row['accountId']);
        }
        $diamond_ios = MoneyService::listYouDiamondios($uids);
        
        $pager = Paginator::make(array(),$totalcount,$pagesize);
        $pager->appends($search);
        $data['search'] = $search;
        $data['pagelinks'] = $pager->links();
        $data['totalcount'] = $totalcount;
        $data['ios_diamond'] = $diamond_ios;
//         print_r(json_encode($data));exit;
        return $this->display('operation-list',$data);
    }

    /**
     * 批量选中用户
     * @return mixed
     */
    public function getBatchCheckUsersList () {
        $data = array();


        return $this->display('batch-check-users-list',$data);
    }

    public function postGetUsersInfo () {
        $users = Input::get('users');
        if(!$users) return json_encode(array('success'=>400, 'msg'=>'操作失败', 'data'=>''));;
        $users = explode(',', $users);
        $data = UserModel::getUsersInfo($users);

        if($data){
            $admin_id = $this->current_user['id'];
            $keyName = 'selected_' . $admin_id . '_uids';
            if(Session::has($keyName)){
                $selectedTemp = Session::get($keyName);
            }
            foreach ($data as $key => $value) {
                $selectedTemp[$value['uid']]  = array('uid'=>$value['uid'],'nickname'=>$value['nickname']);
            }
            Session::put($keyName,$selectedTemp);

            echo json_encode(array('success'=>200, 'msg'=>'操作成功', 'data'=>$data));
        } else {
            echo json_encode(array('success'=>400, 'msg'=>'操作失败', 'data'=>''));
        }
    }

    public function getCurrencyHistory ($uid) {
        $data = $search = array();
        $pageIndex = Input::get('page',1);
        $pageSize = 20;
        $search['accountId'] = $uid;
        $search['platform'] = 'ios';
//        $search['currencyType '] = '2';
        $result = MoneyService::getCurrencyHistory($search, $pageIndex, $pageSize);
//        dd($result);
        if(isset($result['totalCount'])&&$result['totalCount']){
            foreach($result['result'] as &$row){
                $mtime = isset($row['operationTime']) ? date('Y-m-d H:i:s', $row['operationTime']/1000) : '';
                $info = isset($row['operationDesc']) ? $row['operationDesc'] : '';
                $row['mtime'] = $mtime;
                $row['info'] = $info;
            }
            $data['datalist'] = $result['result'];
            $pager = Paginator::make(array(),$result['totalCount'],$pageSize);
            $data['pagelinks'] = $pager->links();
        }

        return $this->display('currency-history',$data);
    }

    public function getDiamondsHistory ($uid) {
        $data = $search = array();
        $pageIndex = Input::get('page',1);
        $pageSize = 20;
        $search['accountId'] = $uid;
        $search['platform'] = 'ios';
        $search['currencyType '] = '1';
        $result = MoneyService::getDiamondsHistory($search, $pageIndex, $pageSize);
        if(isset($result['totalCount'])&&$result['totalCount']){
            foreach($result['result'] as &$row){
                $mtime = isset($row['operationTime']) ? date('Y-m-d H:i:s', $row['operationTime']/1000) : '';
                $info = isset($row['operationDesc']) ? $row['operationDesc'] : '';
                $row['mtime'] = $mtime;
                $row['info'] = $info;
            }
            $data['datalist'] = $result['result'];
            $pager = Paginator::make(array(),$result['totalCount'],$pageSize);
            $data['pagelinks'] = $pager->links();
        }

        return $this->display('currency-history',$data);
    }

    /**
     * excel导出
     */
    public function getDataDownload()
    {
		$data = array();
		$page = Input::get('page',1);
		$search = Input::only('startdate','enddate','keytype','keyword');		
		$pagesize = 1000;
	    $sort = Input::get('sort','dateline');
	    $search['sort'] = $sort;
		if(in_array($sort,array('dateline','score'))){
		    $order = array($sort=>'desc');
		}else{
			$order = array('dateline'=>'desc');
		}
		$total = UserModel::searchCount($search);
		$totalcount = $total;
		//$totalcount = $total*19;		
		//$page = $page > ceil($total/20) ? ceil($page/19) : $page;
		 
		$result = UserModel::searchList($search,$page,$pagesize,$order);
		$uids = array();
		$all_users = array();
		foreach($result['users'] as $row){
			$uids[] = $row['uid'];
			$all_users[$row['uid']] = $row['nickname'];
		}
		$data['datalist'] = $result['users'];

        //var_dump($result);die;
        require_once base_path() . '/libraries/PHPExcel.php';
        $excel = new \PHPExcel();
        $excel->setActiveSheetIndex(0);
        $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $excel->getActiveSheet()->setTitle('用户信息');
        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $excel->getActiveSheet()->setCellValue('A1','UID');
        $excel->getActiveSheet()->setCellValue('B1','用户昵称');
        $excel->getActiveSheet()->setCellValue('C1','手机号');
        $excel->getActiveSheet()->setCellValue('D1','IDFA');
        $excel->getActiveSheet()->setCellValue('E1','注册时间');
        $excel->getActiveSheet()->freezePane('A2');
        foreach($data['datalist'] as $index=>$row){
            $uid = isset($row['uid'])?$row['uid']:'';
            $nickname = isset($row['nickname'])?$row['nickname']:'';
            $mobile = isset($row['mobile'])?$row['mobile']:'';
            $idfa = isset($row['idfa'])?$row['idfa']:'';
            $dateline = isset($row['dateline'])?date('Y-m-d H:i:s',$row['dateline']):'';
            
            $excel->getActiveSheet()->setCellValue('A'.($index+2), $uid);
            $excel->getActiveSheet()->setCellValue('B'.($index+2), $nickname);
            $excel->getActiveSheet()->setCellValue('C'.($index+2), $mobile);
            $excel->getActiveSheet()->setCellValue('D'.($index+2), $idfa);
            $excel->getActiveSheet()->setCellValue('E'.($index+2), $dateline);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. date('Y-m-d').'用户信息.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = \PHPExcel_IOFactory::createWriter($excel,'Excel2007');
        $writer->save('php://output');
    }
    
}