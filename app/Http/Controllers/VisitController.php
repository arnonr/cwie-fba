<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Visit;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

const whitelist = ["127.0.0.1", "::1", "localhost:8117"];

class VisitController extends Controller
{
    protected $uploadUrl = "http://co-op.fba.kmutnb.ac.th/storage/";

    public function getAll(Request $request)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $items = Visit::select(
            "visit.visit_id as visit_id",
            "visit.supervision_id as supervision_id",
            "visit.form_id as form_id",
            "visit.visit_date as visit_date",
            "visit.visit_time as visit_time",
            "visit.co_name as co_name",
            "visit.co_position as co_position",
            "visit.co_phone as co_phone",
            "visit.document_number as document_number",
            "visit.document_date as document_date",
            "visit.visit_type as visit_type",
            "visit.googlemap_url as googlemap_url",
            // 'visit.googlemap_file as googlemap_file',
            DB::raw(
                "(CASE WHEN visit.googlemap_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',visit.googlemap_file) END) AS googlemap_file"
            ),
            "visit.approve_document_at as approve_document_at",
            "visit.address as address",
            "visit.province_id as province_id",
            "visit.amphur_id as amphur_id",
            "visit.tumbol_id as tumbol_id",
            "visit.send_report_at as send_report_at",
            "visit.report_status_id as report_status_id",
            "visit.confirm_report_at as confirm_report_at",
            "visit.reject_report_comment as reject_report_comment",
            "visit.visit_detail as visit_detail",
            // 'visit.report_file as report_file',
            DB::raw(
                "(CASE WHEN visit.report_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',visit.report_file) END) AS report_file"
            ),
            DB::raw(
                "(CASE WHEN visit.report2_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',visit.report2_file) END) AS report2_file"
            ),
            "visit.is_recruit as is_recruit",
            "visit.visit_expense as visit_expense",
            "visit.travel_expense as travel_expense",
            "visit.active as active",
            "visit.created_at as created_at",
            "visit.created_by as created_by",
            "visit.updated_at as updated_at",
            "visit.updated_by as updated_by",
            "visit.deleted_at as deleted_at",
            "visit.visit_status as visit_status",
            "visit.chairman_approved_at as chairman_approved_at",
            "visit.major_head_approve_at as major_head_approve_at",
            "visit.visit_reject_status_id as visit_reject_status_id",
            "visit.cancel_description as cancel_description",
            // 'visit.cancel_file as cancel_file',
            "visit.cancel_at as cancel_at",
            DB::raw(
                "(CASE WHEN visit.cancel_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',visit.cancel_file) END) AS cancel_file"
            )
        )->where("visit.deleted_at", null);

        if ($request->includeProvince) {
            $items->addSelect("province.name_th as province_name");
            $items->leftJoin(
                "province",
                "province.province_id",
                "=",
                "visit.province_id"
            );
        }

        if ($request->includeAmphur) {
            $items->addSelect("amphur.name_th as amphur_name");
            $items->leftJoin(
                "amphur",
                "amphur.amphur_id",
                "=",
                "visit.amphur_id"
            );
        }

        if ($request->includeTumbol) {
            $items->addSelect("tumbol.name_th as tumbol_name");
            $items->leftJoin(
                "tumbol",
                "tumbol.tumbol_id",
                "=",
                "visit.tumbol_id"
            );
        }

        if ($request->visit_id) {
            $items->where("visit.visit_id", $request->visit_id);
        }

        if ($request->supervision_id) {
            $items->where("visit.supervision_id", $request->supervision_id);
        }

        if ($request->form_id) {
            $items->where("visit.form_id", $request->form_id);
        }

        if ($request->visit_date) {
            $items->where("visit.visit_date", $request->visit_date);
        }

        if ($request->visit_type) {
            $items->where("visit.visit_type", $request->visit_type);
        }

        if ($request->province_id) {
            $items->where("visit.province_id", $request->province_id);
        }

        if ($request->amphur_id) {
            $items->where("visit.amphur_id", $request->amphur_id);
        }

        if ($request->tumbol_id) {
            $items->where("visit.tumbol_id", $request->tumbol_id);
        }

        if ($request->report_status_id) {
            $items->where("visit.report_status_id", $request->report_status_id);
        }

        if ($request->visit_reject_status_id) {
            $items->where(
                "visit.visit_reject_status_id",
                $request->visit_reject_status_id
            );
        }

