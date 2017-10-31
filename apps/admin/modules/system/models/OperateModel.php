<?php
namespace modules\system\models;

use Yxd\Models\BaseModel;
class OperateModel extends BaseModel{
	public static function getMonolog($name,$group,$channel,$date_arr,$page=1,$size=10){
		$query = self::dbClubMaster()->table('monolog');
		if($name) $query->where('op_name',$name);
		if($group) $query->where('op_group',$group);
		if($channel) $query->where('channel',$channel);
		if($date_arr) {
			if($date_arr['start_date']) $query->where('time','>=',$date_arr['start_date']);
			if($date_arr['end_date']) $query->where('time','<=',$date_arr['end_date']);
		}
		$query->join('group','monolog.op_group','=','group.name');
		return $query->forPage($page,$size)->orderBy('time','DESC')->get();
	}
	
	public static function getMonologCount($name,$group,$channel,$date_arr){
		$query = self::dbClubMaster()->table('monolog');
		if($name) $query->where('op_name',$name);
		if($group) $query->where('op_group',$group);
		if($channel) $query->where('channel',$channel);
		if($date_arr) {
			if($date_arr['start_date']) $query->where('time','>=',$date_arr['start_date']);
			if($date_arr['end_date']) $query->where('time','<=',$date_arr['end_date']);
		}
		return $query->count();
	}
	
	public static function getMonologById($mo_id){
		return self::dbClubMaster()->table('monolog')->where('monolog_id',$mo_id)->first();
	}
}