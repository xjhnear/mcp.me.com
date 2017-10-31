<?php
namespace modules\wcms\models;

use Illuminate\Support\Facades\Log;
use Yxd\Modules\Core\CacheService;

class HomeSetting extends BaseHttp
{

	/**
	 * 顶部广告
	 */
	public static function GetTopAdvertisingPic()
	{
		$api_url = self::HOST_URL . 'GetTopAdvertisingPic';

		$result = self::http($api_url, null, 'GET');
		if ($result !== false && $result['errorCode'] == 0) {
			return isset($result['result']) ? $result['result'] : array();
		}
		return array();
	}

	/**
	 * 顶部广告
	 */
	public static function SaveTopAdvertisingPic($smallPicUrl, $bigPicTopUrl, $bigPicBottomUrl, $url)
	{
		$api_url = self::HOST_URL . 'SaveTopAdvertisingPic';
		$params = array();

		$params['smallPicUrl'] = $smallPicUrl;
		$params['bigPicTopUrl'] = $bigPicTopUrl;
		$params['bigPicBottomUrl'] = $bigPicBottomUrl;
		$params['url'] = $url;

		$result = self::http($api_url, $params, 'POST');
		if ($result !== false && $result['errorCode'] == 0) {
			return true;
		}
		return false;
	}

	/**
	 * 获取首页背投轮播图列表
	 */
	public static function GetIndexFirstProjection()
	{
		$api_url = self::HOST_URL . 'GetIndexFirstProjection';

		$result = self::http($api_url, null, 'GET');
		if ($result !== false && $result['errorCode'] == 0) {
			return $result['result'];
		}
		return array();
	}

	/**
	 * 保存首页背投轮播图数据
	 * @param $articleId
	 * @param $picUrl
	 * @param $idx
	 * @param null $title
	 * @param null $summary
	 * @param null $gameId
	 * @param null $url
	 * @param bool|false $containVideo
	 * @param string $videoUrl
	 * @param int $videoTime
	 * @param int $autoPlay
	 * @return bool
	 */
	public static function SaveIndexFirstProjection($articleId, $picUrl, $idx, $title = null, $summary = null, $gameId = null, $url = null, $containVideo = false, $videoUrl = '', $videoTime = 0, $autoPlay = 0)
	{
		$api_url = self::HOST_URL . 'SaveIndexFirstProjection';
		$params = array();

		$params['picUrl'] = $picUrl;
		$params['idx'] = $idx;

		if ($articleId) {
			$params['articleId'] = $articleId;
		} else {
			$params['title'] = $title;
			$params['gameId'] = $gameId;
			$params['summary'] = $summary;
			$params['url'] = $url;
			$params['containVideo'] = $containVideo;
			$params['videoUrl'] = $videoUrl;
			$params['videoTime'] = $videoTime;
			$params['autoPlay'] = $autoPlay;
		}
		$result = self::http($api_url, $params, 'POST');
		if ($result !== false && $result['errorCode'] == 0) {
			return true;
		}
		return false;
	}

	/**
	 * 删除首页背投轮播图
	 * @param $idx
	 * @return bool
	 */
	public static function DeleteIndexFirstProjection($idx)
	{
		$api_url = self::HOST_URL . 'DeleteIndexFirstProjection';
		$params['idx'] = $idx;
		$result = self::http($api_url, $params, 'GET');
		if ($result !== false && $result['errorCode'] == 0) {
			return true;
		}
		return false;
	}
	
    /**
	 * 获取首页首屏轮播图列表
	 */
	public static function GetIndexDuoFocus()
	{
		$api_url = self::HOST_URL . 'GetIndexDuoFocus';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}

	/**
	 * 保存首页首屏轮播图数据
	 * @param $articleId
	 * @param $picUrl
	 * @param $idx
	 * @param null $title
	 * @param null $summary
	 * @param null $gameId
	 * @param null $url
	 * @return bool
	 */
	public static function SaveIndexDuoFocus($articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$api_url = self::HOST_URL . 'SaveIndexDuoFocus';
		$params = array();
		
		$params['picUrl'] = $picUrl;
		$params['idx'] = $idx;
		
		if($articleId) {
			$params['articleId'] = $articleId;
		}else{
			$params['title'] = $title;
			$params['gameId'] = $gameId;
			$params['summary'] = $summary;
			$params['url'] = $url;
		}
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}

