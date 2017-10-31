<?php
namespace modules\activity\controllers;
use modules\activity\models\HuntModel;

use modules\activity\models\ActivityModel;

use Yxd\Services\Cms\GameService;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;

use Yxd\Modules\Core\BackendController;
use modules\forum\models\ChannelModel;
use modules\forum\models\TopicModel;

class RuleController extends BackendController
{
	public function _initialize()
	{
		$this->current_module = 'activity';
	}
	
    public function getEdit($type,$act_id,$tid=0)
	{
		$data = array();
		$data['type'] = $type;
		$data['act_id'] = $act_id;
		if($tid){
			$rule = TopicModel::getRuleInfo($tid);			
			$data['rule'] = $rule;
		}
		return $this->display('rule-edit',$data);
	}
	
	public function postSave()
	{
		$tid = (int)Input::get('tid');//帖子ID
		$act_id = (int)Input::get('act_id');//活动ID
		$type = Input::get('type');//活动类型
		$subject = Input::get('subject');//主题
		$message = Input::get('format_message');//内容
		$uid = 1;//发帖人
		$res = TopicModel::saveRuleTopic($tid,$subject,$message,$uid);
		if($res){
			
			if($type=='hunt'){
				HuntModel::updateRule($act_id, $res);
				return $this->redirect('activity/rule/edit/'.$type.'/'.$act_id.'/'.$res)->with('global_tips','规则保存成功');
			}else{
				ActivityModel::updateRule($act_id, $res);
			    return $this->redirect('activity/rule/edit/'.$type.'/'.$act_id.'/'.$res)->with('global_tips','规则保存成功');
			}
		}else{
			return $this->back()->with('global_tips','公告保存失败');
		}
	}
}