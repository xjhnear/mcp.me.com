<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/13
 * Time: 16:12
 */

namespace Youxiduo\Activity\Model;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class DuangPic extends Model implements IModel
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public static function getInfo($id){
        if(!$id) return false;
        return self::db()->where('id',$id)->first();
    }

    /**
     * 保存图片信息
     */
    public static function saveInfo($data)
    {
        if(isset($data['id']) && $data['id']>0){
            $id = $data['id'];
            unset($data['id']);
            self::db()->where('id','=',$id)->update($data);
        }else{
            unset($data['id']);
            $id = self::db()->insertGetId($data);
        }
        return $id;
    }

    public static function search($search,$pageIndex=1,$pageSize=10,$sort=array())
    {
        $website = Config::get('app.img_url');
        $total = self::buildSearch($search)->count();
        $result = self::buildSearch($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
        foreach($result as &$v){
            if(!empty($v['img'])) $v['img'] = $website . $v['img'];
        }

        return array('result'=>$result,'totalCount'=>$total);
    }

    protected static function buildSearch($search)
    {
        $tb = self::db();
        if(isset($search['title'])){
            $tb = $tb->where('title','like',$search['title']);
        }
        if(isset($search['is_show']) && !empty($search['is_show']))
        {
            $tb = $tb->where('is_show','=',$search['is_show']);
        }
        return $tb;
    }

    public static function getShowPics(){
        return self::db()->where('is_show',1)->orderBy('sort','desc')->get();
    }
}