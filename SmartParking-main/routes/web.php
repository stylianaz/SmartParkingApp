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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/map', 'MapController@index')->name('map.searchmap');
Route::get('/mapadmin', 'MapController@indexAdmin')->name('map.adminmap');
Route::get('/mapprovider', 'MapController@indexProvider')->name('map.providermap');
Route::get('/importexceladmin', 'MapController@importexceladmin')->name('import.exceladmin');
Route::post('/importexcel', 'MapController@importexcel')->name('import.excel');
Route::get('/getbookmarks/{user_id}', 'MapController@getbookmarks')->name('get.bookmarks');
Route::get('/getReports/{reporttype}', 'MapController@getreports')->name('get.reports');
Route::get('/predictionsite', 'ParkingPredictionController@getOldPredictionSite');

Route::group(['middleware' => 'auth'], function () {
    Route::post('getcarpark', [
        'uses' => 'ParkingController@getCarpark',
        'as' => 'api.getcarpark',
        'middleware' => 'auth',
    ]);

    Route::post('getprediction', [
        'uses' => 'ParkingPredictionController@getPrediction',
        'as' => 'api.getprediction',
        'middleware' => 'auth',
    ]);

    Route::post('getparkingneardestionation', [
        'uses' => 'APIController@getParkinsNearDestination',
        'as' => 'api.getparkingneardestionation',
        'middleware' => 'auth',
    ]);
    Route::post('getAddressAutocomplete', [
        'uses' => 'APIController@getAddressAutocomplete',
        'as' => 'api.getAddressAutocomplete',
        'middleware' => 'auth',
    ]);
    Route::post('getParkings', [
        'uses' => 'APIController@getParkings',
        'as' => 'api.getParkings',
        'middleware' => 'auth',
    ]);
    Route::post('getParkingsOwner', [
        'uses' => 'APIController@getParkingsOwner',
        'as' => 'api.getParkingsOwner',
        'middleware' => 'auth',
    ]);

    Route::post('getWeightedParkings', [
        'uses' => 'APIController@getWeightedParkings',
        'as' => 'api.getWeightedParkings',
        'middleware' => 'auth',
    ]);
    Route::post('getAddressfromCoordinates', [
        'uses' => 'APIController@getAddressfromCoordinates',
        'as' => 'api.getAddressfromCoordinates',
        'middleware' => 'auth',
    ]);
    Route::post('store', [
        'uses' => 'MapController@store',
        'as' => 'map.storeParking',
        'middleware' => 'auth',
    ]);
    Route::post('storeBookmark', [
        'uses' => 'MapController@storeBookmark',
        'as' => 'map.storeBookMark',
        'middleware' => 'auth',
    ]);
    Route::post('removeBookmark', [
        'uses' => 'MapController@removeBookmark',
        'as' => 'map.removeBookmark',
        'middleware' => 'auth',
    ]);
    Route::post('reserve', [
        'uses' => 'MapController@reserve',
        'as' => 'map.reserverParkingPlace',
        'middleware' => 'auth',
    ]);


    Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {

        Route::get('/users', [
            'uses' => 'UserController@index',
            'as' => 'users'
        ]);

        Route::get('/user/create', [
            'uses' => 'UserController@create',
            'as' => 'user.create'
        ]);

        Route::post('/user/store', [
            'uses' => 'UserController@store',
            'as' => 'user.store'
        ]);

        Route::get('/user/edit', [
            'uses' => 'UserController@edit',
            'as' => 'user.edit'
        ]);

        Route::get('/user/profile/edit', [
            'uses' => 'UserController@editProfile',
            'as' => 'user.profile.edit'
        ]);

        Route::post('/user/profile/update', [
            'uses' => 'UserController@updateProfile',
            'as' => 'user.profile.update'
        ]);


        Route::post('user/deactivate', [
            'uses' => 'UserController@deactivate',
            'as' => 'user.deactivate'
        ]);

        Route::post('user/activate', [
            'uses' => 'UserController@activate',
            'as' => 'user.activate'
        ]);

    });
});