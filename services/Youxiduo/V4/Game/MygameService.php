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

namespace Youxiduo\V4\Game; 
use Youxiduo\Base\BaseService;
use Youxiduo\V4\Game\Model\Tag;
use Youxiduo\V4\Game\Model\Gametype;
use Youxiduo\V4\Game\Model\GameMustPlay;
use Illuminate\Support\Facades\Paginator;
final class MygameService  extends BaseService
{
   	
    public static function getGameTypeList($search=array())
    {
        return Gametype::pagelist($search['page'],$search['pageSize'],Gametype::setsearch($search),array(),'sort');
    }



    public static function getGameTypeInfo($id,$key='')
    {
        return empty($key)?Gametype::getInfo('Id',$id):Gametype::getinfos($key,$id);
    }

    //专题数据详情获取
    public static function getTagInfo($id,$key=''){
        return empty($key)?Tag::getInfo('Id',$id):Tag::getinfos($key,$id);
    }


    public static function deltag($id)
    {

        return Tag::delete('typeid', '=', $id);
    }

    public static function insertTag($datainfo)
    {
        return Tag::insert($datainfo);
    }

    public static function updateGametype($datainfo,$id=0)
    {      
        return Gametype::update($datainfo,$id,'id');//game_type
    }

    public static function getGameMustPlayList($platform='',$pageIndex,$pageSize)
    {
        return  GameMustPlay::getList($platform,$pageIndex,$pageSize);
    }

    public static function getGameMustPlayCount($platform='')
    {
        return  GameMustPlay::getCount($platform);
    }

    public static function  setGameMustPlay($datainfo=array(),$id=0)
    {
        return  GameMustPlay::update($datainfo,$id);
    }

 	/**处理返回数据**/
    public static function _processingInterface($result,$data,$pagesize=10,$is_ajax=0){
        $data['search']=$data;
        $data['datalist']  = !empty($result['result'])?$result['result']:array();
        $data['totalCount']=$result['totalCount'];
        if($is_ajax == 1){
            return $data;
        } 
        $pager = Paginator::make(array(),!empty($result['totalCount'])?$result['totalCount']:0,$pagesize);
        unset($data['search']['pageIndex'],$data['search']['page']);
        $pager->appends($data['search']);
        $data['pagelinks'] = $pager->links();
        return $data;
    }

}