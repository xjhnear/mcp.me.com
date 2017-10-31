<?php
namespace Yxd\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Yxd\Modules\Core\BaseService;

class AndroidService extends BaseService
{
	public static function sync($cmd)
	{
		switch($cmd){
			case 'addgame':
				self::addGame();
				break;
			default:break;
		}
	} 
	
	protected static function addGame()
	{
		$gamelist = self::dbCmsSlave()->table('android_app_info')->where()->orderBy('id','asc')->get();
		$data = array();
		foreach($gamelist as $row){
		    $gname = $row['APP_NAME'];
		    $exists = self::dbCmsSlave()->table('games_apk')->where('shortgname','=',$gname)->first();
		    if($exists){
		    	
		    }else{
			    $game = array();
			    $game['gname'] = $row['APP_NAME'];
			    $game['shortgname'] = $row['APP_NAME'];
			    $game['pricetype'] = 1;
			    $game['type'] = 3;
			    $game['version'] = $row['APK_VERSION'];
			    $game['size'] = $row['APK_SIZE'];
			    $game['language'] = 1;
			    $game['score'] = 0;
			    $game['apkurl'] = $row['APK_DOWNLOAD_URL'];
			    $game['platform'] = $row['APK_CONFIGURE'];
			    $game['company'] = $row['APK_DEVELOPER'];
			    $game['editorcomt'] = '';
			    $game['ico'] = $row['APK_ICON'];
			    $game['addtime'] = time();
			    $game['isdel'] = -1;
			    $data[] = $game;
			    //$gid = self::dbCmsMaster()->table('games_apk')->insertGetId($data);
			    //APK_PACKAGENAME
		    }
		}
		$data && self::dbCmsMaster()->table('games_apk')->insert($data);
		
	}
}