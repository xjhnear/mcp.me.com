<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
/*-------------------------------认证-------------------------------*/
//登录√
Route::get('account/login',array('before'=>'uri_verify','uses'=>'AccountController@getLogin'));
//注册√
Route::get('account/register',array('before'=>'uri_verify','uses'=>'AccountController@getRegister'));
//第三方登录√
Route::get('account/snslogin',array('before'=>'uri_verify','uses'=>'AccountController@getSnslogin'));
//第三方注册√
Route::get('account/snsregister',array('before'=>'uri_verify','uses'=>'AccountController@getSnsregister'));
//绑定第三方
Route::get('account/bind_sns',array('before'=>'uri_verify','uses'=>'AccountController@bindSns'));
//验证邮箱是否被占用
Route::get('account/verify-email',array('before'=>'uri_verify','uses'=>'AccountController@getVerifyEmail'));
//验证码
Route::get('account/verifycode',array('uses'=>'AccountController@getVerifyCode'));
//Route::get('account/verify',array('before'=>'uri_verify','uses'=>'AccountController@getVerify'));
//用户注销
Route::post('account/logout',array('before'=>'uri_verify','uses'=>'AccountController@postLogout'));

/*-------------------------------用户-------------------------------*/
//获取用户信息√
Route::get('user/info',array('before'=>'uri_verify','uses'=>'UserController@getInfo'));
//头像
Route::get('user/avatar/{uid}',array('uses'=>'UserController@getAvatar'));
//用户游币
Route::get('user/money',array('before'=>'uri_verify','uses'=>'UserController@getMoney'));
//完善资料√
Route::post('user/edit',array('before'=>'uri_verify','uses'=>'UserController@postEdit'));
//我的动态
Route::get('user/feeds',array('before'=>'uri_verify','uses'=>'UserController@feeds'));
//@我
Route::get('user/atme',array('before'=>'uri_verify','uses'=>'UserController@atme'));


/*-------------------------------聊天-------------------------------*/
//添加会话用户
Route::get('chat/adduser',array('before'=>'uri_verify','uses'=>'ChatController@addUser'));
//会话用户列表
Route::get('chat/users',array('before'=>'uri_verify','uses'=>'ChatController@users'));
//聊天记录
Route::get('chat/detail',array('before'=>'uri_verify','uses'=>'ChatController@detail'));
//删除聊天记录
Route::get('chat/delete',array('before'=>'uri_verify','uses'=>'ChatController@delete'));
//发送聊天内容
Route::post('chat/send',array('before'=>'uri_verify','uses'=>'ChatController@sendMessage'));

/*-------------------------------系统消息-------------------------------*/
//系统通知√
Route::get('message/notice',array('before'=>'uri_verify','uses'=>'MessageController@notice'));
//消息数√
Route::get('message/number',array('before'=>'uri_verify','uses'=>'MessageController@msgNumber'));
//阅读系统消息√
Route::get('message/read',array('before'=>'uri_verify','uses'=>'MessageController@read'));
//删除系统消息√
Route::get('message/delete',array('before'=>'uri_verify','uses'=>'MessageController@delete'));

/*-------------------------------问答/论坛-------------------------------*/
//论坛首页√
Route::get('forum/home',array('before'=>'uri_verify','uses'=>'ForumController@home'));
//论坛版块帖子列表√
Route::get('forum/topic-list',array('before'=>'uri_verify','uses'=>'ForumController@getTopicList'));
//圈友√
Route::get('forum/friends',array('before'=>'uri_verify','uses'=>'ForumController@circleFriends'));
//发帖√
Route::post('topic/post-topic',array('before'=>'uri_verify','uses'=>'TopicController@postPostTopic'));
//删帖√
Route::get('topic/delete',array('before'=>'uri_verify','uses'=>'TopicController@getDelete'));
/*-------------------------------评论-------------------------------*/
//评论√
Route::get('comment/list',array('before'=>'uri_verify','uses'=>'CommentController@home'));
//发评论/回复评论√
Route::post('comment/post-comment',array('before'=>'uri_verify','uses'=>'CommentController@postComment'));
//删评论√
Route::get('comment/delete-comment',array('before'=>'uri_verify','uses'=>'CommentController@deleteComment'));
//设置最佳答案√
Route::get('comment/set-best',array('before'=>'uri_verify','uses'=>'CommentController@setBest'));

