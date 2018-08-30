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


Route::get('/description', 'DescriptionController@getDescription');

Route::get('/docs', 'SwaggerController@swagger');

Route::any('/lifecycle/installed', 'Atlassian\LifecycleController@installed');

Route::get('/sync/teamwork/{appId}', 'SyncController@syncTeamworkApp')->name('syncTeamwork');
Route::get('/sync/link/{linkId}/{issueId}', 'SyncController@syncIssue');

Route::group([
        'middleware' => [ 'jwt' ]
    ],
    function() {
        Route::get('/admin', 'AdminController@home');
    }
);

Route::group(
    [
        'prefix' => '/jira',
        'middleware' => [ 'jwt' ]
    ],
    function() {

        Route::any('/issues/created', 'Atlassian\Jira\IssueController@created');
        Route::any('/issues/updated', 'Atlassian\Jira\IssueController@updated');
        Route::any('/issues/deleted', 'Atlassian\Jira\IssueController@deleted');

    }
);