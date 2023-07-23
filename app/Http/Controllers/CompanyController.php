<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
const whitelist = ["127.0.0.1", "::1", "localhost"];

class CompanyController extends Controller
{
    protected $uploadUrl = "http://co-op.fba.kmutnb.ac.th/storage/";

    public function getAll(Request $request)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $items = Company::select(
            "company.id as id",
            "company.name_th as name_th",
            "company.name_en as name_en",
            "company.tel as tel",
            "company.fax as fax",
            "company.email as email",
            "company.website as website",
            "company.blacklist as blacklist",
            "company.comment as comment",
            // 'company.namecard_file as namecard_file',
            // 'company.location_file as location_file',
            DB::raw(
                "(CASE WHEN namecard_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',namecard_file) END) AS namecard_file"
            ),
            DB::raw(
                "(CASE WHEN location_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',location_file) END) AS location_file"
            ),
            "company.address as address",
            "company.province_id as province_id",
            "company.amphur_id as amphur_id",
            "company.tumbol_id as tumbol_id",
            "company.active as active",
            "company.is_confirmed as is_confirmed"
        )->where("company.deleted_at", null);

        // Include
        if ($request->includeAll) {
            $items->addSelect(
                "province.name_th as province_name",
                "amphur.name_th as amphur_name",
                "tumbol.name_th as tumbol_name"
            );
            $items->leftJoin(
                "province",
                "province.province_id",
                "=",
                "company.province_id"
            );
            $items->leftJoin(
                "amphur",
                "amphur.amphur_id",
                "=",
                "company.amphur_id"
            );
            $items->leftJoin(
                "tumbol",
                "tumbol.tumbol_id",
                "=",
                "company.tumbol_id"
            );
        }

        if ($request->includeProvince) {
            $items->addSelect("province.name_th as province_name");
            $items->leftJoin(
                "province",
                "province.province_id",
                "=",
                "company.province_id"
            );
        }

        if ($request->includeAmphur) {
            $items->addSelect("amphur.name_th as amphur_name");
            $items->leftJoin(
                "amphur",
                "amphur.amphur_id",
                "=",
                "company.amphur_id"
            );
        }

        if ($request->includeTumbol) {
            $items->addSelect("tumbol.name_th as tumbol_name");
            $items->leftJoin(
                "tumbol",
                "tumbol.tumbol_id",
                "=",
                "company.tumbol_id"
            );
        }

        // Where
        if ($request->id) {
            $items->where("company.id", $request->id);
        }

        if ($request->name_th) {
            $items->where(
                "company.name_th",
                "LIKE",
                "%" . $request->name_th . "%"
            );
        }

        if ($request->name_en) {
            $items->where(
                "company.name_en",
                "LIKE",
                "%" . $request->name_en . "%"
            );
        }

        if ($request->province_id) {
            $items->where("company.province_id", $request->province_id);
        }

        if ($request->amphur_id) {
            $items->where("company.amphur_id", $request->amphur_id);
        }

        if ($request->tumbol_id) {
            $items->where("company.tumbol_id", $request->tumbol_id);
        }

        if ($request->blacklist) {
            $items->where("company.blacklist", $request->blacklist);
        }

        if ($request->active) {
            $items->where("company.active", $request->active);
        }

