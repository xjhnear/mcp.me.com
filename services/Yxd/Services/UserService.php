<?php
namespace Yxd\Services;

use Yxd\Modules\Core\CacheService;

use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Event;

use Yxd\Models\User;
use Yxd\Models\Passport;
use Illuminate\Support\Facades\Config;
use Yxd\Services\TaskService;

use Yxd\Services\Models\TuiguangAccount;
use Yxd\Services\Models\Account;
use Yxd\Services\Models\CreditAccount;
use Yxd\Services\Models\AccountBan;
use Yxd\Services\Models\SystemSetting;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BaseService;

class UserService extends Service
{	
    const OTHER_API_URL = 'app.other_api_url';
    const MALL_API_ACCOUNT = 'app.account_api_url';
    const REDIS_V4USER = 'v4::user';
	/**
	 * 检查该手机设备是否已经被注册过
	 */
	public static function checkOnlyMobile($idfa, $mac)
	{
		if(!$idfa && !$mac){
			return false;
		}elseif($idfa){
			if(TuiguangAccount::db()->where('idfa', '=', $idfa)->count()){
				return FALSE;
			}else{
				return TRUE;
			}	
		}elseif($mac){
			if(TuiguangAccount::db()->where('mac', '=', $mac)->count() || $mac = '02:00:00:00:00:00'){
				return FALSE;
			}else{
				return TRUE;
			}
		}
		
	}
	
	/**
	 * 获取邀请人数
	 */
	public static function getInviteCount($uid)
	{
		$num = TuiguangAccount::db()->where('oldid','=', $uid)->count();
		return $num;
	}
	
	/**
	 * 根据用户的id，查询该用户是否存在
	 */
    public static function getCheckUserById($zhucema, $newid, $idfa, $mac)
	{
		$user = Account::db()->where('zhucema', $zhucema)->first();
		if(!$user){
			$params = array('zhucema'=>$zhucema);
			\Yxd\Modules\Message\NoticeService::sendInvalidInviteCode($newid,$params);
			return false;
		}
		$oldid = $user['uid'];
		$result = User::checkUserById($oldid);
		if($result){
			$ctime = mktime('0', '0', '0', date('m'), date('d'), date('Y'));
			$data=array('oldid'=>$oldid, 'newid'=>$newid, 'ctime'=>$ctime, 'idfa'=>$idfa, 'mac'=>$mac );
			$res = TuiguangAccount::db()->insert($data);
						
			$task_message = Config::get('yxd.task_message');
			if($res){
				$data2 = SystemSetting::db()->where('keyname', 'tuiguang_setting')->first();
				$data2 = unserialize($data2['data']);
				$mtime = mktime('0', '0', '0', date('m'), date('d'), date('Y'));
				$info = str_replace('[text]', $data2['newtuiguang_1'], $task_message['newtuiguang_1']);
				CreditService::handOpUserCredit($newid,$data2['newtuiguang_1'],0,'newtuiguang_1',$info);
				$_newuser = UserService::getUserInfo($newid);
				$_olduser = UserService::getUserInfo($oldid);
				$newuser = array('uid'=>$newid, 'score'=>$data2['newtuiguang_1'], 'num'=>1, 'flag'=>-1,'username'=>$_olduser['nickname']);
				Event::fire('user.new_register_score',array(array($newuser)));
				
				$num = TuiguangAccount::db()->where('oldid','=', $oldid)->count();
				
				TaskService::doTuiguang($oldid, 'oldtuiguang_1');
				$olduser = array('uid'=>$oldid, 'score'=>$data2['oldtuiguang_1'], 'num'=>$num, 'flag'=>1,'username'=>$_newuser['nickname']);
				Event::fire('user.new_register_score',array(array($olduser)));
				
				
				
				if($num>=10 && $num<100){
					TaskService::doTuiguangNum($oldid, 'oldtuiguang_10');
					$olduser = array('uid'=>$oldid, 'score'=>$data2['oldtuiguang_10'], 'num'=>10, 'flag'=>0,'username'=>$_newuser['nickname']);
					Event::fire('user.new_register_score',array(array($olduser)));
				}elseif($num>=100 && $num<500){
					TaskService::doTuiguangNum($oldid, 'oldtuiguang_100');
					$olduser = array('uid'=>$oldid, 'score'=>$data2['oldtuiguang_100'], 'num'=>100, 'flag'=>0,'username'=>$_newuser['nickname']);
					Event::fire('user.new_register_score',array(array($olduser)));
				}elseif($num>=500 && $num<1000){
					TaskService::doTuiguangNum($oldid, 'oldtuiguang_500');
					$olduser = array('uid'=>$oldid, 'score'=>$data2['oldtuiguang_500'], 'num'=>500, 'flag'=>0,'username'=>$_newuser['nickname']);
					Event::fire('user.new_register_score',array(array($olduser)));
				}elseif($num>=1000){
					TaskService::doTuiguangNum($oldid, 'oldtuiguang_1000');
					$olduser = array('uid'=>$oldid, 'score'=>$data2['oldtuiguang_1000'], 'num'=>1000, 'flag'=>0,'username'=>$_newuser['nickname']);
					Event::fire('user.new_register_score',array(array($olduser)));
				}
			}
		}
	}
	/**
	 * 
	 * @param int $uid
	 * @param string $filter 过滤器,可选值[short][basic][full]
	 */
	public static function getUserInfo($uid,$filter='basic')
	{
		return self::formatUserFields(self::filterUserFields(self::getUserInfoCache($uid),$filter));
	}