/*-------------------------------关系-------------------------------*/
//好友
Route::get('relation/friends',array('before'=>'uri_verify','uses'=>'RelationController@getFriends'));
//关注√
Route::get('relation/follows',array('before'=>'uri_verify','uses'=>'RelationController@getFollows'));
//粉丝√
Route::get('relation/followers',array('before'=>'uri_verify','uses'=>'RelationController@getFollowers'));
//添加关注√
Route::get('relation/follow-create',array('before'=>'uri_verify','uses'=>'RelationController@getFollowCreate'));
//取消关注√
Route::get('relation/follow-destroy',array('before'=>'uri_verify','uses'=>'RelationController@getFollowDestroy'));

Route::controller('doc','DocController');
/*-------------------------------首屏-------------------------------*/
//首页√
Config::set('get home',array('3.0.0','3.1.0','3.1.5','3.1.6','3.2.0','3.3.0'));
Route::get('home',array('before'=>'uri_verify','uses'=>'HomeController@index'));

/*-------------------------------资讯-------------------------------*/
//资料大全√
Route::get('article/home',array('before'=>'uri_verify','uses'=>'ArticleController@home'));
//文章详情(包括新闻、攻略、评测、新游、主题帖)√
Route::get('article/detail',array('before'=>'uri_verify','uses'=>'ArticleController@detail'));
//资讯中心-新闻√
Route::get('article/news',array('before'=>'uri_verify','uses'=>'ArticleController@news'));
//资讯中心-攻略合集√
Route::get('article/guide_collect',array('before'=>'uri_verify','uses'=>'ArticleController@guide_collect'));
//资讯中心-攻略列表√
Route::get('article/guide_list',array('before'=>'uri_verify','uses'=>'ArticleController@guide_list'));
//资讯中心-评测√
Route::get('article/opinion',array('before'=>'uri_verify','uses'=>'ArticleController@opinion'));
/*-------------------------------视频-------------------------------*/
//美女视频√
Route::get('video',array('before'=>'uri_verify','uses'=>'VideoController@girl'));
//视频详情√
Route::get('video/detail',array('before'=>'uri_verify','uses'=>'VideoController@detail'));

/*-------------------------------WCA-----------------------------*/
//wca 多栏目 list 列表
Route::get('wca/list',array('uses'=>'WcaController@getGuideLists'));


/*-------------------------------礼包-------------------------------*/
/*
//礼包列表√
Route::get('gift/home',array('before'=>'uri_verify','uses'=>'GiftController@home'));
//搜索礼包√
Route::get('gift/search',array('before'=>'uri_verify','uses'=>'GiftController@search'));
//礼包详情√
Route::get('gift/detail',array('before'=>'uri_verify','uses'=>'GiftController@detail'));
//我的礼包√
Route::get('gift/mygift',array('before'=>'uri_verify','uses'=>'GiftController@myGift'));
//领取礼包√
Route::get('gift/getgift',array('before'=>'uri_verify','uses'=>'GiftController@getGift'));
//我的预定√
Route::get('gift/myreserve',array('before'=>'uri_verify','uses'=>'GiftController@myReserveGift'));
//我的预定-删除√
Route::get('gift/delete-myreserve',array('before'=>'uri_verify','uses'=>'GiftController@removeMyReserveGift'));
//预定礼包√
Route::get('gift/reserve',array('before'=>'uri_verify','uses'=>'GiftController@reserveGift'));
*/


