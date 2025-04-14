<?php

// app/Http/Controllers/TestController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class TestController extends Controller
{
    public function listTables(): JsonResponse
    {
        $tables = DB::select('SHOW TABLES');
        return response()->json($tables);
    }

    public function listPrimaryKeys(): JsonResponse
    {
        $tables = DB::select('SHOW TABLES');
        $result = [];

        foreach ($tables as $tableObj) {
            $tableName = array_values((array)$tableObj)[0];
            $pk = DB::select("SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'");
            $result[$tableName] = $pk;
        }

        return response()->json($result);
    }
}
