<?php
namespace modules\common\controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Yxd\Utility\ImageHelper;
use Youxiduo\Helper\Utility;
use Yxd\Modules\Core\BackendController;

class UploaderController extends BackendController
{
    var $upload_uri,$get_uri = false;
    public function __construct(){
        $env = $_SERVER['SERVER_NAME'];
        $env_split = explode('.',$env);
        if(($env_split && $env_split[0] == 'test') || ($env_split && $env_split[2] == 'dev')){
            //测试
            $this->upload_uri = 'http://test.open.youxiduo.com:8080/module_file_system/file_upload_by_form';
            $this->get_uri = 'http://test.open.youxiduo.com:8080/module_file_system/file/';
        }elseif($env_split && $env_split[2] == 'com'){
            //正式
            $this->upload_uri = 'http://youxiduo-java-slb-1:18080/module_file_system/file_upload_by_form';
            $this->get_uri = 'http://img001.youxiduo.com/';
        }else{
            //错误
            exit;
        }
        if(!$this->upload_uri || $this->get_uri) return $this->returnJson('上传地址配置错误');
    }

	public function _initialize()
	{
		$this->current_module = 'common';
	}

	public function getConfig()
	{
		$ueditor = Config::get('ueditor');
		return Response::json($ueditor);
	}

	public function getUeditor()
	{
		$action = Input::get('action');
		if($action == 'config'){
			$ueditor = Config::get('ueditor');
			return Response::json($ueditor);
		}
	}

	public function postUeditor()
	{
		$action = Input::get('action');
		if($action == 'image'){
			return $this->image();
		}
	}

	public function getBbsUeditor(){
		$action = Input::get('action');
		if($action == 'config'){
			$ueditor = Config::get('bbsueditor');
			return Response::json($ueditor);
		}
	}

	public function postBbsUeditor()
	{
		$action = Input::get('action');
		if($action == 'image'){
			return $this->bbsImage();
		}
	}

	protected function image($save_path='')
	{
		if(!$save_path) $save_path = '/userdirs/';
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
		$path = storage_path() . $dir;
		$new_filename = date('YmdHis') . str_random(4);

		$ueditor_config = Config::get('ueditor');
		ini_set('upload_max_filesize','4M');
		if(Input::hasFile('upimage')){

			/*
			$file = Input::file('upimage');
            $filename = $file->getClientOriginalName();
		    $ext = $file->getClientOriginalExtension();
		    if(!in_array('.'.$ext,$ueditor_config['imageAllowFiles'])) return $this->returnJson('类型错误');
			$file->move($path,$new_filename . '.' . $ext );
			*/
			$config = array(
				'savePath'=>$save_path,
				'driverConfig'=>array('autoSize'=>array(800,640,480,320))
			);
			$uploader = new ImageHelper($config);
			$image = $uploader->upload('upimage');
			$imagefile = '';
			if($image !== false){
				$imagefile = $image['filepath'] . '/' . $image['filename'];
			}

			return $this->returnJson('SUCCESS',str_replace('.','.',$imagefile));
		}else{
			return $this->returnJson('没有文件被上传');
		}
	}

    public function bbsImage(){
        $up_result = Utility::loadByHttp($this->upload_uri,array('file'=>true),'POST','json','web',$_FILES);
        if($up_result && $up_result['fileName']){
            //上传成功
            return $this->returnJson('SUCCESS',$this->get_uri.$up_result['fileName']);
        }else{
            //上传失败
            return $this->returnJson('文件上传失败');
        }
    }

	protected function returnJson($state,$url='',$title='',$original='')
	{
		$json = array(
			'url'=>$url,
			'title'=>$title,
			'original'=>$original,
			'state'=>$state,
		);
		return Response::json($json);
	}
	
	public function postPlupload()
	{
		$dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
		$path = storage_path() . $dir;
		$save_path = '/userdirs/';
		if(Input::hasFile('filedata')){
		    $config = array(
				'savePath'=>$save_path,
				'driverConfig'=>array('autoSize'=>array(800,640,480,320))
			);
			$uploader = new ImageHelper($config);
			$image = $uploader->upload('filedata');
			$imagefile = '';
			if($image !== false){
				$imagefile = $image['filepath'] . '/' . $image['filename'];
				$imagefile = Utility::getImageUrl($imagefile);
			}
			return $this->json(array('OK'=>1,'file'=>$imagefile));
		}
		return $this->json(array('OK'=>0));
	}
}
