<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GameTag extends Command
{
	protected $name = 'command:game-tag';
	
	protected $description = 'This is GameTag command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
//        for($i=1;$i<=5;$i++){
//            echo '~~~~~~~'.$i.'~~~~~~~~'."\n";
//            \Youxiduo\Helper\Utility::loadByHttp('http://http://share.youxiduo.dev/baidu/game-tag');
//        }

        $games = \Youxiduo\Android\Model\Reserve::db()->groupBy('game_id')->orderBy('game_id','desc')->get();
        echo count($games)."\n";
        foreach($games as $k=>$row){
            if($k<=385) continue;
            echo '循环次数: '.($k+1).'~~~~~'."\n";
            self::writeErrorLog('循环次数: '.($k+1)."\n");
            $users = \Youxiduo\Android\Model\Reserve::db()->where('game_id',$row['game_id'])->get();
            if($users){
                foreach($users as $item){
                    $device_info = \Youxiduo\Android\Model\UserDevice::getNewestInfoByUid($item['uid']);
                    if(!$device_info){
                        echo "用户UID ".$item['uid']."----- 无设备记录\n";
                        self::writeErrorLog("用户UID ".$item['uid']."----- 无设备记录\n");
                        continue;
                    }
                    $tag_name = Config::get('yxd.baidu_tags.reserve_giftbag').$row['game_id'];
                    echo '用户UID '.$item['uid']."\n".'百度标签 '."\n".$tag_name."\n".$device_info['device_id']."\n";
                    self::writeErrorLog('用户UID '.$item['uid']."\n".'百度标签 '."\n".$tag_name."\n".$device_info['device_id']."\n");
                    $res = \Youxiduo\Android\BaiduPushService::setTag($tag_name,$device_info['device_id']);
                    if($res){
                        self::writeErrorLog("成功\n");
                    }else{
                        self::writeErrorLog("失败\n");
                    }
                }
            }
            echo "-------------------------------\n";
            self::writeErrorLog("--------------------------\n");
            sleep(2);
        }
	}

    protected static function writeErrorLog($message)
	{
		$log_doc = storage_path() . '/logs/';
		$file_suffix = date('Y-m-d',time());
		$log_file = $log_doc.'log_info_'.$file_suffix.'.txt';
		if(!file_exists($log_file)){ //检测log.txt是否存在
			touch($log_file);
			chmod($log_file, 0777);
		}
		$message = $message;
		@file_put_contents($log_file,$message."\r\n",FILE_APPEND);
	}
}
