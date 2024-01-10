<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Config;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    public function get($id)
    {
        $item = Config::select(
            "setting_id as setting_id",
            "email as email",
            "password as password",
            "teacher_year_default as teacher_year_default",
            "supervisor_year_default as supervisor_year_default",
            "staff_year_default as staff_year_default",
            "active as active"
        )
            ->where("setting_id", $id)
            ->first();

        return response()->json(
            [
                "message" => "success",
                "data" => $item,
            ],
            200
        );
    }

    public function edit($id, Request $request)
    {
        $request->validate(["id as required"]);

        $data = Config::where("setting_id", $id)->update($request->all());

        $responseData = [
            "message" => "success",
            "data" => $data,
        ];

        return response()->json($responseData, 200);
    }
}
