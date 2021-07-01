<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authorization
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// 用户认证模块，包括登录注册等
Auth::routes();

// Client 用户前台各个页面
Route::get('/', 'Client\HomeController@index')->name('home');
Route::get('/home', 'Client\HomeController@index');
Route::post('/get_notice','Client\HomeController@get_notice')->name('get_notice');
Route::get('/status','Client\StatusController@index')->name('status');
Route::post('/ajax_get_status','Client\StatusController@ajax_get_status')->name('ajax_get_status');
Route::get('/problems','Client\ProblemController@problems')->name('problems');
Route::get('/problem/{id}','Client\ProblemController@problem')->middleware('CheckBlacklist')->where(['id'=>'[0-9]+'])->name('problem');
Route::get('/contests','Client\ContestController@contests')->name('contests');
Route::get('/contests/{type}','Client\ContestController@contests')->name('contests');
Route::get('/standings','Client\UserController@standings')->name('standings');
Route::get('/user/{username}','Client\UserController@user')->name('user');
Route::get('/change_language/{lang}','Client\UserController@change_language')->name('change_language');
Route::middleware(['auth','CheckBlacklist'])->where(['id'=>'[0-9]+'])->group(function (){
    Route::get('/solution/{id}','Client\StatusController@solution')->name('solution');
    Route::get('/solution/{id}/wrong_data/{type}','Client\StatusController@solution_wrong_data')
        ->where(['type'=>'(in|out)'])->name('solution_wrong_data');
    Route::any('/user/{username}/edit','Client\UserController@user_edit')->name('user_edit');
    Route::any('/user/{username}/password_reset','Client\UserController@password_reset')->name('password_reset');
    Route::post('/status/submit_solution','Client\StatusController@create')->name('submit_solution');
//    CK editor upload image
    Route::post('/ck_upload_image','UploadController@ck_upload_image')->name('ck_upload_image');

    //tag marking
    Route::post('/tag_mark','Client\ProblemController@tag_mark')->name('tag_mark');

});

//  题目页面讨论板模块
Route::post('/load_discussion', 'Client\ProblemController@load_discussion')->name('load_discussion');
Route::middleware(['auth','CheckBlacklist'])->group(function () {
    Route::post('/edit_discussion/{pid}', 'Client\ProblemController@edit_discussion')->name('edit_discussion');
});
Route::middleware(['auth','CheckBlacklist','Privilege:problem_tag'])->group(function () {
    Route::post('/delete_discussion', 'Client\ProblemController@delete_discussion')->name('delete_discussion');
    Route::post('/top_discussion', 'Client\ProblemController@top_discussion')->name('top_discussion');
    Route::post('/hidden_discussion', 'Client\ProblemController@hidden_discussion')->name('hidden_discussion');
});


// Contest，用户前台竞赛页面所有路由
Route::prefix('contest/{id}')->name('contest.')->where(['id'=>'[0-9]+'])->where(['pid'=>'[0-9]+'])->group(function () {

    Route::middleware(['auth','CheckContest','CheckBlacklist'])->group(function (){
        Route::get('/', 'Client\ContestController@home')->name('home');
        Route::get('/problem/{pid}', 'Client\ContestController@problem')->name('problem');
        Route::get('/status', 'Client\ContestController@status')->name('status');
        Route::post('/cancel_lock', 'Client\ContestController@cancel_lock')->name('cancel_lock');//取消封榜
        Route::get('/notices', 'Client\ContestController@notices')->name('notices');//公告
        Route::post('/get_notice', 'Client\ContestController@get_notice')->name('get_notice');//获取一条公告
        Route::post('/edit_notice', 'Client\ContestController@edit_notice')->name('edit_notice');//编辑/添加一条公告
        Route::post('/delete_notice/{nid}', 'Client\ContestController@delete_notice')->name('delete_notice');//删除一条公告

        Route::middleware(['Privilege:balloon,client'])->group(function (){//气球,需要权限
            Route::get('/balloons', 'Client\ContestController@balloons')->name('balloons');
            Route::post('/deliver_ball/{bid}', 'Client\ContestController@deliver_ball')->name('deliver_ball');
        });
        Route::middleware(['Privilege:contest,client'])->group(function (){
            Route::post('/start_to_judge', 'Client\ContestController@start_to_judge')->name('start_to_judge');
        });
    });
    Route::any('/password', 'Client\ContestController@password')->middleware(['auth'])->name('password');
    Route::get('/rank', 'Client\ContestController@rank')->name('rank');
});


