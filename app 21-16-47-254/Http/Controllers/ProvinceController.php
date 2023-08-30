<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Province;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class ProvinceController extends Controller
{
    public function getAll(Request $request)
    {
        $items = Province::select(
            'province.province_id as province_id',
            'province.province_code as province_code',
            'province.name_th as name_th',
            'province.name_en as name_en',
            'province.visit_expense as visit_expense',
            'province.travel_expense as travel_expense',
            'province.active as active',
        )
        ->where('province.deleted_at', null);

        if ($request->province_id) {
            $items->where('province.province_id', $request->province_id);
        }

        if ($request->province_code) {
            $items->where('province.province_code', $request->province_code);
        }

        if ($request->name_th) {
            $items->where('province.name_th','LIKE',"%".$request->name_th."%");
        }

        if ($request->name_en) {
            $items->where('province.name_en','LIKE',"%".$request->name_en."%");
        }

        if ($request->active) {
            $items->where('province.active', $request->active);
        }

        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
        }else{
            $items = $items->orderBy('province_id', 'asc');
        }
    
        $count = $items->count();
        $perPage = $request->perPage ? $request->perPage : $count;
        $currentPage = $request->currentPage ? $request->currentPage : 1;

        $totalPage = ceil($count/$perPage) == 0 ? 1 : ceil($count / $perPage);
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
        $item = Province::select(
                'province.province_id as province_id',
                'province.province_code as province_code',
                'province.name_th as name_th',
                'province.name_en as name_en',
                'province.visit_expense as visit_expense',
                'province.travel_expense as travel_expense',
                'province.active as active',
            )
            ->where('province.province_id', $id)
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

        $data = Province::where('province_id', $id)->update($request->all());

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $data = Province::where('province_id', $id)->first();
        
        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
    
}
