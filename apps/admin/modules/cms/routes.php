<?php
use Illuminate\Support\Facades\Route;

#Route::controller('cms/article','modules\cms\controllers\ArticleController');
Route::controller('cms/news','modules\cms\controllers\NewsController');
Route::controller('cms/video','modules\cms\controllers\VideoController');
Route::controller('cms/vtype','modules\cms\controllers\VtypeController');
Route::controller('cms/other','modules\cms\controllers\OtherController');
Route::controller('cms/opinion','modules\cms\controllers\OpinionController');
Route::controller('cms/guide','modules\cms\controllers\GuideController');
Route::controller('cms/gamevideo','modules\cms\controllers\GamesVideoController');
Route::controller('cms/summary','modules\cms\controllers\EndYaerSummaryController');