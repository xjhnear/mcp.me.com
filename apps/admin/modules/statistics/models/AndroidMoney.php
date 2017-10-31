<?php
namespace modules\statistics\models;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class AndroidMoney
{
    /**
     * 购买
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumBuy($start_date,$end_date)
    {
        $mall = DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Desc','like','%购买%')
            //->where('Operation_Type','=','reward_task')
            ->sum('Balance_Change');


        return $mall;
    }

    public static function sumGift($start_date,$end_date)
    {
        $gift = DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Desc','like','%兑换%')
            //->where('Operation_Type','=','reward_task')
            ->sum('Balance_Change');
        return $gift;
    }

    /**
     * 任务
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumTask($start_date,$end_date)
    {
        //$a = self::sumTaskRunning($start_date,$end_date);
        //$b = self::sumTaskScreenshop($start_date,$end_date);
        //$c = self::sumTaskCheckinsRunning($start_date,$end_date);
        //$d = self::sumTaskCheckinsCumulative($start_date,$end_date);
        //return $a+$b+$c+$d;
        return DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Type','=','task_reward')
            //->where('Operation_Type','=','reward_task')
            ->sum('Balance_Change');

    }

    /**
     * 任务-试玩
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumTaskRunning($start_date,$end_date)
    {
        return DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Desc','like','%试玩')
            //->where('Operation_Type','=','reward_task')
            ->sum('Balance_Change');
    }

    /**
     * 任务-截图
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumTaskScreenshop($start_date,$end_date)
    {
        return DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Desc','like','%截图')
            //->where('Operation_Type','=','reward_task')
            ->sum('Balance_Change');
    }

    /**
     * 连续签到
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumTaskCheckinsRunning($start_date,$end_date)
    {
        return DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Desc','like','%任务奖励:完成连续签到%')
            //->where('Operation_Type','=','reward_checkins')
            ->sum('Balance_Change');
    }

    /**
     * 累计签到
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumTaskCheckinsCumulative($start_date,$end_date)
    {
        return DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Desc','like','%任务奖励:完成累计签到%')
            //->where('Operation_Type','=','reward_checkins')
            ->sum('Balance_Change');
    }

    /**
     * 每日签到
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumCheckins($start_date,$end_date)
    {
        return DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Desc','like','%任务奖励:每日签到%')
            //->where('Operation_Type','=','reward_checkins')
            ->sum('Balance_Change');
    }

    /**
     * 发放
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumManage($start_date,$end_date)
    {
        return DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            ->where('Operation_Type','=','manage')
            ->sum('Balance_Change');
    }

    /**
     * 分享
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumShare($start_date,$end_date)
    {
        return DB::connection('module_account')->table('ACCOUNT_OPERATION')
            ->where('Operation_Time','>',$start_date)
            ->where('Operation_Time','<',$end_date)
            //->where('Operation_Type','=','reward_task')
            ->where('Operation_Desc','like','%分享%')
            ->sum('Balance_Change');
    }

    /**
     * 商城
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public static function sumShop($start_date,$end_date)
    {
        return DB::connection('module_mall')->table('PRODUCT_LIST')
            ->where('On_Time','>',$start_date)
            ->where('On_Time','<',$end_date)
            ->sum(DB::raw('Product_Game_Price*(Product_Stock+Product_UsedStock)'));
    }
}