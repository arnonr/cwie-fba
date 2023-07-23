<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentDocument;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
const whitelist = ["127.0.0.1", "::1", "localhost:8117"];

class StudentDocumentController extends Controller
{
    protected $uploadUrl = "http://co-op.fba.kmutnb.ac.th/storage/";

    public function getAll(Request $request)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $items = StudentDocument::select(
            "student_document.id as id",
            "student_document.document_name as document_name",
            DB::raw(
                "(CASE WHEN document_file = NULL THEN ''
             ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',document_file) END) AS document_file"
            ),
            "student_document.student_id as student_id",
            "student_document.document_type_id as document_type_id",
            "student_document.active as active"
        )->where("student_document.deleted_at", null);

        // Include
        if ($request->includeAll) {
            $items->addSelect("document_type.name as document_type_name");
            $items->leftJoin(
                "document_type",
                "document_type.id",
                "=",
                "student_document.document_type_id"
            );
        }

        // Where
        if ($request->id) {
            $items->where("student_document.id", $request->id);
        }

        if ($request->document_name) {
            $items->where(
                "student_document.document_name",
                "LIKE",
                "%" . $request->document_name . "%"
            );
        }

        if ($request->student_id) {
            $items->where("student_document.student_id", $request->student_id);
        }

        if ($request->document_type_id) {
            $items->where(
                "student_document.document_type_id",
                $request->document_type_id
            );
        }

        if ($request->active) {
            $items->where("student_document.active", $request->active);
        }

        // Order
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
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $item = StudentDocument::select(
            "student_document.id as id",
            "student_document.document_name as document_name",
            DB::raw(
                "(CASE WHEN document_file = NULL THEN ''
             ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',document_file) END) AS document_file"
            ),
            "student_document.student_id as student_id",
            "student_document.document_type_id as document_type_id",
            "student_document.active as active",
            "document_type.name as document_type_name"
        )
            ->where("student_document.id", $id)
            ->leftJoin(
                "document_type",
                "document_type.id",
                "=",
                "student_document.document_type_id"
            )
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
            "document_name as required",
            "document_type_id as required",
            "student_id as required",
        ]);

        $pathDocument = null;
        if (
            $request->document_file != "" &&
            $request->document_file != "null" &&
            $request->document_file != "undefined"
        ) {
            $fileDocument =
                "document-" .
                rand(10, 100) .
                "-" .
                $request->file("document_file")->getClientOriginalName();
            $pathDocument = "/student/document/" . $fileDocument;
            Storage::disk("public")->put(
                $pathDocument,
                file_get_contents($request->document_file)
            );
        }
        var_dump($pathDocument);
        $item = new StudentDocument();
        $item->document_name = $request->document_name;
        $item->document_type_id = $request->document_type_id;
        $item->document_file = $pathDocument;
        $item->student_id = $request->student_id;
        $item->active = $request->active ? active : 1;
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

        $item = StudentDocument::where("id", $request->id)->first();

        $pathDocument = null;
        if (
            $request->document_file != "" &&
            $request->document_file != "null" &&
            $request->document_file != "undefined"
        ) {
            $fileDocument =
                "document-" .
                rand(10, 100) .
                "-" .
                $request->file("document_file")->getClientOriginalName();
            $pathDocument = "/student/document/" . $fileDocument;
            Storage::disk("public")->put(
                $pathDocument,
                file_get_contents($request->document_file)
            );
            $request->signature_file = $pathDocument;
        } else {
            $pathDocument = $item->document_file;
        }

        $item->document_name = $request->has("document_name")
            ? $request->document_name
            : $item->document_name;
        $item->document_type_id = $request->has("document_type_id")
            ? $request->document_type_id
            : $item->document_type_id;
        $item->document_file = $pathDocument;
        $item->student_id = $request->has("student_id")
            ? $request->student_id
            : $item->student_id;
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
        // $item = StudentDocument::where('id', $id)->first();

        // $item->active = 0;
        // $item->deleted_at = Carbon::now();
        // $item->save();

        $item = StudentDocument::where("id", $id)->delete();

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 204);
    }
}
