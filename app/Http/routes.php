<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');

// Login LDAP
Route::get('auth/ldap', 'Auth\LDAPController@getLogin');
Route::post('auth/ldap', 'Auth\LDAPController@postLogin');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);


Route::get('/', function(){
    if(!Auth::check()){
        return Redirect::to('auth/ldap');
    }
	return view('react');
});

Route::get('/report', function(){

    return view('report');
});

Route::group(['middleware'=>'auth', 'prefix'=>'api/v1'], function(){

	Route::get('talent-section/{id}/talent-score', 'ApiController@getTalentScoreOfSection');


	Route::get('data', 'ApiController@getData');
	Route::get('pegawai/{user_nip}/talent-data', 'ApiController@getDataPegawai');
	Route::post('talent-score', 'ApiController@postTalent');
	Route::post('talent-score/{nip}/delete', 'ApiController@deleteTalent');
	Route::post('talent-score/{nip}/update', 'ApiController@updateTalent');

	Route::get('talent-group', 'ApiController@getGroupList');
	Route::get('talent-group/archived', 'ApiController@getArchivedGroupList');
	Route::post('talent-group', 'ApiController@createGroup');
	Route::get('talent-group/{group_id}/talent-section', 'ApiController@getSectionList');
	Route::post('talent-group/{group_id}/delete', 'ApiController@deleteGroup');
	Route::post('talent-group/{group_id}/duplicate', 'ApiController@duplicateGroup');
	Route::post('talent-group/{group_id}/toggle-archive', 'ApiController@toggleArchiveGroup');

    Route::get('talent-group/{group_id}/summary', 'ApiController@getSummary');

    Route::group(['prefix'=>'report'], function(){
        Route::get('class/{id}', 'ApiController@reportForClass');
        Route::get('group/{id}', 'ApiController@newReportForGroup');
    });
});



// SINGLE LOGIN
Route::get('remote-auth', function(){
    $identifier = \Request::get('identifier');
    $key = \Request::get('login_key');

    if(Session::get('login_key') != $key || !$key){
        return "Mau ngapain hayo?";
    }
    $user = \App\User::where('kemenkeu', $identifier)->first();
    \Auth::loginUsingId($user->id, TRUE);
    Session::forget('login_key');
    return \Redirect::to('/');
});

Route::get('remote-logout', function(){
    \Session::flush();
    \Auth::logout();
    $next = \Request::get('next');
    return Redirect::away('http://apps-itjen.kemenkeu.go.id/account/public/logout?next='.$next);
});
