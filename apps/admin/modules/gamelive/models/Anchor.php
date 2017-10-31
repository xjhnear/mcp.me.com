<?php
namespace modules\gamelive\models;

class Anchor extends BaseHttp
{
    /**
     * @param $page
     * @param $size
     * @param $tag
     * @param string $gameId
     * @return array
     */
    public static function GetPeopleList($page,$size,$tag='',$gameId='')
    {
        $url = self::HOST_URL . 'GetPeopleList';
        $params = array(
            'page'=>$page,
            'size'=>$size,
            'gameId'=>$gameId
        );
        $tag && $params['tag'] = $tag;
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
    public static function GetPeopleDetail($id)
    {
        $url = self::HOST_URL . 'GetPeopleDetail';
        $params['id'] = $id;
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return null;
    }

    /**
     * @param $name
     * @param $picUrl
     * @param $summary
     * @param $idx
     * @param $publishTime
     * @param $tag
     * @param $gameId
     * @param $albumIds
     * @param $thumbnail
     * @return bool
     */
    public static function CreatePeople($name,$picUrl,$summary,$idx,$publishTime,$tag,$gameId,$albumIds,$thumbnail)
    {
        $url = self::HOST_URL . 'CreatePeople';
        $params = array(
            'name'=>$name,
            'picUrl'=>$picUrl,
            'summary'=>$summary,
            'idx'=>$idx,
            'publishTime'=>$publishTime,
            'tag'=>$tag,
            'gameId'=>$gameId,
            'picAlbum'=>$albumIds,
            'thumbnail'=>$thumbnail
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return $result['result'];
        }
        return false;
    }

    /**
     * @param $id
     * @param $name
     * @param $picUrl
     * @param $summary
     * @param $idx
     * @param $publishTime
     * @param $tag
     * @param $gameId
     * @param $albumIds
     * @param $thumbnail
     * @return bool
     */
    public static function UpdatePeople($id,$name,$picUrl,$summary,$idx,$publishTime,$tag,$gameId,$albumIds,$thumbnail)
    {
        $url = self::HOST_URL . 'UpdatePeople';
        $params = array(
            'id'=>$id,
            'name'=>$name,
            'picUrl'=>$picUrl,
            'summary'=>$summary,
            'idx'=>$idx,
            'publishTime'=>$publishTime,
            'tag'=>$tag,
            'gameId'=>$gameId,
            'picAlbum'=>$albumIds,
            'thumbnail'=>$thumbnail
        );

        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool|null
     */
    public static function RemovePeople($id)
    {
        $url = self::HOST_URL . 'RemovePeople';
        $params['peopleId'] = $id;
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }else{
            var_dump($result);exit;
        }
        return null;
    }

    /**
     * @return array
     */
    public static function GetPeopleTags()
    {
        $url = self::HOST_URL . 'GetPeopleTags';
        $result = self::http($url,array());
        if($result['errorCode']==0){
            return $result['result'];
        }
        return array();
    }

    /**
     * @param $oldTag
     * @param $newTag
     * @return bool|null
     */
    public static function UpdatePeopleTag($oldTag,$newTag)
    {
        $url = self::HOST_URL . 'UpdatePeopleTag';
        $params = array(
            'oldTag'=>$oldTag,
            'newTag'=>$newTag
        );
        $result = self::http($url,$params);
        if($result['errorCode']==0){
            return true;
        }
        return null;
    }
}