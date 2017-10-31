<?php
namespace modules\system\models;
use Yxd\Modules\Core\BaseModel;

class AuthorizeModel extends BaseModel
{
    public static function getNodeList($page=1,$pagesize=10)
	{
		return self::dbClubMaster()->table('authorize_node')->orderBy('appname','asc')->orderBy('module','desc')->orderBy('id','asc')->forPage($page,$pagesize)->get();		
	}
	
    public static function getNodeListByModule($module)
	{		
		return self::dbClubMaster()->table('authorize_node')->where('module','=',$module)->orderBy('id','asc')->get();
	}
	
	public static function getNodeCount()
	{
		return self::dbClubMaster()->table('authorize_node')->count();		
	} 
	
	public static function getNode($id)
	{
		return self::dbClubMaster()->table('authorize_node')->where('id','=',$id)->first();
	}
	
	public static function addNode($data)
	{
		return self::dbClubMaster()->table('authorize_node')->insertGetId($data);
	}
	
    public static function updateNode($id,$data)
	{
		return self::dbClubMaster()->table('authorize_node')->where('id','=',$id)->update($data);
	}
	
    public static function deleteNode($id)
	{
		return self::dbClubMaster()->table('authorize_node')->where('id','=',$id)->delete();
	}
}