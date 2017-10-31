<?php
namespace modules\system\controllers;

use Yxd\Utility\ImageHelper;

use Yxd\Modules\Core\CacheService;

use Yxd\Modules\System\SettingService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\BackendController;



class PictureSettingController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'system';
	}
	
	public function getConfig()
	{
		$data = array();
		$config = SettingService::getConfig('home_picture_setting');
		$data['config'] = $config ? $config['data'] : array();
		return $this->display('picture-setting',$data);
	}
	
	public function postSave()
	{
		$config = SettingService::getConfig('home_picture_setting');
		$data = $config ? $config['data'] : array();
	    $dir = '/userdirs/' . date('Y') . '/' . date('m') . '/';
	    $path = storage_path() . $dir;
	    
	    if(Input::hasFile('yugao')){	    	
			$file = Input::file('yugao'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['yugao'] = $dir . $new_filename . '.' . $mime;
		}
		
	    if(Input::hasFile('zixun')){		    	
			$file = Input::file('zixun'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['zixun'] = $dir . $new_filename . '.' . $mime;
		}
		
	    if(Input::hasFile('shipin')){	    	
			$file = Input::file('shipin'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['shipin'] = $dir . $new_filename . '.' . $mime;
		}
		
	    if(Input::hasFile('zhuanti')){	    	
			$file = Input::file('zhuanti'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['zhuanti'] = $dir . $new_filename . '.' . $mime;
		}
		
	    if(Input::hasFile('biwan')){	    	
			$file = Input::file('biwan'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['biwan'] = $dir . $new_filename . '.' . $mime;
		}
		
	    if(Input::hasFile('plaza_1')){	    	
			$file = Input::file('plaza_1'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['plaza_1'] = $dir . $new_filename . '.' . $mime;
		}
		
	    if(Input::hasFile('plaza_2')){	    	
			$file = Input::file('plaza_2'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['plaza_2'] = $dir . $new_filename . '.' . $mime;
		}
		
	    if(Input::hasFile('plaza_3')){	    	
			$file = Input::file('plaza_3'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['plaza_3'] = $dir . $new_filename . '.' . $mime;
		}
		
	    if(Input::hasFile('shop_topbar')){	    	
			$file = Input::file('shop_topbar'); 
			$new_filename = date('YmdHis') . str_random(4);
			$mime = $file->getClientOriginalExtension();			
			$file->move($path,$new_filename . '.' . $mime );
			$data['shop_topbar'] = $dir . $new_filename . '.' . $mime;
		}
		
		SettingService::setConfig('home_picture_setting',$data);
		CacheService::forget('page::home');
		return $this->redirect('system/picture/config')->with('global_tips','图片上传成功');
	}
}