//礼包列表√
Route::get('gift/home',array('before'=>'uri_verify','uses'=>'GiftbagController@home'));
//搜索礼包√
Route::get('gift/search',array('before'=>'uri_verify','uses'=>'GiftbagController@search'));
//礼包详情√
Route::get('gift/detail',array('before'=>'uri_verify','uses'=>'GiftbagController@detail'));
//我的礼包√
Route::get('gift/mygift',array('before'=>'uri_verify','uses'=>'GiftbagController@myGift'));
//领取礼包√
Route::get('gift/getgift',array('before'=>'uri_verify','uses'=>'GiftbagController@getGift'));
//我的预定√
Route::get('gift/myreserve',array('before'=>'uri_verify','uses'=>'GiftbagController@myReserveGift'));
//我的预定-删除√
Route::get('gift/delete-myreserve',array('before'=>'uri_verify','uses'=>'GiftbagController@removeMyReserveGift'));
//预定礼包√
Route::get('gift/reserve',array('before'=>'uri_verify','uses'=>'GiftbagController@reserveGift'));


/*-------------------------------游戏-------------------------------*/
//热门游戏列表√
Route::get('game/hotgame',array('before'=>'uri_verify','uses'=>'GameController@hotgame'));
//最新更新列表√
Route::get('game/lastupdate',array('before'=>'uri_verify','uses'=>'GameController@lastupdate'));
//经典必玩√
Route::get('game/mustplay',array('before'=>'uri_verify','uses'=>'GameController@mustplay'));
//特色专题√
Route::get('game/collect',array('before'=>'uri_verify','uses'=>'GameController@collect'));
//特色专题-详情√
Route::get('game/collect_detail',array('before'=>'uri_verify','uses'=>'GameController@collect_detail'));
//评测表√
Route::get('game/test_table',array('before'=>'uri_verify','uses'=>'GameController@test_table'));
//信息介绍√
Route::get('game/info',array('before'=>'uri_verify','uses'=>'GameController@info'));
//新游预告√
Route::get('game/newgame',array('before'=>'uri_verify','uses'=>'GameController@newgame'));
//搜索提示√
Route::get('game/searchtip',array('before'=>'uri_verify','uses'=>'GameController@searchtip'));
//搜索结果√
Route::get('game/search',array('before'=>'uri_verify','uses'=>'GameController@getSearch'));
//猜你喜欢
Route::get('game/guess',array('before'=>'uri_verify','uses'=>'GameController@guess'));
//玩家推荐应用
Route::get('game/recommend',array('before'=>'uri_verify','uses'=>'GameController@recommend'));
//星座
Route::get('game/discovery',array('uses'=>'GameController@discovery'));
Route::get('game/tags',array('uses'=>'GameController@tags'));
Route::get('game/relation',array('uses'=>'GameController@relation'));
//游戏下载奖励
Route::get('game/download-money',array('before'=>'uri_verify','uses'=>'GameController@downloadMoney'));
//游戏下载统计
Route::get('game/download',array('before'=>'uri_verify','uses'=>'GameController@download'));

//远征队
Route::get('game/expedition',array('before'=>'uri_verify','uses'=>'GameController@expedition'));


/*--------------------------------排行-------------------------------*/
//Tags
Route::get('rank/tags',array('before'=>'uri_verify','uses'=>'RankController@tags'));
//类型
Route::get('rank/types',array('before'=>'uri_verify','uses'=>'RankController@types'));
//排行
Route::get('rank/list',array('before'=>'uri_verify','uses'=>'RankController@chart'));

/*--------------------------------广场-------------------------------*/
//
Route::get('plaza/home',array('before'=>'uri_verify','uses'=>'PlazaController@home'));

