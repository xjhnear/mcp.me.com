<?php
/**
 * @package Youxiduo
 * @category Android 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */

namespace Youxiduo\Android;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Youxiduo\Helper\Easemob;


class EmchatService extends BaseService
{
	
	public static function init()
	{
		$config = array(
		    'client_id'=>'',
		    'client_secret'=>'',
		    'org_name'=>'yxdadmin1',
		    'app_name'=>'yxd',
		    'url'=>'https://a1.easemob.com'
		);
		
		$chat = new Easemob($config);
	}
	
	public static function deactivateUser($uid)
	{
		
	}
}