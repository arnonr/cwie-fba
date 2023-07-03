<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Form;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\PHPMailer;
const whitelist = ["127.0.0.1", "::1", "localhost:8117"];

class FormController extends Controller
{
    protected $uploadUrl = "http://143.198.208.110:8117/storage/";

    public function getAll(Request $request)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $items = Form::select(
            "form.id as id",
            "form.supervision_id as supervision_id",
            "form.semester_id as semester_id",
            "form.student_id as student_id",
            "form.company_id as company_id",
            "form.status_id as status_id",
            "form.start_date as start_date",
            "form.end_date as end_date",
            "form.co_name as co_name",
            "form.co_position as co_position",
            "form.co_tel as co_tel",
            "form.co_email as co_email",
            "form.request_name as request_name",
            "form.request_position as request_position",
            "form.request_document_date as request_document_date",
            "form.request_document_number as request_document_number",
            "form.max_response_date as max_response_date",
            "form.send_document_date as send_document_date",
            "form.send_document_number as send_document_number",
            DB::raw(
                "(CASE WHEN form.response_document_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',form.response_document_file) END) AS response_document_file"
            ),
            "form.response_send_at as response_send_at",
            "form.response_province_id as response_province_id",
            "form.confirm_response_at as confirm_response_at",
            "form.workplace_address as workplace_address",
            "form.workplace_province_id as workplace_province_id",
            "form.workplace_amphur_id as workplace_amphur_id",
            "form.workplace_tumbol_id as workplace_tumbol_id",
            "form.workplace_googlemap_url as workplace_googlemap_url",
            "form.workplace_googlemap_file as workplace_googlemap_file",
            "form.plan_document_file as plan_document_file",
            "form.plan_send_at as plan_send_at",
            "form.plan_accept_at as plan_accept_at",
            "form.reject_status_id as reject_status_id",
            "form.advisor_verified_at as advisor_verified_at",
            "form.chairman_approved_at as chairman_approved_at",
            "form.faculty_confirmed_at as faculty_confirmed_at",
            "form.company_rating as company_rating",
            "form.rating_comment as rating_comment",
            "form.next_coop as next_coop",
            "form.is_pass_coop_subject as is_pass_coop_subject",
            "form.is_pass_general_subject as is_pass_general_subject",
            "form.is_pass_gpa as is_pass_gpa",
            "form.is_pass_suspend as is_pass_suspend",
            "form.is_pass_punishment as is_pass_punishment",
            "form.is_pass_disease as is_pass_disease",
            DB::raw(
                "(CASE WHEN form.namecard_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',form.namecard_file) END) AS namecard_file"
            ),
            "form.province_id as province_id",
            "form.amphur_id as amphur_id",
            "form.tumbol_id as tumbol_id",
            "form.active as active"
        )->where("form.deleted_at", null);

        // Include
        if ($request->includeAll) {
            $items->addSelect(
                DB::raw(
                    "concat(teacher.prefix,'',teacher.firstname, ' ', teacher.surname) as supervision_name"
                ),
                DB::raw(
                    "concat(semester.term,'/',semester.semester_year, ' รอบที่ ', semester.round_no) as semester_name"
                ),
                DB::raw(
                    "concat(student.firstname, ' ', student.surname) as student_fullname"
                ),
                "company.name_th as company_name",
                "form_status.name_th as form_status_name",
                "response_province.name_th as response_province_name",
                "province.name_th as province_name",
                "amphur.name_th as amphur_name",
                "tumbol.name_th as tumbol_name",
                "semester.semester_year as semester_year",
                "semester.term as term",
                "semester.round_no as round_no",
                "semester.chairman_id as chairman_id"
            );
            $items->leftJoin(
                "teacher",
                "teacher.id",
                "=",
                "form.supervision_id"
            );
            $items->leftJoin(
                "semester",
                "semester.id",
                "=",
                "form.semester_id"
            );
            $items->leftJoin("student", "student.id", "=", "form.student_id");
            $items->leftJoin("company", "company.id", "=", "form.company_id");
            $items->leftJoin(
                "form_status",
                "form_status.status_id",
                "=",
                "form.status_id"
            );
            $items->leftJoin(
                "province as response_province",
                "response_province.province_id",
                "=",
                "form.response_province_id"
            );
            $items->leftJoin(
                "province",
                "province.province_id",
                "=",
                "form.province_id"
            );
            $items->leftJoin(
                "amphur",
                "amphur.amphur_id",
                "=",
                "form.amphur_id"
            );
            $items->leftJoin(
                "tumbol",
                "tumbol.tumbol_id",
                "=",
                "form.tumbol_id"
            );
            $items->with("reject_log");
        }

