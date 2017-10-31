<?php
namespace modules\topic\controllers;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Youxiduo\System\Model\Admin;
use Youxiduo\Topic\TopicService;
class TopicinfoController extends BackendController
{
      
	public function _initialize()
	{	
		$this->current_module = 'topic';
	}
  
	//列表处理
	public function getList()
	{	
		$search=array();
		$search['page']=Input::get('page',1);
		$search['pageSize']=15;
		if(Input::get('=ztitle')) $search['=ztitle']=Input::get('=ztitle');
		if(Input::get('=apptype')) $search['=apptype']=Input::get('=apptype');
    $datalist=TopicService::getTopicList($search);//添加查询条件
    if(!empty($datalist['result'])){ 
      foreach ($datalist['result'] as $key => &$value){
        $name=Admin::getInfoById($value['editor']);
        if(!empty($name) && $name['isopen'] == 1){
          $value['authorname']=$name['authorname'];
        }
      } 
    }
		$datalist=TopicService::_processingInterface($datalist,$search,$search['pageSize']);
		return $this->display('topic-list',$datalist);
	}
  
	//删除
	public function getDelect($id=0)
	{
		if(empty($id)){
            return $this->back()->with('global_tips','编号缺失');
		}
		
		$zt_games=TopicService::getTopicGameInfo($id,'zt_id'); 
		if(!$zt_games){
			 TopicService::deleteTopicGame($id,'zt_id');
		}
    TopicService::deleteTopic($id);
		return $this->redirect('topic/topicinfo/list','删除成功');
	}

	//置顶
	public function getApptop($id=0,$isapptop=0){
		if(empty($id)){
            return $this->back()->with('global_tips','编号缺失');
		}
		$datainfo['id']=$id;
		$datainfo['isapptop']=($isapptop == 1) ? 0 : 1;
		TopicService::TopicAddEdit($datainfo);
		return $this->redirect('topic/topicinfo/list','操作成功');
	}
  
   public function getViewAddEdit($id=0)
    {   
        if(empty($id)){
            $data=$this->_getGlobalData(array());
            return $this->display('topic-add-edit',$data);
        }
        $data['id']=$id;
        $data['datainfo']=TopicService::getTopicInfo($id);
        $data=$this->_getGlobalData($data);
        return $this->display('topic-add-edit',$data);
    }

	public function _getGlobalData($data){
       	if(empty($data['id'])) return $data;
        $ztgames=TopicService::getTopicGameInfo($data['id'],'zt_id');
       	if(!empty($ztgames)){
       		$agids=$gid=array();
       		foreach ($ztgames as $key => $value) {
       			 if(!empty($value['agid'])){
       			 	$agids[]=$value['agid'];
       			 }
       			 if(!empty($value['gid'])){
       			 	$gid[]=$value['gid'];
       			 }
       		}
       	}
       	$data['datainfo']['agid']=!empty($agids) ? join(',',$agids) : '';
       	$data['datainfo']['gid']=!empty($gid) ? join(',',$gid) : '';
       	return $data;
    }

	//添加-修改
	public function postAddEdit($id=0)
	{	
		$input = Input::all();
		$biTian=array(
				'ztitle'=>'required',
				'platform'=>'required',
				'writer'=>'required',
				'description'=>'required',
				);
        $message = array(
            'required' => '不能为空',
        );
		$validator = Validator::make($input,$biTian,$message);
    if($validator->fails()){
        $messages = $validator->messages();
        foreach ($messages->all() as $message)
        {
            $strerror[]=$message;
        }
        return $this->back()->with('global_tips',join('-',$strerror));
     }
     if (empty($input['toggle']) || $input['toggle'] == "off"){
        $data['flag'] = 0;
     }else{
        $data['flag'] = 1;
     }
     
		$arr=array('ztitle','writer','description');
 		foreach($arr as $key=>$value){
 			if(!empty($input[$value]))
 				$data[$value]= $input[$value];
 		}
 		$data['apptype']=$input['platform'];
		$data['addtime'] = time();
        $dir = '/u/topicpic/' . date('Y') . '/' . date('m') . '/';
        $path = storage_path() . $dir;
        if(Input::hasFile('litpic')){
            $file = Input::file('litpic');
            $new_filename = date('YmdHis') . str_random(4);
            $mime = $file->getClientOriginalExtension();
            $file_path = $file->move($path,$new_filename . '.' . $mime );
            //if($file_path) $input['productImgpath']['detailPic']=$this->myurl.$dir.$new_filename . '.' . $mime;
            if($file_path) $data['litpic']=$dir.$new_filename . '.' . $mime;
        }
        $uid=$this->getSessionData('youxiduo_admin');
        $data['editor'] =$uid['id'];
        $data['id']=!empty($id)?$id:0;
        TopicService::TopicAddEdit($data);
        if(empty($id)) return $this->back()->with('global_tips','操作失败');
        $ztgames=array();
        $ztgames['zt_id']=$id;
        if(!empty($data['id'])){
        	TopicService::deleteTopicGame($data['id'],'zt_id');
        	$ztgames['zt_id']=$data['id'];
        }	
      	if(!empty($input['iosId']) && $data['apptype'] != 2){
      	 	 $ztgames['agid']=0;
      	 	 $tgames=explode(',', $input['iosId']);
      	 	 foreach ($tgames as $key => $value) {
      	 	 	 $ztgames['gid']=$value;
      	 	 	 TopicService::TopicGameAddEdit($ztgames);
      	 	 }
      	 }	
      	 if(!empty($input['androidId']) && $data['apptype'] != 1){
      	 	 $ztgames['gid']=0;
      	 	 $tgames=explode(',', $input['androidId']);
      	 	 foreach ($tgames as $key => $value) {
			       $ztgames['agid']=$value;
      	 	 	 //$a=self::addedit($ztgames,'id','zt_games');
             TopicService::TopicGameAddEdit($ztgames);
      	 	 }
      	 }
         return $this->redirect('topic/topicinfo/list','操作成功');
     }

     public function getSelectLayer($type)
     {
          $input = Input::all();
          $search['page']=Input::get('page',1) ;
          $search['pageSize']=!empty($input['pageSize']) ? $input['pageSize'] : 7;
          if(!empty($input['searchkey']))
          {     
              foreach($input['searchkey'] as $k=>$key){
                  $key_=strtr($key,array('.'=>'_'));
                  if(!empty($input[$key_])){
                      $search[$key]=$input[$key_];
                  }
              }
          }
          if(!empty($input['needFilter'])){
              $search= $search+$input['needFilter']['0'];
          }
         
          if($type == 1){ 
             $datalist=TopicService::getIosGameList($search);
          }else{
             $datalist=TopicService::getaGameList($search);
          }
          //
          if(!empty($datalist['result'])){ 
            foreach ($datalist['result'] as $key => &$value){ 
                $name=TopicService::getGametypeInfo($value['type']);
                $value['typename']=!empty($name) ? $name['typename']:'';
            }
          }
          $input=$input+TopicService::_processingInterface($datalist,$search,$search['pageSize'],!empty($input['is_page'])?$input['is_page']:0);
          $this->current_module = 'my_public';
          $input['totalCount']=ceil($input['totalCount']/$search['pageSize']);
          if(!empty($input['is_page'])){
            return $this->json(array('datalist'=>$input['datalist'],'totalCount'=>$input['totalCount']));
          }

          return $this->display('/layers',$input);
     }
    
}
?>