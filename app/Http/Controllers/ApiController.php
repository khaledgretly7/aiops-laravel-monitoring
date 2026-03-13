<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{

    public function normal()
    {
        return response()->json([
            "message" => "normal endpoint working"
        ]);
    }

    public function slow(Request $request)
    {
        if ($request->has('hard')) {
            sleep(rand(5,7));
        } else {
            sleep(2);
        }

        return response()->json([
            "message" => "slow endpoint"
        ]);
    }

    public function error()
    {
        throw new \Exception("Simulated system error");
    }

    public function random()
    {
        if (rand(0,1)) {
            return $this->normal();
        }

        return $this->error();
    }

    public function db(Request $request)
    {
        if ($request->has('fail')) {
            DB::select("SELECT * FROM wrong_table");
        }

        $result = DB::select("SELECT 1 as ok");

        return response()->json([
            "db_result" => $result
        ]);
    }

    public function validateData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "age" => "required|integer|between:18,60"
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return response()->json([
            "message" => "valid input"
        ]);
    }
}