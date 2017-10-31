<?php
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Yxd\Models\BaseModel;
/**
 * 后台管理员模型
 */
class AdminModel extends BaseModel
{	
	/**
	 * 登录验证
	 */
	public static function checkAuthorize($username,$password)
	{
		$admin = self::dbCmsMaster()->table('admin')
									->where('admin.username','=',$username)->where('admin.isopen','=',1)->first();
		if(!$admin){
			return array('status'=>500,'error_description'=>'帐号不存在或帐号已被禁用');
		}
		if($admin['password'] === md5($password)){
 			$user = self::getAdminUserInfo($admin['id']);
 			if($user){
 				$admin['uid'] = $user['uid'];
 			}else{
 				$admin['uid'] = 1;
 			}
			return array('status'=>200,'data'=>array('user'=>$admin));
		}else{
			return array('status'=>500,'error_description'=>'帐号或密码错误');
		}
		
	}
	
	public static function getInfo($id)
	{
		return self::dbCmsMaster()->table('admin')->where('id','=',$id)->first();
	}
	
	public static function getAdminUserInfo($admin_id)
	{
		$row = self::dbClubSlave()->table('admin_account')->where('admin_id','=',$admin_id)->first();
		
		return $row;
	}
	
	/**
	 * 
	 */
	public static function verifyLocal($local_login)
	{
		if(empty($local_login)) return false;
		$data = Crypt::decrypt($local_login);
		if(!$data) return false;
		list($uid,$password) = explode('@',$data);
		if(!$uid || !$password) return false;
		$admin = self::getInfo((int)$uid);
		if(!$admin) return false;
		if($admin['password'] == $password){
		    $user = self::getAdminUserInfo($admin['id']);
			if($user){
				$admin['uid'] = $user['uid'];
			}else{
				$admin['uid'] = 1;
			}
			return $admin;
		}else{
			return false;
		}
		return false;
	}
}