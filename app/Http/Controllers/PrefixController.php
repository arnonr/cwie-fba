<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PrefixName;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class PrefixNameController extends Controller
{
    public function getAll(Request $request)
    {
        $items = PrefixName::select(
            'prefix_name.id as id',
            'prefix_name.prefix_name prefix_name',
            'prefix_name.active as active',
        )
        ->where('prefix_name.deleted_at', null);


        if ($request->id) {
            $items->where('id', $request->id);
        }

        if ($request->prefix_name) {
            $items->where('prefix_name.prefix_name', $request->prefix_name);
        }

        if ($request->active) {
            $items->where('prefix_name.active', $request->active);
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
        $item = PrefixName::select(
                'prefix_name.id as id',
                'prefix_name.prefix_name_code as prefix_name_code',
                'prefix_name.department_id as department_id',
                'prefix_name.name_th as name_th',
                'prefix_name.name_en as name_en',
                'prefix_name.active as active',
                'department.name as department_name',
            )
            ->where('prefix_name.id', $id)
            ->leftJoin('department','department.id','=','prefix_name.department_id')
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

        $data = PrefixName::where('id', $id)->update($request->all());

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }
}
