<?php
namespace modules\gamelive\models;

class Video extends BaseHttp
{
    /**
     * @param $page
     * @param $size
     * @param $catalog
     * @param $columnId
     * @param $title
     * @param null $tag
     * @param string $gameId
     * @param string $peopleId
     * @return array
     */
    public static function GetVideoList($page,$size,$catalog=null,$columnId=null,$title='',$tag=null,$gameId='',$peopleId='')
    {
        $url = self::HOST_URL . 'GetVideoList';
        $params = array(
            'page'=>$page,
            'size'=>$size,
            //'catalog'=>$catalog,
            //'columnId'=>$columnId,
            //'titleContain'=>$title,
            //'tag'=>$tag,
            //'gameId'=>$gameId,
            //'peopleId'=>$peopleId
        );

        $columnId && $params['columnId'] = $columnId;
        $catalog && $params['catalog'] = $catalog;
        $title && $params['titleContain'] = $title;
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
    public static function GetVideoDetail($id)
    {
        $url = self::HOST_URL . 'GetVideoDetail';
        $params['id'] = $id;
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return null;
    }

    public static function CreateVideo($title,$subTitle,$titlePic,$publishTime,$catalog,$columnId,$gameId,$summary,$tag,$peopleId,$content)
    {
        $url = self::HOST_URL . 'CreateVideo';
        $params = array();
        $params['title'] = $title;
        $params['subTitle'] = $subTitle;
        $params['gameId'] = $gameId;
        $params['titlePic'] = $titlePic;
        $params['catalog'] = $catalog;
        $params['columnId'] = $columnId;
        $params['summary'] = $summary;
        $params['content'] = $content;
        $params['tag'] = $tag;
        $params['peopleId'] = $peopleId;
        $params['publishTime'] = $publishTime;
        $result = self::http($url,$params,'POST');
        if($result['errorCode']==0){
            return $result['result'];
        }
        //var_dump($result);exit;
        return false;
    }

    /**
     * @param $id
     * @param $title
     * @param $subTitle
     * @param $titlePic
     * @param $publishTime
     * @param $catalog
     * @param $gameId
     * @param $summary
     * @param $tag
     * @param $peopleId
     * @param $content
     * @return bool
     */
    public static function UpdateVideo($id,$title,$subTitle,$titlePic,$publishTime,$catalog,$columnId,$gameId,$summary,$tag,$peopleId,$content)
    {
        $url = self::HOST_URL . 'UpdateVideo';
        $params = array();
        $params['id'] = $id;
        $params['title'] = $title;
        $params['subTitle'] = $subTitle;
        $params['gameId'] = $gameId;
        $params['titlePic'] = $titlePic;
        $params['catalog'] = $catalog;
        $params['columnId'] = $columnId;
        $params['summary'] = $summary;
        $params['content'] = $content;
        $params['tag'] = $tag;
        $params['columnId'] = $columnId;
        $params['peopleId'] = $peopleId;
        $params['publishTime'] = $publishTime;
        $result = self::http($url,$params,'POST');
        //print_r($result);exit;
        if($result['errorCode']==0){
            return true;
        }else{
            //var_dump($result);exit;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool|null
     */
    public static function RemoveVideo($id)
    {
        $url = self::HOST_URL . 'RemoveVideo';
        $params['id'] = $id;
        $result = self::http($url,$params,'POST');
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
    public static function CreateVideoCatalog($url,$name,$idx)
    {
        $apiurl = self::HOST_URL . 'CreateVideoCatalog';
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
    public static function GetVideoCatalogs()
    {
        $url = self::HOST_URL . 'GetVideoCatalogs';
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
    public static function UpdateVideoTag($oldTag,$newTag)
    {
        $url = self::HOST_URL . 'UpdateVideoTag';
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
    public static function SaveVideoTag($idx,$tag)
    {
        $url = self::HOST_URL . 'SaveVideoTag';
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
    public static function RemoveVideoTag($idx)
    {
        $url = self::HOST_URL . 'RemoveVideoTag';
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
    public static function GetVideoTags()
    {
        $url = self::HOST_URL . 'GetVideoTags';
        $result = self::http($url,null);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array();
    }
}