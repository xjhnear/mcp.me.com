<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/
use Illuminate\Support\Facades\Artisan;
Artisan::add(new ShellCmd());
//游戏圈动态
Artisan::add(new CircleFeed());
//加入游戏圈
Artisan::add(new JoinCircle());
//用户动态
Artisan::add(new UserFeed());
//回复我
Artisan::add(new UserAtme());
//消息数分发
Artisan::add(new MsgNum());
//Apple消息推送
Artisan::add(new ApplePush());
//清除缓存
Artisan::add(new ClearCache());
//订阅礼包
Artisan::add(new ReserveGiftbag());
//同步论坛数据
Artisan::add(new SyncForum());
//同步用户到亲加
Artisan::add(new ImportUser());

