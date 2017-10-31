<?php
namespace modules\statistics\models;

use Illuminate\Support\Facades\Config;
use Youxiduo\Base\BaseService;
use Youxiduo\Helper\Utility;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Youxiduo\V4\Activity\Model\ChannelClick;
use Youxiduo\V4\Activity\Model\DownloadChannel;
use Youxiduo\V4\Activity\Model\StatisticConfig;

class MonitorService extends \Youxiduo\V4\Common\MonitorService
{
	public static function searchChannel($search,$pageIndex=1,$pageSize=10)
	{
		$total = self::buildSearchChannel($search)->count();
		$result = self::buildSearchChannel($search)->orderBy('CREATE_TIME','DESC')->forPage($pageIndex,$pageSize)->get();
		return array('result'=>$result,'total'=>$total);
	}
	
	protected static function buildSearchChannel($search)
	{
		$tb = DownloadChannel::db();
		if(isset($search['channel_id']) && $search['channel_id']){
			$tb = $tb->where('CHANNEL_ID','=',$search['channel_id']);
		}
	    if(isset($search['channel_name']) && $search['channel_name']){
			$tb = $tb->where('CHANNEL_NAME','like','%'.$search['channel_name'].'%');
		}
		return $tb;
	}
	
    public static function searchConfig($search,$pageIndex=1,$pageSize=10,$sortField='CREATE_TIME')
	{
		$total = self::buildSearchConfig($search)->count();
		$result = self::buildSearchConfig($search)->orderBy($sortField,'DESC')->forPage($pageIndex,$pageSize)->get();
		return array('result'=>$result,'total'=>$total);
	}
	
	protected static function buildSearchConfig($search)
	{
		$tb = StatisticConfig::db();
		if(isset($search['channel_id']) && $search['channel_id']){
			$tb = $tb->where('CHANNEL_ID','=',$search['channel_id']);
		}
	    if(isset($search['config_id']) && $search['config_id']){
			$tb = $tb->where('CONFIG_ID','=',$search['config_id']);
		}
	    if(isset($search['config_name']) && $search['config_name']){
			$tb = $tb->where('CONFIG_NAME','like','%'.$search['config_name'].'%');
		}
		return $tb;
	}
	
    public static function searchClick($search,$pageIndex=1,$pageSize=10,$sortField='CLICK_TIME')
	{
		$total = self::buildSearchClick($search)->count();
		$result = self::buildSearchClick($search)->orderBy($sortField,'DESC')->forPage($pageIndex,$pageSize)->get();
		return array('result'=>$result,'total'=>$total);
	}
	
	protected static function buildSearchClick($search)
	{
		$tb = ChannelClick::db();
	    if(isset($search['channel_id']) && $search['channel_id']){
			$config_ids = StatisticConfig::db()->where('CHANNEL_ID','=',$search['channel_id'])->lists('CONFIG_ID');
			if($config_ids && is_array($config_ids)){
				$tb = $tb->whereIn('CONFIG_ID',$config_ids);
			}
		}
	    if(isset($search['config_id']) && $search['config_id']){
			$tb = $tb->where('CONFIG_ID','=',$search['config_id']);
		}
	    if(isset($search['click_ip']) && $search['click_ip']){
			$tb = $tb->where('CLICK_IP','=',$search['click_ip']);
		}
		
		if(isset($search['start']) && $search['start']){
			$tb = $tb->where('CLICK_TIME','>=',$search['start']);
		}
	    if(isset($search['end']) && $search['end']){
			$tb = $tb->where('CLICK_TIME','<=',$search['end']);
		}
		return $tb;
	}
	
    public static function searchActive($search,$pageIndex=1,$pageSize=10,$sortField='ACTIVE_TIME')
	{
		$total = self::buildSearchActive($search)->count();
		$result = self::buildSearchActive($search)->orderBy($sortField,'DESC')->forPage($pageIndex,$pageSize)->get();
		return array('result'=>$result,'total'=>$total);
	}
	
	protected static function buildSearchActive($search)
	{
		$tb = ChannelClick::db()->where('IS_ACTIVE','=',1);
	    if(isset($search['config_id']) && $search['config_id']){
			$tb = $tb->where('CONFIG_ID','=',$search['config_id']);
		}
		if(isset($search['channel_id']) && $search['channel_id']){
			$config_ids = StatisticConfig::db()->where('CHANNEL_ID','=',$search['channel_id'])->lists('CONFIG_ID');
			if($config_ids && is_array($config_ids)){
				$tb = $tb->whereIn('CONFIG_ID',$config_ids);
			}
		}
	    if(isset($search['click_ip']) && $search['click_ip']){
			$tb = $tb->where('CLICK_IP','=',$search['click_ip']);
		}
	    if(isset($search['start']) && $search['start']){
			$tb = $tb->where('ACTIVE_TIME','>=',$search['start']);
		}
	    if(isset($search['end']) && $search['end']){
			$tb = $tb->where('ACTIVE_TIME','<=',$search['end']);
		}
		return $tb;
	}
}