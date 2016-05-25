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

Route::get('/analysis/{id}','AnalysisController@main');
Route::get('/aboutus','PageController@getAboutUs');
Route::get('/spark','SparkController@main');
Route::post('/getEstimation','AdminController@getEstimation');
Route::post('/processSetup','AdminController@processSetup');
Route::post('/startProcess','AdminController@startProcess');

//Get Data
Route::get('/exportCSV','AdminController@exportCSV');
Route::get('getCDR/{id}','AnalysisController@getCDR');
Route::get('getCommunities/{id}','AnalysisController@getCommunities');
Route::get('getNodeCommunity/{id}','AnalysisController@getNodeCommunity');
Route::get('getCommunityOfCommunity/{id}','AnalysisController@getCommunityOfCommunity');
Route::get('getCarrier/{id}','AnalysisController@getCarrier');
Route::get('getNodeInSelectedCommunity/{id}','AnalysisController@getNodeInSelectedCommunity');
Route::get('getNodeCommunityProfile/{id}','AnalysisController@getNodeCommunityProfile');
Route::get('getNeighbors/{id}','AnalysisController@getNeighbors');
Route::get('findCommunity/{id}','AnalysisController@findCommunity');

//Admin 
Route::get('/','AdminController@index');
Route::get('/database','DatabaseController@index');
Route::post('/deleteData', 'AdminController@deleteData');
Route::post('/database/uploadcdr', 'DatabaseController@uploadCDR');
Route::post('/database/uploadprofile', 'DatabaseController@uploadProfile');
Route::post('/database/writedb', 'DatabaseController@writeToDatabase');
Route::post('/database/delete', 'DatabaseController@deleteDatabase');
Route::post('/database/rename', 'DatabaseController@renameDatabase');

Route::get('/test', 'TestController@index');
Route::get('/test/getxy', 'TestController@getXY');