        if ($request->active) {
            $items->where("visit.active", $request->active);
        }

        if ($request->orderBy) {
            $items = $items->orderBy($request->orderBy, $request->order);
        } else {
            $items = $items->orderBy("visit_id", "asc");
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
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $item = Visit::select(
            "visit.visit_id as visit_id",
            "visit.supervision_id as supervision_id",
            "visit.form_id as form_id",
            "visit.visit_date as visit_date",
            "visit.visit_time as visit_time",
            "visit.co_name as co_name",
            "visit.co_position as co_position",
            "visit.co_phone as co_phone",
            "visit.document_number as document_number",
            "visit.document_date as document_date",
            "visit.visit_type as visit_type",
            "visit.googlemap_url as googlemap_url",
            // 'visit.googlemap_file as googlemap_file',
            DB::raw(
                "(CASE WHEN visit.googlemap_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',visit.googlemap_file) END) AS googlemap_file"
            ),
            "visit.approve_document_at as approve_document_at",
            "visit.address as address",
            "visit.province_id as province_id",
            "visit.amphur_id as amphur_id",
            "visit.tumbol_id as tumbol_id",
            "visit.send_report_at as send_report_at",
            "visit.report_status_id as report_status_id",
            "visit.confirm_report_at as confirm_report_at",
            "visit.reject_report_comment as reject_report_comment",
            "visit.visit_detail as visit_detail",
            // 'visit.report_file as report_file',
            DB::raw(
                "(CASE WHEN visit.report_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',visit.report_file) END) AS report_file"
            ),
            "visit.visit_expense as visit_expense",
            "visit.travel_expense as travel_expense",
            "visit.active as active",
            "visit.created_at as created_at",
            "visit.created_by as created_by",
            "visit.updated_at as updated_at",
            "visit.updated_by as updated_by",
            "visit.deleted_at as deleted_at",
            "visit.visit_status as visit_status",
            "visit.chairman_approved_at as chairman_approved_at",
            "visit.major_head_approve_at as major_head_approve_at",
            "visit.visit_reject_status_id as visit_reject_status_id",
            "visit.cancel_description as cancel_description",
            // 'visit.cancel_file as cancel_file',
            "visit.cancel_at as cancel_at",
            DB::raw(
                "(CASE WHEN visit.cancel_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',visit.cancel_file) END) AS cancel_file"
            ),
            DB::raw(
                "concat(teacher.prefix,'',teacher.firstname, ' ', teacher.surname) as supervision_name"
            ),
            "province.name_th as province_name",
            "amphur.name_th as amphur_name",
            "tumbol.name_th as tumbol_name"
        )
            ->where("visit.visit_id", $id)
            ->leftJoin("teacher", "teacher.id", "=", "visit.supervision_id")
            ->leftJoin("form", "form.id", "=", "visit.form_id")
            ->leftJoin(
                "province",
                "province.province_id",
                "=",
                "visit.province_id"
            )
            ->leftJoin("amphur", "amphur.amphur_id", "=", "visit.amphur_id")
            ->leftJoin("tumbol", "tumbol.tumbol_id", "=", "visit.tumbol_id")
            // ->with("visit_reject_log")
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

        $pathMap = null;
        if (
            $request->googlemap_file != "" &&
            $request->googlemap_file != "null" &&
            $request->googlemap_file != "undefined"
        ) {
            $fileMap =
                date("YmdHis") .
                "_visit_map_" .
                rand(1, 10000) .
                "." .
                $request->file("googlemap_file")->extension();
            $pathMap = "visit/" . $fileMap;
            Storage::disk("public")->put(
                $pathMap,
                file_get_contents($request->googlemap_file)
            );
        }

        $pathReport = null;
        if (
            $request->report_file != "" &&
            $request->report_file != "null" &&
            $request->report_file != "undefined"
        ) {
            $fileReport =
                date("YmdHis") .
                "_report_" .
                rand(1, 10000) .
                "." .
                $request->file("report_file")->extension();
            $pathReport = "visit/" . $fileReport;
            Storage::disk("public")->put(
                $pathReport,
                file_get_contents($request->report_file)
            );
        }

        $pathCancel = null;
        if (
            $request->cancel_file != "" &&
            $request->cancel_file != "null" &&
            $request->cancel_file != "undefined"
        ) {
            $fileCancel =
                date("YmdHis") .
                "_cancel_file_" .
                rand(1, 10000) .
                "." .
                $request->file("cancel_file")->extension();
            $pathCancel = "visit/" . $fileCancel;
            Storage::disk("public")->put(
                $pathCancel,
                file_get_contents($request->cancel_file)
            );
        }

        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($value == "null") {
                $request[$key] = null;
            }
        }

