<?php
namespace modules\adv\controllers;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Youxiduo\Adv\AdvService;
use Yxd\Modules\Core\BackendController;
class ApptypesController extends BackendController
{	
	public function _initialize()
	{	
		$this->current_module = 'adv';
	}
	//列表查询
	public function getList()
	{	//apptypes
		$search=array();
		$search['page']=Input::get('page',1);
		$search['%name']=Input::get('%name');
		$search['pageSize']=15;
		$search['=is_show']='1';
		$datalist=AdvService::getApptypeList($search);
		$datalist=AdvService::_processingInterface($datalist,$search,$search['pageSize']);
		if(!empty($search['%name'])){
			$datalist['search']['name']=$search['%name'];
		}
		return $this->display('/apptypes/apptypes-list',$datalist);
	}

	//广告应用类型添加视图
	
	public function getApptypesAddEdit($id=0,$type=''){
		$datainfo['datalist']['is_show']=1;
		$datainfo['datalist']['from']=$type;
		if(empty($id)){
			return $this->display('/apptypes/apptypes-addedit',$datainfo);
		}
 
		$datainfo['datainfo']=AdvService::getApptypeInfo($id);
		$datainfo['version']= AdvService::FindVersion("apptypes_id = $id and is_show=1 and versionform = '$type' order by Id desc ");
		$datainfo['recommend']=AdvService::FindRecommend("apptypes_id = $id and is_show=1 order by Id desc ");
		if(!empty($datainfo['recommend'])){
			foreach($datainfo['recommend'] as $key=>$value){
				$name[]=$value['name'];
			}
			$datainfo['recommend']=join(',',$name);
		}
		$datainfo['setversion']=!empty($datainfo['version'])?1:0;
		return $this->display('/apptypes/apptypes-addedit',$datainfo);
	}
	
	//广告应用类型添加
	public function postApptypesAddEdit(){
		$input = Input::only('name','Id','versionform');
        if(empty($input['Id'])){
        	$datainfo['info_name']=AdvService::getApptypeInfo($input['name'],'name');
	        if(!empty($datainfo['info_name'])){
	        		foreach($datainfo['info_name'] as $val){
	        			if($val['is_show'] == 1){
	        					return $this->back()->with('global_tips','不能重复添加');
	        			}
	        		}
			}
        }
        $biTian=array('name'=>'required');
        $message = array(
            'required' => '不能为空',
            //'date' => '必须为日期',
        );
        $validator = Validator::make($input,$biTian,$message);
        if ($validator->fails()){
            $messages = $validator->messages();
           	foreach ($messages->all() as $key=>$message)
            {	
            	$strerror[]=$message;
            }
            return $this->back()->with('global_tips',join('-',array_flip(array_flip($strerror))));
        }
		$id=AdvService::ApptypeAddEdit($input);
		if(!empty($id)){
			return $this->redirect('adv/apptypes/list')->with('global_tips','应用类型操作成功');
		}
		return $this->redirect('adv/apptypes/list')->with('global_tips','应用类型操作成功');
	}
	//对应版本号添加和修改	
	public function postVersionAddEdit()
	{	
		$datainfo=array();
		$input=Input::only('versionNumber','apptypes_id','versionform');
		if(empty($input['versionNumber']) || empty($input['apptypes_id'])){
			return Response::json(array('status'=>0,'msg'=>'数据丢失'));
		}
		$input['Id']=Input::get('Id');
		if(empty($input['Id'])){
			unset($input['Id']);	
		}
		$id=AdvService::VersionAddEdit($input);
		if(empty($id)){
			return Response::json(array('status'=>0,'msg'=>'数据添加失败'));
		}
		if(empty($input['Id'])){
			$input['Id']=$id;
			return Response::json(array('status'=>1,'msg'=>'数据添加成功','datainfo'=>$input));
		}
		return Response::json(array('status'=>2,'msg'=>'数据更新成功','datainfo'=>$input));
	}

	//对应版本号删除
	public function postVersionDel()
	{
		$input['Id']=Input::get('Id');
		$input['is_show']=0;
		$id=AdvService::VersionAddEdit($input);
		if(empty($id)){
			return Response::json(array('status'=>1,'msg'=>'数据删除失败','datainfo'=>$input));
		}
		return Response::json(array('status'=>1,'msg'=>'数据删除成功','datainfo'=>$input));
	}
	
	//删除
	public function getApptypesDelect($id=0)
	{	

		if(empty($id))  return $this->redirect('adv/apptypes/list')->with('global_tips','编号丢失');
		$input['Id']=$id;
		$input['is_show']=0;
		$id=AdvService::VersionAddEdit($input);
		if(empty($id)){
			return $this->redirect('adv/apptypes/list')->with('global_tips','操作失败');
		}
		return $this->redirect('adv/apptypes/list')->with('global_tips','操作成功');

	}


}