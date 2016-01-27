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
Route::post('processData','AnalysisController@processData');
Route::get('runMaven','AnalysisController@runMaven');
Route::get('/aboutus','AboutUsController@getAboutUs');

//Get Data
Route::get('getCDR','AnalysisController@getCDR');

Route::get('/admin','AdminController@index');


Route::get('/test','AnalysisController@test');

Route::get('/testsigma','AnalysisController@testgraph');

