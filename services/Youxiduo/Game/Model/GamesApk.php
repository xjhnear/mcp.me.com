<?php
namespace Youxiduo\Game\Model;
use Illuminate\Support\Facades\DB;
use Youxiduo\Base\IModel;
use Youxiduo\Base\Model;

class GamesApk extends Model implements IModel{

    public static function getClassName(){
        return __CLASS__;
    }

    /**
     * @param mixed $id 游戏id
     * @return array
     */
    public static function getGameInfo($id){
        if(!$id) return false;
        $query = self::db();
        if(!is_array($id)){
            $query->where('id',$id);
            return $query->first();
        }else{
            $query->whereIn('id',$id);
            return $query->get();
        }
    }

    /**
     * 通过游戏名查游戏信息
     * @param $gamename
     * @return bool
     */
    public static function getGameNameInfo($gamename){
        if(!$gamename) return false;
        $query = self::db();
        if(!is_array($gamename)){
            $query->where('gname',$gamename);
            return $query->first();
        }else{
            $query->whereIn('gname',$gamename);
            return $query->get();
        }
    }

    /**
     * 查询游戏信息
     * @param array $feilds
     * @return mixed
     */
    public static function  getGameInfos($feilds = array(),$wid = array()){
        $tb = self::db();
        $select = '';
        foreach($feilds as $v){
            $select .= $v . ',';
        }
        $select = $select ? $select.'count(id) as c' : '* , count(id) as c';
        $tb->select(DB::raw($select));
        if($wid){
            $tb->whereNotIn('id',$wid);
        }
        return $tb->groupBy('gname')->having('c','=',1)->orderBy('id','asc')->get();
    }

    /**
     * 修改游戏
     * @param $data
     * @return bool
     */
    public static function upGame($data){
        if(!$data || empty($data['id'])) return false;
        $id = $data['id'];
        unset($data['id']);
        return self::db()->where('id', $id)->update($data);
    }

    //通过游戏ID获取游戏
    public static function getGamePassIDs(array $ids){
        if(!is_array($ids) || empty($ids)) return array();
        return self::db()->whereIn('id', $ids)->get();
    }

    //按照名称查游戏 like
    public static function getGamePassName($gname,$pageIndex,$pageSize)
    {
        if (empty($gname)) return array();
        $tb = self::db()->where('gname', 'like', '%' . $gname . '%');
        $out['totalCount'] = $tb->count();
        $tb = $tb->forPage($pageIndex, $pageSize);
        $out['result'] = $tb->get();
        return $out;
    }


}