<?php
namespace Youxiduo\Game\Model;
use Youxiduo\Base\IModel;
use Youxiduo\Base\Model;

class Games extends Model implements IModel{

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
}