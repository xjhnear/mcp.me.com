<?php

namespace modules\adv\controllers;

use Youxiduo\Adv\AdvService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
class NatureController extends BackendController
{
	public function _initialize()
	{	
		$this->current_module = 'adv';
	}
	
	//广告性质列表查询
	public function getList()
	{	
		$search=array();
		$search['page']=Input::get('page',1);
		$search['pageSize']=15;
		$search['=is_show']='1';
		$search['%natureName']=Input::get('%natureName');	
		$search['=is_recommend']=Input::get('=is_recommend');
		$datalist=AdvService::getNatureList($search);
		$datalist=AdvService::_processingInterface($datalist,$search,$search['pageSize']);
		if(!empty($search['%natureName'])){
			$datalist['search']['natureName']=$search['%natureName'];
		}
		if(!empty($search['=is_recommend'])){
			$datalist['search']['is_recommend']=$search['=is_recommend'];
		}
		return $this->display('/apptypes/nature-list',$datalist);
	}

	//广告性质添加修改视图
	public function getNatureAddEdit($id=0)
	{	
		$datainfo['datainfo']['is_recommend']='广告模式';
		if(empty($id)){
			return $this->display('/apptypes/nature-addedit',$datainfo);
		}
		$datainfo['datainfo']=AdvService::getNatureInfo($id);
		$Location=AdvService::FindLocation(" nature_id=$id and is_show=1  ");
		if(!empty($Location)){
			$datainfo['datainfo']['nature']=$Location;
		}
		return $this->display('/apptypes/nature-addedit',$datainfo);
	}

	public function postNatureAddEdit($id=0)
	{
		$input = Input::only('natureName','Id','is_recommend','recommend_type');
		$biTian=array('natureName'=>'required');
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
        if(!empty($input['Id'])){
        	foreach($input as $key=>&$value){
        		if(empty($value)){
        			unset($input[$key]);
        		}
        	}
        	$wheres=array(
        		'0'=>array('natureName','=',trim($input['natureName'])),
        		'1'=>array('Id','!=',$input['Id']),
        		'2'=>array('is_recommend','=',$input['is_recommend'])
        		);
        }else{
        	$wheres=array(
        		'0'=>array('natureName','=',trim($input['natureName'])),
        		'1'=>array('is_recommend','=',$input['is_recommend'])
        		);

        }
        $datainfo['info_name']=AdvService::is_Nature_name($wheres);
        	if(!empty($datainfo['info_name'])){ 
	        		foreach($datainfo['info_name'] as $val){
	        			if($val['is_show'] == 1){
	        					return $this->back()->with('global_tips','不能重复添加');
	        			}
	        		}
			}
		$id=AdvService::NatureAddEdit($input);
        if(!empty($id)){
			return $this->redirect('/adv/nature/list')->with('global_tips','操作成功');
		}
		return $this->redirect('/adv/nature/list')->with('global_tips','操作成功');
	}
	

	//广告性质数据添加修改
	public function getNatureDelect($id=0,$is_recommend)
	{
		if(empty($id)){
			return $this->redirect('/adv/nature/list')->with('global_tips','编号丢失');
		}
		if($is_recommend == '推荐'){
			$data=AdvService::FindRecommendedadv(" nature_id=$id and is_show=1 LImit 1");
			//$data=AdvService::getRecommendedadvInfo($id,'nature_id');
			$str='抱歉，在推荐发布中有关联不能删除'; 
		}elseif($is_recommend == '广告模式'){
			//$data=AdvService::getAppadvInfo($id,'nature_id');
			$data=AdvService::FindAppadv(" nature_id=$id and is_show=1 LImit 1");
			//$data=NatureModel::find("select Id,nature_id FROM yxd_advert.yxd_advert_appadv WHERE nature_id=$id limit 1;");
			$str='抱歉，在广告发布中有关联不能删除';
		}
		if(!empty($data)){
			return $this->redirect('/adv/nature/list')->with('global_tips',$str);
		}
		$input['Id']=$id;
		$input['is_show']=0;
        $id=AdvService::NatureAddEdit($input);
		if(empty($id)){
			return $this->redirect('/adv/nature/list')->with('global_tips','删除失败');
		}
		return $this->redirect('/adv/nature/list')->with('global_tips','操作成功');
    }
 

	//用于生成符合前台页面SELECT标签的数组
    private static function array_select($result,$id,$val)
    {
        if($result){
            $selectInfo=array();
            foreach($result as $key=>$value){
                $selectInfo[$value[$id]]=$value[$val];
            }
            return $selectInfo;
        }
        return $result;
    }

	
}