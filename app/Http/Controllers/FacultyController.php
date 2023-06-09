<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Faculty;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class FacultyController extends Controller
{

    public function getAll(Request $request)
    {
        $items = Faculty::select(
            'faculty.id as id',
            'faculty.faculty_code as faculty_code',
            'faculty.faculty_id as faculty_id',
            'faculty.name_th as name_th',
            'faculty.name_en as name_en',
            'faculty.active as active',
        )
        ->where('faculty.deleted_at', null);

        if ($request->faculty_id) {
            $items->where('faculty_id', $request->faculty_id);
        }

        if ($request->faculty_code) {
            $items->where('faculty.faculty_code', $request->faculty_code);
        }

        if ($request->name_th) {
            $items->where('faculty.name_th','LIKE',"%".$request->name_th."%");
        }

        if ($request->name_en) {
            $items->where('faculty.name_en','LIKE',"%".$request->name_en."%");
        }

        if ($request->active) {
            $items->where('faculty.active', $request->active);
        }

        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
        }else{
            $items = $items->orderBy('id', 'asc');
        }

        $count = $items->count();
        $perPage = $request->perPage ? $request->perPage : $count;
        $currentPage = $request->currentPage ? $request->currentPage : 1;

        $totalPage = ceil($count /$perPage) == 0 ? 1 : ceil($count / $perPage);
        $offset = $perPage * ($currentPage - 1);
        $items = $items->skip($offset)->take($perPage);
        $items = $items->get();

        return response()->json([
            'message' => 'success',
            'data' => $items,
            'totalPage' => $totalPage,
            'totalData' => $count,
        ], 200);
    }


    public function get($id)
    {
        $item = Faculty::select(
                'faculty.faculty_id as faculty_id',
                'faculty.faculty_code as faculty_code',
                'faculty.faculty_id as faculty_id',
                'faculty.name_th as name_th',
                'faculty.name_en as name_en',
                'faculty.active as active',
            )
            ->where('faculty.id', $id)
            ->first();

        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate([
            'id as required',
        ]);

        $data = Faculty::where('id', $id)->update($request->all());

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }

    public function import($code,$name){
        $item = Faculty::where('faculty_code',$code)->first();
        if(!$item){
            $item = new Faculty;
            $item->faculty_code = $code;
            $item->name_th = $name;
            $item->name_en = $name;
            $item->created_by = 'arnonr';
            $item->save();
        }

        // $responseData = [
        //     'message' => 'success',
        //     'data' =>  $item,
        // ];

        return $item;
    }
}
