<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/19
 * Time: 19:21
 *//**
 * @package Youxiduo
 * @category Android
 * @link http://dev.youxiduo.com
 * @copyright Copyright (c) 2008 Youxiduo.com
 * @license http://www.youxiduo.com/license
 * @since 4.0.0
 *
 */
namespace Youxiduo\Android\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
/**
 * 图片广告模型类
 */
final class AdvLocation extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public static function getOptions()
    {
        return self::db()->lists('location_name','location_identify');
    }

    public static function searchList($pageIndex=1,$pageSize=20)
    {
        $result = self::db()->forPage($pageIndex,$pageSize)->get();
        foreach($result as $key=>$row){
            if($row['data']) $row['data'] = json_decode($row['data'],true);
            $result[$key] = $row;
        }
        return $result;
    }

    public static function getInfo($id)
    {
        $info = self::db()->where('id','=',$id)->first();
        if($info && $info['data']){
            $info['data'] = json_decode($info['data'],true);
        }
        return $info;
    }

    public static function isExists($identify)
    {
        return self::db()->where('location_identify','=',$identify)->first();
    }

    public static function save($id,$location_name,$location_identify,$location_desc,$params=array())
    {
        $data = array(
            'location_name'=>$location_name,
            'location_identify'=>$location_identify,
            'location_desc'=>$location_desc,
            'data'=>json_encode($params));
        if($id){
            return self::db()->where('id','=',$id)->update($data);
        }else{
            return self::db()->insertGetId($data);
        }
    }
}