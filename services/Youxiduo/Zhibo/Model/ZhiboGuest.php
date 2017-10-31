<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/5/14
 * Time: 14:39
 */
namespace Youxiduo\Zhibo\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Youxiduo\Helper\Utility;


/**
 * 直播游戏
 */
final class ZhiboGuest extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public static function save($data){
        if(!$data) return false;
        if(isset($data['id']) && !empty($data['id'])){
            $id = $data['id'];
            unset($data['id']);
            return self::db()->where('id',$id)->update($data);
        }else{
            unset($data['id']);
            return self::db()->insertGetId($data);
        }
    }

    public static function getList($page = 1 , $pagesize = 10 , $feilds = array(),$where = array()){
        $tb = self::db();
        if(is_array($where)){
            foreach($where as $k=>$v){
                $tb->where($k,'like','%'.$v.'%');
            }
        }
        if(!empty($feilds)){
            $tb->select(self::raw($feilds));
        }
        $out['total'] = $tb->count();
        $out['result'] = $tb->forPage($page,$pagesize)->orderBy('id','desc')->get();
        return $out;
    }

    public static function getDetail($id){
        if(!$id) return false;
        $result = self::db()->where('id',$id)->first();
        $result['webpic'] = !empty($result['webpic']) ? Utility::getImageUrl($result['webpic']) : $result['webpic'];
        $result['h5pic'] = !empty($result['h5pic']) ? Utility::getImageUrl($result['h5pic']) : $result['h5pic'];
        return $result;
    }

    public static function getDel($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->delete();
    }

}