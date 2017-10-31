<?php

namespace modules\adv\controllers;
use Youxiduo\Adv\AdvService;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Youxiduo\V4\Game\GameService;
class ReleaseadvController extends BackendController
{	
	private $genre ='ios';
	public function _initialize()
	{
		$this->current_module = 'adv';
	}

	public function getList()
	{
		$search=array();
		$search['page']=Input::get('page',1);
		$search['pageSize']=15;
		$search['=yxd_advert_appadv.is_show']='1';
		$search['=yxd_advert_appadv.versionform']=Input::get('=versionform');
		$datalist=AdvService::getReleaseadvList($search);//添加查询条件
		if(!empty($datalist['result'])){ 
			foreach ($datalist['result'] as $key => &$value){
				$name=AdvService::getNatureInfo($value['nature_id']);
				if(!empty($name) && $name['is_show'] == 1){
					$value['natureName']=$name['natureName'];
				}
				$locationName=AdvService::getLocationInfo($value['location_id']);
				if(!empty($locationName) && $locationName['is_show'] == 1){
					$value['locationName']=$locationName['name'];
				}
			}	
		}
		$datalist=AdvService::_processingInterface($datalist,$search,$search['pageSize']);
		if(!empty($search['=yxd_advert_appadv.versionform'])){
			$datalist['search']['versionform']=$search['=yxd_advert_appadv.versionform'];
		}
		return $this->display('/apptypes/releaseadv-list',$datalist);
	}

	//广告发布视图添加修改 
	public function getReleaseadvAddEdit($id=0,$from='ios')
	{	
		$datainfo=array();
		$datainfo['type']=array('图片'=>'图片','文字'=>'文字','弹窗'=>'弹窗','轮播'=>'轮播','条幅'=>'条幅','链接'=>'链接','游戏列表'=>'游戏列表','游戏信息'=>'游戏信息');
		$apptypes=AdvService::FindApptypes(" versionform='$from' and   is_show=1");
		if(!empty($apptypes)){
			$datainfo['apptypesselect']=$apptypes;	
		}
		$nature=AdvService::FindNature(" is_recommend='广告模式'  and  is_show=1 ");
		if(!empty($nature)){
			$datainfo['nature']=$nature;	
		}
		$datainfo['adv']['versionform']=$from;
		if(empty($id)){
			if($from == 'ios'){
				return $this->display('/apptypes/releaseadv-addedit',$datainfo);
			}
			return $this->display('/apptypes/releaseadv-addedit-android',$datainfo);
		}
		$datainfo['adv']=AdvService::getAppadvInfo($id);
		$datainfo['location']=AdvService::FindLocation("nature_id =".$datainfo['adv']['nature_id']." and  is_show=1");
		if(!empty($datainfo['adv']['litpic'])){
             $datainfo['adv']['litpic_']= $datainfo['adv']['litpic'];
        }
        if(!empty($datainfo['adv']['bigpic'])){
             $datainfo['adv']['bigpic_']= $datainfo['adv']['bigpic'];
        }
        $game=GameService::getMultiInfoById(array('0'=>$datainfo['adv']['gid']),$this->genre);
        if(!empty($game['0']) && $game != 'game_not_exists'){
       
        	$datainfo['adv']['gname']=$game['0']['gname'];
        }
        
        if($from == 'ios'){
			return $this->display('/apptypes/releaseadv-addedit',$datainfo);
		}
		return $this->display('/apptypes/releaseadv-addedit-android',$datainfo);
		
	}

	public function postReleaseadvAddEdit()
	{	
		$input=Input::only('adv_type','apptypes_id','nature_id','location_id','advname','title','downurl','tosafari','gid','gamename','url','sendmac','sendidfa','sendudid','sendos','versionform','sendplat','sendactive','aid');
		$biTian=array(
			'apptypes_id'=>'required',
			'location_id'=>'required',
			'nature_id'=>'required',
			'aid'=>'required',
			'downurl'=>'required',
			'tosafari'=>'required'
		);
		if($input['adv_type'] == '图片'){
			unset($biTian['downurl']);
		}
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
            if(Input::get('bigpic_')) $input['bigpic']=Input::get('bigpic_');
        }

        $dir = '/advdirs/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;

        if(Input::hasFile('litpic')){
            $file = Input::file('litpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path =$file->move($path,$new_filename . '.' . $mime );
            //if($file_path)  $input['categoryImgpath']=$this->myurl.$dir.$new_filename . '.' . $mime;
            if($file_path)  $input['litpic']=$dir.$new_filename . '.' . $mime;
        }
        if(Input::hasFile('bigpic')){
            $file = Input::file('bigpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path =$file->move($path,$new_filename . '.' . $mime );
            //if($file_path)  $input['categoryImgpath']=$this->myurl.$dir.$new_filename . '.' . $mime;
            if($file_path)  $input['bigpic']=$dir.$new_filename . '.' . $mime;
        }
        
        foreach($input as $key=>&$value){
        	if(empty($value)) $value=0;
        }
       
        $id=AdvService::ReleaseadvAddEdit($input);
 		if(!empty($id)){
			return $this->redirect('/adv/releaseadv/list')->with('global_tips','发布广告操作成功');
		}
		return $this->redirect('/adv/releaseadv/list')->with('global_tips','发布广告操作失败');
	}

	//
	public function getReleaseadvDelect($id=0)
	{
		if(empty($id)){
			return $this->redirect('/adv/releaseadv/list')->with('global_tips','编号丢失');
		}

		$input['Id']=$id;
		$input['is_show']=0;
        $id=AdvService::ReleaseadvAddEdit($input);
		if(empty($id)){
			return $this->redirect('/adv/releaseadv/list')->with('global_tips','删除失败');
		}
		return $this->redirect('/adv/releaseadv/list')->with('global_tips','删除发布广告操作成功');
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
	/***
	public function getVersioninfo()
	{	
		$id=Input::get('id');
		$datainfo=AdvService::FindVersion(" apptypes_id=$id and is_show=1");
		//$type=ReleaseadvModel::getInfo($id);
		//$type=!empty($type['type'])?$type['type']:'';
		if(!empty($datainfo)){
			foreach($datainfo as $key=>$value){
				$version[]=	$value['versionNumber'];
			}
			$version=join(',',$version);
			return $this->json(array('status'=>1,'datainfo'=>$version,'type'=>$type));
		}
		return $this->json(array('status'=>0));
	}***/
    

    public function  getLocationinfo()
    {
    	$id=Input::get('id');
    	$location=AdvService::FindLocation(" nature_id=$id and is_show=1 ");
		$nature=AdvService::getNatureinfo($id);
		if(!empty($location)){
			return $this->json(array('status'=>1,'datainfo'=>$location,'nature'=>$nature));
		}
		return $this->json(array('status'=>0));
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