        $item = new Visit();
        $item->supervision_id = $request->supervision_id;
        $item->form_id = $request->form_id;
        $item->visit_date = $request->visit_date;
        $item->visit_time = $request->visit_time;
        $item->co_name = $request->co_name;
        $item->co_position = $request->co_position;
        $item->co_phone = $request->co_phone;
        $item->document_number = $request->document_number;
        $item->document_date = $request->document_date;
        $item->visit_type = $request->visit_type;
        $item->googlemap_url = $request->googlemap_url;
        $item->googlemap_file = $pathMap;
        $item->approve_document_at = $request->approve_document_at;
        $item->address = $request->address;
        $item->province_id = $request->province_id;
        $item->amphur_id = $request->amphur_id;
        $item->tumbol_id = $request->tumbol_id;
        $item->send_report_at = $request->send_report_at;
        $item->report_status_id = $request->report_status_id;
        $item->confirm_report_at = $request->confirm_report_at;
        $item->reject_report_comment = $request->reject_report_comment;
        $item->visit_detail = $request->visit_detail;
        $item->report_file = $pathReport;
        $item->visit_expense = $request->visit_expense;
        $item->travel_expense = $request->travel_expense;
        $item->active = $request->active;
        $item->visit_status = $request->visit_status;
        $item->chairman_approved_at = $request->chairman_approved_at;
        $item->major_head_approve_at = $request->major_head_approve_at;
        $item->visit_reject_status_id = $request->visit_reject_status_id;
        $item->cancel_description = $request->cancel_description;
        $item->cancel_file = $pathCancel;
        $item->cancel_at = $request->cancel_at;

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
        $request->validate(["visit_id as required"]);

        $item = Visit::where("visit_id", $id)->first();

        $pathMap = null;
        if (
            $request->googlemap_file != "" &&
            $request->googlemap_file != "null" &&
            $request->googlemap_file != "undefined"
        ) {
            $fileMap =
                date("YmdHis") .
                "_visit_map_" .
                rand(1, 10000) .
                "." .
                $request->file("googlemap_file")->extension();
            $pathMap = "visit/" . $fileMap;
            Storage::disk("public")->put(
                $pathMap,
                file_get_contents($request->googlemap_file)
            );
        } else {
            $pathMap = $item->googlemap_file;
        }

        $pathReport = null;
        if (
            $request->report_file != "" &&
            $request->report_file != "null" &&
            $request->report_file != "undefined"
        ) {
            $fileReport =
                date("YmdHis") .
                "_report_" .
                rand(1, 10000) .
                "." .
                $request->file("report_file")->extension();
            $pathReport = "visit/" . $fileReport;
            Storage::disk("public")->put(
                $pathReport,
                file_get_contents($request->report_file)
            );
        } else {
            $pathReport = $item->report_file;
        }

        $pathReport2 = null;
        if (
            $request->report2_file != "" &&
            $request->report2_file != "null" &&
            $request->report2_file != "undefined"
        ) {
            $fileReport2 =
                date("YmdHis") .
                "_report_" .
                rand(1, 10000) .
                "." .
                $request->file("report2_file")->extension();
            $pathReport2 = "visit/" . $fileReport2;
            Storage::disk("public")->put(
                $pathReport2,
                file_get_contents($request->report2_file)
            );
        } else {
            $pathReport2 = $item->report_file;
        }

        $pathCancel = null;
        if (
            $request->cancel_file != "" &&
            $request->cancel_file != "null" &&
            $request->cancel_file != "undefined"
        ) {
            $fileCancel =
                date("YmdHis") .
                "_cancel_file_" .
                rand(1, 10000) .
                "." .
                $request->file("cancel_file")->extension();
            $pathCancel = "visit/" . $fileCancel;
            Storage::disk("public")->put(
                $pathCancel,
                file_get_contents($request->cancel_file)
            );
        } else {
            $pathMap = $item->cancel_file;
        }

