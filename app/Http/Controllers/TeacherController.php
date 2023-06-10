<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

const whitelist = ['127.0.0.1', "::1","localhost:8117"];

class TeacherController extends Controller
{

    protected $uploadUrl = 'http://143.198.208.110:8117/storage';

    public function getAll(Request $request)
    {
        if(in_array($_SERVER['HTTP_HOST'], whitelist)){
            $this->uploadUrl = 'http://localhost:8117/storage';
        }

        $items = Teacher::select(
            DB::raw("(CASE WHEN signature_file = NULL THEN ''
             ELSE CONCAT('".$this->uploadUrl."',signature_file) END) AS signature_file"),
            'teacher.id as id',
            'teacher.user_id as user_id',
            'teacher.person_key as person_key',
            'teacher.prefix as prefix',
            'teacher.firstname as firstname',
            'teacher.surname as surname',
            'teacher.citizen_id as citizen_id',
            'teacher.tel as tel',
            'teacher.email as email',
            'teacher.address as address',
            'teacher.province_id as province_id',
            'teacher.amphur_id as amphur_id',
            'teacher.tumbol_id as tumbol_id',
            'teacher.faculty_id as faculty_id',
            'teacher.department_id as department_id',
            'teacher.executive_position as executive_position',
            'teacher.active as active',
        )
        ->where('teacher.deleted_at', null);

        // Include
        if($request->includeAll){
            $items->addSelect('amphur.name_th as amphur_name',
            'tumbol.name_th as tumbol_name',
            'faculty.name_th as faculty_name',
            'department.name_th as department_name');

            $items->leftJoin('province','province.province_id','=','teacher.province_id')
            ->leftJoin('amphur','amphur.amphur_id','=','teacher.amphur_id')
            ->leftJoin('tumbol','tumbol.tumbol_id','=','teacher.tumbol_id')
            ->leftJoin('faculty','faculty.id','=','teacher.faculty_id')
            ->leftJoin('department','department.id','=','teacher.department_id');
        }
        
        if($request->includeFaculty){
            $items->addSelect('faculty.name_th as faculty_name');
            $items->leftJoin('faculty','faculty.id','=','teacher.faculty_id');
        }

        if($request->includeDepartment){
             $items->addSelect('department.name_th as department_name');
            $items->leftJoin('department','department.id','=','teacher.department_id');
        }

        // WHERE
        if ($request->id) {
            $items->where('teacher.id', $request->id);
        }

        if ($request->user_id) {
            $items->where('teacher.user_id', $request->user_id);
        }

        if ($request->person_key) {
            $items->where('teacher.person_key', $request->person_key);
        }

        if ($request->prefix) {
            $items->where('teacher.prefix', $request->prefix);
        }

        if ($request->firstname) {
            $items->where('teacher.firstname','LIKE',"%".$request->firstname."%");
        }

        if ($request->surname) {
            $items->where('teacher.surname','LIKE',"%".$request->surname."%");
        }

        if ($request->fullname) {
            $items->where(DB::raw("concat(prefix,'',firstname, ' ', surname)"), 'LIKE', "%".$request->fullname."%");
        }

        if ($request->citizen_id) {
            $items->where('citizen_id',$request->citizen_id);
        }

        if ($request->tel) {
            $items->where('tel',$request->tel);
        }

        if ($request->email) {
            $items->where('email',$request->email);
        }

        if ($request->address) {
            $items->where('teacher.address','LIKE',"%".$request->address."%");
        }

        if ($request->province_id) {
            $items->where('teacher.province_id',$request->province_id);
        }

        if ($request->amphur_id) {
            $items->where('teacher.amphur_id',$request->amphur_id);
        }

        if ($request->tumbol_id) {
            $items->where('teacher.tumbol_id',$request->tumbol_id);
        }

        if ($request->faculty_id) {
            $items->where('teacher.faculty_id',$request->faculty_id);
        }

        if ($request->department_id) {
            $items->where('teacher.department_id',$request->department_id);
        }

        if ($request->active) {
            $items->where('teacher.active', $request->active);
        }

        // Order
        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
        }else{
            $items = $items->orderBy('teacher.id', 'asc');
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
        if(in_array($_SERVER['HTTP_HOST'], whitelist)){
            $this->uploadUrl = 'http://localhost:8117/storage';
        }
        $item = Teacher::select(
                DB::raw("(CASE WHEN signature_file = NULL THEN ''
                 ELSE CONCAT('".$this->uploadUrl."',signature_file) END) AS signature_file"),
                'teacher.id as id',
                'teacher.user_id as user_id',
                'teacher.person_key as person_key',
                'teacher.prefix as prefix',
                'teacher.firstname as firstname',
                'teacher.surname as surname',
                'teacher.citizen_id as citizen_id',
                'teacher.tel as tel',
                'teacher.email as email',
                'teacher.address as address',
                'teacher.province_id as province_id',
                'teacher.amphur_id as amphur_id',
                'teacher.tumbol_id as tumbol_id',
                'teacher.faculty_id as faculty_id',
                'teacher.department_id as department_id',
                'teacher.executive_position as executive_position',
                'teacher.active as active',
                'province.name_th as province_name',
                'amphur.name_th as amphur_name',
                'tumbol.name_th as tumbol_name',
                'faculty.name_th as faculty_name',
                'department.name_th as department_name',
            )
            ->where('teacher.id', $id)
            ->leftJoin('province','province.province_id','=','teacher.province_id')
            ->leftJoin('amphur','amphur.amphur_id','=','teacher.amphur_id')
            ->leftJoin('tumbol','tumbol.tumbol_id','=','teacher.tumbol_id')
            ->leftJoin('faculty','faculty.id','=','teacher.faculty_id')
            ->leftJoin('department','department.id','=','teacher.department_id')
            ->first();

        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }

