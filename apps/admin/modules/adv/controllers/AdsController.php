<?php
namespace modules\adv\controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use modules\adv\models\AdvModel;
use libraries\Helpers;
use Youxiduo\Android\Model\AppAdv;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Yxd\Modules\Core\BackendController;

class AdsController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'adv';
	}
	
	public function getList($type)
	{
		$search = array('type'=>$type,'version'=>'3.0.0');
		$advtype = Config::get('yxd.advtype');
        $apptype = Config::get('yxd.applist');
		$data = array();
		$data['advtype'] = $advtype[$type];
		$data['type'] = $type;
		$data['imgurl'] = Config::get('app.img_url');
        $data['apptype'] = $apptype;
		$data['datalist'] = AdvModel::search($search);
		return $this->display('ads-list',$data);
	}	
	
	public function getCreate($type)
	{
		$advtype = Config::get('yxd.advtype');
		$applist = Config::get('yxd.applist');
		$data = array();
		$data['applist'] = $applist;
		$data['versionlist'] = Config::get('yxd.app_version');
		$data['location'] = $this->getLocation($type);
		$data['advtype'] = $advtype[$type];
		$data['type'] = $type;
		return $this->display('ads-edit',$data);
	}
	
	public function getEdit($id)
	{
		$advtype = Config::get('yxd.advtype');
		$applist = Config::get('yxd.applist');
		$data = array();
		$adv = AdvModel::getInfo($id);
		$type = $adv['type'];
		$data['applist'] = $applist;		
		$data['versionlist'] = Config::get('yxd.app_version');
		$data['location'] = $this->getLocation($type);
		$data['advtype'] = $advtype[$type];
		$data['type'] = $type;
		$data['adv'] = $adv;
		return $this->display('ads-edit',$data);
	}

	public function postEdit()
	{
        $input = Input::all();
        $ad_id = Input::get('adid');

        $validate = self::advValidate($input);
        if(!$validate['pass']) return Redirect::to('adv/ads/edit/'.$ad_id)->withInput()->withErrors($validate['validator']);

        $type = $input['type'];

        if($type != 4){
            $file = $input['litpic'];
        }else{
            $file = $input['bigpic'];
        }

        $data = array(
            'title' => isset($input['title']) ? $input['title'] : '',
            'appname' => $input['appname'],
            'version' => $input['version'],
            'type' => $input['type'],
            'location' => $input['location'],
            'aid' => $input['aid'],
            'gid' => isset($input['gid']) ? $input['gid'] : 0,
            'advname' => isset($input['advname']) ? $input['advname'] : '',
            'downurl' => $input['downurl'],
            'url' => $input['url'],
            'sendmac' => isset($input['sendmac']) ? $input['sendmac'] : '',
            'sendidfa' => isset($input['sendidfa']) ?  $input['sendidfa'] : '',
            'sendudid' => isset($input['sendudid']) ? $input['sendudid'] : '',
            'sendos' => isset($input['sendos']) ? $input['sendos'] : '',
            'sendplat' => isset($input['sendplat']) ? $input['sendplat'] : '',
            'sendactive' => isset($input['sendactive']) ? $input['sendactive'] : '',
            'addtime' => time(),
            'tosafari' => $input['tosafari']
        );

        if($file){
            $dir = '/u/advpic/' . date('Y') . date('m') . '/';
            $picpath = Helpers::uploadPic($dir, $file);

            if($type != 4){
                $data['litpic'] = $picpath;
            }else{
                $data['bigpic'] = $picpath;
            }
        }


        if(AppAdv::update($ad_id,$data)){
            return $this->back()->with('global_tips','更新成功！');
        }else{
            return $this->back()->with('global_tips','更新失败，请重试！');
        }
	}

    /**
     * 广告提交表单验证
     * @param $input_data
     * @return array
     */
    private function advValidate($input_data){
        $input = $input_data;
        unset($input_data['litpic']);
        unset($input_data['bigpic']);
        $rule = array(
            'version' => 'required',    //版本
            'location' => 'required',   //广告位置
            'downurl' => 'required',    //跳转地址,
            'tosafari' => 'required',   //浏览器类型
        );

        if(!$input_data['has_pic']){
            if($input_data['type'] != 4){
                $rule['litpic'] = 'required';
            }else{
                $rule['bigpic'] = 'required';
            }

        }

        if (isset($input_data['gid'])) {
            $rule['aid'] = 'required|lo_exist:'.serialize($input_data).'|lo_aid_exist:'.serialize($input_data).'|lo_gid_exist:'.serialize($input_data);
        }else{
            $rule['aid'] = 'required|lo_exist:'.serialize($input_data).'|lo_aid_exist:'.serialize($input_data);
        }

        $message = array(
            'required' => '不能为空',
            'lo_exist' => '广告位置已经存在',
            'lo_aid_exist' => '广告位置和广告标识已经存在',
            'lo_gid_exist' => '广告位置和游戏ID已经存在'
        );

        //判断广告位置是否存在
        /*
        Validator::extend('lo_exist',function($attr,$value,$param){
            $param = unserialize(current($param));
            $conditions = [
                ['appname','=',$param['appname']],
                ['version','=',$param['version']],
                ['location','=',$param['location']],
                ['id','<>',$param['adid']],
                ['type','<>',4]
            ];
            $lo_exist = AppAdv::getByMultiCondition($conditions);
            return $lo_exist ? false : true;
        });

        //判断位置和标识是否存在
        Validator::extend('lo_aid_exist', function($attr, $value, $param)
        {
            $param = unserialize(current($param));
            $conditions = [
                ['appname','=',$param['appname']],
                ['version','=',$param['version']],
                ['location','=',$param['location']],
                ['aid','=',$param['aid']],
                ['id','<>',$param['adid']],
                ['type','<>',4]
            ];
            $lo_aid_exist = AppAdv::getByMultiCondition($conditions);
            return $lo_aid_exist ? false : true;
        });        

        //广告位置和游戏ID是否存在
        Validator::extend('lo_gid_exist', function($attr, $value, $param)
        {
            $param = unserialize(current($param));
            $conditions = [
                ['appname','=',$param['appname']],
                ['version','=',$param['version']],
                ['location','=',$param['location']],
                ['aid','=',$param['aid']],
                ['id','<>',$param['adid']],
                ['type','<>',4],
                ['gid','=',$param['gid']]
            ];
            $lo_gid_exist = AppAdv::getByMultiCondition($conditions);

            return $lo_gid_exist ? false : true;
        });
        */
        $validator = Validator::make($input,$rule,$message);

        if($validator->fails()){
            $pass = false;
        }else{
            $pass = true;
        }

        return array('pass'=>$pass,'validator'=>$validator);
    }
	
	/**
	 * 
	 */
	protected function getLocation($type)
	{
		$advtype = Config::get('yxd.advtype');
		$typename = $advtype[$type];
		$list = array();
		$type = (int)$type;
		switch($type){
			case 1:
				for($i=1;$i<=5;$i++){
					$list[$i] = $typename . $i;
				}
				break;
			case 2:
		        for($i=11;$i<=26;$i++){
					$list[$i] = $typename . $i;
				}
				break;
			case 3:
				$list = array('21'=>$typename . '21');
				break;
			case 4:
				$list = array('23'=>$typename . '23');
				break;
			case 5:
				$list = array('22'=>$typename . '22');
				break;
			case 6:
				$list = array('24'=>$typename . '24');
				break;
			case 7:
				$list = array('26'=>$typename . '26');
				break;
			case 8:
				$list = array('25'=>$typename . '25');
				break;						
		}
		
		return $list;
	}
}