        // $item->news_title = $request->has("news_title")
        //     ? $request->news_title
        //     : $item->news_title;
        // $item->news_detail = $request->has("news_detail")
        //     ? $request->news_detail
        //     : $item->news_detail;
        // $item->pinned = $request->has("pinned")
        //     ? $request->pinned
        //     : $item->pinned;
        // $item->news_cate_id = $request->has("news_cate_id")
        //     ? $request->news_cate_id
        //     : $item->news_cate_id;
        // $item->active = $request->has("active")
        //     ? $request->active
        //     : $item->active;

        $item->supervision_id = $request->has("supervision_id")
            ? $request->supervision_id
            : $item->supervision_id;
        // $item->form_id = $request->has("form_id")
        //     ? $request->form_id
        //     : $item->form_id;
        $item->visit_date = $request->has("visit_date")
            ? $request->visit_date
            : $item->visit_date;
        $item->visit_time = $request->has("visit_time")
            ? $request->visit_time
            : $item->visit_time;
        $item->co_name = $request->has("co_name")
            ? $request->co_name
            : $item->co_name;
        $item->co_position = $request->has("co_position")
            ? $request->co_position
            : $item->co_position;
        $item->co_phone = $request->has("co_phone")
            ? $request->co_phone
            : $item->co_phone;
        $item->document_number = $request->has("document_number")
            ? $request->document_number
            : $item->document_number;
        $item->document_number = $request->has("document_number")
            ? $request->document_number
            : $item->document_number;
        $item->document_date = $request->has("document_date")
            ? $request->document_date
            : $item->document_date;
        $item->visit_type = $request->has("visit_type")
            ? $request->visit_type
            : $item->visit_type;
        $item->googlemap_url = $request->has("googlemap_url")
            ? $request->googlemap_url
            : $item->googlemap_url;
        $item->approve_document_at = $request->has("approve_document_at")
            ? $request->approve_document_at
            : $item->approve_document_at;
        $item->address = $request->has("address")
            ? $request->address
            : $item->address;
        $item->province_id = $request->has("province_id")
            ? $request->province_id
            : $item->province_id;
        $item->amphur_id = $request->has("amphur_id")
            ? $request->amphur_id
            : $item->amphur_id;
        $item->tumbol_id = $request->has("tumbol_id")
            ? $request->tumbol_id
            : $item->tumbol_id;
        $item->send_report_at = $request->has("send_report_at")
            ? $request->send_report_at
            : $item->send_report_at;
        $item->report_status_id = $request->has("report_status_id")
            ? $request->report_status_id
            : $item->report_status_id;
        $item->confirm_report_at = $request->has("confirm_report_at")
            ? $request->confirm_report_at
            : $item->confirm_report_at;
        $item->reject_report_comment = $request->has("reject_report_comment")
            ? $request->reject_report_comment
            : $item->reject_report_comment;
        $item->visit_detail = $request->has("visit_detail")
            ? $request->visit_detail
            : $item->visit_detail;
        $item->visit_expense = $request->has("visit_expense")
            ? $request->visit_expense
            : $item->visit_expense;
        $item->travel_expense = $request->has("travel_expense")
            ? $request->travel_expense
            : $item->travel_expense;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->visit_status = $request->has("visit_status")
            ? $request->visit_status
            : $item->visit_status;
        $item->chairman_approved_at = $request->has("chairman_approved_at")
            ? $request->chairman_approved_at
            : $item->chairman_approved_at;
        $item->major_head_approve_at = $request->has("major_head_approve_at")
            ? $request->major_head_approve_at
            : $item->major_head_approve_at;
        $item->visit_reject_status_id = $request->has("visit_reject_status_id")
            ? $request->visit_reject_status_id
            : $item->visit_reject_status_id;
        $item->cancel_description = $request->has("cancel_description")
            ? $request->cancel_description
            : $item->cancel_description;
        $item->cancel_at = $request->has("cancel_at")
            ? $request->cancel_at
            : $item->cancel_at;
        $item->is_recruit = $request->has("is_recruit")
            ? $request->is_recruit
            : $item->is_recruit;

