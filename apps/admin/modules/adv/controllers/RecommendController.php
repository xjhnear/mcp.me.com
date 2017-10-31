<?php
namespace modules\adv\controllers;
use Youxiduo\Adv\AdvService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\Validator;
class RecommendController extends BackendController
{
	public function _initialize()
	{	
		$this->current_module = 'adv';
		
	}
	//列表查询
	public function getList()
	{	
		$search=array();
		$search['pageIndex']=Input::get('page',1);
		$search['pageSize']=15;
		$search['%yxd_advert_recommend.name']=Input::get('%name');
		$search['=yxd_advert_recommend.is_show']='1';
		$datalist=AdvService::getRecommendList($search);//添加查询条件
		 if(!empty($datalist['result'])){ //AdvService::
			foreach ($datalist['result'] as $key => &$value){
				$name=AdvService::getApptypeInfo($value['apptypes_id']);
				$value['aname']=(!empty($name) && $name['is_show'] == 1) ? $name['name']:'';
			}
		}
		$datalist=AdvService::_processingInterface($datalist,$search,$search['pageSize']);
		if(!empty($search['%yxd_advert_recommend.name'])){
			$datalist['search']['name']=$search['%yxd_advert_recommend.name'];
		}
		return $this->display('/apptypes/recommend-list',$datalist);
	}
	
	//推荐类型视图添加修改
	public function getRecommendAddEdit($id=0)
	{
		$datainfo['datalist']['is_show']=1;
		
		if(empty($id)){
			return $this->display('/apptypes/recommend-addedit',$datainfo);
		}
		$datainfo['datainfo']=AdvService::getRecommendInfo($id);
					$apptypes=AdvService::FindApptypes(' Id='.$datainfo['datainfo']['apptypes_id'].'  and is_show=1 limit 1 ');
		if(!empty($apptypes)){
			$datainfo['datainfo']['apptypes_name']=$apptypes['0']['name'];
		}
		return $this->display('/apptypes/recommend-addedit',$datainfo);
	}
	
	//推荐类型数据添加修改
	public function postRecommendAddEdit()
	{
		$input = Input::only('name','apptypes_id','Id');
		$biTian=array('name'=>'required','apptypes_id'=>'required');
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
        $id=AdvService::RecommendAddEdit($input);
        if(!empty($id)){

			return $this->redirect('/adv/recommend/list')->with('global_tips','应用类型操作成功');
		}
		return $this->redirect('/adv/recommend/list')->with('global_tips','应用类型操作成功');
    }
	
    //推荐类型数据添加修改
	public function getRecommendDelect($id=0)
	{
		if(empty($id)){
			return $this->redirect('/adv/recommend/list')->with('global_tips','编号丢失');
		}
		$input['Id']=$id;
		$input['is_show']=0;
        $id=AdvService::RecommendAddEdit($input);
		if(empty($id)){
			return $this->redirect('/adv/recommend/list')->with('global_tips','删除失败');
		}
		return $this->redirect('/adv/recommend/list')->with('global_tips','推荐类型操作成功');
    }

	//广告应用数据
	public function getApptypesSelect()
	{	
		$search=array();
		$input=Input::get('keyword');
		if(!empty($input)){
			$search['%name']=$input;
		}
		$search['page']=Input::get('page',1);
		$search['pageSize']=7;
		$datalist=AdvService::getApptypeList($search);
		$datalist=AdvService::_processingInterface($datalist,$search,$search['pageSize']);
		$html = $this->html('/apptypes/pop-apptypes-list',$datalist);
        return $this->json(array('html'=>$html));	
	}

}