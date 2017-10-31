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
//游戏圈动态
Artisan::add(new ProfileSync());
//生成会员
Artisan::add(new MakeUser());
//同步评论
Artisan::add(new DataSync());
//同步游戏
Artisan::add(new GameSync());
//应用初始化
Artisan::add(new AppInit());
//安卓
Artisan::add(new AndroidData());
//清除过期的分享任务
Artisan::add(new ClearTask());

