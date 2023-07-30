<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\News;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
const whitelist = ["127.0.0.1", "::1", "localhost:8117"];

class NewsController extends Controller
{
    protected $uploadUrl = "http://co-op.fba.kmutnb.ac.th/storage/";

    public function getAll(Request $request)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $items = News::select(
            "news.id as id",
            "news.news_title as news_title",
            "news.news_detail as news_detail",
            "news.active as active",
            DB::raw(
                "(CASE WHEN news_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',news_file) END) AS news_file"
            ),
            "news.pinned as pinned",
            "news.news_cate_id as news_cate_id"
            // "news_category.name as news_category_name"
        )
            ->where("news.deleted_at", null)
            ->leftJoin(
                "news_category",
                "news_category.id",
                "=",
                "news.news_cate_id"
            );

        // Where
        if ($request->id) {
            $items->where("news.id", $request->id);
        }

        if ($request->news_title) {
            $items->where(
                "news.news_title",
                "LIKE",
                "%" . $request->news_title . "%"
            );
        }

        if ($request->pinned) {
            $items->where("news.pinned", $request->pinned);
        }

        if ($request->news_cate_id) {
            $items->where("news.news_cate_id", $request->news_cate_id);
        }

        if ($request->news_detail) {
            $items->where(
                "news.news_detail",
                "LIKE",
                "%" . $request->news_detail . "%"
            );
        }

        if ($request->active) {
            $items->where("news.active", $request->active);
        }

        // Order
        if ($request->orderBy) {
            // $items = $items->orderBy($request->orderBy, $request->order);
            $items = $items->orderBy("pinned", "desc");
            $items = $items->orderBy("id", "asc");
        } else {
            $items = $items->orderBy("pinned", "desc");
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
        $item = News::select(
            "news.id as id",
            "news.news_title as news_title",
            "news.news_detail as news_detail",
            "news.active as active",
            DB::raw(
                "(CASE WHEN news_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',news_file) END) AS news_file"
            ),
            "news.pinned as pinned",
            "news.news_cate_id as news_cate_id"
            // "news_category.name as news_category_name"
        )
            ->where("news.id", $id)
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
        $request->validate(["news_title as required"]);

        $pathFile = null;
        if (
            $request->news_file != "" &&
            $request->news_file != "null" &&
            $request->news_file != "undefined"
        ) {
            $fileName =
                date("YmdHis") .
                "_document_" .
                rand(1, 10000) .
                "." .
                $request->file("news_file")->extension();
            $pathFile = "news/" . $fileName;
            Storage::disk("public")->put(
                $pathFile,
                file_get_contents($request->news_file)
            );
        }

        $item = new News();
        $item->news_title = $request->news_title;
        $item->news_detail = $request->news_detail;
        $item->active = $request->active;
        $item->pinned = $request->pinned;
        $item->news_cate_id = $request->news_cate_id;
        $item->news_file = $pathFile;
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

        $item = News::where("id", $request->id)->first();

        $pathFile = null;
        if (
            $request->news_file != "" &&
            $request->news_file != "null" &&
            $request->news_file != "undefined"
        ) {
            $fileName =
                date("YmdHis") .
                "_document_" .
                rand(1, 10000) .
                "." .
                $request->file("news_file")->extension();
            $pathFile = "news/" . $fileName;
            Storage::disk("public")->put(
                $pathFile,
                file_get_contents($request->news_file)
            );
            $request->news_file = $pathFile;
        } else {
            $pathFile = $item->news_file;
        }

        $item->news_title = $request->has("news_title")
            ? $request->news_title
            : $item->news_title;
        $item->news_detail = $request->has("news_detail")
            ? $request->news_detail
            : $item->news_detail;
        $item->pinned = $request->has("pinned")
            ? $request->pinned
            : $item->pinned;
        $item->news_cate_id = $request->has("news_cate_id")
            ? $request->news_cate_id
            : $item->news_cate_id;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->updated_by = "arnonr";
        $item->news_file = $pathFile;
        $item->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $item = News::where("id", $id)->first();
        $item->active = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }
}
