<?php
/**
 * @package Youxiduo
 * @category Cms 
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com 
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Cms\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 模型类
 */
final class Addongame extends Model implements IModel
{	
    public static function getClassName()
	{
		return __CLASS__;
	}
	public static function getMobileGame($id){
		return self::db() -> where('aid',$id) -> first();
	}

    /**
     * 修改游戏附加表信息
     * @param array $data
     * @return bool
     */
    public static function save(array $data){
        if(!$data || empty($data['aid'])) return false;
        $id = $data['aid'];
        unset($data['aid']);
        return self::db()->where('aid',$id)->update($data);
    }

}