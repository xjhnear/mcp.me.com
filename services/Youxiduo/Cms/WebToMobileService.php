<?php
namespace Youxiduo\Cms;
use Youxiduo\Cms\Model\Opinion;
use Youxiduo\Helper\Utility;
use Youxiduo\Base\BaseService;
use Youxiduo\Android\Model\News;
use Youxiduo\Android\Model\Guide;
use Youxiduo\Cms\Model\Archives;

class WebToMobileService extends BaseService
{

    public static function syncNewsToQueue($id,$title,$subtitle,$source,$author,$gameId,$titlePic,$albumId,$videoId,$catalog,$summary,$content,$tags,$args=array())
    {
        return false;
        $gid = 0;
        $agid = 0;
        if(is_array($gameId) && $gameId) {
            $gameId = $gameId[0];
            $archive = Archives::db()->where('id','=',$gameId)->first();
            if($archive && isset($archive['yxdid'])){
                $yxdid = $archive['yxdid'];
                if(strpos($yxdid,'g_')===0) {
                    $gid = str_replace('g_', '', $yxdid);
                }
                if(strpos($yxdid,'apk_')===0) {
                    $gid = str_replace('apk_', '', $yxdid);
                }
            }
        }

        try{
            $zxtype = 0;
            switch($catalog){
                case 'zixun'://新闻
                    $zxtype = 0;
                    self::syncNews($id,$title,$subtitle,$author,$content,$titlePic,$gid,$agid,0,0,$summary,1,$zxtype);
                    break;
                case 'gonglue'://攻略
                    self::syncGuide($id,$title,$subtitle,$author,$content,$titlePic,$gid,$agid,0,0,$summary);
                    break;
                case 'industry'://产业新闻
                    $zxtype = 1;
                    self::syncNews($id,$title,$subtitle,$author,$content,$titlePic,$gid,$agid,0,0,$summary,1,$zxtype);
                    break;
                case 'pingce':
                    self::syncOpinion($id,$title,$subtitle,$author,$content,$titlePic,$gid,$agid,0,0,$summary);
                    break;
                case 'newGame':

                    break;
                default:
                    break;
            }
        }catch(\Exception $e){
        }
    }

    public static function syncVideoToQueue($id,$title,$litpic,$writer,$video,$description,$editor,$gameId,$catalog)
    {
        return false;
        $gid = 0;
        $agid = 0;
        if(is_array($gameId) && $gameId) {
            $gameId = $gameId[0];
            $archive = Archives::db()->where('id','=',$gameId)->first();
            if($archive && isset($archive['yxdid'])){
                $yxdid = $archive['yxdid'];
                if(strpos($yxdid,'g_')===0) {
                    $gid = str_replace('g_', '', $yxdid);
                }
                if(strpos($yxdid,'apk_')===0) {
                    $gid = str_replace('apk_', '', $yxdid);
                }
            }
        }
        try{
            $type = $catalog=='yuanchuang' ? 1 : 2;
            self::syncVideo($id,$title,$type,$litpic,$writer,$video,$description,$editor=0,$gid,$agid);
        }catch(\Exception $e){

        }
    }

    /**
     * 同步新闻文章
     *
     * @param $id
     * @param $title
     * @param $shorttitle
     * @param $writer
     * @param $content
     * @param $litpic
     * @param int $gid
     * @param int $agid
     * @param int $editor
     * @param int $sort
     * @param string $webdesc
     * @param int $zxshow
     * @param $zxtype
     * @return mixed
     */
    public static function syncNews($id,$title,$shorttitle,$writer,$content,$litpic,$gid=0,$agid=0,$editor=0,$sort=0,$webdesc='',$zxshow=1,$zxtype)
    {
        $data = array();
        $data['title'] = $title;
        $data['shorttitle'] = $shorttitle;
        $data['writer'] = $writer;
        $data['content'] = $content;
        $data['litpic'] = $litpic;
        $data['gid'] = $gid;
        $data['agid'] = $agid;
        $data['addtime'] = time();
        $data['editor'] = $editor;
        $data['sort'] = $sort;
        $data['webdesc'] = $webdesc;
        $data['zxshow'] = $zxshow;
        $data['zxtype'] = $zxtype;
        $exists = News::db()->where('web_id','=',$id)->first();
        if($exists){
            $res = News::db()->where('web_id','=',$id)->update($data);
            return $res ? $exists['id'] : false;
        }else{
            $data['web_id'] = $id;
            $id = News::db()->insertGetId($data);
            return $id;
        }
    }