    // public function add(Request $request)
    // {
    //     $request->validate([
    //         'name as required',
    //         'name as required',
    //         'email as required',
    //         'account_type as required',
    //     ]);
        
    //     // $data = $request->all();
    //     // $data['column9'] = 'aaaa';
    //     // Province::create($data);

    //     // Province::create([
    //     //     'provincename' => $request->provincename,
    //     //     'name' => $request->name,
    //     //     'email' => $request->email,
    //     //     'citizen_id' => $request->citizen_id,
    //     //     'account_type' => $request->account_type,
    //     // ]);

    //     $data = new Province;
    //     $data->provincename = $request->provincename;
    //     $data->name = $request->name;
    //     $data->email = $request->email;
    //     $data->citizen_id = $request->citizen_id;
    //     $data->account_type = $request->account_type;
    //     $data->save();

    //     $responseData = [
    //         'message' => 'success',
    //         'data' => $data,
    //     ];

    //     return response()->json($responseData, 200);
    // }

    public function edit($id, Request $request)
    {
        $request->validate([
            'id as required',
        ]);

        $item = Teacher::where('id', $id)->first();

        $pathSignature = null;
        if(($request->signature_file != "") && ($request->signature_file != 'null') && ($request->signature_file != 'undefined')){
            $fileSignature = 'signature-'.rand(10,100).'-'.$request->file('signature_file')->getClientOriginalName();
            $pathSignature = '/teacher/signature/'.$fileSignature;
            Storage::disk('public')->put($pathSignature, file_get_contents($request->signature_file));
            $request->signature_file = $pathSignature;
        }else{
            $pathSignature  = $item->signature_file;
        }

        $item->prefix = $request->has('prefix') ? $request->prefix: $item->prefix;
        $item->firstname = $request->has('firstname') ? $request->firstname: $item->firstname;
        $item->surname = $request->has('surname') ? $request->surname: $item->surname;
        // $item-> = $request->has('') ? $request->: $item->;
        $item->tel = $request->has('tel') ? $request->tel : $item->tel;
        $item->email = $request->has('email') ? $request->email : $item->email;
        $item->signature_file = $pathSignature;
        $item->address = $request->has('address') ? $request->address : $item->address;
        $item->province_id = $request->has('province_id') ? $request->province_id : $item->province_id;
        $item->amphur_id  = $request->has('amphur_id') ? $request->amphur_id : $item->amphur_id;
        $item->tumbol_id = $request->has('tumbol_id') ? $request->tumbol_id : $item->tumbol_id;
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
        $data = Teacher::where('id', $id)->first();
        
        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }


