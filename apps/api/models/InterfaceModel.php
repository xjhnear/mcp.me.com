<?php
use Illuminate\Support\Facades\DB;

class InterfaceModel
{
	public static function getList($project_id,$tree = true)
	{
		$result = DB::connection('doc')->table('doc_interface')->where('project_id','=',$project_id)->orderBy('cate_id','asc')->orderBy('id','asc')->get();
		if($tree==true){
			$out = array();
			foreach($result as $key=>$row){
				$out[$row['cate_id']]['name']=$row['cate_name'];
				$out[$row['cate_id']]['interfaces'][]=$row;
			}
			return $out;
		}
		return $result;
	}
	
	public static function getInfo($id)
	{
		$interface = DB::connection('doc')->table('doc_interface')->where('id','=',$id)->first();
		$interface['input_params'] = !empty($interface['input_params']) ? unserialize($interface['input_params']) : array();
		$interface['out_params'] = !empty($interface['out_params']) ? unserialize($interface['out_params']) : array();
		$interface['error_params'] = !empty($interface['error_params']) ? unserialize($interface['error_params']) : array();
		return $interface;
	}
	
	public static function getCateList($project_id,$kv=true)
	{
		if($kv==true){
			return DB::connection('doc')->table('doc_category')->where('project_id','=',$project_id)->lists('name','id');
		}
		return DB::connection('doc')->table('doc_category')->where('project_id','=',$project_id)->get();
	}
	
	public static function getCateInfo($id)
	{
		return DB::connection('doc')->table('doc_category')->where('id','=',$id)->first();
	}
	
	public static function saveCategory($data)
	{
		if(isset($data['id']) && $data['id']){
			$id = $data['id'];
			unset($data['id']);
			return DB::connection('doc')->table('doc_category')->where('id','=',$id)->update($data);
		}else{
			return DB::connection('doc')->table('doc_category')->insertGetId($data);
		}
	}
	
	public static function save($data)
	{
		$data['ctime'] = time();
		$cate = self::getCateInfo($data['cate_id']);
		$data['cate_name'] = $cate['name'];
		if(isset($data['id'])&&$data['id']){
            $id = $data['id'];
            unset($data['id']);
            DB::connection('doc')->table('doc_interface')->where('id','=',$id)->update($data);
			return $id;
		}else{			
			$id = DB::connection('doc')->table('doc_interface')->insertGetId($data);
			if($id){
				DB::connection('doc')->table('doc_project')->where('id','=',$data['project_id'])->increment('interface_num',1);
			}
			return $id;
		}
	}
}