    /**
     *
     * @param array $uids
     * @param string $filter 过滤器,可选值[short][basic][full]
     * 获取一批用户的信息
     * @return array
     */
	public static function getBatchUserInfo($uids,$filter='basic')
	{
		return self::formatBatchUserFields(self::filterBatchUserFields(self::getUserInfoCacheByUids($uids),$filter));
	}
	
	/**
	 * 更新用户资料
	 */
	public static function updateUserInfo($uid,$user,$group_ids=null)
	{
		$success = User::modifyAccountInfo($uid, $user,$group_ids);
		if($success===true){
			Event::fire('user.update_userinfo_cache',array(array($uid)));
			return true;
		}
		return $success;
	}
	/**
	 * 更新用户附加信息
	 */
    public static function updateUserExtend($uid,$data)
	{
		if(empty($data)) return;
		foreach($data as $key=>$val){
			if(empty($val)){
				unset($data[$key]);
			}
		}
		if(empty($data)) return;
	    if(isset($data['idfa']) && $data['idfa']){
			$exists = Account::db()->where('idfa','=',$data['idfa'])->first();
			if(!$exists) {
				$data['is_first'] = 1;
			}else{
				$data['is_first'] = 0;
			}
		}
		$success = Account::db()->where('uid','=',$uid)->update($data);
		if($success===true){
			Event::fire('user.update_userinfo_cache',array(array($uid)));
			return true;
		}
		return $success;
	}
	
	public static function isNewUser($user)
	{
		if($user && isset($user['is_first']) && $user['is_first'] && isset($user['dateline']) && $user['dateline']){
			$time = time();
			$expire = $user['dateline']+3600*24*3;
			return $expire > $time ? 1 : 0;
		}
		return 0;
	}
	
	/**
	 * 更新用户邮箱
	 */
	public static function updateUserEmail($uid,$email)
	{
		$success = User::modifyAccountEmail($uid, $email);
		if($success){
			Event::fire('user.update_userinfo_cache',array(array($uid)));
			return true;
		}
		return $success;
	}
	
	/**
	 * 更新用户昵称
	 */
	public static function updateUserNickname($uid,$nickname)
	{
		$success = User::modifyAccountNickname($uid, $nickname);
		if($success){
			Event::fire('user.update_userinfo_cache',array(array($uid)));
			self::updateUserNicknameCache($uid, $nickname);
			return true;
		}
		return $success;
	}
	
