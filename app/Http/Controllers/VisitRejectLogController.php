<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VisitRejectLog;
use App\Models\Visit;
use App\Models\Student;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class VisitRejectLogController extends Controller
{
    public function getAll(Request $request)
    {
        $items = VisitRejectLog::select(
            "visit_reject_log.log_id as log_id",
            "visit_reject_log.comment as comment",
            "visit_reject_log.user_id as user_id",
            "visit_reject_log.visit_id as visit_id",
            "visit_reject_log.reject_status_id as reject_status_id",
            "visit_reject_log.active as active",
            "visit_reject_log.created_at as created_at",
            "visit_reject_log.updated_at as updated_at",
            "visit_reject_log.created_by as created_by",
            "visit_reject_log.updated_by as updated_by",
        )->where("visit_reject_log.deleted_at", null);

        if ($request->log_id) {
            $items->where("visit_reject_log.log_id", $request->log_id);
        }

        if ($request->visit_id) {
            $items->where("visit_reject_log.visit_id", $request->visit_id);
        }

        if ($request->active) {
            $items->where("visit_reject_log.active", $request->active);
        }

        if ($request->orderBy) {
            $items = $items->orderBy($request->orderBy, $request->order);
        } else {
            $items = $items->orderBy("log_id", "asc");
        }

        $count = $items->count();
        $perPage = $request->perPage ? $request->perPage : $count;
        if ($perPage == 0 && $count == 0) {
            $perPage = 1;
        }
        $currentPage = $request->currentPage ? $request->currentPage : 1;

        $totalPage = ceil($count / $perPage) == 0 ? 1 : ceil($count / $perPage);
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
        $item = VisitRejectLog::select(
            "visit_reject_log.log_id as log_id",
            "visit_reject_log.comment as comment",
            "visit_reject_log.user_id as user_id",
            "visit_reject_log.visit_id as visit_id",
            "visit_reject_log.reject_status_id as reject_status_id",
            "visit_reject_log.active as active",
            "visit_reject_log.created_at as created_at",
            "visit_reject_log.updated_at as updated_at",
            "visit_reject_log.created_by as created_by",
            "visit_reject_log.updated_by as updated_by",
        )
        ->where("visit_reject_log.log_id", $id)
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
        $request->validate(["visit_id as required"]);

        $item = new VisitRejectLog();
        $item->comment = $request->comment;
        $item->user_id = $request->user_id;
        $item->visit_id = $request->visit_id;
        $item->reject_status_id = $request->reject_status_id;
        $item->active = $request->active;
        $item->created_by = "arnonr";
        $item->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate(["id as required"]);

        $item = VisitRejectLog::where("log_id", $id)->first();

        $item->comment = $request->has("comment")
            ? $request->comment
            : $item->comment;
        $item->user_id = $request->has("user_id")
            ? $request->user_id
            : $item->user_id;
        $item->visit_id = $request->has("visit_id")
            ? $request->visit_id
            : $item->visit_id;
        $item->reject_status_id = $request->has("reject_status_id")
            ? $request->reject_status_id
            : $item->reject_status_id;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->updated_by = "arnonr";
        $item->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $item = VisitRejectLog::where("log_id", $id)->first();
        $item->active = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }
}
