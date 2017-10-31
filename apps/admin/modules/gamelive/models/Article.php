<?php
namespace modules\gamelive\models;

class Article extends BaseHttp
{
    /**
     * @param $page
     * @param $size
     * @param $catalog
     * @param $columnId
     * @param $title
     * @param null $tag
     * @param string $gameId
     * @return array
     */
    public static function GetArticleList($page,$size,$catalog=null,$columnId=null,$title=null,$tag=null,$gameId='')
    {
        $url = self::HOST_URL . 'GetArticleList';
        $params = array(
            'page'=>$page,
            'size'=>$size,
        );

        $catalog && $params['catalog'] = $catalog;
        $columnId && $params['columnId'] = $columnId;
        $title && $params['titleContain'] = $title;
        $tag &&  $params['tag'] = $tag;
        $gameId && $params['gameId'] = $gameId;

        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array('totalPage'=>0,'size'=>0,'page'=>0,'list'=>array());
    }

    /**
     * @param $id
     * @return null
     */
    public static function GetArticleDetail($id)
    {
        $url = self::HOST_URL . 'GetArticleDetail';
        $params['id'] = $id;
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return null;
    }

    /**
     * @param $title
     * @param $subtitle
     * @param $source
     * @param $author
     * @param $columnId
     * @param $gameId
     * @param $titlePic
     * @param $catalog
     * @param $summary
     * @param $content
     * @param $tags
     * @param $skipTitle
     * @param $skipUrl
     * @param array $args
     * @return bool
     */
    public static function CreateArticle($title,$subtitle,$source,$author,$columnId,$gameId,$titlePic,$catalog,$summary,$content,$tags,$skipTitle,$skipUrl,$args=array())
    {
        $url = self::HOST_URL . 'CreateArticle';
        $params = array();
        $params['title'] = $title;
        $params['subTitle'] = $subtitle;
        $params['source'] = $source;
        $params['author'] = $author;
        $params['editor'] = isset($args['editor']) ? $args['editor'] : '';
        $params['gameId'] = $gameId;
        $params['titlePic'] = $titlePic;
        $params['columnId'] = $columnId;
        $params['catalog'] = $catalog;
        $params['summary'] = $summary;
        $params['content'] = $content;
        $params['tag'] = $tags;
        $params['publishTime'] = time();
        $params['columnId'] = $columnId;
        $params['skipTitle'] = $skipTitle;
        $params['skipUrl'] = $skipUrl;


        $result = self::http($url,$params,'POST');
        if($result['errorCode']==0){
            return $result['result'];
        }
        return false;
    }

    /**
     * @param $id
     * @param $title
     * @param $subtitle
     * @param $source
     * @param $author
     * @param $columnId
     * @param $gameId
     * @param $titlePic
     * @param $catalog
     * @param $summary
     * @param $content
     * @param $tags
     * @param $skipTitle
     * @param $skipUrl
     * @param array $args
     * @return bool
     */
    public static function UpdateArticle($id,$title,$subtitle,$source,$author,$columnId,$gameId,$titlePic,$catalog,$summary,$content,$tags,$skipTitle,$skipUrl,$args=array())
    {
        $url = self::HOST_URL . 'UpdateArticle';
        $params = array();
        $params['id'] = $id;
        $params['title'] = $title;
        $params['subTitle'] = $subtitle;
        $params['source'] = $source;
        $params['author'] = $author;
        $params['editor'] = isset($args['editor']) ? $args['editor'] : '';
        $params['gameId'] = $gameId;
        $params['titlePic'] = $titlePic;
        $params['columnId'] = $columnId;
        $params['catalog'] = $catalog;
        $params['summary'] = $summary;
        $params['content'] = $content;
        $params['tag'] = $tags;
        $params['publishTime'] = time();
        $params['columnId'] = $columnId;
        $params['skipTitle'] = $skipTitle;
        $params['skipUrl'] = $skipUrl;

        $result = self::http($url,$params,'POST');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }


    /**
     * @param $id
     * @return bool|null
     */
    public static function RemoveArticle($id)
    {
        $url = self::HOST_URL . 'RemoveArticle';
        $params['id'] = $id;
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }
        return null;
    }

    /**
     * @param $url
     * @param $name
     * @param $idx
     * @return bool
     */
    public static function CreateArticleCatalog($url,$name,$idx)
    {
        $apiurl = self::HOST_URL . 'CreateArticleCatalog';
        $params = array(
            'url'=>$url,
            'name'=>$name,
            'idx'=>$idx
        );
        $result = self::http($apiurl,$params,'POST');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public static function GetArticleCatalogs()
    {
        $url = self::HOST_URL . 'GetArticleCatalogs';
        $result = self::http($url,null);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array();
    }

    /**
     * @param $oldTag
     * @param $newTag
     * @return bool
     */
    public static function UpdateArticleTag($oldTag,$newTag)
    {
        $url = self::HOST_URL . 'UpdateArticleTag';
        $params = array(
            'oldTag'=>$oldTag,
            'newTag'=>$newTag
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @param $idx
     * @param $tag
     * @return bool
     */
    public static function SaveArticleTag($idx,$tag)
    {
        $url = self::HOST_URL . 'SaveArticleTag';
        $params = array(
            'idx'=>$idx,
            'tag'=>$tag
        );
        $result = self::http($url,$params,'POST');
        if($result['errorCode']==0){
            return true;
        }

        return false;
    }

    /**
     * @param $idx
     * @return bool
     */
    public static function RemoveArticleTag($idx)
    {
        $url = self::HOST_URL . 'RemoveArticleTag';
        $params = array(
            'idx'=>$idx
        );
        $result = self::http($url,$params,'POST');
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public static function GetArticleTags()
    {
        $url = self::HOST_URL . 'GetArticleTags';
        $result = self::http($url,null);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array();
    }
}