    // public function hrisPersonnelInfo($data) {
    //     let dataParams = {};
    //     if (data.person_key) dataParams["person_key"] = data.person_key;
    
    //     if (data.citizen_id) dataParams["citizen_id"] = data.citizen_id;
    
    //     dataParams["get_work_info"] = 1;
    //     dataParams["get_citizen_id"] = 1;
    //     // dataParams['get_icit_account'] = 1;
    
    //     let config = {
    //       method: "post",
    //       url: "https://api.hris.kmutnb.ac.th/api/personnel-api/personnel-detail",
    //       headers: { Authorization: "Bearer " + apiToken },
    //       data: dataParams,
    //       // data: { person_key: id, get_work_info : 1 },
    //     };
    
    //     return new Promise(async (resolve, reject) => {
    //       try {
    //         const apiObj = await axios(config)
    //           .then((response) => {
    //             return response.data;
    //           })
    //           .catch((error) => {
    //             if (error.response.status === 404) {
    //               //reject(ErrorNotFound("ไม่พบข้อมูลบุคลากรในระบบ HRIS"));
    //               reject("ไม่พบข้อมูลบุคลากรในระบบ HRIS");
    //             }
    //             reject(error);
    //           });
    
    //         let apiData = {
    //           person_key: apiObj.person_key,
    //           citizen_id: apiObj.person_info.citizen_id,
    //           person_photo: apiObj.person_photo,
    //           last_updated_at: apiObj.last_updated_at,
    //           prefix: apiObj.person_info.full_prefix_name_th,
    //           firstname: apiObj.person_info.firstname_th,
    //           surname: apiObj.person_info.lastname_th,
    //           faculty_code: apiObj.work_info.faculty_code,
    //           faculty_name: apiObj.work_info.faculty_name_th,
    //           department_code: apiObj.work_info.department_code,
    //           department_name: apiObj.work_info.department_name_th,
    //           position_id: apiObj.work_info.position_id,
    //           position_th: apiObj.work_info.position_th,
    //           // icit_account: apiObj.icit_account,
    //         };
    //         //console.log("service fac = " +apiObj.work_info.faculty_code);
    //         resolve(apiData);
    //         // resolve(apiObj);
    //       } catch (error) {
    //         reject(error);
    //       }
    //     });
    //   }
      public function getHrisPersonel(Request $request) {

        $request->validate([
            'firstname as required',
            'surname as required',
        ]);

        $access_token = 'rn7496A7JE7jEnstEbAQDsm2bstbKhaW'; // <----- API - Access Token Here
        // 
        $data = [
            'faculty_code' => 14,
        ];

        if ($request->firstname){
            $data['firstname'] = $request->firstname;
        }

        if ($request->surname){
            $data['surname'] = $request->surname;
        }

        if ($request->position_type_id){
            $data['position_type_id'] = $request->position_type_id;
        }

        if ($request->position_type_id){
            $data['person_key'] = $request->person_key;
        }

        $api_url = "https://api.hris.kmutnb.ac.th/api/personnel-api/list-personnel"; // <----- API URL

        $response = Http::timeout(50)->withToken($access_token)->post($api_url, $data);

        if($response != false){
            $get_response = json_decode($response->getBody());
            if (property_exists($get_response, 'data')) {
                return response()->json([
                    'message' => 'success',
                    'person_key' => $get_response->data[0]->person_key,
                    'last_updated_at' =>$get_response->data[0]->last_updated_at,
                    'firstname' =>$get_response->data[0]->firstname_th,
                    'surname' => $get_response->data[0]->lastname_th,
                    'position_type_id' => $get_response->data[0]->position_type_id,
                ], 200);
            } else if ($get_response->status == 404) {
                return response()->json([
                    'message' => 'ไม่พบข้อมูลบุคลากรในระบบ HRIS',
                ], 404);
            } else {
                return $response;
            }
        }else{
            return response()->json([
                'message' => 'API ICIT HRIS ERROR',
            ], 503);
        }
      }
    
}
