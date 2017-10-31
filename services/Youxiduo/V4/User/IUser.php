<?php
/**
 * @package Youxiduo
 * @category Base 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\V4\User;

interface IUser
{
	/**
	 * 邮箱注册
	 * @param string $email 邮箱
	 * @param string $password 密码
	 * 
	 * @return int 成功返回用户的UID,失败返回
	 */
	public static function createUserByEmail($email,$password,$params=array()){}
	
	/**
	 * 手机注册
	 * @param string $mobile 手机
	 * @param string $password 密码
	 * 
	 * @return int 成功返回用户的UID,失败返回
	 */
	public static function createUserByMobile($mobile,$password,$params=array()){}
	
	/**
	 * 第三方注册
	 * @param int $uid
	 * @param int $type
	 * $param array $third
	 * 
	 * @return bool 成功返回true,否则返回false
	 */
	public static function createUserByThird($uid,$type,array $third,$params=array()){}
	
	/**
	 * 通过UID登录
	 * @param int $uid 
	 * @param string $password
	 * 
	 * @return int $result 成功返回用户的UID,失败返回
	 */
	public static function loginByUid($uid,$password){}
	
	/**
	 * 通过邮箱登录
	 * 
	 * @param string $email 
	 * @param string $password
	 * 
	 * @return int $result 成功返回用户的UID,失败返回
	 */
	public static function loginByEmail($email,$password){}
	
	/**
	 * 通过手机登录
	 * 
	 * @param string $mobile 
	 * @param string $password
	 * 
	 * @return int $result 成功返回用户的UID,失败返回
	 */
	public static function loginByMobile($mobile,$password){}
	
	/**
	 * 通过第三方登录
	 * 
	 * @param string $third_userid 
	 * @param string $password
	 * 
	 * @return int $result 成功返回用户的UID,失败返回
	 */
	public static function loginByThird(){}
	
	/**
	 * 通过UID获取用户信息
	 * 
	 * @param int $uid
	 */
	public static function getUserInfoByUid($uid){}
	
	/**
	 * 通过邮箱获取用户信息
	 * 
	 * @param string $email 邮箱
	 */
	public static function getUserInfoByEmail($email){}
	
	/**
	 * 通过手机获取用户信息
	 * 
	 * @param string $mobile 手机
	 */
	public static function getUserInfoByMobile($mobile){}
	
	/**
	 * 通过昵称获取用户信息
	 * 
	 * @param string $nickname 昵称
	 */
	public static function getUserInfoByNickname($nickname){}
	
	/**
	 * 通过UID获取多个用户信息
	 * 
	 * @param array $uids
	 */
	public static function getMultiUserInfoByUids(array $uids){}
	
	/**
	 * 修改用户密码
	 * 
	 * @param int $uid
	 * @param string $password
	 * 
	 */
	public static function modifyUserPassword($uid,$password){}
	
	/**
	 * 修改用户头像
	 * 
	 * @param int $uid
	 * @param string $avatar
	 */
	public static function modifyUserAvatar($uid,$avatar){}
	
	/**
	 * 修改用户资料
	 * 
	 * @param int $uid
	 * @param array $info
	 */
	public static function modifyUserInfo($uid,$info){}
	
	/**
	 * 修改用户手机
	 * 
	 * @param int $uid
	 * @param string $mobile
	 */
	public static function modifyUserMobile($uid,$mobile){}
	
	/**
	 * 修改用户邮箱
	 * 
	 * @param int $uid
	 * @param string $email
	 * 
	 */
	public static function modifyUserEmail($uid,$email){}
	
	/**
	 * 检查邮箱是否存在
	 * 
	 * @param string $email 邮箱
	 * @param int $uid 排除比较的用户UID
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function isExistsByEmail($email,$uid=0){}
	
	/**
	 * 检查手机是否存在
	 * 
	 * @param string $mobile 手机
	 * @param int $uid 排除比较的用户UID
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function isExistsByMobile($mobile,$uid=0){}
	
	/**
	 * 检查昵称是否存在
	 * 
	 * @param string $nickname 昵称
	 * @param int $uid 排除比较的用户UID
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function isExistsByNickname($nickname,$uid){}
	
	/**
	 * 发送短信验证码
	 * 
	 * @param string $mobile 手机
	 * @param string $code 验证码
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function sendVerifyCodeByMobile($mobile,$code){}
	
	/**
	 * 发送邮箱验证码
	 * 
	 * @param string $email 邮箱
	 * @param string $code 验证码
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function sendVerifyCodeByEmail($email,$code){}

	/**
	 * 检查手机验证码
	 * 
	 * @param string $mobile 手机
	 * @param string $code 验证码
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function checkVerifyCodeByMobile($mobile,$code){}
	
    /**
	 * 检查邮箱验证码
	 * 
	 * @param string $email 邮箱
	 * @param string $code 验证码
	 * 
	 * @return bool 存在返回true,否则返回false
	 */
	public static function checkVerifyCodeByEmail($email,$code){}
	
    /**
	 * 记录用户设备信息
	 * @param int $uid 用户UID
	 * @param array $gps 用户的设备信息
	 * @return bool $result 成功返回true,失败返回false
	 */
	public static function recordUserDevice($uid,array $device){}
	
    /**
	 * 记录用户GPS信息
	 * @param int $uid 用户UID
	 * @param array $gps 用户的GPS信息
	 * @return bool $result 成功返回true,失败返回false
	 */
	public static function recordUserGPS($uid,array $gps){}
	
    /**
	 * 获取用户设备信息
	 * @param int $uid 用户UID
	 * @return array $result 返回用户最后记录的设备信息
	 */
	public static function getUserDevice($uid){}
	
    /**
	 * 获取用户GPS信息
	 * @param int $uid 用户UID
	 * @return array $result 返回用户最后记录的GPS信息
	 */
	public static function getUserGPS($uid){}
	
    /**
	 * 生成用户邀请码
	 */
	public static function makeInviteCode(){}
	
	/**
	 * 获取用户邀请码
	 * @param int $uid 用户UID
	 * 
	 * @return string $invitecode 用户的邀请码
	 */
	public static function getUserInviteCode($uid){}
	
	/**
	 * 搜索用户
	 * 
	 * @param array $search 搜索条件
	 * @param int $pageIndex 分页页码
	 * @param int $pageSize 分页大小
	 * @param array $order 排序
	 * 
	 * @return null|array 返回搜索到的用户数组,没有数据返回NULL
	 * 
	 */
	public static function searchUser(array $search,$pageIndex=1,$pageSize=10,$order=array()){}
	
	/**
	 * 检查用户是否禁言
	 * @param int $uid 用户UID
	 * 
	 * return bool $result 已被禁言返回true,否则返回false
	 */
	public static function checkUserIsBan($uid){}
	
	/**
	 * 屏蔽用户某个属性
	 * @param int $uid 用户UID
	 * @param string $field 屏蔽字段
	 * @param string $value 替换的值
	 */
	public static function shieldUserField($uid,$field,$value){}
	
	/**
	 * 禁言某个用户
	 * @param int $uid 用户UID
	 * @param int $expire 禁言时间,单位(分钟)
	 * 
	 * @return bool $result 成功返回true,否则返回false
	 */
	public static function banUser($uid,$expire=30){}
}