        $item->googlemap_file = $pathMap;
        $item->report_file = $pathReport;
        $item->report2_file = $pathReport2;
        $item->cancel_file = $pathCancel;
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
        $item = Visit::where("visit_id", $id)->first();
        $item->active = 0;
        // $item->deleted_at = Carbon::now();
        $item->save();

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }

    public function approve($id, Request $request)
    {
        $request->validate(["visit_id as required"]);

        $item = Visit::where("visit_id", $request->visit_id)->first();

        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($value == "null") {
                $request->$key = null;
            }
        }

        $item->visit_reject_status_id = null;
        $item->visit_status = $request->has("visit_status")
            ? $request->visit_status
            : $item->visit_status;
        $item->major_head_approve_at = $request->has("major_head_approve_at")
            ? $request->major_head_approve_at
            : $item->major_head_approve_at;
        $item->chairman_approved_at = $request->has("chairman_approved_at")
            ? $request->chairman_approved_at
            : $item->chairman_approved_at;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->report_status_id = $request->has("report_status_id")
            ? $request->report_status_id
            : $item->report_status_id;
        $item->confirm_report_at = $request->has("confirm_report_at")
            ? $request->confirm_report_at
            : $item->confirm_report_at;
        $item->updated_by = "arnonr";
        $item->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function addVisitBook(Request $request)
    {
        $request->validate(["visit_id as required"]);

        Visit::whereIn("visit_id", $request->id)->update([
            "document_number" => $request->document_number,
            "document_date" => $request->document_date,
            "updated_by" => "arnonr",
        ]);

        Visit::whereIn("visit_id", $request->id)
            ->where("visit_status", "<", "4")
            ->update([
                "visit_status" => 4,
            ]);

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }

    public function sendReport($id, Request $request)
    {
        $request->validate(["visit_id as required"]);

        $item = Visit::where("visit_id", $id)->first();

        $pathReport = null;
        if (
            $request->report_file != "" &&
            $request->report_file != "null" &&
            $request->report_file != "undefined"
        ) {
            $fileReport =
                date("YmdHis") .
                "_report_" .
                rand(1, 10000) .
                "." .
                $request->file("report_file")->extension();
            $pathReport = "visit/" . $fileReport;
            Storage::disk("public")->put(
                $pathReport,
                file_get_contents($request->report_file)
            );
        } else {
            $pathReport = $item->report_file;
        }

        $pathReport2 = null;
        if (
            $request->report2_file != "" &&
            $request->report2_file != "null" &&
            $request->report2_file != "undefined"
        ) {
            $fileReport2 =
                date("YmdHis") .
                "_report_" .
                rand(1, 10000) .
                "." .
                $request->file("report2_file")->extension();
            $pathReport2 = "visit/" . $fileReport2;
            Storage::disk("public")->put(
                $pathReport2,
                file_get_contents($request->report2_file)
            );
        } else {
            $pathReport2 = $item->report_file;
        }

        $item->send_report_at = $request->has("send_report_at")
            ? $request->send_report_at
            : $item->send_report_at;
        $item->report_status_id = $request->has("report_status_id")
            ? $request->report_status_id
            : $item->report_status_id;
        $item->confirm_report_at = $request->has("confirm_report_at")
            ? $request->confirm_report_at
            : $item->confirm_report_at;
        $item->reject_report_comment = $request->has("reject_report_comment")
            ? $request->reject_report_comment
            : $item->reject_report_comment;
        $item->visit_detail = $request->has("visit_detail")
            ? $request->visit_detail
            : $item->visit_detail;
        $item->visit_expense = $request->has("visit_expense")
            ? $request->visit_expense
            : $item->visit_expense;
        $item->travel_expense = $request->has("travel_expense")
            ? $request->travel_expense
            : $item->travel_expense;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->visit_status = $request->has("visit_status")
            ? $request->visit_status
            : $item->visit_status;
        $item->chairman_approved_at = $request->has("chairman_approved_at")
            ? $request->chairman_approved_at
            : $item->chairman_approved_at;
        $item->major_head_approve_at = $request->has("major_head_approve_at")
            ? $request->major_head_approve_at
            : $item->major_head_approve_at;
        $item->visit_reject_status_id = $request->has("visit_reject_status_id")
            ? $request->visit_reject_status_id
            : $item->visit_reject_status_id;
        $item->cancel_description = $request->has("cancel_description")
            ? $request->cancel_description
            : $item->cancel_description;
        $item->cancel_at = $request->has("cancel_at")
            ? $request->cancel_at
            : $item->cancel_at;

        $item->googlemap_file = $pathMap;
        $item->report_file = $pathReport;
        $item->cancel_file = $pathCancel;
        $item->updated_by = "arnonr";
        $item->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }
}
