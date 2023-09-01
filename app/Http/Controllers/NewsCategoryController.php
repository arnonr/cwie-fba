<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NewsCategory;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;

class NewsCategoryController extends Controller
{
    public function getAll(Request $request)
    {
        $items = NewsCategory::select(
            'news_category.id as id',
            'news_category.name as name',
            'news_category.active as active',
        )
        ->where('news_category.deleted_at', null);

        // Where
        if ($request->id) {
            $items->where('news_category.id', $request->id);
        }

        if ($request->name) {
            $items->where('news_category.name','LIKE',"%".$request->name."%");
        }

        if ($request->active) {
            $items->where('news_category.active', $request->active);
        }

        // Order
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
        $item = NewsCategory::select(
            'news_category.id as id',
            'news_category.name as name',
            'news_category.active as active',
            )
            ->where('news_category.id', $id)
            ->first();

        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }

    public function add(Request $request)
    {
        $request->validate([
            'name as required',
        ]);

        $item = new NewsCategory;
        $item->name = $request->name;
        $item->active = $request->active;
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

        $item = NewsCategory::where('id', $request->id)->first();
        $item->name = $request->has('name') ? $request->name : $item->name;
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
        $item = NewsCategory::where('id', $id)->first();
        $item->active = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }
}
