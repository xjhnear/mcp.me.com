<?php
use Illuminate\Support\Facades\DB;

class NoticeSetting
{
	public static function getNoticeSettingList()
	{
		return DB::table('notice_setting')->orderBy('id','asc')->get();
	}
	
	public static function getNoticeSettingInfo($id)
	{
		return DB::table('notice_setting')->where('id','=',$id)->first();
	}
	
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			return DB::table('notice_setting')->where('id','=',$id)->update($data);
		}else{
			return DB::table('notice_setting')->insertGetId($data);
		}
	}
}