<?php
namespace modules\wcms\models;

use Illuminate\Support\Facades\Log;

class Article extends BaseHttp
{

	/**
	 * @param $search
	 * @param $pageIndex
	 * @param $pageSize
	 * @param null $sort
	 * @return array
	 */
	public static function searchList($search,$pageIndex,$pageSize,$sort=null)
	{
		$out = array('result'=>array(),'totalCount'=>0);
		$url = self::HOST_URL . 'GetArticleList';
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
	 * @param $id
	 * @return array
	 */
	public static function getArticleDetail($id)
	{
		$out = array();
		$url = self::HOST_URL . 'GetArticleDetail';
		$params = array('id'=>$id);
		$result = self::http($url,$params);
		if($result!==false && $result['errorCode']==0){
			$out = $result['result'];
		}
		return $out;
	}

	/**
	 * @param $title
	 * @param $subtitle
	 * @param $source
	 * @param $author
	 * @param $gameId
	 * @param $titlePic
	 * @param $albumId
	 * @param $videoId
	 * @param $catalog
	 * @param $summary
	 * @param $content
	 * @param $tags
	 * @param $args
	 * @return bool
	 */
	public static function addArticle($title,$subtitle,$source,$author,$gameId,$titlePic,$albumId,$videoId,$catalog,$summary,$content,$tags,$args=array(),$pic2="")
	{
		$url = self::HOST_URL . 'CreateArticle';
		$params = array();
		$params['title'] = $title;
		$params['subTitle'] = $subtitle;
		$params['source'] = $source;
		$params['author'] = $author;
		$params['gameId'] = $gameId;
		$params['titlePic'] = $titlePic;
		$params['albumId'] = $albumId;
		$params['videoId'] = $videoId;
		$params['catalog'] = $catalog;
		$params['summary'] = $summary;
		$params['content'] = $content;
		$params['tag'] = $tags;
        $params['titlePic2'] = $pic2;

		if(isset($args['publishTime']) && $args['publishTime']){
			$params['publishTime'] = $args['publishTime'];
		}else{
			$params['publishTime'] = time();
		}

		if(isset($args['editor'])){
			$params['editor'] = $args['editor'];
		}

		$result = self::http($url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		//var_dump($result);exit;
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 更新文章
	 * @param string $id
	 * @param string $title
	 * @param string $subtitle
	 * @param string $source
	 * @param string $author
	 * @param string $gameId
	 * @param string $titlePic
	 * @param string $albumId
	 * @param string $videoId
	 * @param string $catalog
	 * @param string $summary
	 * @param string|array $content
	 * @param array $args
	 * @param array $tags
	 *
	 * @return bool
	 */
	public static function updateArticle($id,$title,$subtitle,$source,$author,$gameId,$titlePic,$albumId,$videoId,$catalog,$summary,$content,$tags,$args=array(),$pic2="")
	{
		$url = self::HOST_URL . 'UpdateArticle';
		$params = array();
		$params['id'] = $id;
		$params['title'] = $title;
		$params['subTitle'] = $subtitle;
		$params['source'] = $source;
		$params['author'] = $author;
		$params['gameId'] = $gameId;
		$params['titlePic'] = $titlePic;
		$params['albumId'] = $albumId;
		$params['videoId'] = $videoId;
		$params['catalog'] = $catalog;
		$params['summary'] = $summary;
		$params['content'] = $content;
		$params['tag'] = $tags;
        $params['titlePic2'] = $pic2;

		if(isset($args['publishTime']) && $args['publishTime']){
			$params['publishTime'] = $args['publishTime'];
		}else{
			$params['publishTime'] = time();
		}

		if(isset($args['editor'])){
			$params['editor'] = $args['editor'];
		}

		$result = self::http($url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 删除文章
	 * @param string $articleId
	 * @return bool true/false
	 */
	public static function RemoveArticle($articleId)
	{
		$api_url = self::HOST_URL . 'RemoveArticle';
		$params = array();
		
		$params['articleId'] = $articleId;
		
		$result = self::http($api_url,$params,'POST');
		if($result!==false && $result['errorCode']==0){
			return true;
		}
		Log::error($result['errorDescription']);
		return false;
	}

	/**
	 * 获取文章分类
	 * @param bool $format 是否返回格式化后的数据
	 * @return array $out
	 */
	public static function getArticleAllCategory($format=true)
	{
		$out = array();
		$url = self::HOST_URL . 'GetArticleCatalogs';
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