/*--------------------------------圈子-------------------------------*/
//游戏圈类型√
Route::get('circle/types',array('before'=>'uri_verify','uses'=>'CircleController@types'));
//游戏圈游戏√
Route::get('circle/games',array('before'=>'uri_verify','uses'=>'CircleController@games'));
//游戏圈主页√
Config::set('get circle/home',array('3.0.0','3.1.0','3.1.5','3.1.6','3.2.0','3.3.0'));
Route::get('circle/home',array('before'=>'uri_verify','uses'=>'CircleController@home'));
//schemesurl
Route::get('circle/schemesurl',array('before'=>'uri_verify','uses'=>'GameController@schemesurl'));
//匹配
Route::post('circle/matching',array('before'=>'uri_verify','uses'=>'CircleController@matching'));
//添加√
Route::get('circle/addgame',array('before'=>'uri_verify','uses'=>'CircleController@addgame'));
//删除√
Route::get('circle/removegame',array('before'=>'uri_verify','uses'=>'CircleController@removegame'));
//置顶√
Route::get('circle/gametostick',array('before'=>'uri_verify','uses'=>'CircleController@gametostick'));
//我的游戏圈√
Route::get('circle/mygamecircle',array('before'=>'uri_verify','uses'=>'CircleController@mygamecircle'));
//圈子动态√
Route::get('circle/feeds',array('before'=>'uri_verify','uses'=>'CircleController@feeds'));

/*-------------------------------任务-------------------------------*/
//任务列表√
Route::get('task/list',array('before'=>'uri_verify','uses'=>'TaskController@home'));
//可接受的任务数
Route::get('task/number',array('before'=>'uri_verify','uses'=>'TaskController@number'));
//签到
Route::get('task/checkin',array('before'=>'uri_verify','uses'=>'TaskController@checkin'));
//分享任务
Route::get('task/share',array('before'=>'uri_verify','uses'=>'TaskController@share'));
//最近一周连续签到记录
//Route::get('task/checkin_log',array('before'=>'uri_verify','uses'=>'TaskController@checkin_log'));

/*-------------------------------赞-------------------------------*/
//赞√
Route::get('like/dolike',array('before'=>'uri_verify','uses'=>'LikeController@dolike'));
//赞列表
Route::get('like/users',array('before'=>'uri_verify','uses'=>'LikeController@users'));

/*-------------------------------游币商城-----------------------------*/
/**
 * 商品列表√
 * @param int pageIndex
 * @param int pageSize
 * @param int cate_id 
 * 
 */
Route::get('shop/goods',array('before'=>'uri_verify','uses'=>'ShopController@goods'));

/**
 * 商品分类
 * 3.1.0新增接口
 */
Route::get('shop/cates',array('before'=>'uri_verify','uses'=>'ShopController@CateList'));

/**
 * 商品详情√
 * @param int atid 活动ID
 * @param int uid 用户UID 
 */
Route::get('shop/goods_detail',array('before'=>'uri_verify','uses'=>'ShopController@goods_detail'));

/**
 * 我的商品√
 * @param int pageIndex
 * @param int pageSize 
 * @param int uid 用户UID 
 */
Route::get('shop/mygoods',array('before'=>'uri_verify','uses'=>'ShopController@mygoods'));

/**
 * 兑换商品√
 * @param int atid 商品ID
 * @param int uid 用户UID 
 */
Route::get('shop/exchange',array('before'=>'uri_verify','uses'=>'ShopController@exchange'));

/*-------------------------------寻宝箱-----------------------------*/
//寻宝箱首页√
Route::get('hunt/home',array('before'=>'uri_verify','uses'=>'HuntController@home'));

/*-------------------------------活动-----------------------------*/
//
/**
 * 活动列表√
 * @param int gid 游戏ID
 * @param int pageIndex
 * @param int pageSize 
 */
Route::get('activity/ask-list',array('before'=>'uri_verify','uses'=>'ActivityController@getList'));

/**
 * 问答详情√
 * @param int atid 活动ID
 * @param int uid 用户UID 
 */
Route::get('activity/ask-detail',array('before'=>'uri_verify','uses'=>'ActivityController@AskDetail'));
/**
 * 提交回答√
 * @param int uid
 * @param int atid 
 * @param json answer[{'numid':1,'choice':'A'}]
 */
Route::get('activity/commit',array('before'=>'uri_verify','uses'=>'ActivityController@doCommit'));
/*-------------------------------系统-----------------------------*/
//配置√
Route::get('app/config',array('before'=>'uri_verify','uses'=>'AppController@getConfig'));

Route::get('app/simple-config',array('before'=>'uri_verify','uses'=>'AppController@simpleConfig'));

