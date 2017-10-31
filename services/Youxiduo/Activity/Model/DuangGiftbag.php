<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/3/11
 * Time: 14:06
 */
namespace Youxiduo\Activity\Model;

use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;
use Youxiduo\V4\Game\GameService;

class DuangGiftbag extends Model implements IModel
{
	public static function getClassName()
	{
		return __CLASS__;
	}

    public static function getInfoByHashcode($hashcode){
		if(!$hashcode) return false;
		return self::db()->where('hashcode',$hashcode)->first();
	}

    public static function getInfoByArticleid($artid){
        if(!$artid) return false;
        return self::db()->where('article_id',$artid)->first();
    }

	public static function getInfo($id){
		if(!$id) return false;
		return self::db()->where('id',$id)->first();
	}

    /**
     * 保存活动信息
     */
    public static function saveInfo($data)
    {
        if(isset($data['id']) && $data['id']>0){
            $id = $data['id'];
            unset($data['id']);
            self::db()->where('id','=',$id)->update($data);
        }else{
            $data['addtime'] = time();
            $id = self::db()->insertGetId($data);
            self::db()->where('id','=',$id)->update(array('hashcode'=>md5($id)));
        }
        return $id;
    }

    public static function search($search,$pageIndex=1,$pageSize=10,$sort=array())
    {
        $total = self::buildSearch($search)->count();
        $result = self::buildSearch($search)->forPage($pageIndex,$pageSize)->orderBy('id','desc')->get();
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
        //开始时间
        if(isset($search['startdate']) && !empty($search['startdate']))
        {
            $tb = $tb->where('addtime','>=',strtotime($search['startdate']));
        }

        //结束时间
        if(isset($search['enddate']) && !empty($search['enddate']))
        {
            $tb = $tb->where('addtime','<=',strtotime($search['enddate']));
        }
        return $tb;
    }

    /**
     * 更新礼包数
     * @param $giftbag_id
     * @param $data
     * @return mixed
     */
    public static function upTotalAndLast($giftbag_id,$data)
    {
        return self::db()->where('id','=',$giftbag_id)->update($data);
    }

    public static function getIsShowAllInfo(){
        return self::db()->where('is_show',1)->get();
    }
}
