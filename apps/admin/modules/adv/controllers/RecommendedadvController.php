<?php

namespace modules\adv\controllers;
use Youxiduo\Adv\AdvService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Youxiduo\V4\Game\GameService;
class RecommendedadvController extends BackendController
{	
	public function _initialize()
	{	
		$this->current_module = 'adv';
	}

	public function getList()
	{
		$search=array();
		$search['page']=Input::get('page',1);
		$search['pageSize']=15;
		$search['=yxd_advert_recommendedadv.is_show']='1';
		$search['=yxd_advert_nature.recommend_type']=Input::get('=re1commend_type');
		$datalist=AdvService::getRecommendedadvList($search);//添加查询条件
		 if(!empty($datalist['result'])){ //AdvService::
			foreach ($datalist['result'] as $key => &$value){ 
				$name=AdvService::getNatureInfo($value['nature_id']); 
				if(!empty($name) && $name['is_show'] == 1){
					$value['nature_name']=$name['natureName'];
					$value['recommend_type']=$name['recommend_type'];
				}
			}	
		}
		$datalist=AdvService::_processingInterface($datalist,$search,$search['pageSize']);
		if(!empty($search['=yxd_advert_nature.recommend_type'])){
			$datalist['search']['recommend_type']=$search['=yxd_advert_nature.recommend_type'];
		}
		return $this->display('/apptypes/recommendedadv-list',$datalist);
	}

	//推荐发布视图添加修改 
	public function getRecommendedadvAddEdit($id=0,$from='ios')
	{	
		$datainfo=array();
		$datainfo['adv']['tabtype']='评测攻略';
		$datainfo['type']=array('图片'=>'图片','文字'=>'文字','弹窗'=>'弹窗','轮播'=>'轮播','推荐'=>'推荐','条幅'=>'条幅','链接'=>'链接','游戏列表'=>'游戏列表','游戏信息'=>'游戏信息');
		$apptypes=AdvService::FindApptypes(" is_show=1 ");
		if(!empty($apptypes)){
			$datainfo['apptypesselect']=$apptypes;	
		}
		$nature=AdvService::FindNature("  is_recommend='推荐' and  is_show=1 ");
		if(!empty($nature)){
			$datainfo['nature']=$nature;	
		}
		if(empty($id)){
			//$datainfo['adv']['type']='评测攻略';
			return $this->display('/apptypes/recommendedadv-addedit',$datainfo);
		}
		$datainfo['adv']=AdvService::getRecommendedadvInfo($id);
		$datainfo['nature_info']=AdvService::getNatureInfo($datainfo['adv']['nature_id']);
		if(!empty($datainfo['adv']['litpic'])){
             $datainfo['adv']['litpic_']= $datainfo['adv']['litpic'];
        }
        return $this->display('/apptypes/recommendedadv-addedit',$datainfo);
	}

