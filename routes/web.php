<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
Route::get('/languages', 'LanguageController@index');

Auth::routes();

Route::group(['prefix' => '/', 'middleware' => 'guest'], function () {
    Route::get('/', 'Auth\LoginController@getLogin');

    Route::get('/register', 'Auth\RegisterController@getRegister');

    Route::post('/register', [
        'as' => 'register-user',
        'uses' => 'Auth\RegisterController@register',
    ]);

    Route::get('/login', 'Auth\LoginController@getLogin');

    Route::post('/login', [
        'as' => 'login-user',
        'uses' => 'Auth\LoginController@login',
    ]);

    Route::get('/redirect/{provider}', 'User\SocialAuthController@redirect');

    Route::get('/callback/{provider}', 'User\SocialAuthController@callback');
});

Route::get('/', 'SurveyController@index');

Route::get('/logout', 'Auth\LoginController@logout');


Route::group(['prefix' => '/supper-admin', 'middleware' => 'supperadmin'], function () {
    Route::resource('/request', 'Admin\RequestController', [
        'only' => ['index'],
    ]);

    Route::post('/request/update/{id}', 'Admin\RequestController@update');

    Route::post('/request/delete', 'Admin\RequestController@destroy');
});

Route::group(['prefix' => '/admin', 'middleware' => 'admin'], function () {
    Route::resource('/dashboard', 'Admin\DashboardController', [
        'only' => ['index'],
    ]);

    Route::resource('/survey', 'Admin\SurveyController', [
        'only' => ['index', 'update'],
    ]);

    Route::post('/destroy-survey', 'Admin\SurveyController@destroySurvey');

    Route::resource('/user', 'Admin\UserController', [
        'only' => ['index', 'update', 'show'],
    ]);

    Route::post('/change-status-user/{status}', 'Admin\UserController@changeStatus');

    Route::get('/search', 'Admin\UserController@search');

    Route::resource('/request', 'Admin\RequestController', ['only' => 'store']);

    Route::post('/request/cancel', 'Admin\RequestController@cancel');

    Route::resource('/feedback', 'FeedbackController', [
        'only' => [
            'index',
            'show',
            'destroy',
        ],
    ]);

    Route::post('/post-update-feedback', 'FeedbackController@update');
});

Route::get('/', 'SurveyController@index');

Route::post('/invite/send/{id}/{type}', 'SurveyController@inviteUser');

Route::post('/delete-survey', 'SurveyController@delete');

Route::post('/close-survey/{id}', 'SurveyController@close');

Route::get('/export/{id}/{type}', 'User\ExportController@export');

Route::group(['prefix' => '/home'], function () {
    Route::get('/', 'SurveyController@index');

    Route::group(['prefix' => '/', 'middleware' => 'auth'], function () {
        Route::post('/mark-survey', 'User\LikeController@markLike');

        Route::get('/list-invited', 'SurveyController@getInviteSurvey');

        Route::get('/individual', 'SurveyController@listSurveyUser');

        Route::get('/user/detail', 'User\UserController@show');

        Route::put('/user/update', 'User\UserController@update');

        Route::get('/private/{token}', 'AnswerController@answerPrivate');

        Route::resource('/save', 'User\SaveTempController', [
            'only' => ['store', 'index'],
        ]);

        Route::get('/show-temp', 'User\SaveTempController@show');
    });
});

Route::get('/detail/{token}', 'AnswerController@show');

Route::get('/user-answer-results/{token}/{type}', 'AnswerController@userAnswerResults')->name('user-answer-results');

Route::post('/add-temp/{type}', 'TempController@addTemp');

Route::post('/create', [
    'as' => 'create',
    'uses' => 'SurveyController@create',
]);

Route::post('/update-setting/{id}/{token}', 'SettingController@update');

Route::post('/survey/result/{token}', 'ResultController@result');

Route::get('/complete/{token}', 'SurveyController@complete');

Route::post('/search', 'SurveyController@search');

Route::get('/public/{token}', 'AnswerController@answerPublic');

Route::get('/show/{token}', 'SurveyController@showDetail');

Route::put('/update-survey/{id}', 'SurveyController@updateSurvey');

Route::get('/show-user-answer-detail/{surveyId}/{userId?}/{email?}/{name?}', 'AnswerController@showMultiHistory');

Route::post('/update/{id}/{token}', 'SurveyController@updateSurveyContent');

Route::get('/feedback', 'FeedbackController@getFeedback');

Route::post('/post-feedback', 'FeedbackController@create');

Route::get('/answer/completed/{survey}/{name?}', 'ResultController@show');
