<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RejectLog;
use App\Models\Form;
use App\Models\Student;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class RejectLogController extends Controller
{
    public function getAll(Request $request)
    {
        $items = RejectLog::select(
            "reject_log.id as id",
            "reject_log.comment as comment",
            "reject_log.user_id as user_id",
            "reject_log.form_id as form_id",
            "reject_log.reject_status_id as reject_status_id",
            "reject_log.active as active"
        )->where("reject_log.deleted_at", null);

        if ($request->id) {
            $items->where("reject_log.id", $request->id);
        }

        if ($request->form_id) {
            $items->where("reject_log.form_id", $request->form_id);
        }

        if ($request->active) {
            $items->where("reject_log.active", $request->active);
        }

        if ($request->orderBy) {
            $items = $items->orderBy($request->orderBy, $request->order);
        } else {
            $items = $items->orderBy("id", "asc");
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
        $item = RejectLog::select(
            "reject_log.id as id",
            "reject_log.comment as comment",
            "reject_log.user_id as user_id",
            "reject_log.form_id as form_id",
            "reject_log.reject_status_id as reject_status_id",
            "reject_log.active as active"
        )
            ->where("reject_log.id", $id)
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
        $request->validate(["form_id as required"]);

        $item = new RejectLog();
        $item->comment = $request->comment;
        $item->user_id = $request->user_id;
        $item->form_id = $request->form_id;
        $item->reject_status_id = $request->reject_status_id;
        $item->active = $request->active;
        $item->created_by = "arnonr";
        $item->save();

        $form = Form::where("id", $item->form_id)->first();
        $form->reject_status_id = $item->reject_status_id;
        if (
            $item->reject_status_id == 1 ||
            $item->reject_status_id == 2 ||
            $item->reject_status_id == 3
        ) {
            $form->status_id = 1;
        }

        if ($item->reject_status_id == 4) {
            $form->status_id = 6;
        }

        if ($item->reject_status_id == 5) {
            $form->status_id = 11;
        }

        $form->save();

        $student = Student::where("id", $form->student_id)->first();
        $student->status_id = $form->status_id;
        $student->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate(["id as required"]);

        $item = RejectLog::where("id", $id)->first();

        $item->comment = $request->has("comment")
            ? $request->comment
            : $item->comment;
        $item->user_id = $request->has("user_id")
            ? $request->user_id
            : $item->user_id;
        $item->form_id = $request->has("form_id")
            ? $request->form_id
            : $item->form_id;
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
}
