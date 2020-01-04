<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\CustomReports;
use App\Http\Controllers\SqlFormatter;

// What has not been done (on purpose):
// 1) Use a PHP library to export an excel file.
// 2) Use a Permission Manager library (like Spatie) to give access to the right people.

class CustomReportController extends Controller
{
    protected $report_model;

    public function __construct(CustomReports $report_model)
    {
        $this->report_model = $report_model;
    }

    public function index()
    {
        return View::make('custom_reports.index', ['reports' => CustomReports::all()]);
    }

    public function create()
    {

        $tables = $this->report_model->get_database_tables();

        return View::make('custom_reports.create', ['tables' => $tables]);
    }

    public function edit($id)
    {

    }

    public function destroy($id)
    {
        $report = CustomReports::findOrFail($id);
        $report->delete();
        Session::flash('message', __('message_success'));

        return Redirect::route('custom_reports_index');
    }   

    public function get_table_columns(Request $request)
    {
        $all_columns = [];

        foreach ($request->input('tables') as $key => $table) {
            $columns = $this->report_model->get_table_columns($table);

            foreach ($columns as $key => $column) $all_columns[] = $table.".".$column->getName();
        }

        return response()->json($all_columns, 200);
    }

    public function generate_table_columns(Request $request)
    {
        $responses = $this->get_table_columns($request)->original;
        $html = View::make('custom_reports.partials.add_columns', ['responses' => $responses])->render();

        return response()->json(array($html), 200);
    }

    public function generate_table_relationships(Request $request)
    {
        $relations = ["INNER JOIN", "LEFT JOIN", "RIGHT JOIN"];
        $operators = ["=", ">", "<", "<>"];
        $tables = $request->tables;
        $columns = $request->columns;
        $html = View::make('custom_reports.partials.add_relationships', 
            [
                'relations' => $relations,
                'operators' => $operators,
                'tables' => $tables,
                'columns' => $columns
            ])->render();

        return response()->json(array($html), 200);
    }

    public function get_sql_statement(Request $request)
    {
        $report = $this->report_model->get_report($request->input('name'));
        $sql = SqlFormatter::format($report->sql_statement);
        $statement_results = $report->statement_results;

        return response()->json(array($sql, $statement_results),  200);
    }

    public function update_sql_statement(Request $request)
    {
        $report = $this->report_model->get_report($request->input('name'));
        $sql_statement = $request->input('sql_statement');

        if($this->report_model->allow_query($sql_statement))
        {
            $report->sql_statement = $sql_statement;
            $report->save();

            return response()->json("The action was successful!",  200);
        }

        return response()->json("The action was NOT successful!",  200);
    }

    // This method is not completed! Just show some results for testing purposes. It should create an Excel or CSV file.
    public function export(Request $request)
    {
        $results =$this->report_model->execute_query($request->sql_statement);

        if($results !== false)
        {
            (sizeof($results) > 10)? $this->report_model->update_sql_results(array_slice($results, 0,10), $request) : $this->report_model->update_sql_results($results, $request);
        } 

        return response()->json(array($results),  200);
    }
    
}
