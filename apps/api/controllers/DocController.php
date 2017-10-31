<?php


use Illuminate\Routing\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class DocController extends Controller
{
	public function getIndex()
	{
		$data = array();
		$data['projects'] = ProjectModel::getList();
		return View::make('help.home',$data);
	}
	
	public function getProjectInfo($id)
	{
		$data = array();
		$data['project'] = ProjectModel::getInfo($id);
		return View::make('help.project-info',$data);
	}
	
    public function getProjectCreate()
	{
		$data = array();
		$data['statuslist'] = array('计划'=>'计划','开发'=>'开发','测试'=>'测试','运行'=>'运行','废弃'=>'废弃');
		return View::make('help.project-edit',$data);
	}
	
    public function getProjectEdit($id)
	{
		$data = array();
		$data['project'] = ProjectModel::getInfo($id);
		$data['statuslist'] = array('计划'=>'计划','开发'=>'开发','测试'=>'测试','运行'=>'运行','废弃'=>'废弃');
		return View::make('help.project-edit',$data);
	}
	
	public function postProjectSave()
	{
		$input = Input::only('id','name','status','host_develop','host_test','host_product','summary');
		$id = ProjectModel::save($input);
		return Redirect::to('doc/project-info/'.$id);
	}
	
	public function getInterfaceList($id)
	{
		$data = array();
		$data['project'] = ProjectModel::getInfo($id);
		$data['interfacelist'] = InterfaceModel::getList($id); 
		return View::make('help.interface-list',$data);
	}
	
	public function getInterfaceInfo($id)
	{
		$data = array();		
		$data['interface'] = InterfaceModel::getInfo($id);
		if(!$data['interface']) return false;
		$data['interfacelist'] = InterfaceModel::getList($data['interface']['project_id']);
		$data['project'] = ProjectModel::getInfo($data['interface']['project_id']);
		return View::make('help.interface-info',$data);
	}
	
    public function getInterfaceCreate($project_id)
	{
		$data = array();
		$data['project'] = ProjectModel::getInfo($project_id);
		//$data['interfacelist'] = InterfaceModel::getList($project_id);
		$data['categorylist'] = InterfaceModel::getCateList($project_id);
		return View::make('help.interface-edit',$data);
	}
	
	public function getInterfaceEdit($id)
	{
		$data = array();
		$data['interface'] = InterfaceModel::getInfo($id);
		if(!$data['interface']) return false;
		$data['interfacelist'] = InterfaceModel::getList($data['interface']['project_id']);
		$data['project'] = ProjectModel::getInfo($data['interface']['project_id']);
		$data['categorylist'] = InterfaceModel::getCateList($data['interface']['project_id']);
		return View::make('help.interface-edit',$data);
	}
	
	public function postInterfaceSave()
	{
		$input = Input::only('id','project_id','name','http_method','url','cate_id','require_login','summary','out_code');
		
		$input_params = array();		
		$input_name = Input::get('input_name');
		$input_type = Input::get('input_type');
		$input_required = Input::get('input_required');
		$input_desc = Input::get('input_desc');
		foreach($input_name as $index=>$name){
			if(empty($name)) continue;
			$input_params[$index]['name'] = $name;
			$input_params[$index]['type'] = isset($input_type[$index]) ? $input_type[$index] : 'string';
			$input_params[$index]['required'] = isset($input_required[$index]) ? $input_required[$index] : 'false';
			$input_params[$index]['desc'] = isset($input_desc[$index]) ? $input_desc[$index] : '';
		}
		
		$out_params   = array();
		$out_name = Input::get('out_name',array());
		$out_sample = Input::get('out_sample');
		$out_desc = Input::get('out_desc');
		$out_type = Input::get('out_type');
	    foreach($out_name as $index=>$name){
			if(empty($name)) continue;
			$out_params[$index]['name'] = $name;
			$out_params[$index]['type'] = isset($out_type[$index]) ? $out_type[$index] : 'string';
			$out_params[$index]['sample'] = isset($out_sample[$index]) ? $out_sample[$index] : '';
			$out_params[$index]['desc'] = isset($out_desc[$index]) ? $out_desc[$index] : '';
		}
		
		$error_params = array();
		$error_no = Input::get('error_no');
		$error_code = Input::get('error_code',array());
		$error_description = Input::get('error_description');
		foreach($error_code as $index=>$code){
			if(empty($code)) continue;
			$error_params[$index]['error_no'] = $error_no[$index];
			$error_params[$index]['error_code'] = $error_code[$index];
			$error_params[$index]['error_description'] = $error_description[$index];
		}
		
		$input['input_params'] = serialize($input_params);
		$input['out_params'] = serialize($out_params);
		$input['error_params'] = serialize($error_params);
		
		$id = InterfaceModel::save($input);
		return Redirect::to('doc/interface-info/'.$id);
	}
	
	public function getEntityInfo($id)
	{
		$data = array();
		
		return View::make('help.entity-info',$data);
	}
	
    public function getEntityCreate()
	{
		$data = array();
		
		return View::make('help.entity-edit',$data);
	}
	
    public function getEntityEdit()
	{
		$data = array();
		
		return View::make('help.entity-edit',$data);
	}
	
    public function postEntitySave()
	{
	}
	
	public function getCategoryCreate($project_id)
	{
		$data = array();
		$data['project'] = ProjectModel::getInfo($project_id);
		return View::make('help.category-edit',$data);
	}
	
	public function postCategorySave()
	{
		$input = Input::only('id','name','project_id');
		InterfaceModel::saveCategory($input);
		return Redirect::to('doc/project-info/'.$input['project_id']);
	}
}