	/**
	 * 删除首页多焦点图
	 * @param $idx
	 * @return bool
	 */
	public static function DeleteIndexDuoFocus($idx)
	{
		$api_url = self::HOST_URL . 'DeleteIndexDuoFocus';
		$params['idx'] = $idx;
		$result = self::http($api_url, $params, 'GET');
		if ($result !== false && $result['errorCode'] == 0) {
			return true;
		}
		return false;
	}
	
    /**
	 * 获取首页首屏图推列表
	 */
	public static function GetIndexDuoPicRecommend()
	{
		$api_url = self::HOST_URL . 'GetIndexDuoPicRecommend';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}

	/**
	 * 保存首页首屏图推数据
	 * @param $articleId
	 * @param $picUrl
	 * @param $idx
	 * @param null $title
	 * @param null $summary
	 * @param null $gameId
	 * @param null $url
	 * @return bool
	 */
	public static function SaveIndexDuoPicRecommend($articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$api_url = self::HOST_URL . 'SaveIndexDuoPicRecommend';
		$params = array();
		
		$params['picUrl'] = $picUrl;
		$params['idx'] = $idx;
		
		if($articleId) {
			$params['articleId'] = $articleId;
		}else{
			$params['title'] = $title;
			$params['gameId'] = $gameId;
			$params['summary'] = $summary;
			$params['url'] = $url;
		}
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}

	public static function SaveIndexDuoPicRecommendSort($updated_idx)
	{
		$old_data = self::GetIndexDuoPicRecommend();
		if(CacheService::file()->has('cache_www_index_duo_pic_recommend')==false){
			CacheService::file()->put('cache_www_index_duo_pic_recommend',$old_data,60);
		}

		$old_idx_data = array();
		foreach($old_data as $row){
			$old_idx_data[$row['idx']] = $row;
		}
		$success = true;
		foreach($updated_idx as $old_idx=>$new_idx){
			$data = $old_idx_data[$new_idx];
			$articleId = isset($data['articleId']) ? $data['articleId'] : null;
			$picUrl = isset($data['picUrl']) ? $data['picUrl'] : null;
			//$idx = isset($data['idx']) ? $data['idx'] : null;
			$title = isset($data['title']) ? $data['title'] : null;
			$summary = isset($data['summary']) ? $data['summary'] : null;
			$gameId = isset($data['gameId']) ? $data['gameId'] : null;
			$url = isset($data['url']) ? $data['url'] : null;
			$success = self::SaveIndexDuoPicRecommend($articleId,$picUrl,$old_idx,$title,$summary,$gameId,$url);
			if($success===false) break;
		}
		if($success==true) CacheService::file()->forget('cache_www_index_duo_pic_recommend');
		return $success;
	}

