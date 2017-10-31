<?php
namespace modules\wcms\models;

use Illuminate\Support\Facades\Log;

class Picture extends BaseHttp
{

	/**
	 * 搜索图集列表
	 * @param $search
	 * @param $pageIndex
	 * @param $pageSize
	 * @param null $sort
	 * @return array
	 */
	public static function searchList($search,$pageIndex,$pageSize,$sort=null)
	{
		$out = array('result'=>array(),'totalCount'=>0);
		$url = self::HOST_URL . 'GetAlbumList';
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
	 * 创建图集
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
	 * @param $picInfo
	 * @return bool
	 */
	public static function CreateAlbum($title,$subtitle,$source,$author,$gameId,$titlePic,$catalog,$summary,$content,$tags,$picInfo)
	{
		$url = self::HOST_URL . 'CreateAlbum';
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
		$params['picInfo'] = $picInfo;
		$params['publishTime'] = time();
		$result = self::http($url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		//var_dump($result);exit;
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 更新图集
	 * @param $albumId
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
	 * @param $picInfo
	 * @return bool
	 */
    public static function UpdateAlbum($albumId,$title,$subtitle,$source,$author,$gameId,$titlePic,$catalog,$summary,$content,$tags,$picInfo)
	{
		$url = self::HOST_URL . 'UpdateAlbum';
		$params = array();
		$params['albumId'] = $albumId;
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
		$params['picInfo'] = $picInfo;
		$params['publishTime'] = time();
		$result = self::http($url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		//var_dump($result);exit;
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 获取图集详情
	 * @param $id
	 * @return array
	 */
    public static function getAlbumDetail($id)
	{
		$out = array();
		$url = self::HOST_URL . 'GetAlbumDetail';
		$params = array('id'=>$id);
		$result = self::http($url,$params);
		if($result!==false && $result['errorCode']==0){
			$out = $result['result'];
		}
		return $out;
	}

	/**
	 * 删除图集
	 * @param $albumId
	 * @return bool
	 */
	public static function RemoveAlbum($albumId)
	{
		$api_url = self::HOST_URL . 'RemoveAlbum';
		$params = array();
		
		$params['albumId'] = $albumId;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 获取图集分类
	 * @param bool|true $format
	 * @return array
	 */
    public static function getAlbumAllCategory($format=true)
	{
		$out = array();
		$url = self::HOST_URL . 'GetAlbumCatalogs';
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