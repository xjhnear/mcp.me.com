<?php

namespace modules\adv\controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use modules\adv\models\LocationModel;
use libraries\Helpers;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class LocationController extends BackendController
{
	public function _initialize()
	{	
		$this->current_module = 'adv';
		//ApptypesModel::setTable('apptypes');
		//ApptypesModel::setDatabase('yxd_advert');
	}
	//广告地址列表 location-list
	public function getList()
	{  echo 1;exit;
		$search=array();
		/***
		$search['pageIndex']=Input::get('page',1);
		$search['pageSize']=3;		
		$search['=yxd_advert_nature.is_show']='1';
		$search['=yxd_advert_location.is_show']='1';
		$where=LocationModel::setsearch($search);//添加查询条件
		$join['0']=array('jointables'=>'nature','key_1'=>'nature.Id','fuhao'=>'=','key_2'=>'nature_id');
		$select=array('location.Id','location.nature_id','location.name as lname','nature.natureName as natureName','location.createdatetime');
		$datalist=LocationModel::pagelist($search['pageIndex'],$search['pageSize'],$join,$where,$select);
		$datalist=self::processingInterface($datalist,$search,$search['pageSize']);
		****/
		//return $this->display('/apptypes/location-list',$datalist);
	}
    /****
	//广告地址添加修改视图
	public function getLocationAddEdit($id=0)
	{	
		$datainfo['datalist']['is_show']=1;
		if(empty($id)){
			return $this->display('/apptypes/location-addedit',$datainfo);
		}
		$datainfo['datainfo']=LocationModel::getInfo($id);
		$Location=LocationModel::find(" SELECT Id,natureName FROM yxd_advert_nature WHERE Id=".$datainfo['datainfo']['nature_id']."  and is_show=1 limit 1 ");
		if(!empty($Location)){
			$datainfo['datainfo']['natureName']=$Location['0']['natureName'];
		}
		return $this->display('/apptypes/location-addedit',$datainfo);
	}

	//广告应用数据
	public function getApptypesSelect()
	{	
		$search=array();
		$input=Input::get('keyword');
		if(!empty($input)){
			$search['%natureName']=$input;
		}
		RecommendModel::setTable('nature');
		$datalist=self::datalist($search,Input::get('page',1),10);
		$html = $this->html('/apptypes/pop-nature-list',$datalist);
        return $this->json(array('html'=>$html));	
	}


	//分页列表调用方法
	private static function datalist($search=array(),$page=1,$pagesize=10){
		$search['=is_show']=1;
		$datainfo['where']=LocationModel::setsearch($search);
		$datainfo['pageIndex']=$page;
		$datainfo['pageSize']=$pagesize;
		$datalist=LocationModel::pagelist($datainfo['pageIndex'],$datainfo['pageSize'],'',$datainfo['where']);
		$datalist=self::processingInterface($datalist,$search,$pagesize);
		return $datalist;
	}

	private static function addedit($datainfo=array())
	{	
		$youxiduo_admin=Session::get('youxiduo_admin');
		if(empty($datainfo['Id'])){
			$datainfo['is_show']=1;
			$datainfo['createuser']=$youxiduo_admin['id'];
			$datainfo['createdatetime']=date("Y-m-d H:i:s");
			return LocationModel::insert($datainfo);
		}
		$datainfo['modifyuser']=$youxiduo_admin['id'];
		$datainfo['modifydatetime']=date("Y-m-d H:i:s");
		return LocationModel::update($datainfo,$datainfo['Id']);
	}


	//处理接口返回数据
    private static function processingInterface($result,$data,$pagesize=10){
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        unset($data['pageIndex']);
        $pager->appends($data);
        $data['pagelinks'] = $pager->links();
        $data['datalist'] = !empty($result['result'])?$result['result']:array();
        return $data;
    }
**/

}