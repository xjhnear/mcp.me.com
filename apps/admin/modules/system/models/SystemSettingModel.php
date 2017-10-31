<?php
namespace modules\system\models;
use Yxd\Modules\Core\BaseModel;
use Yxd\Modules\System\SettingService;

class SystemSettingModel extends BaseModel
{
	public static function getConfig($keyname='')
	{
		return SettingService::getConfig($keyname);
	}
	
	public static function setConfig($keyname,$data)
	{
		return SettingService::setConfig($keyname, $data);
	}
	
}