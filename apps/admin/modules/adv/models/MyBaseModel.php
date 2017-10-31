<?php
namespace modules\adv\models;
use Yxd\Modules\Core\BaseModel;

class MyBaseModel extends BaseModel
{
	private  static $table='';
	private  static $database='';


	public static function setTable($table_='')
	{	
		self::$table=$table_;
	}

	public static function getTable()
	{	
		return self::$table;
	}

	public static function setDatabase($database_='')
	{
		self::$database=$database_;
	}

	public static function getDatabase()
	{
		return self::$database;
	}

	/***
		 添加数据
	***/
	public static function insert($datainfo=array())
	{
		return self::dbAdvMaster()->table(self::getTable())->insertGetId($datainfo);
	}

	/***
		更新数据
	***/
	public static function update($datainfo=array(),$id=0)
	{
		return self::dbAdvMaster()->table(self::getTable())->where('id', $id)->update($datainfo);
	}

	/**
	 * 获取单条记录
	 * @param string $id, boolean $transaction = true
	 * @return array $JsonResult
	 */
	public static function getInfo($id=0)
	{
		$datainfo=self::buildSearch()->where('Id', $id)->first();
		//->select(" SELECT * FROM ".self::$table." WHERE Id=$id Limit 1;");
		return 	$datainfo;
	}

	/**
	 * 获取多条记录
	 * @param string $id, boolean $transaction = true
	 * @return array $JsonResult
	 */
	public static function getInfos($where='',$val='')
	{	
		return 	self::buildSearch()->where($where,$val)->get();
	}

	/**
	 * 获取多条记录多WHERE条件
	 * @param string $id, boolean $transaction = true
	 * @return array $JsonResult
	 */
	public static function getInfos_($wheres=array())
	{	
		if(!empty($wheres)){
			$sql=self::buildSearch();
			foreach($wheres as $key=>$value)
			{
				$sql->where(!empty($value['0'])?$value['0']:'id',!empty($value['1'])?$value['1']:'=',!empty($value['2'])?$value['2']:'0');
			}
		}
		return 	$sql->get();
	}

	/**
	 * 查询
	 * @param string $sql,boolean $transaction = true
	 * @return array $JsonResult
	 */
	public static function find($sql='')
	{
		return 	self::dbAdvMaster()->select($sql);
	}
    


	public static function setsearch($arr=array()){
		$sql=$search=array();
		foreach($arr as $key=>$value){
			$keys=substr($key,1);
			$key_=$key{0};
			if(($key_=="=")&&($value != "" )){
				$sql[]=$keys." = '$value'";
			}
			if(($key_ =="%")&&($value != "" )){
				$sql[]=$keys." like '%%".$value."%%' ";
			}
			if(($key_ ==">")&&($value != "" )){
				$sql[]=$keys." >= '".$value."' ";
			}
			if(($key_ =="<")&&($value != "" )){
				$sql[]=$keys." <= '".$value."' ";
			}
			if(($key_=="!")&&($value != "" )){
				$sql[]=$keys." != '".$value."' ";
			}
				
		}
		return join(' and ',$sql);
	}
	/***
	*
	**/
	public static function  pagelist($page=1,$pageSize=10,$joins=array(),$where='',$select=array(),$ids=''){
		 
		  $sql=self::buildSearch();
		  $datalist=array();
		  if(!empty($joins)){
		  	 foreach($joins as $key => $value){
		  	    $sql=$sql->leftJoin($value['jointables'],$value['key_1'],$value['fuhao'], $value['key_2']);
		     }
		  }
		  if(!empty($where)){
		  		$sql=$sql->whereRaw($where);
		  }
		  $datalist['totalCount']=$sql->count();
		  if(!empty($select)){
		  	$sql=$sql->select($select);
		  }
		  if($datalist['totalCount'] != null){
		  		$datalist['result']=$sql->orderBy(self::getTable().'.Id', 'desc')->forPage($page,$pageSize)->get();
		  }
		  return $datalist;
	}
	
	protected static function buildSearch()
	{
		return self::dbAdvMaster()->table(self::getTable());
	}


}