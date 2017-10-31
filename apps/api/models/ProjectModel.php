<?php
use Illuminate\Support\Facades\DB;

class ProjectModel
{
	public static function getList()
	{
		return DB::connection('doc')->table('doc_project')->orderBy('ctime','desc')->get();
	}
	
	public static function getInfo($id)
	{
		return DB::connection('doc')->table('doc_project')->where('id','=',$id)->first();
	}
	
	public static function save($data)
	{
		
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);			
			DB::connection('doc')->table('doc_project')->where('id','=',$id)->update($data);
			return $id;
		}
		$data['ctime'] = time();
		return DB::connection('doc')->table('doc_project')->insertGetId($data);
	}
	
	public static function delete($id)
	{
		return DB::connection('doc')->table('doc_project')->where('id','=',$id)->delete();
	}
}