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
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DepartmentController;

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

    public function add(Request $request)
    {
        $request->validate([
            'citizen_id as required',
            'person_key as required',
        ]);

        $item = new Teacher;

        $pathSignature = null;
        if(($request->signature_file != "") && ($request->signature_file != 'null') && ($request->signature_file != 'undefined')){
            $fileSignature = 'signature-'.rand(10,100).'-'.$request->file('signature_file')->getClientOriginalName();
            $pathSignature = '/teacher/signature/'.$fileSignature;
            Storage::disk('public')->put($pathSignature, file_get_contents($request->signature_file));
            $request->signature_file = $pathSignature;
        }

        $item->person_key = $request->person_key;
        $item->citizen_id = $request->citizen_id;
        $item->prefix = $request->prefix;
        $item->citizen_id = $request->citizen_id;
        $item->firstname = $request->firstname;
        $item->surname = $request->surname;
        $item->tel = $request->tel;
        $item->email = $request->email;
        $item->signature_file = $pathSignature;
        $item->address = $request->address;
        $item->province_id = $request->province_id;
        $item->amphur_id = $request->amphur_id;
        $item->tumbol_id = $request->tumbol_id;
        $item->faculty_id = $request->faculty_id;
        $item->department_id = $request->department_id;
        $item->updated_by = 'arnonr';
        $item->save();

        $responseData = [
            'message' => 'success',
            'data' => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        // $request->validate([
        //     'id as required',
        // ]);
            // print_r($request);
        if(!empty($request->citizen_id)){
            echo "xxx";
        }else{
            echo "yyy";
        }
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

        $item->person_key = $request->has('person_key') ? $request->person_key: $item->person_key;
        $item->citizen_id = $request->has('citizen_id') ? $request->citizen_id: $item->citizen_id;
        $item->prefix = $request->has('prefix') ? $request->prefix: $item->prefix;
        $item->firstname = $request->has('firstname') ? $request->firstname: $item->firstname;
        $item->surname = $request->has('surname') ? $request->surname: $item->surname;
        $item->tel = $request->has('tel') ? $request->tel : $item->tel;
        $item->email = $request->has('email') ? $request->email : $item->email;
        $item->signature_file = $pathSignature;
        $item->address = $request->has('address') ? $request->address : $item->address;
        $item->province_id = $request->has('province_id') ? $request->province_id : $item->province_id;
        $item->amphur_id  = $request->has('amphur_id') ? $request->amphur_id : $item->amphur_id;
        $item->tumbol_id = $request->has('tumbol_id') ? $request->tumbol_id : $item->tumbol_id;
        $item->active = $request->has('active') ? $request->active : $item->active;
        $item->faculty_id = $request->has('faculty_id') ? $request->faculty_id : $item->faculty_id;
        $item->department_id = $request->has('department_id') ? $request->department_id : $item->department_id;
        $item->hris_last_updated_at = $request->has('hris_last_updated_at') ? $request->hris_last_updated_at : $item->hris_last_updated_at;
        // $item->hris_last_updated_at = '2023-06-11 01:54:00';
        // $item->department_id = 7;

        // echo '$item->hris_last_updated_atxx'.$item->hris_last_updated_at;

        $item->updated_by = 'arnonr';
        // print_r($item);
        // $item->save();
        if($item->save()){
            echo "OK";
        }else{
            echo "Fail";
        }

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


    public function hrisPersonnelInfo(Request $request) {
        $dataParams = [];

        if ($request->person_key) $dataParams["person_key"] = $request->person_key;

        if ($request->citizen_id) $dataParams["citizen_id"] = $request->citizen_id;

        $dataParams["get_work_info"] = 1;
        $dataParams["get_citizen_id"] = 1;
        // dataParams['get_icit_account'] = 1;

        $access_token = 'rn7496A7JE7jEnstEbAQDsm2bstbKhaW'; // <----- API - Access Token Here

        $api_url = 'https://api.hris.kmutnb.ac.th/api/personnel-api/personnel-detail'; // <----- API URL

        $response = Http::timeout(50)->withToken($access_token)->post($api_url, $dataParams);

        if($response != false){
            console.log($response);

            $apiData = [
                'person_key' => $response['person_key'],
                'citizen_id' => $response['person_info']['citizen_id'],
                'person_photo' => $response['person_photo'],
                'last_updated_at' => $response['last_updated_at'],
                'prefix' => $response['person_info']['full_prefix_name_th'],
                'firstname' => $response['person_info']['firstname_th'],
                'surname' => $response['person_info']['lastname_th'],
                'faculty_code' => $response['work_info']['faculty_code'],
                'faculty_name' => $response['work_info']['faculty_name_th'],
                'department_code' => $response['work_info']['department_code'],
                'department_name' => $response['work_info']['department_name_th'],
                'position_id' => $response['work_info']['position_id'],
                'position_th' => $response['work_info']['position_th'],
                // icit_account: apiObj.icit_account,
            ];
              //console.log("service fac = " +apiObj.work_info.faculty_code);

            //   return response()->json([
            //     'message' => 'success',
            //     'apiData' => $apiData
            // ], 200);

            return response()->json($apiData, 200);

        }else{
            return response()->json([
                'message' => 'ไม่พบข้อมูลบุคลากรในระบบ HRIS',
            ], 404);
        }
    }

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
            if (!empty($response['data'])) {
                $api_data = [];
                foreach($response['data'] as $value){
                    array_push($api_data,[
                        'person_key' => $value['person_key'],
                        'last_updated_at' => $value['last_updated_at'],
                        'firstname' => $value['firstname_th'],
                        'surname' => $value['lastname_th'],
                        'position_type_id' => $value['position_type_id'],
                    ]);
                }

                // return $api_data;
                return response()->json($api_data, 200);

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

    public function apiListHrisPersonnel($params)
    {
        $access_token = 'rn7496A7JE7jEnstEbAQDsm2bstbKhaW'; // <----- API - Access Token Here
        //
        $data = [
            'faculty_code' => 14,
        ];

        if (!empty($params['firstname'])){
            $data['firstname'] = $params['firstname'];
        }

        if (!empty($params['surname'])){
            $data['surname'] = $params['surname'];
        }

        if (!empty($params['position_type_id'])){
            $data['position_type_id'] = $params['position_type_id'];
        }

        if (!empty($params['person_key'])){
            $data['person_key'] = $params['person_key'];
        }

        $api_url = "https://api.hris.kmutnb.ac.th/api/personnel-api/list-personnel"; // <----- API URL

        $response = Http::timeout(50)->withToken($access_token)->post($api_url, $data);

        $api_data = [];

        if($response != false){
            if (!empty($response['data'])) {

                foreach($response['data'] as $value){
                    $api_data[] = [
                        'person_key' => $value['person_key'],
                        'last_updated_at' => $value['last_updated_at'],
                        'firstname' => $value['firstname_th'],
                        'surname' => $value['lastname_th'],
                        'position_type_id' => $value['position_type_id'],
                    ];
                }

            }
        }
        return $api_data;
    }

    public function apiHrisPersonnelDetail($params) {
        $dataParams = [];

        if (!empty($params['person_key'])) $dataParams["person_key"] = $params['person_key'];

        if (!empty($params['citizen_id'])) $dataParams["citizen_id"] = $params['citizen_id'];

        $dataParams["get_work_info"] = 1;
        $dataParams["get_citizen_id"] = 1;
        // dataParams['get_icit_account'] = 1;

        $access_token = 'rn7496A7JE7jEnstEbAQDsm2bstbKhaW'; // <----- API - Access Token Here

        $api_url = 'https://api.hris.kmutnb.ac.th/api/personnel-api/personnel-detail'; // <----- API URL

        $response = Http::timeout(50)->withToken($access_token)->post($api_url, $dataParams);
        $apiData = [];
        if($response != false){
            $apiData = [
                'person_key' => $response['person_key'],
                'citizen_id' => $response['person_info']['citizen_id'],
                'person_photo' => $response['person_photo'],
                'last_updated_at' => $response['last_updated_at'],
                'prefix' => $response['person_info']['full_prefix_name_th'],
                'firstname' => $response['person_info']['firstname_th'],
                'surname' => $response['person_info']['lastname_th'],
                'faculty_code' => $response['work_info']['faculty_code'],
                'faculty_name' => $response['work_info']['faculty_name_th'],
                'department_code' => $response['work_info']['department_code'],
                'department_name' => $response['work_info']['department_name_th'],
                'position_id' => $response['work_info']['position_id'],
                'position_th' => $response['work_info']['position_th'],
            ];
        }
        return $apiData;
    }

    function hrisSyncAllTeacher(Request $req) {
        /* ดึงข้อมูลสายวิชาการทั้งหมด */
        $result = $this->apiListHrisPersonnel(['position_type_id' => 1, 'firstname' => 'ทวีศักดิ์']);
        $insert = 0;
        $update = 0;
        foreach ($result as $key => $value) {
            $person_key = $value['person_key'];
            $api_updated_time = $value['last_updated_at'];
            $person_name = $value['firstname'].' '.$value['surname'];

            $teacher_data = Teacher::where('person_key', $person_key)->first();
            // print_r($teacher_data);
            echo "api".$api_updated_time;
            echo '\ndb'.$teacher_data->hris_last_updated_at;
            if ($teacher_data) {
                if($teacher_data->hris_last_updated_at == $api_updated_time){
                    // echo $teacher_data->firstname."Already exists\n";
                    continue;
                }
                // echo $teacher_data->firstname."Update";
                // echo "\n";
            }

            $result_detail = $this->apiHrisPersonnelDetail(['person_key' => $person_key]);

            $citizen_id = $result_detail['citizen_id'];
            $prefix = $result_detail['prefix'];
            $firstname = $result_detail['firstname'];
            $surname = $result_detail['surname'];
            $faculty_code = $result_detail['faculty_code'];
            $faculty_name = $result_detail['faculty_name'];
            $department_code = $result_detail['department_code'];
            $department_name = $result_detail['department_name'];
            $position_id = $result_detail['position_id'];
            $position_th = $result_detail['position_th'];

            // print_r($result_detail);
            $faculty = app('App\Http\Controllers\FacultyController')->import($faculty_code,$faculty_name);
            $department = app('App\Http\Controllers\DepartmentController')->import($department_code,$department_name);

            $reqTeacher = new Request();
            $reqTeacher->person_key = $person_key;
            $reqTeacher->citizen_id = $citizen_id;
            $reqTeacher->prefix = $prefix;
            $reqTeacher->firstname = $firstname;
            $reqTeacher->surname = $surname;
            $reqTeacher->faculty_id = $faculty->id;
            $reqTeacher->department_id = $department->id;
            $reqTeacher->hris_last_updated_at = $api_updated_time;

            // print_r($result_detail);

            if ($teacher_data) {
                echo "ID ".$teacher_data->id;
                $save_data = $this->edit($teacher_data->id, $reqTeacher);
                $update++;
            }else{
                $save_data = $this->add($reqTeacher);
                $insert++;
                echo "Insert";
            }
        }

        $responseData = [
            'message' => 'success',
            'data' => ['insert' => $insert, 'update' => $update],
        ];

        return response()->json($responseData, 200);
    }


    public function OldhrisSyncAllTeacher(Request $req) {
            /* ดึงข้อมูลสายวิชาการทั้งหมด */

            $req = new Request();
            $req->position_type_id = 1;
            //
            $result = $this->getHrisPersonel($req);

            foreach($result as $key => $value){

                print_r($value);

                $person_key = $result[$key]['person_key'];

                $api_updated_time = $result[$key]['last_updated_at'];

                $person_name = $result[$key]['firstname'].' '.$result[$key]['surname'];

                $teacher_data = Teacher::where('person_key', $person_key)->first();

                $db_updated_time = null;

                if ($teacher_data) {
                    $db_updated_time = $api_updated_time;

                    if($db_updated_time == $api_updated_time){
                        continue;
                    }
                }

                $req1 = new Request();
                $req1->person_key = $person_key;

                $resultInfo = $this->hrisPersonnelInfo($req1);

                $faculty = app('App\Http\Controllers\FacultyController')->import($result->faculty_code,$result->faculty_name);
                $major = app('App\Http\Controllers\DepartmentController')->import($result->department_code,$result->department_name);

                $result['faculty_id'] = $faculty->id;
                $result['department_id'] = $department->id;

                $teacher_data = Teacher::where('citizen_id',$resultInfo->citizen_id)->first();

                $resultInfo['person_key'] = resultInfo->person_key;
                // resultInfo['icit_account'] = resultInfo.icit_account;
                $resultInfo['citizen_id'] = resultInfo->citizen_id;
                $resultInfo['faculty_id'] = faculty_id;
                $resultInfo['department_id'] = department_id;
                $resultInfo['hris_last_updated_at'] = resultInfo->last_updated_at;

                $save_data = null;
                if (!$teacher_data) {
                    $resultInfo['created_by'] = 'arnonr';
                    $save_data = $this->add($resultInfo);
                    console.log("insert");
                }else{
                    if($db_updated_time !== $api_updated_time){
                        $save_data = $this->update($teacher_data.id, $resultInfo);
                        console.log("Update");
                    }else{
                        console.log("Up to date");
                    }
                }
            }
            // res.success(result);
    }

}