        if ($request->is_confirmed) {
            $items->where("company.is_confirmed", $request->is_confirmed);
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

        $item = Company::select(
            "company.id as id",
            "company.name_th as name_th",
            "company.name_en as name_en",
            "company.tel as tel",
            "company.fax as fax",
            "company.email as email",
            "company.website as website",
            "company.blacklist as blacklist",
            "company.comment as comment",
            // 'company.namecard_file as namecard_file',
            // 'company.location_file as location_file',
            DB::raw(
                "(CASE WHEN namecard_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',namecard_file) END) AS namecard_file"
            ),
            DB::raw(
                "(CASE WHEN location_file = NULL THEN ''
            ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',location_file) END) AS location_file"
            ),
            "company.address as address",
            "company.province_id as province_id",
            "company.amphur_id as amphur_id",
            "company.tumbol_id as tumbol_id",
            "company.active as active",
            "company.is_confirmed as is_confirmed",
            "province.name_th as province_name",
            "amphur.name_th as amphur_name",
            "tumbol.name_th as tumbol_name",
            "tumbol.postcode as postcode"
        )
            ->where("company.id", $id)
            ->leftJoin(
                "province",
                "province.province_id",
                "=",
                "company.province_id"
            )
            ->leftJoin("amphur", "amphur.amphur_id", "=", "company.amphur_id")
            ->leftJoin("tumbol", "tumbol.tumbol_id", "=", "company.tumbol_id")
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
        $request->validate(["name_th as required"]);

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
            $pathNamecard = "/company/namecard/" . $fileNamecard;
            Storage::disk("public")->put(
                $pathNamecard,
                file_get_contents($request->namecard_file)
            );
            // $request->namecard_file = $pathNamecard;
        }

        $pathLocation = null;
        if (
            $request->location_file != "" &&
            $request->location_file != "null" &&
            $request->location_file != "undefined"
        ) {
            $fileLocation =
                "location-" .
                rand(10, 100) .
                "-" .
                $request->file("location_file")->getClientOriginalName();
            $pathLocation = "/company/location/" . $fileLocation;
            Storage::disk("public")->put(
                $pathLocation,
                file_get_contents($request->location_file)
            );
            // $request->location_file = $pathLocation;
        }

        // $data = $request->all();

        // foreach ($data as $key => $value) {
        //     if($value == 'null'){
        //         $data[$key] = null;
        //     }
        // }

        $item = new Company();
        $item->name_th = $request->name_th;
        $item->name_en = $request->name_en;
        $item->tel = $request->tel;
        $item->fax = $request->fax;
        $item->email = $request->email;
        $item->website = $request->website;
        $item->blacklist = $request->blacklist;
        $item->comment = $request->comment;
        $item->namecard_file = $pathNamecard;
        $item->location_file = $pathLocation;
        $item->address = $request->address;
        $item->province_id = $request->province_id;
        $item->amphur_id = $request->amphur_id;
        $item->tumbol_id = $request->tumbol_id;
        $item->active = $request->active;
        $item->is_confirmed = $request->is_confirmed;
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

        $item = Company::where("id", $request->id)->first();

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
            $pathNamecard = "/company/namecard/" . $fileNamecard;
            Storage::disk("public")->put(
                $pathNamecard,
                file_get_contents($request->namecard_file)
            );
            $request->namecard_file = $pathNamecard;
        } else {
            $pathNamecard = $item->namecard_file;
        }

        $pathLocation = null;
        if (
            $request->location_file != "" &&
            $request->location_file != "null" &&
            $request->location_file != "undefined"
        ) {
            $fileLocation =
                "location-" .
                rand(10, 100) .
                "-" .
                $request->file("location_file")->getClientOriginalName();
            $pathLocation = "/company/location/" . $fileLocation;
            Storage::disk("public")->put(
                $pathLocation,
                file_get_contents($request->location_file)
            );
            $request->location_file = $pathLocation;
        } else {
            $pathLocation = $item->location_file;
        }

        $item->name_th = $request->has("name_th")
            ? $request->name_th
            : $item->name_th;
        $item->name_en = $request->has("name_en")
            ? $request->name_en
            : $item->name_en;
        $item->tel = $request->has("tel") ? $request->tel : $item->tel;
        $item->fax = $request->has("fax") ? $request->fax : $item->fax;
        $item->email = $request->has("email") ? $request->email : $item->email;
        $item->website = $request->has("website")
            ? $request->website
            : $item->website;
        $item->blacklist = $request->has("blacklist")
            ? $request->blacklist
            : $item->blacklist;
        $item->comment = $request->has("comment")
            ? $request->comment
            : $item->comment;
        $item->namecard_file = $pathNamecard;
        $item->location_file = $pathLocation;
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
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;
        $item->is_confirmed = $request->has("is_confirmed")
            ? $request->is_confirmed
            : $item->is_confirmed;
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
        $item = Company::where("id", $id)->first();

        $item->active = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }
}
