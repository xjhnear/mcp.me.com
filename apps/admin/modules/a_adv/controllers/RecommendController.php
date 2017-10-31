<?php
namespace modules\a_adv\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Yxd\Modules\Core\BackendController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

use Youxiduo\Android\Model\AdvImage;
use Youxiduo\Android\Model\AdvAppLink;
use Youxiduo\Android\Model\AdvLocation;
use Youxiduo\Android\Model\Game;
use Youxiduo\Message\Model\MessageType;

class RecommendController extends BackendController
{
	protected $slide = array();
	protected $location = array();
	protected $versions = array();
	public function _initialize()
	{
		$this->current_module = 'a_adv';
		$this->location = AdvLocation::getOptions();
		$this->slide= array(
		    'slide_1'=>'位置-1',
		    'slide_2'=>'位置-2',
		    'slide_3'=>'位置-3',
		    'slide_4'=>'位置-4',
		    'slide_5'=>'位置-5',
		    'slide_6'=>'位置-6',
		    'slide_7'=>'位置-7',
		    'slide_8'=>'位置-8',
		    'slide_9'=>'位置-9',
		    'slide_10'=>'位置-10',
		);
		$this->versions = array(
		    '2.9'=>'2.9',
		    '2.9.1'=>'2.9.1',
		    '2.9.2beta'=>'2.9.2beta',
		    '2.9.2'=>'2.9.2',
			'3.0.0'=>'3.0.0',
			'3.0.1'=>'3.0.1',
			'3.0.2'=>'3.0.2',
			'3.1.0'=>'3.1.0',
		);
	}
	
	public function getImagePlaceList($_place_type='12')
	{
		$place_type = Input::get('place_type',$_place_type);
		$pageIndex = Input::get('page',1);
		$pageSize = 10;
		
		$data = array();		
				
		$data['place_id_list'] = $this->slide;
		$data['place_type_list'] = $this->location;
		$search = array();
		$search['place_type'] = $place_type;
		$data['place_type'] = $place_type;
		$total = AdvImage::searchCount($search);
		$result = AdvImage::searchList($search,$pageIndex,$pageSize);
		
		$pager = Paginator::make(array(),$total,$pageSize);
		$pager->appends($search);
		$data['search'] = $search;
		$data['pagelinks'] = $pager->links();
		$data['datalist'] = $result;
		
		return $this->display('recommend/image-place-list',$data);
	}
	
	public function getImagePlaceEdit($id=0,$place_type)
	{
		$id = Input::get('id',$id);
		$data = array();		
		$result = MessageType::getList();
		$result = Config::get('linktype');
		$linkTypeListDesc = array();
		foreach($result as $key=>$row){
			$linkTypeList[$key] = $row['name'];
			$linkTypeListDesc[$key] = $row['description'];
		}
		
		$data['linkTypeList'] = $linkTypeList;
		$data['descs'] = json_encode($linkTypeListDesc);
		
		$data['place_id_list'] = $this->slide;
		$data['place_type_list'] = $this->location;
		$data['place_type'] = $place_type;
		$data['versions'] = $this->versions;
		if($id){
			$data['adv'] = AdvImage::findOne(array('id'=>$id));			
			$data['selecteds'] = AdvAppLink::getVersionsByAdvId($id,$data['adv']['place_type']);
		}else{
			$data['adv'] = array('is_show'=>1);
			$data['selecteds'] = array();
		}
		return $this->display('recommend/image-place-edit',$data);
	}
	
	public function postImagePlaceEdit()
	{
		$input = Input::only('id','title','words','place_id','place_type','start_time','end_time','is_show','linktype','link');
		
		$rule = array(
		    'title'=>'required',
		    'place_id'=>'required',
		    'start_time'=>'required',
		    'end_time'=>'required'
		);
		
	    $validator = Validator::make($input,$rule);
		if($validator->fails()){
			if($validator->messages()->has('title')){
				return $this->back()->with('global_tips','标题不能为空');
			}
		    if($validator->messages()->has('place_id')){
				return $this->back()->with('global_tips','请选择位置');
			}		    
		    if($validator->messages()->has('starttime')){
				return $this->back()->with('global_tips','请选择活动开始时间');
			}
		    if($validator->messages()->has('datetime')){
				return $this->back()->with('global_tips','请选择活动结束时间');
			}
		}
		
		$data['id'] = (int)$input['id'];
		$data['title'] = $input['title'];
		$data['words'] = $input['words'];
		$data['place_id'] = $input['place_id'];
		$data['place_type'] = $input['place_type'];
		$data['start_time'] = strtotime($input['start_time']);
		$data['end_time'] = strtotime($input['end_time']);
		$data['is_show'] = (int)$input['is_show'];
		$data['linktype'] = $input['linktype'];
		$data['link'] = $input['link'];
		
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    //列表图
	    if(Input::hasFile('filedata')){	    	
			$file = Input::file('filedata'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['img'] = $dir . $new_filename . '.' . $mime;
		}else{
			$data['img'] = Input::get('img');
		}
		if(!$data['img']) return $this->back()->with('global_tips','请选择图片');
		$success = AdvImage::saveAddOrUpdate($data);
		$selecteds = Input::get('version');
		if($success){
			if(is_array($selecteds)){
				$adv_id = $data['id']>0 ? $data['id'] : $success;
				$place_type_badge = $data['place_type'];
				AdvAppLink::saveAdvAppVersion($adv_id,$place_type_badge,'yxdandroid','',$selecteds);
			}
			return $this->redirect('a_adv/recommend/image-place-list/'.$input['place_type'],'推荐位保存成功');
		}
		return $this->back('推荐位保存失败');
	}

	public function getLocationList()
	{
		$data = array();
		$data['datalist'] = AdvLocation::searchList(1,100);
		return $this->display('recommend/place-location-list',$data);
	}

	public function getLocationEdit($id=0)
	{
		$data = array();
		if($id){
			$data['position'] = AdvLocation::getInfo($id);
		}
		return $this->display('recommend/place-location-edit',$data);
	}

	public function postLocationEdit()
	{
		$id = Input::get('id');
		$name = Input::get('name');
		$identify = Input::get('identify');
		$desc = Input::get('desc');
		$data = array();

		$result = AdvLocation::save($id,$name,$identify,$desc,$data);
		if($result){
			return $this->redirect('a_adv/recommend/location-list');
		}
		return $this->back('保存失败');
	}
}