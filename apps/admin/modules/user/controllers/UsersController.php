<?php
namespace modules\user\controllers;

use modules\statistics\models\AndroidMoney;
use Youxiduo\User\Model\AccountSession;

use Youxiduo\Helper\Utility;

use Youxiduo\User\Model\UserMobile;
use Youxiduo\Android\Model\CreditAccount;
use Yxd\Services\CreditService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Yxd\Modules\Core\BackendController;
use Yxd\Services\UserService;
use modules\user\models\UserModel;
use Yxd\Utility\ImageHelper;
use Yxd\Services\PassportService;
use Yxd\Modules\Message\NoticeService;
use Youxiduo\System\AuthService;
use Youxiduo\User\Model\Account;
use PHPImageWorkshop\ImageWorkshop;
use Youxiduo\V4\User\MoneyService;

use Youxiduo\User\Model\MobileSmsHistory;
use Youxiduo\V4\User\Model\LoginLimit;

use Youxiduo\Android\Model\Checkinfo;
use Youxiduo\Android\Model\CreditLevel;
use Youxiduo\V4\User\Model\MobileBlackList;
use Youxiduo\Base\AllService;
use Youxiduo\Helper\MyHelpLx;

class UsersController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'user';
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
		foreach($result['users'] as $row){
			$uids[] = $row['uid'];
		}
		$data['datalist'] = $result['users'];
		$data['usergroups'] = $result['groups'];
		$data['bans'] = UserModel::getBanList();

		$moneys = MoneyService::getQueryResult($uids);
		
		$pager = Paginator::make(array(),$totalcount,$pagesize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['totalcount'] = $totalcount;
		$data['allow_edit'] = AuthService::verifyNodeAuth('user/users/edit');
		$data['allow_ban'] = AuthService::verifyNodeAuth('user/users/ban');
		$data['allow_pwd'] = AuthService::verifyNodeAuth('user/users/pwd');
		$data['allow_clear'] = AuthService::verifyNodeAuth('user/users/clear-post');
		$data['allow_shield_avatar'] = AuthService::verifyNodeAuth('user/users/shield-avatar');
		$data['allow_shield_nickname'] = AuthService::verifyNodeAuth('user/users/shield-nickname');		
		$data['allow_ios_money'] = AuthService::verifyNodeAuth('user/users/ios-money');
		$data['allow_android_money'] = AuthService::verifyNodeAuth('user/users/android-money');
		$data['android_money'] = $moneys;//CreditAccount::getUserCreditByUids($uids);
        //getUserLevel

        $uids=array();
        foreach($data['datalist'] as $key=>$val){
            $uids[]=$val['uid'];
        }
        $users_level = CreditAccount::getUserCreditByUids($uids);
        foreach($data['datalist'] as &$row){
            if(isset($users_level[$row['uid']])){
                $level = CreditLevel::getUserLevel($users_level[$row['uid']]['experience']);
                $row['experience'] = $users_level[$row['uid']]['experience'];
                $row['level_name'] = $level['name'];
                $row['level_max'] = $level['end'];
            }else{
                $row['experience'] = 0;
                $row['level_name'] = '1';
                $row['level_max'] = '50';
            }
            if($row['mobile']){
                $row['mobile'] = preg_replace('/(1[3578]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$row['mobile']);
            }
            $out[] = $row;
            if(isset($row['uid'])){
                $res = AllService::excute2("USER",array('accountId'=>$row['uid']),"account/query");
                if($res['success']){
                    $row['alipayAccount'] = isset($res['data'][0]['alipayAccount'])?$res['data'][0]['alipayAccount']:"0";
                    $row['balance'] = isset($res['data'][0]['balance'])?$res['data'][0]['balance']/100:"0";
                    $row['cashTotal'] = isset($res['data'][0]['cashTotal'])?$res['data'][0]['cashTotal']/100:"0";
                }
            }
        }

        
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
		$moneys = MoneyService::getQueryResult(array($uid));
		$data['moneys'] = $moneys;
		$data['grouplist'] = UserModel::getUserGroupList();
		$data['allow_modify_user'] = AuthService::verifyNodeAuth('user/users/op-money');
		$data['is_valid'] = UserMobile::phoneVerifyStatus($user['mobile']);
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
		$input = Input::only('nickname','email','sex','mobile','birthday','password');
		$uid = Input::get('uid');
		$user = UserModel::getUserInfo($uid);
		if(!$user) return $this->back()->with('gloable_tips','用户不存在');

        $admin=$this->getSessionData('youxiduo_admin');//管理员信息

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
		$is_valid = (int)Input::get('is_valid',0);
		if($input['mobile'] && Utility::validateMobile($input['mobile'])){
			if($is_valid && UserMobile::phoneVerifyStatus($input['mobile'])===false){
				UserMobile::passPhoneValid($input['mobile'], $uid,1);
			}elseif(!$is_valid){
				UserMobile::passPhoneValid($input['mobile'], $uid,0);
			}
		}
		if(AuthService::verifyNodeAuth('user/users/op-money')===true){
            $rmb = Input::get('rmb',0);
            $rmb_info = Input::get('rmb_info');
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

            if((is_numeric($rmb) && $rmb != 0)) {
                if ($platform == 'ios' && $rmb && $rmb_info) {
//                    NoticeService::sendInitiativeMessage(0, 0, '', $rmb_info, $rmb_info, false, false, array($uid));
                } elseif ($platform == 'android' && $rmb) {
                    $info = '管理员后台操作' . ($rmb > 0 ? '加' : '减') . $rmb . '人民币';
                    $arr = array(
                        'rechargeAccountId'=>$uid,
                        'balanceChange'=>$rmb*100,
                        'type'=>'manage',
                        'operationInfo'=>$info,
                        'operationPerson' =>$admin['realname'],
//                        'operatorId' =>$admin['id'],
                        'remoteIp' => MyHelpLx::get_real_ip(),
                    );
                    MoneyService::doRmb($arr);
                }
            }

		}
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

    public function getRmbHistory($uid)
    {
        $data = $search = array();
        $pageIndex = Input::get('page',1);
        $pageSize = 10;
        $search['accountId'] = $uid;
        $search['platform'] = 'android';
        $search['currencyType '] = '2';
        $result = MoneyService::getRmbHistory($search, $pageIndex, $pageSize);
        if(isset($result['totalCount'])&&$result['totalCount']){
            foreach($result['result'] as &$row){
                $mtime = is_numeric($row['operationTime']) ? $row['operationTime']/1000: strtotime($row['operationTime']);
                $info = isset($row['operationDesc']) ? $row['operationDesc'] : '';
                $row['uid'] = $row['accountId'];
                $row['credit'] = $row['balanceChange'];
                $row['mtime'] = $mtime;
                $row['info'] = $info;
            }
            $data['datalist'] = $result['result'];
            $data['user'] = UserService::getUserInfo($uid);
            $pager = Paginator::make(array(),$result['totalCount'],$pageSize);
            $data['pagelinks'] = $pager->links();
        }

        return $this->display('rmb-history',$data);
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
	 * 批量发送人民币页面
	 */
	public function getRmbSendAndroid()
	{
	    return $this->display('rmb-send-android');
	}
	
	/**
	 * 批量发送人民币提交
	 */
	public function postRmbSendAndroid()
	{
	    Input::flash();
	    $input = Input::all();
	    if(empty($input['rmb'])) return $this->back()->with('global_tips','请填写人民币');
	    $rule = array(
	        'rmb'=>'numeric',
	        'rmb_info'=>'required_with:rmb',
	        'uids'=>'required|okid'
	    );
	    $msg = array(
	        'uids.required'=>'用户id不能为空',
	        'uids.okid'=>'用户id必须为整数',
	        'rmb.numeric'=>'人民币必须为整数',
	        'rmb_info.required_with'=>'人民币备注不能为空',
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
	    $admin=$this->getSessionData('youxiduo_admin');//管理员信息
	    $validator = Validator::make($input,$rule,$msg);
	    if($validator->fails()){
	        return $this->back()->with('global_tips',$validator->messages()->first());
	    }else{
	        $rmb = $input['rmb'];
	        $rmb_info = $input['rmb_info'];
	        $uids_arr = explode(',',$input['uids']);
	        $uids_arr = array_unique($uids_arr);
	        	
	        if($rmb){
	            foreach ($uids_arr as $uid){
	                $user = UserModel::getUserInfo($uid);
	                if(!$user) continue;
	                if($rmb){
	                    $info = $rmb_info;
	                    $arr = array(
	                        'rechargeAccountId'=>$uid,
	                        'balanceChange'=>$rmb*100,
	                        'type'=>'manage',
	                        'operationInfo'=>$info,
	                        'operationPerson' =>$admin['realname'],
	                        'remoteIp' => MyHelpLx::get_real_ip(),
	                    );
	                    MoneyService::doRmb($arr);
	                    
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
		$data['gift_sum'] = AndroidMoney::sumGift($start,$end);
		$data['checkins_sum'] = AndroidMoney::sumCheckins($start,$end);
		$data['task_sum'] = AndroidMoney::sumTask($start,$end);
		$data['share_sum'] = AndroidMoney::sumShare($start,$end);
		$data['shop_sum'] = AndroidMoney::sumShop($start,$end);
		$data['manage_sum'] = AndroidMoney::sumManage($start,$end);
		return $this->display('android-report',$data);
	}
}