        if ($request->includeSupervision) {
            $items->addSelect(
                DB::raw(
                    "concat(teacher.prefix,'',teacher.firstname, ' ', teacher.surname) as supervision_name"
                )
            );
            $items->leftJoin(
                "teacher",
                "teacher.id",
                "=",
                "form.supervision_id"
            );
        }

        if ($request->includeRejectLog) {
        }

        if ($request->includeSemester) {
            $items->addSelect(
                DB::raw(
                    "concat(semester.term,'/',semester.semester_year, ' รอบที่', semester.round_no) as semester_name"
                )
            );
            $items->leftJoin(
                "semester",
                "semester.id",
                "=",
                "form.semester_id"
            );
        }

        if ($request->includeStudent) {
            $items->addSelect(
                DB::raw(
                    "concat(student.firstname, ' ', student.surname) as student_fullname"
                )
            );
            $items->leftJoin("student", "student.id", "=", "form.student_id");
        }

        if ($request->includeCompany) {
            $items->addSelect("company.name_th as company_name");
            $items->leftJoin("company", "company.id", "=", "form.company_id");
        }

        if ($request->includeFormStatus) {
            $items->addSelect("form_status.name_th as form_status_name");
            $items->leftJoin(
                "form_status",
                "form_status.status_id",
                "=",
                "form.status_id"
            );
        }

        if ($request->includeResponseProvince) {
            $items->addSelect(
                "response_province.name_th as response_province_name"
            );
            $items->leftJoin(
                "province as response_province",
                "response_province.province_id",
                "=",
                "form.response_province_id"
            );
        }

        if ($request->includeProvince) {
            $items->addSelect("province.name_th as province_name");
            $items->leftJoin(
                "province",
                "province.province_id",
                "=",
                "form.province_id"
            );
        }

        if ($request->includeAmphur) {
            $items->addSelect("amphur.name_th as amphur_name");
            $items->leftJoin(
                "amphur",
                "amphur.amphur_id",
                "=",
                "form.amphur_id"
            );
        }

        if ($request->includeTumbol) {
            $items->addSelect("tumbol.name_th as tumbol_name");
            $items->leftJoin(
                "tumbol",
                "tumbol.tumbol_id",
                "=",
                "form.tumbol_id"
            );
        }

        // Where
        if ($request->id) {
            $items->where("form.id", $request->id);
        }

        if ($request->supervision_id) {
            $items->where("form.supervision_id", $request->supervision_id);
        }

        if ($request->semester_id) {
            $items->where("form.semester_id", $request->semester_id);
        }

        if ($request->student_id) {
            $items->where("form.student_id", $request->student_id);
        }

        if ($request->company_id) {
            $items->where("form.company_id", $request->company_id);
        }

        if ($request->status_id) {
            $items->where("form.status_id", $request->status_id);
        }

        if ($request->reject_status_id) {
            $items->where("form.reject_status_id", $request->reject_status_id);
        }

        if ($request->next_coop) {
            $items->where("form.next_coop", $request->next_coop);
        }

        if ($request->province_id) {
            $items->where("form.province_id", $request->province_id);
        }

        if ($request->amphur_id) {
            $items->where("form.amphur_id", $request->amphur_id);
        }

        if ($request->tumbol_id) {
            $items->where("form.tumbol_id", $request->tumbol_id);
        }

        if ($request->active) {
            $items->where("form.active", $request->active);
        }

        if ($request->is_pass_coop_subject) {
            $items->where(
                "form.is_pass_coop_subject",
                $request->is_pass_coop_subject
            );
        }

        if ($request->is_pass_general_subject) {
            $items->where(
                "form.is_pass_general_subject",
                $request->is_pass_general_subject
            );
        }

        if ($request->is_pass_gpa) {
            $items->where("form.is_pass_gpa", $request->is_pass_gpa);
        }

        if ($request->is_pass_suspend) {
            $items->where("form.is_pass_suspend", $request->is_pass_suspend);
        }

