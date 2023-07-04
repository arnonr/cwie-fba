<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{

    public function getAll(Request $request)
    {
        $items = Department::select(
            'department.id as id',
            'department.department_code as department_code',
            'department.faculty_id as faculty_id',
            'department.name_th as name_th',
            'department.name_en as name_en',
            'department.active as active',
        )
        ->where('department.deleted_at', null);

        if($request->includeAll){
            $items->addSelect('faculty.name_th as faculty_name');
            $items->leftJoin('faculty','faculty.id','=','department.faculty_id');
        }

        if ($request->department_id) {
            $items->where('department_id', $request->department_id);
        }

        if ($request->department_code) {
            $items->where('department.department_code', $request->department_code);
        }

        if ($request->faculty_id) {
            $items->where('faculty_id', $request->faculty_id);
        }

        if ($request->name_th) {
            $items->where('department.name_th','LIKE',"%".$request->name_th."%");
        }

        if ($request->name_en) {
            $items->where('department.name_en','LIKE',"%".$request->name_en."%");
        }

        if ($request->active) {
            $items->where('department.active', $request->active);
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
        $item = Department::select(
                'department.id as id',
                'department.department_code as department_code',
                'department.faculty_id as faculty_id',
                'department.name_th as name_th',
                'department.name_en as name_en',
                'department.active as active',
                'faculty.name_th as faculty_name',
            )
            ->where('department.id', $id)
            ->leftJoin('faculty','faculty.id','=','department.faculty_id')
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

        $data = Department::where('id', $id)->update($request->all());

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }

    public function import($code,$name){
        $item = Department::where('department_code',$code)->first();
        if(!$item){
            $item = new Department;
            $item->department_code = $code;
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
