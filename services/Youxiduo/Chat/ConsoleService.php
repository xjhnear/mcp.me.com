<?php
namespace Youxiduo\Chat;

use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Youxiduo\User\Model\Account;
class ConsoleService
{
	public static function registerQJ($uids)
	{
		if(!is_array($uids)) $uids = array($uids);
		$uids_str = implode(',',$uids);
		$result = Utility::loadByHttp(Config::get('app.ios_chat_api_url').'user/import_users',array('uid'=>$uids_str));
		if($result['errorCode']==0){
			return true;
		}
		return false;
		/*
		if(count($uids)>50){
			$groups = array_chunk($uids,50);
			foreach($groups as $group){
				$uids_str = implode(',',$group);
				//echo Config::get('app.ios_chat_api_url') . 'user/import_users?uid='.$uids_str;
				$result = Utility::loadByHttp(Config::get('app.ios_chat_api_url').'user/import_users',array('uid'=>$uids_str));
			}
		}else{
			$uids_str = implode(',',$uids);
		    $result = Utility::loadByHttp(Config::get('app.ios_chat_api_url').'user/import_users',array('uid'=>$uids_str));
		}
		*/
	}
	
	public static function importUsers()
	{
		$running = true;
		while($running==true){
			if(Cache::has('last_uid')){
				$last_uid = Cache::get('last_uid');
			}else{
				$last_uid = 100000;
			}
			$users = Account::db()->where('uid','>',$last_uid)->forPage(1,50)->orderBy('uid','asc')->select('uid')->get();
			$uids = array();
			foreach($users as $user){
				$uids[] = $user['uid'];
			}
			$len = count($uids);
			$res = self::registerQJ($uids);
			if($res==true){
			    Cache::forever('last_uid',$uids[$len-1]);
			}
			echo $last_uid;
			sleep(5);
		}
	}
}