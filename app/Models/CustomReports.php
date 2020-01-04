<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class CustomReports extends Model
{
    public function get_database_tables()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

        $tables = \array_diff($tables, self::exlcude_tables());

        return $tables;
    }

    private static function exlcude_tables()
    {
    	return ["migrations", "permissions"];
    }

    public function get_report($name)
    {
    	return self::where('name', '=', $name)->firstOrFail();
    }

    public static function get_actions()
    {
        return ["SELECT", "COUNT", "MAX", "MIN", "SUM", "DISTINCT", "AVG"];
    }

    public static function get_operators()
    {
        return [">=", "<=", "=", "<>", "IS NOT", "IN", "NULL", "EMPTY", "BETWEEN", "AND", "OR", "LIKE", "IS NULL"];
    }

    public static function get_relationships()
    {
        return ["FULL JOIN", "UNION", "SELF JOIN", "INNER JOIN", "LEFT JOIN", "RIGHT JOIN", "LEFT OUTER", "RIGHT OUTER"];
    }

    public function get_table_columns($table) 
    {
    	return DB::connection()->getDoctrineSchemaManager()->listTableColumns($table);
    }

    public function identify_tables($tables_and_columns)
    {
        return array_unique(array_map(function($item){return explode(".", $item)[0];}, $tables_and_columns));
    }

    public function update_sql_results($results, $request)
    {
        $report = self::where('name', '=', $request->input('name'))->firstOrFail();

        if(empty($results)) {
            $statement_results = '[]';
        }
        else {
            $statement_results = '[';

            foreach ($results as $index => $object) {
                $statement_results .= '{';

                foreach ($object as $column => $value) {
                    $statement_results .=  '"'.$column.'"'.":".'"'.$value.'"'.',';
                }

                $statement_results = substr($statement_results, 0, -1);
                $statement_results .= '},';
               
                if($index>=10) {
                    break;
                } 
            }

            $statement_results = substr($statement_results, 0, -1);
            $statement_results .= ']';
        }

        $report->statement_results = $statement_results;
        $report->save();

        return;
    }

    public function allow_query($query)
    {
        $disAllows = array(
            'INSERT','UPDATE','DELETE','RENAME','DROP',
            'CREATE','TRUNCATE','ALTER','COMMIT','ROLLBACK',
            'MERGE','CALL','EXPLAIN','LOCK','GRANT',
            'REVOKE','SAVEPOINT','TRANSACTION','SET'
        );

        foreach ($disAllows as $disAllow) {            
            if (stristr($query, $disAllow) !== false) {
                return false;
            }
        }

        return true;     
    }

    // Change that!
    public function construct_sql($tables_and_columns, $tables)
    {
        $sql = '';

        foreach ($tables as $key => $table) { 
            $columns = array_filter(array_map(
                function($item) use ($table)
                {
                    $columns_ = explode(".", $item);

                    if($columns_[0] === $table) {
                        return $columns_[1];
                    }                         
                }, 
                $tables_and_columns));
            
            if($columns > 1) {
                $columns = implode(',', $columns);
            }

            $sql .= "SELECT ".$columns." FROM ".$table." UNION ";
        }

        return substr($sql, 0, -7);
    }

    // Select raw! That could be a problem!
    public function execute_query($sql)
    {
        return (self::allow_query($sql))? DB::select(DB::raw($sql)) : false;
    }
    
}