        if ($request->is_pass_punishment) {
            $items->where(
                "form.is_pass_punishment",
                $request->is_pass_punishment
            );
        }

        if ($request->is_pass_disease) {
            $items->where("form.is_pass_disease", $request->is_pass_disease);
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

        $item = Form::select(
            "form.id as id",
            "form.supervision_id as supervision_id",
            "form.semester_id as semester_id",
            "form.student_id as student_id",
            "form.company_id as company_id",
            "form.status_id as status_id",
            "form.start_date as start_date",
            "form.end_date as end_date",
            "form.co_name as co_name",
            "form.co_position as co_position",
            "form.co_tel as co_tel",
            "form.co_email as co_email",
            "form.request_name as request_name",
            "form.request_position as request_position",
            "form.request_document_date as request_document_date",
            "form.request_document_number as request_document_number",
            "form.max_response_date as max_response_date",
            "form.send_document_date as send_document_date",
            "form.send_document_number as send_document_number",
            DB::raw(
                "(CASE WHEN form.response_document_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',form.response_document_file) END) AS response_document_file"
            ),
            "form.response_send_at as response_send_at",
            "form.response_province_id as response_province_id",
            "form.confirm_response_at as confirm_response_at",
            "form.workplace_address as workplace_address",
            "form.workplace_province_id as workplace_province_id",
            "form.workplace_amphur_id as workplace_amphur_id",
            "form.workplace_tumbol_id as workplace_tumbol_id",
            "form.workplace_googlemap_url as workplace_googlemap_url",
            "form.workplace_googlemap_file as workplace_googlemap_file",
            "form.plan_document_file as plan_document_file",
            "form.plan_send_at as plan_send_at",
            "form.plan_accept_at as plan_accept_at",
            "form.reject_status_id as reject_status_id",
            "form.advisor_verified_at as advisor_verified_at",
            "form.chairman_approved_at as chairman_approved_at",
            "form.faculty_confirmed_at as faculty_confirmed_at",
            "form.company_rating as company_rating",
            "form.rating_comment as rating_comment",
            "form.next_coop as next_coop",
            "form.is_pass_coop_subject as is_pass_coop_subject",
            "form.is_pass_general_subject as is_pass_general_subject",
            "form.is_pass_gpa as is_pass_gpa",
            "form.is_pass_suspend as is_pass_suspend",
            "form.is_pass_punishment as is_pass_punishment",
            "form.is_pass_disease as is_pass_disease",
            DB::raw(
                "(CASE WHEN form.namecard_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',form.namecard_file) END) AS namecard_file"
            ),
            "form.province_id as province_id",
            "form.amphur_id as amphur_id",
            "form.tumbol_id as tumbol_id",
            "form.active as active",
            DB::raw(
                "concat(teacher.prefix,'',teacher.firstname, ' ', teacher.surname) as supervision_name"
            ),
            DB::raw(
                "concat(semester.term,'/',semester.semester_year, ' รอบที่ ', semester.round_no) as semester_name"
            ),
            DB::raw(
                "concat(student.firstname, ' ', student.surname) as student_fullname"
            ),
            "company.name_th as company_name",
            "form_status.name_th as form_status_name",
            "response_province.name_th as response_province_name",
            "province.name_th as province_name",
            "amphur.name_th as amphur_name",
            "tumbol.name_th as tumbol_name"
        )
            ->where("form.id", $id)
            ->leftJoin(
                "province",
                "province.province_id",
                "=",
                "form.province_id"
            )
            ->leftJoin("amphur", "amphur.amphur_id", "=", "form.amphur_id")
            ->leftJoin("tumbol", "tumbol.tumbol_id", "=", "form.tumbol_id")
            ->leftJoin("teacher", "teacher.id", "=", "form.supervision_id")
            ->leftJoin("semester", "semester.id", "=", "form.semester_id")
            ->leftJoin("student", "student.id", "=", "form.student_id")
            ->leftJoin("company", "company.id", "=", "form.company_id")
            ->leftJoin(
                "form_status",
                "form_status.status_id",
                "=",
                "form.status_id"
            )
            ->leftJoin(
                "province as response_province",
                "response_province.province_id",
                "=",
                "form.response_province_id"
            )
            ->with("reject_log")
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
        $request->validate(["semester_id as required"]);

        $pathNamecard = null;
        if (
            $request->namecard_file != "" &&
            $request->namecard_file != "null" &&
            $request->namecard_file != "undefined"
        ) {
            $fileNamecard =
                "namecard-" .
                rand(10, 100) .
                "-" .
                $request->file("namecard_file")->getClientOriginalName();
            $pathNamecard = "/form/namecard/" . $fileNamecard;
            Storage::disk("public")->put(
                $pathNamecard,
                file_get_contents($request->namecard_file)
            );
        }

        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($value == "null") {
                $request[$key] = null;
            }
        }