	public static function updateUserNicknameCache($uid,$nickname)
	{
		$key = 'user:nickname:uid';
		return self::redis()->hset($key,$nickname,$uid);
	}
	
	public static function getUidByNickname($nickname)
	{
		$key = 'user:nickname:uid';
		return self::redis()->hget($key,$nickname);
	}
	
	public static function getBatchUidByNickname($nicknames)
	{
		$key = 'user:nickname:uid';
		return self::redis()->hmget($key,$nicknames);
	}
	
	public static function updateUserAvatar($uid, $avatar)
	{
		$success = User::modifyAccountAvatar($uid, $avatar);
		if($success){
			Event::fire('user.update_userinfo_cache',array(array($uid)));
			return true;
		}
		return $success;
	}
	
    public static function updateUserHomeBg($uid, $bg)
	{
		$success = User::modifyHomeBg($uid, $bg);
		if($success){
			Event::fire('user.update_userinfo_cache',array(array($uid)));
			return true;
		}
		return $success;
	}
	
	/**
	 * 屏蔽昵称或头像
	 */
    public static function shieldAccountField($uid,$field,$data)
	{
		$success = User::shieldAccountField($uid, $field, $data);
		if($success){
			Event::fire('user.update_userinfo_cache',array(array($uid)));
			if($field=='nickname'){
				self::updateUserNicknameCache($uid, $data);
			}
			return true;
		}
		return $success;
	}
	
    /**
	 * 更新用户密码
	 */
	public static function updateUserPassword($uid,$password)
	{
		$success = User::modifyAccountPassword($uid, $password);
		if($success){
			return true;
		}
		return $success;
	}
	
	public static function checkEmailVerifycode($email,$verifycode)
	{
		$info = self::dbClubMaster()->table('account_verifycode')
		->where('email','=',$email)
		->where('verifycode','=',$verifycode)
		->where('is_send_msg','=',1)
		->where('is_valid','=',1)
		->first();
		
		return $info ? $info['uid'] : 0;
	}
	
	public static function updateAccountVerifyCode($uid,$data)
	{
		return self::dbClubMaster()->table('account_verifycode')->where('uid','=',$uid)->update($data);
	}
	
	/**
	 * 更新Redis中的用户信息
	 */
	public static function updateUserInfoCache($uid)
	{
		if(Config::get('app.close_redis_user',true)===true){
			return ;
		}else{
			$info = User::getUserFullInfo($uid);
			$info && $info = self::filterUserFields($info,'full');
			$prefix = substr($uid,-1);
			$key = 'user:table-' . $prefix . ':info';
			$field = $uid;
			$value = json_encode($info);
			
			self::redis()->hset($key,$field,$value);
		}
	}
	
	/**
	 * 批量更新Redis中用户信息
	 */
	public static function updateBatchUserInfoCache($users)
	{
		if(Config::get('app.close_redis_user',true)===true){
			return ;
		}else{
			$tables = array();
			foreach($users as $user){
				$prefix =substr($user['uid'],-1);
				$key = 'user:table-' . $prefix . ':info';
				//过滤取出用户的信息
				$user = self::filterUserFields($user,'full');
				//把数组转化为json格式存入数组中
				$tables[$key][$user['uid']] = json_encode($user);			
			}
			//$tables类似于array($key=>array($uid=>$user1, $uid=>$user2)),  $data类似于array($uid=>$user1, $uid=>$user2)
			foreach($tables as $table=>$data){
				self::redis()->hmset($table,$data);
		    }
		}
	}	
	
	/**
	 * 获取Redis中的用户信息
	 * 直接从缓存中取用户信息，如果缓存中没有，则查询数据库，取出用户的信息，取出后放入缓存，然后再从缓存中取出数据
	 */
	public static function getUserInfoCache($uid)
	{
		if(Config::get('app.close_redis_user',true)===true){
			$user = User::getUserFullInfo($uid);
		}else{
			
			$prefix = substr($uid,-1);
			$table = 'user:table-' . $prefix . ':info';
			//从redis缓存中获取用户的详细信息
			$user = self::redis()->hget($table,$uid);
			if($user){
				$user = json_decode($user,true);
			}else{
				//读库
				$user = User::getUserFullInfo($uid);
				if($user){
				    self::updateUserInfoCache($uid);
				    $user = self::getUserInfoCache($uid);
				}
			}
		}
		return self::formatUserFields($user);
	}
	
