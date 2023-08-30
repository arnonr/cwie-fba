<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Major;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class MajorController extends Controller
{

    public function getAll(Request $request)
    {
        $items = Major::select(
            'major.id as id',
            'major.major_code as major_code',
            'major.department_id as department_id',
            'major.name_th as name_th',
            'major.name_en as name_en',
            'major.active as active',
        )
        ->where('major.deleted_at', null);

        if($request->includeAll){
            $items->addSelect('department.name_th as department_name');
            $items->leftJoin('department','department.id','=','major.department_id');
        }

        if ($request->id) {
            $items->where('id', $request->id);
        }

        if ($request->major_code) {
            $items->where('major.major_code', $request->major_code);
        }

        if ($request->department_id) {
            $items->where('department_id', $request->department_id);
        }

        if ($request->name_th) {
            $items->where('major.name_th','LIKE',"%".$request->name_th."%");
        }

        if ($request->name_en) {
            $items->where('major.name_en','LIKE',"%".$request->name_en."%");
        }

        if ($request->active) {
            $items->where('major.active', $request->active);
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
        $item = Major::select(
                'major.id as id',
                'major.major_code as major_code',
                'major.department_id as department_id',
                'major.name_th as name_th',
                'major.name_en as name_en',
                'major.active as active',
                'department.name as department_name',
            )
            ->where('major.id', $id)
            ->leftJoin('department','department.id','=','major.department_id')
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

        $data = Major::where('id', $id)->update($request->all());

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }
}