        $item = new Form();
        $item->semester_id = $request->semester_id;
        $item->supervision_id = $request->supervision_id;
        $item->student_id = $request->student_id;
        $item->company_id = $request->company_id;
        $item->status_id = $request->status_id;
        $item->start_date = $request->start_date;
        $item->end_date = $request->end_date;
        $item->co_name = $request->co_name;
        $item->co_position = $request->co_position;
        $item->co_tel = $request->co_tel;
        $item->co_email = $request->co_email;
        $item->request_name = $request->request_name;
        $item->request_position = $request->request_position;
        $item->request_document_date = $request->request_document_date;
        $item->request_document_number = $request->request_document_number;
        $item->max_response_date = $request->max_response_date;
        $item->send_document_date = $request->send_document_date;
        $item->send_document_number = $request->send_document_number;
        $item->response_document_file = $request->response_document_file;
        $item->response_send_at = $request->response_send_at;
        $item->response_province_id = $request->response_province_id;
        $item->confirm_response_at = $request->confirm_response_at;
        $item->workplace_address = $request->workplace_address;
        $item->workplace_province_id = $request->workplace_province_id;
        $item->workplace_amphur_id = $request->workplace_amphur_id;
        $item->workplace_tumbol_id = $request->workplace_tumbol_id;
        $item->workplace_googlemap_url = $request->workplace_googlemap_url;
        $item->workplace_googlemap_file = $request->workplace_googlemap_file;
        $item->plan_document_file = $request->plan_document_file;
        $item->plan_send_at = $request->plan_send_at;
        $item->plan_accept_at = $request->plan_accept_at;
        $item->reject_status_id = $request->reject_status_id;
        $item->advisor_verified_at = $request->advisor_verified_at;
        $item->chairman_approved_at = $request->chairman_approved_at;
        $item->faculty_confirmed_at = $request->faculty_confirmed_at;
        $item->company_rating = $request->company_rating;
        $item->rating_comment = $request->rating_comment;
        $item->next_coop = $request->next_coop;
        $item->province_id = $request->province_id;
        $item->amphur_id = $request->amphur_id;
        $item->tumbol_id = $request->tumbol_id;
        $item->active = $request->active;
        $item->namecard_file = $pathNamecard;

        $item->is_pass_coop_subject = $request->is_pass_coop_subject;
        $item->is_pass_general_subject = $request->is_pass_general_subject;
        $item->is_pass_gpa = $request->is_pass_gpa;
        $item->is_pass_suspend = $request->is_pass_suspend;
        $item->is_pass_punishment = $request->is_pass_punishment;
        $item->is_pass_disease = $request->is_pass_disease;
        $item->created_by = "arnonr";
        $item->save();

        // $student = Student::where("id", $item->student_id)->first();
        // $student->status_id = $item->status_id;
        // $student->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate(["id as required"]);

        $item = Form::where("id", $request->id)->first();