	/**
	 * 获取Redis中的用户信息
	 * array_unique() 函数移除数组中的重复的值，并返回结果数组。
                 当几个数组元素的值相等时，只保留第一个元素，其他的元素被删除。
                返回的数组中键名不变。
      $a=array("a"=>"Cat","b"=>"Dog","c"=>"Cat");
      print_r(array_unique($a));
      Array ( [a] => Cat [b] => Dog )
      
      array_merge() 函数把两个或多个数组合并为一个数组。
              如果键名有重复，该键的键值为最后一个键名对应的值（后面的覆盖前面的）。如果数组是数字索引的，则键名会以连续方式重新索引。
      $a1=array("a"=>"Horse","b"=>"Dog");
	  $a2=array("c"=>"Cow","b"=>"Cat");
	  print_r(array_merge($a1,$a2));
	  
	  $a=array(3=>"Horse",4=>"Dog");
      print_r(array_merge($a));
      Array ( [0] => Horse [1] => Dog )
      array_map() 函数返回用户自定义函数作用后的数组。回调函数接受的参数数目应该和传递给 array_map() 函数的数组数目一致。
        function myfunction($v) 
		{
		if ($v==="Dog")
			{
			return "Fido";
			}
		return $v;
		}
		$a=array("Horse","Dog","Cat");
		print_r(array_map("myfunction",$a));
		Array ( [0] => Horse [1] => Fido [2] => Cat )
	 */
	public static function getUserInfoCacheByUids($uids)
	{		
		if(empty($uids)) return array();
		$uids = array_unique($uids);
		$out = array();
		if(Config::get('app.close_redis_user',true)===true){
			$users = User::getUserFullInfoList($uids);
			foreach($users as &$user){
				if(!$user['nickname']) $user['nickname'] = '玩家'.$user['uid'];
				$out[$user['uid']] = $user;
			}
		}else{
			$tables = array();
			if(!is_array($uids)){
				$uids = array($uids);
			}
			if(empty($uids)) return array();
			/*
			 * 把$uids数组转化为redis缓存中存储的表名和在表中存储的数据的键名
			 */
		    foreach($uids as $uid){
		    	//取出uid的最后一位
				$prefix = substr($uid,-1);
				//$key是redis缓存中对应的表名，$uid是在这张表上存储的数据的键名
				$key = 'user:table-' . $prefix . ':info';
				$tables[$key][] = $uid;
			}
			
			$users = array();
			//取出缓存在redis服务器上的用户信息
			foreach($tables as $table=>$fields){
				$users = array_merge($users,self::redis()->hmget($table,$fields));
			}
			//去重去除重复的数据
			$users = array_unique($users);
			//解码把json数据转化为数组返回，键名保持不变，此时$users是一个二维数组 ，其中$info是数组$users中的值
			$users = array_map(function($info){return json_decode($info,true);},$users);
			/*
			 * 把$users中的空数据给过滤掉
			 */
			$ex_uids = array();
			foreach($users as $k=>&$row){
				if($row){
					$ex_uids[] = $row['uid'];
				}else{
					unset($users[$k]);
				}
			}
			/*
			 * 如果经过过滤的数据和过滤前的数据不相等，就执行下面的代码
			 * 如果缓存取出的用户的信息比缓存中获取的$uids对应的用户少，则计算出少那些用户，再根据少取的用户id在数据库中找到这些数据，再把这些数据加入到缓存中
			 */
			if(count($uids)!== count($ex_uids)){
				$no_uids = array_diff($uids,$ex_uids);
				//读数据库 ,取出缓存中缺少的用户信息
				$no_uids && $no_users = User::getUserFullInfoList($no_uids);
				self::updateBatchUserInfoCache($no_users);
				//将缓存中缺少的用户信息和缓存中存在的用户信息合并在一起
				$users = array_merge($users,self::filterUserFields($no_users,'full'));			
			}
			//$out = array(); 放这里会引起报错（当缓存不存在时）
			foreach($users as &$user){
				if(!$user['nickname']) $user['nickname'] = '玩家'.$user['uid'];
				//用用户的uid作为数组的键名来重新重组数组
				$out[$user['uid']] = $user;
			}
			//销毁$users这个数组
			unset($users);
		}
		return self::formatBatchUserFields($out);
	}
	
	
	
