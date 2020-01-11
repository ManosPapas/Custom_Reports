<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Models\CustomReports;
use App\Http\Controllers\SqlFormatter;
use Carbon\Carbon;

// What has not been done (on purpose):
// 1) Use a PHP library to export an excel file.
// 2) Use a Permission Manager library (like Spatie) to give access to the right people.
// 3) UI is not "perfect" because every system has different UI/UX.
// 4) Error Messages

// Bugs: One bug in the select_all/tables+columns. It does not populate as it should. One overhead problem when selecting all tables.
// The message "The action was NOT successful" is green.

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

    public function save(Request $request)
    {
        // Needs validation here.
        $sql_statement = $this->report_model->construct_sql($request);
        $user = $request->user();

        // Add unique in table. it did not work |unique:custom_reports
        $validatedData = $request->validate([
            'report_name' => 'required|string|min:5'
        ]);

        $report = new CustomReports;
        $report->name = $request->report_name;
        $report->sql_statement = $sql_statement;
        $report->modified_by = $user;
        $report->last_run = Carbon::now();
        //$report->save(); //Bug

        return response()->json(SqlFormatter::format($sql_statement), 200);
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

            foreach ($columns as $key => $column) {
                $all_columns[] = $table.".".$column->getName();
            } 
        }

        return response()->json($all_columns, 200);
    }

    public function generate_table_columns(Request $request)
    {
        $columns = $this->get_table_columns($request)->original;
        $checked_tables = $request->input('tables');

        $html_columns = View::make('custom_reports.partials.add_columns', [
            'columns' => $columns,
            'actions' => CustomReports::get_actions()
        ])->render();

        $html_join_tables = View::make('custom_reports.partials.add_join_tables', [
            'tables' => $checked_tables
        ])->render();

        $html_where_columns = View::make('custom_reports.partials.add_where_columns', [
            'columns' => $columns
        ])->render();

        return response()->json(array(
            "html_columns" => $html_columns,
            "html_join_tables" => $html_join_tables,
            "checked_tables" => $checked_tables,
            "action_columns" => $request->input('action_columns'),
            "checked_columns" => $request->input('checked_columns') ?? '',
            "html_where_columns" => $html_where_columns),
            200);
    }

    public function generate_where_clause(Request $request)
    {
        $columns = $this->get_table_columns($request)->original;

        $html = View::make('custom_reports.partials.add_where', [
            'columns' => $columns,
            'operators' => CustomReports::get_operators()
        ])->render();

        return response()->json(array($html), 200);
    }

    public function generate_join_columns(Request $request)
    {
        $checked_columns = $request->input('checked_columns');

        $html = View::make('custom_reports.partials.add_join_columns', [
            'columns' => $checked_columns
        ])->render();

        return response()->json(array($html), 200);
    }

    public function generate_table_relationships(Request $request)
    {
        $tables = $request->tables;
        $columns = $request->columns;
        $html = View::make('custom_reports.partials.add_relationships', 
            [
                'relations' => CustomReports::get_relationships(),
                'operators' => CustomReports::get_operators(),
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

        if($this->report_model->allow_query($sql_statement)) {
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

        if($results !== false) {
            (sizeof($results) > 10)? $this->report_model->update_sql_results(array_slice($results, 0,10), $request) : $this->report_model->update_sql_results($results, $request);
        } 

        return response()->json(array($results),  200);
    }
    
}
