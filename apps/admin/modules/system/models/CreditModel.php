<?php
namespace modules\system\models;
use Yxd\Modules\Core\BaseModel;
use modules\forum\models\TopicModel;

class CreditModel extends BaseModel
{
	/**
	 * 获取积分设置列表
	 */
	public static function getList()
	{
		return self::dbClubSlave()->table('credit_setting')->get();
	}
	
	/**
	 * 获取积分设置项信息
	 */
	public static function getInfo($id)
	{
		return self::dbClubSlave()->table('credit_setting')->where('id','=',$id)->first();
	}
	
	/**
	 * 保存积分设置项信息
	 */
	public static function save($data)
	{
		if(isset($data['id']) && $data['id']>0){
			$id = $data['id'];
			unset($data['id']);
			return self::dbClubMaster()->table('credit_setting')->where('id','=',$id)->update($data);
		}else{
			return self::dbClubMaster()->table('credit_setting')->insertGetId($data);
		}
	}
	
	public static function getRuleInfo()
	{
		$rule = SystemSettingModel::getConfig('credit_rule');
		
		if($rule && isset($rule['data']['rule_id'])){
			$tid = $rule['data']['rule_id'];
			$topic = TopicModel::getRuleInfo($tid);
			
			return $topic;
		}
		return array();
	}
	
	public static function saveRule($tid,$subject,$message,$uid)
	{
		$res = TopicModel::saveRuleTopic($tid,$subject,$message,$uid);
		if($res){
			SystemSettingModel::setConfig('credit_rule',array('rule_id'=>$res));
			return true;
		}
		return false;
	}
}