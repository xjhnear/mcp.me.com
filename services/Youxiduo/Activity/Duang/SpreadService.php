<?php
/**
 * Created by PhpStorm.
 * User: Cody
 * Date: 2015/8/21
 * Time: 15:54
 */
namespace Youxiduo\Activity\Duang;

use Youxiduo\Helper\Utility;

class SpreadService{
    /**
     * 老用户成为推广员
     * @param $promoterUserId   用户UID
     * @param $promoterMobile   用户手机号
     * @param bool $alipayAccount   用户支付宝账户
     * @return bool|mixed|string
     */
    public static function becomePromoter($promoterUserId,$promoterMobile,$alipayAccount=false){
        $params = array(
            'promoterUserId' => $promoterUserId,
            'promoterMobile' => $promoterMobile
        );
        $alipayAccount && $params['alipayAccount'] = $alipayAccount;
        return Utility::loadByHttp(Config::get('app.android_api_url').'promotion/become_promoter',$params);
    }

    public static function relevancePromoter($promoterId,$promoterUserId,$userMobile){
        $params = array(
            'promoterId' => $promoterId,
            'promoterUserId' => $promoterUserId,
            'uesrMobile' => $userMobile
        );
        return Utility::loadByHttp(Config::get('app.android_api_url').'promotion/relevance_promoter',$params);
    }
}