<?php


// Check for permissions!
Route::group(['prefix' => 'custom-reports'], function()
{	
	Route::name('custom_reports_')->group(function () 
	{
		Route::get('', 'CustomReportController@index')->name('index');
		Route::get('create', 'CustomReportController@create')->name('create');
		Route::post('save', 'CustomReportController@save')->name('save');
		Route::post('{id}/edit', 'CustomReportController@edit')->name('edit');
		Route::post('{id}/delete', 'CustomReportController@destroy')->name('delete');
		// Some of the urls could be used for API.
		Route::get('get-table-columns', 'CustomReportController@get_table_columns');
		Route::get('generate-where-clause', 'CustomReportController@generate_where_clause');
		Route::get('generate-table-columns', 'CustomReportController@generate_table_columns');
		Route::get('generate-table-relationships', 'CustomReportController@generate_table_relationships');
		Route::get('generate-join-columns', 'CustomReportController@generate_join_columns');
		Route::get('preview-results', 'CustomReportController@get_sql_statement')->name('preview_results');
		Route::post('update-sql-statement', 'CustomReportController@update_sql_statement')->name('update_sql_statement');

		//Route::get('execute', 'CustomReportController@execute')->name('execute'); // Not in use.
		Route::post('export', 'CustomReportController@export')->name('export');
		
	});	
});
