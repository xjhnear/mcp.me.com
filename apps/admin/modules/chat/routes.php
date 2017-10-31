<?php
use Illuminate\Support\Facades\Route;

Route::controller('chat/chatroom','modules\chat\controllers\ChatroomController');
Route::controller('chat/chatactivity','modules\chat\controllers\ChatActivityController');