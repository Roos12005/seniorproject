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
Route::get('/aboutus','AboutUsController@getAboutUs');
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

//Admin 
Route::get('/','AdminController@index');
Route::get('/database','DatabaseController@index');
Route::post('/deleteData', 'AdminController@deleteData');
