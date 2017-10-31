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
use Illuminate\Support\Facades\DB;

class DuangGiftbagCard extends Model implements IModel
{
	public static function getClassName()
	{
		return __CLASS__;
	}

    /**
     * 批量导入礼包卡
     */
    public static function importCardNoList($giftbag_id,array $cards)
    {
        $tb = self::db();
        $result = DB::transaction(function()use($tb,$cards,$giftbag_id){
            $total = count($cards);
            $addtime = time();
            if($total>500){
                $batch_card = array_chunk($cards,500);
                foreach($batch_card as $group){
                    $table = array();
                    foreach($group as $cardno){
                        $table[] = array(
                            'giftbag_id'=>$giftbag_id,
                            'cardno'=>$cardno['cardno'],
                            'addtime'=>$addtime
                        );
                    }
                    if($table){
                        $tb->insert($table);
                        DuangGiftbagCard::initCardNoNumber($giftbag_id);
                    }
                }
                return true;
            }else{
                $table = array();
                foreach($cards as $cardno){
                    $table[] =  array('giftbag_id'=>$giftbag_id,'cardno'=>$cardno['cardno'],'addtime'=>$addtime);
                }
                if($table){
                    $tb->insert($table);
                    DuangGiftbagCard::initCardNoNumber($giftbag_id);
                    return true;
                }
                return false;
            }
        });
        return $result;
    }


    /**
     * 获取礼包卡列表
     */
    public static function getCardNoList($giftbag_id)
    {
        return self::db()->where('giftbag_id','=',$giftbag_id)->lists('cardno');
    }

    public static function initCardNoNumber($giftbag_id)
    {
        $total_num = self::db()->where('giftbag_id','=',$giftbag_id)->count();
        $last_num = self::db()->where('giftbag_id','=',$giftbag_id)->whereNull('user_id')->count();
        return DuangGiftbag::upTotalAndLast($giftbag_id,array('total_num'=>$total_num,'last_num'=>$last_num));
    }

    /**
     * 搜索礼包卡
     */
    public static function searchCardNoList($search,$pageIndex=1,$pageSize=10,$sort=array())
    {
        $out = array();
        $out['totalCount'] = self::buildSearchCardNo($search)->count();
        $tb = self::buildSearchCardNo($search)->forPage($pageIndex,$pageSize);
        foreach($sort as $field=>$order){
            $tb = $tb->orderBy($field,$order);
        }
        $out['result'] = $tb->get();
        return $out;
    }

    protected static function buildSearchCardNo($search)
    {
        $tb = self::db();
        if(isset($search['giftbag_id'])){
            $tb = $tb->where('giftbag_id','=',$search['giftbag_id']);
        }
        if(isset($search['is_get'])){
            $tb = $tb->where('is_get','=',$search['is_get']);
        }
        //用户
        if(isset($search['uid']) && !empty($search['uid']))
        {
            $tb = $tb->where('user_id','=',$search['uid']);
        }
        //开始时间
        if(isset($search['startdate']) && !empty($search['startdate']))
        {
            $tb = $tb->where('addtime','>=',strtotime($search['startdate'] . '00:00:00'));
        }

        //结束时间
        if(isset($search['enddate']) && !empty($search['enddate']))
        {
            $tb = $tb->where('addtime','<=',strtotime($search['enddate'] . '23:59:59'));
        }
        return $tb;
    }

    /**
     * 删除卡号
     */
    public static function deleteCardNo($id)
    {
        return self::db()->where('id','=',$id)->delete();
    }


	public static function getCardInfo($giftbag_id,$is_get){
		$query = self::db();
		$query->where('giftbag_id',$giftbag_id);
		$query->where('is_get',$is_get);
		$query->orderBy('id','asc');
		return $query->first();
	}

    public static function getCardInfoByCardno($cardno){
        if(!$cardno) return false;
        return self::db()->where('cardno',$cardno)->first();
    }

    public static function getValidCardInfoByCardno($cardno){
        if(!$cardno) return false;
        return self::db()->where('cardno',$cardno)->where('is_send',0)->first();
    }

	public static function updateCard($id,$data){
		if(!$id || !$data) return false;
		return self::db()->where('id',$id)->update($data);
	}

    public static function robUpdateCard($id,$data){
        if(!$id || !$data) return false;
		return self::db()->where('is_get',0)->where('id',$id)->update($data);
    }
}
