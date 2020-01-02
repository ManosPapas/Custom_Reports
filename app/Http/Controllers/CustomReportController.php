<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\CustomReports;
use DB;
use App\Http\Controllers\SqlFormatter;

class CustomReportController extends Controller
{

    public function index()
    {
        return View::make('custom_reports.index', ['reports' => CustomReports::all()]);
    }

    public function create()
    {
        $tables = $this->get_database_tables();

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

    private function get_database_tables()
    {
        return DB::connection()->getDoctrineSchemaManager()->listTableNames();
    }

    private function identify_tables($tables_and_columns)
    {
        return array_unique(array_map(function($item){return explode(".", $item)[0];}, $tables_and_columns));
    }

    public function get_table_columns(Request $request)
    {
        $all_columns = [];

        foreach ($request->input('tables') as $key => $table) {
            $columns = DB::connection()->getDoctrineSchemaManager()->listTableColumns($table);

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
        $result = CustomReports::select('sql_statement', 'statement_results')->where('name', $request->input('name'))->get();
        $sql = SqlFormatter::format($result[0]['sql_statement']);
        $statement_results = ($result[0]['statement_results']);

        return response()->json(array($sql, $statement_results),  200);
    }

    public function update_sql_statement(Request $request)
    {
        $report = CustomReports::where('name', '=', $request->input('name'))->firstOrFail();
        $sql_statement = $request->input('sql_statement') ?? 'SELECT * FROM BBS WHERE 1=1';

        if($this->allow_query($sql_statement))
        {
            $report->sql_statement = $sql_statement;
            $report->save();

            return response()->json("The action was successful!",  200);
        }

        return response()->json("The action was NOT successful!",  200);
    }

    private function update_sql_results($results, $request)
    {
        $report = CustomReports::where('name', '=', $request->input('name'))->firstOrFail();

        if(empty($results)) 
        {
            $statement_results = '[]';
        }
        else 
        {
            $statement_results = '[';

            foreach ($results as $index => $object) 
            {
                $statement_results .= '{';

                foreach ($object as $column => $value) 
                {
                    $statement_results .=  '"'.$column.'"'.":".'"'.$value.'"'.',';
                }

                $statement_results = substr($statement_results, 0, -1);
                $statement_results .= '},';
               
                if($index>=10) break;
            }

            $statement_results = substr($statement_results, 0, -1);
            $statement_results .= ']';
        }

        $report->statement_results = $statement_results;
        $report->save();

        return;
    }

    private function allow_query($query)
    {
        $disAllows = array(
            'INSERT','UPDATE','DELETE','RENAME','DROP',
            'CREATE','TRUNCATE','ALTER','COMMIT','ROLLBACK',
            'MERGE','CALL','EXPLAIN','LOCK','GRANT',
            'REVOKE','SAVEPOINT','TRANSACTION','SET'
        );

        foreach ($disAllows as $disAllow) 
        {            
            if (stristr($query, $disAllow) !== false)
            {
                return false;
            }
        }

        return true;     
    }

    // Wrong
    private function construct_sql($tables_and_columns, $tables)
    {
        $sql = '';

        foreach ($tables as $key => $table) 
        { 
            $columns = array_filter(array_map(
                function($item) use ($table)
                {
                    $columns_ = explode(".", $item);

                    if($columns_[0] === $table) return $columns_[1];
                }, 
                $tables_and_columns));
            
            if($columns > 1) $columns = implode(',', $columns);

            $sql .= "SELECT ".$columns." FROM ".$table." UNION ";
        }

        return substr($sql, 0, -7);
    }

    // Change! to save? befpre prepare_sql
        // Not in use?
        // public function execute(Request $request)
        // {
        //     $tables_and_columns = $request->input('tables_and_columns');
        //     $tables = $columns = [];

        //     try
        //     {
        //         $results = $this->execute_query(
        //             $this->construct_sql($tables_and_columns, $this->identify_tables($tables_and_columns))
        //         );
        //     }
        //     catch(\Exception $e)
        //     {
        //         return response()->json($e, 500);
        //     }

        //     return response()->json($results, 200);
        // }

        // Sanitize has not been done yet!

    private function execute_query($sql)
    {
        return ($this->allow_query($sql))? DB::select(DB::raw($sql)) : false;
    }

    // This method is not completed! Just show some results for testing purposes. It should create an Excel or CSV file.
    public function export(Request $request)
    {
        $results = $this->execute_query($request->sql_statement);

        if($results !== false)
        {
            (sizeof($results) > 10)? $this->update_sql_results(array_slice($results, 0,10), $request) : $this->update_sql_results($results, $request);
        } 

        return response()->json(array($results),  200);
    }
}