	public static function RecoveryIndexDuoPicRecommend()
	{
		$old_data = CacheService::file()->get('cache_www_index_duo_pic_recommend');
		$success = true;
		if($old_data){
			foreach($old_data as $data){
				$articleId = isset($data['articleId']) ? $data['articleId'] : null;
				$picUrl = isset($data['picUrl']) ? $data['picUrl'] : null;
				$idx = isset($data['idx']) ? $data['idx'] : null;
				$title = isset($data['title']) ? $data['title'] : null;
				$summary = isset($data['summary']) ? $data['summary'] : null;
				$gameId = isset($data['gameId']) ? $data['gameId'] : null;
				$url = isset($data['url']) ? $data['url'] : null;
				$success = self::SaveIndexDuoPicRecommend($articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
				if($success===false) break;
			}
		}else{
			return 0;
		}
		if($success==true) CacheService::file()->forget('cache_www_index_duo_pic_recommend');
		return $success;
	}
	
    /**
	 * 获取首页多头条
	 */
	public static function GetIndexDuoTopic()
	{
		$api_url = self::HOST_URL . 'GetIndexDuoTopic';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}
	
	/**
	 * 保存首页首屏图推数据
	 */
	public static function SaveIndexDuoTopic($articleId,$picUrl,$up,$title=null,$summary=null,$url=null)
	{
		$api_url = self::HOST_URL . 'InsertDuoTopic';
		$params = array();
		
		$params['picUrl'] = $picUrl;
		$params['up'] = $up;
		
		if($articleId) {
			$params['articleId'] = $articleId;
		}else{
			$params['title'] = $title;			
			$params['summary'] = $summary;
			$params['url'] = $url;
			$params['publishTime'] = time();
		}
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}

	/**
	 * 删除多头条
	 * @param $idx
	 * @return bool
	 */
	public static function DeleteIndexDuoTopic($idx)
	{
		$api_url = self::HOST_URL . 'DeleteIndexDuoTopic';
		$params = array();
		$params['idx'] = $idx;
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}	
	
	/**
	 * 获取推荐游戏
	 */
	public static function GetIndexTopRecommend()
	{
		$api_url = self::HOST_URL . 'GetIndexTopRecommend';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}
	
	/**
	 * 保存推荐游戏
	 */
	public static function SaveIndexTopRecommend($gameId,$picUrl,$type,$idx)
	{
		$api_url = self::HOST_URL . 'SaveIndexTopRecommend';
		$params = array();
		
		$params['picUrl'] = $picUrl;
		$params['idx'] = $idx;
		$params['gameId'] = $gameId;
		$params['type'] = $type;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}

	public static function SaveIndexTopRecommendSort($updated_idx,$type)
	{
		$old_data = self::GetIndexTopRecommend();
		if(CacheService::file()->has('cache_www_index_top_recommend')==false){
			CacheService::file()->put('cache_www_index_top_recommend',$old_data[$type],60);
		}

		$old_idx_data = array();
		foreach($old_data[$type] as $row){
			$old_idx_data[$row['idx']] = $row;
		}
		$success = true;
		foreach($updated_idx as $old_idx=>$new_idx){
			$data = $old_idx_data[$new_idx];
			$success = self::SaveIndexTopRecommend($data['gameId'],$data['picUrl'],$type,$old_idx);
			if($success===false) break;
		}
		if($success==true) CacheService::file()->forget('cache_www_index_top_recommend');
		return $success;
	}

	public static function RecoveryIndexTopRecommend($type)
	{
		$old_data = CacheService::file()->get('cache_www_index_top_recommend');
		$success = true;
		if($old_data){
			foreach($old_data as $data){
				$success = self::SaveIndexTopRecommend($data['gameId'],$data['picUrl'],$type,$data['idx']);
				if($success===false) break;
			}
		}else{
			return 0;
		}
		if($success==true) CacheService::file()->forget('cache_www_index_top_recommend');
		return $success;
	}
	
    /**
	 * 获取首页二屏轮播图列表
	 */
	public static function GetIndexSecondProjectionBroadcast()
	{
		$api_url = self::HOST_URL . 'GetIndexSecondProjectionBroadcast';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}
	
	/**
	 * 保存首页二屏轮播图数据
	 */
	public static function SaveIndexSecondProjectionBroadcast($articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$api_url = self::HOST_URL . 'SaveIndexSecondProjectionBroadcast';
		$params = array();
		
		$params['picUrl'] = $picUrl;
		$params['idx'] = $idx;
		
		if($articleId) {
			$params['articleId'] = $articleId;
		}else{
			$params['title'] = $title;
			$params['gameId'] = $gameId;
			$params['summary'] = $summary;
			$params['url'] = $url;
		}
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取二屏背景图
	 */
	public static function GetIndexSecondProjectionBg()
	{
		$api_url = self::HOST_URL . 'GetIndexSecondProjectionBg';
		$params = array();
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0 && isset($result['result'])){
			return $result['result'];
		}
		return array();
	}	
	
	/**
	 * 保存二屏背景图
	 */
	public static function SaveIndexSecondProjectionBg($picUrl)
	{
		$api_url = self::HOST_URL . 'SaveIndexSecondProjectionBg';
		$params = array();
		
		$params['picUrl'] = $picUrl;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取二屏栏目设置
	 */
	public static function GetIndexSecondProjectionArticleSetting()
	{
		$api_url = self::HOST_URL . 'GetIndexSecondProjectionArticleSetting';
		$params = array();
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}
	
	public static function SaveIndexSecondProjectionArticleSetting($title,$summary,$gameId,$tag,$idx)
	{
		$api_url = self::HOST_URL . 'SaveIndexSecondProjectionArticleSetting';
		$params = array();
		
		$params['title'] = $title;
		$params['idx'] = $idx;
		$params['gameId'] = $gameId;
		$params['tag'] = $tag;
		$params['summary'] = $summary;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取商城推荐
	 */
    public static function GetMerchantRecommend()
	{
		$api_url = self::HOST_URL . 'GetMerchantRecommend';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}
	
	/**
	 * 保存商城推荐
	 */
	public static function SaveMerchantRecommend($idx,$title,$picUrl,$url)
	{
		$api_url = self::HOST_URL . 'SaveMerchantRecommend';
		$params = array();
		
		$params['title'] = $title;
		$params['idx'] = $idx;
		$params['picUrl'] = $picUrl;
		$params['url'] = $url;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}	
	
    /**
	 * 获取福利活动
	 */
    public static function GetIndexWelfareEvent()
	{
		$api_url = self::HOST_URL . 'GetIndexWelfareEvent';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}
	
	/**
	 * 保存福利活动
	 */
	public static function SaveIndexWelfareEvent($idx,$title,$picUrl,$url)
	{
		$api_url = self::HOST_URL . 'SaveIndexWelfareEvent';
		$params = array();
		
		$params['title'] = $title;
		$params['idx'] = $idx;
		$params['picUrl'] = $picUrl;
		$params['url'] = $url;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取热门礼包
	 *
	 */
	public static function GetIndexHotGiftBagPicRecommend()
	{
		$api_url = self::HOST_URL . 'GetIndexHotGiftBagPicRecommend';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}
	
	/**
	 * 保存热门礼包
	 *
	 */
	public static function SaveIndexHotGiftBagPicRecommend($idx,$title,$picUrl,$url)
	{
		$api_url = self::HOST_URL . 'SaveIndexHotGiftBagPicRecommend';
		$params = array();
		
		$params['title'] = $title;
		$params['idx'] = $idx;
		$params['picUrl'] = $picUrl;
		$params['url'] = $url;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
    /**
	 * 获取新游期待背景图
	 * 
	 */
	public static function GetIndexNewGameExpectBg()
	{
		$api_url = self::HOST_URL . 'GetIndexNewGameExpect';
		$params = array();
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}	
	
	/**
	 * 获取新游期待榜
	 */
	public static function GetIndexNewGameExpect()
	{
		$api_url = self::HOST_URL . 'GetIndexNewGameExpect';
		$params = array();
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return $result['result'];
		}
		return array();
	}
	
	/**
	 * 保存游戏期待榜
	 */
	public static function SaveIndexNewGameExpectEntry($idx,$gameId,$title)
	{
		$api_url = self::HOST_URL . 'SaveIndexNewGameExpectEntry';
		$params = array();
		
		$params['idx'] = $idx;
		$params['gameId'] = $gameId;
		$params['title'] = $title;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 保存新游期待背景图
	 * 
	 */
	public static function SaveIndexNewGameExpectBg($picUrl)
	{
		$api_url = self::HOST_URL . 'SaveIndexNewGameExpectBg';
		$params = array();
		
		$params['picUrl'] = $picUrl;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 视频直播
	 */
	public static function GetIndexThirdProjection()
	{
		$api_url = self::HOST_URL . 'GetIndexThirdProjection';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return isset($result['result']) ? $result['result'] : array();
		}
		return array();
	}
	
	/**
	 * 保存视频直播
	 */
	public static function SaveIndexThirdProjection($videoPic,$bgPic,$liveVideo,$announce)
	{
		$api_url = self::HOST_URL . 'SaveIndexThirdProjection';
		$params = array();
		
		$params['picUrl'] = $videoPic;
		$params['bgPicUrl'] = $bgPic;
		$params['liveVideo'] = $liveVideo;
		$params['announce'] = $announce;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取新游预告
	 *
	 */
	public static function GetIndexNewGamePreview()
	{
		$api_url = self::HOST_URL . 'GetIndexNewGamePreview';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return isset($result['result']) ? $result['result'] : array();
		}
		return array();
	}
	
	/**
	 * 保存新游预告
	 *
	 */
	public static function SaveIndexNewGamePreview($picUrl,$title,$url)
	{
		$api_url = self::HOST_URL . 'SaveIndexNewGamePreview';
		$params = array();
		
		$params['title'] = $title;
		$params['picUrl'] = $picUrl;
		$params['url'] = $url;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取新游专题
	 */
	public static function GetIndexNewGameSpecial()
	{
		$api_url = self::HOST_URL . 'GetIndexNewGameSpecial';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return isset($result['result']) ? $result['result'] : array();
		}
		return array();
	}
	
	/**
	 * 测评推荐
	 */
	public static function GetIndexGameTestRecommend()
	{
		$uri = 'GetIndexGameTestRecommend';
		return self::GetRecommendList($uri);
	}
	
	/**
	 * 测评推荐
	 */
	public static function SaveIndexGameTestRecommend($articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$uri = 'SaveIndexGameTestRecommend';
		return self::SaveRecommendList($uri,$articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
	}
	
	/**
	 * 视频推荐
	 */
	public static function GetIndexVideoRecommend()
	{
		$uri = 'GetIndexVideoRecommend';
		$result = self::GetRecommendList($uri);
		if($result) return array($result);
		return $result;
	}
	
	/**
	 * 视频推荐
	 */
	public static function SaveIndexVideoRecommend($articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$uri = 'SaveIndexVideoRecommend';
		return self::SaveRecommendList($uri,$articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
	}
	
	/**
	 * 原创栏目
	 */
	public static function GetIndexOriginalSection()
	{
		$uri = 'GetIndexOriginalSection';
		return self::GetRecommendList($uri);
	}
	
	/**
	 * 原创栏目
	 */
	public static function SaveIndexOriginalSection($articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$uri = 'SaveIndexOriginalSection';
		return self::SaveRecommendList($uri,$articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
	}
	
	/**
	 * 产业推荐
	 */
	public static function GetIndexIndustryRecommend()
	{
		$uri = 'GetIndexIndustryRecommend';
		$result = self::GetRecommendList($uri);
		if($result) return array($result);
		return $result;
	}
	
	/**
	 * 保存产业推荐
	 */
	public static function SaveIndexIndustryRecommend($articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$uri = 'SaveIndexIndustryRecommend';
		return self::SaveRecommendList($uri,$articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
	}
	
	/**
	 * 产业栏目
	 */
	public static function GetIndexIndustrySection()
	{
		$uri = 'GetIndexIndustrySection';
		return self::GetRecommendList($uri);
	}
	
	/**
	 * 保存产业栏目
	 */
	public static function SaveIndexIndustrySection($articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$uri = 'SaveIndexIndustrySection';
		return self::SaveRecommendList($uri,$articleId,$picUrl,$idx,$title,$summary,$gameId,$url);
	}	

    /**
	 * 获取列表
	 */
	protected static function GetRecommendList($uri)
	{
		$api_url = self::HOST_URL . $uri;
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return isset($result['result']) ? $result['result'] : array();
		}
		return array();
	}
	
	/**
	 * 保存首页首屏图推数据
	 */
	public static function SaveRecommendList($uri,$articleId,$picUrl,$idx,$title=null,$summary=null,$gameId=null,$url=null)
	{
		$api_url = self::HOST_URL . $uri;
		$params = array();
		
		$params['picUrl'] = $picUrl;
		$params['idx'] = $idx;
		
		if($articleId) {
			$params['articleId'] = $articleId;
		}else{
			$params['title'] = $title;
			$params['gameId'] = $gameId;
			$params['summary'] = $summary;
			$params['url'] = $url;
		}
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 保存新游专题
	 */
	public static function SaveIndexNewGameSpecial($picUrlB,$picUrlS,$title,$url,$idx)
	{
		$api_url = self::HOST_URL . 'SaveIndexNewGameSpecial';
		$params = array();
		
		$params['title'] = $title;
		$params['picUrlB'] = $picUrlB;
		$params['picUrlS'] = $picUrlS;
		$params['url'] = $url;
		$params['idx'] = $idx;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取四屏设置
	 */
	public static function GetIndexFourthProjection()
	{
		$api_url = self::HOST_URL . 'GetIndexFourthProjection';
		
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return isset($result['result']) ? $result['result'] : array();
		}
		return array();
	}
	
	/**
	 * 保存四屏设置
	 * @param string $game_id 游戏ID
	 * @param string $bgPicUrl 背景图设置
	 * @param string $communityUrl 社区URL
	 * @param string $specialUrl 专区URL
	 * @param string $officialUrl 官网URL
	 * @param string $url 本站URL
	 * 
	 */
	public static function SaveIndexFourthProjection($gameId,$bgPicUrl,$communityUrl,$specialUrl,$officialUrl,$url)
	{
		$api_url = self::HOST_URL . 'SaveIndexFourthProjection';
		$params = array();
		
		$params['gameId'] = $gameId;		
		$params['bgPicUrl'] = $bgPicUrl;
		$params['communityUrl'] = $communityUrl;
		$params['specialUrl'] = $specialUrl;
		$params['officialUrl'] = $officialUrl;		
		$params['url'] = $url;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取攻略
	 */
	public static function GetIndexStrategyRecommend()
	{
		$api_url = self::HOST_URL . 'GetIndexStrategyRecommend';
		$result = self::http($api_url,null,'GET');
		if($result!==false && $result['errorCode']==0){
			return isset($result['result']) ? $result['result'] : array();
		}
		return array();
	}
	
	/**
	 * 保存推荐的攻略
	 */
	public static function SaveIndexStrategyRecommend($idx,$articleId,$picUrl)
	{
		$api_url = self::HOST_URL . 'SaveIndexStrategyRecommend';
		$params = array();
		
		$params['idx'] = $idx;
		$params['articleId'] = $articleId;
		$params['picUrl'] = $picUrl;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		return false;
	}
}