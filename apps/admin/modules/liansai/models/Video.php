<?php
namespace modules\wcms\models;

use Illuminate\Support\Facades\Log;
use Youxiduo\Cms\WebToMobileService;

class Video extends BaseHttp
{

	/**
	 * 搜索视频列表
	 * @param $search
	 * @param $pageIndex
	 * @param $pageSize
	 * @param null $sort
	 * @return array
	 */
	public static function searchList($search,$pageIndex,$pageSize,$sort=null)
	{
		$out = array('result'=>array(),'totalCount'=>0);
		$url = self::HOST_URL . 'GetVideoList';
		$params = array('page'=>$pageIndex,'size'=>$pageSize);
		if(isset($search['catalog'])){
			$params['catalog'] = $search['catalog'];
		}
		if(isset($search['keyword']) && !empty($search['keyword'])){
			$params['titleContain'] = $search['keyword'];
		}
		$result = self::http($url,$params);
		if($result!==false && $result['errorCode']==0){
			$out = array('result'=>$result['result']['list'],'totalCount'=>($result['result']['totalPage']*$result['result']['size']));
		}
		return $out;
	}

	/**
	 * 创建视频
	 * @param $title
	 * @param $subtitle
	 * @param $source
	 * @param $author
	 * @param $gameId
	 * @param $titlePic
	 * @param $catalog
	 * @param $summary
	 * @param $content
	 * @param $tags
	 * @return bool
	 */
	public static function CreateVideo($title,$subtitle,$source,$author,$gameId,$titlePic,$catalog,$summary,$content,$tags)
	{
		$url = self::HOST_URL . 'CreateVideo';
		$params = array();
		$params['title'] = $title;
		$params['subTitle'] = $subtitle;
		$params['source'] = $source;
		$params['author'] = $author;
		$params['gameId'] = $gameId;
		$params['titlePic'] = $titlePic;
		$params['catalog'] = $catalog;
		$params['summary'] = $summary;
		$params['content'] = $content;
		$params['tag'] = $tags;
		$params['publishTime'] = time();
		$result = self::http($url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			$videoId = $result['result'];
			WebToMobileService::syncVideoToQueue($videoId,$title,$titlePic,$author,$content,$summary,0,$gameId,$catalog);
			return true;
		}
		//var_dump($result);exit;
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 更新视频
	 * @param $videoId
	 * @param $title
	 * @param $subtitle
	 * @param $source
	 * @param $author
	 * @param $gameId
	 * @param $titlePic
	 * @param $catalog
	 * @param $summary
	 * @param $content
	 * @param $tags
	 * @return bool
	 */
    public static function UpdateVideo($videoId,$title,$subtitle,$source,$author,$gameId,$titlePic,$catalog,$summary,$content,$tags,$publishTime)
	{
		$url = self::HOST_URL . 'UpdateVideo';
		$params = array();
		$params['videoId'] = $videoId;
		$params['title'] = $title;
		$params['subTitle'] = $subtitle;
		$params['source'] = $source;
		$params['author'] = $author;
		$params['gameId'] = $gameId;
		$params['titlePic'] = $titlePic;
		$params['catalog'] = $catalog;
		$params['summary'] = $summary;
		$params['content'] = $content;
		$params['tag'] = $tags;
		$params['publishTime'] = $publishTime;
		$result = self::http($url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			WebToMobileService::syncVideoToQueue($videoId,$title,$titlePic,$author,$content,$summary,0,$gameId,$catalog);
			return true;
		}
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 获取视频详情
	 * @param $id
	 * @return array
	 */
    public static function getVideoDetail($id)
	{
		$out = array();
		$url = self::HOST_URL . 'GetVideoDetail';
		$params = array('id'=>$id);
		$result = self::http($url,$params);
		if($result!==false && $result['errorCode']==0){
			$out = $result['result'];
		}
		return $out;
	}

	/**
	 * 删除视频
	 * @param $videoId
	 * @return bool
	 */
	public static function RemoveVideo($videoId)
	{
		$api_url = self::HOST_URL . 'RemoveVideo';
		$params = array();
		
		$params['videoId'] = $videoId;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 获取视频分类
	 * @param bool|true $format
	 * @return array
	 */
    public static function getAllCategory($format=true)
	{
		$out = array();
		$url = self::HOST_URL . 'GetVideoCatalogs';
		$params = array();
		$result = self::http($url,$params);
		if($result!==false && $result['errorCode']==0){
			if($format==true){
				foreach($result['result'] as $row){
					$out[$row['url']] = $row['name'];
				}
			}else{
				$out = $result['result'];
			}
		}
		return $out;
	}
}