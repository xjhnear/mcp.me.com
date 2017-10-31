<?php
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

Route::controller('forum/api','modules\forum\controllers\ApiController');
Route::controller('forum/channel','modules\forum\controllers\ChannelController');
Route::controller('forum/game','modules\forum\controllers\GameController');
Route::controller('forum/topic','modules\forum\controllers\TopicController');
Route::controller('forum/notice','modules\forum\controllers\NoticeController');
Route::controller('forum/inform','modules\forum\controllers\InformController');
