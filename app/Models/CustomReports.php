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
        return ["=", ">=", "<=", "<>", ">", "<", "IS NOT", "IN", "NULL", "EMPTY", "BETWEEN", "AND", "OR", "LIKE", "IS NULL", "IS NOT NULL"];
    }

    public static function get_relationships()
    {
        return ["LEFT JOIN", "INNER JOIN", "RIGHT JOIN", "LEFT OUTER", "RIGHT OUTER", "FULL JOIN", "UNION", "SELF JOIN"];
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
    public function construct_sql($arguments)
    {
        $sql = 'SELECT ';

        $actions = $arguments->actions ?? [];
        $select_columns = $arguments->select_columns ?? [];
        $from_table = $arguments->from_table ?? [];
        $relationship_tables = $arguments->relationship_tables ?? [];
        $relationships = $arguments->relationships ?? [];
        $relationship_operators = $arguments->relationship_operators ?? [];
        $relationship_columns = $arguments->relationship_columns ?? [];
        $where_columns = $arguments->where_columns ?? [];
        $where_operators = $arguments->where_operators ?? [];
        $where_input = $arguments->where_input ?? [];

        if($select_columns) {
            for ($i=0; $i < sizeof($select_columns); $i++) { 
                if($actions[$i] === 'SELECT') {
                    $sql .= $select_columns[$i].",";
                }
                else {
                    $sql .= $actions[$i].'('.$select_columns[$i]."),";
                }
            }

            $sql = substr($sql, 0, -1); //Remove last comma.
        }
        else {
            $sql .= " * ";
        }

        $sql .= " FROM " . $from_table . ' ';

        $counter = 0;
        for ($i=0; $i < sizeof($relationship_tables); $i++) { 
            $sql .= $relationships[$i] . ' ' . $relationship_tables[$i] . ' ON ';
            $sql .= $relationship_columns[$counter] .' '. $relationship_operators[$i] .' '. $relationship_columns[$counter+1] . ' ';
            $counter = $counter + 2;
        }

        if($where_columns) {
            $sql .= 'WHERE ';

            for ($i=0; $i <sizeof($where_columns) ; $i++) {
                $operator = $where_operators[$i];
                $value = $where_input[$i];

                if($operator === 'IN') {
                    $value = explode(',', $value);

                    $sql .= $where_columns[$i] . ' ' . $operator . ' (';

                    $temp = '';
                    for ($j=0; $j < sizeof($value); $j++) { 
                        $temp .= '"'.$value[$j] . '",';
                    }

                    $sql = substr($sql, 0, -1); //Remove last comma.
                    $sql .= $temp . ')'. ' AND ';
                }
                else {
                    $sql .= $where_columns[$i] . ' ' . $operator . ' "' . $value . '" AND '; //needs change.
                }
                
            }

            $sql = substr($sql, 0, -5); //Remove last operator.
        }

        return $sql.";";
    }

    // Select raw! That could be a problem!
    public function execute_query($sql)
    {
        return (self::allow_query($sql))? DB::select(DB::raw($sql)) : false;
    }
    
}
