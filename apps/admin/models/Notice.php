<?php
use Illuminate\Support\Facades\DB;

class Notice
{
	public static function searchCount($search)
	{
		$tb = self::buildSearch($search);
		return $tb->count();
	}
	
	public static function searchList($search,$page=1,$size=10)
	{
		$tb = self::buildSearch($search);
		
		return $tb->forPage($page,$size)->get();
	}
	
	public static function buildSearch($search)
	{
		$tb = DB::table('forum_notice')->leftJoin('forum','forum_notice.gid','=','forum.gid')->select('forum_notice.*','forum.name as forum_name');
	    //开始时间
		if(isset($search['startdate']) && !empty($search['startdate']))
		{
			$tb = $tb->where('dateline','>=',strtotime($search['startdate'] . ' 00:00:00'));
		}
		//截至时间
		if(isset($search['enddate']) && !empty($search['enddate']))
		{
			$tb = $tb->where('dateline','<=',strtotime($search['enddate'] . ' 23:59:59'));
		}
		
		return $tb;
	}
	
	public static function info($id)
	{
		return DB::table('forum_notice')->where('id','=',$id)->first();
	}
	
	public static function add($data)
	{
		$data['startdate'] = strtotime($data['startdate'] . ' 00:00:00');
		$data['enddate']   = strtotime($data['enddate'] . ' 23:59:59');
		
		return DB::table('forum_notice')->insertGetId($data);
	}
	
	public static function update($id,$data)
	{
		$data['startdate'] = strtotime($data['startdate'] . ' 00:00:00');
		$data['enddate']   = strtotime($data['enddate'] . ' 23:59:59');
		
		return DB::table('forum_notice')->where('id','=',$id)->update($data);
	}
	
	public static function delete($ids)
	{
		if(is_numeric($ids)){
			return DB::table('forum_notice')->where('id','=',$ids)->delete();
		}elseif(is_array($ids)){
			return DB::table('forum_notice')->whereIn('id',$ids)->delete();
		}
		return false;
	}
}