	/**
	 * 处理用户积分
	 * @param number $uid 用户唯一标识
	 * @param string $action 动作
	 */
	public static function doUserCredit($uid,$action)
	{
		return CreditService::doUserCredit($uid, $action);
	}
	
	
	/**
	 * 过滤用户隐私信息
	 * @param array $user 用户信息
	 * @param string|array 过滤器,默认值:short
	 * 根据不同的需求显示用户字段的信息不同
	 */
	public static function filterUserFields($user,$filter='short')
	{
		if(!$user) return $user;
		//默认的fields的字段列表是全部的字段
		$fields = array(
		    'uid','nickname','avatar',
		    'email','mobile','sex','birthday','summary','homebg','score','experience','dateline','reg_ip','is_first',
		    'apple_token','idfa','mac','openudid','osversion',
		    'groups','authorize_nodes','province','city','region','address','alipay_num','alipay_name'
		);
		
		if(is_string($filter)){
			if($filter === 'short'){
				$fields = array('uid','nickname','avatar');
			}elseif($filter === 'basic'){
				$fields = array(
				    'uid','nickname','avatar','summary','homebg','sex',
				    'score','experience','dateline','reg_ip','is_first',
				    'apple_token','idfa','mac','openudid','osversion',
				    'groups','address','alipay_num','alipay_name'
				);
			}
		}		
		$out = array();
		//检测获取到的用户的字段是否在$fields中，如果存在的话，把这个字段存入$out数组中，然后销毁$user数组，返回$out这个数组
		foreach($user as $field=>$value){
			if(in_array($field,$fields)){
				$out[$field] = $value;
			}
		}
		unset($user);
		return $out;
	}
	
	/**
	 * 批量过滤用户隐私信息
	 * @param array $users 多个用户信息列表
	 * @param string|array 过滤器,默认值:short
	 */
	public static function filterBatchUserFields($users,$filter='short')
	{
		$out = array();
		foreach($users as $key=>$user){
			$out[$key] = self::filterUserFields($user,$filter);
		}
		return $out;
	}
	
