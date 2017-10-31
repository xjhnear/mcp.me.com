<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:27
 */
namespace Youxiduo\Activity\Model\Variation;
use Youxiduo\Android\Model\Giftbag;
use Youxiduo\Base\Model;
use Youxiduo\Base\IModel;

class GiftbagDepot extends Model implements IModel
{
    public static function getClassName(){
        return __CLASS__;
    }

    public static function add($data){
        if(!$data) return false;
        $my_giftbag = array(
            'game_id' => $data['gid'],
            'is_android' => 1,
            'is_ios' => 0,
            'title' => $data['name'],
            'content' => $data['description'],
            'ctime' => time(),
            'is_show' => 1,
            'is_activity' => 1,
            'condition' => '{"score":"0"}',
            'is_send' => 1
        );
        $db = self::db();
        try{
            self::transaction(function()use($my_giftbag,$data,$db){
                $data['m_giftbag_id'] = Giftbag::m_save($my_giftbag);
                $db->insert($data);
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    public static function getList($name='',$page,$size,$type=array()){
        $query = self::db();
        $name && $query->where('name','like','%'.$name.'%');
        $type && $query->whereIn('type',$type);
        return $query->forPage($page,$size)->orderBy('depot_id','desc')->get();
    }

    public static function getListCount($name='',$type=array()){
        $query = self::db();
        $name && $query->where('name','like','%'.$name.'%');
        $type && $query->whereIn('type',$type);
        return $query->count();
    }

    public static function getInfo($depot_id){
        if(!$depot_id) return false;
        if(is_array($depot_id)){
            return self::db()->whereIn('depot_id',$depot_id)->get();
        }else{
            return self::db()->where('depot_id',$depot_id)->first();
        }
    }

    public static function getInfoByName($name){
        if(!$name) return false;
        return self::db()->where('name',$name)->first();
    }

    public static function update($depot_id,$data){
        if(!$depot_id || !$data) return false;
        $db = self::db();
        try{
            self::transaction(function()use($depot_id,$data,$db){
                $info = GiftbagDepot::getInfo($depot_id);
                $m_data = array(
                    'id' => $info['m_giftbag_id'],
                    'game_id' => $data['gid'],
                    'title' => $data['name'],
                    'content' => $data['description']
                );
                Giftbag::m_save($m_data);
                $db->where('depot_id',$depot_id)->update($data);
            });
        }catch (\Exception $e){
            echo $e;exit;
            return false;
        }
        return true;
    }

    public static function updateSelf($depot_id,$data){
        if(!$depot_id || !$data) return false;
        return self::db()->where('depot_id',$depot_id)->update($data);
    }

    public static function getAllValidDepot(){
        return self::db()->where('valid',1)->get();
    }

    public static function initCardNumber($depot_id){
        if(!$depot_id) return false;
        //总量
        $total = GiftbagList::getListCount($depot_id);
        //获取使用量
        $used_num = GiftbagList::getSendedCardNum($depot_id);
        return self::db()->where('depot_id',$depot_id)->update(array('total_num'=>$total,'last_num'=>$total-$used_num));
    }

    public static function delete($depot_id){
        if(!$depot_id) return false;
        $info = self::getInfo($depot_id);
        $db = self::db();
        try{
            self::transaction(function()use($depot_id,$info,$db){
                Giftbag::m_delete($info['m_giftbag_id']);
                return $db->where('depot_id',$depot_id)->delete();
            });
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    /**
     * 礼包仓库列表搜索目标ids
     * @param $name
     * @param array $type
     * @return mixed
     */
    public static function getSearchDepotids($name,$type=array()){
        $query = self::db();
        $name && $query->where('name','like','%'.$name.'%');
        $type && $query->whereIn('type',$type);
        return $query->lists('depot_id');
    }
}
