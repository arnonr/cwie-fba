<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MajorHead;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class MajorHeadController extends Controller
{

    public function getAll(Request $request)
    {
        $items = MajorHead::select(
            'major_head.id as id',
            'major_head.semester_id as semester_id',
            'major_head.teacher_id as teacher_id',
            'major_head.major_id as major_id',
            'major_head.active as active',
        )
        ->where('major_head.deleted_at', null);

        if($request->includeAll){
            $items->addSelect('major.name_th as major_name',DB::raw("concat(teacher.prefix,'',teacher.firstname, ' ', teacher.surname) as teacher_name"));
            $items->leftJoin('major','major.id','=','major_head.major_id');
            $items->leftJoin('teacher','teacher.id','=','major_head.teacher_id');
        }

        if ($request->id) {
            $items->where('id', $request->id);
        }

        if ($request->semester_id) {
            $items->where('semester_id', $request->semester_id);
        }

        if ($request->teacher_id) {
            $items->where('teacher_id', $request->teacher_id);
        }

        if ($request->major_id) {
            $items->where('major_id', $request->major_id);
        }

        if ($request->active) {
            $items->where('major_head.active', $request->active);
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
        $item = MajorHead::select(
            'major_head.id as id',
            'major_head.semester_id as semester_id',
            'major_head.teacher_id as teacher_id',
            'major_head.major_id as major_id',
            'major_head.active as active',
            'major.name_th as major_name',
            DB::raw("concat(teacher.prefix,'',teacher.firstname, ' ', teacher.surname) as teacher_name")
        )->where('major_head.major_head_id', $id)
        ->leftJoin('major','major.id','=','major_head.major_id')
        ->leftJoin('teacher','teacher.id','=','major_head.teacher_id')
        ->first();

        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }

    public function add(Request $request)
    {
        $request->validate([
            'semester_id as required',
            'teacher_id as required',
            'major_id as required'
        ]);

        $item = new MajorHead;
        $item->semester_id =$request->semester_id;
        $item->teacher_id = $request->teacher_id;
        $item->major_id  = $request->major_id;
        // $item->active = $request->active;
        $item->created_by = 'arnonr';
        $item->save();

        $responseData = [
            'message' => 'success',
            'data' => $item,
        ];

        return response()->json($responseData, 200);
    }


    public function edit($id, Request $request)
    {
        $request->validate([
            'id as required',
        ]);

        $item = MajorHead::where('id', $request->id)->first();
        $item->semester_id = $request->has('semester_id') ? $request->semester_id : $item->semester_id;
        $item->teacher_id = $request->has('teacher_id') ? $request->teacher_id : $item->teacher_id;
        $item->major_id  = $request->has('major_id') ? $request->major_id : $item->major_id;
        $item->active = $request->has('active') ? $request->active : $item->active;
        $item->updated_by = 'arnonr';
        $item->save();

        $responseData = [
            'message' => 'success',
            'data' => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $data = MajorHead::where('id', $id)->first();

        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];
    }
}
