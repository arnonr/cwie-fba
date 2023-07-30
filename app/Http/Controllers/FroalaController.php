<?php

namespace App\Http\Controllers;

use App\Models\NewsGallery;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
const whitelist = ["127.0.0.1", "::1", "localhost:8117"];

class FroalaController extends Controller
{
    protected $uploadUrl = "http://co-op.fba.kmutnb.ac.th/storage/";

    public function image(Request $request)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }
        $request->validate(["file as required"]);

        $pathFile = "";
        if ($request->file != "" && $request->file != "null") {
            $fileName =
                date("Y-m-d") .
                "-rand" .
                rand(10, 100) .
                "-" .
                $request->file("file")->getClientOriginalName();
            $pathFile = "/froala/image/" . $fileName;
            Storage::disk("public")->put(
                $pathFile,
                file_get_contents($request->file)
            );
        } else {
            return response()->json(
                [
                    "message" => "error Upload Not Found",
                ],
                200
            );
        }

        $res = $this->uploadUrl . $pathFile;

        $responseData = [
            "message" => "success",
            "link" => $res,
        ];

        return response()->json($responseData, 200);
    }

    public function document(Request $request)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }
        $request->validate(["file as required"]);

        $pathFile = "";
        if ($request->file != "" && $request->file != "null") {
            $fileName =
                date("Y-m-d") .
                "-rand" .
                rand(10, 100) .
                "-" .
                $request->file("file")->getClientOriginalName();
            $pathFile = "/froala/document/" . $fileName;
            Storage::disk("public")->put(
                $pathFile,
                file_get_contents($request->file)
            );
        } else {
            return response()->json(
                [
                    "message" => "error Upload Not Found",
                ],
                200
            );
        }

        $res = $this->uploadUrl . $pathFile;

        $responseData = [
            "message" => "success",
            "link" => $res,
        ];

        return response()->json($responseData, 200);
    }

    public function video(Request $request)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }
        $request->validate(["file as required"]);

        $pathFile = "";
        if ($request->file != "" && $request->file != "null") {
            $fileName =
                date("Y-m-d") .
                "-rand" .
                rand(10, 100) .
                "-" .
                $request->file("file")->getClientOriginalName();
            $pathFile = "/froala/video/" . $fileName;
            Storage::disk("public")->put(
                $pathFile,
                file_get_contents($request->file)
            );
        } else {
            return response()->json(
                [
                    "message" => "error Upload Not Found",
                ],
                200
            );
        }

        $res = $this->uploadUrl . $pathFile;

        $responseData = [
            "message" => "success",
            "link" => $res,
        ];

        return response()->json($responseData, 200);
    }
}