        $pathNamecard = null;
        if (
            $request->namecard_file != "" &&
            $request->namecard_file != "null" &&
            $request->namecard_file != "undefined"
        ) {
            $fileNamecard =
                "namecard-" .
                rand(10, 100) .
                "-" .
                $request->file("namecard_file")->getClientOriginalName();
            $pathNamecard = "/form/namecard/" . $fileNamecard;
            Storage::disk("public")->put(
                $pathNamecard,
                file_get_contents($request->namecard_file)
            );
            $request->namecard_file = $pathNamecard;
        } else {
            $pathNamecard = $item->namecard_file;
        }

        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($value == "null") {
                $request->$key = null;
            }
        }

        $item->semester_id = $request->has("semester_id")
            ? $request->semester_id
            : $item->semester_id;
        $item->supervision_id = $request->has("supervision_id")
            ? $request->supervision_id
            : $item->supervision_id;
        $item->student_id = $request->has("student_id")
            ? $request->student_id
            : $item->student_id;
        $item->company_id = $request->has("company_id")
            ? $request->company_id
            : $item->company_id;
        $item->status_id = $request->has("status_id")
            ? $request->status_id
            : $item->status_id;
        $item->start_date = $request->has("start_date")
            ? $request->start_date
            : $item->start_date;
        $item->end_date = $request->has("end_date")
            ? $request->end_date
            : $item->end_date;
        $item->co_name = $request->has("co_name")
            ? $request->co_name
            : $item->co_name;
        $item->co_position = $request->has("co_position")
            ? $request->co_position
            : $item->co_position;
        $item->co_tel = $request->has("co_tel")
            ? $request->co_tel
            : $item->co_tel;
        $item->co_email = $request->has("co_email")
            ? $request->co_email
            : $item->co_email;
        $item->request_name = $request->has("request_name")
            ? $request->request_name
            : $item->request_name;
        $item->request_position = $request->has("request_position")
            ? $request->request_position
            : $item->request_position;
        $item->request_document_date = $request->has("request_document_date")
            ? $request->request_document_date
            : $item->request_document_date;
        $item->request_document_number = $request->has(
            "request_document_number"
        )
            ? $request->request_document_number
            : $item->request_document_number;
        $item->max_response_date = $request->has("max_response_date")
            ? $request->max_response_date
            : $item->max_response_date;
        $item->send_document_date = $request->has("send_document_date")
            ? $request->send_document_date
            : $item->send_document_date;
        $item->send_document_number = $request->has("send_document_number")
            ? $request->send_document_number
            : $item->send_document_number;
        $item->response_document_file = $request->has("response_document_file")
            ? $request->response_document_file
            : $item->response_document_file;
        $item->response_send_at = $request->has("response_send_at")
            ? $request->response_send_at
            : $item->response_send_at;
        $item->response_province_id = $request->has("response_province_id")
            ? $request->response_province_id
            : $item->response_province_id;
        $item->confirm_response_at = $request->has("confirm_response_at")
            ? $request->confirm_response_at
            : $item->confirm_response_at;
        $item->workplace_address = $request->has("workplace_address")
            ? $request->workplace_address
            : $item->workplace_address;
        $item->workplace_province_id = $request->has("workplace_province_id")
            ? $request->workplace_province_id
            : $item->workplace_province_id;
        $item->workplace_amphur_id = $request->has("workplace_amphur_id")
            ? $request->workplace_amphur_id
            : $item->workplace_amphur_id;
        $item->workplace_tumbol_id = $request->has("workplace_tumbol_id")
            ? $request->workplace_tumbol_id
            : $item->workplace_tumbol_id;
        $item->workplace_googlemap_url = $request->has(
            "workplace_googlemap_url"
        )
            ? $request->workplace_googlemap_url
            : $item->workplace_googlemap_url;
        $item->workplace_googlemap_file = $request->has(
            "workplace_googlemap_file"
        )
            ? $request->workplace_googlemap_file
            : $item->workplace_googlemap_file;
        $item->plan_document_file = $request->has("plan_document_file")
            ? $request->plan_document_file
            : $item->plan_document_file;
        $item->plan_send_at = $request->has("plan_send_at")
            ? $request->plan_send_at
            : $item->plan_send_at;
        $item->plan_accept_at = $request->has("plan_accept_at")
            ? $request->plan_accept_at
            : $item->plan_accept_at;
        $item->reject_status_id = null;
        $item->advisor_verified_at = $request->has("advisor_verified_at")
            ? $request->advisor_verified_at
            : $item->advisor_verified_at;
        $item->chairman_approved_at = $request->has("chairman_approved_at")
            ? $request->chairman_approved_at
            : $item->chairman_approved_at;
        $item->faculty_confirmed_at = $request->has("faculty_confirmed_at")
            ? $request->faculty_confirmed_at
            : $item->faculty_confirmed_at;
        $item->company_rating = $request->has("company_rating")
            ? $request->company_rating
            : $item->company_rating;
        $item->rating_comment = $request->has("rating_comment")
            ? $request->rating_comment
            : $item->rating_comment;
        $item->next_coop = $request->has("next_coop")
            ? $request->next_coop
            : $item->next_coop;
        $item->namecard_file = $pathNamecard;
        $item->province_id = $request->has("province_id")
            ? $request->province_id
            : $item->province_id;
        $item->amphur_id = $request->has("amphur_id")
            ? $request->amphur_id
            : $item->amphur_id;
        $item->tumbol_id = $request->has("tumbol_id")
            ? $request->tumbol_id
            : $item->tumbol_id;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->is_pass_coop_subject = $request->has("is_pass_coop_subject")
            ? $request->is_pass_coop_subject
            : $item->is_pass_coop_subject;
        $item->is_pass_general_subject = $request->has(
            "is_pass_general_subject"
        )
            ? $request->is_pass_general_subject
            : $item->is_pass_general_subject;
        $item->is_pass_gpa = $request->has("is_pass_gpa")
            ? $request->is_pass_gpa
            : $item->is_pass_gpa;
        $item->is_pass_suspend = $request->has("is_pass_suspend")
            ? $request->is_pass_suspend
            : $item->is_pass_suspend;
        $item->is_pass_punishment = $request->has("is_pass_punishment")
            ? $request->is_pass_punishment
            : $item->is_pass_punishment;
        $item->is_pass_disease = $request->has("is_pass_disease")
            ? $request->is_pass_disease
            : $item->is_pass_disease;
        $item->updated_by = "arnonr";
        $item->save();

        $student = Student::where("id", $item->student_id)->first();
        $student->status_id = $item->status_id;
        $student->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function approve($id, Request $request)
    {
        $request->validate(["id as required"]);

        $item = Form::where("id", $request->id)->first();

        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($value == "null") {
                $request->$key = null;
            }
        }

        $item->reject_status_id = null;
        $item->status_id = $request->has("status_id")
            ? $request->status_id
            : $item->status_id;
        $item->advisor_verified_at = $request->has("advisor_verified_at")
            ? $request->advisor_verified_at
            : $item->advisor_verified_at;
        $item->chairman_approved_at = $request->has("chairman_approved_at")
            ? $request->chairman_approved_at
            : $item->chairman_approved_at;
        $item->faculty_confirmed_at = $request->has("faculty_confirmed_at")
            ? $request->faculty_confirmed_at
            : $item->faculty_confirmed_at;
        $item->confirm_response_at = $request->has("confirm_response_at")
            ? $request->confirm_response_at
            : $item->confirm_response_at;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->updated_by = "arnonr";
        $item->save();

        $student = Student::where("id", $item->student_id)->first();
        $student->status_id = $item->status_id;
        $student->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function addRequestBook(Request $request)
    {
        $request->validate(["id as required"]);

        Form::whereIn("id", $request->id)->update([
            "request_document_number" => $request->request_document_number,
            "request_document_date" => $request->request_document_date,
            "max_response_date" => $request->max_response_date,
            "updated_by" => "arnonr",
        ]);
        // รอส่งเมลแจ้งเตือนหนังสือขอความอนุเคราะห์
        //send_mail = 1

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }

    public function addSendBook(Request $request)
    {
        $request->validate(["id as required"]);

        Form::whereIn("id", $request->id)->update([
            "send_document_number" => $request->send_document_number,
            "send_document_date" => $request->send_document_date,
            "updated_by" => "arnonr",
        ]);
        // รอส่งเมลแจ้งเตือนหนังสือส่งตัว

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }

    public function addResponseBook(Request $request)
    {
        $request->validate(["id as required"]);

        $item = Form::where("id", $request->id)->first();

        $pathResponseFile = null;
        if (
            $request->response_document_file != "" &&
            $request->response_document_file != "null" &&
            $request->response_document_file != "undefined"
        ) {
            $fileResponse =
                "response-" .
                rand(10, 100) .
                "-" .
                $request
                    ->file("response_document_file")
                    ->getClientOriginalName();
            $pathResponseFile = "/student/response-document/" . $fileResponse;
            Storage::disk("public")->put(
                $pathResponseFile,
                file_get_contents($request->response_document_file)
            );
        } else {
            $pathResponseFile = $item->response_document_file;
        }

        $item->response_document_file = $pathResponseFile;
        $item->response_send_at = $request->response_send_at;
        $item->response_province_id = $request->province_id;
        $item->status_id = $request->status_id;
        $item->updated_by = "arnonr";
        $item->save();

        $student = Student::where("id", $item->student_id)->first();
        $student->status_id = $item->status_id;
        $student->save();

        // student

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $item = Form::where("id", $id)->first();

        $item->active = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }

    public function importFormSupervisor(Request $request)
    {
        if(!$request->has('semester_id')) {
            return response()->json(["message" => "No semester_id"], 400);
        }

        if(!$request->has('data')) {
            return response()->json(["message" => "No data"], 400);
        }

        $semester_id = $request->semester_id;

        // print_r($request->all());
        // Sample JSON data
        // {
        //     "semester_id": 3,
        //     "data": [
        //     {"student_code": "5402041520261","firstname": "ธานินทร์","surname": "ศิลป์จารุ"},
        //     {"student_code": "5402041520016","firstname": "ทวีศักดิ์","surname": "รูปสิงห์"}
        //     ]
        // }

        $data = [];
        $student_code_list = [];
        if(count($request->data) > 0){
            foreach ($request->data as $key => $value) {

                $import_message = [];
                $status = true;

                $student_code = $value['student_code'];
                $firstname = $value['firstname'];
                $surname = $value['surname'];
                $student_code_list[] = $student_code;

                $student = Student::where("student_code", $student_code)->first();
                if ($student === null) {
                    $import_message[] = "Student not found";
                    $status = false;
                }

                $teacher = Teacher::where("firstname", $firstname)
                    ->where("surname", $surname)
                    ->first();
                if ($teacher === null) {
                    $import_message[] = "Teacher not found";
                    $status = false;
                }

                if ($student !== null && $teacher !== null) {
                    $form = Form::where("semester_id", $semester_id)
                        ->where("student_id", $student->id)
                        ->where("active", 1)
                        ->first();

                    if ($form === null) {
                        $import_message[] = "Form not found";
                        $status = false;
                    } else {
                        $form->supervision_id = $teacher->id;
                        $form->save();
                        $import_message[] = "success";
                    }
                }

                $data[$student_code] = [
                    "status" => $status,
                    "message" => implode(", ", $import_message),
                ];

            } /* !-- foreach */
        }

        // $student_code_list
        $this->testSendMail($student_code_list);

        $responseData = [
            "message" => "success",
            "data" => $data,
        ];

        return response()->json($responseData, 200);
    }

    private function testSendMail($student_code = [])
    {
        // Sample input
        // $student_code = ["5402041520261", "5402041520016"];

        $student = Student::whereIn("student_code", $student_code)->get();
        $email_list = [];
        foreach ($student as $key => $value) {

            $name = $value->firstname ." ". $value->surname;
            $email = $value->email;

            if(empty($email)) continue;

            $email_list[] = [
                "name" => $name,
                "email" => $email,
            ];
        }

        $subject = "หนังสือส่งตัวสหกิจศึกษา คณะบริหารธุรกิจ มจพ.";
        $body = "ท่านได้รับการอนุมัติหนังสือส่งสหกิจศึกษา คณะบริหารธุรกิจ มจพ. เรียบร้อยแล้ว กรุณาตรวจสอบที่ระบบสหกิจศึกษา";

        $this->sendEmail($email_list, $subject, $body);
    }

    private function sendEmail($receiverList = [], $subject = "", $body = "")
    {
        // $receiverList = []
        // print_r($receiverList);

        if(empty($receiverList)) return null;

        // $sender = "arnon.r@technopark.kmutnb.ac.th";
        $username = "";
        $password = "";

        $sender = $username;
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->CharSet    = "UTF-8";

            //Recipients
            $mail->setFrom($sender , 'CWIE-FBA');
            // $mail->addAddress($receiver, 'Receiver'); /* Receiver */
            $mail->addReplyTo($sender , 'CWIE-FBA');
            // $mail->addCC('arnon.r@technopark.kmutnb.ac.th');
            // $mail->addCC('siwakorn.l@icit.kmutnb.ac.th', "ศิวกร");

            foreach ($receiverList as $key => $value) {
                $email = $value['email'];
                $name = $value['name'];
                $mail->addCC($email, $name);
            }
            // print_r($mail->getCcAddresses());

            // Content
            $mail->isHTML(true);
            // $mail->Subject = 'หนังสือส่งตัว สหกิจศึกษา';
            // $mail->Body    = 'Body หนังสือส่งตัว';
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // $mail->AltBody = 'Test mail from Laravel : This is the body in plain text for non-HTML mail clients';

            $mail->send();
            // echo 'Message has been sent';

        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
