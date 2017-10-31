<?php
/**
 * @package Youxiduo
 * @category adv 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Adv;
use Youxiduo\Adv\Model\Apptypes;
use Youxiduo\Adv\Model\Appadv;
use Youxiduo\Adv\Model\Location;
use Youxiduo\Adv\Model\Nature;
use Youxiduo\Adv\Model\Recommend; 
use Youxiduo\Adv\Model\Recommendedadv;
use Youxiduo\Adv\Model\Version;
use Youxiduo\Base\BaseService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Paginator;
class AdvService extends BaseService
{	
	//Apptype列表数据获取
	public static function getApptypeList($search=array())
	{	
		return Apptypes::pagelist($search['page'],$search['pageSize'],Apptypes::setsearch($search));
	}
	//Apptype数据详情获取
	public static function getApptypeInfo($id,$key=''){
		return empty($key)?Apptypes::getInfo('Id',$id):Apptypes::getinfos($key,$id);
	}
	//查询Version表数据
	public static function FindVersion($where='')
	{	
		return Version::find($where);
	}
	//查询Recommend表数据
	public static function FindRecommend($where='')
	{
		return Recommend::find($where);
	}
	//Apptype数据添加修改
	public static function ApptypeAddEdit($datainfo=array())
	{	
		$session=Session::get('youxiduo_admin');
		$datainfo['is_show']=1;
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Apptypes::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Apptypes::update($datainfo,$datainfo['Id']);
	}
	//Version数据添加修改
    public static function VersionAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Version::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Version::update($datainfo,$datainfo['Id']);
    } 
    //========================Apptype模块所需结束 ===================================
    //========================Recommend模块所需开始 =================================       
    //Recommend列表数据获取
	public static function getRecommendList($search=array())
	{	
		return Recommend::pagelist($search['page'],$search['pageSize'],Recommend::setsearch($search));
	}

	public static function FindApptypes($where='')
	{
		return Apptypes::find($where);
	}

	public static function getRecommendInfo($id)
	{
		return empty($key)?Recommend::getInfo('Id',$id):Recommend::getinfos($key,$id);
	}
   
    public static function RecommendAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Recommend::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Recommend::update($datainfo,$datainfo['Id']);
    }
    //========================Recommend模块所需结束=================================
    //========================Nature模块所需开始===================================
    public static function  getNatureList($search=array())
	{
		return Nature::pagelist($search['page'],$search['pageSize'],Nature::setsearch($search));
	}
	//Nature数据详情获取
	public static function getNatureInfo($id,$key=''){
		return empty($key)?Nature::getInfo('Id',$id):Nature::getinfos($key,$id);
	}
	//查询Location表数据
	public static function FindLocation($where='')
	{
		return Location::find($where);
	}

	public static function NatureAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Nature::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Nature::update($datainfo,$datainfo['Id']);
    }
    //添加修改前判断是否拥有相同记录
    public static function is_Nature_name($where=array())
    {
    	return Nature::getInfos_($where);
    }

    //========================Nature模块所需结束===================================
     
    //========================recommendedadv模块所需开始==================================
    public static function getRecommendedadvList($search=array(),$where='')
    {
    	return Recommendedadv::pagelist($search['page'],$search['pageSize'],!empty($where)?$where:Appadv::setsearch($search));
    }

    //Recommendedadv数据详情获取
	public static function getRecommendedadvInfo($id,$key=''){
		return empty($key)?Recommendedadv::getInfo('Id',$id):Recommendedadv::getinfos($key,$id);
	}

	public static function RecommendedadvAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Recommendedadv::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Recommendedadv::update($datainfo,$datainfo['Id']);
    }


     //查询Appadv表数据
    public static function FindRecommendedadv($where='')
    {
        return Recommendedadv::find($where);
    }
    //========================recommendedadv模块所需结束==================================

    //========================appadv模块所需开始==================================
 	public static function getReleaseadvList($search=array(),$where='')
	{
		return Appadv::pagelist($search['page'],$search['pageSize'],!empty($where)?$where:Appadv::setsearch($search));
	}

	//Appadv数据详情获取
	public static function getAppadvInfo($id,$key=''){
		return empty($key)?Appadv::getInfo('Id',$id):Appadv::getinfos($key,$id);
	}

    //查询Appadv表数据
    public static function FindAppadv($where='')
    {
        return Appadv::find($where);
    }


    public static function ReleaseadvAddEdit($datainfo=array())
    {
        $session=Session::get('youxiduo_admin');
        
        if(empty($datainfo['Id'])){
            $datainfo['is_show']=1;
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Appadv::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
       
        return Appadv::update($datainfo,$datainfo['Id']);
    }
    //========================appadv模块所需结束===================================

    //========================Location模块所需开始==================================    
	public static function  getLocationList($search=array())
	{
		return Location::pagelist($search['page'],$search['pageSize'],Location::setsearch($search));
	}
	
	//Location数据详情获取
	public static function getLocationInfo($id,$key=''){
		return empty($key)?Location::getInfo('Id',$id):Location::getinfos($key,$id);
	}
	//查询Nature表数据
	public static function FindNature($where='')
	{
		return Nature::find($where);
	}


	public static function LocationAddEdit($datainfo=array())
    {
    	$session=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
            $datainfo['createuser']=$session['id'];
            $datainfo['createdatetime']= date('Y-m-d G:i:s');
            return Location::insert($datainfo);
        }
        $datainfo['modifyuser']=$session['id'];
        $datainfo['modifydatetime']= date('Y-m-d G:i:s');
        return Location::update($datainfo,$datainfo['Id']);
    }
    
    //========================Location模块所需结束=================================


	/**处理返回数据**/
    public static function _processingInterface($result,$data,$pagesize=10,$is_ajax=0){
        $data['search']=$data;
        $data['datalist']  = !empty($result['result'])?$result['result']:array();
        if($is_ajax == 1){
            $data['totalCount']=$result['totalCount'];
            return $data;
        } 
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        unset($data['search']['pageIndex']);
        $pager->appends($data['search']);
        $data['pagelinks'] = $pager->links();
        return $data;
    }




}
