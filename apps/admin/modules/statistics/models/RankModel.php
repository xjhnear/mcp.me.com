<?php
namespace modules\statistics\models;

use Yxd\Modules\Core\BaseModel;
use Illuminate\Support\Facades\DB;

class RankModel extends BaseModel
{
	
	public static function getMoneyIncomeSum($start,$end)
	{
		return self::dbClubSlave()->table('account_credit_history')
		->where('mtime','>',$start)
		->where('mtime','<',$end)
		->where('credit','>',0)
		->sum('credit');
	}
	
	public static function getMoneyIncomeList($start,$end,$size=500)
	{
		$table = self::dbClubSlave()->table('account_credit_history')
			->where('mtime','>',$start)
			->where('mtime','<',$end)
			->where('credit','>',0)
			->select(DB::raw('uid,sum(credit) as income'))
			->groupBy('uid')
			->forPage(1,$size)
			->orderBy('income','desc')
			->lists('income','uid');
			
		if(!$table) return array();
		
		$uids = array_keys($table);
		$_users = self::dbClubSlave()->table('account')->whereIn('uid',$uids)->get();
		$users = array();
		foreach($_users as $row){
			$users[$row['uid']] = $row;
		}
		$moneylist = self::dbClubSlave()->table('credit_account')->whereIn('uid',$uids)->lists('score','uid');
		$out = array();
		foreach($table as $uid=>$income){
			if(!isset($users[$uid])) continue;
			$user = $users[$uid];
			$user['income'] = $income;
			$user['credit'] = isset($moneylist[$uid]) ? $moneylist[$uid] : 0;
			$out[$uid] = $user; 
		}
		return $out;
	}
	
	
    public static function getMoneyExpendSum($start,$end)
	{
		return self::dbClubSlave()->table('account_credit_history')
		->where('mtime','>',$start)
		->where('mtime','<',$end)
		->where('credit','<',0)
		->sum('credit');
	}
	
    public static function getMoneyExpendList($start,$end,$size=500)
	{
		$table = self::dbClubSlave()->table('account_credit_history')
		->where('mtime','>',$start)
		->where('mtime','<',$end)
		->where('credit','<',0)
		->select(DB::raw('uid,sum(credit) as expend'))
		->groupBy('uid')
		->forPage(1,$size)
		->orderBy('expend','asc')
		->lists('expend','uid');
		
		if(!$table) return array();
		
		$uids = array_keys($table);
		$_users = self::dbClubSlave()->table('account')->whereIn('uid',$uids)->get();
		$users = array();
		foreach($_users as $row){
			$users[$row['uid']] = $row;
		}
		$moneylist = self::dbClubSlave()->table('credit_account')->whereIn('uid',$uids)->lists('score','uid');
		$out = array();
		foreach($table as $uid=>$expend){
			$user = $users[$uid];
			$user['expend'] = $expend;
			$user['credit'] = isset($moneylist[$uid]) ? $moneylist[$uid] : 0;
			$out[$uid] = $user; 
		}
		return $out;
	}
}