	/**
	 * 格式化用户信息 
	 * is_readable($file)判断文件是否是可读文件
	 * file_exists检查一个文件是否存在
	 * 1.如果用户的头像不存在,就返回一个默认图片，如果用户的头像存在，就把这个图片替换为一个120px大小的图片，
	 * 如果$user['experience']存在
	 * {http://img1.gtimg.com/news/pics/hv1/71/64/1666/108348041.jpg
	 * 2.$levels获取用户的级别信息，$levels是二维数组，每一个元素就是一行数据
	 * 3.根据$levels中的start和end信息确定user['level_name']的称号
	 * 4.如果user['level_name']不存在，默认给他一个游戏菜鸟的称号
	 * }
	 * 5.如果$user['experience']不存在但是$user为真
	 * {
	 * 	    $user['level_name'] = $levels[0]['name'];
			$user['level_icon'] = '/userdirs/common/level/' . $levels[0]['img'];
			$user['score'] = 0;
			$user['experience'] = 0;
	 * }
	 * 都会获得一个默认值
	 */
	public static function formatUserFields($user)
	{
		
		//如果存在用户的头像这个变量并且该变量为空
		if(isset($user['avatar']) && empty($user['avatar'])){
			$user['avatar'] = '/userdirs/common/avatar@2x.png?v=10';
		}elseif(isset($user['avatar']) && !empty($user['avatar'])){
			$file = storage_path() . $user['avatar'];
			if(file_exists($file) && is_readable($file)){
				$avatar = str_replace('.','_120.',$user['avatar']);
				$small = storage_path() . $avatar;
				if(file_exists($small) && is_readable($small)){
					$user['avatar'] = $avatar;
				}
			}
		}
		$levels = User::getCreditLevel();
		if(isset($user['experience'])){		
			foreach($levels as $level){
				if($user['experience'] >= $level['start'] && $user['experience']<=$level['end']){
					$user['level_name'] = $level['name'];
					$user['level_icon'] = '/userdirs/common/level/' . $level['img'];
					//跳出循环，执行下面的代码
					break;
				}
			}
			if(!isset($user['level_name'])){
				$user['level_name'] = $levels[0]['name'];
			    $user['level_icon'] = '/userdirs/common/level/' . $levels[0]['img'];
			}
		}
		elseif($user){
			$user['level_name'] = $levels[0]['name'];
			$user['level_icon'] = '/userdirs/common/level/' . $levels[0]['img'];
			$user['score'] = 0;
			$user['experience'] = 0;
		}
		/*
				$a=array("a"=>"Cat","b"=>"Dog","c"=>"Horse");
				print_r(array_values($a));
				输出：
				Array ( [0] => Cat [1] => Dog [2] => Horse )
				group_id==1 管理员
				group_id==2总编
				group_id==3编辑
				group_id==4撰稿人
				group_id==5普通用户
				group_id==6vip用户
				group_id==7企业用户
				group_id==8禁言用户
				intval($group['group_id'])!=5如果用户不是普通用户的就给
				$user['level_name']  $user['level_icon']赋一个默认值
				$user['groups']是一个二维数组
				'groups' => 
					    array
					      3 => 
					        array
					          'group_id' => string '3' (length=1)
					          'group_name' => string '编辑' (length=6)
					          'ctime' => null
					          'group_icon' => string 'level9@2x.png?v=4' (length=17)
					          'group_type' => string '0' (length=1)
					          'yxd_name' => string 'public' (length=6)
					          'authorize_nodes' => string '' (length=0)
			    ，经过array_values($user['group'])处理后变成一个索引二维数组
			    'groups' => 
					    array
					      0 => 
					        array
					          'group_id' => string '3' (length=1)
					          'group_name' => string '编辑' (length=6)
					          'ctime' => null
					          'group_icon' => string 'level9@2x.png?v=4' (length=17)
					          'group_type' => string '0' (length=1)
					          'yxd_name' => string 'public' (length=6)
					          'authorize_nodes' => string '' (length=0)
		 */
		if(isset($user['groups']) && is_array($user['groups'])){
			$group = array_values($user['groups']);
			$group = isset($group[0]) ? $group[0] : null;
			if($group){
				if(intval($group['group_id'])!=5){
					$user['level_name'] = $group['group_name'];
					$user['level_icon'] = '/userdirs/common/level/' . $group['group_icon'];
				}				
			}
						
		}
		
		return $user;
	}
	
	/**
	 * 获取用户当前等级信息
	 * 根据$experience的经验值来判断用户的级别
	 * 通过遍历用户级别表，通过对比经验值来判断该用户属于那个级别的
	 */
	public static function getLevelInfo($uid,$experience)
	{
		$levels = User::getCreditLevel();
	    foreach($levels as $level){
			if($experience >= $level['start'] && $experience <= $level['end']){
				return $level;
			}
		}
		return $levels[0];
	}
	
	/**
	 * 批量格式化用户信息
	 */
	public static function formatBatchUserFields($users)
	{
		$out = array();
		foreach($users as $key=>$user){
			$out[$key] = self::formatUserFields($user);
		}
		return $out;
	}
	/**
	 * 获取用户主页设置
	 */
	public static function getUserPage($uid)
	{
		$page = User::getUserPage($uid);
		
		return $page;
	}	
	
