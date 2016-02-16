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

// Route::get('/', function () {
//     return view('analysis.analysis');
// });

Route::get('/','AnalysisController@getIndex');
Route::get('/analysis/{id}','AnalysisController@main');
Route::post('processData','AnalysisController@processData');
Route::get('/aboutus','AboutUsController@getAboutUs');
Route::post('/getEstimation','AdminController@getEstimation');
Route::post('/processSetup','AdminController@processSetup');
Route::post('/startProcess','AdminController@startProcess');
//Get Data

Route::get('getCDR','AnalysisController@getCDR');
//Route::get('getCDR/{id}','AnalysisController@getCDR');
Route::get('getCommunities','AnalysisController@getCommunities');
Route::get('getNodeCommunity','AnalysisController@getNodeCommunity');
Route::get('getCommunityOfCommunity','AnalysisController@getCommunityOfCommunity');
Route::get('getCarrier','AnalysisController@getCarrier');
Route::get('getNodeInSelectedCommunity','AnalysisController@getNodeInSelectedCommunity');

Route::get('/admin','AdminController@index');
Route::get('/database','DatabaseController@index');

Route::post('/deleteData', 'AdminController@deleteData');

Route::get('/test','AnalysisController@test');

Route::get('/testsigma','AnalysisController@testgraph');