// Administration 管理员后台页面所有路由
Route::middleware(['auth','CheckBlacklist'])->prefix('admin')->name('admin.')->where(['id'=>'[0-9]+'])->group(function () {

    Route::middleware(['Privilege:teacher,client'])->group(function (){
        Route::get('/', 'Admin\HomeController@index')->name('home');
    });

    Route::middleware(['Privilege:admin'])->group(function (){
        // 判题端指令：启动、重启、停止
        Route::post('/cmd_polling', 'Admin\HomeController@cmd_polling')->name('cmd_polling');
        // 修改配置文件.env。包括修改判题端配置
        Route::post('/modify_env', 'Admin\HomeController@modify_env')->name('modify_env');
    });

//    manage notice
    Route::middleware(['Privilege:admin'])->prefix('notice')->name('notice.')->group(function (){
        Route::get('/list','Admin\NoticeController@list')->name('list');
        Route::any('/add','Admin\NoticeController@add')->name('add');
        Route::any('/update/{id}','Admin\NoticeController@update')->name('update');
        Route::post('/delete','Admin\NoticeController@delete')->name('delete');
        Route::post('/update/state','Admin\NoticeController@update_state')->name('update_state');
    });

//   manage user
    Route::middleware(['Privilege:admin'])->prefix('user')->name('user.')->group(function (){
        Route::get('/list', 'Admin\UserController@list')->name('list');
        Route::get('/privileges', 'Admin\UserController@privileges')->name('privileges');
        Route::any('/create','Admin\UserController@create')->name('create');
        Route::post('/delete','Admin\UserController@delete')->name('delete');
        Route::post('/update/revise','Admin\UserController@update_revise')->name('update_revise');
        Route::post('/privilege/create','Admin\UserController@privilege_create')->name('privilege_create');
        Route::post('/privilege/delete','Admin\UserController@privilege_delete')->name('privilege_delete');
        Route::any('/reset_pwd','Admin\UserController@reset_pwd')->name('reset_pwd');
        Route::get('/blacklist', 'Admin\UserController@blacklist')->name('blacklist');
        Route::post('/blacklist/create','Admin\UserController@blacklist_create')->name('blacklist_create');
        Route::post('/blacklist/delete','Admin\UserController@blacklist_delete')->name('blacklist_delete');
    });

//   manage problem list
    Route::middleware(['Privilege:problem_list'])->prefix('problem')->name('problem.')->group(function (){
        Route::get('/list', 'Admin\ProblemController@list')->name('list');
    });

//   manage problem editor
    Route::middleware(['Privilege:edit_problem'])->prefix('problem')->name('problem.')->group(function (){
        Route::any('/add','Admin\ProblemController@add')->name('add');
        Route::get('/update','Admin\ProblemController@update')->name('update');
        Route::any('/update/{id}','Admin\ProblemController@update')->name('update_withId');
        Route::post('/update/hidden','Admin\ProblemController@update_hidden')->name('update_hidden');
        Route::get('/get_spj/{pid}','Admin\ProblemController@get_spj')->name('get_spj');
    });

//   manage problem tag
    Route::middleware(['Privilege:problem_tag'])->prefix('problem')->name('problem.')->group(function (){
        Route::get('/tags', 'Admin\ProblemController@tags')->name('tags');
        Route::post('/tag_delete', 'Admin\ProblemController@tag_delete')->name('tag_delete');
        Route::get('/tag_pool', 'Admin\ProblemController@tag_pool')->name('tag_pool');
        Route::post('/tag_pool_delete', 'Admin\ProblemController@tag_pool_delete')->name('tag_pool_delete');
        Route::post('/tag_pool_hidden', 'Admin\ProblemController@tag_pool_hidden')->name('tag_pool_hidden');
    });

//   manage problem data
    Route::middleware(['Privilege:problem_data'])->prefix('problem')->name('problem.')->group(function (){
        Route::get('/test_data','Admin\ProblemController@test_data')->name('test_data');
        Route::post('/upload/data','Admin\ProblemController@upload_data')->name('upload_data');
        Route::post('/get_data','Admin\ProblemController@get_data')->name('get_data');
        Route::post('/update/data','Admin\ProblemController@update_data')->name('update_data');
        Route::post('/delete/data','Admin\ProblemController@delete_data')->name('delete_data');
    });

//   manage problem rejudge
    Route::middleware(['Privilege:admin'])->prefix('problem')->name('problem.')->group(function (){
        Route::any('/rejudge','Admin\ProblemController@rejudge')->name('rejudge');
    });

//   manage problem import export
    Route::middleware(['Privilege:admin'])->prefix('problem')->name('problem.')->group(function (){
        Route::get('/import_export','Admin\ProblemController@import_export')->name('import_export');
        Route::any('/import','Admin\ProblemController@import')->name('import');
        Route::any('/export','Admin\ProblemController@export')->name('export');
    });

//   manage contest
    Route::middleware(['Privilege:contest'])->prefix('contest')->name('contest.')->group(function (){
        Route::get('/list','Admin\ContestController@list')->name('list');
        Route::any('/add','Admin\ContestController@add')->name('add');
        Route::any('/update/{id}','Admin\ContestController@update')->name('update');
        Route::post('/delete','Admin\ContestController@delete')->name('delete');
        Route::post('/delete/file/{id}','Admin\ContestController@delete_file')->name('delete_file');
        Route::post('/update/hidden','Admin\ContestController@update_hidden')->name('update_hidden');
        Route::post('/clone','Admin\ContestController@clone')->name('clone');
        Route::post('/set_top','Admin\ContestController@set_top')->name('set_top');
    });

//    setting
    Route::any('/settings','Admin\SettingController@settings')->middleware(['Privilege:admin'])->name('settings');
    Route::post('/upgrade_oj', 'Admin\SettingController@upgrade_oj')->middleware(['Privilege:admin'])->name('upgrade_oj');
});