    /**
     * 同步攻略文章
     * @param $id
     * @param $title
     * @param $shorttitle
     * @param $writer
     * @param $content
     * @param $litpic
     * @param int $gid
     * @param int $agid
     * @param int $editor
     * @param int $sort
     * @param string $webdesc
     * @return bool
     */
    public static function syncGuide($id,$title,$shorttitle,$writer,$content,$litpic,$gid=0,$agid=0,$editor=0,$sort=0,$webdesc='')
    {
        $data = array();
        $data['gtitle'] = $title;
        $data['shorttitle'] = $shorttitle;
        $data['writer'] = $writer;
        $data['content'] = $content;
        $data['litpic'] = $litpic;
        $data['gid'] = $gid;
        $data['agid'] = $agid;
        $data['addtime'] = time();
        $data['editor'] = $editor;
        $data['sort'] = $sort;
        $data['webdesc'] = $webdesc;
        $exists = Guide::db()->where('web_id','=',$id)->first();
        if($exists){
            $res = Guide::db()->where('web_id','=',$id)->update($data);
            return $res ? $exists['id'] : false;
        }else{
            $data['web_id'] = $id;
            $id = Guide::db()->insertGetId($data);
            return $id;
        }
    }

    /**
     * 同步评测文章
     * @param $id
     * @param $title
     * @param $shorttitle
     * @param $writer
     * @param $content
     * @param $litpic
     * @param int $gid
     * @param int $agid
     * @param int $editor
     * @param int $sort
     * @param string $webdesc
     * @return bool
     */
    public static function syncOpinion($id,$title,$shorttitle,$writer,$content,$litpic,$gid=0,$agid=0,$editor=0,$sort=0,$webdesc='')
    {
        $data = array();
        $data['ftitle'] = $title;
        $data['shorttitle'] = $shorttitle;
        $data['writer'] = $writer;
        $data['content'] = $content;
        $data['litpic'] = $litpic;
        $data['gid'] = $gid;
        $data['agid'] = $agid;
        $data['addtime'] = time();
        $data['editor'] = $editor;
        $data['sort'] = $sort;
        $data['webdesc'] = $webdesc;
        $exists = Opinion::db()->where('web_id','=',$id)->first();
        if($exists){
            $res = Opinion::db()->where('web_id','=',$id)->update($data);
            return $res ? $exists['id'] : false;
        }else{
            $data['web_id'] = $id;
            $id = Opinion::db()->insertGetId($data);
            return $id;
        }
    }


    /**
     * 同步视频
     * @param $id
     * @param $title
     * @param $type
     * @param $litpic
     * @param $writer
     * @param $video
     * @param $description
     * @param int $editor
     * @param int $gid
     * @param int $agid
     * @return bool
     */
    public static function syncVideo($id,$title,$type,$litpic,$writer,$video,$description,$editor=0,$gid=0,$agid=0)
    {
        $data = array();
        $data['vname'] = $title;
        $data['type'] = $type;
        $data['litpic'] = $litpic;
        $data['writer'] = $writer;
        $data['video'] = $video;
        $data['description'] = $description;
        $data['editor'] = $editor;
        $data['gid'] = $gid;
        $data['agid'] = $agid;
        $exists = Videos::db()->where('web_id','=',$id)->first();
        if($exists){
            $res = Videos::db()->where('web_id','=',$id)->update($data);
            return $res ? $exists['id'] : false;
        }else{
            $data['web_id'] = $id;
            $id = Videos::db()->insertGetId($data);
            return $id;
        }
    }

}