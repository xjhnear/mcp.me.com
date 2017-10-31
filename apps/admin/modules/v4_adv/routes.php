<?php
use Illuminate\Support\Facades\Route;


Route::controller('v4adv/popup','modules\v4_adv\controllers\PopupController');
Route::controller('v4adv/carousel','modules\v4_adv\controllers\CarouselController');
Route::controller('v4adv/video','modules\v4_adv\controllers\VideoController');
Route::controller('v4adv/recommend','modules\v4_adv\controllers\RecommendController');
Route::controller('v4adv/indexbanner','modules\v4_adv\controllers\IndexbannerController');
Route::controller('v4adv/banner','modules\v4_adv\controllers\BannerController');
Route::controller('v4adv/vicebanner','modules\v4_adv\controllers\VicebannerController');
Route::controller('v4adv/webgame','modules\v4_adv\controllers\WebgameController');
Route::controller('v4adv/pcgame','modules\v4_adv\controllers\PcgameController');
Route::controller('v4adv/gameinfo','modules\v4_adv\controllers\GameinfoController');
Route::controller('v4adv/gameinfotop','modules\v4_adv\controllers\GameinfotopController');
Route::controller('v4adv/guessyoulike','modules\v4_adv\controllers\GuessyoulikeController');
Route::controller('v4adv/startup','modules\v4_adv\controllers\StartupController');
Route::controller('v4adv/task','modules\v4_adv\controllers\TaskController');
Route::controller('v4adv/btndownload','modules\v4_adv\controllers\BtnDownloadController');
Route::controller('v4adv/core','modules\v4_adv\controllers\CoreController');


