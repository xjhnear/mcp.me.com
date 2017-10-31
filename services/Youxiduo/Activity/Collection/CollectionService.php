<?php
/**
 * Created by PhpStorm.
 * User: jfj
 * Date: 2015/3/31
 * Time: 11:19
 */

namespace Youxiduo\Activity\Collection;

use Youxiduo\Activity\Model\ActivityCollection;
use Youxiduo\Activity\Model\ActivityCollectionInfo;

class CollectionService{
    /**
     * 获取游戏大区信息
     * @param array $field
     * @param string $platform
     * @return array
     */
    public static function getDistrict( $field = array(),$platform=''){
        $out = array();
        $result = ActivityCollection::getDistrict($platform,$field);
        if($platform == ''){
            foreach($result as $v){
                if($v['platform'] == 'ios' || $v['platform'] == 'android'){
                    $out[$v['platform']][] = $v;
                }else{
                    $out['other'][] = $v;
                }
            }
        }else{
            $out[$platform] = $result;
        }
        return $out;
    }

    /**
     * 添加信息
     * @param array $data
     */
    public static function addInfo(array $data){
        $data['addtime'] = time();
        return ActivityCollectionInfo::addInfo($data);
    }
}