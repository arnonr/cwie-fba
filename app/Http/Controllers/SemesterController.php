<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Semester;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
const whitelist = ["127.0.0.1", "::1", "localhost:8117"];

class SemesterController extends Controller
{
    public function getAll(Request $request)
    {
        $items = Semester::select(
            "semester.id as id",
            "semester.semester_year as semester_year",
            "semester.term as term",
            "semester.round_no as round_no",
            "semester.chairman_id as chairman_id",
            "semester.default_request_doc_no as default_request_doc_no",
            "semester.default_request_doc_date as default_request_doc_date",
            "semester.start_date as start_date",
            "semester.end_date as end_date",
            "semester.regis_start_date",
            "semester.regis_end_date",
            "semester.active as active",
            "semester.is_current as is_current"
        )->where("semester.deleted_at", null);

        // Include
        if ($request->includeAll) {
            $items->addSelect(
                DB::raw(
                    'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS chairman_name'
                )
            );
            $items->leftJoin(
                "teacher",
                "teacher.id",
                "=",
                "semester.chairman_id"
            );
        }

        if ($request->includeChairman) {
            $items->addSelect(
                DB::raw(
                    'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS chairman_name'
                )
            );
            $items->leftJoin(
                "teacher",
                "teacher.id",
                "=",
                "semester.chairman_id"
            );
        }

        if ($request->id_array) {
            $items->whereIn("semester.id", $request->id_array);
        }

        // Where
        if ($request->id) {
            $items->where("semester.id", $request->id);
        }

        if ($request->semester_year) {
            $items->where(
                "semester.semester_year",
                "LIKE",
                "%" . $request->semester_year . "%"
            );
        }

        if ($request->semester_year_and_term) {
            $items->where(
                DB::raw("concat(term,'/',semester_year)"),
                "LIKE",
                "%" . $request->semester_year_and_term . "%"
            );
        }

        if ($request->term) {
            $items->where("semester.term", $request->term);
        }

        if ($request->round_no) {
            $items->where("semester.round_no", $request->round_no);
        }

        if ($request->chairman_id) {
            $items->where("semester.chairman_id", $request->chairman_id);
        }

        if ($request->active) {
            $items->where("semester.active", $request->active);
        }

        if ($request->is_current) {
            $items->where("semester.is_current", $request->is_current);
        }

        // Order
        if ($request->orderBy) {
            $items = $items->orderBy($request->orderBy, $request->order);
        } else {
            $items = $items->orderBy("id", "asc");
        }

        $count = $items->count();
        $perPage = $request->perPage ? $request->perPage : $count;
        $currentPage = $request->currentPage ? $request->currentPage : 1;
        $totalPage = 0; // new_code
        if ($count != 0) {
            // new_code
            $totalPage =
                ceil($count / $perPage) == 0 ? 1 : ceil($count / $perPage);
        } // new_code

        $offset = $perPage * ($currentPage - 1);
        $items = $items->skip($offset)->take($perPage);
        $items = $items->get();

        return response()->json(
            [
                "message" => "success",
                "data" => $items,
                "totalPage" => $totalPage,
                "totalData" => $count,
            ],
            200
        );
    }

    public function get($id)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $item = Semester::select(
            "semester.id as id",
            "semester.semester_year as semester_year",
            "semester.term as term",
            "semester.round_no as round_no",
            "semester.chairman_id as chairman_id",
            "semester.default_request_doc_no as default_request_doc_no",
            "semester.default_request_doc_date as default_request_doc_date",
            "semester.start_date as start_date",
            "semester.end_date as end_date",
            "semester.regis_start_date",
            "semester.regis_end_date",
            "semester.active as active",
            "semester.is_current as is_current",
            DB::raw(
                'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS chairman_name'
            )
        )
            ->where("semester.id", $id)
            ->leftJoin("teacher", "teacher.id", "=", "semester.chairman_id")
            ->first();

        return response()->json(
            [
                "message" => "success",
                "data" => $item,
            ],
            200
        );
    }

    public function add(Request $request)
    {
        $request->validate([
            "semester_year as required",
            "term as required",
            "round_no as required",
        ]);

        $item = new Semester();
        $item->semester_year = $request->semester_year;
        $item->term = $request->term;
        $item->round_no = $request->round_no;
        $item->chairman_id = $request->chairman_id;
        $item->default_request_doc_no = $request->default_request_doc_no;
        $item->default_request_doc_date = $request->default_request_doc_date;
        $item->start_date = $request->start_date;
        $item->end_date = $request->end_date;
        $item->regis_start_date = $request->regis_start_date;
        $item->regis_end_date = $request->regis_end_date;
        $item->active = $request->active;
        $item->is_current = $request->is_current;
        $item->created_by = "arnonr";
        $item->save();

        if ($item->is_current == 1) {
            Semester::where("id", "<>", $request->id)->update([
                "is_current" => 0,
            ]);
        }

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate(["id as required"]);

        $item = Semester::where("id", $request->id)->first();

        $item->semester_year = $request->has("semester_year")
            ? $request->semester_year
            : $item->semester_year;
        $item->term = $request->has("term") ? $request->term : $item->term;
        $item->round_no = $request->has("round_no")
            ? $request->round_no
            : $item->round_no;
        $item->chairman_id = $request->has("chairman_id")
            ? $request->chairman_id
            : $item->chairman_id;
        $item->default_request_doc_no = $request->has("default_request_doc_no")
            ? $request->default_request_doc_no
            : $item->default_request_doc_no;
        $item->default_request_doc_date = $request->has(
            "default_request_doc_date"
        )
            ? $request->default_request_doc_date
            : $item->default_request_doc_date;
        $item->start_date = $request->has("start_date")
            ? $request->start_date
            : $item->start_date;
        $item->end_date = $request->has("end_date")
            ? $request->end_date
            : $item->end_date;
        $item->regis_start_date = $request->has("regis_start_date")
            ? $request->regis_start_date
            : $item->regis_start_date;
        $item->regis_end_date = $request->has("regis_end_date")
            ? $request->regis_end_date
            : $item->regis_end_date;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->is_current = $request->has("is_current")
            ? $request->is_current
            : $item->is_current;
        $item->updated_by = "arnonr";
        $item->save();

        if ($item->is_current == 1) {
            Semester::where("id", "<>", $request->id)->update([
                "is_current" => 0,
            ]);
        }

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $item = Semester::where("id", $id)->first();

        $item->active = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }
}