/**
 * 版本√
 * @param string appname 应用名称
 * @param string version 版本
 */
Route::get('app/check-version',array('before'=>'uri_verify','uses'=>'AppController@checkVersion'));

/*-------------------------------举报-----------------------------*/

/**
 * 举报主题√
 * @param int linkid 主题ID
 * @param int uid 举报人UID
 */
Route::get('inform/topic',array('before'=>'uri_verify','uses'=>'InformController@topic'));
/**
 * 举报评论√
 * @param int cid 评论ID
 * @param int uid 举报人UID
 * @param int typeID 评论类型 
 */
Route::get('inform/comment',array('before'=>'uri_verify','uses'=>'InformController@comment'));


/*-------------------------------分享-----------------------------*/
/**
 * 分享√
 * @param int type 分享类型[0:关于我们][1:游戏][2:新游][3:专题][4:攻略][5:评测][6:新闻][7:视频][8:帖子][9:活动][10:礼包][11:关于我们]
 * @param string shareid 分享资源ID  
 */
Route::get('share/to',array('before'=>'uri_verify','uses'=>'ShareController@to'));

/*-------------------------------广告-----------------------------*/

/**
 * 启动页广告√
 * @param string appname 应用名称
 * @param string version 版本
 * @param int isiphone5 0/1是否是iphone5
 */
Config::set('get adv/launch',array('3.0.0','3.1.0','3.1.5','3.1.6','3.2.0','3.3.0'));
Route::get('adv/launch',array('before'=>'uri_verify','uses'=>'AdvController@launch'));

/**
 * 弹窗广告√
 * @param string appname 应用名称
 * @param string version 版本
 * @param null|1 entrance 如果存在则为游戏详情广告否则为首页广告
 *  
 */
Config::set('get adv/detail',array('3.0.0','3.1.0','3.1.5','3.1.6','3.2.0','3.3.0'));
Route::get('adv/detail',array('before'=>'uri_verify','uses'=>'AdvController@openwin'));

/**
 * 统计√
 * @param string appname 应用名称
 * @param string version 版本
 * @param string advid
 * @param string mac
 * @param string idfa
 * @param string osversion
 * @param string code
 * @param int linkid
 * @param string location
 * @param string openudid
 * @param string source
 * @param int type
 * @param string os 
 */
Route::get('adv/advstat',array('before'=>'uri_verify','uses'=>'AdvController@advstat'));

/**
 * 激活统计√
 * @param string advid
 * @param string mac
 * @param string idfa
 */
Route::get('advcate/activestat',array('uses'=>'AdvController@activestat'));
/*-------------------------------临时版-----------------------------*/
//新闻√
Route::get('beta/news',array('before'=>'uri_verify','uses'=>'BetaController@news'));
//攻略大全√
Route::get('beta/guide',array('before'=>'uri_verify','uses'=>'BetaController@guide'));
//攻略合集√
Route::get('beta/guide-list',array('before'=>'uri_verify','uses'=>'BetaController@guideList'));

/*-------------------------------小游戏-----------------------------*/
//list 列表
Route::get('xgame/list',array('before'=>'uri_verify','uses'=>'XgameController@getlist'));
//游戏详情  article
Route::get('xgame/article',array('before'=>'uri_verify','uses'=>'XgameController@article'));

//增加热度
Route::get('xgame/dohot',array('before'=>'uri_verify','uses'=>'XgameController@doHot'));
//统计
Route::any('xgame/count',array('before'=>'uri_verify','uses'=>'XgameController@anyCount'));
//banner list
Route::get('xgame/bannerlist',array('before'=>'uri_verify','uses'=>'XgameController@getBannerList'));

//require_once 'routes_v4.php';

/*App::missing(function($exception){
	return Response::json(array('result'=>array(),'errorCode'=>11211,'errorMessage'=>'Page Is Not Exists!!'));
});
*/
App::error(function($exception){
    //return Response::json(array('result'=>array(),'errorCode'=>11211,'errorMessage'=>'Server Error!!'));
});

//require_once 'h5inline.php';
