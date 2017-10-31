<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/1/5
 * Time: 16:06
 */
namespace Youxiduo\Bbs\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class BbsAppend extends Model implements IModel{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function add($data){
        if(!$data) return false;
        return self::db()->insert($data);
    }

    public static function update($data,$val){
        if(!$data) return false;
        return self::db()->where('fid',$val)->update($data);
    }

    public static function getBbsinfoByFid($val){
        if(!$val) return false;
        return self::db()->where('fid',$val)->first();
    }
    
}