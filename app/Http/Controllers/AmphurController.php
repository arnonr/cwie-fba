<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Amphur;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class AmphurController extends Controller
{

    public function getAll(Request $request)
    {
        $items = Amphur::select(
            'amphur.amphur_id as amphur_id',
            'amphur.amphur_code as amphur_code',
            'amphur.province_id as province_id',
            'amphur.name_th as name_th',
            'amphur.name_en as name_en',
            'amphur.active as active',
        )
        ->where('amphur.deleted_at', null);

        if($request->includeAll){
            $items->addSelect('province.name_th as province_name');
            $items->leftJoin('province','province.province_id','=','amphur.province_id');
        }

        if ($request->amphur_id) {
            $items->where('amphur.amphur_id', $request->amphur_id);
        }

        if ($request->amphur_code) {
            $items->where('amphur.amphur_code', $request->amphur_code);
        }

        if ($request->province_id) {
            $items->where('amphur.province_id', $request->province_id);
        }

        if ($request->name_th) {
            $items->where('amphur.name_th','LIKE',"%".$request->name_th."%");
        }

        if ($request->name_en) {
            $items->where('amphur.name_en','LIKE',"%".$request->name_en."%");
        }

        if ($request->active) {
            $items->where('amphur.active', $request->active);
        }

        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
        }else{
            $items = $items->orderBy('amphur_id', 'asc');
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
        $item = Amphur::select(
                'amphur.amphur_id as amphur_id',
                'amphur.amphur_code as amphur_code',
                'amphur.province_id as province_id',
                'amphur.name_th as name_th',
                'amphur.name_en as name_en',
                'amphur.active as active',
                'province.name_th as province_name',
            )
            ->where('amphur.amphur_id', $id)
            ->leftJoin('province','province.province_id','=','amphur.province_id')
            ->first();

        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }

   
}