	public function postRecommendedadvAddEdit()
	{	
		$input=Input::only('location_id','apptypes_id','nature_id','recommended_id','link_id','title','sort','tabtype','versionform','adv_type','words');
        $biTian=array(
				'apptypes_id'=>'required',
				'recommended_id'=>'required',
				'nature_id'=>'required',
				'link_id'=>'required',
				'title'=>'required',
				'location_id'=>'required',
		);
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
        if(Input::get('Id')){
            $input['Id']=Input::get('Id');
            if(Input::get('litpic_'))  $input['litpic']=Input::get('litpic_');
        }
		$dir = '/userdirs/common/icon_v4/test/adv/';
        $path = storage_path() . $dir;
		if(Input::hasFile('litpic')){	
            $file = Input::file('litpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path =$file->move($path,$new_filename . '.' . $mime );
            if($file_path)  $input['litpic']=$dir.$new_filename . '.' . $mime;
        }else{
        	if(empty($input['litpic']))  return $this->back()->with('global_tips','推荐位缩略图没有上传');
        }
        foreach($input as $key=>&$value){
        	if(empty($value)) $value=0;
        }
        $id=AdvService::RecommendedadvAddEdit($input);
 		if(!empty($id)){
			return $this->redirect('/adv/recommendedadv/list')->with('global_tips','推荐发布操作成功');
		}
		return $this->redirect('/adv/recommendedadv/list')->with('global_tips','推荐发布操作失败');
	}


	public function postYouxiquanAddEdit()
	{
		$input=Input::only('apptypes_id','adv_type','recommended_id','versionform','nature_id','gid','gamename','sort','youxiTitle','youxiWords','location_id');
		$biTian=array(
				'gid'=>'required',
				'versionform'=>'required',
				'gamename'=>'required',
				'youxiTitle'=>'required',
				'location_id'=>'required',
		);
		
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
        if(!empty($input['youxiTitle'])){
        	$input['title']=$input['youxiTitle'];
        	unset($input['youxiTitle']);
        }else{
        	unset($input['youxiTitle']);
        }
        if(!empty($input['youxiWords'])){
        	$input['words']=$input['youxiWords'];
        	unset($input['youxiWords']);
        }else{
        	unset($input['youxiWords']);
        }
        if(Input::get('Id')){
            $input['Id']=Input::get('Id');
            if(Input::get('youxi_litpic_'))  $input['litpic']=Input::get('litpic_');
        }
        $dir = '/userdirs/common/icon_v4/test/adv/';
        $path = storage_path() . $dir;
		if(Input::hasFile('youxi_litpic')){	
            $file = Input::file('youxi_litpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path =$file->move($path,$new_filename . '.' . $mime );
            if($file_path)  $input['litpic']=$dir.$new_filename . '.' . $mime;
        }else{
        	if(empty($input['litpic']))  return $this->back()->with('global_tips','推荐位缩略图没有上传');
        }
        
        foreach($input as $key=>&$value){
        	if(empty($value)) $value=0;
        }

        $id=AdvService::RecommendedadvAddEdit($input);
        if(!empty($id)){
			return $this->redirect('/adv/recommendedadv/list')->with('global_tips','推荐发布成功');
		}
		return $this->redirect('/adv/recommendedadv/list')->with('global_tips','推荐发布操作失败');
	}

	//
	public function getRecommendedadvDelect($id=0)
	{
		if(empty($id)){
			return $this->redirect('/adv/recommendedadv/list')->with('global_tips','编号丢失');
		}
		$input['Id']=$id;
		$input['is_show']=0;
        $id=AdvService::RecommendedadvAddEdit($input);
		if(empty($id)){
			return $this->redirect('/adv/recommendedadv/list')->with('global_tips','删除失败');
		}
		return $this->redirect('/adv/recommendedadv/list')->with('global_tips','删除推荐发布操作成功');
    }


	public function getVersioninfo()
	{	
		$id=Input::get('id');
		$datainfo=AdvService::FindVersion(" apptypes_id=$id and is_show=1");
		
		if(!empty($datainfo)){
			$recommend=AdvService::FindRecommend(" apptypes_id=$id and is_show=1");
			foreach($datainfo as $key=>$value){
				$version[]=	$value['versionNumber'];
			}
			$version=join(',',$version);
			return $this->json(array('status'=>1,'datainfo'=>$version,'selectrecommend'=>$recommend));
		}
		return $this->json(array('status'=>0));
	}
    
    public function getLocationinfo()
    {
    	$id=Input::get('id');
    	$datainfo=AdvService::FindLocation(" nature_id=$id and is_show=1");
    	if(!empty($datainfo)){
    		return $this->json(array('status'=>1,'datainfo'=>$datainfo));	
    	}
		return $this->json(array('status'=>0));
    }

    public function getNatureType(){
    	$id=Input::get('id');
    	if(!empty($id)){
    		//RecommendedadvModel::setTable('nature');
			$type=AdvService::getNatureInfo($id);
			if(!empty($type['recommend_type'])){
				return $this->json(array('status'=>1,'type'=>$type['recommend_type']));
			}
		}
    	return $this->json(array('status'=>0,'type'=>0));
    }



	/***

	//广告应用数据
	public function getApptypesSelect()
	{	
		$search=array();
		$input=Input::get('keyword');
		if(!empty($input)){
			$search['%name']=$input;
		}
		RecommendModel::setTable('apptypes');
		$datalist=self::datalist($search,Input::get('page',1),10);
		$html = $this->html('/apptypes/pop-apptypes-list',$datalist);
        return $this->json(array('html'=>$html));	
	}
    ****/
	
}