	/**
	 * 获取用户实时游币
	 */
	public static function getUserRealTimeCredit($uid,$field=null)
	{
	    //迁移后游币获取
	    if ($field == 'score') {
	        $params = array('accountId'=>$uid,'platform'=>'ios');
	        $params_ = array('accountId','platform');
	        $new = Utility::preParamsOrCurlProcess($params,$params_,Config::get(self::MALL_API_ACCOUNT).'account/query');
	        if ($new['result']) {
	            return isset($new['result'][0]['balance']) ? $new['result'][0]['balance'] : 0;
	        }
	    }
	    $credit = CreditAccount::db()->where('uid','=',$uid)->first();
	    if($field && in_array($field,array('score','experience'))){
	        return $credit ? $credit[$field] : 0;
	    }else{
	        return $credit ? $credit : array('score'=>0,'experience'=>0);
	    }
	}
	
	/**
	 * 检查用户是否被禁言
	 */
	public static function checkUserBan($uid)
	{
		$ban = AccountBan::db()->where('uid','=',$uid)->where('type','=',1)->first();
		if(!$ban) return false;
		$expired = (int)$ban['expired'];
		if($expired==0) return true;
		if($expired>=time()) return true;
		return false;
	}
	
	/**
	 * 检查用户发帖是否过快
	 */
	public static function checkUserSpeed($uid,$reset=false)
	{
		$key = 'user::' . $uid . '::postspeed';
		$time = time();
		//if($reset===true) self::redis()->setex($key,30,$time);
		//$last = (int)self::redis()->get($key);
		if($reset===true) CacheService::put($key,$time,30);
		$last = (int) CacheService::get($key,0);
		if(($time-$last)>10){
			return false;
		}else{
			return true;
		}
	}
	
	public static function getUserAppleIdentify($uid)
	{
		$user = Account::db()->select('uid','idfa','mac','openudid')->where('uid','=',$uid)->first();
		if(!$user) return false;
		if(isset($user['idfa']) && !empty($user['idfa'])){
			return $user['idfa'];
		}elseif(isset($user['mac']) && !empty($user['mac']) && $user['mac'] != '02:00:00:00:00:00'){
			return $user['mac'];
		}
		return false;
	}
	
	public static function getUserAppleIdentifyBy($uid,$field='')
	{
		$user = Account::db()->select('uid','idfa','mac','openudid')->where('uid','=',$uid)->first();
		if(!$user) return false;
		if($field == 'idfa' && isset($user['idfa']) && !empty($user['idfa'])){
			return $user['idfa'];
		}elseif( $field=='mac' && isset($user['mac']) && !empty($user['mac']) && $user['mac'] != '02:00:00:00:00:00'){
			return $user['mac'];
		}elseif(empty($field)){
			return $user;
		}
		return false;
	}
	
	public static function getAppleIdentifyByUids($uids)
	{
		if(!$uids) return array();
		$users = Account::db()->select('uid','idfa','mac','openudid')->whereIn('uid',$uids)->get();
		$out = array();
		foreach($users as $user){
			if($user['idfa']){
				$out[$user['uid']] = $user['idfa'];
			}elseif($user['mac'] != '02:00:00:00:00:00'){
				$out[$user['uid']] = $user['mac'];
			}
		}
		return $out;
	}

	public static function getTokenList($uids,$all=false)
	{
		if(!$uids && $all==false) return array();
		$tb = Account::db()->where('apple_token','!=','')->distinct()->select('apple_token','uid');
		if($uids){
			$tb = $tb->whereIn('uid',$uids);
		}
		return $tb->lists('apple_token','uid');
	}
	
	public static function del_user_pic($uid,$type="true")
	{
	    $params = array(
	        'uid'=>$uid,
	        'isActive' => $type
	    );
	    $result = Utility::loadByHttp(Config::get(self::OTHER_API_URL).'relevance/del_game_file',$params,'POST');
	    if($result['errorCode']==0){
	        return true;
	    }
	    return false;
	}
}