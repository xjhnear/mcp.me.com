<?php
namespace modules\adv\controllers;
use Youxiduo\Adv\AdvService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class LocationController extends BackendController
{
	public function _initialize()
	{	
		$this->current_module = 'adv';
	}
	//广告地址列表 location-list
	public function getList()
	{   
		$search=array();
		$search['pageIndex']=Input::get('page',1);
		$search['pageSize']=15;
		$search['%yxd_advert_location.name']=Input::get('%name');	
		$search['=yxd_advert_location.is_show']='1';
		$datalist=AdvService::getLocationList($search);//添加查询条件
		if(!empty($datalist['result'])){ 
			foreach ($datalist['result'] as $key => &$value){
				$name=AdvService::getNatureInfo($value['nature_id']);
				$value['natureName']=(!empty($name) && $name['is_show'] == 1) ? $name['natureName']:'';
			}
		}
		$datalist=AdvService::_processingInterface($datalist,$search,$search['pageSize']);
		if(!empty($search['%yxd_advert_location.name'])){
			$datalist['search']['name']=$search['%yxd_advert_location.name'];
		}
		return $this->display('/apptypes/location-list',$datalist);
	}
   
	//广告地址添加修改视图
	public function getLocationAddEdit($id=0)
	{	
		$datainfo['datalist']['is_show']=1;
		if(empty($id)){
			return $this->display('/apptypes/location-addedit',$datainfo);
		}
		$datainfo['datainfo']=AdvService::getLocationInfo($id);
		$Nature=AdvService::FindNature(" Id=".$datainfo['datainfo']['nature_id']."  and is_show=1 limit 1 ");
		
		if(!empty($Nature)){
			$datainfo['datainfo']['natureName']=$Nature['0']['natureName'];
		}
		return $this->display('/apptypes/location-addedit',$datainfo);
	}

	public function postLocationAddEdit($id=0)
	{
		$input = Input::only('name','nature_id','Id','sort','biaoshi');
		$biTian=array('name'=>'required','nature_id'=>'required','biaoshi'=>'required');
        $message = array(
            'required' => '不能为空',
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
        $id=AdvService::LocationAddEdit($input);
        if(!empty($id)){
			return $this->redirect('/adv/location/list')->with('global_tips','操作成功');
		}
		return $this->redirect('/adv/location/list')->with('global_tips','操作完毕');
	}
	
	//广告地址数据添加修改
	public function getLocationDelect($id=0)
	{
		if(empty($id)){
			return $this->redirect('/adv/location/list')->with('global_tips','编号丢失');
		}
		$input['Id']=$id;
		$input['is_show']=0;
        $id=AdvService::LocationAddEdit($input);
		if(empty($id)){
			return $this->redirect('/adv/location/list')->with('global_tips','删除失败');
		}
		return $this->redirect('/adv/location/list')->with('global_tips','操作成功');
    }

	public function getNatureSelect()
	{	
		$search=array();
		$input=Input::get('keyword');
		if(!empty($input)){
			$search['%natureName']=$input;
		}
		$search['=is_show']=1;
		$search['page']=Input::get('page',1);
		$search['pageSize']=7;
		//$search['=is_recommend']='广告模式';
		$datalist=AdvService::getNatureList($search);
		$datalist=AdvService::_processingInterface($datalist,$search,$search['pageSize']);
		$html = $this->html('/apptypes/pop-nature-list',$datalist);
        return $this->json(array('html'=>$html));	
	}
 
}