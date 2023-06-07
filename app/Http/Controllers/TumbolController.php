<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tumbol;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class TumbolController extends Controller
{

    public function getAll(Request $request)
    {
        $items = Tumbol::select(
            'tumbol.tumbol_id as tumbol_id',
            'tumbol.tumbol_code as tumbol_code',
            'tumbol.amphur_id as amphur_id',
            'tumbol.name_th as name_th',
            'tumbol.name_en as name_en',
            'tumbol.active as active',
        )
        ->where('tumbol.deleted_at', null);

        if($request->includeAll){
            $items->addSelect('amphur.name_th as amphur_name');
            $items->leftJoin('amphur','amphur.amphur_id','=','tumbol.amphur_id');
        }

        if ($request->tumbol_id) {
            $items->where('tumbol.tumbol_id', $request->tumbol_id);
        }

        if ($request->tumbol_code) {
            $items->where('tumbol.tumbol_code', $request->tumbol_code);
        }

        if ($request->amphur_id) {
            $items->where('tumbol.amphur_id', $request->amphur_id);
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
            $items = $items->orderBy('tumbol_id', 'asc');
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
        $item = Tumbol::select(
                'tumbol.tumbol_id as tumbol_id',
                'tumbol.tumbol_code as tumbol_code',
                'tumbol.amphur_id as amphur_id',
                'tumbol.name_th as name_th',
                'tumbol.name_en as name_en',
                'tumbol.active as active',
                'amphur.name_th as amphur_name',
            )
            ->where('tumbol.tumbol_id', $id)
            ->leftJoin('amphur','amphur.amphur_id','=','tumbol.amphur_id')
            ->first();

        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }
}
