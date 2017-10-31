<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Android\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Illuminate\Support\Facades\Config;
/**
 * 游戏模型类
 */
final class GameApkDownload extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}

    public static function downloadCount($gid,$pid){
        $cur_day_time = strtotime(date('Y-m-d',time()));
        $query = self::db()->where('pid',$pid)->where('agid',$gid)->where('down_time',$cur_day_time);
        $item = $query->first();
        if($item){
            $query->increment('number');
        }else{
            $data['pid'] = $pid;
            $data['agid'] = $gid;
            $data['down_time'] = $cur_day_time;
            $data['number'] = 1;
            self::db()->insertGetId($data);
        }
    }
}